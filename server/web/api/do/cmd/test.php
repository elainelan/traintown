<?php
/**
 * 测试操作
 *
 * @author dragonets
 * @package do
 * @subpackage cmd
 */

if (!defined('CV_ROOT')) {
    exit('No direct script access allowed');
}

/**
 * 测试操作类
 *
 * @author dragonets
 * @package do
 * @subpackage cmd
 */
class test
{

    /**
     * 类实例化错误
     */
    public function err()
    {
        //         $a = new abc();
    

    }

    /**
     * 函数引用错误
     */
    public function err2()
    {
        //         abc();
    }

    /**
     * sql数据库操作
     */
    public function sql()
    {
        $db = CDbuser::getInstanceDbPdo();
        // 查询
        $res = Sql::select('*')
            ->from('admin_users')
            ->where('id=?', 1)
            ->get($db, 'id')
        ;
        ResultParser::succ($res);
    }

    /**
     * 访问频率限制
     */
    public function freq()
    {
        $rules = array(array('tm' => 1, 'cnt' => 2), array('tm' => 2, 'cnt' => 4));
        $frequencyControl = new FrequencyControl(CDbuser::getInstanceMemcache());
        $is_limit = $frequencyControl->limitByTimeCnt(__METHOD__, __METHOD__ . ClientIp::get(), $rules);
        if ($is_limit !== false) {
            // 达到限制条件后的处理
            ResultParser::error(CErrorCode::FREQUENCY_LIMIT);
        }
        $data = 'freq_CLASS: ' . __CLASS__;
        ResultParser::succ($data);
    }

    /**
     * 黑白名单访问控制
     */
    public function adminip()
    {
        $platid = (int)HttpParam::get('platid');
        // 访问控制
        $rules_white = array(
            array('tm' => 1, 'cnt' => 61),
        );
        $rules_nomal = array(
            array('tm' => 1, 'cnt' => 11),
            array('tm' => 2, 'cnt' => 17),
        );
        AdminIpAudit::ipAudit($platid, $rules_white, $rules_nomal);
        $data = 'adminip_CLASS: ' . __CLASS__;
        ResultParser::succ($data);
    }

    public function gsapi_sql()
    {
        $sids = array(1, 2, 3);
        $platid = null;
        $post['cmds'] = array();
        
        $post['cmds'][] = array(
            'cmd_type' => APICMD::SQL_SELECT, 
            'cmd_para' => array(
                'db' => APICMD::DB_TABLE_GAME,
                'sql' => Sql::select('cid,name')->from('chas')->where('cid=?', 1)->limit(0, 5)->getContextSerialize(), 
            )
        );
        
        $api_res = CThroughAPI::GSAPIMulti('gapi.api', $sids, $platid, $post);
        ResultParser::succ($api_res);
    }
    
    public function gsapi_gs()
    {
        $sids = array(1);
        $platid = null;
        
        $post['cmds'] = array();
        
        $post['cmds'][] = array(
            'cmd_type' => APICMD::GAMESERVER_CONF,
            'cmd_para' => array( 
                'cmd' => 'get_stastic',
                'post' => array(),
            )
        );
        
        $api_res = CThroughAPI::GSAPIMulti('gapi.api', $sids, $platid, $post);
        ResultParser::succ($api_res);
    }

    public function gsapi_memcache()
    {
        $sids = array(1);
        $platid = null;
        
        $post['cmds'] = array();
        
        $post['cmds'][] = array(
            'cmd_type' => APICMD::MEMCACHE,
            'cmd_para' => array(
                'cmd' => 'flush',
                'params' => array(),
            )
        );
        
        $api_res = CThroughAPI::GSAPIMulti('gapi.api', $sids, $platid, $post);
        ResultParser::succ($api_res);
    }

    public function memcache()
    {
        $cache_key = "0_mmmm_admin_login";
        $res = CDbuser::getInstanceMemcache()->set($cache_key, 'abc', 10);
        ResultParser::succ($res);
    }
    
    public function dbapi()
    {
        $url = 'http://10.1.8.132/new/api/api_d/apid.php?dmd=dapi.test';
        
        $get = array(
            'dmdmass'=> 'bbss',
        );
        $post = array(
            'dmd'=>100,
            'xx' => 20,
            'bb' => 101,
        );
        $res = CThroughAPI::DBAPIOne($url, $get, $post);
        ResultParser::succ($res);
    }
}