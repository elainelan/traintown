<?php
/**
 * 表单数据类
 *
 * @author whx
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 处理表单数据类
 *
 * @author 王海修
 * @package common
 * @subpackage base
 */
class FormData
{
    /**
     * 把表单(request)数据转化为标准数据
     *
     * @param array $request_conf_arys
     *        array(
     *        request_key_1 => array(
     *        array(type, para, error),
     *        
     *        array('transform', array(class, method), error),
     *        array('auto', array(class, method), error),
     *        
     *        'skip_value' => array("", null),
     *        'skip_check' => array("", null),
     *        ),
     *        request_key_2 => array(
     *        array(type, para, error),
     *        )
     *        )
     */
    public static function getFormArgs($request_conf_arys)
    {
        $data = array();
        foreach ($request_conf_arys as $request_key => $request_confs) {
            
            // 获取值
            $request_value = HttpParam::request($request_key);
            
            // skip_check、skip_value配置获取
            if (is_object($request_confs)) {
                $skip_check = isset($request_confs->skip_check) ? $request_confs->skip_check : null;
                $skip_value = isset($request_confs->skip_value) ? $request_confs->skip_value : null;
                unset($request_confs->skip_check, $request_confs->skip_value);
            }
            else {
                $skip_check = isset($request_confs['skip_check']) ? $request_confs['skip_check'] : null;
                $skip_value = isset($request_confs['skip_value']) ? $request_confs['skip_value'] : null;
                unset($request_confs['skip_check'], $request_confs['skip_value']);
            }
            
            // skip_check检查
            if (!$skip_check || !in_array($request_value, $skip_check, true)) {
                foreach ($request_confs as $request_conf) {
                    
                    $type = $request_conf[0];
                    $para = $request_conf[1];
                    $error = isset($request_conf[2]) ? $request_conf[2] : null;
                    $reverse = isset($request_conf[3]) ? $request_conf[3] : false;
                    
                    // 检查处理
                    switch ($type) {
                        case 'int': // 强转int
                            $request_value = (int)$request_value;
                            break;
                        case 'bool': // 强转boolean
                            $request_value = (bool)$request_value;
                            break;
                        case 'rename': // 数据key改名
                            $request_key = $para;
                            break;
                        case 'transform': // 数据格式化转换
                        case 'auto': // 自动生成数据
                            try {
                                $request_value = call_user_func_array($para, array($request_value));
                            }
                            catch (Exception $e) {
                                ResultParser::error($error);
                            }
                            break;
                        
                        default:
                            //类实例化
                            if (is_array($para) && isset($para['db'])) {
                                $db = CDbuser::getInstanceTable($para['db']);
                                unset($para['db']);
                                array_unshift($para, $db);
                            }
                            // 数据检查
                            if (!CVDataValidate::check($request_value, $para, $type, $reverse)) {
                                ResultParser::error($error);
                            }
                            break;
                    }
                }
            }
            
            // skip_value检查
            if ($skip_value) {
                if (in_array($request_value, $skip_value, true)) {
                    continue;
                }
            }
            
            $data[$request_key] = $request_value;
        }
        return $data;
    }

}