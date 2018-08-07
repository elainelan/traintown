<?php
/**
 * admin_ip_black表处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * admin_ip_white处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class AdminIpBlack extends CDbTableBase
{

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'admin_ip_black';
        if (isset($this->s_db_default) && $this->s_db_default == 'http') {
            $this->s_db = CDbuser::getInstanceDbHttp($this->table);
        }
        else {
            $this->s_db = CDbuser::getInstanceDbPdo();
        }
    }

    /**
     * 获取全部(platid=null)或者某个(platid=x)平台的黑名单IP
     * 参考@see CDbTableBase::getWithCache()
     *
     * @param int $platid
     *        平台id
     * @param array $where_args
     *        条件数组，强制：array()
     * @param string $idx_key
     *        数据索引key，强制：ip
     * @return array array('ip' => array() )
     */
    public function getWithCache($platid, $where_args = array(), $idx_key = 'ip')
    {
        // 为防止缓存数据索引key无法确定，指定数据索引key
        $idx_key = 'ip';
        $where_args = array();
        if ($platid !== null) {
            $where_args = array('platid' => $platid);
        }
        
        return parent::getWithCache($platid, $where_args, $idx_key);
    }

    /**
     * 参考
     *
     * @see CDbTableBase::insert()
     * @param int $platid
     *        平台ID
     * @param array $insert_values
     *        插入数据数组
     * @param string $insert_attr
     *        插入方式属性（IGNORE等）
     */
    public function insertWithCacheID($platid, $insert_values, $insert_attr = null)
    {
        return parent::insertWithCacheID($platid, $insert_values, $insert_attr);
    }

    /**
     * 参考
     *
     * @see CDbTableBase::del()
     * @param int $platid
     *        平台ID
     * @param array $where_args
     *        删除条件数组
     */
    public function delWithCache($platid, $where_args)
    {
        return parent::delWithCache($platid, $where_args);
    }

    /**
     * 修改黑名单
     *
     * @param int $platid        
     * @param array $update_args        
     * @param array $where_args        
     */
    public function updateWithCache($platid, $update_args, $where_args)
    {
        return parent::updateWithCache($platid, $update_args, $where_args);
    }
}

