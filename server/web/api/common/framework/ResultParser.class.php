<?php
/**
 * 结果输出处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 输出结果处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class ResultParser
{

    /**
     * 异常种类对应起始错误ID数组
     *
     * @var array
     */
    private static $Exception = array(
        'CVMemcacheException'   =>  10000,
        'PDOException'          =>  20000,
        'CVFileException'       =>  30000,
    );
    
    /**
     * 是否需要记录操作记录，默认开启
     * 
     * @var boolean
     */
    public static $operationRecord = true;

    /**
     * 返回成功结果
     *
     * @param string|array $succ_data
     *        成功返回数据
     * @param string $succ_code
     *        成功代码
     * @param string $succ_param
     *        成功语言包key
     * @return json
     */
    static function succ($succ_data, $succ_code = null, $succ_param = array())
    {
        $res = array('r' => 1, 'data' => $succ_data);
        if (defined('DEBUG_ERROR') && DEBUG_ERROR) {
            $res['succMsg'] = $succ_code ? Lang::show($succ_code, $succ_param) : null;
            CVLog::addlog('succ', $res);
        }
        if (defined('LOG_PERFORMANCE') && LOG_PERFORMANCE) {
            $performance = Performance::getParseData(HttpParam::server('REQUEST_URI'));
            CVLog::addlog('performance', $performance);
            if (defined('SHOW_PERFORMANCE') && SHOW_PERFORMANCE) {
                $res['performance'] = $performance;
            }
        }
        self::operationRecord($res);
        echo json_encode($res);
        exit();
    }

    /**
     * 返回错误结果
     *
     * @param string $err_code
     *        失败代码
     * @param array $err_param
     *        失败语言包key
     */
    static function error($err_code, $err_param = array())
    {
        $res = array('r' => 0, 'errCode' => $err_code);
        if (defined('DEBUG_ERROR') && DEBUG_ERROR) {
            $res['errMsg'] = Lang::show($err_code, $err_param);
        }
        self::operationRecord($res);
        exit(json_encode($res));
    }

    /**
     * 错误异常输出
     *
     * @param Exception $exception        
     */
    static function errorException($exception)
    {
        
        $err_code = $exception->getCode();
        $class = get_class($exception);
        if (isset(self::$Exception[$class])) {
            $err_code += self::$Exception[$class];
        }
        
        $err_msg = $exception->getMessage();
        
        $res = array('r' => 0, 'errCode' => $err_code, 'errMsg' => $err_msg);
        self::operationRecord($res);
        
        // 转换500错误成200错误输出
        header('HTTP/1.1 200 OK');
        //$res['errCode'] = "[{$res['errCode']}]{$res['errMsg']}";

        if (!defined('DEBUG_ERROR') || !DEBUG_ERROR) {
            unset($res['errMsg']);
        }
        
        exit(json_encode($res));
    }

    /**
     * 记录返回结果
     *
     * @param array $res
     *        返回结果数组
     */
    static function operationRecord($res)
    {
        if (self::$operationRecord) {
            $admin_operation = CDbuser::getInstanceTable('AdminOperation');
            //$admin_operation = new AdminOperation(); // for dev
            return $admin_operation->updateDbValues($res);
        }
        return null;
    }
}

