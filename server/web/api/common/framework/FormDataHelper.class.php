<?php
/**
 * FormDataHelper Form表单参数配置生成辅助类
 *
 * @author whx
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * FormDataHelper
 *
 * How-to-use:

 * FormDataHelper::init($error)                   //初始化，可设置默认错误码
 * ->skip_check(array(null, '', 0))                 //如果表单的值在此范围内，直接跳过检查，默认值array(null, '')
 * ->skip_value(array(null, '', 0))                 //如果表单的值在此范围内，直接跳过检查，默认值array(null, '')
 * ->number($error)                                 //数字检查，检测不通过报错，错误码缺省，报默认错误
 * ->rename('id')                                   //重命名键名
 * ->between(array(1,100), $error)                  //范围检查，在此范围内检查通过，不通过报错，错误码缺省，报默认错误
 * ->notbetween(array(1,100), $error)               //范围检查，不在此范围内检查通过，不通过报错，错误码缺省，报默认错误
 * ->in(array(2,3,4), $error)                       //集合检查，在此集合数组内验证通过，错误码缺省，报默认错误
 * ->notin(array(3,6,7), $error)                    //集合检查，不在此集合数组内验证通过，错误码缺省，报默认错误
 * ->length(array(1,4), $error)                     //长度检查，在此范围内检查通过，不通过报错，错误码缺省，报默认错误
 * ->equal(1, $error)                               //相等检查，等于检查通过，不通过报错，错误码缺省，报默认错误
 * ->notequal(1, $error)                            //相等检查，不等于检查通过，不通过报错，错误码缺省，报默认错误
 * ->func(array($this, 'is_ok', $reserve), $error)  //使用 函数/类-方法 对参数进行验证 不通过报错，错误码缺省，报默认错误($reserve=false，表示方法返回true表示验证通过，$reserve=true或者缺省，表示方法返回false表示验证通过)
 * ->transform('strtotime', $error)                 //使用的 函数/类-方法 对表单值进行处理，处理失败报错，错误码缺省，报默认错误
 * ->auto(array($this, 'make_md5'), $error)         //没有提交值时使用 函数/类-方法 自动生成，生成失败报错，错误码缺省，报默认错误
 *
 */

/**
 * Form表单参数配置生成辅助类
 *
 * @author whx
 * @package common
 * @subpackage base
 */
class FormDataHelper
{

    /**
     * 检查条件索引ID
     */
    private $cond_idx = 0;

    /**
     * 默认错误码
     */
    private $default_error_code = 0;

    /**
     * 生成FormDataHelper类的一个实例
     * 
     * @param CErrorCode $default_error
     *        默认错误代码。初始值0，表示无默认错误代码。
     * @return FormDataHelper
     */
    public static function init($default_error = 0)
    {
        return new self($default_error);
    }

    /**
     * 构造方法
     * @param CErrorCode $default_error 默认错误代码
     */
    public function __construct($default_error)
    {
        if ($default_error) {
            $this->default_error_code = $default_error;
        }
    }

    /**
     * 跳过检查设置
     * 
     * @param array $param
     *        参数的值在数组中时，跳过检查
     * @return FormDataHelper 实例自身
     */
    public function skip_check($param = array(null, ''))
    {
        $this->skip_check = $param;
        return $this;
    }

    /**
     * 跳过赋值设置
     * 
     * @param array $param
     *        参数的值在数组中时，跳过赋值操作，并删除此键值
     * @return FormDataHelper 实例自身
     */
    public function skip_value($param = array(null, ''))
    {
        $this->skip_value = $param;
        return $this;
    }

    /**
     * 长度检查
     * 
     * @param array $param
     *        长度范围，在此范围内验证通过，array(最短长度，最长长度)
     * @param CErrorCode $error
     *        验证不通过时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function length($param, $error = '', $reverse = false)
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("length", $param, $error, $reverse);
        return $this;
    }

    /**
     * 数值范围检查
     * 
     * @param array $param
     *        数值范围，在此范围内验证通过，array(起始数值，结束数值)
     * @param CErrorCode $error
     *        验证不通过时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function between($param, $error = '', $reverse = false)
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("between", $param, $error, $reverse);
        return $this;
    }

    /**
     * 不在数值范围内的检查
     * 
     * @param array $param
     *        数值范围，不在此范围内验证通过，array(起始数值，结束数值)
     * @param CErrorCode $error
     *        验证不通过时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function notbetween($param, $error = '', $reverse = false)
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("notbetween", $param, $error, $reverse);
        return $this;
    }

    /**
     * 集合内检查
     * 
     * @param array $param
     *        集合内检查，在此集合数组内验证通过，array(元素1,元素2......)
     * @param CErrorCode $error
     *        验证不通过时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function in($param, $error = '', $reverse = false)
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("in", $param, $error, $reverse);
        return $this;
    }

    /**
     * 集合外检查
     * 
     * @param array $param
     *        集合外检查，不在此数组集合内验证通过，array(元素1,元素2......)
     * @param CErrorCode $error
     *        验证不通过时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function notin($param, $error = '', $reverse = false)
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("notin", $param, $error, $reverse);
        return $this;
    }

    /**
     * 相等检查(==)
     * 
     * @param mixed $param
     *        等于时(==)验证通过
     * @param CErrorCode $error
     *        验证不通过时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function equal($param, $error = '', $reverse = false)
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("equal", $param, $error, $reverse);
        return $this;
    }

    /**
     * 不相等(!=)检查
     * 
     * @param string $param
     *        不等于时(!=)验证通过
     * @param CErrorCode $error
     *        验证不通过时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function notequal($param, $error = '', $reverse = false)
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("notequal", $param, $error, $reverse);
        return $this;
    }

    /**
     * 正则检查
     * 
     * @param string $param
     *        正则表达式，匹配时验证通过
     * @param CErrorCode $error
     *        验证不通过时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function regex($param, $error = '', $reverse = false)
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("regex", $param, $error,$reverse);
        return $this;
    }

    /**
     * 数字检查，0和正整数
     * 
     * @param CErrorCode $error
     *        验证不通过时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function number($error = '', $reverse = false)
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("regex", "number", $error, $reverse);
        return $this;
    }

    /**
     * 重命名键
     * 
     * @param string $name
     *        重命名的键名
     * @return FormDataHelper 实例自身
     */
    public function rename($name)
    {
        $this->{'cond_' . $this->cond_idx++} = array("rename", $name);
        return $this;
    }

    /**
     * 数值转换
     * 
     * @param array|string $param
     *        处理使用的函数/类-方法数组
     * @param CErrorCode $error
     *        处理失败时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function transform($param, $error = '')
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("transform", $param, $error);
        return $this;
    }

    /**
     * 没有提交值时自动生成
     * 
     * @param array|string $param
     *        自动生成使用的函数/类-方法数组
     * @param CErrorCode $error
     *        自动生成失败时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function auto($param, $error = '')
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("auto", $param, $error);
        return $this;
    }

    /**
     * 使用方法对参数进行验证
     * 
     * @param array|string $param
     *        自动生成使用的函数/类-方法数组
     * @param CErrorCode $error
     *        自动生成失败时返回的错误码
     * @return FormDataHelper 实例自身
     */
    public function func($param, $error = '', $reverse = false)
    {
        if (!$error && $this->default_error_code) {
            $error = $this->default_error_code;
        }
        $this->{'cond_' . $this->cond_idx++} = array("func", $param, $error, $reverse);
        return $this;
    }
}