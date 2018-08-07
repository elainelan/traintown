<?php
/**
 * 类自动加载实现
 *
 * @author dragonets
 * @package common
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 自动加载类
 *
 * @author dragonets
 * @package common
 */
class Loader
{

    /**
     * 初始化autoload
     */
    public static function autoload()
    {
        // common/base
        spl_autoload_register(array('Loader', 'commonBase'));
        
        // common/framework
        spl_autoload_register(array('Loader', 'commonFramework'));
        
        // common/application
        spl_autoload_register(array('Loader', 'commonApplication'));
        
        // common/extension
        spl_autoload_register(array('Loader', 'commonExtension'));
        
        // do
        spl_autoload_register(array('Loader', 'doClass'));
    }

    /**
     * common目录设置
     * 
     * @param string $subfolder        
     * @param string $classname        
     */
    private static function _commonFile($subfolder, $classname)
    {
        $filename = COM_PATH . $subfolder . $classname . '.class.php';
        self::_includeFile($filename);
    }

    /**
     * do目录设置
     * 
     * @param string $classname        
     */
    private static function _doFile($classname)
    {
        $filename = DO_PATH . $classname . '.php';
        self::_includeFile($filename);
    }

    /**
     * 包含文件
     * 
     * @param string $filename        
     */
    private static function _includeFile($filename)
    {
        if (file_exists($filename)) {
            include_once $filename;
        }
    }

    /**
     * common/base目录配置
     * 
     * @param string $classname        
     */
    public static function commonBase($classname)
    {
        self::_commonFile('base/', $classname);
        self::_commonFile('base/ezsql/', $classname);
    }

    /**
     * common/framework目录配置
     * 
     * @param string $classname        
     */
    public static function commonFramework($classname)
    {
        self::_commonFile('framework/', $classname);
        self::_commonFile('framework/' . PATH_PREFIX . '/', $classname);
    }

    /**
     * common/application目录配置
     * 
     * @param string $classname        
     */
    public static function commonApplication($classname)
    {
        self::_commonFile('application/', $classname);
        self::_commonFile('application/' . PATH_PREFIX . '/', $classname);
    }
    
    /**
     * common/extension目录配置
     *
     * @param string $classname
     */
    public static function commonExtension($classname)
    {
        self::_commonFile('extension/', $classname);
        self::_commonFile('extension/' . PATH_PREFIX . '/', $classname);
    }

    /**
     * do目录配置
     * 
     * @param string $classname        
     */
    public static function doClass($classname)
    {
        self::_doFile($classname);
    }
}

Loader::autoload();

