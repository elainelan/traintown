<?php

/**
 * CCVDbHttp类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */

/**
 * CCVDbHttp类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */
class CCVDbHttp extends SqlExecImplAbstract
{

    /**
     * 实例指针数组
     *
     * @var array
     */
    private static $s_instance = array();

    private $db_http_url;
    private $table;
    
    /**
     * 初始化DB连接
     *
     * @param string $db_http_url
     *        DBAPI接口地址
     */
    public function __construct($db_http_url, $table)
    {
        $this->db_http_url = $db_http_url;
        $this->table = $table;
    }

    /**
     * 获取实例指针
     *
     * @param string $db_http_url
     *        DBAPI接口地址
     * @param string $table
     *        操作表名称
     * @return CCVDbHttp
     */
    public static function getInstance($db_http_url, $table)
    {
        $instance_key = $db_http_url . '_' . $table;
        if (!isset(self::$s_instance[$instance_key])) {
            self::$s_instance[$instance_key] = new CCVDbHttp($db_http_url, $table);
        }
        return self::$s_instance[$instance_key];
    }
    
    public function sqlExecImplExec($context, $errExce = true)
    {
        $get = array(
            'dmd'   =>  'dapi.api',
        );
        
        $post = array();
        $post['cmds'] = array();
        
        $post['cmds']['0'] = array(
            'cmd_type' => APICMD::SQL_MOD,
            'cmd_para' => array(
                'sql'   => serialize($context),
                'table' => $this->table,
            )
        );
        
        $api_res = CThroughAPI::DBAPIOne($this->db_http_url, $get, $post);
        
        if ($api_res['r']) {
            // 成功
            $res = $api_res['data']['0'];
            return $res;
        }
        else if ($errExce) {
            // 数据请求失败
            throw new Exception("SqlExecImplExec error! ", $api_res['errCode']);
        }
        
        return false;
    }
    
    public function sqlExecImplExport($context, $file, $errExce = true)
    {
        
    }
    
    public function sqlExecImplGet($context, $dictAs = null, $errExce = true)
    {
        $get = array(
            'dmd'   =>  'dapi.api',
        );
        
        $post = array();
        $post['cmds'] = array();
        
        $post['cmds']['0'] = array(
            'cmd_type' => APICMD::SQL_SELECT,
            'cmd_para' => array(
                'sql'   => serialize($context),
                'table' => $this->table,
            )
        );
        
        $api_res = CThroughAPI::DBAPIOne($this->db_http_url, $get, $post);
        
        if ($api_res['r']) {
            // 成功
            $res = $api_res['data']['0'];
            
            // 排序
            if ($dictAs) {
                $dict = array();
                foreach ($res as & $i) {
                    $dict[$i[$dictAs]] = $i;
                }
                return $dict;
            }
            return $res;
        }
        else if ($errExce) {
            // 数据请求失败
            throw new Exception("SqlExecImplGet error! ", $api_res['errCode']);
        }

        return false;
    }
}
