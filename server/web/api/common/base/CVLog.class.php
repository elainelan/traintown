<?php
/**
 * 文件日志处理
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 文件日志记录类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class CVLog
{

    /**
     * 添加日志
     *
     * @param string $type
     *        日志类型
     * @param array $content
     *        日志内容
     * @param int $force_tmp
     *        是否强制在/tmp写日志
     * @return boolean
     */
    static public function addlog($type, $content = array(), $force_tmp = false)
    {
        return self::_addCustFileLog($type, $content, $force_tmp);
    }

    /**
     * 添加文件日志
     *
     * @param string $type
     *        日志类型
     * @param array $content
     *        日志内容
     * @param int $force_tmp
     *        强制/tmp目录下存储日志
     * @return boolean
     */
    private static function _addCustFileLog($type, $content = array(), $force_tmp)
    {
        $logfile = "/tmp/CVLOG_{$type}.log";
        if (!$force_tmp && defined('CVLOG_PATH') && CVLOG_PATH) {
            $logfile = CVLOG_PATH . "/CVLOG_{$type}.log";
        }
        // 读写方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之。
        $f_res = CVFile::getInstance($logfile);
        
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        $content = date('Y-m-d H:i:s') . "\tURL:{$url}\n" . "GET：" . print_r($_GET, true) . "POST：" . print_r($_POST, true) . "LOG：" . print_r($content, true) . "\n\n";
        
        if ($f_res->append($content) === false) {
            return false;
        }
        $f_res->closed();
        return true;
    }
}

