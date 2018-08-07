# Host: 10.1.8.132  (Version: 5.1.73)
# Date: 2018-01-30 09:42:24
# Generator: MySQL-Front 5.3  (Build 1.18)

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;

#
# Source for table "admin_ip_black"
#

DROP TABLE IF EXISTS `admin_ip_black`;
CREATE TABLE `admin_ip_black` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `platid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '平台ID',
  `ip` char(15) NOT NULL DEFAULT '' COMMENT '允许访问后台的IP',
  `notes` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`),
  KEY `platid` (`platid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

#
# Data for table "admin_ip_black"
#


#
# Source for table "admin_ip_white"
#

DROP TABLE IF EXISTS `admin_ip_white`;
CREATE TABLE `admin_ip_white` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `platid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '平台ID',
  `ip` char(15) NOT NULL DEFAULT '' COMMENT '允许访问后台的IP',
  `notes` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`),
  KEY `platid` (`platid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

#
# Data for table "admin_ip_white"
#

INSERT INTO `admin_ip_white` VALUES (1,0,'10.1.*.*','公司内网');

#
# Source for table "admin_login_record"
#

DROP TABLE IF EXISTS `admin_login_record`;
CREATE TABLE `admin_login_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `platid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `admin_userid` int(11) unsigned NOT NULL DEFAULT '0',
  `loginip` varchar(15) NOT NULL DEFAULT '0',
  `logintime` int(11) unsigned NOT NULL DEFAULT '0',
  `errorcnt` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `errornum` (`errorcnt`),
  KEY `loginip` (`loginip`),
  KEY `logintime` (`logintime`),
  KEY `platid` (`platid`),
  KEY `userid` (`admin_userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

#
# Data for table "admin_login_record"
#


#
# Source for table "admin_menu_trees"
#

DROP TABLE IF EXISTS `admin_menu_trees`;
CREATE TABLE `admin_menu_trees` (
  `tree_id` int(11) unsigned NOT NULL DEFAULT '0',
  `uri` char(128) DEFAULT '',
  `parent_tree_id` int(11) unsigned NOT NULL DEFAULT '0',
  `sort` int(11) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `desc` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tree_id`),
  UNIQUE KEY `uri_idx` (`uri`),
  KEY `parent_tree_id_idx` (`parent_tree_id`),
  KEY `sort_idx` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

#
# Data for table "admin_menu_trees"
#

INSERT INTO `admin_menu_trees` (`tree_id`,`uri`,`parent_tree_id`,`sort`,`hidden`,`icon`,`desc`) VALUES (1,'admin',0,1000000,0,'fa fa-cogs','系统管理'),(2,'admin.mylogin_list',1,1010000,0,'fa fa-user-circle-o','系统管理--我的登录'),(3,'admin.modpwd_self',1,1020000,0,'fa fa-key','系统管理--我的密码'),(4,'admin.account',1,1030000,0,'fa fa-user-o','系统管理--账号管理'),(5,'admin.account.get',4,1030001,1,'','系统管理--账号管理--查询账号'),(6,'admin.account.manager',4,1030002,1,'','系统管理--账号管理--管理账号'),(7,'uri_blank_01',1,1040000,0,'','系统管理--权限管理'),(8,'admin.permission_user',7,1040100,0,'fa fa-user','系统管理--权限管理--用户角色表'),(9,'admin.permission_user.get',8,1040101,1,'','系统管理--权限管理--用户角色表--查看用户权限'),(10,'admin.permission_user.manager',8,1040102,1,'','系统管理--权限管理--用户角色表--管理用户权限'),(11,'admin.permission_role',7,1040200,0,'fa fa-users','系统管理--权限管理--角色用户表'),(12,'admin.permission_role.get',11,1040201,1,'','系统管理--权限管理--角色用户表--查看角色权限'),(13,'admin.permission_role.manager',11,1040202,1,'','系统管理--权限管理--角色用户表--管理角色权限'),(14,'uri_blank_02',1,1050000,0,'','系统管理--登录管理'),(15,'platforms',0,2000000,0,'fa fa-handshake-o','平台管理'),(16,'platforms.manager',15,2010000,0,'fa fa-info-circle','平台管理--平台信息'),(17,'servers',0,3000000,0,'fa fa-server','区服管理'),(18,'servers.manager',17,3010000,0,'fa fa-globe','区服管理--区服信息'),(19,'optools',0,4000000,0,'fa fa-wrench','运营工具'),(20,'optools.servers',19,4010000,0,'glyphicon glyphicon-pushpin','运营工具--区服工具'),(21,'optools.servers.get',20,4010001,1,'','运营工具--区服工具--状态查询'),(22,'optools.servers.entrance',20,4010002,1,'','运营工具--区服工具--入口管理'),(23,'optools.servers.close_server',20,4010003,1,'','运营工具--区服工具--关闭区服'),(24,'optools.servers.prt',20,4010004,1,'','运营工具--区服工具--守护管理'),(25,'optools.servers.flush_cache',20,4010005,1,'','运营工具--区服工具--刷新缓存'),(26,'optools.servers.sql',20,4010006,1,'','运营工具--区服工具--执行SQL'),(27,'admin.permission',7,1040300,0,'fa fa-tree','系统管理--权限管理--角色组管理'),(28,'admin.permission.get',27,1040301,1,'','系统管理--权限管理--角色组管理--查看角色组'),(29,'admin.permission.manager',27,1040302,1,'','系统管理--权限管理--角色组管理--管理角色组'),(30,'platforms.manager.get',16,2010001,1,'','平台管理--平台信息--查看平台'),(31,'platforms.manager.manager',16,2010002,1,'','平台管理--平台信息--管理平台'),(32,'servers.manager.get',18,3010001,1,'','区服管理--区服信息--查看区服'),(33,'servers.manager.manager',18,3010002,1,'','区服管理--区服信息--管理区服信息（基础）'),(34,'servers.plat',17,3020000,0,'fa fa-leaf','区服管理--分平台区服信息'),(35,'servers.plat.get',34,3020001,1,'','区服管理--分平台区服信息--查看信息'),(36,'servers.plat.manager',34,3020002,1,'','区服管理--分平台区服信息--管理信息'),(37,'optools.servers_version',19,4020000,0,'glyphicon glyphicon-pushpin','运营工具--版本信息比较'),(38,'optools.servers_url',19,4030000,0,'glyphicon glyphicon-pushpin','运营工具--路径及资源URL批量修改-'),(39,'optools.servers_prepare',19,4040000,0,'glyphicon glyphicon-pushpin','运营工具--开服准备'),(40,'optools.servers_bs_name',19,4050000,0,'glyphicon glyphicon-pushpin','运营工具--跨服战区服名称显示'),(41,'optools.servers_combine_sid',19,4060000,0,'glyphicon glyphicon-pushpin','运营工具--合服后区服ID修改'),(42,'admin.login_list',14,1050100,0,'fa fa-sign-in','系统管理--登录管理--登录记录'),(43,'admin.ip_white',14,1050200,0,'fa fa-check','系统管理--登录管理--IP白名单'),(44,'admin.ip_white.get',43,1050201,1,'','系统管理--登录管理--IP白名单--查看'),(45,'admin.ip_white.manager',43,1050202,1,'','系统管理--登录管理--IP白名单--管理'),(46,'admin.ip_black',14,1050300,0,'fa fa-ban','系统管理--登录管理--IP黑名单'),(47,'admin.ip_black.get',46,1050301,1,'','系统管理--登录管理--IP黑名单--查看'),(48,'admin.ip_black.manager',46,1050302,1,'','系统管理--登录管理--IP黑名单--管理'),(49,'admin.mylogin_list.get',2,1010001,1,'','系统管理--我的登录--查看记录'),(50,'admin.mylogin_list.download',2,1010002,1,'','系统管理--我的登录--下载导出'),(51,'admin.login_list.get',42,1050101,1,'','系统管理--登录管理--登录记录--查看记录'),(52,'admin.login_list.download',42,1050102,1,'','系统管理--登录管理--登录记录--下载导出'),(53,'admin.myoperation_list',1,1025000,0,'fa fa-keyboard-o','系统管理--我的操作'),(54,'admin.myoperation_list.download',53,1025101,1,'','系统管理--我的操作--下载导出'),(55,'servers.manager.config',18,3010003,1,'','区服管理--区服信息--管理区服信息（扩展）'),(56,'servers.manager.download',18,3010004,1,'','区服管理--区服信息--下载导出'),(57,'admin.myoperation_list.get',53,1025100,1,'','系统管理--我的操作--查看记录'),(58,'admin.operation_list.get',60,1060101,1,'','系统管理--操作管理--查看'),(59,'admin.operation_list.download',60,1060102,1,'','系统管理--操作管理--下载记录'),(60,'admin.operation_list',1,1060100,0,'fa fa-book','系统管理--操作管理'),(61,'admin.settings',1,1070100,0,'fa fa-cog','系统管理--配置管理'),(62,'admin.settings.get',61,1070101,1,'','系统管理--配置管理--查看配置'),(63,'admin.settings.manager',61,1070102,1,'','系统管理--配置管理--修改配置'),(64,'export',0,5000000,0,'fa  fa-download','数据导出'),(65,'export.table',64,2010000,0,'fa fa-table','数据导出-表数据导出');

#
# Source for table "admin_operation"
#

DROP TABLE IF EXISTS `admin_operation`;
CREATE TABLE `admin_operation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `platid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `tm` int(11) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `uri` varchar(64) NOT NULL DEFAULT '',
  `param` varchar(2048) NOT NULL DEFAULT '',
  `res` varchar(2048) NOT NULL DEFAULT '',
  `success` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid_idx` (`userid`),
  KEY `tm_idx` (`tm`),
  KEY `ip_idx` (`ip`),
  KEY `uri_idx` (`uri`),
  KEY `sucess_idx` (`success`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

#
# Data for table "admin_operation"
#


#
# Source for table "admin_platforms"
#

DROP TABLE IF EXISTS `admin_platforms`;
CREATE TABLE `admin_platforms` (
  `id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '平台ID',
  `money_min` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '充值隐藏订单金额范围最小值',
  `money_max` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '充值隐藏订单奖金额范围最大值',
  `num_pct` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '充值隐藏充值总订单数量百分比',
  `pay_pct` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '充值隐藏充值总金额百分比',
  `ptoolbar` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '页面导航栏',
  `safe` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '账号安全保护是否开启',
  `phone_url` varchar(255) NOT NULL DEFAULT '' COMMENT '手机验证地址',
  `web` varchar(255) NOT NULL DEFAULT '' COMMENT '平台网站入口',
  `pfcm` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '平台防沉迷是否开启',
  `bbs` varchar(255) NOT NULL DEFAULT '' COMMENT '平台论坛地址',
  `fcm` varchar(255) NOT NULL DEFAULT '' COMMENT '反沉迷信息提交接口地址',
  `gm_url` varchar(255) NOT NULL DEFAULT '' COMMENT '联运平台客服地址',
  `cm_url` varchar(255) NOT NULL DEFAULT '' COMMENT '联运平台反沉迷信息填写地址',
  `newcard_url` varchar(255) NOT NULL DEFAULT '' COMMENT '新手卡领取地址',
  `mini` varchar(255) NOT NULL DEFAULT '' COMMENT '微端下载地址',
  `mini_login` varchar(255) NOT NULL DEFAULT '' COMMENT '微端登录入口地址',
  `mini_ver` varchar(10) NOT NULL DEFAULT '' COMMENT '微端最新版本号',
  `name` char(11) NOT NULL DEFAULT '' COMMENT '平台名称',
  `game_sig` char(32) NOT NULL DEFAULT '' COMMENT '平台游戏sigkey',
  `pay_sig` char(32) NOT NULL DEFAULT '' COMMENT '平台支付sigkey',
  `close_tm` int(11) unsigned NOT NULL DEFAULT '0',
  `forcein` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `automis` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sj_test` varchar(2048) NOT NULL DEFAULT '',
  `sj_pid` varchar(2048) NOT NULL DEFAULT '',
  `muids2_config` varchar(2048) NOT NULL DEFAULT '',
  `supervip_config` varchar(2048) NOT NULL DEFAULT '',
  `onbeforeunload` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pay_adr` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

#
# Data for table "admin_platforms"
#

INSERT INTO `admin_platforms` VALUES (1,0,0,0,0,1,1,'','',0,'','','','','','','','','测试平台','3b38697985b507ea6bb2dc30d0a4dc40','gxkro404rklibvjzkmlgij2qg47cvepr',0,0,0,'','','','',0,''),(8,0,0,0,0,1,0,'','',1,'','','','','','','','','搜狗','307ca69591ee4bf92a40661daa00eec0','9d894eaaa57859a31d366f29fee61583',0,0,1,'','fss','','',0,'3332'),(9,0,0,0,0,1,0,'','',1,'','','','','','','','','YY','91de3f579cd2b066290bd11d218986ac','73a6f4e6cc3b0195437f151e6fb470d8',0,0,1,'','fss','','',0,'');

#
# Source for table "admin_role_permissions"
#

DROP TABLE IF EXISTS `admin_role_permissions`;
CREATE TABLE `admin_role_permissions` (
  `role_id` int(11) unsigned NOT NULL DEFAULT '0',
  `tree_id` int(11) unsigned NOT NULL DEFAULT '0',
  KEY `role_id_idx` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Data for table "admin_role_permissions"
#

INSERT INTO `admin_role_permissions` (`role_id`,`tree_id`) VALUES (1,2),(1,3),(1,7),(1,4),(1,53),(4,15),(4,18),(6,3),(6,49),(6,12),(6,28),(6,30),(6,44),(6,47),(6,5),(6,51),(6,57),(6,58),(6,9),(4,1),(4,64);

#
# Source for table "admin_roles"
#

DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE `admin_roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `desc` varchar(255) NOT NULL DEFAULT '',
  `baoliu` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

#
# Data for table "admin_roles"
#

INSERT INTO `admin_roles` VALUES (1,'权限管理组','拥有权限管理功能',1),(4,'超级管理组','超级管理组',0),(6,'功能测试','功能测试权限',0);

#
# Source for table "admin_settings"
#

DROP TABLE IF EXISTS `admin_settings`;
CREATE TABLE `admin_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `settings` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

#
# Data for table "admin_settings"
#

INSERT INTO `admin_settings` VALUES (1,'{\"weixin_service_appid\":\"wx813d434310c5dc7b\",\"weixin_service_appsecret\":\"726cc99bfa9be39994dc2cbb537dba05\",\"weixin_service_login_template_id\":\"06jqlZe2kskNo2vnKeFhpk_Xt_U20QZbxFL9oAFlmI4\",\"weixin_service_game_admin_name\":\"\\u79e6\\u65f6\\u660e\\u6708\\u9875\\u6e38-\\u8054\\u8fd0\",\"weixin_service_callback_ip\":\"10.1.6.61\",\"weixin_service_callback_seq\":\"1\"}');

#
# Source for table "admin_user_roles"
#

DROP TABLE IF EXISTS `admin_user_roles`;
CREATE TABLE `admin_user_roles` (
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `role_id` int(11) unsigned NOT NULL DEFAULT '0',
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Data for table "admin_user_roles"
#

INSERT INTO `admin_user_roles` VALUES (1,1),(2,1),(2,4),(4,1),(3,1),(3,4),(4,4),(5,4),(5,1),(10,6),(11,4),(12,4);

#
# Source for table "admin_users"
#

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'GM_ID',
  `expire` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'token过期时间',
  `platid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '平台ID',
  `admin_group` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '权限角色id',
  `loginname` varchar(32) NOT NULL DEFAULT '' COMMENT '登录名',
  `loginpwd` varchar(32) NOT NULL DEFAULT '' COMMENT '登录密码',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '使用者名称',
  `ip` varchar(15) NOT NULL DEFAULT '' COMMENT '最近一次登录ip',
  `token` char(64) NOT NULL DEFAULT '' COMMENT '登录token',
  `lastlogin` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次登录',
  `regtm` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '账号创建时间',
  `pri` longblob NOT NULL COMMENT '账号扩展权限',
  `lv` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '跨平台GM等级',
  `flag` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '机器人操作',
  `pay` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '充值隐藏',
  `force_pwd` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '首次登录修改密码',
  `safekey` varchar(21) NOT NULL DEFAULT '' COMMENT '安全码',
  `baoliu` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否保留账号，=1不能修改角色，不能被删除',
  `wx_bind` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `platid` (`platid`),
  KEY `loginname_idx` (`loginname`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

#
# Data for table "admin_users"
#

INSERT INTO `admin_users` VALUES (1,1517286337,0,0,'admin','b1keZKbSj22Wg','Administrator','10.1.6.62','d86435322411aaa57757901ad32889a5_2018-01-30 09:25:37-admin',1517275537,1457505035,'',0,0,0,0,'b1cbGsPWO/GpQ',1,''),(2,1517286350,0,1,'mmmm','beZaf/YDJgsjo','mmmm','10.1.6.62','a6a11759d18d0b241bc2c30b94367857_2018-01-30 09:25:50-mmmm',1517275550,1457505035,X'00000000000000883D03',0,0,0,0,'beeK5bH3wgoSE',0,''),(3,0,0,0,'cccc','635j4Lo3GP7Go','cccc','','',0,1508989745,'',0,0,0,0,'63i0DmBwYtfcQ',0,''),(4,1516862886,0,0,'whx','a8nk0ELjNTyHA','whx','10.1.6.61','1018671004ef88ca739307f7c73511f6_2018-01-25 11:48:06-whx',1516852086,1508989952,'',0,0,0,0,'a8gUxZ2SyFI9.',0,''),(5,1509957394,1,0,'whx','a8nk0ELjNTyHA','whx','10.1.6.61','f05c3cbd866d87c3cda4a5477925466f_2017-11-06 13:36:34-whx',1509946594,1509946477,'',0,0,0,0,'a8gUxZ2SyFI9.',0,''),(10,1510286755,0,0,'test','1cQEzaspbQsYM','test','10.1.6.62','ae396608b5fd88dbbab51f5b0b5d0783_2017-11-10 09:05:55-test',1510275955,1510212273,'',0,0,0,0,'1cigD0W/eCAS6',0,''),(11,1514265880,0,0,'zcx','d5a8dKxR1mGbM','zcx','10.1.6.60','5b7dad1f0212ba811622a6ad30a53372_2017-12-26 10:24:40-zcx',1514255080,1511526851,'',0,0,0,0,'d59yK1uJ7YjN2',0,''),(12,1513686629,0,0,'whx2','82sVxs3FsKxuY','whx2','10.1.6.61','6e295b729303b039e5dd89925a5a4d96_2017-12-19 17:30:29-whx2',1513675829,1512546328,'',0,0,0,0,'82VmDkWFWKMEg',0,'oTbHxwurdraTffVYakpDVuulBb2o');

#
# Source for table "game_servers"
#

DROP TABLE IF EXISTS `game_servers`;
CREATE TABLE `game_servers` (
  `sid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'GameServer_id',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '服务器类型(0正常，1合服，2历史服)',
  `close` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '入口状态',
  `msg` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '聊天日志开关',
  `optm` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '开服时间',
  `opver` varchar(16) NOT NULL DEFAULT '' COMMENT '开服版本',
  `combine_sid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '合服后的sid',
  `combine_tm` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '合服时间',
  `game_port` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '服务器端口',
  `srv_conf_port` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '服务器配置端口',
  `srv_prt_port` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '服务器监控端口',
  `srv_ip` char(15) NOT NULL DEFAULT '' COMMENT '服务器IP',
  `srv_ip2` char(15) NOT NULL DEFAULT '' COMMENT '服务器IP2',
  `gamedb_ip` char(15) NOT NULL DEFAULT '' COMMENT '游戏数据库IP',
  `logdb_ip` char(15) NOT NULL DEFAULT '' COMMENT '日志数据库IP',
  `paydb_ip` char(15) NOT NULL DEFAULT '' COMMENT '充值数据库IP',
  `kfdb_ip` char(15) NOT NULL DEFAULT '' COMMENT '客服数据库IP',
  `gamedb_user` varchar(20) NOT NULL DEFAULT '' COMMENT '游戏数据库连接用户名',
  `gamedb_pwd` varchar(20) NOT NULL DEFAULT '' COMMENT '游戏数据库连接密码',
  `gamedb_name` varchar(20) NOT NULL DEFAULT '' COMMENT '游戏数据库名称',
  `logdb_user` varchar(20) NOT NULL DEFAULT '' COMMENT '日志数据库连接用户名',
  `logdb_pwd` varchar(20) NOT NULL DEFAULT '' COMMENT '日志数据库连接密码',
  `logdb_name` varchar(20) NOT NULL DEFAULT '' COMMENT '日志数据库名称',
  `paydb_user` varchar(20) NOT NULL DEFAULT '' COMMENT '充值数据库连接用户名',
  `paydb_pwd` varchar(20) NOT NULL DEFAULT '' COMMENT '充值数据库连接密码',
  `paydb_name` varchar(20) NOT NULL DEFAULT '' COMMENT '充值数据库名称',
  `kfdb_user` varchar(20) NOT NULL DEFAULT '' COMMENT '客服数据库连接用户名',
  `kfdb_pwd` varchar(20) NOT NULL DEFAULT '' COMMENT '客服数据库连接密码',
  `kfdb_name` varchar(20) NOT NULL DEFAULT '' COMMENT '客服数据库名称',
  `srv_name` varchar(60) NOT NULL DEFAULT '' COMMENT '服务器名称',
  `platids` varchar(128) NOT NULL DEFAULT '' COMMENT '所属平台ID(可多个,逗号分离)',
  `cq_root` varchar(128) NOT NULL DEFAULT '' COMMENT 'cqserver路径',
  `static_url` varchar(128) NOT NULL DEFAULT '' COMMENT '游戏静态资源地址',
  `web_static` varchar(128) NOT NULL DEFAULT '' COMMENT '页面静态资源地址',
  `iface_his` varchar(255) NOT NULL DEFAULT '' COMMENT '合服接口/联运混服SID',
  `ropass` varchar(20) NOT NULL DEFAULT '',
  `def` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '服务器类型默认服',
  `new` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '服务器类型新开服',
  `recomm` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '服务器类型推荐服',
  `srv_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '运营状态',
  PRIMARY KEY (`sid`),
  KEY `optm_idx` (`optm`),
  KEY `srv_ip_idx` (`srv_ip`),
  KEY `srv_ip2_idx` (`srv_ip2`),
  KEY `type_idx` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

#
# Data for table "game_servers"
#

INSERT INTO `game_servers` VALUES (1,1,0,0,1498042206,'V2.0',0,0,64999,8080,8081,'10.1.8.132','','127.0.0.1','127.0.0.1','127.0.0.1','127.0.0.1','root','123456','cquser1','root','123456','cqlog1','root','123456','cqpay1','root','123456','cqkf1','客户端11服','1','/home/coovanftp/ftp/server_client/whserver/','http://10.1.8.132/static_client/','http://10.1.8.132/static_gmd/','','',1,1,1,1),(2,0,1,1,1465464596,'',0,0,64999,8080,8081,'10.1.3.76','','127.0.0.1','127.0.0.1','10.1.3.76','10.1.3.76','root','123456','cquser2','root','123456','cqlog2','root','123456','cqpay2','root','123456','cqkf5','测试2','1,2','/home/coovanftp/ftp/server_client/whserver/','http://10.1.8.132/static_client/','http://10.1.8.132/static_gmd/','','',1,0,0,1),(3,0,1,0,1465464596,'',0,0,64943,9070,9071,'127.0.0.2','','127.0.0.1','127.0.0.1','127.0.0.1','127.0.0.1','root','123456','cquser2','root','123456','cqlog2','root','123456','cqpay2','root','123456','cqkf2','test3','1','/home/coovanftp/ftp/server_client/whserver/','http://10.1.8.132/static_client/','http://10.1.8.132/static_gmd/','','',0,0,0,4),(4,3,0,0,0,'',0,0,64999,8080,8081,'10.1.8.132','','127.0.0.1','127.0.0.1','127.0.0.1','127.0.0.1','root','123456','cquser1','root','123456','cqlog1','root','123456','cqpay1','root','123456','cqkf1','客户端服','2','/home/coovanftp/ftp/server_client/whserver/','http://10.1.8.132/static_client/','http://10.1.8.132/static_gmd/','','',0,0,0,0),(5,0,0,0,1465464596,'',0,0,64940,81,81,'1.1.1.1','','1.1.1.1','1.1.1.1','1.1.1.1','1.1.1.1','root','123456','cquser1','root','123456','cqlog1','root','123456','cqpay1','root','123456','cqkf1','test5','1','/home/coovanftp/ftp/server_client/whserver/','http://10.1.8.132/static_client/','http://10.1.8.132/static_gmd/','','',0,0,0,0),(6,1,0,0,1465464596,'',5,1472784654,64940,81,81,'2.2.2.2','','2.2.2.2','2.2.2.2','2.2.2.2','2.2.2.2','root','123456','cquser1','root','123456','cqlog1','root','123456','cqpay1','root','123456','cqkf1','test6','1','/home/coovanftp/ftp/server_client/whserver/','http://10.1.8.132/static_client/','http://10.1.8.132/static_gmd/','','',0,0,0,0),(7,2,0,0,1497680410,'V1.0',0,0,65069,9020,9021,'10.135.0.147','','10.135.2.171','10.135.2.171','10.135.2.171','10.135.2.171','root','1234561qaz9ol.','cquser_60_s421','root','1234561qaz9ol.','cqlog_60_s421','root','1234561qaz9ol.','cqpay_60_s421','root','1234561qaz9ol.','cqkf_60_s421','联盟112服(s421)','','/home/coovanftp/ftp/6000421/whserver/','','','','',0,0,0,0),(8,2,0,0,0,'V1.0',0,0,65069,9020,9021,'10.128.13','','10.135.2.171','10.135.2.171','10.135.2.171','10.135.2.171','root','1234561qaz9ol.','cquser_60_s421','root','1234561qaz9ol.','cqlog_60_s421','root','1234561qaz9ol.','cqpay_60_s421','root','1234561qaz9ol.','cqkf_60_s421','test8','','/home/coovanftp/ftp/6000421/whserver/','','','','',0,0,0,0),(9,0,0,0,1498838158,'v1.1',0,0,65069,9020,9021,'10.135.0.147','','10.135.2.171','10.135.2.171','10.135.2.171','10.135.2.171','root','1234561qaz9ol.','cquser_60_s421','root','1234561qaz9ol.','cqlog_60_s421','root','1234561qaz9ol.','cqlog_60_s421','root','1234561qaz9ol.','cqlog_60_s421','test9','2,3','/home/coovanftp/ftp/6000421/whserver/','http:///home/coovanftp/ftp/6000421/whserver/','http:///home/coovanftp/ftp/6000421/whserver/','','',0,0,0,0),(10,2,0,0,1498128646,'V1.10',0,0,10,10,10,'10.1.8.131','10.1.8.150','10.135.2.171','10.135.2.172','10.135.2.173','10.135.2.174','root1','1234561qaz9ol.1','cqkf_60_s42_1','root2','1234561qaz9ol.2','cqkf_60_s42_2','root3','1234561qaz9ol.3','cqkf_60_s42_3','root4','1234561qaz9ol.4','cqkf_60_s42_4','test10','16','/home/coovanftp/ftp/6000421/whserver/10','http://ro.cdnunion.com/res_rolr/v1.0.3.25/10','http://ro.cdnunion.com/res_rolr/static_gmd/10','','',0,0,0,0),(11,2,0,0,1498747817,'V1.11',0,0,65311,65311,65311,'127.5.6.3','127.5.6.8','10.135.2.171','10.135.2.171','10.135.2.171','10.135.2.171','root11','123456','cquser_60_s421','root12','123456','cquser_60_s421','root13','123456','cquser_60_s421','root13','123456','cquser_60_s421','联盟112服(s421)','','/home/coovanftp/ftp/6000421/whserver/','http://ro.cdnunion.com/res_rolr/v1.0.3.25/','http://ro.cdnunion.com/res_rolr/static_gmd/','','',0,0,0,0),(12,2,0,0,1498113030,'V1.12',0,0,12,12,12,'12.8.9.3','12.8.9.4','10.135.2.171','10.135.2.171','10.135.2.171','10.135.2.171','root','123456','cqkf_60_s421','root','123456','cqkf_60_s421','root','123456','cqkf_60_s421','root','123456','cqkf_60_s421','test12','6','/home/coovanftp/ftp/6000421/whserver/','http://ro.cdnunion.com/res_rolr/v1.0.3.25/','http://ro.cdnunion.com/res_rolr/static_gmd/','','',0,0,0,0),(22,2,0,0,1498113030,'V1.12',0,0,12,12,12,'12.8.9.3','12.8.9.4','10.135.2.171','10.135.2.171','10.135.2.171','10.135.2.171','root','123456','cqkf_60_s421','root','123456','cqkf_60_s421','root','123456','cqkf_60_s421','root','123456','cqkf_60_s421','test22','1,6','/home/coovanftp/ftp/6000421/whserver/','http://ro.cdnunion.com/res_rolr/v1.0.3.25/','http://ro.cdnunion.com/res_rolr/static_gmd/','','',0,0,0,3),(23,0,0,0,1497684894,'V1.8',0,0,65059,9030,9031,'10.104.215.84','','10.104.222.52','10.104.222.52','10.104.222.52','10.104.222.52','root','1234561qaz9ol.','cquser_60_s366_h1','root','1234561qaz9ol.','cquser_60_s366_h1','root','1234561qaz9ol.','cquser_60_s366_h1','root','1234561qaz9ol.','cquser_60_s366_h1','联盟合1(91-93)(366-368)','8,16','/home/coovanftp/ftp/6000366/whservers/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/','','',0,0,0,0),(24,1,0,0,1497684894,'V1.8',123321,1497874620,65059,9030,9031,'10.104.215.84','','10.104.222.52','10.104.222.52','10.104.222.52','10.104.222.52','root','1234561qaz9ol.','cquser_60_s366_h1','root','1234561qaz9ol.','cquser_60_s366_h1','root','1234561qaz9ol.','cquser_60_s366_h1','root','1234561qaz9ol.','cquser_60_s366_h1','联盟合1(91-93)(366-368)','1','/home/coovanftp/ftp/6000366/whservers/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/','','',0,0,0,0),(25,0,0,0,1497684894,'V1.8',0,0,65059,9030,9031,'10.104.215.84','','10.104.222.52','10.104.222.52','10.104.222.52','10.104.222.52','root','1234561qaz9ol.','cquser_60_s366_h1','root','1234561qaz9ol.','cquser_60_s366_h1','root','1234561qaz9ol.','cquser_60_s366_h1','root','1234561qaz9ol.','cquser_60_s366_h1','联盟合1(91-93)(366-368)','1,2,3,4,5','/home/coovanftp/ftp/6000366/whservers/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/','','',0,0,0,0),(26,2,0,0,1501210247,'V1.8.28',888888,1498833000,65535,65535,65535,'10.104.215.27','28','10.104.222.52.2','10.104.222.52.2','10.104.222.52.2','10.104.222.52','root28','1234561qaz9ol.28','cquser_60_s366_h128','root28','1234561qaz9ol.28','cquser_60_s366_h128','root28','1234561qaz9ol.28','cquser_60_s366_h128','root28','1234561qaz9ol.28','cquser_60_s366_h128','联盟合1(91-93)(366-368)','1,6','/home/coovanftp/ftp/6000366/whservers/28/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/28/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/28/','28','',1,1,1,5),(27,1,0,0,0,'',0,1497874620,0,0,0,'10.104.215.84','','','','','','','','','','','','','','','','','','联盟合1(91-93)(366-368)','','','','','','',0,0,0,0),(28,1,0,0,0,'',124,1497874620,0,0,0,'10.104.215.84','','','','','','','','','','','','','','','','','','联盟合1(91-93)(366-368)','','','','','','',0,0,0,0),(29,3,0,0,1501469447,'V1.8.290',888290,1504189800,65290,65290,65290,'10.104.215.290','290','10.104.222.290','10.104.222.290','10.104.222.290','10.104.222.290','root290','1234561qaz9ol290','cquser_60_s366_h290','root290','1234561qaz9ol290','cquser_60_s366_h290','root290','1234561qaz9ol290','cquser_60_s366_h290','root290','1234561qaz9ol.290','cquser_60_s366_h290','联盟合1(91-93)(366-290)','8,16','/home/coovanftp/ftp/6000366/whservers/290/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/290/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/290/','290','',0,1,0,0),(30,2,0,0,1501210247,'V1.8.28',1970,1498833000,65535,65535,65535,'10.104.215.27','28','10.104.222.52.2','10.104.222.52.2','10.104.222.52.2','10.104.222.52','root28','1234561qaz9ol.28','cquser_60_s366_h128','root28','1234561qaz9ol.28','cquser_60_s366_h128','root28','1234561qaz9ol.28','cquser_60_s366_h128','root28','1234561qaz9ol.28','cquser_60_s366_h128','联盟合1(91-93)(366-368)','1,6','/home/coovanftp/ftp/6000366/whservers/28/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/28/','http://ro.cdnunion.com/res_rolr/v1.0.3.29/28/','28','',1,1,1,4),(411,0,0,1,1512022826,'v1.0.0.0.0.8',0,0,6000,6001,6002,'10.1.8.145','','127.0.0.1','127.0.0.1','127.0.0.1','127.0.0.1','root','123456','cquser1','root','123456','cqlog1','root','123456','cqpay1','root','123456','cqkf1','TEST','1,2,3','1/','1/','1/','','',1,1,1,3);

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
