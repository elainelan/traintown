<?php
/**
 * GameServers区服信息
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * GameServers区服信息类
 *
 * 缓存结构：
 * 单个区服信息缓存：cache_key_sub_string : $sid, cache_value: $table_$sid => array(sid=>array(...))
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class GameServers extends CDbTableBase
{

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'game_servers';
        if (isset($this->s_db_default) && $this->s_db_default == 'http') {
            $this->s_db = CDbuser::getInstanceDbHttp($this->table);
        }
        else {
            $this->s_db = CDbuser::getInstanceDbPdo();
        }
    }

    /**
     * 通过sids数组获取区服信息（无缓存）
     *
     * @param unknown $sids        
     */
    function getBySids($sids)
    {
        
        $data = Sql::select('*')->from($this->table)->where('sid in (?)', $sids)->get($this->s_db, 'sid');
        
        // platids转换成数组
        if ($data) {
            foreach ($data as $k => & $v) {
                if (!empty($v['platids'])) {
                    $v['platids'] = explode(',', $v['platids']);
                }
            }
        }
        
        return $data;
    }

    /**
     * 通过条件获取区服信息（无缓存）
     *
     * @param array $where_args
     *        查询条件where数组
     * @param int $page
     *        页码
     * @param int $num
     *        每页数据量
     * @return array 区服信息array(sid=>array('srv_ip'=>'', 'name'=>'', 'platids'=>array(1,2), ...))
     */
    function getByCond($where_args = array(), $page = 1, $num = 0, $orderby_args = array())
    {
        // 条件数组含platid处理
        if (!empty($where_args['platids'])) {
            $platid = (int)$where_args['platids'];
            $where_args['0'] = array('<>' => Sql::native("find_in_set('{$platid}', platids)"));
        }
        unset($where_args['platids']);
        
        // limit处理
        $start = 0;
        $size = 0;
        if (!empty($num) && $num > 0) {
            $start = ($page - 1) * $num;
            $size = $num;
        }
        
        $total = Sql::select('count(1) as sum')->from($this->table)->whereArgs($where_args)->get($this->s_db);
        $data = Sql::select('*')->from($this->table)->whereArgs($where_args)->orderByArgs($orderby_args)->limit($start, $size)->get($this->s_db);
        
        // platids转换成数组
        if ($data) {
            foreach ($data as $k => & $v) {
                if (!empty($v['platids'])) {
                    $v['platids'] = explode(',', $v['platids']);
                }
            }
        }
        return Pagination::formatData($page, ceil($total[0]['sum'] / $size), $size, $total[0]['sum'], $data);
    }

    /**
     * 通过条件获取通用区服信息（无缓存）
     *
     * @param array $where_args
     *        查询条件where数组
     *        每页数据量
     * @return array 区服信息array(sid=>array('srv_ip'=>'', 'name'=>'', 'platids'=>array(1,2), ...))
     */
    function commonGetByCond($where_args = array(), $orderby_args = array(), $select_keys)
    {
        // 条件数组含platid处理
        if (!empty($where_args['platids'])) {
            $platid = (int)$where_args['platids'];
            $where_args['0'] = array('<>' => Sql::native("find_in_set('{$platid}', platids)"));
        }
        $data = Sql::select($select_keys)->from($this->table)->whereArgs($where_args)->orderByArgs($orderby_args)->get($this->s_db);
        
        // platids转换成数组
        if ($data) {
            foreach ($data as $k => & $v) {
                if (!empty($v['platids'])) {
                    $v['platids'] = explode(',', $v['platids']);
                }
            }
        }
        return $data;
    }

    /**
     * 验证区服ID是否已经存在（从缓存获取）
     *
     * @param int $sid
     *        区服ID
     */
    public function getValidServer($sid)
    {
        $res = parent::getWithCache($sid, array('sid' => $sid), 'sid');
        if (!$res || !$res[$sid]) {
            return false;
        }
        return $res[$sid];
    }

    /**
     * 新增区服信息
     *
     * @see CDbTableBase::insert()
     * @param int $sid
     *        区服id
     * @param array $insert_values
     *        插入数据数组
     * @param string $insert_attr
     *        插入方式属性（IGNORE等）
     */
    public function insertWithCacheID($sid, $insert_values, $insert_attr = null)
    {
        return parent::insertWithCacheID($sid, $insert_values, $insert_attr);
    }

    public function insertServer($sid, $insert_values)
    {
        return parent::insertWithCacheID($sid, $insert_values);
    }

    /**
     * 删除区服信息
     *
     * @see CDbTableBase::del()
     * @param int $sid
     *        区服ID
     * @param array $where_args
     *        删除条件数组
     */
    public function delWithCache($sid, $where_args)
    {
        return parent::delWithCache($sid, $where_args);
    }

    /**
     * 修改区服信息
     *
     * @param int $sid
     *        区服ID
     * @param array $update_args
     *        更新数据数组
     * @param array $where_args
     *        更新条件数组
     */
    public function updateWithCache($sid, $update_args, $where_args)
    {
        return parent::updateWithCache($sid, $update_args, $where_args);
    }
}