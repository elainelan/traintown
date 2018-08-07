<?php
/**
 * API通讯类型
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * API指令类型定义类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class APICMD
{

    /**
     * SQL查询操作
     *
     * @var number
     */
    const SQL_SELECT = 100;

    /**
     * SQL修改处理
     *
     * @var number
     */
    const SQL_MOD = 101;

    const DB_TABLE_GAME = 102;

    const DB_TABLE_LOG = 103;

    const DB_TABLE_PAY = 104;

    const DB_TABLE_KF = 105;

    /**
     * 和游戏服务器通讯
     *
     * @var number
     */
    const GAMESERVER_CONF = 200;

    const GAMESERVER_PRT = 201;

    const GAMESERVER_GAME = 202;

    /**
     * 和memcache缓存通信
     *
     * @var number
     */
    const MEMCACHE = 300;

    /**
     * 和redis缓存通信
     *
     * @var number
     */
    const REDIS = 400;

}