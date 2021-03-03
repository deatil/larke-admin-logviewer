<?php

use Larke\Admin\Facade\Extension;

Extension::routes(function ($router) {
    $router
        ->namespace('Larke\\Admin\\LogViewer\\Controller')
        ->group(function ($router) {
            $router->get('/log-viewer/files', 'Viewer@files')->name('larke-admin.log-viewer.files');
            $router->get('/log-viewer/logs', 'Viewer@logs')->name('larke-admin.log-viewer.logs');
            $router->delete('/log-viewer/delete', 'Viewer@delete')->name('larke-admin.log-viewer.delete');
            $router->get('/log-viewer/download', 'Viewer@download')->name('larke-admin.log-viewer.download');
        });
});