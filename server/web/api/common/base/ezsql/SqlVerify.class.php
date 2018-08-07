<?php

/**
 * SQL数据类型验证类文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */

/**
 * SQL数据类型验证类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */
class SqlVerify
{

    /**
     * 如果判断不为true,抛出异常
     *
     * @param boolean $var        
     * @param string|Exception $msg        
     * @param number $code        
     * @throws Exception
     * @return unknown
     */
    static public function isTrue($var, $msg = null)
    {
        if (!$var) {
            CVLog::addlog('SQL_SqlVerify_error', $msg);
            throw new Exception($msg);
        }
        else {
            return $var;
        }
    }
}