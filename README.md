# Typecho plugins

## 目录

* [hCaptcha](#hcaptcha)

## 使用

### hCaptcha

**Typecho 版本：>= 1.2.0**

_1._ 注册 [hCaptcha](https://www.hcaptcha.com/signup-interstitial) 账号，在 Sites 菜单栏里点击 `New Site` 添加一个网站获取 `Site Key`，点击你的头像 - Settings 获取 `Secret Key`；
  
_2._ 下载插件，文件夹命名为 `hCaptcha` 上传到 Typecho 网站目录 `/usr/plugins/` 路径下；

_3._ 进入网站后台-控制台-插件，点击启用：

- **Site Key**：第一步中获取的 `Site Key`
- **Secret Key**：第一步中获取的 `Secret Key`
- **Widget Theme**：主题颜色，可设置 `Light` 或者 `Dark`
- **Widget Size**：样式大小，可设置 `Normal` 或者 `Compact`

_4._ 打开 `/usr/themes/` 你的主题目录下 `comments.php` 文件，在提交按钮前面/后面插入下面代码：
```php
<?php hCaptcha_Plugin::output(); ?>
```

_*5._ 如果提交评论失败，可能是开启了评论反垃圾保护导致，在网站后台-设置-评论里关闭，或者在主题目录下的 `functions.php` 文件中找到 `function themeInit()` 函数，里面添加：
```php
$options = Helper::options();
$options -> commentsAntiSpam = false;
```
