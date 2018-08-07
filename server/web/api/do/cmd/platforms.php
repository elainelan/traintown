<?php
/**
 * 平台信息操作
 *
 * @author lbc
 * @package do
 * @subpackage cmd
 */

if (!defined('CV_ROOT')) {
    exit('No direct script access allowed');
}

/**
 * 平台信息操作类
 *
 * @author dragonets
 * @package do
 * @subpackage cmd
 */
class platforms extends CDoBase
{
    /**
     * 获取平台信息列表
     * (接收参数方式 _GET or _POST) 
     * @return array('r'=> '1 | 0' ,'parameter'=>'') r：查询结果成功|失败 parameter：返回的参数
     */
    public function manager()
    {
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $this->_manager_get();
                break;
            case 'manager':
                
                // 分平台账号禁止操作平台信息
                $admin_uinfo = AdminLogin::$admin_uinfo;
                if (!empty($admin_uinfo['platid'])) {
                    ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
                }
                
                $b = HttpParam::request('b');
                switch ($b) {
                    case 'add':
                        $this->_manager_add();
                        break;
                    case 'del':
                        $this->_manager_del();
                        break;
                    case 'mod':
                        $this->_manager_mod();
                        break;
                }
                break;
        }
        ResultParser::error(CErrorCode::PARAM_ERROR);
    }

    private function _manager_get()
    {
        $request_conf_arys = array(
            'platid'    =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->number()->rename('id')
        );
        $where_args = FormData::getFormArgs($request_conf_arys);
        // 分平台只能获取自身平台信息
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($admin_uinfo['platid']) {
            $where_args['id'] = $admin_uinfo['platid'];
        }
        
        $select_strings = 
            //基础信息
            'id,name,game_sig,pay_sig,close_tm'.
            // 功能开关
            ',ptoolbar,safe,pfcm,onbeforeunload,forcein,automis'.
            // 其他功能
            ',mini,mini_login,mini_ver,supervip_config,pay_adr'.
            // URL链接地址
            ',web,bbs,gm_url,cm_url,newcard_url,phone_url'.
            // 手游配置
            ',sj_test,sj_pid'
            ;
        
        $this->getPage(CDbuser::getInstanceTable('AdminPlatforms'), $select_strings, $where_args);
    }

    private function _manager_add()
    {
        $request_conf_arys = array(
            'id'                =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)
                                    ->number()
                                    ->func(array('db'=>'AdminPlatforms', 'getAvailablePlatId')),
            
            'name'              =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)
                                    ->length(array(1, 11))
                                    ->func(array('db'=>'AdminPlatforms', 'getAvailablePlatName')),
            
            'game_sig'          =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->auto(array($this, 'make_md5')),
            
            'pay_sig'           =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->auto(array($this, 'make_md5')),
            
        
            'ptoolbar'          =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
            'safe'              =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
            'pfcm'              =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
            'onbeforeunload'    =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
        
            'web'               =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
            'bbs'               =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
            'gm_url'            =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
            'cm_url'            =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
            'newcard_url'       =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
            'fcm'               =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
            'mini'              =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
            'mini_login'        =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
            'mini_ver'          =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
            'phone_url'         =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
            
            'close_tm'          =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->transform('strtotime'),
        
            'forcein'           =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
            'automis'           =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
        
            'sj_test'           =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 2000)),
            'sj_pid'            =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 2000)),
            'muids2_config'     =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 2000)),
            'supervip_config'   =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 2000)),
            'pay_adr'           =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(0, 255)),
        );
        
        $insert_data = FormData::getFormArgs($request_conf_arys);
        
        $this->addWithCacheID(CDbuser::getInstanceTable('AdminPlatforms'), $insert_data, $insert_data['id']);
    }
    
    
    private function _manager_del()
    {
        $db_admin_platforms = CDbuser::getInstanceTable('AdminPlatforms');
        $request_conf_arys = array(
            'id'    =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->number()->func(array('db'=>'AdminPlatforms', 'getAvailablePlatId', 'reserve'=>true), CErrorCode::PLATFORM_ID_ERROR)
        );
        
        // 分平台只能处理自身平台信息
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($admin_uinfo['platid']) {
            $where_args['id'] = $admin_uinfo['platid'];
        }
        
        $where_args = FormData::getFormArgs($request_conf_arys);
        
        $this->delWithCache($db_admin_platforms, $where_args, $where_args['id']);
    }
    
    private  function _manager_mod()
    {
        $request_conf_arys = array(
            'id'                =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)
                                    ->number()
                                    ->func(array('db'=>'AdminPlatforms', 'getAvailablePlatId'), CErrorCode::PLATFORM_ID_ERROR, true)
        );
        $where_args = FormData::getFormArgs($request_conf_arys);
        // 分平台只能处理自身平台信息
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($admin_uinfo['platid']) {
            $where_args['id'] = $admin_uinfo['platid'];
        }
        
        $db_admin_platforms = CDbuser::getInstanceTable("AdminPlatforms");
        $db_res = $db_admin_platforms->getWithCache($where_args['id']);
        $this_platform_name = $db_res[$where_args['id']]['name'];
        
        $request_conf_arys = array(
            'name'              =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)
                                    ->skip_check(array($this_platform_name))
                                    ->skip_value(array($this_platform_name))
                                    ->length(array(1, 11))
                                    ->func(array('db'=>'AdminPlatforms', 'getAvailablePlatName')),
            
            'game_sig'          =>  FormDataHelper::init(ErrorCode::PARAM_ERROR),
            
            'pay_sig'           =>  FormDataHelper::init(ErrorCode::PARAM_ERROR),
            
        
            'ptoolbar'          =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
            'safe'              =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
            'pfcm'              =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
            'onbeforeunload'    =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
        
            'web'               =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
            'bbs'               =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
            'gm_url'            =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
            'cm_url'            =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
            'newcard_url'       =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
            'fcm'               =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
            'mini'              =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
            'mini_login'        =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
            'mini_ver'          =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
            'phone_url'         =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
            
            'close_tm'          =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->transform('strtotime'),
        
            'forcein'           =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
            'automis'           =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0, 1)),
        
            'sj_test'           =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 2000)),
            'sj_pid'            =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 2000)),
            'muids2_config'     =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 2000)),
            'supervip_config'   =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 2000)),
            'pay_adr'           =>  FormDataHelper::init(ErrorCode::PARAM_ERROR)->skip_check(array(null))->skip_value(array(null))->length(array(0, 255)),
        );
        $update_data = FormData::getFormArgs($request_conf_arys);
        
        $this->updateWithCache(CDbuser::getInstanceTable('AdminPlatforms'), $update_data, $where_args, $where_args['id']);
    }
    
    private function _manager_mod_get($id)
    {
        $db_admin_platforms = CDbuser::getInstanceTable('AdminPlatforms');
        //$db_admin_platforms = new AdminPlatforms(); // for dev
    
        $db_res = $db_admin_platforms->getWithCache($id);

        if($db_res[$id]['close_tm'] != 0) {
            $db_res[$id]['close_tm'] = date('Y-m-d H:i:s',$db_res[$id]['close_tm']);    //将平台关服时间数据由时间戳转换为显示用的格式
        } 
        else {
            $db_res[$id]['close_tm'] = '';
        }

        ResultParser::succ($db_res);
    }


}