<?php
/**
 * hCaptcha Plugin
 *
 * @package hCaptcha
 * @author ATP
 * @version 1.0.0
 * @link https://atpx.com
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget;
use Typecho\Widget\Exception;
use Widget\Options;

class hCaptcha_Plugin implements PluginInterface
{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
	public static function activate() {
		\Typecho\Plugin::factory('Widget_Feedback')->comment = array(__CLASS__, '::filter');
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
		$siteKey = new Text('siteKey', NULL, '', _t('Site Key'), _t('You’ll need your <b>site key</b> and <b>secret key</b> on hCaptcha.com in order to proceed.'));
		$secretKey = new Text('secretKey', NULL, '', _t('Secret Key'), _t(''));
		$widgetTheme = new Radio('widgetTheme', array("light" => "Light", "dark" => "Dark"), "light", _t('Widget Theme'), _t('Set the color theme of the widget. Defaults to light.'));
		$widgetSize = new Radio('widgetSize', array("normal" => "Normal", "compact" => "Compact"), "normal", _t('Widget Size'), _t('Set the size of the widget. Defaults to normal.'));
		$form->addInput($siteKey);
		$form->addInput($secretKey);
		$form->addInput($widgetTheme);
        $form->addInput($widgetSize);
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
	 * <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
	 * <div class="h-captcha" data-sitekey="your_site_key"></div>
	 */
	public static function output() {
		$siteKey = Options::alloc()->plugin('hCaptcha')->siteKey;
		$secretKey = Options::alloc()->plugin('hCaptcha')->secretKey;
		$widgetTheme = Options::alloc()->plugin('hCaptcha')->widgetTheme;
		$widgetSize = Options::alloc()->plugin('hCaptcha')->widgetSize;
      		if ($siteKey != "" && $secretKey != "") {
        		echo '<script src="https://hcaptcha.com/1/api.js" async defer></script><div class="h-captcha" data-sitekey="' . $siteKey . '" data-theme="' . $widgetTheme . '" data-size="' . $widgetSize . '"></div>';
      		} else {
			throw new Exception(_t('Error, No hCaptcha Site/Secret Keys.'));
		}
  	}

	/**
     * 插件实现方法
     *
     * @access public
     */
	public static function filter($comments) {
		$user = Widget::widget('Widget_User');
		if($user->hasLogin() && $user->pass('administrator', true)) {
			return $comments;
    	} elseif (isset($_POST['h-captcha-response'])) {
			$siteKey = Options::alloc()->plugin('hCaptcha')->siteKey;
			$secretKey = Options::alloc()->plugin('hCaptcha')->secretKey;
			function getCaptcha($hcaptcha_response, $secretKey) {
				$response = file_get_contents("https://hcaptcha.com/siteverify?secret=".$secretKey."&response=".$hcaptcha_response);
				$response = json_decode($response);
				return $response;
			}
			$responseData = getCaptcha($_POST['h-captcha-response'], $secretKey);
			if ($responseData->success == true) {
				return $comments;
			} else {
				throw new Exception(_t('hCaptcha verification failed. Please try again.'), 403);
			}
		} else {
			throw new Exception(_t('Could not connect to the hCaptcha service. Please check your internet connection and reload to get a hCaptcha challenge.'), 404);
	  	}
  	}
}