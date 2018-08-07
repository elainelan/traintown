<?php

/**
 * SQL输入输出类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 */

/**
 * SQL处理结果返回
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlResponse
{

    /**
     *
     * @var bool true on success or false on failure.
     */
    public $success;

    /**
     *
     * @var int the number of rows.
     */
    public $rows;

    /**
     *
     * @var PDO pdo实例化对象
     */
    public $pdo;

    /**
     *
     * @var PDOStatement PDOStatement对象
     */
    public $st;

    /**
     * 初始化
     *
     * @param boolean $success        
     * @param PDO $pdo        
     * @param PDOStatement $st        
     */
    public function __construct($success, $pdo, $st)
    {
        $this->pdo = $pdo;
        $this->st = $st;
        $this->success = $success;
        $this->rows = $this->st->rowCount();
    }

    /**
     * 获取最后成功插入的id
     *
     * @param string $name        
     */
    public function lastInsertId($name = null)
    {
        if ($this->last_insert_id) {
            return $this->last_insert_id;
        }
        return $this->pdo->lastInsertId($name);
    }

    /**
     * 序列化时执行操作
     */
    public function __sleep()
    {
        if (stripos($this->st->queryString, 'insert') === 0) {
            $this->last_insert_id = $this->lastInsertId();
            return array('success', 'rows', 'last_insert_id');
        }
        return array('success', 'rows');
    }

}

/**
 * SqlSelectImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlSelectImpl
{

    /**
     * SELECT列
     *
     * @param SqlConetxt $context        
     * @param string $columns        
     */
    static public function select($context, $columns)
    {
        $context->appendSql("SELECT $columns");
    }
}

/**
 * SqlFromImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlFromImpl
{

    /**
     * SELECT-FROM
     *
     * @param SqlConetxt $context        
     * @param SqlBasicRule $tables        
     * @param string $as        
     */
    static public function from($context, $tables, $as = null)
    {
        if ($tables instanceof SqlBasicRule) {
            $context->appendSql("FROM (" . $tables->context->sql . ')');
            $context->params = array_merge($context->params, $tables->context->params);
        }
        else {
            $context->appendSql("FROM $tables");
        }
        if ($as) {
            $context->appendSql("as $as");
        }
    }
}

/**
 * SqlDeleteImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlDeleteImpl
{

    /**
     * DELETE FROM
     *
     * @param SqlConetxt $context        
     * @param string $from        
     */
    static public function deleteFrom($context, $from)
    {
        $context->appendSql("DELETE FROM $from");
    }
}

/**
 * SqlJoinImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlJoinImpl
{

    /**
     * XX JOIN
     *
     * @param SqlConetxt $context        
     * @param string $type        
     * @param string $table        
     */
    static public function join($context, $type, $table)
    {
        if ($type) {
            $context->appendSql("$type JOIN $table");
        }
        else {
            $context->appendSql("JOIN $table");
        }
    }
}

/**
 * SqlJoinOnImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlJoinOnImpl
{

    /**
     * JOIN ON
     *
     * @param SqlConetxt $context        
     * @param string $condition        
     */
    static public function on($context, $condition)
    {
        $context->appendSql("ON $condition");
    }
}

/**
 * SqlForUpdateImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlForUpdateImpl
{

    /**
     * FOR UPDATE
     *
     * @param SqlConetxt $context        
     */
    static public function forUpdate($context)
    {
        $context->appendSql("FOR UPDATE");
    }
}

/**
 * SqlForUpdateOfImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlForUpdateOfImpl
{

    /**
     * OF
     *
     * @param SqlConetxt $context        
     * @param string $column        
     */
    static public function of($context, $column)
    {
        $context->appendSql("OF $column");
    }
}

/**
 * SqlInsertImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlInsertImpl
{

    /**
     * INSERT INTO
     *
     * @param SqlConetxt $context        
     * @param string $table        
     * @param string $insertAttr        
     */
    static public function insertInto($context, $table, $insertAttr)
    {
        $context->appendSql("INSERT $insertAttr INTO $table");
    }
}

/**
 * SqlReplaceImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlReplaceImpl
{

    /**
     * REPLACE INTO
     *
     * @param SqlConetxt $context        
     * @param string $table        
     */
    static public function replaceInto($context, $table)
    {
        $context->appendSql("REPLACE INTO $table");
    }
}

/**
 * SqlValuesImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlValuesImpl
{

    /**
     * values sql
     *
     * @var string
     */
    private $sql = null;

    /**
     * VALUES
     *
     * @param SqlConetxt $context        
     * @param array $values        
     */
    static public function values($context, $values)
    {
        $params = array();
        $stubs = array();
        foreach ($values as $v) {
            if (is_a($v, 'SqlNative')) { //直接拼接sql，不需要转义
                $stubs[] = $v->get();
            }
            else {
                $stubs[] = '?';
                $params[] = $v;
            }
        }
        $stubs = implode(',', $stubs);
        
        if (array_keys($values) === range(0, count($values) - 1)) {
            //VALUES(val0, val1, val2)
            $context->appendSql("VALUES($stubs)");
        
        }
        else {
            //(col0, col1, col2) VALUES(val0, val1, val2)
            $columns = implode(',', array_keys($values));
            $context->appendSql("($columns) VALUES($stubs)", false);
        }
        $context->appendParams($params);
    }

    /**
     * VALUES (),(),()
     *
     * @param SqlConetxt $context        
     * @param array $valuesMulti        
     */
    static function valuesMulti($context, $valuesMulti)
    {
        $params = array();
        $stubs_all = array();
        foreach ($valuesMulti as $values) {
            $stubs = array();
            foreach ($values as $v) {
                if (is_a($v, 'SqlNative')) { // 直接拼接sql，不需要转义
                    $stubs[] = $v->get();
                }
                else {
                    $stubs[] = '?';
                    $params[] = $v;
                }
            }
            $stubs = implode(',', $stubs);
            $stubs_all[] = "($stubs)";
        }
        
        $stubs_all = implode(',', $stubs_all);
        if (array_keys($values) === range(0, count($values) - 1)) {
            //VALUES(val0, val1, val2),(val0, val1, val2)
            $context->appendSql("VALUES{$stubs_all}");
        }
        else {
            //(col0, col1, col2) VALUES(val0, val1, val2),(val0, val1, val2)
            $columns = implode(',', array_keys($values));
            $context->appendSql("($columns) VALUES{$stubs_all}", false);
        }
        $context->appendParams($params);
    }
}

/**
 * SqlUpdateImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlUpdateImpl
{

    /**
     * UPDATE
     *
     * @param SqlConetxt $context        
     * @param string $table        
     */
    static public function update($context, $table)
    {
        $context->appendSql("UPDATE $table");
    }
}

/**
 * SqlUpdateSetImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlUpdateSetImpl
{

    /**
     * 第一次处理
     *
     * @var boolean
     */
    private $first = true;

    /**
     * UPDATE SET
     *
     * @param SqlConetxt $context        
     * @param string $column        
     * @param string|SqlNative $value        
     */
    public function set($context, $column, $value)
    {
        $prefix = '';
        if ($this->first) {
            $this->first = false;
            $prefix = 'SET ';
        }
        else {
            $prefix = ',';
        }
        if (is_a($value, 'SqlNative')) {
            $context->appendSql("$prefix$column=$value", $prefix == 'SET ');
        }
        else {
            $context->appendSql("$prefix$column=?", $prefix == 'SET ');
            $context->appendParams(array($value));
        }
    }

    /**
     * UPDATE SET BY expr
     *
     * @param SqlConetxt $context        
     * @param string $expr        
     * @param array $args        
     */
    public function setExpr($context, $expr, $args)
    {
        $prefix = '';
        if ($this->first) {
            $this->first = false;
            $prefix = 'SET ';
        }
        else {
            $prefix = ',';
        }
        
        $context->appendSql("$prefix$expr", $prefix == 'SET ');
        $context->appendParams($args);
    
    }

    /**
     * UPDATE SET
     *
     * @param SqlConetxt $context        
     * @param array $values        
     */
    public function setArgs($context, $values)
    {
        $set = array();
        $params = array();
        foreach ($values as $k => $v) {
            if (is_a($v, 'SqlNative')) { //直接拼接sql，不需要转义
                $set[] = "$k=" . $v->get();
            }
            else {
                $set[] = "$k=?";
                $params[] = $v;
            }
        }
        if ($this->first) {
            $this->first = false;
            $context->appendSql('SET ' . implode(',', $set));
            $context->appendParams($params);
        }
        else {
            $context->appendSql(',' . implode(',', $set), false);
            $context->appendParams($params);
        }
    }

}

/**
 * SqlOrderByImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlOrderByImpl
{

    /**
     * 第一次处理
     *
     * @var boolean
     */
    private $first = true;

    /**
     * ORDER BY args
     *
     * @param SqlConetxt $context        
     * @param array $orders        
     * @return SqlOrderByImpl
     */
    public function orderByArgs($context, $orders)
    {
        if (empty($orders)) {
            return $this;
        }
        $params = array();
        foreach ($orders as $k => $v) {
            if (is_integer($k)) {
                SqlVerify::isTrue(preg_match('/^[a-zA-Z0-9_.]+$/', $v), "invalid params for orderBy(" . json_encode($orders) . ")");
                
                $params[] = $v;
            }
            else {
                $v = strtoupper($v);
                SqlVerify::isTrue(preg_match('/^[a-zA-Z0-9_.]+$/', $k) && ($v == 'DESC' || $v == 'ASC'), "invalid params for orderBy(" . json_encode($orders) . ")");
                
                $params[] = "$k $v";
            }
        }
        if ($this->first) {
            $this->first = false;
            $context->appendSql('ORDER BY ' . implode(',', $params));
        }
        else {
            $context->appendSql(',' . implode(',', $params), false);
        }
        return $this;
    }

    /**
     * ORDER BY
     *
     * @param SqlConetxt $context        
     * @param string $column        
     * @param string $order        
     * @return SqlOrderByImpl
     */
    public function orderBy($context, $column, $order = null)
    {
        if ($this->first) {
            $this->first = false;
            $context->appendSql("ORDER BY $column");
        }
        else {
            $context->appendSql(",$column", false);
        }
        if ($order) {
            $context->appendSql($order);
        }
        return $this;
    }

}

/**
 * SqlLimitImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlLimitImpl
{

    /**
     * LIMIT
     *
     * @param SqlConetxt $context        
     * @param int $size        
     */
    static public function limit($context, $size)
    {
        $intSize = intval($size);
        SqlVerify::isTrue(strval($intSize) == $size, "invalid params for limit($size)");
        $context->appendSql("LIMIT $size");
    }

    /**
     * LIMIT x,x
     *
     * @param SqlConetxt $context        
     * @param int $start        
     * @param int $size        
     */
    static public function limitWithOffset($context, $start, $size)
    {
        $intStart = intval($start);
        $intSize = intval($size);
        SqlVerify::isTrue(strval($intStart) == $start && strval($intSize) == $size, "invalid params for limit($start, $size)");
        $context->appendSql("LIMIT $start,$size");
    }
}

/**
 * SqlWhereImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlWhereImpl
{

    /**
     * FINDQ
     *
     * @param string $str        
     * @param number $offset        
     * @param number $no        
     */
    static private function findQ($str, $offset = 0, $no = 0)
    {
        $found = strpos($str, '?', $offset);
        if ($no == 0 || $found === false) {
            return $found;
        }
        return self::findQ($str, $found + 1, $no - 1);
    }

    /**
     * HAVING
     *
     * @param SqlConetxt $context        
     * @param string $expr        
     * @param array $args        
     */
    static public function having($context, $expr, $args)
    {
        self::condition($context, 'HAVING', $expr, $args);
    }

    /**
     * WHERE
     *
     * @param SqlConetxt $context        
     * @param string $expr        
     * @param array $args        
     */
    static public function where($context, $expr, $args)
    {
        self::condition($context, 'WHERE', $expr, $args);
    }

    /**
     * HAVING args
     *
     * @param SqlConetxt $context        
     * @param array $args        
     */
    static public function havingArgs($context, $args)
    {
        self::conditionArgs($context, 'HAVING', $args);
    }

    /**
     * WHERE args
     *
     * @param SqlConetxt $context        
     * @param array $args        
     */
    static public function whereArgs($context, $args)
    {
        self::conditionArgs($context, 'WHERE', $args);
    }

    /**
     * find like Mongodb query glossary
     * whereArray(
     * [
     * 'id'=>['>'=>1],
     * 'name'=>'cym',
     * ]
     * )
     * 支持的操作符有
     * = 'id'=>['=' => 1]
     * > 'id'=>['>' => 1]
     * < 'id'=>['<' => 1]
     * <> 'id'=>['<>' => 1]
     * >= 'id'=>['>=' => 1]
     * <= 'id'=>['<=' => 1]
     * BETWEEN 'id'=>['BETWEEN' => [1 ,2]]
     * LIKE 'id'=>['LIKE' => '1%']
     * IN 'id'=>['IN' => [1,2,3]]
     * NOT IN 'id'=>['NOT IN' => [1,2,3]]
     *
     *
     * @param SqlConetxt $context        
     * @param string $prefix        
     * @param array $args        
     */
    static public function conditionArgs($context, $prefix, $args = array())
    {
        if ($args === null) {
            return;
        }
        $exprs = array();
        $params = array();
        foreach ($args as $k => $v) {
            if (is_array($v)) {
                $ops = array('=', '>', '<', '<>', '>=', '<=', 'IN', 'NOT IN', 'BETWEEN', 'LIKE');
                $op = array_keys($v);
                $op = strtoupper($op[0]);
                
                SqlVerify::isTrue(false !== array_search($op, $ops), "invalid param $op for whereArgs");
                
                $var = array_values($v);
                $var = $var[0];
                if ($op == 'IN' || $op == 'NOT IN') {
                    $stubs = array();
                    foreach ($var as $i) {
                        if (is_a($i, 'SqlNative')) {
                            $stubs[] = strval($i);
                        }
                        else {
                            $stubs[] = '?';
                            $params[] = $i;
                        }
                    }
                    $stubs = implode(',', $stubs);
                    $exprs[] = "$k $op ($stubs)";
                }
                else if ($op == 'BETWEEN') {
                    $cond = "$k BETWEEN";
                    if (is_a($var[0], 'SqlNative')) {
                        $cond = "$cond " . strval($var[0]);
                    }
                    else {
                        $cond = "$cond ?";
                        $params[] = $var[0];
                    }
                    if (is_a($var[1], 'SqlNative')) {
                        $cond = "$cond AND " . strval($var[1]);
                    }
                    else {
                        $cond = "$cond AND ?";
                        $params[] = $var[1];
                    }
                    $exprs[] = $cond;
                }
                else {
                    if (is_a($var, 'SqlNative')) {
                        $exprs[] = "$k $op " . strval($var);
                    }
                    else {
                        $exprs[] = "$k $op ?";
                        $params[] = $var;
                    }
                }
            }
            else {
                if (is_a($v, 'SqlNative')) {
                    $exprs[] = "$k = " . strval($v);
                
                }
                else {
                    $exprs[] = "$k = ?";
                    $params[] = $v;
                }
            }
        }
        
        return self::condition($context, $prefix, implode(' AND ', $exprs), $params);
    }

    /**
     * condition
     *
     * @param SqlConetxt $context        
     * @param string $prefix        
     * @param string $expr        
     * @param array $args        
     */
    static public function condition($context, $prefix, $expr, $args)
    {
        if (!empty($expr)) {
            if ($args) {
                //因为PDO不支持绑定数组变量, 这里需要手动展开数组
                //也就是说把 where("id IN(?)", [1,2])  展开成 where("id IN(?,?)", 1,2)
                $cutted = null;
                $cut = null;
                $toReplace = array();
                
                $newArgs = array();
                //找到所有数组对应的?符位置
                foreach ($args as $k => $arg) {
                    if (is_array($arg) || is_a($arg, 'SqlNative')) {
                        if (!$cutted) {
                            $cut = new SqlNestedStringCut($expr);
                            $cutted = $cut->getText();
                        }
                        //找到第$k个?符
                        $pos = self::findQ($cutted, 0, $k);
                        $pos = $cut->mapPos($pos);
                        SqlVerify::isTrue($pos !== false, "unmatched params and ? @ base64: " . base64_encode(json_encode($expr)));
                        
                        if (is_array($arg)) {
                            $stubs = array();
                            foreach ($arg as $i) {
                                if (is_a($i, 'SqlNative')) {
                                    $stubs[] = strval($i);
                                }
                                else {
                                    $stubs[] = '?';
                                    $newArgs[] = $i;
                                }
                            }
                            $stubs = implode(',', $stubs);
                        }
                        else {
                            $stubs = strval($arg);
                        }
                        $toReplace[] = array($pos, $stubs);
                    
                    }
                    else {
                        $newArgs[] = $arg;
                    }
                }
                
                if (count($toReplace)) {
                    $toReplace = array_reverse($toReplace);
                    foreach ($toReplace as $i) {
                        list($pos, $v) = $i;
                        $expr = substr($expr, 0, $pos) . $v . substr($expr, $pos + 1);
                    }
                    $args = $newArgs;
                }
            }
            $context->appendSql($prefix . ' ' . $expr);
            if ($args) {
                $context->appendParams($args);
            }
        }
    }
}

/**
 * SqlGroupByImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlGroupByImpl
{

    /**
     * GROUP BY
     *
     * @param SqlConetxt $context        
     * @param string $column        
     */
    static public function groupBy($context, $column)
    {
        $context->appendSql("GROUP BY $column");
    }
}

/**
 * SqlExecImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlExecImpl
{

    /**
     * 执行SQL处理
     *
     * @param SqlConetxt $context
     *        SqlConetxt上下文
     * @param PDO/Object $db
     *        POD类或者其他数据获取方式类
     * @param boolean $errExce
     *        是否抛出错误异常
     * @return SqlResponse
     */
    static public function exec($context, $db, $errExce = true)
    {
        if (defined('LOG_SQL_EXEC') && LOG_SQL_EXEC) {
            CVLog::addlog('SQL_exec', $context);
        }
        if ($db instanceof PDO) {
            // 直接连接数据库（PDO）
            if ($errExce) {
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            $db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
            $st = $db->prepare($context->sql);
            $success = $st->execute($context->params);
            return new SqlResponse($success, $db, $st);
        }
        else if ($db instanceof SqlExecImplAbstract) {
            // 其他方式连接数据库
            return $db->sqlExecImplExec($context, $errExce);
        }
        else {
            // $db错误
            throw new Exception("SqlExecImpl db type not support!");
        }
    }

    /**
     * 获取数据
     *
     * @param SqlConetxt $context
     *        SqlConetxt上下文
     * @param PDO/Object $db
     *        POD类或者其他数据获取方式类
     * @param string $asDict
     *        排序字段，如果有返回按此字段排序数组，否则返回数组
     * @param boolean $errExce
     *        是否抛出错误异常
     * @return false|array
     */
    static public function get($context, $db, $dictAs = null, $errExce = true)
    {
        if (defined('LOG_SQL_GET') && LOG_SQL_GET) {
            CVLog::addlog('SQL_get', $context);
        }
        if ($db instanceof PDO) {
            // 直接连接数据库（PDO）
            if ($errExce) {
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            $db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
            $st = $db->prepare($context->sql);
            if ($st->execute($context->params)) {
                $res = $st->fetchAll(PDO::FETCH_ASSOC);
                if ($dictAs) {
                    $dict = array();
                    foreach ($res as & $i) {
                        $dict[$i[$dictAs]] = $i;
                    }
                    return $dict;
                }
                return $res;
            }
            else {
                return false;
            }
        }
        else if ($db instanceof SqlExecImplAbstract) {
            // 其他方式连接数据库
            return $db->sqlExecImplGet($context, $dictAs, $errExce);
        }
        else {
            // $db错误
            throw new Exception("SqlExecImpl db type not support!");
        }
    
    }

    /**
     * 导出数据
     *
     * @param SqlConetxt $context
     *        SqlContext上下文
     * @param PDO $db
     *        POD类或者其他数据获取方式类
     * @param string $asDict
     *        排序字段，如果有返回按此字段排序数组，否则返回数组
     * @param boolean $errExce
     *        是否抛出错误异常
     * @return false|array
     */
    static public function export($context, $db, $file, $errExce = true)
    {
        if (defined('LOG_SQL_GET') && LOG_SQL_GET) {
            CVLog::addlog('SQL_get', $context);
        }
        if ($db instanceof PDO) {
            // 直接连接数据库（PDO）
            if ($errExce) {
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            $db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
            $st = $db->prepare($context->sql);
            if ($st->execute($context->params)) {
                
                // 打开CSV导出文件
                $f_res = CVFile::getInstance($file);
                
                $title = false;
                // 添加BOM，避免乱码的问题出现
                $f_res->append(chr(0xEF) . chr(0xBB) . chr(0xBF));
                
                while (($res = $st->fetch(PDO::FETCH_ASSOC)) != false) {
                    if (!$title) {
                        $title = array_keys($res);
                        foreach ($title as &$v) {
                            $v = '"' . str_replace('"', '""', $v) . '"';
                        }
                        
                        $content = implode(",", $title) . "\r\n";
                        // 数据写入
                        if ($f_res->append($content) === false) {
                            fclose($f_res);
                            ResultParser::error(ErrorCode::EXPORT_FILE_WRITE_FAILED);
                        }
                        $title = true;
                    }
                    
                    foreach ($res as &$v) {
                        $v = '"' . str_replace('"', '""', $v) . '"';
                    }
                    $content = implode(",", $res) . "\r\n";
                    // 数据写入
                    if ($f_res->append($content) === false) {
                        $f_res->closed();
                        ResultParser::error(ErrorCode::EXPORT_FILE_WRITE_FAILED);
                    }
                }
                
                // 关闭文件
                $f_res->closed();
                
                return substr($file, strlen(CV_ROOT));
            
            }
            else {
                return false;
            }
        }
        else if ($db instanceof SqlExecImplAbstract) {
            // 其他方式连接数据库
            return $db->sqlExecImplExport($content, $file, $errExce);
        }
        else {
            // $db错误
            throw new Exception("SqlExecImpl db type not support!");
        }
    }
}

/**
 * SqlOnDuplicateKeyUpdateImpl输入类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/impls
 *            
 */
class SqlOnDuplicateKeyUpdateImpl
{

    /**
     * 是否第一次
     *
     * @var boolean
     */
    private $first = true;

    /**
     * SET
     *
     * @param SqlConetxt $context        
     * @param string $column        
     * @param string $value        
     */
    public function set($context, $column, $value)
    {
        $prefix = '';
        if ($this->first) {
            $this->first = false;
            $prefix = 'ON DUPLICATE KEY UPDATE ';
        }
        else {
            $prefix = ',';
        }
        if (is_a($value, 'SqlNative')) {
            $context->appendSql("$prefix$column=$value", $prefix == 'ON DUPLICATE KEY UPDATE ');
        }
        else {
            $context->appendSql("$prefix$column=?", $prefix == 'ON DUPLICATE KEY UPDATE ');
            $context->appendParams(array($value));
        }
    }

    /**
     * SET
     *
     * @param SqlConetxt $context        
     * @param string $expr        
     * @param array $args        
     */
    public function setExpr($context, $expr, $args)
    {
        $prefix = '';
        if ($this->first) {
            $this->first = false;
            $prefix = 'ON DUPLICATE KEY UPDATE ';
        }
        else {
            $prefix = ',';
        }
        
        $context->appendSql("$prefix$expr", $prefix == 'ON DUPLICATE KEY UPDATE ');
        $context->appendParams($args);
    
    }

    /**
     * SET args
     *
     * @param SqlConetxt $context        
     * @param array $values        
     */
    public function setArgs($context, $values)
    {
        $set = array();
        $params = array();
        foreach ($values as $k => $v) {
            if (is_a($v, 'Native')) { //直接拼接sql，不需要转义
                $set[] = "$k=" . $v->get();
            }
            else {
                $set[] = "$k=?";
                $params[] = $v;
            }
        }
        if ($this->first) {
            $this->first = false;
            $context->appendSql('ON DUPLICATE KEY UPDATE ' . implode(',', $set));
            $context->appendParams($params);
        }
        else {
            $context->appendSql(',' . implode(',', $set), false);
            $context->appendParams($params);
        }
    }

}



