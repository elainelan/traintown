<?php
/**
 * 语言包文件
 * 
 * @author dragonets
 * @package lang
 * @subpackage cmd_zh_cn
 */



/**
 * define('DEBUG_ERROR', 1); 时有效，如果匹配，返回结果中有succMsg/errMsg
 * 
 */
$language = array(
    
    CErrorCode::DB_ERROR               =>  'database： %trace%',
    CErrorCode::MISS_CMD               =>  '非法请求！',
    CErrorCode::CLASS_NOT_FOUND        =>  '无效类：%class%',
    CErrorCode::METHOD_NOT_FOUND       =>  '无效方法：%method%',
    CErrorCode::IP_BLACK               =>  '关小黑屋了！',
    CErrorCode::FREQUENCY_LIMIT        =>  '您的操作频率过快，请稍候重试。',
    
    
    CErrorCode::PARAM_ERROR            =>  '参数错误',

    CErrorCode::PLATFORM_ID_ERROR      =>  '非法的平台ID：%platid%',
    CErrorCode::PLATFORM_GET_NULL      =>  '无平台信息',
    
    
    
);