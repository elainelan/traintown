<?php
/**
 * API分发/解析文件
 *
 * @author dragonets
 */

header('P3P: CP="CAO PSA OUR"');

define('CV_ROOT', dirname(__FILE__) . '/');
define('COM_PATH', CV_ROOT . 'common/');
define('CONF_PATH', CV_ROOT . 'conf/');
define('PATH_PREFIX', 'gmd'); // 路径前缀
define('DO_PATH', CV_ROOT . 'do/' . PATH_PREFIX . '/');

require_once CONF_PATH . PATH_PREFIX . 'config.php';
require_once COM_PATH . 'autoload.php';

// 关闭DB操作记录的记录
ResultParser::$operationRecord = false;

// 生产环境下的错误捕获，当配置DEBUG_ERROR=0生效
ErrorHandlerRegister::register();

// 性能记录起始点
if (defined('LOG_PERFORMANCE') && LOG_PERFORMANCE) {
    $performance_key = HttpParam::server('REQUEST_URI');
    Performance::setStartTime($performance_key);
}

// cmd解析成（cmd=class.method)
$cmdstr = HttpParam::request(PATH_PREFIX);
if (!$cmdstr) {
    ResultParser::error(GErrorCode::MISS_CMD);
}
$cmds = explode('.', $cmdstr);
if (isset($cmds[0])) {
    $class = strtolower($cmds[0]);
}
if (isset($cmds[1])) {
    $method = strtolower($cmds[1]);
}
if ($class && class_exists($class)) {
    $obj = new $class();
}
else {
    ResultParser::error(GErrorCode::CLASS_NOT_FOUND, array('class' => $class));
}
if ($method && method_exists($obj, $method)) {
    // 记录类名和方法及action
    Api::init($class, $method, HttpParam::request('a'));
    // 调用接口
    $obj->$method();
}
else {
    ResultParser::error(GErrorCode::METHOD_NOT_FOUND, array('method' => $method));
}
