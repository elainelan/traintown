<?php
/**
 * 通用错误代码处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 通用错误代码类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class ErrorCode
{
    // 数据库错误
    const DB_ERROR = 1000;
    // 错误的指令
    const MISS_CMD = 1001;
    // 模组类未找到
    const CLASS_NOT_FOUND = 1002;
    // 模组方法未找到
    const METHOD_NOT_FOUND = 1003;
    
    // 拉小黑屋了
    const IP_BLACK = 1004;
    // 访问太频繁了
    const FREQUENCY_LIMIT = 1005;
    // 禁止访问，权限不够
    const PERMISSION_FORBIDDEN = 1006;
    
    // 接口参数错误
    const PARAM_ERROR = 1101;
    // 加内存锁失败
    const GET_CACHE_LOCK_FAIL = 1102;
    // 配置错误
    const CONF_ERROR = 1103;
    
    // 导出目录创建失败
    const EXPORT_FOLDER_CREATE_FAILED = 1201;
    // 创建/打开导出文件失败
    const EXPORT_FILE_CREATE_FAILED = 1202;
    // 写入导出数据失败
    const EXPORT_FILE_WRITE_FAILED = 1203;
    // 导出数据为空
    const EXPORT_DATA_NULL = 1204;
    // 数据导出失败
    const EXPORT_FAILED = 1205;
    // 导出数据压缩失败
    const EXPORT_ZIP_FAILED = 1206;
    // 正在导出其它数据
    const EXPORTING_OTHER_DATA = 1207;
    
    
    // CURL访问错误
    const CURL_RES_ERROR            = 1300;
    // API接口sign错误
    const API_SIGN_ERROR            = 1301;
    // API接口数据解密失败
    const API_DATA_ERROR            = 1302;
    // API缺少cmds指令
    const API_MISS_CMDS             = 1303;

}
