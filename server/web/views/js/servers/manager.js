app.controller('servers.manager.ctrl', function ($scope, focus, $filter, $rootScope, $http) {

    var api_name = 'servers.manager';
    
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
    $scope.orderby = {};
    
    $scope.modobj = {};     // 新增、修改编辑区服信息对象
    $scope.search = {};     // 搜索
    $scope.theads = {};     // 表头
    $scope.search.type = 0; // 默认搜索“正常服”
    
    
    $scope.headfunc = {};   // 管理等权限
    $scope.headfunc.manager = $rootScope.getPrivileges(api_name+'.manager');    //管理权限
    $scope.headfunc.config = $rootScope.getPrivileges(api_name+'.config');      //配置权限
    $scope.headfunc.download = 0;//$rootScope.getPrivileges(api_name+'.download');  //下载权限
    $scope.headfunc.export = 1;
    
    var today = new Date(); // 为日期时间插件选取起始时间，默认为当前时刻
    
    $scope.color_slider_bar = {
        value: 5,
        options: {
            showTicksValues: true,
            showTicksKeys: false,
            stepsArray: [
              { value: 1, legend: '流畅' },
              { value: 2, legend: '良好' },
              { value: 3, legend: '拥挤' },
              { value: 4, legend: '爆满' },
              { value: 5, legend: '维护' }
            ],
            getSelectionBarColor: function(value) {
            if (value <= 3) return 'red'
            if (value <= 6) return 'orange'
            if (value <= 9) return 'yellow'
            return '#2AE02A'
          },
          getPointerColor: function(value) {
              if (value == 1 ) return 'green'
              if (value==2) return 'blue'
              if (value==3) return 'yellow'
              if (value==4) return 'red'
              return '#2AE02A'
            }
          }
      }
    
    // ui-grid
    //自定义数据处理函数
    //根据数据自动显示对应语言包
    $rootScope.ui_grid.get_state_info = function(data_key, lang_key, visible) {
        return {
            field: data_key, displayName: $rootScope.langs[lang_key],
            cellTemplate: '<label class="ui-grid-cell-contents" data-ng-class="{\'text-success\':row.entity.'+data_key+'==1,\'text-danger\':row.entity.'+data_key+'==0}">{{ grid.appScope.langs["'+lang_key+'_x"][row.entity.'+data_key+'] }}</label>',
            visible: visible,
            minWidth:80 
        };
    };
    /**
     * 使用说明
     * 1.data_key与lang_key必须相同
     * 2. 语言包需要这么设置
     *   "recomm" : "推荐标记",
     *   "recomm_x":  {"0":"未设置", "1":"已设置"},
     */
    $rootScope.ui_grid.get_state_info_export = function(grid, row, col, input) {
        return grid.appScope.langs[col.colDef.name+'_x'][input];;
    };
    
    $rootScope.ui_grid.get_srv_platids = function(data_key, lang_key, visible) {
    $rootScope.getPlatforms();
        return {
            field: data_key, displayName: $rootScope.langs[lang_key],
            cellTemplate: '<div class="ui-grid-cell-contents" >{{ grid.appScope.get_platnames_by_platids(row.entity.'+data_key+') }}</div>',
            visible: visible,
            minWidth:80 
        };
    };
    $rootScope.ui_grid.get_srv_platids_export = function(grid, row, col, input) {
        return grid.appScope.get_platnames_by_platids(input);;
    };
    // ====调整样式的grid高度
    $scope.ui_grid_style = {};
    // ====gridOptions初始化
    $scope.gridOptions = $rootScope.ui_grid.init();
    // ====gridOptions.columnDefs初始化(语言包加载完成后执行)
    $scope.unWatch = $scope.$watch('langs.copy_add_server', function(){
        if ($rootScope.langs.copy_add_server) {
            $scope.unWatch();
            $scope.gridOptions.columnDefs = [
                $rootScope.ui_grid.get_seq(), // 序号
                { field: 'sid', displayName: $rootScope.langs['sid'] , minWidth:70 }, // sid
                { field: 'srv_name', displayName: $rootScope.langs['srv_name'], minWidth:140 }, // 区服名称
                // 区服类型
                {
                    field: 'type', displayName: $rootScope.langs['type'],
                    cellTemplate: '<label class="ui-grid-cell-contents" data-ng-class="{\'text-success\':row.entity.type==0,\'text-primary\':row.entity.type==1,\'text-danger\':row.entity.type==2,\'text-black\':row.entity.type==3}">{{ grid.appScope.langs["type_x"][row.entity.type] }}</label>',
                    minWidth:100 
                }, 
                $rootScope.ui_grid.get_ts('optm', 'optm'), // 关服时间
                
                { field: 'opver', displayName: $rootScope.langs['opver'] ,visible: false, minWidth:80 },
                { field: 'srv_ip', displayName: $rootScope.langs['srv_ip'], minWidth:120 },
                { field: 'srv_ip2', displayName: $rootScope.langs['srv_ip2'], visible: false, minWidth:120 },
                $rootScope.ui_grid.get_srv_platids('platids', 'platids'),
                //{ field: 'platids', displayName: $rootScope.langs['platids'], minWidth:120 },
                
                $rootScope.ui_grid.get_state_info('msg', 'msg', false),
                $rootScope.ui_grid.get_state_info('close', 'close', false),

                { field: 'gamedb_ip', displayName: $rootScope.langs['gamedb_ip'], visible: false, minWidth:110 },//cquser数据库配置
                { field: 'gamedb_name', displayName: $rootScope.langs['gamedb_name'], visible: false, minWidth:110 },
                { field: 'gamedb_user', displayName: $rootScope.langs['gamedb_user'], visible: false, minWidth:165 },
                { field: 'gamedb_pwd', displayName: $rootScope.langs['gamedb_pwd'], visible: false, minWidth:150 },
                

                { field: 'logdb_ip', displayName: $rootScope.langs['logdb_ip'], visible: false, minWidth:110 },//cqlog数据库配置
                { field: 'logdb_name', displayName: $rootScope.langs['logdb_name'], visible: false, minWidth:110 },
                { field: 'logdb_user', displayName: $rootScope.langs['logdb_user'], visible: false, minWidth:165 },
                { field: 'logdb_pwd', displayName: $rootScope.langs['logdb_pwd'], visible: false, minWidth:150 },

                { field: 'paydb_ip', displayName: $rootScope.langs['paydb_ip'], visible: false, minWidth:110 },//cqpay数据库配置
                { field: 'paydb_name', displayName: $rootScope.langs['paydb_name'], visible: false, minWidth:110 },
                { field: 'paydb_user', displayName: $rootScope.langs['paydb_user'], visible: false, minWidth:165 },
                { field: 'paydb_pwd', displayName: $rootScope.langs['paydb_pwd'], visible: false, minWidth:150 },
                
                { field: 'kfdb_ip', displayName: $rootScope.langs['kfdb_ip'], visible: false, minWidth:110 },//cqkf数据库配置
                { field: 'kfdb_name', displayName: $rootScope.langs['kfdb_name'], visible: false, minWidth:110 },
                { field: 'kfdb_user', displayName: $rootScope.langs['kfdb_user'], visible: false, minWidth:165 },
                { field: 'kfdb_pwd', displayName: $rootScope.langs['logdb_pwd'], visible: false, minWidth:150 },
                

                { field: 'cq_root', displayName: $rootScope.langs['cq_root'], visible: false, minWidth:240 },
                { field: 'static_url', displayName: $rootScope.langs['static_url'], visible: false, minWidth:240  },
                { field: 'web_static', displayName: $rootScope.langs['web_static'], visible: false, minWidth:240  },
                { field: 'ropass', displayName: $rootScope.langs['ropass'], visible: false , minWidth:120 },
                $rootScope.ui_grid.get_state_info('def', 'def', false),
                $rootScope.ui_grid.get_state_info('new', 'new', false),
                $rootScope.ui_grid.get_state_info('recomm', 'recomm', false),
                //运营状态
                {
                    field: 'srv_status', displayName: $rootScope.langs['srv_status'],
                    cellTemplate: '<label class="ui-grid-cell-contents" data-ng-class="{\'text-success\':row.entity.srv_status==0,\'text-primary\':row.entity.srv_status==1,\'text-yellow\':row.entity.srv_status==2,\'text-red\':row.entity.srv_status==3,\'text-black\':row.entity.srv_status==4}">{{ grid.appScope.langs["srv_status_x"][row.entity.srv_status] }}</label>',
                    visible: false,
                    minWidth:100 
                }, 

                
            ];
            // 管理栏位
            if ($scope.headfunc.manager) {var operation_cope_add_button = '<button data-ng-click="grid.appScope.modal_mod(row.entity, 3)" title="{{grid.appScope.langs.copy_add_server}}" class="pull-left btn btn-success btn-xs btn-func"><i class="fa fa-pencil-square-o"></i></button>';
            var operation_cope_add_button = '<button data-ng-click="grid.appScope.modal_mod(row.entity, 3)" title="{{grid.appScope.langs.copy_add_server}}" class="pull-left btn btn-success btn-xs btn-func"><i class="fa fa-clone"></i></button>';
            var operation_mod_button = '<button data-ng-click="grid.appScope.modal_mod(row.entity)" title="{{grid.appScope.langs.mod}}" class="pull-left btn btn-warning btn-xs btn-func"><i class="fa fa-pencil-square-o"></i></button>';
                var operation_del_button = '<button data-ng-click="grid.appScope.modal_del(row.entity)" title="{{ grid.appScope.langs.del }}" class="pull-left btn btn-danger btn-xs btn-func"><i class="fa fa-times"></i></button>';
                $scope.gridOptions.columnDefs.push( { enableSorting: false, enableFiltering: false, field: 'operation', displayName: $rootScope.langs['operation'], minWidth: 120, cellTemplate: '<div class="ui-grid-cell-contents">'+operation_cope_add_button+operation_mod_button+operation_del_button+'</div>' } );
            }
            
            $scope.gridOptions.exportColumnDefs = {
                    seq:$rootScope.ui_grid.get_export_seq,
                    optm:$rootScope.ui_grid.get_export_ts,
                    platids:$rootScope.ui_grid.get_srv_platids_export,
                    type:$rootScope.ui_grid.get_state_info_export,
                    def:$rootScope.ui_grid.get_state_info_export,
                    'new':$rootScope.ui_grid.get_state_info_export,
                    msg:$rootScope.ui_grid.get_state_info_export,
                    srv_status:$rootScope.ui_grid.get_state_info_export,
                    recomm:$rootScope.ui_grid.get_state_info_export,
                    close:$rootScope.ui_grid.get_state_info_export,
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

    // 翻页跳转功能
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
        angular.extend(post_data, $scope.search);
        
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
        // 查找框弹出
        $('#modal_search').modal("show");
    }
    /*
    $scope.modal_search_platform = function() {
        // 选择平台后，用户如果不是选择平台的用户，重置
        if ($scope.search.admin_userid && $scope.search.platid != $rootScope.admin_users[$scope.search.admin_userid].platid) {
            $scope.search.admin_userid = null;
        }
    }
    */
    $scope.modal_search_ok = function() {
        // 确认搜索
        $scope.get_data(1);
        $('#modal_search').modal("hide");
        // 根据选定的字段确定显示的相关数据字段
        $scope.confirm_fields = $scope.search.theads;
    }
    // ==================./查找===============

    // ===================删除=================
    $scope.modal_del = function(obj) {
        // 弹出删除确认框
        $rootScope.langs.modal_del_body = $rootScope.langs.confirm_del_server_info;
        $scope.delobj = obj;
        $('#modal_del').modal("show");
    }

    // 确认删除区服信息功能
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
                $rootScope.getPlatforms(1); 
            }
        });
    }
    // =================./删除================

    // ===================区服类型下拉框内容切换时触发的切换页面显示内容事件=================
    $scope.modal_servertypes_change = function() {
        // 确定下拉框中所选中的区服类型信息
        $scope.pageType_server = $scope.modobj.type;
    }
    // ==================./区服类型下拉框内容切换时触发的切换页面显示内容事件================

    // =================页面重置功能================
    $scope.modal_add_reset = function() {
        // 清空新增页面上的数据
        $scope.modobj = {}; 
        // 将type的默认值设置为0      
        $scope.modobj.type = '0';
        // 将msg的默认值设置为0
        $scope.modobj.msg = '0'; 
        // 将def的默认值设置为0
        $scope.modobj.def = '0'; 
        // 将new的默认值设置为0
        $scope.modobj.new = '0';
        // 将recomm的默认值设置为0
        $scope.modobj.recomm = '0';
        // 将srv_status的默认值设置为0
        $scope.modobj.srv_status = '0';   
        // 将所有选定过的所属平台信息清空
        $scope.modobj.platids = [];
        // 将页面状态重置为选择区服类型为“正常服”的情形 
        $scope.pageType_server = 0;
        //入口状态自动关闭
        $scope.modobj.close = '0';  
        // 将所属平台数据信息清空
        var arr = [];
        $('#platids_mod').selectpicker('val', arr);
        // 设置输入焦点
        focus('sid_mod');
    }
    // =================./页面重置功能================

    // ===================添加===================
    $scope.modal_add = function () {
        // 弹出相对应的弹窗
        $('#modal_mod').modal('show');
        // 重置页面元素数据
        $scope.modal_add_reset();
        // 确定弹出框的种类，1：新增，2：修改，3：复制新增
        $scope.pageType = 1;
    }
    
    $scope.modal_add_check = function() {
        // 添加数据验证
        return true;
    }

    $scope.modal_add_ok = function () {
        // 确认添加
        if ($scope.modal_add_check()) {
            $('#modal_mod').modal('hide');
            
            var post_data = { a:'manager', b:'add'};
            // 合并添加数据
            angular.extend(post_data, $scope.modobj);
            
            $http.post(getApiUrl(api_name), post_data).then(function(response) {
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode, $scope.modal_mod_show);
                }
                else {
                    $scope.jump($scope.data.page_current);
                    
                    $rootScope.getPlatforms(1);
                    
                    $scope.modal_add_reset();
                }
            });
        }
    }
    // ===================./添加==================

    // ===================修改===================
    $scope.modal_mod = function (obj, pageType) {
    
        pageType = pageType || 2;
        // 点击修改按钮
        var post_data = { a:'manager', b:'mod', get:'1', sid:obj.sid };
        
        // 根据平台ID获取该平台的详情数据
        $http.post(getApiUrl(api_name), post_data).then(function (response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            } else {
                $scope.r = 1;
                $scope.modobj = response.data.data[obj.sid];
                //$scope.selected = response.data[obj.sid]['platids'];
                $scope.pageType_server = response.data.data[obj.sid]['type'];
                // 确定弹出框的类型
                pageType == 2 ? $scope.pageType = 2 :  $scope.pageType = 3;
                $('#modal_mod').modal('show');

                // 将已经选择的区服所属平台信息填写到相应的位置上
                var arr = response.data.data[obj.sid]['platids'];
                $('#platids_mod').selectpicker('val', arr);
            }
        });  
    }

    $scope.modal_mod_check = function() {
        // 修改数据验证
        return true;
    }
    


    $scope.modal_mod_ok = function () {
        if ($scope.modal_mod_check()) {
            $('#modal_mod').modal('hide');
            
            var post_data = { a:'manager', b:'mod'};

            // 合并添加数据
            angular.extend(post_data, $scope.modobj);
            
            $http.post(getApiUrl(api_name), post_data).then(function(response) {
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode, $scope.modal_mod_show);
                }
                else {
                    $scope.jump($scope.data.page_current);
                    $rootScope.getPlatforms(1);
                }
            });
        }
    }
    // ===================./修改===================

    // ===================复制新增===================
    $scope.modal_copy_add = function (obj) {
        var post_data = { a:'manager', b:'mod', get:'1', sid:obj.sid };
        
        
        // 根据平台ID获取该平台的详情数据
        $http.post(getApiUrl(api_name), post_data).then(function (response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            } else {
                $scope.r = 1;
                $scope.modobj = response.data.data[obj.sid];
                $scope.pageType_server = response.data.data[obj.sid]['type'];
                $scope.pageType = 3;

                // 弹出复制新增框（基本与修改框相同）
                $('#modal_mod').modal('show');

                // 将已经选择的区服所属平台信息填写到相应的位置上
                var arr = response.data.data[obj.sid]['platids'];
                $('#platids_mod').selectpicker('val', arr);
            }
        });
    }

    // 复制新增表单提交
    $scope.modal_copy_add_ok = function () {
        if ($scope.modal_mod_check()) {
            $('#modal_mod').modal('hide');
            
            var post_data = { a:'manager', b:'add'};
            // 合并添加数据
            angular.extend(post_data, $scope.modobj);
            
            $http.post(getApiUrl(api_name), post_data).then(function(response) {
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode, $scope.modal_mod_show);
                }
                else {
                    $scope.jump($scope.data.page_current);
                    $rootScope.getPlatforms(1);
                }
            });
        }
    }
    // ===================./复制新增===================
    
    // ===================模态框回调===============
    // $('#modal_search').on("shown.bs.modal", function(){
    //     focus('ip');
    // });

    $('#modal_search').on("show.bs.modal", function(){
        $scope.search_ext_load();
    });
    
    $('#modal_mod').on("show.bs.modal", function(){
    $scope.search_ext_load();
    });

    $('#modal_mod').on("shown.bs.modal", function(){

        $scope.add_ext_load();
        // 表单内容验证
        if ($scope.pageType == 2) {//修改
            $('#sid_mod').attr('readonly', true);
            $("#modal_mod").bootstrapValidator('enableFieldValidators', 'sid_mod', false);
            $('#srv_status_mod').bootstrapSlider('setValue', $scope.modobj.srv_status);
            $('#srv_status_mod').trigger('change');
        }
        else {
            $('#sid_mod').attr('readonly', false);
            $("#modal_mod").bootstrapValidator('enableFieldValidators', 'sid_mod', true);
        }
        for (var i in $scope.modal_add_fields) {
        $("#modal_mod").bootstrapValidator('revalidateField', i);
        }
        $('#modal_mod').data('bootstrapValidator').validate();
        if ($("#modal_mod").data('bootstrapValidator').isValid()) {
            $('#modal_mod').bootstrapValidator('disableSubmitButtons', false);
        }
        
    });
    
    // ===================./模态框回调=============
    
    // 获取平台信息
    $rootScope.getPlatforms(0);
    
    $scope.add_ext_load = function() {
        if (typeof($scope.add_ext_loaded) == 'undefined') {
            
            // 表单验证插件
            // ====配置变量
            $scope.modal_add_id = 'modal_mod';
            $scope.modal_add_fields = 
            {
                sid_mod: {
                    message: $rootScope.langs.sid_invalid,
                    validators: {
                        between: {
                            min: 1,
                            max: 4294967295,
                            message: $rootScope.langs.sid_between_invalid
                        },
                        regexp: {
                            regexp: $rootScope.regexp.platid,
                            message: $rootScope.langs.sid_regexp_invalid
                        },
                        notEmpty: {
                            message: $rootScope.langs.sid_not_empty
                        }
                    }
                },
                srv_name_mod: {
                    message: $rootScope.langs.srv_name_invalid,
                    validators: {
                        stringLength: {
                            max: 60,
                            message: $rootScope.langs.srv_name_stringLength_invalid
                        },
                        notEmpty: {
                            message: $rootScope.langs.srv_name_not_empty
                        }
                    }
                },
                srv_ip_mod: {
                    message: $rootScope.langs.srv_ip_invalid,
                    validators: {
                        stringLength: {
                            max: 60,
                            message: $rootScope.langs.srv_ip_stringLength_invalid
                        },
                        regexp: {
                            regexp: /^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/,
                            message: $rootScope.langs.sid_regexp_invalid
                        },
                        notEmpty: {
                            message: $rootScope.langs.srv_ip_not_empty
                        }
                    }
                },
                platids_mod: {
                    message: $rootScope.langs.srv_ip_invalid,
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.platids_not_empty
                        }
                    }
                },
                game_port_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.game_port+$rootScope.langs.not_empty
                        }
                    }
                },
                srv_conf_port_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.srv_conf_port+$rootScope.langs.not_empty
                        }
                    }
                },
                srv_prt_port_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.srv_prt_port+$rootScope.langs.not_empty
                        }
                    }
                },
                gamedb_name_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.gamedb_name+$rootScope.langs.not_empty
                        }
                    }
                },
                gamedb_ip_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.gamedb_ip+$rootScope.langs.not_empty
                        }
                    }
                },
                gamedb_user_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.gamedb_user+$rootScope.langs.not_empty
                        }
                    }
                },
                gamedb_pwd_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.gamedb_pwd+$rootScope.langs.not_empty
                        }
                    }
                },
                logdb_name_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.logdb_name+$rootScope.langs.not_empty
                        }
                    }
                },
                logdb_ip_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.logdb_ip+$rootScope.langs.not_empty
                        }
                    }
                },
                logdb_user_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.logdb_user+$rootScope.langs.not_empty
                        }
                    }
                },
                logdb_pwd_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.logdb_pwd+$rootScope.langs.not_empty
                        }
                    }
                },
                paydb_name_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.paydb_name+$rootScope.langs.not_empty
                        }
                    }
                },
                paydb_ip_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.paydb_ip+$rootScope.langs.not_empty
                        }
                    }
                },
                paydb_user_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.paydb_user+$rootScope.langs.not_empty
                        }
                    }
                },
                paydb_pwd_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.paydb_pwd+$rootScope.langs.not_empty
                        }
                    }
                },
                cq_root_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.cq_root+$rootScope.langs.not_empty
                        }
                    }
                },
                static_url_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.static_url+$rootScope.langs.not_empty
                        }
                    }
                },
                web_static_mod: {
                    validators: {
                        notEmpty: {
                            message: $rootScope.langs.web_static+$rootScope.langs.not_empty
                        }
                    }
                }
            };
            // ====初始化
            $scope.form_bootstrapValidator_excluded = ''; // 不管是否隐藏，都需要验证，保证平台信息折叠后也能正常验证
            $rootScope.form_bootstrapValidator_init($scope.modal_add_id, $scope.modal_add_fields, $scope.form_bootstrapValidator_excluded);
            
            console_log('modal_add_fields', $scope.modal_add_fields);
            
            // 区服状态slider插件初始化
            $scope.srv_status_change = function(){
                var status = $(this).bootstrapSlider('getValue');
                var color = '';
                switch (status) {
                    case 0:
                        color = 'rgb(0, 166, 90)';
                        break;
                    case 1:
                        color = 'rgb(54, 127, 169)';
                        break;
                    case 2:
                        color = 'rgb(249, 206, 138)';
                        break;
                    case 3:
                        color = 'rgb(221, 79, 57)';
                        break;
                    case 4:
                        color = 'rgb(215, 215, 215)';
                        break;
                }
                $('#srv_status_slider').find('.slider-selection').css('background', color);
                $('#srv_status_slider').find('.round').css('background', '');
                $('#srv_status_slider').find('.round.min-slider-handle').css('background', color);
                $('#srv_status_slider').find('.round.in-selection').css('background', color);
            };
            var status_keys = [];
            var status_vals = [];
            $.each($rootScope.langs.srv_status_x, function(k, v){
                status_keys.push(k);
                status_vals.push(v);
            });
            $('#srv_status_mod').bootstrapSlider({
                ticks: status_keys,
                ticks_labels: status_vals,
                ticks_snap_bounds: 1,
                value: 1,
                tooltip: 'hide'
            })
            .on('change', $scope.srv_status_change);
            $('#srv_status_mod').trigger('change');
            
            //展开后需要relayout Slider
            $('a[data-target="#collapseSeven_add"]').one('click', function(){
                setTimeout(function(){
                    $('#srv_status_mod').bootstrapSlider('relayout');
                }, 100);
                
            });
            
            
            $scope.add_ext_loaded = true;
        }
    }
    
    // 平台信息二级栏位展开/折叠点击事件处理
    $('.panel-collapse').on('show.bs.collapse', function () {
        $(this).prev().find('i').removeClass().addClass('fa fa-chevron-circle-down');
    }).on('hide.bs.collapse', function () {
        $(this).prev().find('i').removeClass().addClass('fa fa-chevron-circle-right');
    })


    $scope.search_ext_load = function() {
        if (typeof($scope.search_ext_loaded) == 'undefined') {
            // 开服时间
            $('#optm_mod').datetimepicker({
                format: "yyyy-mm-dd hh:ii:ss",
                autoclose: true,
                startDate: today
            });

            // 合服时间
            $('#combine_tm_mod').datetimepicker({
                format: "yyyy-mm-dd hh:ii:ss",
                autoclose: true,
                startDate: today
            });

            // 选择下拉插件
            $('.select2').select2();

            // 选择多选框插件（修改区服信息）
            $('#platids_mod').selectpicker({
                'selectedText': 'cat'
            });

            // 选择多选框插件（查询区服信息）
            $('#search_theads').selectpicker({
                'selectedText': 'cat'
            }); 
            
            
            $scope.search_ext_loaded = true;
        }
    }
    
    $scope.get_platnames_by_platids = function (platids) {
    var platforms = [];
    for (var i in platids) {
    if (typeof($rootScope.platforms[platids[i]]) == 'undefined') {
    platforms.push(platids[i]);
    }
    else {
    platforms.push($rootScope.platforms[platids[i]].name);
    }
    
    }
    return platforms.toString();
    }
    
    //操作失败显示新增/修改/复制新增模态框回调
    $scope.modal_mod_show = function() {
    $('#modal_mod').modal('show');
    }
    
    // =================当页数据导出================
    $scope.export = function() {
        $scope.gridApi.exporter.csvExport( 'visible', 'visible');
    }
    // =================./当页数据导出================
});