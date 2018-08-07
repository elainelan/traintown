app.controller('platforms.manager.ctrl', function ($scope, focus, $filter, $rootScope, $http) {

    var api_name = 'platforms.manager';
    
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
    
    $scope.add = {};
    $scope.modobj = {};
    $scope.search = {}; // 搜索
    $scope.headfunc = {};   // 管理等权限
    $scope.headfunc.manager = $rootScope.getPrivileges(api_name+'.manager');
    $scope.headfunc.export = 1;
    // ====排序
    $scope.orderby = {orderby:{id:'asc'}};

    var today = new Date();
    /*
    var today = new Date();
    var before = new Date();
    before.setDate(today.getDate()-30);
    $scope.search.time = $filter('date')(before, 'yyyy-MM-dd') + ' - ' + $filter('date')(today, 'yyyy-MM-dd');
    */
    //$scope.time = '2016/11/09 00:00 - 2016/11/12 23:00';
    
    
    // ui-grid
    // ====调整样式的grid高度
    $scope.ui_grid_style = {};
    // ====gridOptions初始化
    $scope.gridOptions = $rootScope.ui_grid.init();
    // ====gridOptions.columnDefs初始化(语言包加载完成后执行)
    $scope.unWatch = $scope.$watch('langs.platform_phone', function(){
        if ($rootScope.langs.platform_phone) {
            $scope.unWatch();
            $scope.gridOptions.columnDefs = [
                $rootScope.ui_grid.get_seq(), // 序号
                { field: 'id', displayName: $rootScope.langs['platform_id'] }, // 平台ID
                { field: 'name', displayName: $rootScope.langs['platname'] }, // 平台名称
                { field: 'game_sig', displayName: $rootScope.langs['game_sig'], minWidth: 250 }, // 登录key
                { field: 'pay_sig', displayName: $rootScope.langs['pay_sig'], minWidth: 250 }, // 支付key
                $rootScope.ui_grid.get_ts('close_tm', 'close_tm', false), // 关服时间
                
                { field: 'ptoolbar', displayName: $rootScope.langs['ptoolbar'], visible: false },
                { field: 'safe', displayName: $rootScope.langs['safe'], visible: false },
                { field: 'pfcm', displayName: $rootScope.langs['pfcm'], visible: false },
                { field: 'onbeforeunload', displayName: $rootScope.langs['onbeforeunload'], visible: false },
                { field: 'forcein', displayName: $rootScope.langs['forcein'], visible: false },
                { field: 'automis', displayName: $rootScope.langs['automis'], visible: false },
                
                { field: 'mini', displayName: $rootScope.langs['miniurl'], visible: false },
                { field: 'mini_login', displayName: $rootScope.langs['mini_login'], visible: false },
                { field: 'mini_ver', displayName: $rootScope.langs['mini_ver'], visible: false },
                { field: 'supervip_config', displayName: $rootScope.langs['supervip_config'], visible: false },
//                { field: 'pay_adr', displayName: $rootScope.langs['pay_adr'], visible: false },
                
                { field: 'web', displayName: $rootScope.langs['weburl'], visible: false },
                { field: 'bbs', displayName: $rootScope.langs['bbsurl'], visible: false },
                { field: 'gm_url', displayName: $rootScope.langs['gmurl'], visible: false },
                { field: 'cm_url', displayName: $rootScope.langs['cmurl'], visible: false },
                { field: 'newcard_url', displayName: $rootScope.langs['newcardurl'], visible: false },
                { field: 'phone_url', displayName: $rootScope.langs['phone_url'], visible: false },
                
                { field: 'sj_test', displayName: $rootScope.langs['sj_test'], visible: false },
                { field: 'sj_pid', displayName: $rootScope.langs['sj_pid'], visible: false },
                
            ];
            // 管理栏位
            if ($scope.headfunc.manager) {
                var operation_mod_button = '<button data-ng-click="grid.appScope.modal_add(\'mod\', row.entity)" title="{{grid.appScope.langs.mod}}" class="pull-left btn btn-warning btn-xs btn-func"><i class="fa fa-pencil-square-o"></i></button>';
                var operation_del_button = '<button data-ng-click="grid.appScope.modal_del(row.entity)" title="{{ grid.appScope.langs.del }}" class="pull-left btn btn-danger btn-xs btn-func"><i class="fa fa-times"></i></button>';
                $scope.gridOptions.columnDefs.push( { enableSorting: false, enableFiltering: false, field: 'operation', displayName: $rootScope.langs['operation'], minWidth: 80, cellTemplate: '<div class="ui-grid-cell-contents">'+operation_mod_button+operation_del_button+'</div>' } );
            }
            
            $scope.gridOptions.exportColumnDefs = {
                    seq:$rootScope.ui_grid.get_export_seq
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
        $scope.get_data(page);
    }

    $scope.modal_change_key = function (valueinfo) {
        $http.post(getApiUrl('common.get_md5')).then(function(response){
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            }
            else {
                switch (valueinfo) {
                    case 'add.game_sig':
                        $scope.add.game_sig = response.data.data[0];
                        break;
                    case 'add.pay_sig':
                        $scope.add.pay_sig = response.data.data[0];
                        break;
                    case 'modobj.game_sig':
                        $scope.modobj.game_sig = response.data.data[0];
                        break;
                    case 'modobj.pay_sig':
                        $scope.modobj.pay_sig = response.data.data[0];
                        break;
                }
            }
        });
    }
    
    $scope.modal_clear_key = function (valueinfo) {
        switch (valueinfo) {
            case 'add.game_sig':
                $scope.add.game_sig = "";
                break;
            case 'add.pay_sig':
                $scope.add.pay_sig = "";
                break;
            case 'modobj.game_sig':
                $scope.modobj.game_sig = "";
                break;
            case 'modobj.pay_sig':
                $scope.modobj.pay_sig = "";
                break;
        }
    }

    $scope.get_data(); // 页面初始化，先获取第一页数据
    // ==================./数据获取============
    
    
    
    // ================== 数据下载=============
    /*
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
    */
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
    $scope.modal_search_ok = function() {
        // 确认搜索
        $scope.get_data(1);
        $('#modal_search').modal("hide");
    }
    // ==================./查找===============
    
    
    
    
    // ===================删除=================
    $scope.modal_del = function(obj) {
        // 弹出删除确认框
        $rootScope.langs.modal_del_body = $rootScope.langs.confirm_del_plat_info;
        $scope.id = obj.id;
        $('#modal_del').modal("show");
    }
    $scope.modal_del_ok = function() {
        var post_data = { a:'manager', b:'del', id:$scope.id};
        // 确认删除
        $('#modal_del').modal("hide");
        $http.post(getApiUrl(api_name), post_data).then(function(response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            }
            else {
                if($scope.search.platid != $scope.id) {
                    $scope.jump($scope.data.page_current);
                }
                else {
                    $scope.search.platid = '';
                    $scope.get_data();
                }
                
                $rootScope.getPlatforms(1);
            }
        });
    }
    // =================./删除================
    
    
    
    
    // ===================添加===================
    //初始化添加信息
    $scope.add_tmp = $scope.add = {action:'add'};
    $scope.modal_add = function (action ,obj) {
        //备份新增信息
        if ($scope.add.action == 'add') {
            $scope.add_tmp = $scope.add;
        }
        
        switch (action) {
            case 'add':
                $scope.add = $scope.add_tmp;
                break;
            case 'mod':
                //备份新增信息
                if ($scope.add.action == 'add') {
                    $scope.add_tmp = $scope.add;
                }
                $scope.add = obj;
                $scope.add.close_tm = $scope.add.close_tm !='0' ? $filter('date')($scope.add.close_tm*1000, 'yyyy-MM-dd hh:mm:ss') : '';
                $scope.add.action = 'mod';
                break;
            default :
                break;
        }

        $('#modal_add').modal('show');
    };
    
    $scope.modal_add_check = function() {
        // 添加数据验证
        return true;
    }
    $scope.modal_add_reset = function() {
        // 新增重置功能
        $scope.add = {};    //清空新增页面上的数据
        $scope.add.ptoolbar = '0';
        $scope.add.safe = '0';
        $scope.add.pfcm = '0';
        $scope.add.onbeforeunload = '0';
        $scope.add.forcein = '0';
        $scope.add.automis = '0';
        focus('platid');
        
        // 表单验证重新处理
        $('#modal_add').bootstrapValidator('resetForm', 'true');
        $('#modal_add').data('bootstrapValidator').validate();
    }
    $scope.modal_add_ok = function () {
        // 确认添加
        if ($scope.modal_add_check()) {
            $('#modal_add').modal('hide');
            
            var post_data = { a:'manager', b:$scope.add.action};
            // 合并添加数据
            angular.extend(post_data, $scope.add);
            
            $http.post(getApiUrl(api_name), post_data).then(function(response) {
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode, $scope.modal_add);
                }
                else {
                    $scope.jump($scope.data.page_current);
                    
                    $rootScope.getPlatforms(1);
                    
                    $scope.modal_add_reset();
                    
                    // 表单验证重新处理
                    $('#modal_add').bootstrapValidator('resetForm', 'true');
                    $('#modal_add').data('bootstrapValidator').validate();
                }
            });
        }
    };
    // ===================./添加==================

    
    
    
    
    // ===================模态框回调===============
    $('#modal_search').on("shown.bs.modal", function(){
        focus('ip');
    });

    $('#modal_search').on("show.bs.modal", function(){
        $scope.search_ext_load();
    });
    
    $('#modal_add').on("shown.bs.modal", function(){
        //focus('platid');
        // 表单内容验证
        if ($scope.add.action == 'mod') {
            $('#platid').attr('readonly', true);
            $("#modal_add").bootstrapValidator('enableFieldValidators', 'platid', false);
        }
        else {
            $('#platid').attr('readonly', false);
            $("#modal_add").bootstrapValidator('enableFieldValidators', 'platid', true);
        }
        $("#modal_add").bootstrapValidator('revalidateField', 'platid');
        $("#modal_add").bootstrapValidator('revalidateField', 'platname');
        $('#modal_add').data('bootstrapValidator').validate();
        if ($("#modal_add").data('bootstrapValidator').isValid()) {
            $('#modal_add').bootstrapValidator('disableSubmitButtons', false);
        }
    });
    $('#modal_add').on("show.bs.modal", function(){
        $scope.add_ext_load();
    });

    // ===================./模态框回调=============
    
    // 获取平台信息
    $rootScope.getPlatforms(0);
    
    // 平台信息二级栏位展开/折叠点击事件处理
    $('.panel-collapse').on('show.bs.collapse', function () {
        $(this).prev().find('i').removeClass().addClass('fa fa-chevron-circle-down');
    }).on('hide.bs.collapse', function () {
        $(this).prev().find('i').removeClass().addClass('fa fa-chevron-circle-right');
    })

    $scope.add_ext_load = function() {
        if (typeof($scope.add_ext_loaded) == 'undefined') {
            
            // 表单验证插件
            // ====配置变量
            $scope.modal_add_id = 'modal_add';
            $scope.modal_add_fields = 
            {
                platid: {
                    message: $rootScope.langs.platform_id_invalid,
                    validators: {
                        between: {
                            min: 1,
                            max: 65534,
                            message: $rootScope.langs.platform_id_between_invalid
                        },
                        regexp: {
                            regexp: $rootScope.regexp.platid,
                            message: $rootScope.langs.platform_id_regexp_invalid
                        },
                        notEmpty: {
                            message: $rootScope.langs.platform_id_not_empty
                        }
                    }
                },
                platname: {
                    message: $rootScope.langs.platform_name_invalid,
                    validators: {
                        stringLength: {
                            max: 11,
                            message: $rootScope.langs.platform_name_stringLength_invalid
                        },
                        notEmpty: {
                            message: $rootScope.langs.platform_name_not_empty
                        }
                    }
                }
            };
            // ====初始化
            $scope.form_bootstrapValidator_excluded = ''; // 不管是否隐藏，都需要验证，保证平台信息折叠后也能正常验证
            $rootScope.form_bootstrapValidator_init($scope.modal_add_id, $scope.modal_add_fields, $scope.form_bootstrapValidator_excluded);
            
            // 关服时间
            $('#close_tm').datetimepicker({
                format: "yyyy-mm-dd hh:ii:ss",
                autoclose: true,
                startDate: today
            });
            
            $scope.add_ext_loaded = true;
        }
    }
    
    $scope.search_ext_load = function() {
        if (typeof($scope.search_ext_loaded) == 'undefined') {
            // 日期时间插件（新增平台信息）
            // regtm
            $('#close_tm').datetimepicker({
                format: "yyyy-mm-dd hh:ii:ss",
                autoclose: true,
                startDate: today
            });

            // 日期时间插件（修改平台信息）
            // regtm
            $('#close_tm_mod').datetimepicker({
                format: "yyyy-mm-dd hh:ii:ss",
                autoclose: true,
                startDate: today
            });
            // 日期时间插件
            /*
            $('#reservation').daterangepicker({
                locale : {
                    format: 'YYYY-MM-DD' //控件中from和to 显示的日期格式  
                }
            });
            */

            // 选择插件
            $('.select2').select2();
            
            $scope.search_ext_loaded = true;
        }
    }
});