<?php

declare (strict_types = 1);

namespace Larke\Admin\LogViewer;

use Illuminate\Support\Facades\Artisan;

use Larke\Admin\Extension\Rule;
use Larke\Admin\Extension\Menu;
use Larke\Admin\Extension\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * 扩展信息
     */
    protected $info = [
        'name' => 'larke/log-viewer',
        'title' => '日志查看器',
        'description' => 'laravel的日志查看器',
        'keywords' => [
            'larke',
            'larke-admin',
            'log',
            'viewer',
            'logviewer',
        ],
        'homepage' => 'https://github.com/deatil/larke-admin-logviewer',
        'authors' => [
            [
                'name' => 'deatil', 
                'email' => 'deatil@github.com', 
                'homepage' => 'https://github.com/deatil', 
            ],
        ],
        'version' => '1.4.1',
        'adaptation' => '>=1.4.0',
    ];
    
    /**
     * 扩展图标
     */
    protected $icon = __DIR__ . '/../icon.png';
    
    protected $slug = 'larke-admin.ext.log-viewer';
    
    /**
     * 初始化
     */
    public function boot()
    {
        // 扩展注册
        $this->withExtension(
            $this->info['name'], 
            $this->withExtensionInfo(
                __CLASS__, 
                $this->info, 
                $this->icon
            )
        );
        
        // 事件
        $this->bootListeners();
    }
    
    /**
     * 运行中
     */
    public function start()
    {
        $this->exceptSlugs();
        
        // 语言包
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
        
        // 路由
        $this->loadRoutesFrom(__DIR__ . '/../resources/route/admin.php');
    }
    
    /**
     * 过滤slug
     */
    protected function exceptSlugs()
    {
        $this->withAuthenticateExcepts([
            'larke-admin.log-viewer.download',
        ]);
        
        $this->withPermissionExcepts([
            'larke-admin.log-viewer.download',
        ]);
    }
    
    /**
     * 推送
     */
    protected function assetsPublishes()
    {
        $this->publishes([
            __DIR__.'/../resources/assets/log-viewer' => public_path('extension/log-viewer'),
        ], 'larke-admin-log-viewer-assets');
        
        Artisan::call('vendor:publish', [
            '--tag' => 'larke-admin-log-viewer-assets',
            '--force' => true,
        ]);
    }
    
    /**
     * 监听器
     */
    public function bootListeners()
    {
        $thiz = $this;
        
        // 安装后
        $this->onInatll(function ($name, $info) use($thiz) {
            if ($name == $thiz->info["name"]) {
                $thiz->install();
            }
        });
        
        // 卸载后
        $this->onUninstall(function ($name, $info) use($thiz) {
            if ($name == $thiz->info["name"]) {
                $thiz->uninstall();
            }
        });
        
        // 更新后
        $this->onUpgrade(function ($name, $oldInfo, $newInfo) use($thiz) {
            if ($name == $thiz->info["name"]) {
                $thiz->upgrade();
            }
        });
        
        // 启用后
        $this->onEnable(function ($name, $info) use($thiz) {
            if ($name == $thiz->info["name"]) {
                $thiz->enable();
            }
        });
        
        // 禁用后
        $this->onDisable(function ($name, $info) use($thiz) {
            if ($name == $thiz->info["name"]) {
                $thiz->disable();
            }
        });
    }
    
    /**
     * 安装后
     */
    protected function install()
    {
        $slug = $this->slug;
        
        $rules = include __DIR__ . '/../resources/rules/rules.php';
        
        // 添加权限
        Rule::create($rules);
        
        // 添加菜单
        Menu::create($rules);
        
        $this->assetsPublishes();
    }
    
    /**
     * 卸载后
     */
    protected function uninstall()
    {
        // 删除权限
        Rule::delete($this->slug);
        
        // 删除菜单
        Menu::delete($this->slug);
    }
    
    /**
     * 更新后
     */
    protected function upgrade()
    {}
    
    /**
     * 启用后
     */
    protected function enable()
    {
        // 启用权限
        Rule::enable($this->slug);
        
        // 启用菜单
        Menu::enable($this->slug);
    }
    
    /**
     * 禁用后
     */
    protected function disable()
    {
        // 禁用权限
        Rule::disable($this->slug);
        
        // 禁用菜单
        Menu::disable($this->slug);
    }
    
}
