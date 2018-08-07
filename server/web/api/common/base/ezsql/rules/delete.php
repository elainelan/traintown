<?php

/**
 * SQL DELETE 规则文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */

/**
 * Delete规则类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql/rules
 */
class DeleteSqlRule extends SqlBasicRule
{

    /**
     * deleteFrom('table') => "DELETE FROM table"
     *
     * @param string $table        
     * @return SqlWhereRule
     */
    public function deleteFrom($table)
    {
        SqlDeleteImpl::deleteFrom($this->context, $table);
        return new SqlWhereRule($this->context);
    }
}
