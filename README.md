## 日志查看器 - laravel日志查看扩展


### 项目介绍

*  本扩展为 `larke-admin` 扩展
*  特性为查看 `laravel` 生成的日志


### 环境要求

 - PHP >= 7.3.0
 - Laravel >= 8.0.0
 - larke-admin >= 1.1.7


### 截图预览

![logviewer-index](https://user-images.githubusercontent.com/24578855/109376630-e393b080-7900-11eb-883a-585902b655b7.png)

![logviewer-detail](https://user-images.githubusercontent.com/24578855/109376628-e098c000-7900-11eb-9fc5-a86fed3b3c2c.png)


### 安装步骤

1、下载安装扩展

```php
composer require larke/log-viewer
```

或者在`本地扩展->扩展管理->上传扩展` 本地上传

2、然后在 `本地扩展->扩展管理->安装/更新` 安装本扩展

3、安装后可以在 `public/extension/log-viewer` 发现本扩展的前端文件

4、将 `log-viewer` 该文件夹复制到前端编译目录 `src/extension` 下进行编译预览

5、你可以在 `src/routes.js` 文件修改扩展在左侧菜单的排序


### 开源协议

*  本扩展 遵循 `Apache2` 开源协议发布，在保留本扩展版权的情况下提供个人及商业免费使用。 


### 版权

*  该系统所属版权归 deatil(https://github.com/deatil) 所有。
