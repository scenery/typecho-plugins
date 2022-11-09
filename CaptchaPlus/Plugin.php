<?php
/**
 * 评论设置 hCaptcha 验证并通过规则过滤
 *
 * @package CaptchaPlus
 * @author ATP
 * @version 1.1.0
 * @link https://atpx.com
 * 
 * Version 1.1.0 (2022-11-10)
 * 添加评论语种过滤功能
 * 
 * Version 1.0.0 (2022-11-05)
 * 使用 hCaptcha 验证
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

use Typecho\Plugin\PluginInterface;
use Typecho\Widget;
use Typecho\Widget\Exception;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Typecho\Cookie;
use Widget\Options;

class CaptchaPlus_Plugin implements PluginInterface
{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
	public static function activate() {
		\Typecho\Plugin::factory('Widget_Feedback')->comment = __CLASS__ . '::filter';
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
	public static function deactivate()
	{}

    /**
     * 获取插件配置面板
     *
     * @param Form $form
     */
	public static function config(Form $form) {
		$site_key = new Text('site_key', NULL, '', _t('Site Key'), _t('需要注册 <a href="https://www.hcaptcha.com/" target="_blank">hCaptcha</a> 账号以获取 <b>site key</b> 和 <b>secret key</b>'));
		$form->addInput($site_key);

		$secret_key = new Text('secret_key', NULL, '', _t('Secret Key'), _t(''));
		$form->addInput($secret_key);

		$widget_theme = new Radio('widget_theme', array("light" => "浅色", "dark" => "深色"), "light", _t('主题'), _t('设置验证工具主题颜色，默认为浅色'));
		$form->addInput($widget_theme);

		$widget_size = new Radio('widget_size', array("normal" => "常规", "compact" => "紧凑"), "normal", _t('样式'), _t('设置验证工具布局样式，默认为常规'));
        $form->addInput($widget_size);

		$opt_noru = new Radio('opt_noru', array("none" => "无动作", "waiting" => "标记为待审核", "spam" => "标记为垃圾", "abandon" => "评论失败"), "abandon",
        _t('俄文评论操作'), _t('如果评论中包含俄文，则强行按该操作执行'));
        $form->addInput($opt_noru);
        
        $opt_nocn = new Radio('opt_nocn', array("none" => "无动作", "waiting" => "标记为待审核", "spam" => "标记为垃圾", "abandon" => "评论失败"), "waiting",
			_t('非中文评论操作'), _t('如果评论中不包含中文，则强行按该操作执行'));
        $form->addInput($opt_nocn);

		$opt_ban = new Radio('opt_ban', array("none" => "无动作", "waiting" => "标记为待审核", "spam" => "标记为垃圾", "abandon" => "评论失败"), "abandon",
			_t('禁止词汇操作'), _t('如果评论中包含禁止词汇列表中的词汇，将执行该操作'));
        $form->addInput($opt_ban);

        $words_ban = new Textarea('words_ban', NULL, "fuck\n傻逼\ncnm",
			_t('禁止词汇'), _t('多条词汇请用换行符隔开'));
        $form->addInput($words_ban);

        $opt_chk = new Radio('opt_chk', array("none" => "无动作", "waiting" => "标记为待审核", "spam" => "标记为垃圾", "abandon" => "评论失败"), "waiting",
			_t('敏感词汇操作'), _t('如果评论中包含敏感词汇列表中的词汇，将执行该操作'));
        $form->addInput($opt_chk);

        $words_chk = new Textarea('words_chk', NULL, "http://\nhttps://",
			_t('敏感词汇'), _t('多条词汇请用换行符隔开<br />注意：如果词汇同时出现于禁止词汇，则执行禁止词汇操作'));
        $form->addInput($words_chk);
	}

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
	public static function personalConfig(Form $form)
	{}

	/**
	 * 显示 hCaptcha
	 */
	public static function output() {
		$site_key = Options::alloc()->plugin('CaptchaPlus')->site_key;
		$secret_key = Options::alloc()->plugin('CaptchaPlus')->secret_key;
		$widget_theme = Options::alloc()->plugin('CaptchaPlus')->widget_theme;
		$widget_size = Options::alloc()->plugin('CaptchaPlus')->widget_size;
		if ($site_key != "" && $secret_key != "") {
			echo '<script src="https://hcaptcha.com/1/api.js" async defer></script><div class="h-captcha" data-sitekey="' . $site_key . '" data-theme="' . $widget_theme . '" data-size="' . $widget_size . '"></div>';
		} else {
		// throw new Exception(_t('Error, No hCaptcha Site/Secret Keys.'));
		}
  	}

	/**
     * 插件实现方法
     *
     * @access public
     */
	public static function filter($comment) {
		$filter_set = Options::alloc()->plugin('CaptchaPlus');
		$user = Widget::widget('Widget_User');
		function commentFilter($comment){

		}
		if($user->hasLogin() && $user->pass('administrator', true)) {
			return $comment;
    	} elseif (isset($_POST['h-captcha-response'])) {
			$site_key = $filter_set->site_key;
			$secret_key = $filter_set->secret_key;
			function getCaptcha($hcaptcha_response, $secret_key) {
				$response = file_get_contents("https://hcaptcha.com/siteverify?secret=".$secret_key."&response=".$hcaptcha_response);
				$response = json_decode($response);
				return $response;
			}
			$response_data = getCaptcha($_POST['h-captcha-response'], $secret_key);
			if ($response_data->success == true) {
				$opt = "none";
				$error = "";
				// 俄文评论处理
				if ($opt == "none" && $filter_set->opt_noru != "none") {
					if (preg_match("/([\x{0400}-\x{04FF}]|[\x{0500}-\x{052F}]|[\x{2DE0}-\x{2DFF}]|[\x{A640}-\x{A69F}]|[\x{1C80}-\x{1C8F}])/u", $comment['text']) > 0) {
						$error = "Error.";
						$opt = $filter_set->opt_noru;
					}
				}
				// 非中文评论处理
				if ($opt == "none" && $filter_set->opt_nocn != "none") {
					if (preg_match("/[\x{4e00}-\x{9fa5}]/u", $comment['text']) == 0) {
						$error = "At least one Chinese character is required.";
						$opt = $filter_set->opt_nocn;
					}
				}
				// 禁止词汇处理
				if ($opt == "none" && $filter_set->opt_ban != "none") {
					if (CaptchaPlus_Plugin::check_in($filter_set->words_ban, $comment['text'])) {
						$error = "Language, plz :)";
						$opt = $filter_set->opt_ban;
					}
				}
				// 敏感词汇处理
				if ($opt == "none" && $filter_set->opt_chk != "none") {
					if (CaptchaPlus_Plugin::check_in($filter_set->words_chk, $comment['text'])) {
						$error = "Error.";
						$opt = $filter_set->opt_chk;
					}
				}
				// 执行操作
				if ($opt == "abandon") {
					Cookie::set('__typecho_remember_text', $comment['text']);
					throw new Exception($error);
				}
				elseif ($opt == "spam") {
					$comment['status'] = 'spam';
				}
				elseif ($opt == "waiting") {
					$comment['status'] = 'waiting';
				}
				Cookie::delete('__typecho_remember_text');
				return $comment;
			} else {
				throw new Exception(_t('hCaptcha verification failed. Please try again.'));
			}
		} else {
			throw new Exception(_t('Could not connect to the hCaptcha service. Please check your internet connection and reload to get a hCaptcha challenge.'));
	  	}
  	}

	/**
     * 检查 $str 中是否含有 $words_str 中的词汇
     * 
     */
	private static function check_in($words_str, $str)
	{
		$words = explode("\n", $words_str);
		if (empty($words)) {
			return false;
		}
		foreach ($words as $word) {
            if (false !== strpos($str, trim($word))) {
                return true;
            }
		}
		return false;
	}
}