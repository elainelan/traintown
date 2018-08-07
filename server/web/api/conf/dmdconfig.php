<?php
/**
 * DMD配置文件
 * 
 * @author dragonets
 * @package conf
 * 
*/

///////// 时区配置 //////////////////
ini_set('date.timezone', 'Asia/Shanghai');


///////// 缓存配置 /////////////////////
// define('MEMCACHE_IP', '127.0.0.1');
// define('MEMCACHE_PORT', 11211);


///////// 日志记录配置  //////////////////

define('CVLOG_PATH', '/tmp');   // 默认日志目录，未配置默认/tmp

define('DEBUG_ERROR', 0);       // 是否显示错误，生产环境下，需要设置成0
define('LOG_PERFORMANCE', 1);   // 是否开启接口性能记录/tmp/CVLOG_performance.log
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

// DBAPI
define('API_DB_SIGN_KEY', 'db_asldjfl30cwI#cep3DC_s(&@#');


///////// API数据解密配置 ////////////////////////
define('API_DATA_KEY', 'ASm3DFaY8*#KDLCeNDKsdkf');



