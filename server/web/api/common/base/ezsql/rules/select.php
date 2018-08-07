<?php

/**
 * SELECT规则文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */

/**
 * SELECT规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectSqlRule extends SqlBasicRule
{

    /**
     * select('column0, column1') => "SELECT column0, column1"
     * select('column0', 'column1') => "SELECT column0, column1"
     *
     * @param string $columns        
     * @return SelectFromRule
     */
    public function select($columns)
    {
        SqlSelectImpl::select($this->context, $columns);
        return new SelectFromRule($this->context);
    }
}

/**
 * SELECT GET规则类，获取数据
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectGetRule extends SqlBasicRule
{

    /**
     * Execute sql and get responses
     *
     * @param PDO $db
     *        PDO数据库指针
     * @param string $asDict
     *        做为数据索引的key值
     * @param boolean $errExce
     *        是否抛异常throw exceptions
     * @return array
     */
    public function get($db, $asDict = false, $errExce = true)
    {
        return SqlExecImpl::get($this->context, $db, $asDict, $errExce);
    }
    
    /**
     * Execute sql and export responses
     *
     * @param PDO $db
     *        PDO数据库指针
     * @param string $asDict
     *        做为数据索引的key值
     * @param boolean $errExce
     *        是否抛异常throw exceptions
     * @return array
     */
    public function export($db, $file, $asDict = false, $errExce = true)
    {
        return SqlExecImpl::export($this->context, $db, $file, $errExce);
    }

    /**
     * Execute sql and get one response
     *
     * @param PDO $db
     *        PDO数据库指针
     * @param bool $errExce
     *        是否抛异常throw exceptions
     * @return mixed
     */
    public function getOnce($db, $errExce = true)
    {
        $get_res = SqlExecImpl::get($this->context, $db, false, $errExce);
        return $get_res[0];
    }

    /**
     * 获取SQL语句
     *
     * @return string
     */
    public function getSql()
    {
        return $this->context->sql;
    }
}

/**
 * SELECT-FROM规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectFromRule extends SelectGetRule
{

    /**
     * from('table') => "FROM table"
     *
     * @param string $table
     *        表名
     * @param string $as
     *        表别名
     * @return SelectJoinRule
     */
    public function from($table, $as = null)
    {
        SqlFromImpl::from($this->context, $table, $as);
        return new SelectJoinRule($this->context);
    }
}

/**
 * SELECT-FORUPDATEOF类规则
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectForUpdateOfRule extends SelectGetRule
{

    /**
     * forUpdate()->of('column') => 'FOR UPDATE OF column'
     *
     * @param string $column        
     * @return SelectGetRule
     */
    public function of($column)
    {
        SqlForUpdateOfImpl::of($this->context, $column);
        return new SelectGetRule($this->context);
    }
}

/**
 * SELECT-FORUPDATE规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectForUpdateRule extends SelectGetRule
{

    /**
     * forUpdate() => 'FOR UPDATE'
     *
     * @return SelectForUpdateOfRule
     */
    public function forUpdate()
    {
        SqlForUpdateImpl::forUpdate($this->context);
        return new SelectForUpdateOfRule($this->context);
    }
}

/**
 * SELECT-LIMIT规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectLimitRule extends SelectForUpdateRule
{

    /**
     * limit(0,1) => "LIMIT 0,1"
     *
     * @param int $start        
     * @param int $size        
     * @return SelectForUpdateRule
     */
    public function limit($start, $size)
    {
        SqlLimitImpl::limitWithOffset($this->context, $start, $size);
        return new SelectForUpdateRule($this->context);
    }
}

/**
 * SELECT-ORDERBY规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectOrderByRule extends SelectLimitRule
{

    /**
     * 输入类
     *
     * @var OrderByImpl
     */
    private $order;

    /**
     * 初始化
     *
     * @param SqlConetxt $context        
     */
    public function __construct($context)
    {
        parent::__construct($context);
        $this->order = new SqlOrderByImpl();
    }

    /**
     * orderBy('column') => "ORDER BY column"
     * orderBy('column', Sql::$ORDER_BY_ASC) => "ORDER BY column ASC"
     * orderBy('column0')->orderBy('column1') => "ORDER BY column0, column1"
     *
     * @param string $column        
     * @param string $order
     *        Sql::$ORDER_BY_ASC or Sql::$ORDER_BY_DESC
     * @return SelectOrderByRule
     */
    public function orderBy($column, $order = null)
    {
        $this->order->orderBy($this->context, $column, $order);
        return $this;
    }

    /**
     * orderByArgs(['column0', 'column1'=>Sql::$ORDER_BY_ASC]) => "ORDER BY column0,column1 ASC"
     *
     * @param array $args        
     * @return SelectOrderByRule
     */
    public function orderByArgs($args)
    {
        $this->order->orderByArgs($this->context, $args);
        return $this;
    }

}

/**
 * SELECT-HAVING规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectHavingRule extends SelectOrderByRule
{

    /**
     *
     * having('SUM(a)=?', 1) => "HAVING SUM(a)=1"
     * having('a>?', Sql::native('now()')) => "HAVING a>now()"
     * having('a IN (?)', [1, 2]) => "HAVING a IN (1,2)"
     *
     * @param string $expr        
     * @param string $_        
     * @return SelectOrderByRule
     */
    public function having($expr, $_ = null)
    {
        SqlWhereImpl::having($this->context, $expr, array_slice(func_get_args(), 1));
        return new SelectOrderByRule($this->context);
    }

    /**
     *
     * havingArgs([
     * 'a'=>1,
     * 'b'=>['IN'=>[1,2]]
     * 'c'=>['BETWEEN'=>[1,2]]
     * 'd'=>['<>'=>1]
     * ])
     *
     * =>
     * "HAVING a=1 AND b IN(1,2) AND c BETWEEN 1 AND 2 AND d<>1"
     *
     *
     * @param array $args        
     * @return SelectOrderByRule
     */
    public function havingArgs($args)
    {
        SqlWhereImpl::havingArgs($this->context, $args);
        return new SelectOrderByRule($this->context);
    }
}

/**
 * SELECT-GROUPBY规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectGroupByRule extends SelectOrderByRule
{

    /**
     * groupBy('column') => "GROUP BY column"
     *
     * @param string $column        
     * @return SelectHavingRule
     */
    public function groupBy($column)
    {
        SqlGroupByImpl::groupBy($this->context, $column);
        return new SelectHavingRule($this->context);
    }
}

/**
 * SELECT-WHERE规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectWhereRule extends SelectGroupByRule
{

    /**
     *
     * where('a=?', 1) => "WHERE a=1"
     * where('a=?', Sql::native('now()')) => "WHERE a=now()"
     * where('a IN (?)', array(1, 2)) => "WHERE a IN (1,2)"
     *
     * @param string $expr        
     * @param mixed $_        
     * @return SelectGroupByRule
     */
    public function where($expr, $_ = null)
    {
        SqlWhereImpl::where($this->context, $expr, array_slice(func_get_args(), 1));
        return new SelectGroupByRule($this->context);
    }

    /**
     * whereArgs([
     * 'a'=>1,
     * 'b'=>['IN'=>[1,2]]
     * 'c'=>['BETWEEN'=>[1,2]]
     * 'd'=>['<>'=>1]
     * ])
     *
     * =>
     * "WHERE a=1 AND b IN(1,2) AND c BETWEEN 1 AND 2 AND d<>1"
     *
     * @param array $args        
     * @return SelectGroupByRule
     */
    public function whereArgs($args)
    {
        SqlWhereImpl::whereArgs($this->context, $args);
        return new SelectGroupByRule($this->context);
    }
}

/**
 * SELECT-JOIN规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectJoinRule extends SelectWhereRule
{

    /**
     * join('table1')->on('table0.id=table1.id') => "JOIN table1 ON table0.id=table1.id"
     *
     * @param string $table        
     * @return SelectJoinOnRule
     */
    public function join($table)
    {
        SqlJoinImpl::join($this->context, null, $table);
        return new SelectJoinOnRule($this->context);
    }

    /**
     * leftJoin('table1')->on('table0.id=table1.id') => "LEFT JOIN table1 ON table0.id=table1.id"
     *
     * @param string $table        
     * @return SelectJoinOnRule
     */
    public function leftJoin($table)
    {
        SqlJoinImpl::join($this->context, 'LEFT', $table);
        return new SelectJoinOnRule($this->context);
    }

    /**
     * rightJoin('table1')->on('table0.id=table1.id') => "RIGHT JOIN table1 ON table0.id=table1.id"
     *
     * @param string $table        
     * @return SelectJoinOnRule
     */
    public function rightJoin($table)
    {
        SqlJoinImpl::join($this->context, 'RIGHT', $table);
        return new SelectJoinOnRule($this->context);
    }

    /**
     * innerJoin('table1')->on('table0.id=table1.id') => "INNER JOIN table1 ON table0.id=table1.id"
     *
     * @param string $table        
     * @return SelectJoinOnRule
     */
    public function innerJoin($table)
    {
        SqlJoinImpl::join($this->context, 'INNER', $table);
        return new SelectJoinOnRule($this->context);
    }
}

/**
 * SELECT-JOINON规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SelectJoinOnRule extends SqlBasicRule
{

    /**
     * join('table1')->on('table0.id=table1.id') => "JOIN table1 ON table0.id=table1.id"
     *
     * @param string $condition        
     * @return SelectJoinRule
     */
    public function on($condition)
    {
        SqlJoinOnImpl::on($this->context, $condition);
        return new SelectJoinRule($this->context);
    }
}



