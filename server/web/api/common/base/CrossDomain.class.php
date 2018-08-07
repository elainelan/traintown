<?php

/**
 * 跨域处理文件
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

/**
 * 跨域处理类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class CrossDomain
{

    /**
     * 检查是否可以跨域请求
     * 
     * @param string $allow_domain
     *        用逗号分隔的域名，如果全部允许，请用*
     */
    public static function validate($allow_domain)
    {
        $orign_domain = HttpParam::server('HTTP_ORIGN');
        // 是跨域访问，并且设置了跨域域名
        if ($orign_domain && $allow_domain) {
            $cross_domain = null;
            if ($allow_domain == '*') {
                $cross_domain = $orign_domain;
            }
            else {
                $allow_domain_ary = explode(',', $allow_domain);
                if (in_array($orign_domain, $allow_domain_ary)) {
                    $cross_domain = $orign_domain;
                }
            }
            if ($cross_domain) {
                header("Access-Control-Allow-Origin:" . $cross_domain);
                header("Access-Control-Allow-Credentials: true");
            }
        }
    }
}

?>