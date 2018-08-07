app.controller('admin.account.ctrl', function ($scope, focus, $filter, $rootScope, $http) {

    var api_name = 'admin.account';
    
     // 生成面包屑导航
    $rootScope.breadcrumb();
    
    // 合并页面语言包
    $rootScope.setLang(api_name.replace(/\./, '/')+'.json');
    
    // 页面变量初始化
    $scope.r = 0; // 获取结果状态
    
    // ====分页种类 如果需要自定义，可以在这里重新写
    //$scope.page_number = $rootScope.page_number;
    // ====默认分页数量
    $scope.length_select = $rootScope.get_default_page_number();
    
    $scope.search = {}; // 搜索
    $scope.add = {};
    $scope.manager = {};// 管理
    $scope.headfunc = {};// 功能
    $scope.headfunc.manager = $rootScope.getPrivileges(api_name+'.manager');
    // ====排序
    $scope.orderby = {orderby:{id:'asc'}};
    
    //var today = new Date();
    //var before = new Date();
    //before.setDate(today.getDate()-30);
    //$scope.search.lastlogin = $filter('date')(before, 'yyyy-MM-dd') + ' - ' + $filter('date')(today, 'yyyy-MM-dd');
    //$scope.lastlogin = '2016/11/09 00:00 - 2016/11/12 23:00';
    
    
    // ui-grid
    // ====调整样式的grid高度
    $scope.ui_grid_style = {};
    // ====gridOptions初始化
    $scope.gridOptions = $rootScope.ui_grid.init();
    // ====gridOptions.columnDefs初始化(语言包加载完成后执行)
    $scope.unWatch = $scope.$watch('langs.force_pwd', function(){
        if ($rootScope.langs.force_pwd) {
            $scope.unWatch();
            $scope.gridOptions.columnDefs = [
                $rootScope.ui_grid.get_seq(), // 序号
                $rootScope.ui_grid.get_platform('platid', 'platform'), // 所属平台
                $rootScope.ui_grid.get_user('id', 'username'), // 使用者
                { field: 'loginname', displayName: $rootScope.langs['loginname'], minWidth: 80 }, // 账号
                $rootScope.ui_grid.get_ip('ip', 'login_ip'), // 最近登录IP
                $rootScope.ui_grid.get_ts('lastlogin', 'lastlogin'), // 最近登录时间
                $rootScope.ui_grid.get_ts('regtm', 'regtm'), // 注册时间
                { field: 'force_pwd', displayName: $rootScope.langs['force_pwd'], minWidth: 80, visible: false } // 登录改密
            ];
            // 管理栏位
            if ($scope.headfunc.manager) {
                var operation_manager_button = '<button data-ng-if="row.entity.baoliu != 1" data-ng-click="grid.appScope.manager(row.entity)" title="{{grid.appScope.langs.manage}}" class="pull-left btn btn-primary btn-xs btn-func"><i class="fa fa-wrench"></i></button>';
                var operation_del_button = '<button data-ng-if="row.entity.baoliu != 1" data-ng-click="grid.appScope.modal_del(row.entity)" title="{{ grid.appScope.langs.del }}" class="pull-left btn btn-danger btn-xs btn-func"><i class="fa fa-times"></i></button>';
                $scope.gridOptions.columnDefs.push( { enableSorting: false, enableFiltering: false, field: 'operation', displayName: $rootScope.langs['operation'], minWidth: 80, cellTemplate: '<div class="ui-grid-cell-contents">'+operation_manager_button+operation_del_button+'</div>' } );
            }
            
            $scope.gridOptions.exportColumnDefs = {
                    seq:$rootScope.ui_grid.get_export_seq,
                    platid: $rootScope.ui_grid.get_export_platform,
                    id: $rootScope.ui_grid.get_export_user,
                    lastlogin:$rootScope.ui_grid.get_export_ts,
                    regtm:$rootScope.ui_grid.get_export_ts
            }
            $scope.gridOptions.exporterFieldCallback = function( grid, row, col, input ) {
                if( typeof($scope.gridOptions.exportColumnDefs[col.name]) == 'function' ){
                    return $scope.gridOptions.exportColumnDefs[col.name]( grid, row, col, input, $filter );
                }
                else {
                    return input;
                }
            };
            $scope.gridOptions.exporterCsvFilename = api_name + '.csv';
        }
    });
    // ====注入事件处理方法
    $scope.gridOptions.onRegisterApi = function( gridApi ) {
        $scope.gridApi = gridApi;
        $scope.gridApi.core.on.sortChanged( $scope, $scope.sortChanged );
    };
    
    
    // ====外部排序
    // ========开启
    $scope.gridOptions.useExternalSorting = true;
    // ========事件处理
    $scope.sortChanged = function ( grid, sortColumns ) {
        $scope.orderby.orderby = {};
        if (sortColumns.length > 0) {
            angular.forEach(sortColumns, function(data){
                $scope.orderby.orderby[data.field] = data.sort.direction;
            });
        }
        // 重新获取数据
        $scope.get_data();
    };
    
    
    
    // ==============数据获取=================
    $scope.get_data = function(page) {
        var post_data = { a:'get', page:page, num:$scope.length_select };
        // 合并查询条件
        angular.extend(post_data, $scope.search, $scope.orderby);
        
        // 获取第几页数据
        $http.post(getApiUrl(api_name), post_data).then(function (response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            } else {
                $scope.r = 1;
                $scope.data = response.data.data;
                
                
                // 显示数据绑定
                $scope.ui_grid_style.height = (parseInt($scope.data.items.length) + 1)*30 + 'px';
                $scope.gridOptions.data = $scope.data.items;
                
                $scope.go_page=$scope.data.page_current;
                // 搜索条件样式重置
                $scope.modal_search_reset();
            }
        });
    }
    $scope.jump = function (page) {
        if (!$scope.is_moded()){
            // GO跳转/分页长度调整
            $scope.get_data(page);
        }
    }
    $scope.get_data(); // 页面初始化，先获取第一页数据
    // ==================./数据获取============
    
    // ================== 数据下载=============
    $scope.download = function() {
        var post_data = { a:'download' };
        // 合并查询条件
        angular.extend(post_data, $scope.search, $scope.orderby);
        
        // 获取下载地址
        $http.post(getApiUrl(api_name), post_data).then(function (response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            } else {
                console_log('download', response.data);
                window.location.href = getExport(response.data.data);
            }
        });
    }
    // ==================./数据下载============
    
    // ==================查找=================
    $scope.modal_search = function () {
        if (!$scope.is_moded()){
            $('#modal_search').modal("show");
        }
    }
    $scope.modal_search_platform = function() {
        // 选择平台后，用户如果不是选择平台的用户，重置
        if ($scope.search.admin_userid && $scope.search.platid != $rootScope.admin_users[$scope.search.admin_userid].platid) {
            $scope.search.admin_userid = null;
        }
        // 如果标题栏处于搜索功能已经提交的状态
        $scope.modal_search_title_not_submit();
    }
    $scope.modal_search_ok = function() {
        // 确认搜索
        $scope.get_data(1);
        $('#modal_search').modal("hide");
    }
    // ==================./查找===============
    
    // ===================删除=================
    $scope.modal_del = function(obj) {
        if (!$scope.is_moded()) {
            // 弹出删除确认框
            $rootScope.langs.modal_del_body = $rootScope.langs.confirm_del_account;
            $scope.delobj = obj;
            $('#modal_del').modal("show");
        }
    }
    $scope.modal_del_ok = function() {
        var post_data = { a:'manager', b:'del'};
        // 合并删除条件
        angular.extend(post_data, $scope.delobj);
        // 确认删除
        $('#modal_del').modal("hide");
        $http.post(getApiUrl(api_name), post_data).then(function(response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            }
            else {
                // 重新获取adminUsers信息
                $rootScope.admin_users = undefined;
                $rootScope.getAdminUsers();
                $scope.jump($scope.data.page_current);
            }
        });
    }
    // =================./删除================
    
    // ================管理=============
    $scope.manager = function (x) {
        if (!$scope.is_moded()) {
            $scope.manager.reset_force_pwd = x.force_pwd;
            $scope.manager.reset_gm_id = x.id;
            $scope.manager.resetpwd = "";
            $scope.manager.reset_sk = "";
            
            $("#modal_manager").modal('show');
        }
    };
    // 确认提交
    $scope.manager_ok = function () {
        var post_data = { a:'manager', b:'manager'};
        // 合并条件
        angular.extend(post_data, $scope.manager);
        // 确认删除
        $("#modal_manager").modal('hide');
        $http.post(getApiUrl(api_name), post_data).then(function(response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            }
            else {
                $rootScope.modal_succ($scope.langs.reset_succ);
                $scope.jump($scope.data.page_current);
            }
        });
    };

    // ===================./管理角色权限===================.
    
    // ===================添加===================
    $scope.modal_add = function () {
        if (!$scope.is_moded()){
            // 弹出添加框
            
            $('#modal_add').modal('show');
        }
    };
    $scope.modal_add_check = function() {
        // 添加数据验证
        return true;
    }
    $scope.modal_add_reset = function() {
        // 新增重置功能
        $scope.add = {};
        $scope.add.platid = $rootScope.users.data.platid;
        $scope.add.loginpwd = '';
        $scope.add.safekey = '';
        $scope.add.force_pwd = '1';
        focus('add_name');
        
        // $example.val("CA").trigger("change")方式会出错，trigger和AngularJS有冲突
        // select2强制刷新
        $('.add_select2').val($rootScope.users.data.platid).select2("destroy").select2();
        
        // 表单验证重新处理
        $('#modal_add').bootstrapValidator('resetForm', 'true');
        $('#modal_add').data('bootstrapValidator').validate();
    }
    $scope.modal_add_ok = function () {
        // 确认添加
        if ($scope.modal_add_check()) {
            $('#modal_add').modal('hide');
            
            var post_data = { a:'manager', b:'add'};
            // 合并添加数据
            angular.extend(post_data, $scope.add);
            
            $http.post(getApiUrl(api_name), post_data).then(function(response) {
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode, $scope.modal_add);
                }
                else {
                    // 重新获取账号信息
                    $rootScope.admin_users = undefined;
                    $rootScope.getAdminUsers();
                    $scope.jump($scope.data.page_current);
                    $scope.modal_add_reset();
                }
            });
        }
    };
    // ===================./添加==================

    // ===================修改===================
    $scope.is_moded = function() {
        // 是否正在修改
        if (typeof($scope.modobj) != 'undefined' && $scope.modobj) {
            $rootScope.modal_err($rootScope.langs.has_moded, function () {
                focus("mod_notes");
            });
            return true;
        }
        return false;
    }
    $scope.mod_cancel = function(obj) {
        // 点击修改取消（关闭保存和取消按钮，显示修改按钮）
        obj.edit = false;
        obj.notes = $scope.modobj.notes;
        delete $scope.modobj;
    }
    $scope.moded_add = function(obj) {
        // 点击修改按钮（关闭修改按钮，显示保存和取消按钮）
        obj.edit = true;
        $scope.modobj = angular.copy(obj);
        focus("mod_notes");
    }
    $scope.mod = function (obj) {
        // 点击修改按钮
        if (!$scope.is_moded()) {
            $scope.moded_add(obj);
        }
    };
    $scope.mod_ok = function (obj) {
        if ($scope.modobj.notes != obj.notes ){
            var post_data = { a:'manager', b:'mod'};
            // 合并修改数据
            angular.extend(post_data, obj);

            $http.post(getApiUrl(api_name),post_data).then(function (response) {
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode);
                }
                else {
                    $scope.jump($scope.data.page_current);
                }
            });
        }
        $scope.mod_cancel(obj);
    };
    // ===================./修改===================
    
    // ===================模态框回调===============
    $('#modal_search').on("shown.bs.modal", function(){
        focus('search_ip');
        // 表单内容验证
        $("#modal_search").data('bootstrapValidator').validate();
        if ($("#modal_search").data('bootstrapValidator').isValid()) {
            $('#modal_search').bootstrapValidator('disableSubmitButtons', false);
        }
    });
    $('#modal_search').on("show.bs.modal", function(){
        $scope.search_ext_load();
    });
    
    $('#modal_add').on("shown.bs.modal", function(){
        focus('add_name');
        // 表单内容验证
        $("#modal_add").data('bootstrapValidator').validate();
        if ($("#modal_add").data('bootstrapValidator').isValid()) {
            $('#modal_add').bootstrapValidator('disableSubmitButtons', false);
        }
    });
    $('#modal_add').on("show.bs.modal", function(){
        $scope.add_ext_load();
    });
    
    $('#modal_manager').on("show.bs.modal", function(){
        $scope.manager_ext_load();
    });
    $('#modal_manager').on("shown.bs.modal", function(){
        focus('resetpwd');
        
        // 表单内容验证
        $("#modal_manager").data('bootstrapValidator').validate();
        if ($("#modal_manager").data('bootstrapValidator').isValid()) {
            $('#modal_manager').bootstrapValidator('disableSubmitButtons', false);
        }
    });
    $('#modal_manager').on("hide.bs.modal", function(){
        // 表单验证重置
        $('#'+$scope.modal_manager_id).bootstrapValidator('resetForm', 'true');
    });
    // ===================./模态框回调=============
    
    // 获取平台信息
    $rootScope.getPlatforms(0);
    
    // 获取后台用户信息
    $rootScope.getAdminUsers();
    
    $scope.search_ext_load = function() {
        if (typeof($scope.search_ext_loaded) == 'undefined') {
            
            // 表单验证插件
            // ====配置变量
            $scope.modal_search_id = 'modal_search';
            $scope.modal_search_fields = 
            {
                search_regtm: {
                    message: $rootScope.langs.search_regtm_invalid,
                    validators: {
                        regexp: {
                            regexp: $rootScope.regexp.daterange,
                            message: $rootScope.langs.search_regtm_invalid
                        }
                    }
                },
                search_lastlogin: {
                    message: $rootScope.langs.search_lastlogin_invalid,
                    validators: {
                        regexp: {
                            regexp: $rootScope.regexp.daterange,
                            message: $rootScope.langs.search_lastlogin_invalid
                        }
                    }
                },
                search_ip: {
                    message: $rootScope.langs.search_ip_invalid,
                    validators: {
                        regexp: {
                            regexp: $rootScope.regexp.ip,
                            message: $rootScope.langs.search_ip_invalid
                        }
                    }
                }
            };
            // ====初始化
            $rootScope.form_bootstrapValidator_init($scope.modal_search_id, $scope.modal_search_fields, $rootScope.form_bootstrapValidator_excluded);
            
            
            
            // 日期插件
            // ====配置变量
            $scope.daterange_regtm_id = 'search_regtm';
            $scope.daterange_lastlogin_id = 'search_lastlogin';
            // ====初始化
            $rootScope.daterangepicker_init($scope.daterange_regtm_id);
            $rootScope.daterangepicker_init($scope.daterange_lastlogin_id);
            // ====日期变化处理regtm
            $('#'+$scope.daterange_regtm_id)
            .on('apply.daterangepicker', function(ev, picker) {
                $scope.search.regtm = picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD');
                $(this).val($scope.search.regtm);
                $scope.modal_search_title_not_submit();
                $('#'+$scope.modal_search_id).bootstrapValidator('revalidateField', $scope.daterange_regtm_id);
            })
            .on('cancel.daterangepicker', function(ev, picker) {
                $scope.search.regtm = '';
                $(this).val($scope.search.regtm);
                $scope.modal_search_title_not_submit();
                $('#'+$scope.modal_search_id).bootstrapValidator('revalidateField', $scope.daterange_regtm_id);
            });
            // ====日期变化处理lastlogin
            $('#'+$scope.daterange_lastlogin_id)
            .on('apply.daterangepicker', function(ev, picker) {
                $scope.search.lastlogin = picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD');
                $(this).val($scope.search.lastlogin);
                $scope.modal_search_title_not_submit();
                $('#'+$scope.modal_search_id).bootstrapValidator('revalidateField', $scope.daterange_lastlogin_id);
            })
            .on('cancel.daterangepicker', function(ev, picker) {
                $scope.search.lastlogin = '';
                $(this).val($scope.search.lastlogin);
                $scope.modal_search_title_not_submit();
                $('#'+$scope.modal_search_id).bootstrapValidator('revalidateField', $scope.daterange_lastlogin_id);
            });
            
            
            
            // 选择插件
            $('.search_select2').select2();
            
            
            
            $scope.search_ext_loaded = true;
        }
    }
    
    $scope.add_ext_load = function() {
        if (typeof($scope.add_ext_loaded) == 'undefined') {
            
            // 表单验证插件
            // ====配置变量
            $scope.modal_add_id = 'modal_add';
            $scope.modal_add_fields = 
            {
                add_name: {
                    message: $rootScope.langs.add_name_invalid,
                    validators: {
                        stringLength: {
                            max: 30,
                            message: $rootScope.langs.add_name_max_length
                        },
                        notEmpty: {
                            message: $rootScope.langs.add_name_not_empty
                        }
                    }
                },
                add_loginname: {
                    message: $rootScope.langs.add_loginname_invalid,
                    validators: {
                        stringLength: {
                            max: 30,
                            message: $rootScope.langs.add_loginname_max_length
                        },
                        notEmpty: {
                            message: $rootScope.langs.add_loginname_not_empty
                        }
                    }
                },
                add_loginpwd: {
                    message: $rootScope.langs.add_loginpwd_invalid,
                    validators: {
                        stringLength: {
                            message: $rootScope.langs.pwd_stringLength,
                            min: 8,
                            max: 12
                        },
                        regexp: {
                            regexp: $rootScope.regexp.password,
                            message: $rootScope.langs.pwd_regexp
                        },
                        notEmpty: {
                            message: $rootScope.langs.add_loginpwd_not_empty
                        }
                    }
                },
                add_safekey: {
                    message: $rootScope.langs.add_safekey_invalid,
                    validators: {
                        stringLength: {
                            min: 4,
                            max: 6,
                            message: $rootScope.langs.safekey_stringLength
                        },
                        regexp: {
                            regexp: $rootScope.regexp.safekey,
                            message: $rootScope.langs.safekey_regexp
                        },
                        notEmpty: {
                            message: $rootScope.langs.add_safekey_not_empty
                        }
                    }
                }
            };
            // ====初始化
            $rootScope.form_bootstrapValidator_init($scope.modal_add_id, $scope.modal_add_fields, $rootScope.form_bootstrapValidator_excluded);
            
            
            
            // 选择插件
            $('.add_select2').select2();
            
            
            
            $scope.add_ext_loaded = true;
        }
    }
    
    
    $scope.manager_ext_load = function() {
        if (typeof($scope.manager_ext_loaded) == 'undefined') {
            
            // 表单验证插件
            // ====配置变量
            $scope.modal_manager_id = 'modal_manager';
            $scope.modal_manager_fields = 
            {
                resetpwd: {
                    message: $rootScope.langs.manager_resetpwd_invalid,
                    validators: {
                        stringLength: {
                            message: $rootScope.langs.pwd_stringLength,
                            min: 8,
                            max: 12
                        },
                        regexp: {
                            regexp: $rootScope.regexp.password,
                            message: $rootScope.langs.pwd_regexp
                        }
                    }
                },
                reset_sk: {
                    message: $rootScope.langs.manager_reset_sk_invalid,
                    validators: {
                        stringLength: {
                            message: $rootScope.langs.safekey_stringLength,
                            min: 4,
                            max: 6
                        },
                        regexp: {
                            regexp: $rootScope.regexp.safekey,
                            message: $rootScope.langs.safekey_regexp
                        }
                    }
                }
            };
            // ====初始化
            $rootScope.form_bootstrapValidator_init($scope.modal_manager_id, $scope.modal_manager_fields, $rootScope.form_bootstrapValidator_excluded);
            
            $scope.manager_ext_loaded = true;
        }
    }
});

