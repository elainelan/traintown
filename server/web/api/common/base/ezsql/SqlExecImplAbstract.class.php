<?php
/**
 * SQL处理类文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */
 

/**
 * SQL处理操作类
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */
class SqlExecImplAbstract
{
    /**
     * 执行SQL处理
     *
     * @param SqlConetxt $context
     *        SqlConetxt上下文
     * @param boolean $errExce
     *        是否抛出错误异常
     * @return SqlResponse
     */
    public function sqlExecImplExec($context, $errExce = true)
    {

    }

    /**
     * 获取数据
     *
     * @param SqlConetxt $context
     *        SqlConetxt上下文
     * @param string $asDict
     *        排序字段，如果有返回按此字段排序数组，否则返回数组
     * @param boolean $errExce
     *        是否抛出错误异常
     * @return false|array
     */
    public function sqlExecImplGet($context, $dictAs = null, $errExce = true)
    {

    }

    /**
     * 整个数据库表导出
     *
     * @param SqlConetxt $context
     *        SqlContext上下文
     * @param string $asDict
     *        排序字段，如果有返回按此字段排序数组，否则返回数组
     * @param boolean $errExce
     *        是否抛出错误异常
     * @return false|array
     */
    public function sqlExecImplExport($context, $file, $errExce = true)
    {

    }
}