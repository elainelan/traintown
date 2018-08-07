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
define('PATH_PREFIX', 'cmd'); // 路径前缀
define('DO_PATH', CV_ROOT . 'do/' . PATH_PREFIX . '/');

require_once CONF_PATH . PATH_PREFIX . 'config.php';
require_once COM_PATH . 'autoload.php';

// 设置跨域域名
CrossDomain::validate(API_CLIENT_DOMAIN);

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
    ResultParser::error(CErrorCode::MISS_CMD);
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
    ResultParser::error(CErrorCode::CLASS_NOT_FOUND, array('class' => $class));
}
if ($method && method_exists($obj, $method)) {
    // 记录类名和方法及action
    Api::init($class, $method, HttpParam::request('a'));
    // 登录状态验证
    AdminLogin::check();
    // 后台配置获取
    AdminSetting::get();
    // 记录操作记录
    $admin_operation = CDbuser::getInstanceTable('AdminOperation');
    //$admin_operation = new AdminOperation(); // for dev
    $oper_id = $admin_operation->insertRecord();
    // 记录操作记录id
    Api::setOperId($oper_id);
    // 权限验证
    AdminPermissions::check();
    // 调用接口
    $obj->$method();
}
else {
    ResultParser::error(CErrorCode::METHOD_NOT_FOUND, array('method' => $method));
}
