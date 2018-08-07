<?php
/**
 * dapi类文件
 *
 * @author dragonets
 * @package do
 * @subpackage dmd
 */

if (!defined('CV_ROOT')) {
    exit('No direct script access allowed');
}

/**
 * dapi操作类
 *
 * @author dragonets
 * @package do
 * @subpackage dmd
 */
class dapi extends DDoBase
{
    /**
     * 构造函数，实例化的时候进行数据验证
     * post数据解密后存到了DBAPI::$data中，可以直接使用
     */
    public function __construct()
    {
        parent::decrypt();
    }
    
    /**
     * 接收请求接口
     */
    public function test()
    {
        $decrypt_data = DBAPI::$data;
        ResultParser::succ(Sign::encryptData(DBAPI::$data));
    }

    public function api()
    {
        if (is_array(DBAPI::$data['cmds'])) {
            $res = array();
            foreach (DBAPI::$data['cmds'] as $cmd_key => $cmd) {
                switch ($cmd['cmd_type']) {
                    case APICMD::SQL_SELECT:
                        $res[$cmd_key] = DBAPISql::get($cmd['cmd_para']);
                        break;
                    case APICMD::SQL_MOD:
                        $res[$cmd_key] = DBAPISql::exec($cmd['cmd_para']);
                        break;
                    default:
                        break;
                }
            }
            ResultParser::succ(Sign::encryptData($res));
        }
        ResultParser::error(DErrorCode::API_MISS_CMDS);
    }
}