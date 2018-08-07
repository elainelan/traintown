<?php
/**
 * 和API通讯处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 和API通讯处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class GThroughAPI extends ThroughAPIBase
{
    public static function GSAPIOne($gs_api_url, $get=array(), $post=array())
    {
        return parent::postOne($gs_api_url, API_GS_SIGN_KEY, $get, $post);
    }
    
    public static function DBAPIOne($db_api_url, $get=array(), $post=array())
    {
        return parent::postOne($db_api_url, API_DB_SIGN_KEY, $get, $post);
    }
    
    public static function CenterAPIOne($center_api_url, $get=array(), $post=array())
    {
        return parent::postOne($center_api_url, API_CENTER_SIGN_KEY, $get, $post);
    }
}
