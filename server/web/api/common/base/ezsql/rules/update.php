<?php

/**
 * UPDATE 更新规则文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */

/**
 * UPDATE规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class UpdateSqlRule extends SqlBasicRule
{

    /**
     * update('table')->set('a', 1) => "UPDATE table SET a=1"
     *
     * @param string $table        
     * @return UpdateSetSqlRule
     */
    public function update($table)
    {
        SqlUpdateImpl::update($this->context, $table);
        return new UpdateSetSqlRule($this->context);
    }
}

/**
 * UPDATE-SET规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class UpdateSetSqlRule extends SqlWhereRule
{

    /**
     * 输入类
     *
     * @var SqlUpdateSetImpl
     */
    private $impl;

    /**
     * 初始化，绑定上下文类及impl类
     *
     * @param SqlConetxt $context        
     */
    public function __construct($context)
    {
        parent::__construct($context);
        $this->impl = new SqlUpdateSetImpl();
    }

    /**
     * update('table')->set('a', 1) => "UPDATE table SET a=1"
     * update('table')->set('a', 1)->set('b',Sql::native('now()')) => "UPDATE table SET a=1,b=now()"
     *
     * @param string $column        
     * @param mixed $value        
     * @return UpdateSetSqlRule
     */
    public function set($column, $value)
    {
        $this->impl->set($this->context, $column, $value);
        return $this;
    }

    /**
     * update('table')->set(['a'=>1, 'b'=>Sql::native('now()')]) => "UPDATE table SET a=1,b=now()"
     *
     * @param array $values        
     * @return UpdateSetSqlRule
     */
    public function setArgs($values)
    {
        $this->impl->setArgs($this->context, $values);
        return $this;
    }

    /**
     * update('table')->setExpr('a=a+?',1)
     *
     * @param string $expr        
     * @param mixed $_        
     * @return UpdateSetSqlRule
     */
    public function setExpr($expr, $_ = null)
    {
        $this->impl->setExpr($this->context, $expr, array_slice(func_get_args(), 1));
        return $this;
    }
}



