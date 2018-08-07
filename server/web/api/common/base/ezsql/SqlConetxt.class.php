<?php

/**
 * SQL状态类文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */

/**
 * SQL状态类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */
class SqlConetxt
{

    /**
     * SQL语句
     *
     * @var string
     */
    public $sql = '';

    /**
     * 绑定变量
     *
     * @var array
     */
    public $params = array();

    /**
     * 拼接sql语句，并自动插入空格
     *
     * @param string $sql
     *        表达式
     * @param boolean $addSpace
     *        连接SQL时，是否需要添加空格
     */
    public function appendSql($sql, $addSpace = true)
    {
        if ($this->sql == '') {
            $this->sql = $sql;
        }
        else {
            if ($addSpace) {
                $this->sql = $this->sql . ' ' . $sql;
            }
            else {
                $this->sql = $this->sql . $sql;
            }
        }
    }

    /**
     * 增加绑定变量值
     *
     * @param array $params
     *        变量
     */
    public function appendParams($params)
    {
        $this->params = array_merge($this->params, $params);
    }

}