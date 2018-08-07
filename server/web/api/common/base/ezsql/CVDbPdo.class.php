<?php

/**
 * CVDbPdo类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */

/**
 * CVDbPdo类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */
class CVDbPdo extends PDO
{

    /**
     * PDO实例指针数组
     *
     * @var array
     */
    private static $s_instance = array();

    /**
     * 初始化DB连接
     *
     * @param string $dsn
     *        pdo的连接dsn
     * @param string $username
     *        数据库账号
     * @param string $passwd
     *        数据库密码
     * @param string $options
     *        数据库初始化属性
     */
    public function __construct($dsn, $username, $passwd, $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';",PDO::MYSQL_ATTR_FOUND_ROWS => true))
    {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * 获取实例指针
     *
     * @param string $dbip
     *        数据库IP
     * @param string $dbname
     *        数据库库名
     * @param string $dbuser
     *        数据库连接用户名
     * @param string $dbpwd
     *        数据库连接密码
     *        
     * @return CVDbPdo
     */
    public static function getInstance($dbip, $dbname, $dbuser, $dbpwd)
    {
        $instance_key = $dbip . '_' . $dbname . '_' . $dbuser;
        if (!isset(self::$s_instance[$instance_key])) {
            $dsn = "mysql:host={$dbip};port=3306;dbname={$dbname}";
            self::$s_instance[$instance_key] = new CVDbPdo($dsn, $dbuser, $dbpwd);
        }
        return self::$s_instance[$instance_key];
    }
}
