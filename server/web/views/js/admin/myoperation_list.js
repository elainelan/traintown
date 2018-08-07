app.controller('admin.myoperation_list.ctrl', function ($scope, focus, $filter, $rootScope, $http) {

    var api_name = 'admin.myoperation_list';

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
    $scope.headfunc = {};   // 下载等权限
    $scope.headfunc.download = $rootScope.getPrivileges(api_name+'.download');
    $scope.headfunc.export = 1;
    $scope.orderby = {orderby:{tm:'desc'}};
    
    // 菜单结构处理
    $scope.tree_menu = new Object();
    for (var ruri in $rootScope.treeMenu) {
        var tmp_menu = $rootScope.treeMenu[ruri];
        var tmp = new Object();
        tmp.uri = tmp_menu.uri;
        tmp.repeat = '';
        $scope.tree_menu[tmp.uri] = tmp;
        
        for (var ruri_1 in tmp_menu.sub) {
            var tmp_menu_1 = tmp_menu.sub[ruri_1];
            var tmp = new Object();
            tmp.uri = tmp_menu_1.uri;
            tmp.repeat = '—';
            $scope.tree_menu[tmp.uri] = tmp;
            
            for (var ruri_2 in tmp_menu_1.sub) {
                var tmp_menu_2 = tmp_menu_1.sub[ruri_2];
                var tmp = new Object();
                tmp.uri = tmp_menu_2.uri;
                tmp.repeat = '——';
                $scope.tree_menu[tmp.uri] = tmp;
            }
        }
    }
    
    //console_log('整理后的菜单$scope.tree_menu',$scope.tree_menu);
    var today = new Date();
    var before = new Date();
    before.setDate(today.getDate()-29);
    $scope.search.tm = $filter('date')(before, 'yyyy-MM-dd') + ' - ' + $filter('date')(today, 'yyyy-MM-dd');
    //$scope.time = '2016/11/09 00:00 - 2016/11/12 23:00';
    
    

    // ui-grid
    // ====调整样式的grid高度
    $scope.ui_grid_style = {};
    
    // 自定义的数据处理方法
    $rootScope.ui_grid.get_uri_transform = function(data_key, lang_key, visible) {
        return {
            field: data_key, displayName: $rootScope.langs[lang_key], minWidth: 240,
            cellTemplate: '<div class="ui-grid-cell-contents">\
                                <ed data-ng-if="grid.appScope.detail" class="tooltip-show"  data-toggle="tooltip" data-placement="auto left" data-html="true"  title="{{grid.appScope.param_transform(row.entity.param)}}">{{grid.appScope.uri_transform(row.entity.'+data_key+') }}</ed>\
                                <ed data-ng-if="!grid.appScope.detail" class="tooltip-show">{{grid.appScope.uri_transform(row.entity.'+data_key+') }}</ed>\
                            </div>',
            visible: visible
        };
    };
    $rootScope.ui_grid.get_export_uri_transform = function( grid, row, col, input ) {
        return grid.appScope.uri_transform(input);
    };
    $rootScope.ui_grid.get_uri_handle = function(data_key, lang_key, visible) {
        return {
            field: data_key, displayName: $rootScope.langs[lang_key], minWidth: 200,
            cellTemplate: '<div class="ui-grid-cell-contents">{{grid.appScope.uri_handle(row.entity.'+data_key+') }}</div>',
            visible: visible
        };
    };
    $rootScope.ui_grid.get_export_uri_handle = function( grid, row, col, input ) {
        return grid.appScope.uri_handle(input);
    };
    $rootScope.ui_grid.get_res = function(data_key, lang_key, visible) {
        return {
            field: data_key, displayName: $rootScope.langs[lang_key], width: 90,
            cellTemplate: '<div class="ui-grid-cell-contents">\
                                <a style="margin: 0 5px 0 5px;" data-ng-if="row.entity.'+data_key+'==1" class="text-success pull-left ng-scope glyphicon glyphicon-ok" }}"></a>\
                                <a style="margin: 0 5px 0 5px;" data-ng-if="row.entity.'+data_key+'==0" class="text-danger pull-left ng-scope glyphicon glyphicon-remove" data-toggle="tooltip" data-placement="auto left" data-html="true" title="{{grid.appScope.res_transform(row.entity.res)}}" }}"></a>\
                            </div>',
            visible: visible
        };
    };
    
    // ====gridOptions初始化
    $scope.gridOptions = $rootScope.ui_grid.init();
    // ====gridOptions.columnDefs初始化(语言包加载完成后执行)
    $scope.unWatch = $scope.$watch('langs.operation_res', function(){
        if ($rootScope.langs.operation_res) {
            $scope.unWatch();
            $scope.gridOptions.columnDefs = [
                $rootScope.ui_grid.get_seq(),
                $rootScope.ui_grid.get_id('id', 'rec_id', false),
                //$rootScope.ui_grid.get_platform('platid', 'operation_plat', false),
                //$rootScope.ui_grid.get_user('userid', 'operation_user'),
                $rootScope.ui_grid.get_ts('tm', 'operation_tm'),
                $rootScope.ui_grid.get_ip('ip', 'operation_ip', false),
                $rootScope.ui_grid.get_uri_handle('uri', 'operation_uri', false),
                $rootScope.ui_grid.get_uri_transform('uri', 'operation_function'),
                $rootScope.ui_grid.get_res('success', 'operation_res')
            ];
            $scope.gridOptions.exportColumnDefs = {
                    seq:$rootScope.ui_grid.get_export_seq,
                    tm:$rootScope.ui_grid.get_export_ts,
                    uri:$rootScope.ui_grid.get_export_uri_handle,
                    uri2:$rootScope.ui_grid.get_export_uri_transform,
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
            //console_log('orderby.orderby.length', $scope.orderby.orderby);
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
                //$scope.go_page=$scope.data.page_current;
                // 搜索条件样式重置
                //$scope.modal_search_reset();
                
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

    $scope.modal_search_ok = function() {
        // 确认搜索
        $scope.get_data(1);
        $('#modal_search').modal("hide");
    }
    // ==================./查找===============

    
    
    
    
    
    // ===================模态框回调===============
    $('#modal_search').on("shown.bs.modal", function(){
        focus('ip');
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
                ip: {
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
            // ===配置变量
            $scope.search_date_id = 'search_time';
            // ====初始化
            $rootScope.daterangepicker_init($scope.search_date_id);
            // ====日期变化处理
            $('#'+$scope.search_date_id)
            .on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $scope.search.tm = $(this).val();
                $rootScope.modal_search_title_not_submit();
                $('#'+$scope.modal_search_id).bootstrapValidator('revalidateField', $scope.search_date_id);
            })
            .on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $scope.search.tm = $(this).val();
                $rootScope.modal_search_title_not_submit();
                $('#'+$scope.modal_search_id).bootstrapValidator('revalidateField', $scope.search_date_id);
            });
            
            
            
            // 选择插件
            $('.select2').select2();
            
            
            
            $scope.search_ext_loaded = true;
        }
    }
    
    // ===================./操作名解析=============
    $scope.uri_handle = function(uri) {
        var uri_arr = uri.split('.');
        var result = '';
        switch (uri_arr.length) {
            case 4:
                result = uri_arr[0] + '.' + uri_arr[1] + '&a=' + uri_arr[2] + '&b=' + uri_arr[3];
                break;
            case 3:
                result = uri_arr[0] + '.' + uri_arr[1] + '&a=' + uri_arr[2];
                break;
            case 2:
                result = uri_arr[0] + '.' + uri_arr[1];
                break;
            case 1:
                result = uri_arr[0];
                break;
            default:
                break;
        }
        return result;
    }
    
    // ===================./操作名解析=============
    $scope.uri_transform = function(uri) {
        var uri_arr = uri.split('.');
        var result,tmp = '';
        switch (uri_arr.length) {
            case 4:
            case 3:
                tmp = $scope.langs['tree_' + uri_arr[0] + '.' + uri_arr[1] + '.' + uri_arr[2]];
                if (result && tmp) {
                    result = tmp + ' > ' + result;
                }
                else if (tmp) {
                    result = tmp;
                }
            case 2:
                tmp = $scope.langs['tree_' + uri_arr[0] + '.' + uri_arr[1]];
                if (result && tmp) {
                    result = tmp + ' > ' + result;
                }
                else if (tmp) {
                    result = tmp;
                }
            case 1:
                tmp = $scope.langs['tree_' + uri_arr[0]];
                if (result && tmp) {
                    result = tmp + ' > ' + result;
                }
                else if (tmp) {
                    result = tmp;
                }
            default:
        }
        return result;
    }
    
    // ===================./操作参数处理=============
    $scope.param_transform = function(param) {
        if (!param) {
            return;
        }
        var result = '';
        try {
            var param_obj = eval('(' + param + ')');
            for (var k in param_obj) {
                var k_val = param_obj[k];
                // 如果值是对象转化为json字符串
                if (typeof(k_val) == 'object') {
                    k_val = JSON.stringify(k_val);
                }
                
                result += '<strong class="text-primary">' + 
                k + 
                '</strong> : <span class="">' + 
                k_val + 
                '</span><br/>';
            }
        }
        catch(e) {
            result = param;
        }
        return '<div align="left" style="word-wrap:break-word">' + result + '</div>';
    }
    
    // ===================./操作参数处理=============
    $scope.res_transform = function(param) {
        if (!param) {
            return;
        }
        var result = '';
        try {
            var param_obj = eval('(' + param + ')');
            if (typeof(param_obj.errCode) != "undefined" && $scope.langs['error_' + param_obj.errCode]) {
                param_obj.errInfo = $scope.langs['error_' + param_obj.errCode];
            }
            for (var k in param_obj) {
                var k_val = param_obj[k];
                // 如果值是对象转化为json字符串
                if (typeof(k_val) == 'object') {
                    k_val = JSON.stringify(k_val);
                }
                
                result += '<strong class="text-primary">' + 
                k + 
                '</strong> : <span class="">' + 
                k_val + 
                '</span><br/>';
            }
        }
        catch(e) {
            result = param;
        }
        return '<div align="left" style="word-wrap:break-word">' + result + '</div>';
    }
});

