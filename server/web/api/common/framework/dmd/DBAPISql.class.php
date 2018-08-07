<?php
/**
 * DBAPISql通讯处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/dmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

require_once COM_PATH . 'base/ezsql/Sql.class.php';

/**
 * DBAPISql通讯处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/dmd
 */
class DBAPISql
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
        // SqlConetxt判断
        self::$sqlConetxt = $sqlConetxt = unserialize($cmd_para['sql']);
        if (!self::$sqlConetxt instanceof SqlConetxt) {
            ResultParser::error(DErrorCode::PARAM_ERROR);
        }
        
        // 获取数据库连接
        self::$s_db = self::_getDbInstance($cmd_para['table']);
    }

    /**
     * 根据表名获取数据库连接信息
     *
     * @param string $table
     *        需要操作的表名称
     *        
     */
    private static function _getDbInstance($table)
    {
        $db_instance = null;
        switch ($table) {
            default:
                // TODO: 测试代码，需要按照实际情况编写
                $db_instance = CVDbPdo::getInstance('127.0.0.1', 'new_center_api', 'root', '123456');
                break;
        }
        
        if (!$db_instance) {
            ResultParser::error(DErrorCode::PARAM_ERROR);
        }
        
        return $db_instance;
    }
}

