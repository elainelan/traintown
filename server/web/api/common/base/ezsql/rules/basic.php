<?php

/**
 * SQL基本规则类文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */

/**
 * SQL基础规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SqlBasicRule
{

    /**
     * 上下文指针
     *
     * @var SqlConetxt
     */
    public $context;

    /**
     * 初始化，绑定上下文类
     *
     * @param SqlConetxt $context        
     */
    public function __construct($context)
    {
        $this->context = $context;
    }

    /**
     * 获取sqlContext实例
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * 序列化输出sqlContext实例，一般用作传输用
     */
    public function getContextSerialize()
    {
        return serialize($this->context);
    }
}

/**
 * SQL执行类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SqlExecRule extends SqlBasicRule
{

    /**
     * 执行SQL指令
     *
     * @param PDO $db
     *        PDO实例指针
     * @param boolean $errExce
     *        whether throw exceptios
     * @return Response 处理结果类Response
     */
    public function exec($db, $errExce = true)
    {
        return SqlExecImpl::exec($this->context, $db, $errExce);
    }
}

/**
 * SQL LIMIT 规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SqlLimitRule extends SqlExecRule
{

    /**
     * limit(1) => "LIMIT 1"
     *
     * @param int $size        
     * @return SqlExecRule
     */
    public function limit($size)
    {
        SqlLimitImpl::limit($this->context, $size);
        return new SqlExecRule($this->context);
    }
}

/**
 * SQL ORDER BY 规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SqlOrderByRule extends SqlLimitRule
{

    /**
     * 输入类
     *
     * @var SqlOrderByImpl
     */
    private $impl;

    /**
     * 初始化
     *
     * @param SqlConetxt $context        
     */
    public function __construct($context)
    {
        parent::__construct($context);
        $this->impl = new SqlOrderByImpl();
    }

    /**
     * orderByArgs(['column0', 'column1'=>Sql::$ORDER_BY_ASC]) => "ORDER BY column0,column1 ASC"
     *
     * @param array $orders        
     * @return SqlLimitRule
     */
    public function orderByArgs($orders)
    {
        $this->impl->orderByArgs($this->context, $orders);
        return new SqlLimitRule($this->context);
    }

    /**
     *
     * orderBy('column') => "ORDER BY column"
     * orderBy('column', Sql::$ORDER_BY_ASC) => "ORDER BY column ASC"
     * orderBy('column0')->orderBy('column1') => "ORDER BY column0, column1"
     *
     * @param string $column        
     * @param string $order
     *        Sql::$ORDER_BY_ASC or Sql::$ORDER_BY_DESC
     *        
     * @return SqlLimitRule
     */
    public function orderBy($column, $order = null)
    {
        $this->impl->orderBy($this->context, $column, $order);
        return new SqlLimitRule($this->context);
    }

}

/**
 * SQL WHERE 规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class SqlWhereRule extends SqlOrderByRule
{

    /**
     *
     * where('a=?', 1) => "WHERE a=1"
     * where('a=?', Sql::native('now()')) => "WHERE a=now()"
     * where('a IN (?)', [1, 2]) => "WHERE a IN (1,2)"
     *
     * @param string $expr        
     * @param mixed $_        
     * @return SqlOrderByRule
     */
    public function where($expr, $_ = null)
    {
        SqlWhereImpl::where($this->context, $expr, array_slice(func_get_args(), 1));
        return new SqlOrderByRule($this->context);
    }

    /**
     *
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
     * @param string $args        
     * @return SqlOrderByRule
     */
    public function whereArgs($args)
    {
        SqlWhereImpl::whereArgs($this->context, $args);
        return new SqlOrderByRule($this->context);
    }
}