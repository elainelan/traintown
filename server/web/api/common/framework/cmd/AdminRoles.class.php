<?php
/**
 * admin_roles角色信息
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * admin_roles角色信息类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class AdminRoles extends CDbTableBase
{

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'admin_roles';
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
    function getWithCache($role_id, $where_args = array(), $idx_key = 'id')
    {
        // 为防止缓存数据索引key无法确定，指定数据索引key
        $where_args = array();
        $idx_key = 'id';
        if ($role_id) {
            $where_args = array('id' => $role_id);
        }
        
        return parent::getWithCache($role_id, $where_args, $idx_key);
    }

    /**
     * 获取所有角色id,name
     *
     * @return array
     */
    function getRoles()
    {
        $db_val = Sql::select('id,name')->from($this->table)->get($this->s_db);
        $index = parent::array_column($db_val, 'id');
        $db_val = array_combine($index, $db_val);
        return $db_val;
    }

    /**
     * 获取某个用户可以添加的角色列表信息（ID和NAME）
     *
     * @param int $user_id        
     * @return array
     */
    function getAddRolesIdNameByUserId($user_id)
    {
        return Sql::select('id, name')->from($this->table)->where('id not in (select role_id from admin_user_roles where user_id = ?)', $user_id)->get($this->s_db, 'id');
    }

    function delRole($role_id)
    {
        $db_val = Sql::deleteFrom($this->table)->where('id =?', $role_id)->exec($this->s_db)->rows;
        return $db_val;
    }

    function addRole($data)
    {
        $db_val = Sql::insertInto($this->table)->values($data)->exec($this->s_db);
        return $db_val;
    }

    function getRoleById($key, $value)
    {
        
        $db_val = Sql::select('id')->from($this->table)->where($key . '=?', $value)->get($this->s_db);
        return $db_val;
    }

    function updateRoleById($id, $name, $desc)
    {
        $update['name'] = $name;
        $update['`desc`'] = $desc;
        $db_val = Sql::update($this->table)
            ->setArgs($update)
            ->where('id=?', $id)
            ->exec($this->s_db)
            ->rows;
        return $db_val;
    }
}

