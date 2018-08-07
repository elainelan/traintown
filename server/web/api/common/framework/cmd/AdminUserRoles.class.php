<?php
/**
 * admin_user_roles后台用户角色处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * admin_user_roles后台用户角色处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class AdminUserRoles extends CDbTableBase
{

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'admin_user_roles';
        if (isset($this->s_db_default) && $this->s_db_default == 'http') {
            $this->s_db = CDbuser::getInstanceDbHttp($this->table);
        }
        else {
            $this->s_db = CDbuser::getInstanceDbPdo();
        }
    }

    /**
     * 通过user_id获取所属角色id
     *
     * @param int $user_id        
     * @return array
     */
    function getRolesByUserId($user_id)
    {
        $roles = array();
        
        $db_val = Sql::select('role_id')->from($this->table)->where('user_id=?', $user_id)->get($this->s_db);
        if ($db_val) {
            $roles = array_unique(array_values(parent::array_column($db_val, 'role_id')));
        }
        
        return $roles;
    }

    function delRolesByUserId($user_id)
    {
        return Sql::deleteFrom($this->table)->where('user_id=?', $user_id)->exec($this->s_db);
    }

    /**
     * 通过角色id获取用户id数组
     *
     * @param int $role_id        
     * @return array
     */
    function getUsersByRoleId($role_id)
    {
        $users = array();
        
        $db_val = Sql::select('user_id')->from($this->table)->where('role_id=?', $role_id)->get($this->s_db);
        if ($db_val) {
            $users = array_unique(array_values(parent::array_column($db_val, 'user_id')));
        }
        
        return $users;
    }

    /**
     * 获取用户角色权限组
     *
     * @param array $search
     *        使用者，模糊查询
     * @param number $page
     *        第几页
     * @param number $num
     *        每页数据量
     * @return array
     */
    function getUserRole($where = array(), $page = 1, $num = 10)
    {
        $items_total_db_res = Sql::select('count(*) as cnts')->from('admin_users');
        if (!empty($where)) {
            $items_total_db_res = $items_total_db_res->whereArgs($where);
        
        }
        $items_total_db_res = $items_total_db_res->getOnce($this->s_db);
        $items_total = $items_total_db_res['cnts'];
        $page_total = ceil($items_total / $num);
        
        if ($page > $page_total && $page_total > 0) {
            $page = $page_total;
        }
        
        $db_val = Sql::select("admin_users.id as user_id,admin_users.platid as platid,admin_users.baoliu as baoliu,admin_users.name as loginname,GROUP_CONCAT(CAST(CONCAT_ws(',',role_id,
        admin_roles.name) AS char)
        order by role_id SEPARATOR '|') as idname ")->from("admin_users")->leftJoin('admin_user_roles')->on('admin_users.id=admin_user_roles.user_id')->leftJoin('admin_roles')->on('admin_user_roles.role_id=admin_roles.id');
        if (!empty($where)) {
            $db_val = $db_val->whereArgs($where);
        }
        $db_val = $db_val->groupBy('admin_users.id')->limit(($page - 1) * $num, $num)->get($this->s_db);
        return Pagination::formatData($page, $page_total, $num, $items_total, $db_val);
    }

    /**
     * 删除 用户的 角色
     *
     * @param
     *        $user_id
     * @param
     *        $role_id
     * @return Response
     */
    function delUserRole($user_id, $role_id)
    {
        if ($user_id != 0) {
            $where = array('user_id' => $user_id, 'role_id' => $role_id);
        }
        else {
            $where = array('role_id' => $role_id);
        }
        $db_val = Sql::deleteFrom($this->table)->whereArgs($where)->exec($this->s_db);
        return $db_val;
    }

    /**
     * 用户添加角色
     *
     * @param
     *        $user_id
     * @param
     *        $role_ids
     * @return Response
     */
    function addUserRoles($user_id, $role_ids)
    {
        foreach ($role_ids as $value) {
            $arr[] = array('user_id' => $user_id, 'role_id' => $value);
        }
        $db_val = Sql::insertInto($this->table)->valuesMulti($arr)->exec($this->s_db);
        return $db_val;
    }

    /**
     * 获取角色用户
     *
     * @param $role_name 角色名称        
     * @param        
     *
     * @return SelectGroupByRule|SelectHavingRule|SelectJoinRule
     */
    
    /**
     * 获取角色用户
     *
     * @param string $role_name
     *        角色名称，模糊查询
     * @param number $page
     *        第几页
     * @param number $num
     *        每页数据量
     * @return array
     */
    function getRoleUser($where = array(), $page = 1, $num = 10)
    {
        $items_total_db_res = Sql::select('count(*) as cnts')->from('admin_roles');
        if (!empty($where)) {
            $items_total_db_res = $items_total_db_res->whereArgs($where);
        }
        $items_total_db_res = $items_total_db_res->getOnce($this->s_db);
        $items_total = $items_total_db_res['cnts'];
        $page_total = ceil($items_total / $num);
        if ($page > $page_total && $page_total > 0) {
            $page = $page_total;
        }
        $db_val = Sql::select("admin_roles.id as role_id,admin_roles.name as role_name,GROUP_CONCAT(CAST(CONCAT_ws(',',user_id,admin_users.name,admin_users
        .baoliu) AS char) 
        order by user_id SEPARATOR '|') as uidnames")->from("admin_roles ")->leftJoin('admin_user_roles')->on('admin_roles.id = admin_user_roles.role_id ')->leftJoin('admin_users')->on('admin_user_roles.user_id = admin_users.id');
        if (!empty($where)) {
            $db_val = $db_val->whereArgs($where);
        }
        $db_val = $db_val->groupBy('admin_roles.id')->limit(($page - 1) * $num, $num)->get($this->s_db);
        return Pagination::formatData($page, $page_total, $num, $items_total, $db_val);
    }

    /**
     * 角色 添加用户
     *
     * @param
     *        $user_id
     * @param
     *        $role_ids
     * @return Response
     */
    function addRoleUsers($user_ids, $role_id)
    {
        foreach ($user_ids as $value) {
            $arr[] = array('role_id' => $role_id, 'user_id' => $value);
        }
        $db_val = Sql::insertInto($this->table)->valuesMulti($arr)->exec($this->s_db);
        return $db_val;
    }

    function getLoginnameByRoleId($role_id)
    {
        $db_val = Sql::select('loginname,platid')->from($this->table)
            ->leftJoin('admin_users')->on('admin_user_roles.user_id = admin_users.id')
            ->where('role_id=?', $role_id)->get($this->s_db);
        return $db_val;
    }
}

