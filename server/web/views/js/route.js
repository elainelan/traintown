/*
        .state("类名_方法名",{
            url:"/类名/方法名",
            templateUrl:"pages/类名/方法名.html",
            controller:"类名.方法名.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/类名/方法名.js");
                }]
            }
        })
*/


app.config(["$stateProvider","$urlRouterProvider",function ($stateProvider,$urlRouterProvider) {
    $urlRouterProvider.otherwise("/welcome");
    $stateProvider
        // 欢迎页
        .state("welcome",{
            url:"/welcome",
            templateUrl:"pages/welcome.html",
            controller:"welcome.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/welcome.js");
                }]
            }
        })
        // 我的登录
        .state("admin_mylogin_list",{
            url:"/admin/mylogin_list",
            templateUrl:"pages/admin/mylogin_list.html",
            controller:"admin.mylogin_list.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/mylogin_list.js");
                }],
                moment:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/moment/min/moment.min.js").then(function(){
                        return $ocLazyLoad.load('lib/bower_components/bootstrap-daterangepicker/daterangepicker.js');
                    });
                }],
                daterangepicker_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-daterangepicker/daterangepicker.css");
                }]
            }
        })
        // 我的密码
        .state("admin_modpwd_self",{
            url:"/admin/modpwd_self",
            templateUrl:"pages/admin/modpwd_self.html",
            controller:"admin.modpwd_self.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/modpwd_self.js");
                }]
            }
        })

        // 登录记录
        .state("admin_login_list",{
            url:"/admin/login_list/14",
            templateUrl:"pages/admin/login_list.html",
            controller:"admin.login_list.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/login_list.js");
                }],
                moment:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/moment/min/moment.min.js").then(function(){
                        return $ocLazyLoad.load('lib/bower_components/bootstrap-daterangepicker/daterangepicker.js');
                    });
                }],
                daterangepicker_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-daterangepicker/daterangepicker.css");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // IP白名单
        .state("admin_ip_white",{
            url:"/admin/ip_white/14",
            templateUrl:"pages/admin/ip_white.html",
            controller:"admin.ip_white.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/ip_white.js");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // IP黑名单
        .state("admin_ip_black",{
            url:"/admin/ip_black/14",
            templateUrl:"pages/admin/ip_black.html",
            controller:"admin.ip_black.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/ip_black.js");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // 平台管理
        .state("platforms_manager",{
            url:"/platforms/manager",
            templateUrl:"pages/platforms/manager.html",
            controller:"platforms.manager.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/platforms/manager.js");
                }],
                moment:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/moment/min/moment.min.js").then(function(){
                        return $ocLazyLoad.load('lib/bower_components/bootstrap-datetimepicker/bootstrap-datetimepicker.js');
                    });
                }],
                daterangepicker_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-datetimepicker/bootstrap-datetimepicker.css");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // 区服管理
        .state("servers_manager",{
            url:"/servers/manager",
            templateUrl:"pages/servers/manager.html",
            controller:"servers.manager.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/servers/manager.js");
                }],
                moment:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/moment/min/moment.min.js").then(function(){
                        return $ocLazyLoad.load('lib/bower_components/bootstrap-datetimepicker/bootstrap-datetimepicker.js');
                    });
                }],
                datetimepicker_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-datetimepicker/bootstrap-datetimepicker.css");
                }],
                select_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-select/css/bootstrap-select.min.css");
                }],
                select:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-select/js/bootstrap-select.min.js");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }],
                daterangepicker_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-daterangepicker/daterangepicker.css");
                }],
                daterangepicker:["$ocLazyLoad",function($ocLazyLoad){
		                return $ocLazyLoad.load('lib/bower_components/bootstrap-daterangepicker/daterangepicker.js');
		        }]
            }
        })
        // 给用户添加权限角色
        .state("admin_permission_user",{
            url:"/admin/permission_user/7",
            templateUrl:"pages/admin/permission_user.html",
            controller:"admin.permission_user.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/permission_user.js");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // 账号管理
        .state("admin_account",{
            url:"/admin/account",
            templateUrl:"pages/admin/account.html",
            controller:"admin.account.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/account.js");
                }],
                moment:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/moment/min/moment.min.js").then(function(){
                        return $ocLazyLoad.load('lib/bower_components/bootstrap-daterangepicker/daterangepicker.js');
                    });
                }],
                daterangepicker_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-daterangepicker/daterangepicker.css");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // 给权限角色添加用户
        .state("admin_permissin_role",{
            url:"/admin/permission_role/7",
            templateUrl:"pages/admin/permission_role.html",
            controller:"admin.permission_role.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/permission_role.js");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // 权限角色分配权限
        .state("admin_permission",{
            url:"/admin/permission/7",
            templateUrl:"pages/admin/permission.html",
            controller:"admin.permission.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/permission.js");
                }],
                zTree_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/jquery-ztree/css/zTreeStyle/zTreeStyle.css");
                }],
                zTree:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/jquery-ztree/js/jquery.ztree.core.js").then(function(){
                        return $ocLazyLoad.load('lib/bower_components/jquery-ztree/js/jquery.ztree.excheck.js');
                    });
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // 我的操作
        .state("admin_myoperation_list",{
            url:"/admin/myoperation_list",
            templateUrl:"pages/admin/myoperation_list.html",
            controller:"admin.myoperation_list.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/myoperation_list.js");
                }],
                moment:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/moment/min/moment.min.js").then(function(){
                        return $ocLazyLoad.load('lib/bower_components/bootstrap-daterangepicker/daterangepicker.js');
                    });
                }],
                daterangepicker_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-daterangepicker/daterangepicker.css");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // 操作管理
        .state("admin_operation_list",{
            url:"/admin/operation_list",
            templateUrl:"pages/admin/operation_list.html",
            controller:"admin.operation_list.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/operation_list.js");
                }],
                moment:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/moment/min/moment.min.js").then(function(){
                        return $ocLazyLoad.load('lib/bower_components/bootstrap-daterangepicker/daterangepicker.js');
                    });
                }],
                daterangepicker_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-daterangepicker/daterangepicker.css");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // 配置管理
        .state("admin_settings",{
            url:"/admin/settings",
            templateUrl:"pages/admin/settings.html",
            controller:"admin.settings.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/admin/settings.js");
                }],
                moment:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/moment/min/moment.min.js").then(function(){
                        return $ocLazyLoad.load('lib/bower_components/bootstrap-daterangepicker/daterangepicker.js');
                    });
                }],
                daterangepicker_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/bootstrap-daterangepicker/daterangepicker.css");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
        // 数据导出
        .state("export_table",{
            url:"/export/table",
            templateUrl:"pages/export/table.html",
            controller:"export.table.ctrl",
            resolve:{
                deps:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("js/export/table.js");
                }],
                select2_css:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/css/select2.min.css");
                }],
                select2:["$ocLazyLoad",function($ocLazyLoad){
                    return $ocLazyLoad.load("lib/bower_components/select2/dist/js/select2.full.min.js");
                }]
            }
        })
}]);
