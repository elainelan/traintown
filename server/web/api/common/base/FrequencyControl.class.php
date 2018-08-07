<?php
/**
 * 访问频率控制
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 访问频率控制类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class FrequencyControl
{

    /**
     * memcache实例指针
     *
     * @var CVMemcache
     */
    private $s_memcache;

    /**
     * 初始化
     *
     * @param CVMemcache $s_memcache
     *        CVMemcache指针
     */
    function __construct($s_memcache)
    {
        $this->s_memcache = $s_memcache;
    }

    /**
     * 添加频率限制
     *
     * @param string $rule_key
     *        规则key
     * @param array $rules
     *        规则数组, array(array('tm'=>1,'cnt'=>100))
     * @return boolean
     */
    public function addRules($rule_key, $rules = array())
    {
        return $this->s_memcache->add($rule_key, json_encode($rules));
    }

    /**
     * 清理频率限制
     *
     * @param string $rule_key
     *        规则key
     * @return boolean
     */
    public function delRules($rule_key)
    {
        return $this->s_memcache->del($rule_key);
    }

    /**
     * 更新频率限制
     *
     * @param string $rule_key
     *        规则key
     * @param array $rules
     *        规则数组，array(array('tm'=>1,'cnt'=>100))
     * @return boolean
     */
    public function updateRules($rule_key, $rules)
    {
        return $this->s_memcache->set($rule_key, json_encode($rules));
    }

    /**
     * 获取频率限制配置
     *
     * @param string $rule_key
     *        规则key
     * @return boolean
     */
    public function getRules($rule_key)
    {
        return json_decode($this->s_memcache->get($rule_key), true);
    }

    /**
     * 检查是否被限制
     *
     * @param string $check_key
     *        检查key
     * @param string $rule_key
     *        规则key
     * @return false|rule_idx
     */
    private function _isLimitByTimeCnt($check_key, $rule_key)
    {
        $rules = self::getRules($rule_key);
        if (!$rules) {
            return false;
        }
        
        //计算出记录最长需要保存多久
        $tmparr = array();
        foreach ($rules as $rule) {
            $tmparr[] = $rule['tm'];
        }
        $maxtm = max($tmparr);
        
        // 获取历史访问信息
        // array(tm1, tm2, ...)
        $tmAry = $this->s_memcache->get($check_key);
        
        //写入本次登录记录
        $curtm = time();
        $tmAry[] = $curtm;
        
        // 删除超出时间限制的记录
        foreach ($tmAry as $k => $tm) {
            if ($curtm - $tm > $maxtm * 60) {
                unset($tmAry[$k]);
            }
        }
        
        // 更新缓存
        if (!$this->s_memcache->set($check_key, $tmAry)) {
            // 重试一次
            $mres = $this->s_memcache->set($check_key, $tmAry);
            if (!$mres) {
                $errmsg = __METHOD__ . ':有过期记录，清理过期记录后更新缓存。更新memcached缓存失败';
                CVLog::addlog('FrequencyControl_error', array('check_key' => $errmsg));
            }
        }
        
        $return_rule_idx = null;
        foreach ($rules as $rule_idx => $rule) {
            if (count($tmAry) >= $rule['cnt']) {
                // 如果该ip访问次数到达出现的最大值,计算出该规则时间范围的次数
                $count = 0;
                foreach ($tmAry as $tm) {
                    if ($curtm - $tm <= $rule['tm'] * 60) {
                        $count++;
                    }
                }
                
                // 再次比较，如果小于次数，退出比较
                if ($count < $rule['cnt']) {
                    // 没到次数，不做封停
                    continue;
                }
                
                // 达到限制条件
                $return_rule_idx = $rule_idx;
            }
        }
        if (!is_null($return_rule_idx)) {
            return $return_rule_idx;
        }
        return false;
    }

    /**
     * 频率检查，按照时间和次数维度检查
     *
     * 例子：按IP访问频次检查
     * $rules = array(array('tm'=>1, 'cnt'=>2), array('tm'=>2, 'cnt'=>4));
     * $frequencyControl = new FrequencyControl(CDbuser::getInstanceMemcache());
     * $is_limit = $frequencyControl->limitByTimeCnt(__METHOD__, __METHOD__.ClientIp::get(), $rules);
     * if ($is_limit !== false) {
     * // 达到限制条件后的处理
     * }
     *
     *
     * @param string $rule_key
     *        规则key
     * @param string $check_key
     *        检查key
     * @param array $rules
     *        规则数组：array( 'rule_idx' => array('tm'=>1, 'cnt'=>5), 'rule_idx' => array('tm'=>2, 'cnt'=>7) )
     *        1分钟4次，2分钟6次
     * @return false|rule_idx
     */
    public function limitByTimeCnt($rule_key, $check_key, $rules = array())
    {
        self::addRules($rule_key, $rules);
        
        $this->s_memcache->lock($check_key);
        $is_limt = self::_isLimitByTimeCnt($check_key, $rule_key);
        $this->s_memcache->unlock($check_key);
        
        return $is_limt;
    }
}