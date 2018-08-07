<?php
/**
 * admin类文件
 *
 * @author dragonets
 * @package do
 * @subpackage cmd
 */

if (!defined('CV_ROOT')) {
    exit('No direct script access allowed');
}

/**
 * 后台admin操作类
 *
 * @author dragonets
 * @package do
 * @subpackage cmd
 */
class admin extends CDoBase
{

    /**
     * 后台登录
     * 参数：
     * platid：登录平台id
     * loginname：登录账号
     * loginpwd：登录密码
     * safekey：登录安全码
     */
    public function login()
    {
        // 参数获取
        $platid = (int)HttpParam::request('platid');
        $loginname = HttpParam::request('loginname');
        $loginpwd = HttpParam::request('loginpwd');
        $safekey = HttpParam::request('safekey');
        $session_id = HttpParam::cookie(AdminSession::SESSION_COOKIE_KEY);
        if (empty($session_id)) {
            $session_id = AdminSession::createSessionId();
            setcookie(AdminSession::SESSION_COOKIE_KEY, $session_id);
        }
        
        // 参数基本验证
        if (!$loginname || !$loginpwd) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        // =safekey验证：如果是非白名单IP，需要safekey
        if (!AdminIpAudit::isWhiteIp($platid) && !$safekey) {
            ResultParser::error(CErrorCode::ADMIN_LOGIN_NEED_SAFEKEY);
        }
        
        // 访问控制
        $rules_white = array(array('tm' => 1, 'cnt' => 61));
        $rules_nomal = array(array('tm' => 1, 'cnt' => 11), array('tm' => 2, 'cnt' => 17));
        AdminIpAudit::ipAudit($platid, $rules_white, $rules_nomal);
        
        // 参数有效验证
        // =platid验证
        if ($platid) {
            $db_admin_platforms = CDbuser::getInstanceTable('AdminPlatforms');
            $platform = $db_admin_platforms->getValidPlatform($platid);
            if (!$platform) {
                ResultParser::error(CErrorCode::PLATFORM_ID_ERROR);
            }
        }
        
        // 登录验证
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        $admin_user_info = $db_admin_users->login($platid, $loginname, $loginpwd, 0, $safekey, 0);
        if ($admin_user_info) {
            // 登录成功
            $session = array('platid' => $platid, 'token' => $admin_user_info['token'], 'loginname' => $admin_user_info['loginname']);
            AdminSession::set($session_id, $session);
            
            $db_admin_login_record = CDbuser::getInstanceTable('AdminLoginRecord');
            $insert_values = array('platid' => $platid, 'admin_userid' => $admin_user_info['id'], 'loginip' => ClientIp::get(), 'logintime' => time());
            //                 'errorcnt'      =>  $count,
            

            $db_admin_login_record->insertDbValues($insert_values);
            if ($admin_user_info['wx_bind']) {
                AdminMessage::login(
                    array(
                        'wxuser'    => $admin_user_info['wx_bind'],
                        'loginname' => $admin_user_info['loginname'],
                        'logintime' => $insert_values['logintime'],
                        'loginip'   => $insert_values['loginip'],
                    )
                );
            }
            
            // 如果需要强制修改密码，返回错误
            if ($admin_user_info['force_pwd']) {
                ResultParser::error(CErrorCode::ADMIN_USER_FORCE_PWD);
            }
            
            // 删除敏感信息后返回给客户端登录信息
            $res_data = $db_admin_users->delSecrets($admin_user_info);
            ResultParser::succ($res_data);
        }
        else {
            // 登录失败处理
            ResultParser::error(CErrorCode::ADMIN_USER_LOGIN_FAIL);
        }
    }

    /**
     * 后台登出
     * 参数：
     * 无
     */
    public function logout()
    {
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        if ($db_admin_users->logout()) {
            ResultParser::succ(null);
        }
        ResultParser::error(CErrorCode::ADMIN_LOGOUT_FAIL);
    }

    /**
     * 我的密码（修改密码接口）
     * 参数：
     * oldpwd：原密码
     * newpwd：修改后的密码
     * oldsk：原安全码
     * newsk：修改后的安全码
     */
    public function modpwd_self()
    {
        // 验证是否登录状态
        $admin_uinfo = AdminLogin::$admin_uinfo;
        
        // 获取参数
        $oldpwd = HttpParam::request('oldpwd');
        $newpwd = HttpParam::request('newpwd');
        
        $oldsk = HttpParam::request('oldsk');
        $newsk = HttpParam::request('newsk');
        
        // 验证参数
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        //$db_admin_users = new AdminUsers(); // for dev
        // =判断是否需要修改密码，并进行参数验证
        $valid_pwd = $admin_uinfo['force_pwd'] || $newpwd || $oldpwd;
        if ($valid_pwd) {
            if (!$newpwd || !$oldpwd) {
                ResultParser::error(CErrorCode::PARAM_ERROR);
            }
            $oldpwd_crypt = $db_admin_users->getCryptedLoginpwd($admin_uinfo['platid'], $admin_uinfo['loginname'], $oldpwd);
            if ($oldpwd_crypt != $admin_uinfo['loginpwd']) {
                ResultParser::error(CErrorCode::ADMIN_OLDPWD_ERROR);
            }
            if (!$db_admin_users->validPwd($newpwd)) {
                ResultParser::error(CErrorCode::ADMIN_PWD_VALID_FAIL);
            }
        }
        // =判断是否需要修改安全码，并进行参数验证
        $valid_sk = $newsk || $oldsk;
        if ($valid_sk) {
            if (!$newsk || !$oldsk) {
                ResultParser::error(CErrorCode::PARAM_ERROR);
            }
            $oldsk_crypt = $db_admin_users->getCryptedSafekey($admin_uinfo['platid'], $admin_uinfo['loginname'], $oldsk);
            if ($oldsk_crypt != $admin_uinfo['safekey']) {
                ResultParser::error(CErrorCode::ADMIN_OLDSAFEKEY_ERROR);
            }
            if (!$db_admin_users->validSafekey($newsk)) {
                ResultParser::error(CErrorCode::ADMIN_SAFEKEY_VALID_FAIL);
            }
        }
        
        $res = $db_admin_users->modSecretSelf($newpwd, $newsk, $admin_uinfo);
        
        ResultParser::succ($res);
    }

    /**
     * 判断是否已经登录
     */
    public function is_logined()
    {
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        //$db_admin_users = new AdminUsers(); // for dev
        

        ResultParser::succ($db_admin_users->delSecrets(AdminLogin::$admin_uinfo));
    }

    /**
     * 获取用户有权限的菜单列表
     */
    public function menu()
    {
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (empty($admin_uinfo['pri'])) {
            ResultParser::succ();
        }
        $db_admin_menu_trees = CDbuser::getInstanceTable('AdminMenuTrees');
        ResultParser::succ($db_admin_menu_trees->getMenuTreesByAdminUser($admin_uinfo['pri']));
    }
    
    /**
     * 微信绑定
     */
    public function bind()
    {
        if (!AdminSetting::$admin_settings['weixin_service_appid'] || !AdminSetting::$admin_settings['weixin_service_appsecret']) {
            ResultParser::error(ErrorCode::CONF_ERROR);
        }
        $admin_uinfo = AdminLogin::$admin_uinfo;
        $wechat = new CVWeChatService(AdminSetting::$admin_settings['weixin_service_appid'], AdminSetting::$admin_settings['weixin_service_appsecret']);
        $evt_type = 1;
        $scene_str = AdminSetting::$admin_settings['weixin_service_callback_seq'] . ',' . $evt_type . ',' . $admin_uinfo['id'];
        $param = array(
            'scene' =>  array('scene_str'=>$scene_str)
        );
        $url = $wechat->getQrcodeUrl($param);
        
        if ($url) {
            ResultParser::succ($url);
        }
        ResultParser::error(ErrorCode::PARAM_ERROR);
    }

    /**
     * 我的登录
     */
    public function mylogin_list()
    {
        $this->_get_login_list(1);
    }
    
    /**
     * 登录记录
     */
    public function login_list()
    {
        $this->_get_login_list(0);
    }
    
    /**
     * 获取登录记录
     * @param int $myself 1：我的登录；0：所有人的登录记录
     */
    private function _get_login_list($myself = 0)
    {
        $request_conf_arys = array(
            'a'             =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)
                                ->skip_check(array('get', 'download'))
                                ->skip_value(array('get', 'download'))
                                ->length('-1'),
            
            'admin_userid'  =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->number(),
            'loginip'       =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(1, 16))->transform(array($this, 'ip_format')),
            'time'          =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->transform(array($this, 'between_time_format'))->rename('logintime'),
            'platid'        =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->number(),
        );
        
        $where_args = FormData::getFormArgs($request_conf_arys);
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($myself) {
            $where_args['admin_userid'] = $admin_uinfo['id'];
        }
        if ($admin_uinfo['platid']) {
            $where_args['platid'] = $admin_uinfo['platid'];
        }
        
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $select_string = 'id, platid, admin_userid, loginip, logintime';
                $this->getPage(CDbuser::getInstanceTable('AdminLoginRecord'), $select_string, $where_args);
                break;
            case 'download':
                $select_string = 'id, platid, admin_userid, loginip, from_unixtime(logintime) as logintime';
                $this->downloadPage(CDbuser::getInstanceTable('AdminLoginRecord'), $select_string, $where_args, $myself ? 'my_login_list' : 'login_list');
                break;
        }
        ResultParser::error(CErrorCode::PARAM_ERROR);
    }
    
    private function _get_operation_list($myself=0)
    {
        $request_conf_arys = array(
            'a'         =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)
                                ->skip_check(array('get', 'download'))
                                ->skip_value(array('get', 'download'))
                                ->length('-1'),
            
            'userid'    =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->number(),
            'ip'        =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(1, 16))->transform(array($this, 'ip_format')),
            'uri'       =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(1, 16))->transform(array($this, 'uri_format')),
            'tm'        =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->transform(array($this, 'between_time_format')),
            'res'       =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0,1))->rename('success'),
            'platid'    =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->number(),
        );
        $where_args = FormData::getFormArgs($request_conf_arys);
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($myself) {
            $where_args['userid'] = $admin_uinfo['id'];
        }
        if ($admin_uinfo['platid']) {
            $where_args['platid'] = $admin_uinfo['platid'];
        }
        
        $select_string = '*';
        
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $this->getPage(CDbuser::getInstanceTable('AdminOperation'), $select_string, $where_args);
                break;
            case 'download':
                $this->downloadPage(CDbuser::getInstanceTable('AdminOperation'), $select_string, $where_args, $myself ? 'my_operation_list' : 'operation_list');
                break;
        }
        ResultParser::error(CErrorCode::PARAM_ERROR);
    }

    /**
     * 权限管理->用户角色表
     */
    public function permission_user()
    {
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $b = HttpParam::request('b');
                switch ($b) {
                    case 'admin_users':
                        $this->_permission_get_admin_users();
                        break;
                    default:
                        $this->_permission_user_get();
                }
                break;
            case 'manager':
                $b = HttpParam::request('b');
                switch ($b) {
                    case 'add_get_roles':
                        $this->_permission_user_add_get_roles();
                        break;
                    case 'add':
                        $this->_permission_user_add_roles();
                        break;
                    case 'del':
                        $this->_permission_user_del_role();
                        break;
                }
                break;
        }
        ResultParser::error(CErrorCode::PARAM_ERROR);
    }

    /**
     * 获取用户包含角色列表的信息
     */
    private function _permission_user_get()
    {
        $page = (int)HttpParam::request('page');
        $num = (int)HttpParam::request('num');
        $page = $page > 0 ? $page : 1;
        $num = $num > 0 ? $num : 10;
        
        $search = array();
        
        // 账号ID
        $user_id = HttpParam::request('user_id');
        if (!empty($user_id)) {
            $search['admin_users.id'] = $user_id;
        }
        
        // 账号平台ID
        $platid = HttpParam::request('platid');
        if (strlen($platid) > 0 ) {
            $search['admin_users.platid'] = $platid;
        }
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $search['admin_users.platid'] = $admin_uinfo['platid'];
        }
        
        $db_admin_user_roles = CDbuser::getInstanceTable('AdminUserRoles');
        $result = $db_admin_user_roles->getUserRole($search, $page, $num);
        if ($result['items']) {
            foreach ($result['items'] as $k => & $value) {
                if ($value['idname'] != '') {
                    $value['idname'] = explode('|', $value['idname']);
                }
                else {
                    $value['idname'] = array();
                }
            }
        }
        ResultParser::succ($result);
    }

    /**
     * @deprecated 2017/07/25
     */
    private function _permission_get_admin_users()
    {
        $this->__get_admin_users();
    }
    /**
     * 把该用户的角色移除
     */
    private function _permission_user_del_role()
    {
        $user_id = (int)HttpParam::request('user_id');
        $role_id = (int)HttpParam::request('role_id');
        
        if (empty($user_id) || empty($role_id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        // uid有效性检查
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        //$db_admin_users = new AdminUsers(); // for dev
        
        $gm_info = $db_admin_users->getAdminUsers(array('id'=>$user_id));
        if (empty($gm_info['items_total'])) {
            // uid不存在
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if ($gm_info['items'][0]['baoliu'] == 1) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        
        $db_admin_user_roles = CDbuser::getInstanceTable('AdminUserRoles');
        $result = $db_admin_user_roles->delUserRole($user_id, $role_id);

        $this->_del_user_cache($user_id); // 删除用户缓存

        ResultParser::succ();
    }

    /**
     * 用户加入多个角色
     */
    private function _permission_user_add_roles()
    {
        $user_id = (int)HttpParam::request('uid');
        $role_ids = HttpParam::request('role_ids');

        if (empty($user_id) || empty($role_ids)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        // uid有效性检查
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        //$db_admin_users = new AdminUsers(); // for dev
        
        $gm_info = $db_admin_users->getAdminUsers(array('id'=>$user_id));
        if (empty($gm_info['items_total'])) {
            // uid不存在
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if ($gm_info['items'][0]['baoliu'] == 1) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        
        // 插入数据
        $db_admin_user_roles = CDbuser::getInstanceTable('AdminUserRoles');
        //$db_admin_user_roles = new AdminUserRoles(); // for dev
        
        $result = $db_admin_user_roles->addUserRoles($user_id, $role_ids);
        $this->_del_user_cache($user_id); // 删除用户缓存

        ResultParser::succ();
    }

    /**
     * 获取该用户可以加入的角色列表
     */
    private function _permission_user_add_get_roles()
    {
        $user_id = HttpParam::request('user_id');
        if (empty($user_id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_admin_roles = CDbuser::getInstanceTable('AdminRoles');
        //$db_admin_roles = new AdminRoles(); // for dev
        $result = $db_admin_roles->getAddRolesIdNameByUserId($user_id);
        ResultParser::succ($result);
    }

    /**
     * 权限管理->角色用户表
     */
    public function permission_role()
    {
        $admin_uinfo = AdminLogin::$admin_uinfo; // 分平台用户没有权限管理角色用户
        if (!empty($admin_uinfo['platid'])) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $b = HttpParam::request('b');
                switch ($b){
                    case 'admin_roles':
                        $this->_permission_get_admin_roles();
                        break;
                    default:
                        $this->_permission_role_get();
                        break;
                }
            case 'manager':
                $b = HttpParam::request('b');
                switch ($b) {
                    case 'add_get_users':
                        $this->_permission_role_add_get_users();
                        break;
                    case 'add':
                        $this->_permission_role_add_users();
                        break;
                    case 'del':
                        $this->_permission_role_del_user();
                        break;
                }
                break;
        }
        ResultParser::error(CErrorCode::PARAM_ERROR);
    }

    /**
     * 获取角色组包含用户列表信息
     */
    private function _permission_role_get()
    {
        $page = (int)HttpParam::request('page');
        $num = (int)HttpParam::request('num');
        $page = $page > 0 ? $page : 1;
        $num = $num > 0 ? $num : 10;
        $search = array();

        $role_id = HttpParam::request('role_id');
        if (!empty($role_id)) {
            $search['admin_roles.id'] = $role_id;
        }

        $db_admin_user_roles = CDbuser::getInstanceTable('AdminUserRoles');

        $result = $db_admin_user_roles->getRoleUser($search, $page, $num);
        if ($result['items']) {
            foreach ($result['items'] as $k => & $value) {
                if ($value['uidnames'] != '') {
                    $value['uidnames'] = explode('|', $value['uidnames']);
                }
                else {
                    $value['uidnames'] = array();
                }
            }
        }
        ResultParser::succ($result);
    }
    private function _permission_get_admin_roles(){
        $db_admin_roles = new AdminRoles();
        $db_res = $db_admin_roles->getRoles();
        ResultParser::succ($db_res);
    }
    /**
     * 把用户从角色组移除
     */
    private function _permission_role_del_user()
    {
        $user_id = (int)HttpParam::request('user_id');
        $role_id = (int)HttpParam::request('role_id');
        
        if (empty($user_id) || empty($role_id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        // uid有效性检查
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        //$db_admin_users = new AdminUsers(); // for dev
        
        $gm_info = $db_admin_users->getAdminUsers(array('id'=>$user_id));
        if (empty($gm_info['items_total'])) {
            // uid不存在
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if ($gm_info['items'][0]['baoliu'] == 1) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        
        $db_admin_user_roles = CDbuser::getInstanceTable('AdminUserRoles');
        $result = $db_admin_user_roles->delUserRole($user_id, $role_id);

        $this->_del_user_cache($user_id); // 删除用户缓存

        ResultParser::succ();
    }

    /**
     * 把多个用户加入指定的角色组中
     */
    private function _permission_role_add_users()
    {
        $user_ids = HttpParam::request('user_ids');
        $role_id = (int)HttpParam::request('role_id');
        
        if (empty($user_ids) || empty($role_id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        // uid有效性检查
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        //$db_admin_users = new AdminUsers(); // for dev
        
        $gm_infos = $db_admin_users->getAdminUsers(array('id'=>array('IN' => $user_ids)));
        if (empty($gm_infos['items_total'])) {
            // uid不存在
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        $user_ids = array();
        foreach ($gm_infos['items'] as $gm_info) {
            if ($gm_info['baoliu'] != 1) {
                // 剔除保留账号
                $user_ids[] = $gm_info['id'];
            }
        }
        
        $db_admin_user_roles = CDbuser::getInstanceTable('AdminUserRoles');
        $result = $db_admin_user_roles->addRoleUsers($user_ids, $role_id);

        foreach ($user_ids as $user_id){
            $this->_del_user_cache($user_id); // 删除用户缓存
        }

        ResultParser::succ();
    }

    /**
     * 获取可以加入角色的用户列表
     */
    private function _permission_role_add_get_users()
    {
        $role_id = HttpParam::request('role_id');
        if (empty($role_id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        // $db_admin_users = new AdminUsers(); // for dev
        $result = $db_admin_users->getAddUsersIdNameByRoleId($role_id);
        ResultParser::succ($result);
    }
    /**
     *  根据用户id删除该用户缓存
     * @param $user_id
     * @return void
     */

    private function _del_user_cache($user_id){
        $db_admin_users =  CDbuser::getInstanceTable('AdminUsers');
        $user = $db_admin_users->getUserById($user_id);
        $db_admin_users->delUserCache($user[0]['loginname'],$user[0]['platid']);
    }
    /**
     * 权限管理->角色组管理
     *
     * @return void
     */
    public function permission()
    {
        $admin_uinfo = AdminLogin::$admin_uinfo; // 分平台用户没有权限管理角色用户
        if (!empty($admin_uinfo['platid'])) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $b = HttpParam::request('b');
                switch ($b) {
                    case 'admin_roles':
                        $this->_permission_get_admin_roles();
                        break;
                    default:
                        $this->_permission_get();
                        break;
                }
            case 'manager':
                $b = HttpParam::request('b');
                switch ($b) {
                    case 'treeids': // 获取权限菜单id
                        $this->_permission_treeids();
                        break;
                    case 'update': // 角色赋权后更新方法
                        $this->_permission_update();
                        break;
                    case 'del': // 删除角色组
                        $this->_permission_del();
                        break;
                    case 'add': // 新增角色组
                        $this->_permission_add();
                        break;
                    case 'check':
                        $this->_permission_check();
                        break;
                    case 'mod':
                        $this->_permission_mod();
                        break;
                    default:
                        ResultParser::error(CErrorCode::PARAM_ERROR);
                }
                break;
            default:
                ResultParser::error(CErrorCode::PARAM_ERROR);
        }
    }
    
    private function _permission_get()
    {
        $page = (int)HttpParam::request('page');
        $num = (int)HttpParam::request('num');
        $page = $page > 0 ? $page : 1;
        $num = $num > 0 ? $num : 10;
        $search = array();
        $role_id = HttpParam::request('role_id');
        if (!empty($role_id)) {
            $search['admin_roles.id'] = $role_id;
        }
        $db_admin_role_permissions = CDbuser::getInstanceTable('AdminRolePermissions');
        $result = $db_admin_role_permissions->getRoleTreeids($search, $page, $num);
        if ($result['items']) {
            foreach ($result['items'] as $k => & $value) {
                if ($value['treeids'] != '') {
                    $value['treeids'] = explode('|', $value['treeids']);
                }
                else {
                    $value['treeids'] = array();
                }
            }
        }
        ResultParser::succ($result);
    }
    
    private function _permission_update()
    {
        $role_id = HttpParam::request('role_id');
        
        if (empty($role_id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_admin_roles = CDbuser::getInstanceTable('AdminRoles');
        $db_admin_role_permissions = CDbuser::getInstanceTable('AdminRolePermissions');
        $db_admin_user_roles = CDbuser::getInstanceTable('AdminUserRoles');
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        
        
        //$db_admin_roles = new AdminRoles(); // for dev
        //$db_admin_role_permissions = new AdminRolePermissions(); // for dev
        //$db_admin_user_roles = new AdminUserRoles(); // for dev
        //$db_admin_users = new AdminUsers(); // for dev
        
        
        // 判断role_id
        $db_res = $db_admin_roles->getWithCache($role_id);
        if (empty($db_res)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if ($db_res[$role_id]['baoliu'] == 1) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        
        $oldtreeids = HttpParam::request('oldtreeids');
        $newtreeids = HttpParam::request('newtreeids');
        if ($oldtreeids) {
            $addids = array_diff($newtreeids, $oldtreeids);
        }
        else {
            $addids = $newtreeids;
        }
        if ($newtreeids) {
            $delids = array_diff($oldtreeids, $newtreeids);
        }
        else {
            $delids = $oldtreeids;
        }
        
        // 清理权限组缓存
        $result = $db_admin_role_permissions->updatePermission($role_id, $addids, $delids);
        
        // 清理后台用户登录缓存
        $admin_infos = $db_admin_user_roles->getLoginnameByRoleId($role_id);
        if ($admin_infos) {
            foreach ($admin_infos as $admin_info) {
                $db_admin_users->delUserCache($admin_info['loginname'], $admin_info['platid']);
            }
        }
        
        ResultParser::succ();
    }

    /**
     * 获取权限树 treeids
     *
     * @return void
     */
    private function _permission_treeids()
    {
        $db_admin_menu_trees = CDbuser::getInstanceTable('AdminMenuTrees');
        $result = $db_admin_menu_trees->getTrees();
        ResultParser::succ($result);
    }

    /**
     *  删除一个角色，及其所有权限 ， admin_role_permission admin_user_role admin_role  拥有该角色的用户也要重新登录
     * @return void
     */
    private function _permission_del()
    {
        $role_id = HttpParam::request('role_id');
        if (empty($role_id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_admin_roles = CDbuser::getInstanceTable('AdminRoles');
        $db_admin_role_permissions = CDbuser::getInstanceTable('AdminRolePermissions');
        $db_admin_users_roles =  CDbuser::getInstanceTable('AdminUserRoles');
        $db_admin_users =  CDbuser::getInstanceTable('AdminUsers');
        
        //$db_admin_roles = new AdminRoles(); // for dev
        //$db_admin_role_permissions = new AdminRolePermissions(); // for dev
        //$db_admin_users_roles = new AdminUserRoles(); // for dev
        //$db_admin_users = new AdminUsers(); // for dev
        
        // 判断role_id
        $db_res = $db_admin_roles->getWithCache($role_id);
        if (empty($db_res)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if ($db_res[$role_id]['baoliu'] == 1) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        
        // 删除角色权限表
        $result = $db_admin_role_permissions->delRole($role_id);

        // 删除角色表
        $result1 = $db_admin_roles->delRole($role_id);

        // 删除用户缓存
        $users = $db_admin_users_roles->getLoginnameByRoleId($role_id);
        foreach ($users as $user){
            $db_admin_users->delUserCache($user['loginname'],$user['platid']);
        }

        // 删除用户角色表
        $result2 = $db_admin_users_roles->delUserRole(0,$role_id);  // user_id为0 就是所有user

        ResultParser::succ();
    }

    /**
     * 添加角色
     * @return void
     */
    private  function _permission_add(){
        $data = array();
        $data['name']= HttpParam::request('role_name');
        $data['`desc`'] = HttpParam::request('role_desc');
        if(empty($data['name']) || empty($data['`desc`'])){
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        $db_admin_roles = CDbuser::getInstanceTable('AdminRoles');
        
        // 检查是否已经有重复角色名
        if ($db_admin_roles->getRoleById('name', $data['name'])) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        $result = $db_admin_roles->addRole($data);
        ResultParser::succ();
    }

    /**
     * 检查角色id是否存在
     * @return void
     */
    private function _permission_check()
    {
        $key = 'name';
        $value = HttpParam::request('role_name');
        $db_admin_roles = CDbuser::getInstanceTable('AdminRoles');
        $result = $db_admin_roles->getRoleById($key, $value);
        
        $type = HttpParam::request('data_type');
        
        switch ($type) {
            case 'bootstrapValidator':
                if ($result[0]['id']) {
                    $result = '{ "valid": false }';
                }
                else {
                    $result = '{ "valid": true }';
                }
                echo $result;
                break;
            default:
                ResultParser::succ($result);
                break;
        }
    }

    /**
     * 修改角色名称和描述
     * @return void
     */
    private function _permission_mod()
    {
        $id = HttpParam::request('id');
        $name = HttpParam::request('name');
        $desc = HttpParam::request('desc');
        if (empty($id) || empty($name)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_admin_roles = CDbuser::getInstanceTable('AdminRoles');
        
        // 判断role_id
        $db_res = $db_admin_roles->getWithCache($id);
        if (empty($db_res)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if ($db_res[$id]['baoliu'] == 1) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        
        $result = $db_admin_roles->updateRoleById($id, $name, $desc);
        ResultParser::succ();
    }
    
    
    
    public function ip_white()
    {
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $this->_ip_white_get();
                break;
            case 'manager':
                $b = HttpParam::request('b');
                switch ($b) {
                    case 'add':
                        $this->_ip_white_add();
                        break;
                    case 'del':
                        $this->_ip_white_del();
                        break;
                    case 'mod':
                        $this->_ip_white_mod();
                        break;
                }
                break;
        }
        ResultParser::error(CErrorCode::PARAM_ERROR);
        
    }
    
    private function _ip_white_get()
    {
        $page = (int)HttpParam::request('page');
        $num = (int)HttpParam::request('num');
        $page = $page > 0 ? $page : 1;
        $num = $num > 0 ? $num : 10;
        $search = array(
            'ip'    =>  HttpParam::request('ip'),
            'platid'=>  HttpParam::request('platid'),
            'notes' =>  HttpParam::request('notes'),
        );
        
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $search['platid'] = $admin_uinfo['platid'];
        }
        
        $search_patten = implode('', $search);
        
        $db_admin_ip_white = CDbuser::getInstanceTable('AdminIpWhite');
        //$db_admin_ip_white = new AdminIpWhite(); // for dev
        
        $db_res = $db_admin_ip_white->getWithCache(null);
        if ($search_patten !== '' && $db_res) {
            $return_db_res = array();
            foreach ($db_res as $k => & $v) {
                if (strlen($search['ip']) > 0 && stripos($v['ip'], $search['ip']) === false) {
                    continue;
                }
                if (strlen($search['notes']) > 0 && strpos($v['notes'], $search['notes']) === false) {
                    continue;
                }
                if (strlen($search['platid']) > 0 && $v['platid'] != $search['platid']) {
                    continue;
                }
                $return_db_res[$k] = $v;
            }
            $db_res = $return_db_res;
        }
        // TODO: 如果白名单IP数量多，需要改成：直接从数据库中获取分页数据
        ResultParser::succ(Pagination::chunkData($db_res, $page, $num));
    }
    
    private function _ip_white_add()
    {
        $platid = (int)HttpParam::request('platid');
        $ip = HttpParam::request('ip');
        $notes = HttpParam::request('notes');
        
        $ip_rule = '/^[0-9]{1,3}\.[0-9|\*]{1,3}\.[0-9|\*]{1,3}\.[0-9|\*]{1,3}$/';
        if (empty($ip) || !preg_match($ip_rule, $ip)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_admin_ip_white = CDbuser::getInstanceTable('AdminIpWhite');
        //$db_admin_ip_white = new AdminIpWhite(); // for dev
        
        $insert_values = array(
            'platid'    =>  $platid,
            'ip'        =>  $ip,
            'notes'     =>  $notes,
        );
        
        // 分平台判断
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $insert_values['platid'] = $admin_uinfo['platid'];
        }
        
        // 重复ip判断
        $ip_whites = $db_admin_ip_white->getWithCache(null);
        if ($ip_whites[$insert_values['ip']]) {
            ResultParser::error(CErrorCode::IP_EXIST_IN_OTHERS);
        }
        
        $db_res = $db_admin_ip_white->insertWithCache($platid, $insert_values);
        ResultParser::succ($db_res);
    }
    
    private function _ip_white_del()
    {
        $id = (int)HttpParam::request('id');
        $platid = (int)HttpParam::request('platid');
        if (empty($id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_admin_ip_white = CDbuser::getInstanceTable('AdminIpWhite');
        //$db_admin_ip_white = new AdminIpWhite(); // for dev
        
        $where_args = array(
            'id'    =>  $id
        );
        
        // 分平台判断
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $where_args['platid'] = $admin_uinfo['platid'];
        }
        
        $db_res = $db_admin_ip_white->delWithCache($platid, $where_args);
        if (!empty($db_res->rows)) {
            ResultParser::succ();
        }
        ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
    }

    private  function _ip_white_mod()
    {
        $id = HttpParam::request('id');
        $notes = HttpParam::request('notes');
        $platid = (int)HttpParam::request('platid');
        if (empty($id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if (!isset($platid)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        $db_admin_ip_white = CDbuser::getInstanceTable('AdminIpWhite');
        $update_args = array(
            'notes' => $notes
        );
        $where_args = array(
            'id' => $id
        );
        
        // 分平台判断
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $where_args['platid'] = $admin_uinfo['platid'];
        }
        
        $db_res = $db_admin_ip_white->updateWithCache($platid,$update_args,$where_args);
        if (!empty($db_res->rows)) {
            ResultParser::succ();
        }
        ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
    }
    public function ip_black()
    {
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $this->_ip_black_get();
                break;
            case 'manager':
                $b = HttpParam::request('b');
                switch ($b) {
                    case 'add':
                        $this->_ip_black_add();
                        break;
                    case 'del':
                        $this->_ip_black_del();
                        break;
                    case 'mod':
                        $this->_ip_black_mod();
                        break;
                }
                break;
        }
        ResultParser::error(CErrorCode::PARAM_ERROR);
    }
    
    private function _ip_black_get()
    {
        $page = (int)HttpParam::request('page');
        $num = (int)HttpParam::request('num');
        $page = $page > 0 ? $page : 1;
        $num = $num > 0 ? $num : 10;
        $search = array(
            'ip'    =>  HttpParam::request('ip'),
            'platid'=>  HttpParam::request('platid'),
            'notes' =>  HttpParam::request('notes'),
        );
        
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $search['platid'] = $admin_uinfo['platid'];
        }
        
        $search_patten = implode('', $search);
        
        $admin_uinfo = AdminLogin::$admin_uinfo;
        $db_admin_ip_black = CDbuser::getInstanceTable('AdminIpBlack');
        //$db_admin_ip_black = new AdminIpBlack(); // for dev
        
        $db_res = $db_admin_ip_black->getWithCache(null);
        if ($search_patten !== '' && $db_res) {
            $return_db_res = array();
            foreach ($db_res as $k => & $v) {
                if (strlen($search['ip']) > 0 && stripos($v['ip'], $search['ip']) === false) {
                    continue;
                }
                if (strlen($search['notes']) > 0 && strpos($v['notes'], $search['notes']) === false) {
                    continue;
                }
                if (strlen($search['platid']) > 0 && $v['platid'] != $search['platid']) {
                    continue;
                }
                $return_db_res[$k] = $v;
            }
            $db_res = $return_db_res;
        }
        // TODO: 如果黑名单IP数量多，需要改成：直接从数据库中获取分页数据
        ResultParser::succ(Pagination::chunkData($db_res, $page, $num));
    }
    
    private function _ip_black_add()
    {
        $platid = (int)HttpParam::request('platid');
        $ip = HttpParam::request('ip');
        $notes = HttpParam::request('notes');
        
        $ip_rule = '/^[0-9]{1,3}\.[0-9|\*]{1,3}\.[0-9|\*]{1,3}\.[0-9|\*]{1,3}$/';
        if (empty($ip) || !preg_match($ip_rule, $ip)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_admin_ip_black = CDbuser::getInstanceTable('AdminIpBlack');
        //$db_admin_ip_black = new AdminIpBlack(); // for dev
        
        $insert_values = array(
            'platid'    =>  $platid,
            'ip'        =>  $ip,
            'notes'     =>  $notes,
        );
        
        // 分平台判断
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $insert_values['platid'] = $admin_uinfo['platid'];
        }
        
        // 重复ip判断
        $ip_blacks = $db_admin_ip_black->getWithCache(null);
        if ($ip_blacks[$insert_values['ip']]) {
            ResultParser::error(CErrorCode::IP_EXIST_IN_OTHERS);
        }
        
        $db_res = $db_admin_ip_black->insertWithCache($platid, $insert_values);
        ResultParser::succ($db_res);
    }
    
    private function _ip_black_del()
    {
        $id = HttpParam::request('id');
        $platid = (int)HttpParam::request('platid');
        if (empty($id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if (!isset($platid)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_admin_ip_black = CDbuser::getInstanceTable('AdminIpBlack');
        // $db_admin_ip_black = new AdminIpBlack(); // for dev
        
        $where_args = array(
            'id'    =>  $id
        );
        
        // 分平台判断
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $where_args['platid'] = $admin_uinfo['platid'];
        }
        
        $db_res = $db_admin_ip_black->delWithCache($platid, $where_args);
        if (!empty($db_res->rows)) {
            ResultParser::succ();
        }
        ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
    }
    
    private function _ip_black_mod()
    {
        $id = HttpParam::request('id');
        $notes = HttpParam::request('notes');
        $platid = (int)HttpParam::request('platid');
        if (empty($id)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if (!isset($platid)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        $db_admin_ip_black = CDbuser::getInstanceTable('AdminIpBlack');
        $update_args = array(
            'notes' => $notes
        );
        $where_args = array(
            'id' => $id
        );
        // 分平台判断
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $where_args['platid'] = $admin_uinfo['platid'];
        }
        
        $db_res = $db_admin_ip_black->updateWithCache($platid,$update_args,$where_args);
        if (!empty($db_res->rows)) {
            ResultParser::succ();
        }
        ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
    }
    
    public function account()
    {
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $b = HttpParam::request('b');
                switch ($b) {
                    default:
                        $this->_account_get();
                        break;
                }
                break;
            case 'manager':
                $b = HttpParam::request('b');
                switch ($b) {
                    case 'add':
                        $this->_account_add();
                        break;
                    case 'del':
                        $this->_account_del();
                        break;
                    case 'manager':
                        $this->_account_manager();
                        break;
                }
                break;
        }
        ResultParser::error(CErrorCode::PARAM_ERROR);
    }
    
    private function _account_get()
    {
        $request_conf_arys = array(
            'admin_userid'  =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->number()->rename('id'),
            'ip'            =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(1, 16))->transform(array($this, 'ip_format')),
            'lastlogin'     =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->transform(array($this, 'between_time_format')),
            'regtm'         =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->transform(array($this, 'between_time_format')),
            'platid'        =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->number(),
            'force_pwd'     =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->in(array(0,1))
        );
        
        $where_args = FormData::getFormArgs($request_conf_arys);
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($admin_uinfo['platid']) {
            $where_args['platid'] = $admin_uinfo['platid'];
        }
        
        $select_string = 'id, platid, loginname, name, ip, lastlogin, regtm, force_pwd, baoliu';
        
        $this->getPage(CDbuser::getInstanceTable('AdminUsers'), $select_string, $where_args);
    }
    
    private function _account_add()
    {
        $request_conf_arys = array(
            'platid'    =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->number(),
            'force_pwd' =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->in(array(0,1)),
            'name'      =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->length(array(1,32)),
            'loginname' =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->length(array(1,32)),
            'loginpwd'  =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->length(array(1,32))->func(array('db'=>'AdminUsers', 'validPwd')),
            'safekey'   =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->length(array(1,21))->func(array('db'=>'AdminUsers', 'validSafekey')),
        );
        
        $insert_data = FormData::getFormArgs($request_conf_arys);
        
        // 分平台
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($admin_uinfo['platid']) {
            $insert_data['platid'] = $admin_uinfo['platid'];
        }
        
        // loginname重复检查
        $db_admin_users = CDbuser::getInstanceTable("AdminUsers");
        $gm_info = $db_admin_users->getAdminUsers(array('loginname'=>$insert_data['loginname'], 'platid'=>$insert_data['platid']));
        if ($gm_info['items_total']) {
            ResultParser::error(CErrorCode::ADMIN_USER_EXIST);
        }
        
        // 密码加密
        $insert_data['loginpwd'] = $db_admin_users->getCryptedLoginpwd($insert_data['platid'], $insert_data['loginname'], $insert_data['loginpwd']);
        $insert_data['safekey'] = $db_admin_users->getCryptedSafekey($insert_data['platid'], $insert_data['loginname'], $insert_data['safekey']);
        
        // 注册时间
        $insert_data['regtm'] = time();
        
        // 插入数据
        $insert_id = $db_admin_users->addUser($insert_data['platid'], $insert_data);
        if ($insert_id) {
            ResultParser::succ($insert_id);
        }
        ResultParser::error(CErrorCode::ADMIN_USER_ADDED_FAIL);
    }
    
    private function _account_del()
    {
        $request_conf_arys = array(
            'id'    =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->number(),
        );
        
        $where_args = FormData::getFormArgs($request_conf_arys);
        
        // 获取待删除账号信息
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        //$db_admin_users = new AdminUsers(); // for dev
        
        $gm_info = $db_admin_users->getAdminUsers($where_args);
        if (empty($gm_info['items_total'])) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if ($gm_info['items'][0]['baoliu'] == 1) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        
        $platid = $gm_info['items'][0]['platid'];
        $loginname = $gm_info['items'][0]['loginname'];
        $admin_id = $where_args['id'];
        
        // 分平台权限判断
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($admin_uinfo['platid']) {
            // 分平台的账号管理，只能删除本平台用户
            if ($admin_uinfo['platid'] != $platid) {
                ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
            }
        }
        
        // 数据删除
        $res = $db_admin_users->delUser($platid, $loginname, $where_args);
        if ($res) {
            // 删除权限组数据
            $db_admin_user_roles = CDbuser::getInstanceTable('AdminUserRoles');
            //$db_admin_user_roles = new AdminUserRoles(); // for dev
            $db_admin_user_roles->delRolesByUserId($admin_id);
            ResultParser::succ($admin_id);
        }
        ResultParser::error(CErrorCode::DB_ERROR);
    }
    
    private function _account_manager()
    {
        $request_conf_arys = array(
            'reset_gm_id'    =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->number()->rename('id'),
        );
        $where_args = FormData::getFormArgs($request_conf_arys);
        
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        //$db_admin_users = new AdminUsers(); // for dev
        
        $reset_gm_info = $db_admin_users->getAdminUsers($where_args);
        if (empty($reset_gm_info['items_total'])) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        if ($reset_gm_info['items'][0]['baoliu'] == 1) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        
        $platid = $reset_gm_info['items'][0]['platid'];
        $loginname = $reset_gm_info['items'][0]['loginname'];
        $admin_id = $where_args['id'];
        
        // 分平台权限判断
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($admin_uinfo['platid']) {
            // 分平台的账号管理，只能管理本平台用户
            if ($admin_uinfo['platid'] != $platid) {
                ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
            }
        }
        
        
        $request_conf_arys = array(
            'reset_force_pwd'   => FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->number()->rename('force_pwd'),
            'resetpwd'          => FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(1,32))->func(array('db'=>'AdminUsers', 'validPwd'))->rename('loginpwd'),
            'reset_sk'          => FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->length(array(1,21))->func(array('db'=>'AdminUsers', 'validSafekey'))->rename('safekey'),
        );
        
        $update_args = FormData::getFormArgs($request_conf_arys);
        if ($update_args['loginpwd']) {
            $update_args['loginpwd'] = $db_admin_users->getCryptedLoginpwd($platid, $loginname, $update_args['loginpwd']);
        }
        if ($update_args['safekey']) {
            $update_args['safekey'] = $db_admin_users->getCryptedSafekey($platid, $loginname, $update_args['safekey']);
        }
        
        $res = $db_admin_users->updateUser($platid, $loginname, $admin_id, $update_args);
        if ($res) {
            ResultParser::succ($admin_id);
        }
        ResultParser::error(CErrorCode::DB_ERROR);
    }


    /**
     * 我的操作
     */
    public function myoperation_list()
    {
        $this->_get_operation_list(1);
    }
    

    /**
     * 所有操作
     */
    public function operation_list()
    {
        $this->_get_operation_list(0);
    }
    
    /**
     * 配置管理
     */
    public function settings()
    {
        $admin_uinfo = AdminLogin::$admin_uinfo;
        // 分平台用户没有权限管理配置
        if (!empty($admin_uinfo['platid'])) {
            ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
        }
        
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $b = HttpParam::request('b');
                switch ($b) {
                    default:
                        $this->_settings_get();
                        break;
                }
                break;
            case 'manager':
                $b = HttpParam::request('b');
                switch ($b) {
                    case 'manager':
                        $this->_settings_manager();
                        break;
                }
                break;
        }
        ResultParser::error(CErrorCode::PARAM_ERROR);
    }
    
    private function _settings_get()
    {
        ResultParser::succ(AdminSetting::$admin_settings);
    }
    
    private function _settings_manager()
    {
        $id = 1;
        
        $request_conf_arys = array(
            'weixin_service_appid'      =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value(),
            'weixin_service_appsecret'  =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value(),
            'weixin_service_login_template_id'  =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value(),
            'weixin_service_game_admin_name'    =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value(),
            'weixin_service_callback_ip'        =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value(),
            'weixin_service_callback_seq'       =>  FormDataHelper::init(CErrorCode::PARAM_ERROR)->skip_check()->skip_value()->number(),
            
        );
        
        $insert_data = FormData::getFormArgs($request_conf_arys);
        
        $update_args = array('settings'=>json_encode($insert_data), 'id'=>$id);
        
        $db_admin_settings = CDbuser::getInstanceTable('AdminSettings');
        // $db_admin_settings = new AdminSettings(); // for dev
        
        $where_args = array('id'=>$id);
        $get_res = $db_admin_settings->getWithCache($id);
        if ($get_res[$id]) {
            unset($update_args['id']);
            $res = $db_admin_settings->updateWithCache($id, $update_args, $where_args);
            if ($res) {
                ResultParser::succ();
            }
        }
        else {
            $res = $db_admin_settings->insertWithCache($id, $update_args);
            if ($res) {
                ResultParser::succ();
            }
        }
        
        ResultParser::error(CErrorCode::DB_ERROR);
    }
    
}