<?php
/**
 * Replace Hyperdown with Parsedown
 *
 * @package Parsedown
 * @author ATP
 * @version 1.0.0
 * @link https://atpx.com
 */

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;

require_once 'Parsedown.php';

class Parsedown_Plugin implements PluginInterface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        \Typecho\Plugin::factory('Widget_Abstract_Contents')->markdown = __CLASS__ . '::parse';
        \Typecho\Plugin::factory('Widget_Abstract_Comments')->markdown = __CLASS__ . '::parse';
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {}

    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
    public static function config(Form $form)
    {}

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {}

    public static function parse($text)
    {
        return Parsedown::instance()->setBreaksEnabled(true)->text($text);
    }
}