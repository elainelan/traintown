<?php
/**
 * 代码规范说明文档
 * 
 * @author dragonets
 */



exit;
?>




    
    
<?php
/*

 common类代码风格统一要求：
1、类名首字母大写，采用驼峰命名：ClassA
2、类方法首字母小写，采用驼峰命名：doSomething
3、类变量全部小写，使用下划线分隔：$http_params



do类代码风格（暴露出去的接口类）：
1、类名、类方法、类变量都使用下划线_分隔，全部小写
class_a
do_something
$http_params


各类都需要填写PHPDOC规范注释
@package
如果有subpackage
@subpackage


*/

/**
 * COMMON类例子
 * @author dragonets
 *
 */
class TestClass
{
    /**
     * 测试类例子
     * @param string $class_name
     * @return string
     */
    public function getClass($class_name)
    {
        return $class_name;
    }
}

/**
 * DO类例子
 * @author dragonets
 *
 */
class test_class
{
    /**
     * 测试例子
     * @param string $class_name
     * @return string
     */
    public function get_class($class_name)
    {
        return $class_name;
    }
}


?>