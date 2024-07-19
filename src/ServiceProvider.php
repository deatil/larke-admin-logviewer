<?php

declare (strict_types = 1);

namespace Larke\Admin\LogViewer;

use Illuminate\Support\Facades\Artisan;

use Larke\Admin\Extension\Rule;
use Larke\Admin\Extension\Menu;
use Larke\Admin\Extension\ServiceProvider as BaseServiceProvider;

use function Larke\Admin\register_install_hook;
use function Larke\Admin\register_uninstall_hook;
use function Larke\Admin\register_upgrade_hook;
use function Larke\Admin\register_enable_hook;
use function Larke\Admin\register_disable_hook;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * composer
     */
    public $composer = __DIR__ . '/../composer.json';

    /**
     * 扩展图标
     */
    protected $icon = __DIR__ . '/../icon.png';
    
    // 包名
    protected $pkg = "larke/log-viewer";
    
    protected $slug = 'larke-admin.ext.log-viewer';
    
    /**
     * 初始化
     */
    public function boot()
    {
        // 扩展注册
        $this->addExtension(
            name:     __CLASS__, 
            composer: $this->composer,
            icon:     $this->icon,
        );
    }
    
    /**
     * 在扩展安装、扩展卸载等操作时有效
     */
    public function action()
    {
        register_install_hook($this->pkg, [$this, 'install']);
        register_uninstall_hook($this->pkg, [$this, 'uninstall']);
        register_upgrade_hook($this->pkg, [$this, 'upgrade']);
        register_enable_hook($this->pkg, [$this, 'enable']);
        register_disable_hook($this->pkg, [$this, 'disable']);
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
        $this->addAuthenticateExcepts([
            'larke-admin.log-viewer.download',
        ]);
        
        $this->addPermissionExcepts([
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
