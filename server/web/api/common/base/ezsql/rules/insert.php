<?php

/**
 * SQL INSERT 规则文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */

/**
 * INSERT规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class InsertSqlRule extends SqlBasicRule
{

    /**
     *
     * insertInto('table')->values([1,2]) => "INSERT INTO table VALUES(1,2)"
     *
     * @param string $table
     *        数据库表
     * @param string $insertAttr
     *        插入属性, Sql::$INSERT_DELAYED, Sql::$INSERT_IGNORE等
     * @return InsertValuesRule
     */
    public function insertInto($table, $insertAttr)
    {
        SqlInsertImpl::insertInto($this->context, $table, $insertAttr);
        return new InsertValuesRule($this->context);
    }
}

/**
 * INSERT-VALUE规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class InsertValuesRule extends SqlBasicRule
{

    /**
     *
     * insertInto('table')->values(array(1,2)) => "INSERT INTO table VALUES(1,2)"
     * insertInto('table')->values(array('a'=>1, 'b'=>Sql::native('now()'))) => "INSERT INTO table(a,b) VALUES(1,now())"
     *
     * @param array $values
     *        数据数组
     * @return InsertOnDuplicateKeyUpdateRule
     */
    public function values($values)
    {
        SqlValuesImpl::values($this->context, $values);
        return new InsertOnDuplicateKeyUpdateRule($this->context);
    }

    /**
     *
     * insertInto('table')->values(array(array(1,2), array(3,4) => "INSERT INTO table VALUES(1,2),(3,4)"
     * insertInto('table')->values(array(array('a'=>1, 'b'=>Sql::native('now()'), array('a'=>3, 'b'=>4)) => "INSERT INTO table(a,b) VALUES(1,now()),(3,4)"
     *
     * @param array $values
     *        数据数组
     * @return InsertOnDuplicateKeyUpdateRule
     */
    public function valuesMulti($values)
    {
        SqlValuesImpl::valuesMulti($this->context, $values);
        return new InsertOnDuplicateKeyUpdateRule($this->context);
    }
}

/**
 * INSERT-OnDuplicateKeyUpdate规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class InsertOnDuplicateKeyUpdateRule extends SqlExecRule
{

    /**
     * 输入规则impl
     *
     * @var SqlOnDuplicateKeyUpdateImpl
     */
    private $impl;

    /**
     * 构造函数，初始化impl
     *
     * @param SqlConetxt $context
     *        SqlConetxt上下文类
     */
    public function __construct($context)
    {
        parent::__construct($context);
        $this->impl = new SqlOnDuplicateKeyUpdateImpl();
    }

    /**
     *
     * insertInto('table')
     * ->values(['a'=>1, 'b'=>Sql::native('now()')])
     * ->onDuplicateKeyUpdate('a', Sql::native('a+1'))
     * => "INSERT INTO table(a,b) VALUES(1,now()) ON DUPLICATE KEY UPDATE a=a+1"
     *
     * @param string $column        
     * @param mixed $value        
     * @return SqlExecRule
     */
    public function onDuplicateKeyUpdate($column, $value)
    {
        $this->impl->set($this->context, $column, $value);
        return new SqlExecRule($this->context);
    }

    /**
     *
     * insertInto('table')
     * ->values(['a'=>1, 'b'=>Sql::native('now()')])
     * ->onDuplicateKeyUpdateArgs(['a'=>Sql::native('a+1')])
     * => "INSERT INTO table(a,b) VALUES(1,now()) ON DUPLICATE KEY UPDATE a=a+1"
     *
     * @param string $column        
     * @param mixed $value        
     * @return SqlExecRule
     */
    public function onDuplicateKeyUpdateArgs($values)
    {
        $this->impl->setArgs($this->context, $values);
        return new SqlExecRule($this->context);
    }

    /**
     *
     * insertInto('table')
     * ->values(['a'=>1, 'b'=>Sql::native('now()')])
     * ->onDuplicateKeyUpdateExpr('a=a+1')
     * => "INSERT INTO table(a,b) VALUES(1,now()) ON DUPLICATE KEY UPDATE a=a+1"
     *
     * @param string $column        
     * @param mixed $value        
     * @return SqlExecRule
     */
    public function onDuplicateKeyUpdateExpr($expr, $_ = null)
    {
        $this->impl->setExpr($this->context, $expr, array_slice(func_get_args(), 1));
        return new SqlExecRule($this->context);
    }

}