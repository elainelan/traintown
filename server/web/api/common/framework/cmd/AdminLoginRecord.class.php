<?php
/**
 * admin_login_record后台用户组处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * admin_login_record后台用户组处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class AdminLoginRecord extends CDbTableBase
{

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'admin_login_record';
        if (isset($this->s_db_default) && $this->s_db_default == 'http') {
            $this->s_db = CDbuser::getInstanceDbHttp($this->table);
        }
        else {
            $this->s_db = CDbuser::getInstanceDbPdo();
        }
    }

    /**
     * 插入DB数据
     *
     * @see CDbTableBase::insertToDbID
     * @param array $insert_values        
     * @see InsertValuesRule
     */
    function insertDbValues($insert_values)
    {
        $sql = Sql::insertInto($this->table)->values($insert_values);
        return parent::insertToDbID($sql);
    }

    /**
     * 查询个人登录数据
     *
     * @see CDbTableBase::getLoginRecordByUserId
     * @param int $id
     *        用户ID
     * @param int $page
     *        第几页
     * @param int $num
     *        每页显示
     */
    function getLoginRecordByUserId($id, $page = 1, $num = 10)
    {
        $total = Sql::select('count(1) as sum')->from($this->table)->whereArgs(array('admin_userid' => $id))->get($this->s_db);
        $data = Sql::select('*')->from($this->table)->whereArgs(array('admin_userid' => $id))->orderByArgs(array('logintime' => Sql::$ORDER_BY_DESC))->limit(($page - 1) * $num, $num)->get($this->s_db);
        return Pagination::formatData($page, ceil($total[0]['sum'] / $num), $num, $total[0]['sum'], $data);
    }

    /**
     * 查询登录数据
     *
     * @see CDbTableBase::insertToDbID
     * @param int $page
     *        第几页
     * @param int $num
     *        每页显示
     * @param array $login_time
     *        登录时间
     * @param int $platid
     *        平台ID
     * @param string $username
     *        用户名
     * @param string $ip
     *        登录IP
     */
    //     function getLoginRecord($page = 1, $num = 10, $login_time = array(), $plat = 0, $username = '', $ip = '')
    //     {
    //         $user_table = 'admin_users';
    //         $cond_ary = array();
    //         if ($plat) {
    //             $cond_ary[$this->table . '.platid'] = $plat;
    //         }
    //         if ($username) {
    //             $cond_ary[$user_table . '.name'] = $username;
    //         }
    //         if ($ip) {
    //             $cond_ary[$this->table . '.loginip'] = $ip;
    //         }
    //         if ($login_time) {
    //             $cond_ary[$this->table . '.logintime'] = array('between' => $login_time);
    //         }
    //         $total = Sql::select('count(1) as sum')->from($this->table)->leftJoin($user_table)->on($this->table . ".admin_userid=$user_table.id")->whereArgs($cond_ary)->get($this->s_db);
    //         $data = Sql::select($this->table . ".*,$user_table.name as username")->from($this->table)->leftJoin($user_table)->on($this->table . ".admin_userid=$user_table.id")->whereArgs($cond_ary)->orderByArgs(array('logintime' => Sql::$ORDER_BY_DESC))->limit(($page - 1) * $num, $num)->get($this->s_db));
    //         return Pagination::formatData($page, ceil($total[0]['sum'] / $num), $num, $total[0]['sum'], $data);
    //     }
    

    function getLoginRecords($where_args, $orderby_args = array(), $page = 1, $num = 10)
    {
        $total = Sql::select('count(1) as sum')->from($this->table);
        $data = Sql::select("*")->from($this->table);
        if ($where_args) {
            $total = $total->whereArgs($where_args);
            $data = $data->whereArgs($where_args);
        }
        $total = $total->get($this->s_db);
        
        $page_total = ceil($total[0]['sum'] / $num);
        if ($page > $page_total && $page_total > 0) {
            $page = $page_total;
        }
        
        if (is_array($orderby_args) && $orderby_args) {
            $data = $data->orderByArgs($orderby_args);
        }
        
        $data = $data->limit(($page - 1) * $num, $num)->get($this->s_db);
        return Pagination::formatData($page, ceil($total[0]['sum'] / $num), $num, $total[0]['sum'], $data);
    }
}

