<?php
/**
 * GSAPISql通讯处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

require_once COM_PATH . 'base/ezsql/Sql.class.php';

/**
 * GSAPISql通讯处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */
class GSAPISql
{

    /**
     * 传递过来的sqlConetxt
     *
     * @var SqlConetxt
     */
    private static $sqlConetxt;

    /**
     * 数据库连接指针
     *
     * @var pdo
     */
    private static $s_db;

    /**
     * 获取数据
     *
     * @param array $cmd_para        
     */
    public static function get($cmd_para)
    {
        self::_parsePara($cmd_para);
        return SqlExecImpl::get(self::$sqlConetxt, self::$s_db);
    }

    /**
     * 处理数据
     *
     * @param array $cmd_para        
     */
    public static function exec($cmd_para)
    {
        self::_parsePara($cmd_para);
        return SqlExecImpl::exec(self::$sqlConetxt, self::$s_db);
    }

    /**
     * 处理cmd_para参数
     *
     * @param array $cmd_para        
     */
    private static function _parsePara($cmd_para)
    {
        $db_str = self::_getDbTypeStr($cmd_para['db']);
        
        $db_ip = GSAPI::$data['server'][$db_str . 'db_ip'];
        $db_name = GSAPI::$data['server'][$db_str . 'db_name'];
        $db_pwd = GSAPI::$data['server'][$db_str . 'db_pwd'];
        $db_user = GSAPI::$data['server'][$db_str . 'db_user'];
        
        // 数据库 连接信息判断
        if (!$db_ip || !$db_name || !$db_pwd || !$db_user) {
            ResultParser::error(GErrorCode::PARAM_ERROR);
        }
        
        // SqlConetxt判断
        self::$sqlConetxt = $sqlConetxt = unserialize($cmd_para['sql']);
        if (!self::$sqlConetxt instanceof SqlConetxt) {
            ResultParser::error(GErrorCode::PARAM_ERROR);
        }
        
        self::$s_db = GDbuser::getInstanceDb($db_ip, $db_name, $db_user, $db_pwd);
    }

    /**
     * 对应数据库类型字符
     *
     * @param int $db_type        
     */
    private static function _getDbTypeStr($db_type)
    {
        switch ($db_type) {
            case APICMD::DB_TABLE_LOG:
                $db_str = 'log';
                break;
            case APICMD::DB_TABLE_GAME:
                $db_str = 'game';
                break;
            case APICMD::DB_TABLE_KF:
                $db_str = 'kf';
                break;
            case APICMD::DB_TABLE_PAY:
                $db_str = 'pay';
                break;
            default:
                ResultParser::error(GErrorCode::PARAM_ERROR);
                break;
        }
        return $db_str;
    }
}

