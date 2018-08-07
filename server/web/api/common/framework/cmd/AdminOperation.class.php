<?php
/**
 * admin_login_record后台用户组处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * admin_login_record后台用户组处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class AdminOperation extends CDbTableBase
{
    /**
     * 默认需要记录
     * @var boolean
     */
    public static $need_record = true;

    /**
     * 不需要记录的操作
     *
     * @var array
     */
    private static $except_uris = array(
        'admin.myoperation_list.get', // 我的操作获取接口，注意，此条不可删除，否则会死循环导致数据量指数增长
        'admin.operation_list.get', // 操作获取接口，注意，此条不可删除，否则会死循环导致数据量指数增长
        'admin.login',          // 登录
        'admin.is_logined',     // 登录验证接口
        'admin.logout',         // 退出登录
        'admin.menu',           // 菜单获取接口
        'common.platforms',     // 平台信息获取接口
        'common.admin_users',   // 用户信息获取接口
        'capi',                 // GS调用Center接口
    );


    /**
     * 简化记录的操作
     * 仅对操作成功有效，操作失败仍会全部记录
     *
     * @var array
     */
    private static $simplify_uris = array(
        'admin.mylogin_list.get',
        'admin.login_list.get',
        'admin.myoperation_list.get',
        'admin.account.get',
        'admin.permission_user.get',
        'admin.permission_role.get',
        'optools.servers.get',
        'admin.permission.get',
        'platform.manager.get',
        'server.manager.get',
        'server.plat.get',
        'admin.ip_white.get',
        'admin.ip_black.get',
        
    );

    

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'admin_operation';
        if (isset($this->s_db_default) && $this->s_db_default == 'http') {
            $this->s_db = CDbuser::getInstanceDbHttp($this->table);
        }
        else {
            $this->s_db = CDbuser::getInstanceDbPdo();
        }
    }

    /**
     * 插入DB数据
     *
     * @see CDbTableBase::insertToDbID
     * @param array $insert_values        
     * @see InsertValuesRule
     */
    function insertRecord()
    {
        if (!self::_isNeedRecord()) {
            return 0;
        }
        $admin_info = Adminlogin::$admin_uinfo;
        if (!$admin_info) {
            return 0;
        }
        $uirs = Api::getUri();
        $b = HttpParam::request('b');
        $uri = array_pop($uirs) . ($b ? ".$b" : '');
        $insert_values = array(
            'platid'    =>  (int)$admin_info['platid'],
            'userid'    =>  (int)$admin_info['id'],
            'tm'        =>  time(),
            'ip'        =>  HttpParam::server('REMOTE_ADDR'),
            'uri'       =>  $uri,
            'param'     =>  json_encode($_REQUEST)
        
        );
        $sql = Sql::insertInto($this->table)->values($insert_values);
        return parent::insertToDbID($sql);
    }

    /**
     * 插入DB数据
     *
     * @see CDbTableBase::insertToDbID
     * @param array $insert_values        
     * @see InsertValuesRule
     */
    function updateDbValues($res)
    {
        
        if (!self::_isNeedRecord()) { //不需要记录
            return 0;
        }
        $oper_id = (int)API::getOperId();
        if (!$oper_id) { //未获取到操作ID，不记录结果
            return;
        }
        if (self::_isSimplifyRecord() && $res['r']) { //简化记录且操作成功，不记录结果
            $record = '';
        }
        else { //完整记录
            $record = json_encode($res);
        }
        $sql = Sql::update($this->table)->set('res', $record)->set('success', $res['r'])->whereArgs(array('id' => $oper_id));
        return parent::updateToDb($sql);
    }

    /**
     * 是否需要记录操作
     *
     * @return boolean
     */
    private static function _isNeedRecord()
    {
        if (!self::$need_record) {
            // 忽略所有记录
            return false;
        }
        if (self::$except_uris) {
            $check_uris = Api::getUri();
            foreach (self::$except_uris as $except_uri) {
                if (in_array($except_uri, $check_uris)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 是否需要简化记录操作
     *
     * @return boolean
     */
    private static function _isSimplifyRecord()
    {
        if (self::$simplify_uris) {
            $check_uris = Api::getUri();
            foreach (self::$simplify_uris as $simplify_uri) {
                if (in_array($simplify_uri, $check_uris)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 查询操作记录
     *
     * @see CDbTableBase::insertToDbID
     * @param array $where_args
     *        查询条件
     * @param int $page
     *        第几页
     * @param int $num
     *        每页显示
     */
    function getOperationRecords($where_args, $page = 1, $num = 10)
    {
        $total = Sql::select('count(1) as sum')->from($this->table);
        $data = Sql::select("*")->from($this->table);
        if ($where_args) {
            $total = $total->whereArgs($where_args);
            $data = $data->whereArgs($where_args);
        }
        $total = $total->get($this->s_db);
        
        $page_total = ceil($total[0]['sum'] / $num);
        if ($page > $page_total && $page_total > 0) {
            $page = $page_total;
        }
        
        $data = $data->orderByArgs(array('tm' => Sql::$ORDER_BY_DESC))->limit(($page - 1) * $num, $num)->get($this->s_db);
        return Pagination::formatData($page, ceil($total[0]['sum'] / $num), $num, $total[0]['sum'], $data);
    }
}

