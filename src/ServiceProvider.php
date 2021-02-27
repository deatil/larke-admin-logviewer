<?php

declare (strict_types = 1);

namespace Larke\Admin\LogViewer;

use Illuminate\Support\Facades\Artisan;

use Larke\Admin\Facade\Extension;
use Larke\Admin\Extension\Rule;
use Larke\Admin\Extension\ServiceProvider as BaseServiceProvider;
use Larke\Admin\Frontend\Support\Menu;

class ServiceProvider extends BaseServiceProvider
{
    public $info = [
        'name' => 'larke/log-viewer',
        'title' => '日志查看器',
        'description' => 'laravel日志查看扩展',
        'keywords' => [
            'rsa',
            'ecdsa',
            'eddsa',
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
        'version' => '1.0.0',
        'adaptation' => '1.1.*',
        'require' => [],
    ];
    
    /**
     * 扩展图标
     */
    public $icon = __DIR__ . '/../icon.png';
    
    protected $slug = '';
    
    /**
     * 初始化
     */
    public function boot()
    {
        // 扩展注册
        Extension::extend($this->info['name'], __CLASS__);
        
        $this->slug = 'larke-admin.extension.log-viewer';
    }
    
    /**
     * 运行中
     */
    public function start()
    {
        $this->exceptSlugs();
        
        $this->loadRoutesFrom(__DIR__ . '/../resource/route/admin.php');
    }
    
    /**
     * 过滤slug
     */
    protected function exceptSlugs()
    {
        $authenticateExcepts = config('larkeadmin.auth.authenticate_excepts', []);
        $authenticateExcepts[] = 'larke-admin.log-viewer.download';
        
        $permissionExcepts = config('larkeadmin.auth.permission_excepts', []);
        $permissionExcepts[] = 'larke-admin.log-viewer.download';
        
        config([
            'larkeadmin.auth.authenticate_excepts' => $authenticateExcepts,
            'larkeadmin.auth.permission_excepts' => $permissionExcepts,
        ]);
    }
    
    /**
     * 推送
     */
    protected function assetsPublishes()
    {
        $this->publishes([
            __DIR__.'/../resource/assets/log-viewer' => public_path('extension/log-viewer'),
        ], 'larke-admin-log-viewer-assets');
        
        Artisan::call('vendor:publish', [
            '--tag' => 'larke-admin-log-viewer-assets',
        ]);
    }
    
    /**
     * 安装后
     */
    public function install()
    {
        $rules = include __DIR__ . '/../resource/rules/rules.php';
        
        // 添加权限
        Rule::create($rules);
        
        // 添加菜单
        Menu::create($rules);
        
        $this->assetsPublishes();
    }
    
    /**
     * 卸载后
     */
    public function uninstall()
    {
        // 删除权限
        Rule::delete($this->slug);
        
        // 删除菜单
        Menu::delete($this->slug);
    }
    
    /**
     * 更新后
     */
    public function upgrade()
    {}
    
    /**
     * 启用后
     */
    public function enable()
    {
        // 启用权限
        Rule::enable($this->slug);
        
        // 启用菜单
        Menu::enable($this->slug);
    }
    
    /**
     * 禁用后
     */
    public function disable()
    {
        // 禁用权限
        Rule::disable($this->slug);
        
        // 禁用菜单
        Menu::disable($this->slug);
    }
    
}
