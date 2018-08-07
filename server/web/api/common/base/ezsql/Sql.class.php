<?php
/**
 * SQL拼写入口文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

require_once 'impls/impls.php';

require_once 'rules/basic.php';
require_once 'rules/delete.php';
require_once 'rules/replace.php';
require_once 'rules/insert.php';
require_once 'rules/select.php';
require_once 'rules/update.php';

/**
 * Easy SQL
 *
 * How-to-use:
 *
 * $db = new DB($dsn, $username, $passwd);
 * // 1. select
 * $res = Sql::select('a, b')
 * ->from('table')
 * ->leftJoin('table1')->on('table.id=table1.id')
 * ->where('a=?',1)
 * ->groupBy('b')->having('sum(b)=?', 2)
 * ->orderBy('c', Sql::$ORDER_BY_ASC)
 * ->limit(0,1)
 * ->forUpdate()->of('d')
 * ->get($db);
 *
 * // 2. update
 * $rows = Sql::update('table')
 * ->set('a', 1)
 * ->where('b=?', 2)
 * ->orderBy('c', Sql::$ORDER_BY_ASC)
 * ->limit(1)
 * ->exec($db)
 * ->rows
 *
 * // 3. insert
 * $newId = Sql::insertInto('table')
 * ->values(['a'=>1])
 * ->exec($db)
 * ->lastInsertId()
 *
 * //4. delete
 * $rows = Sql::deleteFrom('table')
 * ->where('b=?', 2)
 * ->orderBy('c', Sql::$ORDER_BY_ASC)
 * ->limit(1)
 * ->exec($db)
 * ->rows
 *
 * @author caoym <caoyangmin@gmail.com>
 */

/**
 * SQL拼写类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */
class Sql
{

    /**
     * 升序排列
     *
     * @var string
     */
    public static $ORDER_BY_ASC = 'ASC';

    /**
     * 降序排列
     *
     * @var string
     */
    public static $ORDER_BY_DESC = 'DESC';

    /**
     * 插入属性DELAYED
     *
     * @var string
     */
    public static $INSERT_DELAYED = 'DELAYED';

    /**
     * 插入属性IGNORE
     *
     * @var string
     */
    public static $INSERT_IGNORE = 'IGNORE';

    /**
     * select('column0,column1') => "SELECT column0,column1"
     *
     * select('column0', 'column1') => "SELECT column0,column1"
     *
     * @param $param0 columns
     *        select_key数组
     * @param string|array $_
     *        后续参数
     * @return selectFromRule
     */
    static public function select($param0 = '*', $_ = null)
    {
        $obj = new SelectSqlRule(new SqlConetxt());
        $args = func_get_args();
        if (empty($args)) {
            $args = array('*');
        }
        return $obj->select(implode(',', $args));
    }

    /**
     * insertInto('table') => "INSERT INTO table"
     *
     * insertInto('table', Sql::$INSERT_DELAYED) => "INSERT DELAYED INTO table"
     *
     * insertInto('table', Sql::$INSERT_IGNORE) => "INSERT IGNORE INTO table"
     *
     * @param string $table        
     * @param string $insertAttr        
     * @return InsertValuesRule
     */
    static public function insertInto($table, $insertAttr = null)
    {
        $obj = new InsertSqlRule(new SqlConetxt());
        return $obj->insertInto($table, $insertAttr);
    }

    /**
     * update('table') => "UPDATE table"
     *
     * @param string $table        
     * @return UpdateSetSqlRule
     */
    static public function update($table)
    {
        $obj = new UpdateSqlRule(new SqlConetxt());
        return $obj->update($table);
    }

    /**
     * deleteFrom('table') => "DELETE FROM table"
     *
     * @param string $table        
     * @return SqlWhereRule
     */
    static public function deleteFrom($table)
    {
        $obj = new DeleteSqlRule(new SqlConetxt());
        return $obj->deleteFrom($table);
    }

    /**
     * replaceInto('table') => "REPLACE INTO table"
     *
     * @param string $table        
     * @return ReplaceValuesRule
     */
    static public function replaceInto($table)
    {
        $obj = new ReplaceIntoSqlRule(new SqlConetxt());
        return $obj->replaceInto($table);
    }

    /**
     * Splice sql use native string(without escaping)
     * for example:
     * where('time>?', 'now()') => " WHERE time > 'now()' "
     * where('time>?', Sql::native('now()')) => " WHERE time > now() "
     *
     * @param string $str        
     * @return SqlNative
     */
    static public function native($str)
    {
        return new SqlNative($str);
    }

}
