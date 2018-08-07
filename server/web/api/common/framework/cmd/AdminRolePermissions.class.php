<?php
/**
 * admin_role_permissions角色权限
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * admin_role_permissions角色权限类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class AdminRolePermissions extends CDbTableBase
{

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'admin_role_permissions';
        if (isset($this->s_db_default) && $this->s_db_default == 'http') {
            $this->s_db = CDbuser::getInstanceDbHttp($this->table);
        }
        else {
            $this->s_db = CDbuser::getInstanceDbPdo();
        }
    }

    /**
     * 参考
     *
     * @see CDbTableBase::get()
     * @param int $role_id
     *        角色id
     * @param array $where_args
     *        条件数组
     * @param string $idx_key
     *        数据索引key
     */
    function getWithCache($role_id, $where_args = array(), $idx_key = null)
    {
        // 为防止缓存数据索引key无法确定，指定数据索引key
        $where_args = array();
        $idx_key = null;
        if ($role_id) {
            $where_args = array('role_id' => $role_id);
        }
        
        return parent::getWithCache($role_id, $where_args, $idx_key);
    }

    function getWithCacheForce($role_id, $where_args = array(), $idx_key = null)
    {
        // 为防止缓存数据索引key无法确定，指定数据索引key
        $where_args = array();
        $idx_key = null;
        if ($role_id) {
            $where_args = array('role_id' => $role_id);
        }
        
        return parent::getWithCacheForce($role_id, $where_args, $idx_key);
    }

    /**
     * 获取角色权限
     *
     * @param array $role_ids        
     * @return array
     */
    function getPermissions($role_ids)
    {
        $return_permissions = array();
        if (!empty($role_ids)) {
            foreach ($role_ids as $role_id) {
                $permissions = $this->getWithCache($role_id);
                $return_permissions = array_merge($return_permissions, $permissions);
            }
            if ($return_permissions) {
                $return_permissions = array_values(array_unique(parent::array_column($return_permissions, 'tree_id', null)));
                sort($return_permissions);
            }
        }
        return $return_permissions;
    }

    /**
     * 获取角色所有treeids
     */
    function getRoleTreeids($search = array(), $page = 1, $num = 10)
    {
        $items_total_db_res = Sql::select('count(*) as cnts')->from('admin_roles');
        if (!empty($search)) {
            $items_total_db_res = $items_total_db_res->whereArgs($search);
        }
        $items_total_db_res = $items_total_db_res->getOnce($this->s_db);
        
        $items_total = $items_total_db_res['cnts'];
        $page_total = ceil($items_total / $num);
        
        $db_val = Sql::select("id,name,`desc`, baoliu,GROUP_CONCAT(CAST( tree_id AS char) order by tree_id SEPARATOR '|') as treeids")->from('admin_roles')->leftJoin($this->table)->on('admin_role_permissions.role_id = admin_roles.id');
        if (!empty($search)) {
            $db_val = $db_val->whereArgs($search);
        }
        $page_total = ceil($items_total / $num);
        if ($page > $page_total && $page_total > 0) {
            $page = $page_total;
        }
        $db_val = $db_val->groupBy('admin_roles.id')->limit(($page - 1) * $num, $num)->get($this->s_db);
        return Pagination::formatData($page, $page_total, $num, $items_total, $db_val);
    }

    function updatePermission($role_id, $addids, $delids)
    {
        if (!empty($addids)) {
            foreach ($addids as $addid) {
                $add_ids[] = array('role_id' => $role_id, 'tree_id' => $addid);
            }
            $db_val = Sql::insertInto($this->table)->valuesMulti($add_ids)->exec($this->s_db)->lastInsertId();
        }
        if (!empty($delids)) {
            foreach ($delids as $delid) {
                $del_ids = array('role_id' => $role_id, 'tree_id' => $delid);
                $db_val = Sql::deleteFrom($this->table)->whereArgs($del_ids)->exec($this->s_db)->rows;
            }
        
        }
        // 更新角色组缓存
        self::getWithCacheForce($role_id); // 当前角色组缓存
        self::getWithCacheForce(null); // 所有角色组缓存
        

        return $db_val;
    }

    function delRole($role_id)
    {
        $db_val = Sql::deleteFrom($this->table)->where('role_id=?', $role_id)->exec($this->s_db)->rows;
        return $db_val;
    }
}

