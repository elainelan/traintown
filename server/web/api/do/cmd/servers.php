<?php
/**
 * 区服信息操作
 *
 * @author lbc
 * @package do
 * @subpackage cmd
 */

if (!defined('CV_ROOT')) {
    exit('No direct script access allowed');
}

/**
 * 区服信息操作类
 *
 * @author lbc
 * @package do
 * @subpackage cmd
 */
class servers extends CDoBase
{

    /**
     * 区服信息功能
     */
    public function manager()
    {
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get':
                $this->_manager_get();
                break;
            case 'download':
                $this->_manager_download();
                break;
            case 'manager':
                
                // 分平台账号禁止操作区服信息
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
    
    /**
     * 获取区服信息
     */
    private function _manager_get()
    {
        $request_conf_arys = array(
            'platid'    =>  array(
                'skip_check' => array(null, ''),
                'skip_value' => array(null, ''),
                array("regex", 'number', CErrorCode::PARAM_ERROR),
                array('rename', 'platids'),
            ),
            'type'    =>  array(
                'skip_check' => array(null, ''),
                'skip_value' => array(null, ''),
                array("regex", 'number', CErrorCode::PARAM_ERROR),
            ),
        );
        $where_args = FormData::getFormArgs($request_conf_arys);
        
        $page = (int)HttpParam::request('page');
        $num = (int)HttpParam::request('num');
        $orderby = HttpParam::request('orderby');
        
        $page = $page > 0 ? $page : 1;
        $num = $num > 0 ? $num : 10;
        $orderby_args = $orderby ? $orderby : array();
        
        // 分平台只能获取自身平台信息
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($admin_uinfo['platid']) {
            $where_args['platids'] = $admin_uinfo['platid'];
        }
        
        
        ResultParser::succ(CDbuser::getInstanceTable('GameServers')->getByCond($where_args, $page, $num, $orderby_args));
    }

    /**
     * 区服信息数据下载导出
     */
    private function _manager_download()
    {
        $search = array('type' => HttpParam::request('type'), 'platid' => HttpParam::request('platid'), 'theads' => HttpParam::request('theads'));

        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $search['platid'] = $admin_uinfo['platid'];
        }
        
        $db_game_servers = CDbuser::getInstanceTable('GameServers');
        //$db_game_servers = new GameServers(); // for dev
        $db_admin_platforms = CDbuser::getInstanceTable('AdminPlatforms');
        //$db_admin_platforms = new AdminPlatforms();   // for dev
        
        $where_args = array();
        if ($search['type'] !== "" && $search['type'] !== null) {
            $where_args['type'] = (int)$search['type'];
        }
        if (!empty($search['platid'])) {
            $where_args['platids'] = (int)$search['platid'];
        }

        // 分批次获取数据后写入文件
        $page = 1;      // 初始化页数，1
        $num = 3000;    // 每次获取3000条记录写入文件
        $rs = 1;        // 是否还有数据需要读取
        $file = 'servers_'.date('Y-m-d-H-i-s-ms',time()).'.csv';   // 导出文件（未压缩）
        ExportData::setCsvHeader($search['theads']); // 设置CSV导出标题
    
        do {
            $export_data = $db_game_servers->getByCond($where_args, $page, $num);
            $export_data['items'] = self::_manager_deal_servers_info($export_data['items']);
            if ($export_data['page_total']) { // 有数据
                // 数据处理
                // 获取对应platids的平台ID
                $platids_array = array();
                foreach ($export_data['items'] as $k => & $v) {
                    if ($v['platids']) {
                        $platids_array = array_unique(array_merge($platids_array, $v['platids']));
                    }
                }
                
                // 获取platids里面的平台信息
                $platforms_where_args = array('id' => array('IN' => $platids_array));
                $admin_platforms = $db_admin_platforms->getByCondData($platforms_where_args);
                
                // 数据转换（optm、platids、combine_tm）
                foreach ($export_data['items'] as &$v) {
                    // 将相关的数据库中的开服时间字段optm由时间戳格式转换为日期时间的格式
                    // 时间处理
                    $tm_keys = array('optm', 'combine_tm');
                    foreach ($tm_keys as $tm_key) {
                        if ($v[$tm_key]) {
                            $v[$tm_key] = date('Y-m-d H:i:s', $v[$tm_key]);
                        }
                    }
                
                    // 将字段platids数据由平台ID转换为平台的名称
                    if (!empty($v['platids'])) {
                        array_merge($platids_array, $v['platids']);
                        $platform_name_array = array();
                        foreach ($v['platids'] as $platid) {
                            if ($admin_platforms[$platid]['name']) {
                                array_push($platform_name_array, $admin_platforms[$platid]['name']);
                            }
                            else {
                                array_push($platform_name_array, $platid);
                            }
                        }
                        $v['platids'] = implode(',', $platform_name_array);
                    }
                }

                // 根据用户所选择的搜索项进行数据的筛选
                foreach ($export_data['items'] as &$item) {
                    $item_temp = array();
                    foreach($search['theads'] as $theads) {
                        array_push($item_temp,$item[$theads]);
                    }
                    $item = $item_temp;
                }
                
                // 存入数据
                ExportData::addCsv($file, $export_data['items']);
                
                // 指向下页
                ++$page;
                
                // 到达最后一页，跳出循环
                if ($export_data['page_total'] == $export_data['page_current']) {
                    $rs = 0;
                }
            }
            else {
                // 没有数据，跳出循环
                ResultParser::error(CErrorCode::EXPORT_DATA_NULL);
            }
        }
        while ($rs != 0);
    
        if ($export_data['page_total']) {
            // 尝试zip打包
            $zip_file = ExportData::zip(ExportData::$folder.$file);
            if ($zip_file !== false) {
                // 打包成功
                ResultParser::succ($zip_file);
            }
            // 打包失败
            ResultParser::succ(ExportData::$folder.$file);
        }
        ResultParser::error(ErrorCode::EXPORT_FAILED);
    }
    
    /**
     * 新增区服信息
     */
    private function _manager_add()
    {
        // 参数处理
        $insert_args = self::_manager_deal_params();
        
        // 获取区服id
        $sid = $insert_args['sid'];
        
        $db_game_servers = CDbuser::getInstanceTable('GameServers');
        //$db_game_servers = new GameServers(); // for dev

        // 验证新增的区服ID是否已经存在
        if ($db_game_servers->getValidServer($sid)) {
            ResultParser::error(CErrorCode::SID_EXIST);
        }
        
        // 准备执行插入数据库的相关操作
        $db_res = $db_game_servers->insertServer($sid, $insert_args);
        ResultParser::succ($db_res);
    }
    
    /**
     * 删除区服信息
     */
    private function _manager_del()
    {
        // 获取需要删除的区服SID
        $sid = (int)HttpParam::request('sid');
        if (empty($sid)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_game_servers = CDbuser::getInstanceTable('GameServers');
        //$db_game_servers = new GameServers(); // for dev
        
        // 删除条件
        $where_args = array('sid' => $sid);
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $where_args['platids'] = $admin_uinfo['platid'];
            // 如果是平台混服，删除条件是：sid=x and platids=x
            // 混服的platids是多个平台，条件不匹配，是无法删除的
        }
        
        $db_res = $db_game_servers->delWithCache($sid, $where_args);
        
        if (!empty($db_res->rows)) {
            ResultParser::succ();
        }
        
        ResultParser::error(CErrorCode::SID_DEL_ERROR);
    }
    
    /**
     * 修改区服信息
     */
    private function _manager_mod()
    {
        $get = HttpParam::request('get');
        if (!empty($get)) {
            // 修改区服的时候获取区服信息
            self::_manager_mod_get();
        }
        // 提交修改区服信息
        self::_manager_mod_mod();
    }
    
    /**
     * 提交修改区服信息
     */
    private function _manager_mod_mod()
    {
        
        $update_args = self::_manager_deal_params();
        
        // 根据区服SID的选择来决定是哪一条区服信息需要进行修改
        $where_args = array('sid' => $update_args['sid']);
        
        $db_game_servers = CDbuser::getInstanceTable('GameServers');
        //$db_game_servers = new GameServers(); // for dev
        
        // 准备执行修改数据库的相关操作
        $db_res = $db_game_servers->updateWithCache($update_args['sid'], $update_args, $where_args);
        
        ResultParser::succ($db_res);
    }
    
    /**
     * 修改区服页面，初始化时，根据sid来获取单个区服数据详情信息的功能
     * @param int $sid
     */
    private function _manager_mod_get()
    {
        $sid = (int)HttpParam::request('sid');
        if (empty($sid)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_game_servers = CDbuser::getInstanceTable('GameServers');
        //$db_game_servers = new GameServers(); // for dev
        
        $db_res = $db_game_servers->getBySids(array($sid));
        if (!$db_res) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        $db_res = self::_manager_deal_servers_info($db_res);
        
        // platids处理
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            $db_res_new = array();
            foreach ($db_res as $k => &$v) {
                if (in_array($admin_uinfo['platid'], $v['platids'])) {
                    $db_res_new[$k] = $v;
                }
            }
            $db_res = $db_res_new;
        }
        
        // 时间处理与combine_sid的处理
        $tm_keys = array('optm', 'combine_tm', 'combine_sid');
        foreach ($tm_keys as $tm_key) {
            $db_res[$sid][$tm_key] = $db_res[$sid][$tm_key] ? date('Y-m-d H:i:s', $db_res[$sid][$tm_key]) : "";
        }
        
        ResultParser::succ($db_res);
    }

    /**
     * 新增与修改区服信息表单数据获取与整理（参数处理）
     * @return array array('sid'=>x,'xx'=>'xx'...) 
     */
    private function _manager_deal_params()
    {
        // 可以操作的数据key数组
        $server_info_keys = self::_manager_get_server_info_keys_by_permission();
        
        // 区服类型
        $type = (int)HttpParam::request('type');
        if (!in_array($type, array(0, 1, 2, 3))) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        
        // 根据不同的区服类型来获取必填项的相关数据信息
        
        // 公共必填项
        $params_key_array = array('sid', 'srv_name', 'srv_ip', 'type');

        // 线上服、历史服、测试服必填项
        if (in_array($type, array(0,2,3))) {
            $params_key_array = array_merge($params_key_array, array(
                                    'optm',
                                    'msg', 'close',
                                    'def', 'new', 'recomm', 'srv_status',
                                    'game_port', 'srv_conf_port', 'srv_prt_port',
                                    'gamedb_name', 'gamedb_ip', 'gamedb_user', 'gamedb_pwd',
                                    'logdb_name', 'logdb_ip', 'logdb_user', 'logdb_pwd',
                                    'paydb_name', 'paydb_ip', 'paydb_user', 'paydb_pwd',
                                    'cq_root', 'static_url', 'web_static', 'platids'
                                ));
        }
        
        // 已合服、历史服必填项
        if (in_array($type, array(1,2))) {
            array_push($params_key_array, 'combine_sid', 'combine_tm');   
        }
        
        // 将表单提交而来的数据赋值于对应的变量之中，并同时验证相关的必填参数
        foreach ($params_key_array as $params_key) {
            // 获取参数值
            $$params_key = HttpParam::request($params_key);
            
            // 取整参数处理
            if (in_array($params_key, array('sid', 'type', 'combine_sid', 'def', 'new', 'recomm', 'srv_status'))) {
                $$params_key = (int)$$params_key;
            }
            
            // 非空判断
            if (strlen($$params_key) == 0 && !in_array($params_key, $server_info_keys)) {
                ResultParser::error(CErrorCode::PARAM_ERROR);
            }
        }
        
        // 选填项处理
        $ext_keys = array('srv_ip2', 'iface_his','kfdb_name', 'kfdb_ip', 'kfdb_user', 'kfdb_pwd','opver');
        foreach ($ext_keys as $ext) {
            $ext_http = HttpParam::request($ext);
            if (isset($ext_http)) {
                array_push($params_key_array, $ext);
                $$ext = $ext_http;
            }
        }
        
        // 时间字段转换处理（开服时间、合服时间）
        $tm_keys = array('optm', 'combine_tm');
        foreach ($tm_keys as $key) {
            if ($$key && strtotime($$key)) {
                $$key = strtotime($$key);
            }
            else {
                $$key = 0;
            }
        }
        
        // 路径需要/结尾处理（cq_root、static_url、web_static)
        $path_keys = array('cq_root', 'static_url', 'web_static');
        foreach ($path_keys as $path) {
            $$path = substr($$path, -1) == '/' ? $$path : $$path.'/';
        }
        
        // 所属平台platids处理
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if (!empty($admin_uinfo['platid'])) {
            // 分平台用户，添加的server的platid只能是本平台下的
            $platids = $admin_uinfo['platid'];
        }
        else {
            sort($platids);
            $platids = implode(',', $platids);
        }
        
        // 数据库操作信息数组
        foreach ($params_key_array as $params_key) {
            if (in_array($params_key, $server_info_keys)) {
                $datas[$params_key] = $$params_key;
            }
        }
        
        return $datas;
    }
    
    /**
     * 处理需要显示的区服信息
     * @param array $servers_info 区服信息
     * @param array $customer_server_info_keys 自定义字段数组
     */
    private function _manager_deal_servers_info($servers_info, $customer_server_info_keys=array())
    {
        $server_info_keys = self::_manager_get_server_info_keys_by_permission();
        
        // 根据权限提取所需数据
        $return_servers_info = array();
        foreach ($servers_info as $k => & $v) {
            foreach ($server_info_keys as $server_info_key) {
                if ($customer_server_info_keys) {
                    if (in_array($server_info_key, $customer_server_info_keys)) {
                        $return_servers_info[$k][$server_info_key] = $v[$server_info_key];
                    }
                }
                else {
                    $return_servers_info[$k][$server_info_key] = $v[$server_info_key];
                }
                
            }
        }
        
        return $return_servers_info;
    }
    
    /**
     * 根据权限处理区服信息
     * @return Ambigous <multitype:string , multitype:>
     */
    private function _manager_get_server_info_keys_by_permission()
    {
        $server_info_keys = array(
            'sid', 'type', 'close', 'msg', 'optm', 'opver',
            'combine_sid', 'combine_tm', 'srv_ip', 'srv_ip2',
            'srv_name', 'platids',
            'def', 'new', 'recomm', 'srv_status',
        );
        
        // 相关信息获取权限检查
        $permission_actions = array(
            'config'    =>  array(
                'game_port', 'srv_conf_port', 'srv_prt_port',
                'gamedb_ip', 'gamedb_user', 'gamedb_pwd', 'gamedb_name',
                'logdb_ip', 'logdb_user', 'logdb_pwd', 'logdb_name',
                'paydb_ip', 'paydb_user', 'paydb_pwd', 'paydb_name',
                'kfdb_ip', 'kfdb_user', 'kfdb_pwd', 'kfdb_name',
                'cq_root', 'static_url', 'web_static', 'iface_his',
            ),
        
        );
        foreach ($permission_actions as $action => $action_server_info_keys) {
            $permission = AdminPermissions::hasActionPermission($action);
            if ($permission) {
                $server_info_keys = array_unique(array_merge($server_info_keys, $action_server_info_keys));
            }
        }
        return $server_info_keys;
    }
}