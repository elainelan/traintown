<?php
/**
 * 数据库表处理基础
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 数据库表处理基础类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class CDbTableBase
{

    /**
     * 数据库表名称
     *
     * @var string
     */
    protected $table;

    /**
     * 数据库连接实例指针
     *
     * @var object
     */
    protected $s_db;
    
    /**
     * DB连接类型，默认pdo
     * 
     * @var string pdo/http
     */
    protected $s_db_default = 'pdo';

    /**
     * 内部实现：获取数据（设缓存）
     *
     * @param string $cache_key_sub_string
     *        缓存key组合方式：数据库表名_$cache_key_sub_string
     * @param array $where_args
     *        查询条件数组
     * @param string $idx_key
     *        哪个数据库表字段名做为返回数组索引
     * @param int $force
     *        是否强制从数据库中获取数据
     * @return array array('idx'=>array()) || array( array(), array() )
     */
    private function _getWithCache($cache_key_sub_string, $where_args, $idx_key, $force)
    {
        $cache_key = $this->table . '_' . $cache_key_sub_string;
        
        if (!$force) {
            $cache_val = CDbuser::getInstanceMemcache()->get($cache_key);
            if ($cache_val) {
                return $cache_val;
            }
        }
        
        $db_sql = Sql::select("*")->from($this->table);
        if (!empty($where_args)) {
            $db_sql = $db_sql->whereArgs($where_args);
        }
        
        $db_val = $db_sql->get($this->s_db, $idx_key);
        if ($db_val) {
            CDbuser::getInstanceMemcache()->set($cache_key, $db_val, 0);
        }
        
        return $db_val;
    }

    /**
     * 获取数据（设缓存）
     *
     * @param string $cache_key_sub_string
     *        缓存key组合方式：数据库表名_$cache_key_sub_string
     * @param array $where_args
     *        查询条件数组
     * @param string $idx_key
     *        哪个数据库表字段名做为返回数组索引
     * @return array array('idx'=>array()) || array( array(), array() )
     */
    public function getWithCache($cache_key_sub_string, $where_args = array(), $idx_key = null)
    {
        return $this->_getWithCache($cache_key_sub_string, $where_args, $idx_key, 0);
    }

    /**
     * 强制忽略缓存，重新获取数据（设缓存）
     *
     * @param string $cache_key_sub_string
     *        缓存key组合方式：数据库表名_$cache_key_sub_string
     * @param array $where_args
     *        查询条件数组
     * @param string $idx_key
     *        哪个数据库表字段名做为返回数组索引
     * @return array array('idx'=>array()) || array( array(), array() )
     */
    public function getWithCacheForce($cache_key_sub_string, $where_args = array(), $idx_key = null)
    {
        return $this->_getWithCache($cache_key_sub_string, $where_args, $idx_key, 1);
    }

    /**
     * 删除数据（删缓存）
     *
     * @param string $cache_key_sub_string
     *        缓存key组合方式：数据库表名_$cache_key_sub_string
     * @param array $where_args
     *        删除条件数组
     * @return SqlResponse SqlResponse实例，参考@see SqlResponse
     */
    public function delWithCache($cache_key_sub_string, $where_args)
    {
        $cache_key = $this->table . '_' . $cache_key_sub_string;
        $db_res = Sql::deleteFrom($this->table)->whereArgs($where_args)->exec($this->s_db);
        if ($db_res) {
            CDbuser::getInstanceMemcache()->delete($cache_key);
            
            $cache_key_null = $this->table . '_';
            CDbuser::getInstanceMemcache()->delete($cache_key_null);
        }
        return $db_res;
    }

    /**
     * 删除数据，返回删除的记录数（删缓存）
     *
     * @param unknown $cache_key_sub_string
     *        缓存key组合方式：数据库表名_$cache_key_sub_string
     * @param unknown $where_args
     *        删除条件数组
     * @return int 删除的记录数
     */
    public function delWithCacheRows($cache_key_sub_string, $where_args)
    {
        return $this->delWithCache($cache_key_sub_string, $where_args)->rows;
    }

    /**
     * 更新数据（删缓存）
     *
     * @param string $cache_key_sub_string
     *        缓存key组合方式：数据库表名_$cache_key_sub_string
     * @param array $update_args
     *        更新数据数组
     * @param array $where_args
     *        更新条件数组
     * @return SqlResponse SqlResponse实例，参考@see SqlResponse
     */
    public function updateWithCache($cache_key_sub_string, $update_args, $where_args)
    {
        $cache_key = $this->table . '_' . $cache_key_sub_string;
        $db_res = Sql::update($this->table)->setArgs($update_args)->whereArgs($where_args)->exec($this->s_db);
        if ($db_res) {
            CDbuser::getInstanceMemcache()->delete($cache_key);
            
            $cache_key_null = $this->table . '_';
            CDbuser::getInstanceMemcache()->delete($cache_key_null);
        }
        return $db_res;
    }

    /**
     * 更新数据，返回更新的记录数 （删缓存）
     *
     * @param string $cache_key_sub_string
     *        缓存key组合方式：数据库表名_$cache_key_sub_string
     * @param array $update_args
     *        更新数据数组
     * @param array $where_args
     *        更新条件数组
     * @return SqlResponse SqlResponse实例，参考@see SqlResponse
     */
    public function updateWithCacheRows($cache_key_sub_string, $update_args, $where_args)
    {
        return $this->updateWithCache($cache_key_sub_string, $update_args, $where_args)->rows;
    }

    /**
     * 插入数据（删缓存）
     *
     * @param string $cache_key_sub_string
     *        缓存key组合方式：数据库表名_$cache_key_sub_string
     * @param array $insert_values
     *        插入数据数组
     * @param string $insert_attr
     *        插入特殊处理属性，Sql::$INSERT_IGNORE等，参考@see Sql
     * @return SqlResponse SqlResponse实例，参考@see SqlResponse
     */
    public function insertWithCache($cache_key_sub_string, $insert_values, $insert_attr = null)
    {
        $cache_key = $this->table . '_' . $cache_key_sub_string;
        $db_res = Sql::insertInto($this->table, $insert_attr)->values($insert_values)->exec($this->s_db);
        if ($db_res) {
            CDbuser::getInstanceMemcache()->delete($cache_key);
            
            $cache_key_null = $this->table . '_';
            CDbuser::getInstanceMemcache()->delete($cache_key_null);
        }
        return $db_res;
    }

    /**
     * 插入数据，返回插入记录的自增ID（删缓存）
     *
     * @param string $cache_key_sub_string
     *        缓存key组合方式：数据库表名_$cache_key_sub_string
     * @param array $insert_values
     *        插入数据数组
     * @param string $insert_attr
     *        插入特殊处理属性，Sql::$INSERT_IGNORE等，参考@see Sql
     * @return int 最后一次成功插入id
     */
    public function insertWithCacheID($cache_key_sub_string, $insert_values, $insert_attr = null)
    {
        return $this->insertWithCache($cache_key_sub_string, $insert_values, $insert_attr)->lastInsertId();
    }

    /**
     * 直接插入数据到数据库（无缓存）
     *
     * @param SqlExecRule $sql
     *        sql语句
     * @return SqlResponse SqlResponse实例，参考@see SqlResponse
     */
    public function insertToDb(SqlExecRule $sql)
    {
        return $sql->exec($this->s_db);
    }

    /**
     * 直接操作数据库插入，返回插入自增ID（无缓存）
     *
     * @param SqlExecRule $sql
     *        sql语句
     * @return 返回最后一次插入的id
     */
    public function insertToDbID(SqlExecRule $sql)
    {
        return $this->insertToDb($sql)->lastInsertId();
    }

    /**
     * 直接操作数据库更新（无缓存）
     *
     * @param SqlExecRule $sql
     *        sql语句
     * @return SqlResponse SqlResponse实例，参考@see SqlResponse
     */
    public function updateToDb(SqlExecRule $sql)
    {
        return $sql->exec($this->s_db);
    }

    /**
     * 直接操作数据库更新（无缓存）
     *
     * @param SqlExecRule $sql
     *        sql语句
     * @return int 更新数据条数
     */
    public function updateToDbRows(SqlExecRule $sql)
    {
        return $this->updateToDb($sql)->rows;
    }

    /**
     * 直接操作数据库删除（无缓存）
     *
     * @param SqlExecRule $sql
     *        sql语句
     * @return SqlResponse SqlResponse实例，参考@see SqlResponse
     */
    public function delToDb(SqlExecRule $sql)
    {
        return $sql->exec($this->s_db);
    }

    /**
     * 直接操作数据库删除，返回删除记录数（无缓存）
     *
     * @param SqlExecRule $sql
     *        sql语句
     * @return int 删除记录数
     */
    public function delToDbRows(SqlExecRule $sql)
    {
        return $this->delToDb($sql)->rows;
    }

    /**
     * 获取数据列数据
     *
     * @param array $array
     *        原始数据
     * @param string $column_key
     *        数据字段key
     * @param string $index_key
     *        数据索引key
     */
    public function array_column($array, $column_key, $index_key = null)
    {
        if (!function_exists('array_column')) {
            $return_array = array();
            if ($array) {
                if ($index_key) {
                    foreach ($array as &$v) {
                        $return_array[$v[$index_key]] = $v[$column_key];
                    }
                }
                else {
                    foreach ($array as &$v) {
                        $return_array[] = $v[$column_key];
                    }
                }
            }
            return $return_array;
        }
        return array_column($array, $column_key, $index_key);
    }

    /**
     * （通用）从数据库查询数据，不带缓存
     *
     * @param array $where_args
     *        查询条件
     * @param array $order_args
     *        排序条件
     * @param int $page
     *        页数
     * @param int $num
     *        每页显示
     */
    public function getByCondFromDb($select_strings = '*', $where_args, $page = 1, $num = 10, $orderby_args = array())
    {
        $total = Sql::select('count(1) as sum')->from($this->table);
        $data = Sql::select($select_strings)->from($this->table);
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

    /**
     * （通用）从数据库删除数据，不带缓存
     *
     * @param array $where_args
     *        删除条件
     */
    public function delByCondFromDb($where_args)
    {
        $sql = Sql::deleteFrom($this->table)->whereArgs($where_args);
        return $this->delToDb($sql);
    }

    /**
     * （通用）从数据库新增数据，不带缓存
     *
     * @param array $insert_values
     *        插入数据
     */
    public function insertByCondFromDbID($insert_values)
    {
        $sql = Sql::insertInto($this->table)->values($insert_values);
        return $this->insertToDbID($sql);
    }

    /**
     * （通用）从数据库更新数据，不带缓存
     *
     * @param array $update_args
     *        更新数组
     * @param array $where_args
     *        更新条件
     *        删除条件
     */
    public function updateByCondFromDb($update_args, $where_args)
    {
        $sql = Sql::update($this->table)->setArgs($update_args)->whereArgs($where_args);
        return $this->updateToDb($sql);
    }

    /**
     * （通用）从数据库验证数据，不带缓存
     *
     * @param array $where_args
     *        验证条件
     *        删除条件
     * @return true 存在， false 不存在
     */
    public function validByCondFromDb($select_strings, $where_args)
    {
        $sql = Sql::select($select_strings)->from($this->table)->whereArgs($where_args)->limit(0, 1);
        return (bool)$sql->get($this->s_db);
    }
}