<?php

declare (strict_types = 1);

namespace Larke\Admin\LogViewer\Support;

use Symfony\Component\Finder\Finder;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

/**
 * 日志
 */
class LogViewer
{
    /**
     * 目录
     *
     * @var string
     */
    protected $path;

    /**
     * Start and end offset in current page.
     *
     * @var array
     */
    protected $pageOffset = [
        'start' => 0,
        'end' => 0,
    ];

    /**
     * 设置目录
     *
     * @param null $file
     */
    public function withPath($path)
    {
        $this->path = $path;
        
        return $this;
    }

    /**
     * 获取文件
     *
     * @param null $file
     */
    public function getFilePath($file = null)
    {
        $path = $this->path;
        return $path.($file ? DIRECTORY_SEPARATOR . $file : $file);
    }

    /**
     * 文件列表
     *
     * @param string $directory
     * @param string $keywords
     *
     * @return array
     */
    public function getFiles($keywords = '')
    {
        $directory = $this->getFilePath();
        
        if (! empty($keywords)) {
            $data = iterator_to_array(
                Finder::create()
                    ->files()
                    ->ignoreDotFiles(true)
                    ->name('/'.$keywords.'/')
                    ->in($directory)
                    ->sortByName(),
                false
            );
        } else {
            $data = iterator_to_array(
                Finder::create()
                    ->files()
                    ->ignoreDotFiles(true)
                    ->in($directory)
                    ->sortByName(),
                false
            );
        }
        
        $thiz = $this;
        $list = collect($data)
            ->map(function($item) use($thiz) {
                return [
                    'name' => File::basename($item),
                    'file' => File::basename($item),
                    'size' => $thiz->getFileSize($item),
                    'format_size' => $thiz->getFileFormatSize($item),
                    'time' => $thiz->getFileModified($item),
                ];
            })
            ->toArray();
        
        return $list;
    }

    /**
     * Get previous page.
     *
     * @return bool|string
     */
    public function getPrevPage($file)
    {
        $filePath = $this->getFilePath($file);
        if ($this->pageOffset['end'] >= $this->getFilesize($filePath) - 1) {
            return false;
        }

        return $this->pageOffset['end'];
    }

    /**
     * Get Next page.
     *
     * @return bool|string
     */
    public function getNextPage()
    {
        if ($this->pageOffset['start'] == 0) {
            return false;
        }

        return -$this->pageOffset['start'];
    }

    /**
     * Fetch logs by giving offset.
     *
     * @param int $seek
     * @param int $lines
     * @param int $buffer
     *
     * @return array
     *
     * @see http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
     */
    public function fetch(
        $file, 
        $seek = 0, 
        $lines = 20, 
        $buffer = 4096
    ) {
        $f = fopen($this->getFilePath($file), 'rb');

        if ($seek) {
            fseek($f, abs($seek));
        } else {
            fseek($f, 0, SEEK_END);
        }

        if (fread($f, 1) != "\n") {
            $lines -= 1;
        }
        fseek($f, -1, SEEK_CUR);

        // 从前往后读,上一页
        if ($seek > 0) {
            $output = '';

            $this->pageOffset['start'] = ftell($f);

            while (!feof($f) && $lines >= 0) {
                $output = $output.($chunk = fread($f, $buffer));
                $lines -= substr_count($chunk, "\n[20");
            }

            $this->pageOffset['end'] = ftell($f);

            while ($lines++ < 0) {
                $strpos = strrpos($output, "\n[20") + 1;
                $_ = mb_strlen($output, '8bit') - $strpos;
                $output = substr($output, 0, $strpos);
                $this->pageOffset['end'] -= $_;
            }

            // 从后往前读,下一页
        } else {
            $output = '';

            $this->pageOffset['end'] = ftell($f);

            while (ftell($f) > 0 && $lines >= 0) {
                $offset = min(ftell($f), $buffer);
                fseek($f, -$offset, SEEK_CUR);
                $output = ($chunk = fread($f, $offset)).$output;
                fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
                $lines -= substr_count($chunk, "\n[20");
            }

            $this->pageOffset['start'] = ftell($f);

            while ($lines++ < 0) {
                $strpos = strpos($output, "\n[20") + 1;
                $output = substr($output, $strpos);
                $this->pageOffset['start'] += $strpos;
            }
        }

        fclose($f);

        return $this->parseLog($output);
    }

    /**
     * Get tail logs in log file.
     *
     * @param int $seek
     *
     * @return array
     */
    public function tail($file, $seek)
    {
        // Open the file
        $f = fopen($this->getFilePath($file), 'rb');

        if (!$seek) {
            // Jump to last character
            fseek($f, -1, SEEK_END);
        } else {
            fseek($f, abs($seek));
        }

        $output = '';

        while (!feof($f)) {
            $output .= fread($f, 4096);
        }

        $pos = ftell($f);

        fclose($f);

        $logs = [];

        foreach ($this->parseLog(trim($output)) as $log) {
            $logs[] = $log;
        }

        return [$pos, $logs];
    }
    
    /**
     * Parse raw log text to array.
     *
     * @param $raw
     *
     * @return array
     */
    protected function parseLog($raw)
    {
        $logs = preg_split('/\[(\d{4}(?:-\d{2}){2} \d{2}(?::\d{2}){2})\] (\w+)\.(\w+):((?:(?!{"exception").)*)?/', trim($raw), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($logs as $index => $log) {
            if (preg_match('/^\d{4}/', $log)) {
                break;
            } else {
                unset($logs[$index]);
            }
        }

        if (empty($logs)) {
            return [];
        }

        $parsed = [];

        foreach (array_chunk($logs, 5) as $log) {
            $parsed[] = [
                'time'  => $log[0] ?? '',
                'env'   => $log[1] ?? '',
                'level' => $log[2] ?? '',
                'info'  => $log[3] ?? '',
                'trace' => trim($log[4] ?? ''),
            ];
        }

        unset($logs);

        rsort($parsed);

        return $parsed;
    }

    /**
     * 文件大小
     *
     * @return int
     */
    public function getFileSize($filePath)
    {
        return File::size($filePath);
    }

    /**
     * 格式化后的文件大小
     *
     * @return int
     */
    public function getFileFormatSize($filePath)
    {
        return $this->formatFileSize($this->getFileSize($filePath));
    }

    /**
     * 获取日志最后修改时间
     * @return string
     */
    public function getFileModified($filePath)
    {
        return Carbon::parse(File::lastModified($filePath))->format('Y-m-d H:i:s');
    }

    /**
     * 获取日志大小
     * @return string
     */
    public function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'QB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

}
