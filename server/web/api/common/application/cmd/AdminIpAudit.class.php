<?php
/**
 * AdminIpAudit-IP审计（黑白名单）处理
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * AdminIpAudit-IP审计（黑白名单）处理类
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */
class AdminIpAudit
{

    /**
     * 当前访问IP是否是白名单IP
     *
     * @param number $platid
     *        平台ID
     * @return boolean
     */
    public static function isWhiteIp($platid)
    {
        return self::_isMatchIp($platid, 'white');
    }

    /**
     * 当前IP是否是黑名单IP
     *
     * @param number $platid
     *        平台ID
     * @return boolean
     */
    public static function isBlackIp($platid)
    {
        return self::_isMatchIp($platid, 'black');
    }

    /**
     * IP是否匹配黑白名单
     *
     * @param int $platid
     *        平台ID
     * @param string $type
     *        白名单white/黑名单black
     * @return boolean
     */
    private static function _isMatchIp($platid, $type)
    {
        $ip_lists = array();
        switch ($type) {
            case 'white':
                $db_table = CDbuser::getInstanceTable('AdminIpWhite');
                break;
            case 'black':
                $db_table = CDbuser::getInstanceTable('AdminIpBlack');
                break;
            default:
                return false;
                break;
        }
        $db_ip_lists = $db_table->getWithCache($platid);
        if ($db_ip_lists) {
            $ip_lists = $db_ip_lists;
        }
        
        if ($platid) {
            $db_ip_lists_0 = $db_table->getWithCache(0);
            if ($db_ip_lists_0) {
                $ip_lists += $db_ip_lists_0;
            }
        }
        return self::_matchIp(ClientIp::get(), $ip_lists);
    }

    /**
     * IP正则匹配
     *
     * @param string $ip
     *        需要检查的IP
     * @param array $ip_lists
     *        匹配列表
     * @return boolean
     */
    private static function _matchIp($ip, $ip_lists)
    {
        if (in_array($ip, array_keys($ip_lists))) {
            return true;
        }
        foreach ($ip_lists as $ip_list => $v) {
            if (strpos($ip_list, '*')) {
                $regular_str = str_replace(array('.', '*'), array('\.', '[0-9]{1,3}'), $ip_list);
                if (preg_match("/$regular_str/", $ip)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * IP审计（非法IP会加入到黑名单）
     *
     * @param number $platid
     *        平台ID
     * @param array $rules_white
     *        白名单规则数组：array( 'rule_idx' => array('tm'=>1, 'cnt'=>5), 'rule_idx' => array('tm'=>2, 'cnt'=>7) )
     *        1分钟4次，2分钟6次
     * @param array $rules_nomal
     *        普通规则数组：array( 'rule_idx' => array('tm'=>1, 'cnt'=>5), 'rule_idx' => array('tm'=>2, 'cnt'=>7) )
     *        1分钟4次，2分钟6次
     */
    public static function ipAudit($platid, $rules_white, $rules_nomal)
    {
        if (self::isBlackIp($platid)) {
            // 黑名单IP，禁止访问
            ResultParser::error(CErrorCode::IP_BLACK);
        }
        $frequency_control = new FrequencyControl(CDbuser::getInstanceMemcache());
        if (self::isWhiteIp($platid)) {
            // 白名单处理
            // 达到限制暂停访问
            if ($rules_white) {
                if ($frequency_control->limitByTimeCnt(__METHOD__, __METHOD__ . ClientIp::get(), $rules_white) !== false) {
                    ResultParser::error(CErrorCode::FREQUENCY_LIMIT);
                }
            }
        }
        else {
            // 非黑名单也非白名单处理
            // 达到任意一条触发访问限制，达到最后一条限制，加入黑名单
            if ($rules_nomal) {
                $is_limit = $frequency_control->limitByTimeCnt(__METHOD__, __METHOD__ . ClientIp::get(), $rules_nomal);
                if ($is_limit !== false) {
                    // 达到限制条件后的处理
                    end($rules_nomal);
                    $end_rule_idx = key($rules_nomal);
                    if ($end_rule_idx === $is_limit && !self::isWhiteIp($platid)) {
                        // 达到最后一个条件，加入黑名单
                        $db_admin_ip_black = CDbuser::getInstanceTable('AdminIpBlack');
                        $insert_values = array('platid' => 0, 'ip' => ClientIp::get(), 'notes' => 'frequency_limit_add:' . Api::getClass() . '.' . Api::getMethod());
                        $db_admin_ip_black->insertWithCache(0, $insert_values);
                    }
                    ResultParser::error(CErrorCode::FREQUENCY_LIMIT);
                }
            }
        }
    }

}

