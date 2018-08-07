<?php
/**
 * admin_menu_trees后台用户角色处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * admin_menu_trees后台用户角色处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class AdminMenuTrees extends CDbTableBase
{

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'admin_menu_trees';
        if (isset($this->s_db_default) && $this->s_db_default == 'http') {
            $this->s_db = CDbuser::getInstanceDbHttp($this->table);
        }
        else {
            $this->s_db = CDbuser::getInstanceDbPdo();
        }
    }

    /**
     * 通过tree_ids获取权限uri
     *
     * @param array $tree_ids        
     * @return array [ {uri=>tree_id} ]
     */
    function getUriByTreeIds($tree_ids)
    {
        $menu_trees = array();
        
        $db_val = Sql::select('tree_id, uri')->from($this->table)->where('tree_id in (?)', $tree_ids)->get($this->s_db);
        if ($db_val) {
            $menu_trees = parent::array_column($db_val, 'tree_id', 'uri');
        }
        return $menu_trees;
    }

    /**
     * 获取折叠菜单权限数组，用于权限判断
     *
     * @param array $uri_treeid_ary
     *        getUriByTreeIds方法返回的结果数组
     * @return array [ {uri=>tree_id} ]
     */
    function getUriBlankExt($uri_treeid_ary = array())
    {
        $uri_blank_ext = array();
        if (!empty($uri_treeid_ary)) {
            $uri_blank = array();
            foreach ($uri_treeid_ary as $uri => $tree_id) {
                if (preg_match('/^uri_blank_/', $uri)) {
                    $uri_blank[] = $tree_id;
                }
            }
            if ($uri_blank) {
                $db_val = Sql::select('tree_id, uri')->from($this->table)->where('parent_tree_id in (?)', $uri_blank)->get($this->s_db);
                if ($db_val) {
                    $uri_blank_ext = parent::array_column($db_val, 'tree_id', 'uri');
                }
            }
        }
        return $uri_blank_ext;
    }

    /**
     * 通过uri获取菜单数组
     *
     * @param array $uris        
     * @return array
     */
    function getTreeIdsByUris($uris)
    {
        $tree_ids = array();
        
        $db_val = Sql::select('tree_id')->from($this->table)->where('uri in (?)', $uris)->get($this->s_db);
        if ($db_val) {
            $tree_ids = parent::array_column($db_val, 'uri', 'tree_id');
        }
        
        return $tree_ids;
    }

    /**
     * 获取用户/全部菜单数组
     *
     * @return array
     */
    function getMenuTreesByAdminUser($pri = null)
    {
        $db_val = Sql::select('*')->from($this->table)->orderBy('parent_tree_id')->orderBy('sort')->get($this->s_db);
        $trees = $this->_getTreeByAdminUser($db_val, 0, $pri);
        return $trees;
    }

    /**
     *
     * @param array $trees
     *        菜单数组
     * @param int $parent_id        
     * @return array
     */
    private function _getTreeByAdminUser($tree_ids, $parent_id = 0, $pri = null)
    {
        $tree = array('menu' => array(), 'hidden' => array());
        
        foreach ($tree_ids as $v) {
            if ($v['parent_tree_id'] == $parent_id) {
                if (!$pri || isset($pri[$v['uri']])) {
                    $tmp = array(
                        'tree_id'   =>  $v['tree_id'],
                        'uri'       =>  $v['uri'],
                        'icon'      =>  $v['icon'],
//                         'desc'      =>  $v['desc'],
                    );
                    if (!$v['hidden']) {
                        $sub = $this->_getTreeByAdminUser($tree_ids, $v['tree_id']);
                        if ($sub['menu']) {
                            $tmp['sub'] = $sub['menu'];
                        }
                        $tree['menu'][] = $tmp;
                        $tree['hidden'] = array_merge($tree['hidden'], $sub['hidden']);
                    }
                    else {
                        $tree['hidden'][] = $tmp;
                    }
                }
                else {
                    $sub = $this->_getTreeByAdminUser($tree_ids, $v['tree_id'], $pri);
                    if ($sub['menu'] || $sub['hidden']) {
                        $tmp = array(
                            'tree_id'   =>  $v['tree_id'],
                            'uri'       =>  $v['uri'],
                            'icon'      =>  $v['icon'],
//                             'desc'      =>  $v['desc'],
                        );
                        if (!$v['hidden']) {
                            $sub = $this->_getTreeByAdminUser($tree_ids, $v['tree_id'], $pri);
                            if ($sub['menu']) {
                                $tmp['sub'] = $sub['menu'];
                            }
                            $tree['menu'][] = $tmp;
                            $tree['hidden'] = array_merge($tree['hidden'], $sub['hidden']);
                        }
                        else {
                            $tree['hidden'][] = $tmp;
                        }
                    }
                }
            }
        }
        return $tree;
    }

    function getTrees()
    {
        $db_val = Sql::select('tree_id as id,parent_tree_id as pId,uri')->from($this->table)->orderBy("sort", Sql::$ORDER_BY_ASC)->get($this->s_db);
        return $db_val;
    
    }
}

