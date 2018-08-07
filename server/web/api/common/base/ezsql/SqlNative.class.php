<?php

/**
 * 原始SQL字符拼接处理文件
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */

/**
 * 原始sql字符串, 拼接时不进行转义
 *
 * @author dragonets
 * @package common
 * @subpackage base/ezsql
 */
class SqlNative
{

    /**
     * 不转义字符串
     *
     * @var string
     */
    private $str;

    /**
     * 初始化
     *
     * @param string $str
     *        不转义字符
     */
    function __construct($str)
    {
        $this->str = $str;
    }

    /**
     * 不转义字符转换成字符
     */
    public function __toString()
    {
        return $this->str;
    }

    /**
     * 获取不转义字符
     */
    public function get()
    {
        return $this->str;
    }

}
