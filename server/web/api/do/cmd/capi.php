<?php
/**
 * CenterApi接口capi类文件
 *
 * @author dragonets
 * @package do
 * @subpackage cmd
 */

if (!defined('CV_ROOT')) {
    exit('No direct script access allowed');
}

/**
 * CenterApi接口capi操作类
 *
 * @author dragonets
 * @package do
 * @subpackage cmd
 */
class capi extends CDoBase
{

    /**
     * 构造函数，实例化的时候进行数据验证
     * post数据解密后存到了CenterAPI::$data中，可以直接使用
     */
    public function __construct()
    {
        parent::decrypt();
        // 关闭登录检查
        AdminLogin::$need_check = false;
        // 关闭权限检查
        AdminPermissions::$need_check = false;
        // 关闭日志记录
        AdminOperation::$need_record = false;
    }

    /**
     * 接收请求接口（示例）
     */
    public function api()
    {
        $decrypt_data = CenterAPI::$data;
        ResultParser::succ(Sign::encryptData(CenterAPI::$data));
    }

}