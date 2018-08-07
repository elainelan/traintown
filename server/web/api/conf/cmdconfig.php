<?php
/**
 * CMD配置文件
 * 
 * @author dragonets
 * @package conf
*/

///////// 时区配置 //////////////////
ini_set('date.timezone', 'Asia/Shanghai');


///////// 跨域访问配置 ////////////////////////////
// 客户端域名，当客户端访问域名和API_C的域名不一致时，需要配置，*表示所有
// 如果多个域名，使用逗号分隔，例如： a.com,b.com
define('API_CLIENT_DOMAIN', '');


///////// 数据库配置 /////////////////

// 直连
define('DB_IP', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PWD', '123456');
define('DB_NAME', 'center_lgl');

// DBAPI
define('API_DB_INTERFACE', 'http://10.1.8.132/new/api/api_d/apid.php');
define('API_DB_SIGN_KEY', 'db_asldjfl30cwI#cep3DC_s(&@#');


///////// 缓存配置 /////////////////////

define('MEMCACHE_IP', '127.0.0.1');
define('MEMCACHE_PORT', 11211);


///////// 日志记录配置  //////////////////

define('CVLOG_PATH', '/tmp');   // 默认日志目录，未配置默认/tmp

define('DEBUG_ERROR', 0);       // 是否显示错误，生产环境下，需要设置成0
define('LOG_PERFORMANCE', 0);   // 是否开启接口性能记录/tmp/CVLOG_performance.log
define('SHOW_PERFORMANCE', 0);  // 是否在接口中返回性能信息，LOG_PERFORMANCE=1生效

define('LOG_SQL_GET', 1);       // 是否记录所有SQL查询日志/tmp/CVLOG_SQL_get.log（严重错误必定记录/tmp/CVLOG_prod.log，不受此开关限制）
define('LOG_SQL_EXEC', 1);      // 是否记录所有SQL更新日志/tmp/CVLOG_SQL_exec.log（严重错误必定记录/tmp/CVLOG_prod.log，不受此开关限制）
define('LOG_CURL', 1);          // 是否记录所有CRUL执行日志/tmp/CVLOG_CURL_succ.log（严重错误必定记录/tmp/CVLOG_CURL_fail.log，不受此开关限制）


///////// 其他API配置  /////////////////////

// CenterAPI接口key
define('API_CENTER_SIGN_KEY', 'center_asldjfl30cwI#cep3DC_s(&@#');

// GSAPI接口key
define('API_GS_SIGN_KEY', 'gs_asldjfl30cwI#cep3DC_s(&@#');
    // GSAPI接口路径。配置api/apig.php，表示http://server/api/apig.php
define('API_GAME_INTERFACE', 'lgl/api_g/apig.php');


///////// API数据解密配置 ////////////////////////
define('API_DATA_KEY', 'ASm3DFaY8*#KDLCeNDKsdkf');



















/////// 以下老配置，未做确认及提取

define('CMD_PREFIX', 'idxc.php?');
define('GMD_INTERFACE','idxg.php?');

define('WEB_STATIC', 'http://10.1.8.33/static_cmd/');

// for TENCENT
/* 或者如下情况：
 *     gameserver没有配置center域名，但是从gameserver后台返回center后台，需要使用center域名的
 *     默认是使用center的第一个ip返回的，如果这个情况下需要返回center域名，用域名访问center后台即可
 */
define('IS_QQ', 1);
// 定义是否QQ平台，是的话，显示QQ平台相关信息
//define('QQ_PLATFORM', 1);

// GM操作日志配置，默认开启，没有配置ADMINUSER_FILE_LOG就是开启。只有配置ADMINUSER_FILE_LOG=0关闭
//define('ADMINUSER_FILE_LOG', 0);
// GM操作日志文件路径前缀，默认'/home/coovanftp/ftp/'，注意后面/结尾
//define('ADMINUSER_FILE_LOGPATH', '/home/coovanftp/ftp/');

// 游戏名称
define('GAMENAME', '热江');

//  后台多语言选择 如定义为ru 则后台可以切换俄文版本, 如需要切换多个语言版本，配置时需要用','连接 如配置俄文和英文 'ru,en'
//  可以配置的语言有
//  ru     俄文
//  zh_tw  繁体中文
//  en     英语
define('OTHER_LANG', '');


// superCenter-IP
define('SUPER_CENTER_IP', serialize(array(
    '122.224.80.107','124.160.115.227',
)));
// superCenter访问key
define('CENTER_KEY', '12345');

// robotsCenter-IP
define('ROBOTS_CENTER_IP', serialize(array(
    '112.65.133.10',
    '183.61.80.118',
    '119.38.131.118'
)));
// robotsCenter访问key
define('ROBOTS_KEY', '12345');

// 聊天监控配置URL
define('CHATMON_CONFIG_URL', 'http://10.1.8.59/chatmon/chatConfig.php');

// IP监控配置URL
define('IPMON_CONFIG_URL', 'http://10.1.8.59/ipmon/ipConfig.php');

// CARD礼包中心配置URL
define('CARD_CONFIG_URL', 'http://10.1.8.59/card/cardConfig.php');

