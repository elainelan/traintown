<?php
/**
 * 生产环境错误控制
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 生产环境错误异常控制入口类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class ErrorHandlerRegister
{

    /**
     * 方 法：注册异常、错误拦截
     * 参 数：void
     * 返 回：void
     */
    public static function register()
    {
        global $argv;
        // 如果开启调试模式
        if (defined('DEBUG_ERROR') && DEBUG_ERROR) {
            ini_set('display_errors', 1);
            return;
        }
        //如果不开启调试模式
        ini_set('error_reporting', -1);
        ini_set('display_errors', 0);
        $handler = new errorHandler();
        $handler->argvs = $argv; //此处主要兼容命令行模式下获取参数
        $handler->register();
    }
}

/**
 * 异常捕获类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class ErrorHandler
{

    /**
     * 参数数组
     *
     * @var array
     */
    public $argvs = array();

    /**
     * 备用内存大小
     *
     * @var int
     */
    public $memoryReserveSize = 262144;

    /**
     * 备用内存数
     *
     * @var int
     */
    private $_memoryReserve;

    /**
     * 方 法：注册自定义错误、异常拦截器
     * 参 数：void
     * 返 回：void
     */
    public function register()
    {
        // 截获未捕获的异常
        set_exception_handler(array($this, 'handleException'));
        
        // 截获各种错误 此处切不可掉换位置
        set_error_handler(array($this, 'handleError'));
        
        // 留下备用内存 供后面拦截致命错误使用
        $this->memoryReserveSize > 0 && $this->_memoryReserve = str_repeat('x', $this->memoryReserveSize);
        
        // 截获致命性错误
        register_shutdown_function(array($this, 'handleFatalError'));
    }

    /**
     * 方 法：取消自定义错误、异常拦截器
     * 参 数：void
     * 返 回：void
     */
    public function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * 方 法：处理截获的未捕获的异常
     *
     * @param Exception $exception        
     * @return void
     */
    public function handleException($exception)
    {
        $this->unregister();
        try {
            if ($this->logException($exception) === false) {
                $this->logException($exception, true);
            }
        }
        catch (Exception $e) {
            // LOG记录失败
            CVLog::addlog('writeLogError', $e, true);
        }
        // API输出结果
        ResultParser::errorException($exception);
        exit(1);
    }

    /**
     * 方 法：处理截获的错误
     *
     * @param int $code
     *        错误代码
     * @param string $message
     *        错误信息
     * @param string $file
     *        错误文件
     * @param int $line
     *        错误的行数
     *        返 回：boolean
     */
    public function handleError($code, $message, $file, $line)
    {
        //该处思想是将错误变成异常抛出 统一交给异常处理函数进行处理
        if ((error_reporting() & $code) && !in_array($code, array(E_NOTICE, E_WARNING, E_USER_NOTICE, E_USER_WARNING, E_DEPRECATED))) { //此处只记录严重的错误 对于各种WARNING NOTICE不作处理
            $exception = new ErrorHandlerException($message, $code, $code, $file, $line);
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($trace); //trace的第一个元素为当前对象 移除
            foreach ($trace as $frame) {
                if ($frame['function'] == '__toString') { //如果错误出现在 __toString 方法中 不抛出任何异常
                    $this->handleException($exception);
                    exit(1);
                }
            }
            throw $exception;
        }
        return false;
    }

    /**
     * 方 法：截获致命性错误
     * 参 数：void
     * 返 回：void
     */
    public function handleFatalError()
    {
        unset($this->_memoryReserve); //释放内存供下面处理程序使用
        $error = error_get_last(); //最后一条错误信息
        if (ErrorHandlerException::isFatalError($error)) { //如果是致命错误进行处理
            $exception = new ErrorHandlerException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
            $this->logException($exception);
            ResultParser::errorException($exception);
            exit(1);
        }
    }

    /**
     * 方 法：获取服务器IP
     * 参 数：void
     * 返 回：string
     */
    final public function getServerIp()
    {
        $serverIp = '';
        if (isset($_SERVER['SERVER_ADDR'])) {
            $serverIp = $_SERVER['SERVER_ADDR'];
        }
        elseif (isset($_SERVER['LOCAL_ADDR'])) {
            $serverIp = $_SERVER['LOCAL_ADDR'];
        }
        elseif (isset($_SERVER['HOSTNAME'])) {
            $serverIp = gethostbyname($_SERVER['HOSTNAME']);
        }
        else {
            $serverIp = getenv('SERVER_ADDR');
        }
        return $serverIp;
    }

    /**
     * 方 法：获取当前URI信息
     * 参 数：void
     * 返 回：string $url
     */
    public function getCurrentUri()
    {
        $uri = '';
        if ($_SERVER["REMOTE_ADDR"]) { //浏览器浏览模式
            $uri = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }
        else { //命令行模式
            $params = $this->argvs;
            $uri = $params[0];
            array_shift($params);
            for ($i = 0,$len = count($params); $i < $len; $i++) {
                $uri .= ' ' . $params[$i];
            }
        }
        return $uri;
    }

    /**
     * 方 法：记录异常信息
     *
     * @param ErrorHandlerException $e
     *        错误异常
     * @param boolean $force_tmp
     *        是否强制/tmp记录日志
     * @return boolean 是否保存成功
     */
    final public function logException($e, $force_tmp = false)
    {
        $time = time();
        $error = array(
            'add_time' => $time,
            'time' => date('Y-m-d H:i:s', $time),
            'title' => ErrorHandlerException::getName($e->getCode()), //这里获取用户友好型名称
            'message' => $e->getMessage(),
            'server_ip' => $this->getServerIp(),
            'code' => ErrorHandlerException::getLocalCode($e->getCode()), //这里为各种错误定义一个编号以便查找
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $this->getCurrentUri(),
            'trace' => array(),
        );
        $e_getPrevious = null;
        do {
            //$e->getFile() . ':' . $e->getLine() . ' ' . $e->getMessage() . '(' . $e->getCode() . ')'
            $trace = (string)$e;
            $error['trace'][] = $trace;
            $e_getPrevious = $e->getPrevious();
        }
        while ($e_getPrevious);
        $error['trace'] = implode("\r\n", $error['trace']);
        return $this->logError($error, $force_tmp);
    }

    /**
     * 方 法：记录异常信息
     *
     * @param array $error
     *        = array(
     *        'time' => int,
     *        'title' => 'string',
     *        'message' => 'string',
     *        'code' => int,
     *        'server_ip' => 'string'
     *        'file' => 'string',
     *        'line' => int,
     *        'url' => 'string',
     *        );
     * @param boolean $force_tmp
     *        是否强制/tmp记录
     * @return boolean 是否保存成功
     */
    public function logError($error, $force_tmp = false)
    {
        /* 这里去实现如何将错误信息记录到日志 */
        return CVLog::addlog('prod', $error, $force_tmp);
    }
}

/**
 * 异常处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class ErrorHandlerException extends ErrorException
{

    /**
     * 本地错误代码映射
     *
     * @var array
     */
    public static $localCode = array(E_COMPILE_ERROR => 4001, E_COMPILE_WARNING => 4002, E_CORE_ERROR => 4003, E_CORE_WARNING => 4004, E_DEPRECATED => 4005, E_ERROR => 4006, E_NOTICE => 4007, E_PARSE => 4008, E_RECOVERABLE_ERROR => 4009, E_STRICT => 4010, E_USER_DEPRECATED => 4011, E_USER_ERROR => 4012, E_USER_NOTICE => 4013, E_USER_WARNING => 4014, E_WARNING => 4015, 4016 => 4016);

    /**
     * 本地编译错误映射
     *
     * @var array
     */
    public static $localName = array(E_COMPILE_ERROR => 'PHP Compile Error', E_COMPILE_WARNING => 'PHP Compile Warning', E_CORE_ERROR => 'PHP Core Error', E_CORE_WARNING => 'PHP Core Warning', E_DEPRECATED => 'PHP Deprecated Warning', E_ERROR => 'PHP Fatal Error', E_NOTICE => 'PHP Notice', E_PARSE => 'PHP Parse Error', E_RECOVERABLE_ERROR => 'PHP Recoverable Error', E_STRICT => 'PHP Strict Warning', E_USER_DEPRECATED => 'PHP User Deprecated Warning', E_USER_ERROR => 'PHP User Error', E_USER_NOTICE => 'PHP User Notice', E_USER_WARNING => 'PHP User Warning', E_WARNING => 'PHP Warning', 4016 => 'Customer`s Error');

    /**
     * 方 法：构造函数
     * 摘 要：相关知识请查看 http://php.net/manual/en/errorexception.construct.php
     *
     * @param string $message
     *        异常信息(可选)
     * @param int $code
     *        异常代码(可选)
     * @param int $severity        
     * @param string $filename
     *        异常文件(可选)
     * @param int $line
     *        异常的行数(可选)
     * @param Exception $previous
     *        上一个异常(可选)
     *        
     * @return void
     */
    public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $line = __LINE__, Exception $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $line, $previous);
    }

    /**
     * 方 法：是否是致命性错误
     *
     * @param array $error        
     * @return boolean
     */
    public static function isFatalError($error)
    {
        $fatalErrors = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING);
        return isset($error['type']) && in_array($error['type'], $fatalErrors);
    }

    /**
     * 方 法：根据原始的错误代码得到本地的错误代码
     *
     * @param int $code        
     * @return int $localCode
     */
    public static function getLocalCode($code)
    {
        return isset(self::$localCode[$code]) ? self::$localCode[$code] : self::$localCode[4016];
    }

    /**
     * 方 法：根据原始的错误代码获取用户友好型名称
     *
     * @param int $code
     *        错误代码
     * @return string $name
     */
    public static function getName($code)
    {
        return isset(self::$localName[$code]) ? self::$localName[$code] : self::$localName[4016];
    }
}