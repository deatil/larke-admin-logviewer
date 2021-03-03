<?php

return [
    'title' => '日志查看器',
    'url' => '#',
    'method' => 'OPTIONS',
    'slug' => $this->slug,
    'description' => '用于查看laravel生成的日志',
    'children' => [
        [
            'title' => '日志文件',
            'url' => 'log-viewer/files',
            'method' => 'GET',
            'slug' => 'larke-admin.log-viewer.files',
            'description' => '日志文件',
        ],
        
        [
            'title' => '日志列表',
            'url' => 'log-viewer/logs',
            'method' => 'GET',
            'slug' => 'larke-admin.log-viewer.logs',
            'description' => '日志列表',
        ],
        
        [
            'title' => '删除日志',
            'url' => 'log-viewer/delete',
            'method' => 'DELETE',
            'slug' => 'larke-admin.log-viewer.delete',
            'description' => '删除日志文件',
        ],
        
        [
            'title' => '下载日志',
            'url' => 'log-viewer/download',
            'method' => 'GET',
            'slug' => 'larke-admin.log-viewer.download',
            'description' => '下载一个日志文件',
        ],
    ],
];
