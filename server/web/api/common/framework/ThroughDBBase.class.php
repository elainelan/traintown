<?php
/**
 * 通过DB获取数据
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 通过DB获取数据处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 *            
 */
class ThroughDBBase
{

    public static function getInstanceDb($dbip, $dbname, $dbuser, $dbpwd)
    {
        return CVDbPdo::getInstance($dbip, $dbname, $dbuser, $dbpwd);
    }
    
    // TODO：后续补充操作
}