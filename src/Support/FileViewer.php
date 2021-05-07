<?php

declare (strict_types = 1);

namespace Larke\Admin\LogViewer\Support;

/**
 * 日志
 */
class FileViewer
{
    /**
     * 目录
     *
     * @var string
     */
    protected $path;

    /**
     * 已读取大小
     *
     * @var int
     */
    protected $haveReadSize = 0;

    /**
     * 每次读取大小
     *
     * @var int
     */
    protected $readSize = 1024 * 350;

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
     * 上一页
     *
     * @return bool|string
     */
    public function getPrevPage()
    {
        if ($this->haveReadSize < $this->readSize && $this->haveReadSize > 0) {
            return 0;
        }
        
        if (($this->haveReadSize - $this->readSize) < 0) {
            return false;
        }

        return $this->haveReadSize - $this->readSize;
    }

    /**
     * 下一页
     *
     * @return bool|string
     */
    public function getNextPage($file)
    {
        $filePath = $this->getFilePath($file);
        if (($this->haveReadSize + $this->readSize) >= filesize($filePath)) {
            return false;
        }

        return $this->haveReadSize + $this->readSize;
    }

    /**
     * 读取
     *
     * @param string $file
     * @param int $seek
     * @return array
     */
    public function fetch($file, $seek = 0) 
    { 
        $filename = $this->getFilePath($file);
        
        // 文件大小
        $filesize = filesize($filename);
        
        $readSize = $this->readSize;
        if ($seek + $readSize > $filesize) {
            if ($readSize > $filesize) {
                $seek = 0;
                $readSize = $filesize;
            } else {
                $seek = $filesize - $readSize;
            }
        }
        
        $this->haveReadSize = $seek;

        $output = file_get_contents($filename, false, null, (int) $seek, (int) $readSize);

        return $this->parseLog($output);
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

}
