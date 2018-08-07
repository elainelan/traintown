<?php

/**
 * REPLACE 规则文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */

/**
 * REPLACE规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class ReplaceIntoSqlRule extends SqlBasicRule
{

    /**
     * replaceInto('table')->values([1,2]) => "REPLACE INTO table VALUES(1,2)"
     *
     * @param string $table        
     * @return ReplaceValuesRule
     */
    public function replaceInto($table)
    {
        SqlReplaceImpl::replaceInto($this->context, $table);
        return new ReplaceValuesRule($this->context);
    }
}

/**
 * REPLACE-VALUES规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class ReplaceValuesRule extends SqlBasicRule
{

    /**
     * replaceInto('table')->values([1,2]) => "REPLACE INTO table VALUES(1,2)"
     * replaceInto('table')->values(['a'=>1, 'b'=>Sql::native('now()')]) => "REPLACE INTO table(a,b) VALUES(1,now())"
     *
     * @param array $values        
     * @return SqlExecRule
     */
    public function values($values)
    {
        SqlValuesImpl::values($this->context, $values);
        return new SqlExecRule($this->context);
    }
}