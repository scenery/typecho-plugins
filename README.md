Typecho plugins

# 目录

* [CaptchaPlus](#captchaplus)
* [Parsedown](#parsedown)

# 使用

## CaptchaPlus

_**Update 2023-01-22**_

添加 [Cloudflare Turnstile](https://www.cloudflare-cn.com/products/turnstile/) 验证工具支持。

---

**Typecho 版本：>= 1.2.0**

1. 注册 [hCaptcha](https://www.hcaptcha.com/signup-interstitial) 或者 [Cloudflare](https://dash.cloudflare.com/sign-up) 账号，在 Sites 菜单栏里点击 `New Site` 添加一个网站获取 `Site Key`，点击你的头像 - Settings 获取 `Secret Key`；
  
2. 下载插件，文件夹命名为 `CaptchaPlus` 上传到 Typecho 网站目录 `/usr/plugins/` 路径下；

3. 进入网站后台-控制台-插件，找到 CaptchaPlus 点击启用并设置。

4. 打开 `/usr/themes/` 你的主题目录下 `comments.php` 文件，在提交按钮前面/后面插入下面代码：
```php
<?php if (array_key_exists('CaptchaPlus', Typecho_Plugin::export()['activated'])) : CaptchaPlus_Plugin::output(); endif; ?>
```

5. 如果提交评论失败，可能是开启了评论反垃圾保护导致，在网站后台-设置-评论里关闭，或者在主题目录下的 `functions.php` 文件中找到 `function themeInit()` 函数，里面添加：
```php
$options = Helper::options();
$options -> commentsAntiSpam = false;
```

在[我的博客](https://atpx.com/typecho-captchaplus-plugin/)中查看更详细的介绍。

### Thanks
- [reCAPTCHA](https://github.com/shuxiao9058/reCAPTCHA)
- [CommentFilter](https://www.imhan.com)

## Parsedown

**Typecho 版本：>= 1.2.0**

将 Typecho 默认的 Markdown 解析器 [Hyperdown](https://github.com/segmentfault/HyperDown) 替换为 [Parsedown](https://github.com/erusev/parsedown)。
 
1. 下载插件，文件夹命名为 `Parsedown` 上传到 Typecho 网站目录 `/usr/plugins/` 路径下；

2. 进入网站后台-控制台-插件，找到 Parsedown 点击启用即可，默认会替换文章和评论内容的解析。

