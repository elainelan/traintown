<?php
/**
 * 语言包输出处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 输出语言包类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class Lang
{

    /**
     * 语言包数组
     *
     * @var array
     */
    private static $lang;

    /**
     * 获取语言解析
     *
     * @param string $lang_key        
     */
    private static function _getLang($lang_key)
    {
        if (!self::$lang) {
            // 判断语言包
            switch (HttpParam::request('lang')) {
                case 'zh_tw':
                    define('LANG_PATH', CV_ROOT . 'lang/' . PATH_PREFIX . '_zh_tw/');
                    break;
                case 'en':
                    define('LANG_PATH', CV_ROOT . 'lang/' . PATH_PREFIX . '_en/');
                    break;
                case 'ru':
                    define('LANG_PATH', CV_ROOT . 'lang/' . PATH_PREFIX . '_ru/');
                    break;
                default: // zh_cn
                    define('LANG_PATH', CV_ROOT . 'lang/' . PATH_PREFIX . '_zh_cn/');
                    break;
            }
            $filename = LANG_PATH . 'language.php';
            if (file_exists($filename)) {
                $language = null;
                include_once $filename;
                self::$lang = $language;
            }
        }
        return isset(self::$lang[$lang_key]) ? self::$lang[$lang_key] : null;
    }

    /**
     * 替换显示语言
     *
     * @param string $lang_key
     *        语言包key
     * @param array $lang_param
     *        语言包替换数组：array('name'=>val)
     * @return string
     */
    static function show($lang_key, $lang_param)
    {
        $language = self::_getLang($lang_key);
        if (!$language) {
            $language = $lang_key;
        }
        else if (!empty($lang_param) && is_array($lang_param)) {
            foreach ($lang_param as $lang_k => $lang_v) {
                $language = str_replace("%{$lang_k}%", $lang_v, $language);
            }
        }
        return $language;
    }
}

