<?php
/**
 * admin_users后台用户处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * admin_users后台用户处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class AdminUsers extends CDbTableBase
{

    /**
     * 登录缓存过期时间（秒）
     *
     * @var int
     */
    const ADMIN_LOGIN_CACHE_TIME = 3600;

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'admin_users';
        if (isset($this->s_db_default) && $this->s_db_default == 'http') {
            $this->s_db = CDbuser::getInstanceDbHttp($this->table);
        }
        else {
            $this->s_db = CDbuser::getInstanceDbPdo();
        }
    }

    /**
     * 登录
     *
     * @param int $platid
     *        平台ID
     * @param string $loginname
     *        登录账号
     * @param string $loginpwd
     *        登录密码
     * @param number $loginpwd_is_crypted
     *        登录密码是否是加密密码
     * @param string $safekey
     *        登录安全码
     * @param number $safekey_is_crypted        
     * @return boolean|array
     */
    public function login($platid, $loginname, $loginpwd, $loginpwd_is_crypted = 0, $safekey = null, $safekey_is_crypted = 0)
    {
        $cache_key = "{$platid}_{$loginname}_admin_login";
        if (empty($loginpwd_is_crypted)) {
            $loginpwd = $this->getCryptedLoginpwd($platid, $loginname, $loginpwd);
        }
        if ($safekey && empty($safekey_is_crypted)) {
            $safekey = $this->getCryptedSafekey($platid, $loginname, $safekey);
        }
        
        $where_args = array();
        
        $where_args['platid'] = $platid;
        $where_args['loginname'] = $loginname;
        
        $db_res = Sql::select('*')->from($this->table)->whereArgs($where_args)->get($this->s_db);
        if (!$db_res || $loginpwd != $db_res[0]['loginpwd'] || $loginname != $db_res[0]['loginname'] || ($safekey && $safekey != $db_res[0]['safekey'])) {
            // 登录失败(无该登录用户，登录名密码不一致,安全码不正确)
            return false;
        }
        
        $cur_time = time();
        $cdatetime = date('Y-m-d H:i:s', $cur_time);
        
        // 更新最后一次登录时间、最后一次登录IP、当前登录token及token过期时间
        $set_args = array();
        $uinfo = $db_res[0];
        $uinfo['lastlogin'] = $set_args['lastlogin'] = $cur_time;
        $uinfo['ip']        = $set_args['ip'] = ClientIp::get();
        $uinfo['expire']    = $set_args['expire'] = $cur_time + CDbuser::CACHE_TIME;
        $uinfo['token']     = $set_args['token'] = md5($platid . ClientIp::get() . $cur_time) . '_' . $cdatetime . '-' . $uinfo['loginname'];
        // 管理员用户登录后生成token最大64字符
        if (sizeof($uinfo['token']) > 64) {
            $uinfo['token'] = $set_args['token'] = substr($uinfo['token'], 0, 64);
        }
        
        $update_res = Sql::update($this->table)->setArgs($set_args)->whereArgs($where_args)->exec($this->s_db);
        
        if ($update_res) {
            // 账号权限获取
            $permissions = $this->_getPermissions($uinfo['id']);
            $uinfo = array_merge($uinfo, $permissions);
            CDbuser::getInstanceMemcache()->set($cache_key, $uinfo, self::ADMIN_LOGIN_CACHE_TIME);
            
            return $uinfo;
        }
        else { // 更新失败，应该是在PDOException就被截获错误了
            return false;
        }
    }

    /**
     * 获取登录缓存信息
     *
     * @param string $loginname
     *        登录名称
     * @param int $platid
     *        登录账号所属平台ID
     * @return array|NULL
     */
    private function _getLoginedCache($loginname, $platid)
    {
        $cache_key = "{$platid}_{$loginname}_admin_login";
        $cache_val = CDbuser::getInstanceMemcache()->get($cache_key);
        if ($cache_val) {
            // 访问一次接口，延长有效期
            CDbuser::getInstanceMemcache()->set($cache_key, $cache_val, self::ADMIN_LOGIN_CACHE_TIME);
            return $cache_val;
        }
        else {
            return null;
        }
    }

    /**
     * 判断是否已经登录
     *
     * @return boolean|array
     */
    public function isLogined()
    {
        $session_id = HttpParam::cookie(AdminSession::SESSION_COOKIE_KEY);
        if (!$session_id) {
            setcookie(AdminSession::SESSION_COOKIE_KEY, AdminSession::createSessionId());
            return false;
        }
        
        $session = AdminSession::get($session_id);
        if ($session['token'] && $session['loginname']) {
            // 有登录session，检查session
            $admin_uinfo = $this->_getLoginedCache($session['loginname'], $session['platid']);
            if ($admin_uinfo && $admin_uinfo['token'] == $session['token'] && $admin_uinfo['expire'] > time() + 60) {
                return $admin_uinfo;
            }
            else {
                // 未通过，可能有其他人从其他地方使用相同账号重新登录了
                // 或者是刚清理过后台memcache缓存
                // 销毁session,重新登录
                AdminSession::del($session_id);
            }
        }
        return false;
    }

    /**
     * 获取账号权限信息
     *
     * @param array $admin_uinfo        
     */
    private function _getPermissions($admin_id)
    {
        $permissions = array('pri' => array(), 'pri_ext' => array());
        
        // 表初始化
        $db_admin_user_roles = CDbuser::getInstanceTable('AdminUserRoles');
        $db_admin_role_permissions = CDbuser::getInstanceTable('AdminRolePermissions');
        $db_admin_menu_trees = CDbuser::getInstanceTable('AdminMenuTrees');
        // for dev
        //$db_admin_user_roles = new AdminUserRoles();
        //$db_admin_role_permissions = new AdminRolePermissions();
        //$db_admin_menu_trees = new AdminMenuTrees();
        

        // 获取角色id数组
        $role_ids = $db_admin_user_roles->getRolesByUserId($admin_id);
        if (!empty($role_ids)) {
            $role_tree_ids = $db_admin_role_permissions->getPermissions($role_ids);
            if (!empty($role_tree_ids)) {
                $permissions['pri'] = $db_admin_menu_trees->getUriByTreeIds($role_tree_ids);
                $permissions['pri_ext'] = $db_admin_menu_trees->getUriBlankExt($permissions['pri']);
            }
        }
        
        return $permissions;
    }

    /**
     * 账号登出
     *
     * @return boolean
     */
    public function logout()
    {
        $session_id = HttpParam::cookie(AdminSession::SESSION_COOKIE_KEY);
        if ($session_id) {
            return AdminSession::del($session_id);
        }
        return true;
    }

    /**
     * 验证密码是否符合规则
     *
     * @param string $pwd        
     * @return boolean
     */
    public function validPwd($pwd)
    {
        $pwdRule = '/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])([\w\+=~!@#$%^&*]{5,})/';
        if (preg_match($pwdRule, $pwd)) {
            return true;
        }
        return false;
    }

    /**
     * 验证安全码是否符合规则
     *
     * @param string $safekey        
     * @return boolean
     */
    public function validSafekey($safekey)
    {
        $safekeyRule = '/^[a-zA-Z0-9]{4,6}$/';
        if (preg_match($safekeyRule, $safekey)) {
            return true;
        }
        return false;
    }

    /**
     * 更新后台用户信息
     *
     * @param int $platid
     *        所属平台ID
     * @param string $loginname
     *        账号
     * @param int $admin_id
     *        账号ID
     * @param array $update_args
     *        更新数组
     * @return boolean
     */
    public function updateUser($platid, $loginname, $admin_id, $update_args)
    {
        $cache_key = "{$platid}_{$loginname}_admin_login";
        
        $where_args = array('id' => $admin_id);
        // 清理整个platid下的用户信息
        $res = parent::updateWithCache($platid, $update_args, $where_args);
        if ($res) {
            // 清理登录token
            CDbuser::getInstanceMemcache()->delete($cache_key);
            return true;
        }
        return false;
    }

    /**
     * 删除后台用户
     *
     * @param int $platid        
     * @param string $loginname        
     * @param array $where_args        
     *
     * @return boolean
     */
    public function delUser($platid, $loginname, $where_args)
    {
        $cache_key = "{$platid}_{$loginname}_admin_login";
        
        // 清理整个platid下的用户信息
        $res = parent::delWithCache($platid, $where_args);
        if ($res) {
            // 清理登录token
            CDbuser::getInstanceMemcache()->delete($cache_key);
            return true;
        }
        return false;
    }

    public function addUser($platid, $insert_values)
    {
        // 清理整个platid下的用户信息
        $res = parent::insertWithCache($platid, $insert_values);
        if ($res) {
            return $res;
        }
        return false;
    }

    /**
     * 修改自己账号的密码/安全码
     *
     * @param string $newpwd
     *        新密码
     * @param string $newsk
     *        新安全码
     * @param array $admin_uinfo
     *        用户登录信息
     * @return boolean
     */
    public function modSecretSelf($newpwd, $newsk, $admin_uinfo)
    {
        // 准备修改的数据
        $update_args = array();
        if ($newpwd) {
            $newpwd_crypt = $this->getCryptedLoginpwd($admin_uinfo['platid'], $admin_uinfo['loginname'], $newpwd);
            $update_args['loginpwd'] = $newpwd_crypt;
        }
        if ($newsk) {
            $newsk_crypt = $this->getCryptedSafekey($admin_uinfo['platid'], $admin_uinfo['loginname'], $newsk);
            $update_args['safekey'] = $newsk_crypt;
        }
        if ($admin_uinfo['force_pwd']) {
            $update_args['force_pwd'] = 0;
        }
        return $this->updateUser($admin_uinfo['platid'], $admin_uinfo['loginname'], $admin_uinfo['id'], $update_args);
    }

    /**
     * 删除admin_user中敏感信息
     *
     * @param array $admin_uinfo        
     * @return array
     */
    public function delSecrets($admin_uinfo)
    {
        //绑定微信按钮显示
        if (!$admin_uinfo['wx_bind'] && 
            AdminSetting::$admin_settings['weixin_service_appid'] && 
            AdminSetting::$admin_settings['weixin_service_appsecret'] &&
            AdminSetting::$admin_settings['weixin_service_login_template_id'] &&
            AdminSetting::$admin_settings['weixin_service_game_admin_name'] &&
            AdminSetting::$admin_settings['weixin_service_callback_ip'] &&
            AdminSetting::$admin_settings['weixin_service_callback_seq']) {
            $admin_uinfo['wx_bind_button'] = 1;
        }
        else {
            $admin_uinfo['wx_bind_button'] = 0;
        }
        $secret_keys = array('loginpwd', 'safekey', 'lv', 'flag', 'pay', 'force_pwd', 'wx_bind');
        foreach ($secret_keys as $secret_key) {
            unset($admin_uinfo[$secret_key]);
        }
        return $admin_uinfo;
    }

    /**
     * 获取加密后的安全码
     *
     * @param int $platid
     *        平台ID
     * @param string $loginname
     *        登录用户名
     * @param string $safekey
     *        未加密的safekey
     * @return string
     */
    public function getCryptedSafekey($platid, $loginname, $safekey)
    {
        return crypt(md5($safekey), md5($platid . $loginname));
    }

    /**
     * 获取加密后的登录密码
     *
     * @param int $platid
     *        平台ID
     * @param string $loginname
     *        登录用户名
     * @param string $loginpwd
     *        未加密的登录密码
     * @return string
     */
    public function getCryptedLoginpwd($platid, $loginname, $loginpwd)
    {
        return crypt(md5($loginpwd), md5($platid . $loginname));
    }

    public function getIdNames()
    {
        $db_val = Sql::select('id,name')->from($this->table)->get($this->s_db);
        $index = parent::array_column($db_val, 'id');
        $db_val = array_combine($index, $db_val);
        return $db_val;
    }

    /**
     * 获取某个角色可以添加的用户列表信息（ID和NAME）
     *
     * @param int $role_id        
     * @return array
     */
    function getAddUsersIdNameByRoleId($role_id)
    {
        return Sql::select('id, name')->from($this->table)->where('id not in (select user_id from admin_user_roles where role_id = ?) and baoliu=0', $role_id)->get($this->s_db, 'id');
    }

    /**
     * 删除登录缓存
     *
     * @param
     *        $loginname
     * @param
     *        $platid
     * @return null
     */
    private function _delLoginedCache($loginname, $platid)
    {
        $cache_key = "{$platid}_{$loginname}_admin_login";
        $cache_val = CDbuser::getInstanceMemcache()->get($cache_key);
        if ($cache_val) {
            // 删除用户缓存
            CDbuser::getInstanceMemcache()->delete($cache_key);
            return $cache_val;
        }
        else {
            return null;
        }
    }

    public function delUserCache($loginname, $platid)
    {
        return $this->_delLoginedCache($loginname, $platid);
    }

    public function getUserById($id)
    {
        $db_val = Sql::select('loginname,platid')->from($this->table)->where('id=?', $id)->get($this->s_db);
        return $db_val;
    }

    public function getCommonInfos($platid)
    {
        $db_res = Sql::select('id, name, platid')->from($this->table);
        if (isset($platid)) {
            $db_res = $db_res->whereArgs(array('platid' => $platid));
        }
        $db_res = $db_res->get($this->s_db, 'id');
        return $db_res;
    }

    function getAdminUsers($where_args, $page = 1, $num = 10)
    {
        $total = Sql::select('count(1) as sum')->from($this->table);
        $data = Sql::select("id,platid,loginname,name,ip,lastlogin,regtm,force_pwd,baoliu")->from($this->table);
        if ($where_args) {
            $total = $total->whereArgs($where_args);
            $data = $data->whereArgs($where_args);
        }
        $total = $total->get($this->s_db);
        
        $page_total = ceil($total[0]['sum'] / $num);
        if ($page > $page_total && $page_total > 0) {
            $page = $page_total;
        }
        
        $data = $data->limit(($page - 1) * $num, $num)->get($this->s_db);
        return Pagination::formatData($page, ceil($total[0]['sum'] / $num), $num, $total[0]['sum'], $data);
    }
}

