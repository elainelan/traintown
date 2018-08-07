app.controller('admin.ip_black.ctrl', function ($scope, focus, $rootScope, $http) {

    var api_name = 'admin.ip_black';
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
    
    $scope.headfunc = {};// 功能
    $scope.headfunc.manager = $rootScope.getPrivileges(api_name+'.manager');
    
    $scope.add = {};
    $scope.add.platid = $rootScope.users.data.platid;
    

    // ==============数据获取=================
    $scope.get_data = function(page) {
        var post_data = { a:'get', page:page, num:$scope.length_select };
        // 合并查询条件
        angular.extend(post_data, $scope.search);
        
        // 获取第几页数据
        $http.post(getApiUrl(api_name), post_data).then(function (response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            } else {
                $scope.r = 1;
                $scope.data = response.data.data;
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
    
    // ==================查找=================
    $scope.modal_search = function () {
        if (!$scope.is_moded()){
            $('#modal_search').modal("show");
        }
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
            $rootScope.langs.modal_del_body = $rootScope.langs.confirm_del_black_ip;
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
                $scope.jump($scope.data.page_current);
            }
        });
    }
    // =================./删除================
    
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
    $scope.modal_add_reset = function () {
        // 新增重置功能
        $scope.add = {};
        $scope.add.platid = $rootScope.users.data.platid;
        $scope.add.ip = '';
        focus('add_ip');

        // $example.val("CA").trigger("change")方式会出错，trigger和AngularJS有冲突
        // select2强制刷新
        $('#add_select2').val($rootScope.users.data.platid).select2("destroy").select2();
        
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
                    $rootScope.modal_err(response.data.errCode);
                }
                else {
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
        focus('search_black_ip');
        // 表单内容验证
        $("#modal_search").data('bootstrapValidator').validate();
        if ($("#modal_search").data('bootstrapValidator').isValid()) {
            $('#modal_search').bootstrapValidator('disableSubmitButtons', false);
        }
    });
    $('#modal_search').on("show.bs.modal", function(){
        $scope.ext_search_load();
    });
    
    $('#modal_add').on("shown.bs.modal", function(){
        focus('add_ip');
        // 表单内容验证
        $("#modal_add").data('bootstrapValidator').validate();
        if ($("#modal_add").data('bootstrapValidator').isValid()) {
            $('#modal_add').bootstrapValidator('disableSubmitButtons', false);
        }
    });
    $('#modal_add').on("show.bs.modal", function(){
        $scope.ext_add_load();
    });
    // ===================./模态框回调=============
    
    // 获取平台信息
    $rootScope.getPlatforms(0);
    
    
    // ===================扩展插件加载=============
    $scope.ext_search_load = function() {
        if (typeof($scope.search_ext_loaded) == 'undefined') {
            
            // 表单验证插件
            // ====配置变量
            $scope.modal_search_id = 'modal_search';
            $scope.modal_search_fields = 
            {
                search_black_ip: {
                    message: $rootScope.langs.search_black_ip_invalid,
                    validators: {
                        regexp: {
                            regexp: $rootScope.regexp.ip_regexp,
                            message: $rootScope.langs.search_black_ip_invalid
                        }
                    }
                },
                search_notes: {
                    message: $rootScope.langs.search_notes_invalid,
                    validators: {
                        stringLength: {
                            message: $rootScope.langs.search_notes_stringLength,
                            max: 100
                        }
                    }
                }
            };
            // ====初始化
            $rootScope.form_bootstrapValidator_init($scope.modal_search_id, $scope.modal_search_fields, $rootScope.form_bootstrapValidator_excluded);
            
            
            
//            // 日期插件
//            // ====配置变量
//            $scope.search_date_id = 'search_time';
//            // ====初始化
//            $rootScope.daterangepicker_init($scope.search_date_id);
//            // ====日期变化处理
//            $('#'+$scope.search_date_id)
//            .on('apply.daterangepicker', function(ev, picker) {
//                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
//                $scope.search.time = $(this).val();
//                $rootScope.modal_search_title_not_submit();
//                $('#'+$scope.modal_search_id).bootstrapValidator('revalidateField', $scope.search_date_id);
//            })
//            .on('cancel.daterangepicker', function(ev, picker) {
//                $(this).val('');
//                $scope.search.time = $(this).val();
//                $rootScope.modal_search_title_not_submit();
//                $('#'+$scope.modal_search_id).bootstrapValidator('revalidateField', $scope.search_date_id);
//            });
            
            
            
            // 选择插件
            $('.select2').select2();
            
            
            
            $scope.search_ext_loaded = true;
        }
    }
    
    $scope.ext_add_load = function() {
        if (typeof($scope.add_ext_loaded) == 'undefined') {
            
            // 表单验证插件
            // ====配置变量
            $scope.modal_add_id = 'modal_add';
            $scope.modal_add_fields = 
            {
                add_ip: {
                    message: $rootScope.langs.add_ip_invalid,
                    validators: {
                        regexp: {
                            regexp: $rootScope.regexp.ip_regexp,
                            message: $rootScope.langs.add_ip_invalid
                        },
                        notEmpty: {
                            message: $rootScope.langs.add_ip_not_empty
                        }
                    }
                },
                add_notes: {
                    message: $rootScope.langs.add_notes_invalid,
                    validators: {
                        stringLength: {
                            message: $rootScope.langs.add_notes_stringLength,
                            max: 100
                        },
                        notEmpty: {
                            message: $rootScope.langs.add_notes_not_empty
                        }
                    }
                }
            };
            // ====初始化
            $rootScope.form_bootstrapValidator_init($scope.modal_add_id, $scope.modal_add_fields, $rootScope.form_bootstrapValidator_excluded);
            
            // 选择插件
            $('.select2').select2();
            
            
            $scope.add_ext_loaded = true;
        }
    }
    // ===================./扩展插件加载=============
});