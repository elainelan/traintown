<?php
/**
 * 基础操作类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 基础操作类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class CDoBase
{

    /**
     * (通用)分页获取数据方法
     *
     * @param Object $table_instance
     *        CDbuser::getInstanceTable获取生成的数据库表实例化指针
     * @param array $where_args
     *        查询条件数组
     */
    public function getPage($table_instance, $select_strings = '*', $where_args = array())
    {
        $page = (int)HttpParam::request('page');
        $num = (int)HttpParam::request('num');
        $orderby = HttpParam::request('orderby');
        
        $page = $page > 0 ? $page : 1;
        $num = $num > 0 ? $num : 10;
        $orderby_args = $orderby ? $orderby : array();
        
        ResultParser::succ($table_instance->getByCondFromDb($select_strings, $where_args, $page, $num, $orderby_args));
    }

    /**
     * (通用)从某个区服分页获取数据方法
     *
     * @param int $sid
     *        区服ID
     * @param string $db_table
     *        数据库表名称
     * @param string $select_strings
     *        查询字段字符串
     * @param array $where_args
     *        查询条件数组
     */
    public function getPageBySid($sid, $db_table, $select_strings = '*', $where_args = array())
    {
        $page = (int)HttpParam::request('page');
        $num = (int)HttpParam::request('num');
        $orderby = HttpParam::request('orderby');
        
        $page = $page > 0 ? $page : 1;
        $num = $num > 0 ? $num : 10;
        $orderby_args = $orderby ? $orderby : array();
        
        $sids = array($sid);
        
        $post['cmds'] = array();
        $post['cmds'][] = array(
            'cmd_type' => APICMD::SQL_SELECT,
            'cmd_para' => array(
                'db' => APICMD::DB_TABLE_GAME,
                'sql' => Sql::select('count(1) as sum')->from($db_table)->whereArgs($where_args)->getContextSerialize(),
            )
        );
        
        $sql = Sql::select($select_strings)->from($db_table)->whereArgs($where_args);
        if (is_array($orderby_args) && $orderby_args) {
            $sql = $sql->orderByArgs($orderby_args);
        }
        $sql = $sql->limit(($page - 1) * $num, $num)->getContextSerialize();
        
        $post['cmds'][] = array(
            'cmd_type' => APICMD::SQL_SELECT,
            'cmd_para' => array(
                'db' => APICMD::DB_TABLE_GAME,
                'sql' => $sql,
            )
        );
        
        $api_res = CThroughAPI::GSAPIMulti('gapi.api', $sids, $where_args['platid'], $post);
        
        if ($api_res[$sid]['r']) {
            // 返回数据整理
            $total = $api_res[$sid]['data'][0];
            $data = $api_res[$sid]['data'][1];
            
            $page_total = ceil($total[0]['sum'] / $num);
            if ($page > $page_total && $page_total > 0) {
                $page = $page_total;
            }
            
            ResultParser::succ(Pagination::formatData($page, ceil($total[0]['sum'] / $num), $num, $total[0]['sum'], $data));
        }
        
        ResultParser::error($api_res[$sid]['errCode']);
    }

    /**
     * (通用)删除数据方法
     *
     * @param Object $table_instance
     *        CDbuser::getInstanceTable获取生成的数据库表实例化指针
     * @param array $where_args
     *        删除条件数组
     */
    public function del($table_instance, $where_args = array())
    {
        ResultParser::succ($table_instance->delByCondFromDb($where_args));
    }
    
    public function delWithCache($table_instance, $where_args = array(), $cache_key_sub_string)
    {
        ResultParser::succ($table_instance->delWithCache($cache_key_sub_string, $where_args));
    }

    /**
     * (通用)添加数据方法
     *
     * @param Object $table_instance
     *        CDbuser::getInstanceTable获取生成的数据库表实例化指针
     * @param array $insert_data
     *        插入数据数组
     */
    public function add($table_instance, $insert_data)
    {
        ResultParser::succ($table_instance->insertByCondFromDbID($insert_data));
    }
    
    public function addWithCacheID($table_instance, $insert_data, $cache_key_sub_string)
    {
        ResultParser::succ($table_instance->insertWithCacheID($cache_key_sub_string, $insert_data));
    }

    /**
     * (通用)更新数据方法
     *
     * @param Object $table_instance
     *        CDbuser::getInstanceTable获取生成的数据库表实例化指针
     * @param array $update_data
     *        更新数据数组
     * @param array $where_args
     *        更新数据条件数组
     */
    public function update($table_instance, $update_data, $where_args)
    {
        ResultParser::succ($table_instance->updateByCondFromDb($update_data, $where_args));
    }
    
    public function updateWithCache($table_instance, $update_data, $where_args, $cache_key_sub_string)
    {
        ResultParser::succ($table_instance->updateWithCache($cache_key_sub_string, $update_data, $where_args));
    }

    /**
     * (通用)导出数据方法
     *
     * @param Object $table_instance
     *        CDbuser::getInstanceTable获取生成的数据库表实例化指针
     * @param array $where_args
     *        数据查询条件数组
     * @param string $file
     *        导出文件名
     * @param array $title_ary
     *        导出栏位语言包数组，array(栏位key => 栏位显示, ... )
     */
    public function downloadPage($table_instance, $select_strings = '*', $where_args = array(), $file = 'download', $title_ary = array())
    {
        $orderby = HttpParam::request('orderby');
        $orderby_args = $orderby ? $orderby : array();
        
        $page = 1; // 初始化页数，1
        $num = 3000; // 每次获取3000条记录写入文件
        

        $rs = 1; // 是否还有数据需要读取
        $file = $file . '_' . date('Y_m_d_H_i_s_ms') . '.csv'; // 导出文件（未压缩）
        //$header = 0;
        //ExportData::setCsvHeader(array('id', 'platid', 'userid', 'tm', 'ip', 'uri', 'param', 'res', 'success')); // 设置CSV导出标题
        

        do {
            $export_data = $table_instance->getByCondFromDb($select_strings, $where_args, $page, $num, $orderby_args);
            if ($export_data['page_total']) { // 有数据
                // 存入数据
                ExportData::addCsv($file, $export_data['items'], $title_ary);
                // 指向下页
                ++$page;
                
                if ($export_data['page_total'] == $export_data['page_current']) {
                    // 到达最后一页，跳出循环
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
            $zip_file = ExportData::zip(ExportData::$folder . $file);
            if ($zip_file !== false) {
                // 打包成功
                ResultParser::succ($zip_file);
            }
            // 打包失败
            ResultParser::succ(ExportData::$folder . $file);
        }
        ResultParser::error(ErrorCode::EXPORT_FAILED);
    }


    /**
     * (通用)从某个区服下载数据方法
     *
     * @param int $sid
     *        区服ID
     * @param string $db_table
     *        数据库表名称
     * @param string $select_strings
     *        查询字段字符串
     * @param array $where_args
     *        查询条件数组
     * @param string $file
     *        导出文件名
     * @param array $title_ary
     *        导出栏位语言包数组，array(栏位key => 栏位显示, ... )
     */
    public function downloadPageBySid($sid, $db_table, $select_strings = '*', $where_args = array(), $file = 'download', $title_ary = array())
    {
        $orderby = HttpParam::request('orderby');
        $orderby_args = $orderby ? $orderby : array();
        
        $page = 1; // 初始化页数，1
        $num = 3000; // 每次获取3000条记录写入文件
        

        $rs = 1; // 是否还有数据需要读取
        $file = $file . '_' . date('Y_m_d_H_i_s_ms') . '.csv'; // 导出文件（未压缩）
        //$header = 0;
        //ExportData::setCsvHeader(array('id', 'platid', 'userid', 'tm', 'ip', 'uri', 'param', 'res', 'success')); // 设置CSV导出标题
        

        $sids = array($sid);
        do {
            
            $post['cmds'] = array();
            $post['cmds'][] = array('cmd_type' => APICMD::SQL_SELECT, 'cmd_para' => array('db' => APICMD::DB_TABLE_GAME, 'sql' => Sql::select('count(1) as sum')->from($db_table)->whereArgs($where_args)->getContextSerialize()));
            
            $sql = Sql::select($select_strings)->from($db_table)->whereArgs($where_args);
            if (is_array($orderby_args) && $orderby_args) {
                $sql = $sql->orderByArgs($orderby_args);
            }
            $sql = $sql->limit(($page - 1) * $num, $num)->getContextSerialize();
            
            $post['cmds'][] = array('cmd_type' => APICMD::SQL_SELECT, 'cmd_para' => array('db' => APICMD::DB_TABLE_GAME, 'sql' => $sql));
            
            $api_res = CThroughAPI::GSAPI('gapi.api', $sids, $where_args['platid'], $post);
            
            if ($api_res[$sid]['r']) {
                // 返回数据整理
                $total = $api_res[$sid]['data'][0];
                $data = $api_res[$sid]['data'][1];
                
                $page_total = ceil($total[0]['sum'] / $num);
                if ($page > $page_total && $page_total > 0) {
                    $page = $page_total;
                }
                
                $export_data = Pagination::formatData($page, ceil($total[0]['sum'] / $num), $num, $total[0]['sum'], $data);
                
                if ($export_data['page_total']) { // 有数据
                    // 存入数据
                    ExportData::addCsv($file, $export_data['items'], $title_ary);
                    // 指向下页
                    ++$page;
                    
                    if ($export_data['page_total'] == $export_data['page_current']) {
                        // 到达最后一页，跳出循环
                        $rs = 0;
                    }
                }
                else {
                    // 没有数据，跳出循环
                    ResultParser::error(CErrorCode::EXPORT_DATA_NULL);
                }
            }
            else {
                // 获取不到数据，跳出循环
                $rs = 0;
            }
        }
        while ($rs != 0);
        
        if ($export_data['page_total']) {
            // 尝试zip打包
            $zip_file = ExportData::zip(ExportData::$folder . $file);
            if ($zip_file !== false) {
                // 打包成功
                ResultParser::succ($zip_file);
            }
            // 打包失败
            ResultParser::succ(ExportData::$folder . $file);
        }
        ResultParser::error(ErrorCode::EXPORT_FAILED);
    }

    /**
     * 生成随机的md5码
     *
     * @return string
     */
    public function make_md5()
    {
        return md5(HttpParam::server('REMOTE_ADDR') . microtime(1) . rand(10000, 99999));
    }

    /**
     * 时间范围格式化（查询条件）
     *
     * @param string $date
     *        如'2017-09-21 - 2017-10-20'
     * @return array(betwwenn=>array(tm1, tm2)) || null
     */
    public function between_time_format($date, $separate = ' - ')
    {
        $time_explode_ary = explode($separate, $date);
        if (count($time_explode_ary) == 2) {
            $tm_start = strtotime($time_explode_ary[0]);
            $tm_end = strtotime($time_explode_ary[1]);
            if ($tm_start && $tm_end) {
                return array('between' => array($tm_start, $tm_end + 3600 * 24 - 1));
            }
        }
        
        return null;
    }

    /**
     * ip格式化（查询条件）
     *
     * @param string $ip        
     * @return array(like=>%ip%) || null
     */
    public function ip_format($ip)
    {
        if ($ip) {
            return array('like' => '%' . $ip . '%');
        }
        
        return null;
    }

    /**
     * uri格式化（查询条件）
     *
     * @param string $uri        
     * @return array(like=>uri%) || null
     */
    public function uri_format($uri)
    {
        if ($uri) {
            return array('like' => $uri . '%');
        }
        
        return null;
    }
    
    /**
     * 数据解密方法
     */
    protected function decrypt()
    {
        //sign验证
        if (!Sign::validSign(HttpParam::get(), API_CENTER_SIGN_KEY)) {
            ResultParser::error(CErrorCode::API_SIGN_ERROR);
        }
        // 数据解密
        CenterAPI::decrypt();
    }

}

