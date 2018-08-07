app.controller('admin.login_list.ctrl', function ($scope, focus, $filter, $rootScope, $http) {

    var api_name = 'admin.login_list';
    
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
    
    $scope.search = {};
    $scope.headfunc = {};
    $scope.headfunc.download = $rootScope.getPrivileges(api_name+'.download');
    $scope.headfunc.export = 1;
    // ====排序
    $scope.orderby = {orderby:{logintime:'desc'}};

    var today = new Date();
    var before = new Date();
    before.setDate(today.getDate()-29);
    $scope.search.time = $filter('date')(before, 'yyyy-MM-dd') + ' - ' + $filter('date')(today, 'yyyy-MM-dd');
    //$scope.time = '2016/11/09 00:00 - 2016/11/12 23:00';
    
    
    
 // ui-grid
    // ====调整样式的grid高度
    $scope.ui_grid_style = {};
    // ====gridOptions初始化
    $scope.gridOptions = $rootScope.ui_grid.init();
    // ====gridOptions.columnDefs初始化(语言包加载完成后执行)
    $scope.unWatch = $scope.$watch('langs.login_time', function(){
        if ($rootScope.langs.login_time) {
            $scope.unWatch();
            $scope.gridOptions.columnDefs = [
                $rootScope.ui_grid.get_seq(),
                $rootScope.ui_grid.get_id('id', 'rec_id', false),
                $rootScope.ui_grid.get_platform('platid', 'login_plat'),
                $rootScope.ui_grid.get_user('admin_userid', 'username'),
                $rootScope.ui_grid.get_ts('logintime', 'login_time'),
                $rootScope.ui_grid.get_ip('loginip', 'login_ip')
            ];
            $scope.gridOptions.exportColumnDefs = {
                    seq:$rootScope.ui_grid.get_export_seq,
                    logintime:$rootScope.ui_grid.get_export_ts,
                    loginip:$rootScope.ui_grid.get_export_ip,
                    platid:$rootScope.ui_grid.get_export_platform,
                    admin_userid:$rootScope.ui_grid.get_export_user,
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
            console_log('orderby.orderby.length', $scope.orderby.orderby);
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
        // GO跳转/分页长度调整
        $scope.get_data(page);
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
    
    
    // =================当页数据导出================
    $scope.export = function() {
        $scope.gridApi.exporter.csvExport( 'visible', 'visible');
    }
    // =================./当页数据导出================
    
    
    // ==================查找=================
    $scope.modal_search = function () {
        $('#modal_search').modal("show");
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
        // 检查搜索元素是否符合要求
        if ($("#modal_search").data('bootstrapValidator').isValid()) {
            // 确认搜索
            $scope.get_data(1);
            $('#modal_search').modal("hide");
        }
    }
    // ==================./查找===============
    
    
    
    
    
    
    
    // ===================模态框回调===============
    $('#modal_search').on("shown.bs.modal", function(){
        focus('search_loginip');
        // 表单内容验证
        $("#modal_search").data('bootstrapValidator').validate();
        if ($("#modal_search").data('bootstrapValidator').isValid()) {
            $('#modal_search').bootstrapValidator('disableSubmitButtons', false);
        }
    });
    $('#modal_search').on("show.bs.modal", function(){
        $scope.search_ext_load();
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
                search_time: {
                    message: $rootScope.langs.search_time_invalid,
                    validators: {
                        regexp: {
                            regexp: $rootScope.regexp.daterange,
                            message: $rootScope.langs.search_time_invalid
                        }
                    }
                },
                search_loginip: {
                    message: $rootScope.langs.search_loginip_invalid,
                    validators: {
                        regexp: {
                            regexp: $rootScope.regexp.ip,
                            message: $rootScope.langs.search_loginip_invalid
                        }
                    }
                }
            };
            // ====初始化
            $rootScope.form_bootstrapValidator_init($scope.modal_search_id, $scope.modal_search_fields, $rootScope.form_bootstrapValidator_excluded);
            
            
            
            // 日期插件
            // ====配置变量
            $scope.search_date_id = 'search_time';
            // ====初始化
            $rootScope.daterangepicker_init($scope.search_date_id);
            // ====日期变化处理
            $('#'+$scope.search_date_id)
            .on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $scope.search.time = $(this).val();
                $rootScope.modal_search_title_not_submit();
                $('#'+$scope.modal_search_id).bootstrapValidator('revalidateField', $scope.search_date_id);
            })
            .on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $scope.search.time = $(this).val();
                $rootScope.modal_search_title_not_submit();
                $('#'+$scope.modal_search_id).bootstrapValidator('revalidateField', $scope.search_date_id);
            });
            
            
            
            // 选择插件
            $('.select2').select2();
            
            
            
            $scope.search_ext_loaded = true;
        }
    }
});
