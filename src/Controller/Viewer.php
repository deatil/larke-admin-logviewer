<?php

declare (strict_types = 1);

namespace Larke\Admin\LogViewer\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\LogViewer\Support\LogViewer;
use Larke\Admin\LogViewer\Support\FileViewer;
use Larke\Admin\Http\Controller as BaseController;

/**
 * 查看器
 *
 * @create 2021-2-5
 * @author deatil
 */
#[RouteRule(
    title: "日志查看器", 
    desc:  "用于查看laravel生成的日志",
    order: 155,
    auth:  true,
    slug:  "larke-admin.ext.log-viewer"
)]
class Viewer extends BaseController
{
    /**
     * 日志文件列表
     *
     * @return Response
     */
    #[RouteRule(
        title:  "日志文件", 
        desc:   "日志文件列表",
        order:  160,
        parent: "larke-admin.ext.log-viewer",
        auth:   true
    )]
    public function files(Request $request)
    {
        $keywords = (string) request()->input('keywords', '');
        $order = (string) request()->input('order', 'time');
        
        // 分页
        $page = (int) request()->input('page', 1);
        $pagesize = (int) request()->input('pagesize', 120);
        
        $logViewer = new LogViewer();
        $files = $logViewer
            ->withPath(storage_path('logs'))
            ->withTimezone(config('app.timezone', 'UTC'))
            ->getFiles($keywords);
        
        $list = collect($files);
        
        if ($order == 'name') {
            $list = $list->sort(function ($item) {
                    return $item['name'];
                });
        } else {
            $list = $list->sortByDesc(function ($item) {
                    return $item['time'];
                });
        }
        $list = $list
            ->slice(($page - 1) * $pagesize, $pagesize)
            ->values()
            ->toArray();
        
        $total = count(collect($files)->toArray());
        
        $data = [
            'list' => $list, // 数据
            'total' => $total, // 数量
            'page' => $page, // 当前页码
            'pagesize' => $pagesize, // 每页数量
        ];
        
        return $this->success(__('获取成功'), $data);
    }
    
    /**
     * 日志列表
     *
     * @return Response
     */
    #[RouteRule(
        title:  "日志列表", 
        desc:   "日志列表",
        order:  161,
        parent: "larke-admin.ext.log-viewer",
        auth:   true
    )]
    public function logs(Request $request)
    {
        $file = $request->get('file');
        $offset = $request->get('offset', 0);
        
        if (empty($file)) {
            return $this->error(__('获取失败'));
        }
        
        $fileViewer = new FileViewer();
        $list = $fileViewer
            ->withPath(storage_path('logs'))
            ->withFile($file)
            ->fetch($offset);
        
        $data = [
            'list' => $list,
            'havesize' => $fileViewer->getHaveReadSize(),
            'readsize' => $fileViewer->getReadSize(),
            'filesize' => $fileViewer->getFilesize(),
        ];
        
        return $this->success(__('获取成功'), $data);
    }
    
    /**
     * 删除日志
     *
     * @return Response
     */
    #[RouteRule(
        title:  "删除日志", 
        desc:   "删除日志",
        order:  163,
        parent: "larke-admin.ext.log-viewer",
        auth:   true
    )]
    public function delete(Request $request)
    {
        $file = $request->get('file');
        if (empty($file)) {
            return $this->error(__('日志文件不存在'));
        }
        
        $logViewer = new LogViewer();
        $file = $logViewer
            ->withPath(storage_path('logs'))
            ->getFilePath($file);
            
        if (! File::exists($file) || ! File::isFile($file)) {
            return $this->error(__('日志文件不可操作'));
        }
        
        if (! File::delete($file)) {
            return $this->error(__('日志文件删除失败'));
        }
        
        return $this->success(__('日志文件删除成功'));
    }
    
    /**
     * 下载日志文件
     *
     * @return Response
     */
    #[RouteRule(
        title:  "下载日志", 
        desc:   "下载日志文件",
        order:  165,
        parent: "larke-admin.ext.log-viewer",
        auth:   true
    )]
    public function download(Request $request)
    {
        $file = $request->input('file');
        if (empty($file)) {
            return $this->error(__('日志文件不能为空'));
        }
        
        // 具体日志文件
        $logViewer = new LogViewer();
        $logFile = $logViewer
            ->withPath(storage_path('logs'))
            ->getFilePath($file);
        
        if (! File::exists($logFile) || ! File::isFile($logFile)) {
            return $this->error(__('日志文件不存在'));
        }
        
        return Response::download($logFile, $file);
    }
}
