<?php
/**
 * 文件操作类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 文件操作类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class CVFile
{

    /**
     * 文件实例数组
     *   文件名=>文件操作实例
     */
    private static $_cvfile_instances = array();
    
    /**
     * 操作文件名
     */
    private $_file_name = null;
    
    /**
     * 操作文件资源
     */
    private $_file_resource = null;
    
    /**
     * 操作文件打开方式
     */
    private $_file_open_type = null;
    
    /**
     * 获取文件操作对象实例
     *
     * @param string $file 文件名
     * 
     * @return object CVFile 
     */
    static public function getInstance($file)
    {
        if (!isset(self::$_cvfile_instances[$file])) {
            self::$_cvfile_instances[$file] = new self($file);
        }
        return self::$_cvfile_instances[$file];
    }

    /**
     * 构造函数
     * @param string $file 文件名
     */
    public function __construct($file)
    {
        $this->_file_name = $file;
    }
    
    /**
     * 打开文件方法
     * @param string $type
     * 
     * @return boolean
     */
    private function _openFile($type)
    {
        $this->_file_resource = @fopen($this->_file_name, $type);
        if (!$this->_file_resource) {
            throw new CVFileException("Open file {$this->_file_name} failed", CVFileException::FILE_OPEN_FAILED);
        }

        $this->_file_open_type = $type;
        return true;
    }
    
    /**
     * 向文件追加内容
     * @param sting $content 写入字符串
     * @return mix 返回写入的字符数，出现错误时则返回 false
     */
    public function append($content)
    {
        if (!$this->_file_resource) {
            $this->_openFile('a+b');
        }
        else if ($this->_file_open_type != 'a+b') {
            throw new CVFileException("File {$this->_file_name} has been opened in {$this->_file_open_type}", CVFileException::FILE_OPEN_WRONG);
        }
        
        return fwrite($this->_file_resource, $content);
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->closed();
    }
    
    /**
     * 关闭文件
     * @return boolean
     */
    public function closed()
    {
        if ($this->_file_resource) {
            $res = fclose($this->_file_resource);
            if ($res) {
                $this->_file_resource = null;
            }
            return $res;
        }
        
        return true;
    }
}

/**
 * CVFile报错异常类
 *
 * @author dragonets
 */
class CVFileException extends Exception
{

    const FILE_OPEN_FAILED = 1;//文件打开失败
    const FILE_OPEN_WRONG = 2;//文件打开方式错误
}