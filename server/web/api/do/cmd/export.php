<?php
/**
 * 数据库表导出操作
 *
 * @author lbc
 * @package do
 * @subpackage cmd
 */

if (!defined('CV_ROOT')) {
    exit('No direct script access allowed');
}

/**
 * 数据库表导出操作类
 *
 * @author dragonets
 * @package do
 * @subpackage cmd
 */
class export extends CDoBase
{
    /**
     * PHP导出超时时间
     * int $_export_time_out
     */
    private $_export_time_out = 300;
    
    /**
     * 导出表数据
     */
    public function table()
    {
        $a = HttpParam::request('a');
        switch ($a) {
            case 'get_conf':
                $this->_get_conf();
                break;
            case 'download':
                $this->_download_table_data();
                break;
        }
        ResultParser::error(ErrorCode::PARAM_ERROR);
    }

    /**
     * 获取数据库配置
     */
    private function _get_conf()
    {
        //获取区服配置
        $db_game_server = CDbuser::getInstanceTable('GameServers');
        $where_args['type'] = array('IN'=>array(0));
        $servers = $db_game_server->getWithCache('type0', $where_args, 'sid');
        krsort($servers);
        foreach ($servers as $server) {
            $game_db = CVDbPdo::getInstance($server['gamedb_ip'], $server['gamedb_name'], $server['gamedb_user'], $server['gamedb_pwd']);
            $game_table = $game_db->query('show tables')->fetchAll(PDO::FETCH_ASSOC);
            
            $log_db = CVDbPdo::getInstance($server['logdb_ip'], $server['logdb_name'], $server['logdb_user'], $server['logdb_pwd']);
            $log_table = $log_db->query('show tables')->fetchAll(PDO::FETCH_ASSOC);
            
            $pay_db = CVDbPdo::getInstance($server['paydb_ip'], $server['paydb_name'], $server['paydb_user'], $server['paydb_pwd']);
            $pay_table = $pay_db->query('show tables')->fetchAll(PDO::FETCH_ASSOC);

            $kf_table = array();
            if ($server['kfdb_ip']) {
                $kf_db = CVDbPdo::getInstance($server['kfdb_ip'], $server['kfdb_name'], $server['kfdb_user'], $server['kfdb_pwd']);
                $kf_table = $kf_db->query('show tables')->fetchAll(PDO::FETCH_ASSOC);
            }
            if ($game_table && $log_db && $pay_db) {
                $conf = array('db'=>array('gamedb', 'logdb', 'paydb'), 'table'=>array());
                foreach ($game_table as $table) {
                    $v = array(
                        'table_name'=>  array_pop($table),
                        'db_type'   =>  'gamedb'
                    );
                    $conf['table'][] = $v;
                }
                foreach ($log_table as $table) {
                    $v = array(
                        'table_name'=>  array_pop($table),
                        'db_type'   =>  'logdb'
                    );
                    $conf['table'][] = $v;
                }
                foreach ($pay_table as $table) {
                    $v = array(
                        'table_name'=>  array_pop($table),
                        'db_type'   =>  'paydb'
                    );
                    $conf['table'][] = $v;
                }
                if ($kf_db) {
                    $conf['db'][] = 'kfdb';
                    foreach ($kf_table as $table) {
                    $v = array(
                        'table_name'=>  array_pop($table),
                        'db_type'   =>  'kfdb'
                    );
                        $conf['table'][] = $v;
                    }
                }
                ResultParser::succ($conf);
                break;
            }
           
        }
    }
    
    /**
     * 导出
     */
    private function _download_table_data()
    {
        $db_type = HttpParam::request('db_type');
        if (!in_array($db_type, array('gamedb', 'logdb', 'paydb', 'kfdb'))) {
            ResultParser::error(ErrorCode::PARAM_ERROR);
        }
        $table_name = HttpParam::request('table_name');
        if (!$table_name) {
            ResultParser::error(ErrorCode::PARAM_ERROR);
        }
        $sids = HttpParam::request('sids');
        if (!$sids) {
            ResultParser::error(ErrorCode::PARAM_ERROR);
        }
        
        $path = CV_ROOT . ExportData::$folder . $db_type . '_' . $table_name . '_' . date('Y_m_d_H_i_s_ms') . '/';
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                ResultParser::error(ErrorCode::EXPORT_FOLDER_CREATE_FAILED);
            }
        }
        $lock_key = 'download_table_data';
        
        //设置PHP超时
        ini_set('max_execution_time', $this->_export_time_out);
        $mem = CDbuser::getInstanceMemcache();
        if (!$mem->lock($lock_key, 1, $this->_export_time_out, 1)){
            ResultParser::error(ErrorCode::EXPORTING_OTHER_DATA);
        }

        //获取区服配置
        $db_game_server = CDbuser::getInstanceTable('GameServers');
        $where_args['type'] = array('IN'=>array(0));
        $servers = $db_game_server->getWithCache('type0', $where_args, 'sid');

        foreach($sids as $sid => $selected) {
            if ($selected && isset($servers[$sid]) && isset($servers[$sid][$db_type.'_ip'])) {
                $file = $path . $servers[$sid]['srv_name'] . '[' . $sid . ']' . '.csv';
                $db = CVDbPdo::getInstance($servers[$sid][$db_type.'_ip'], $servers[$sid][$db_type.'_name'], $servers[$sid][$db_type.'_user'], $servers[$sid][$db_type.'_pwd']);
                $res = Sql::select('*')->from($table_name)->export($db, $file);
            }
        }
        $zip_file = substr($path, 0, -1) . '.zip';
        if (exec("zip -j {$zip_file} {$path}* ")) {
            $mem->unlock($lock_key);
            ResultParser::succ(substr($zip_file, strlen(CV_ROOT)));
        }
        else{
            $mem->unlock($lock_key);
            ResultParser::error(ErrorCode::EXPORT_ZIP_FAILED);
        }
        
        
    }
}