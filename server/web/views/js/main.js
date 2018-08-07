
// 控制器：userCtrl
app.controller("userCtrl", function ($scope, focus, $rootScope, $http, $location) {
    
    // ============ userCtrl 加载逻辑及方法 =============
    // 获取全局base语言包，$rootScope.langBase
    $http.get(getLangUrl('lang.base.json')).then(function (response) {
        $rootScope.langBase = response.data;
        $http.get(getLangUrl('lang.base.proj.json')).then(function (response) {
            angular.extend($rootScope.langBase, response.data);
            $rootScope.langs = {};
            angular.extend($rootScope.langs, $rootScope.langBase);
            //console_log("基础语言包信息：$rootScope.langBase", $rootScope.langBase);
        });
    });

    // 获取登录用户信息，$rootScope.users
    $http.post(getApiUrl('admin.is_logined')).then(function (response) {
        if (response.data.r == 0) {
            window.location.href = "index.html?platid=" + platid;
        }
        $rootScope.users = response.data;
        console_log("登录用户信息：$rootScope.users", response.data);
    });

    // 获取菜单栏
    $http.post(getApiUrl('admin.menu')).then(function (response) {
        if (response.data.data != null) {
            $rootScope.treeMenu = response.data.data.menu;
            $rootScope.hidden = response.data.data.hidden;
        }
        console_log('菜单栏$rootScope.treeMenu', $rootScope.treeMenu);
        console_log('菜单栏$rootScope.hidden', $rootScope.hidden);
    });
    
     // 用户退出操作方法
    $scope.signout = function () {
        $http.post(getApiUrl('admin.logout')).then(function (response) {
            window.location.href = "index.html?platid=" + platid;
        }); 
    };
    
    
    //绑定模态框
    $scope.bind_qrcode_url = '';
    $scope.modal_bind = function () {
        if ($scope.bind_qrcode_url.length==0) {
            $http.post(getApiUrl('admin.bind'), {a:"bind"}).then(function (response) {
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode);
                } else {
                    $scope.bind_qrcode_url = response.data.data;
                    var img = new Image();
                    img.src = $scope.bind_qrcode_url;
                    img.width = '300';
                    console.log(img);
                    $('#modal_bind #content_img').append(img);
                    $('#modal_bind').modal('show');
                }
            });
        }
        else {
            $('#modal_bind').modal('show');
        }
    }
    
    // 菜单栏sidebar加载完成后处理
    $scope.sidebarmenu_done = function() {
        //console.log('sidebarmenu_done');
        // tree初始化
        $('.sidebar-menu').tree({accordion: false, animationSpeed: 200});
        
        // 菜单栏二级菜单展开/折叠点击事件处理
        $('.sidebar-menu').on('click', '.treeview a.treemenu2', function (event) {
            var isOpen = $(this).parent().hasClass('menu-open');
            var element_i = $(this).find('i');
            
            //console_log('treeMenu_isOpen', isOpen);
            if (isOpen) {
                element_i.removeClass().addClass('fa fa-minus-circle');
            }
            else {
                element_i.removeClass().addClass('fa fa-plus-circle');
            }
        });
    };
    
    // 页面加载完成后的通用处理
    $scope.$watch('$viewContentLoaded', function(){
        $('body').tooltip({
            selector: "[data-toggle='tooltip']",
            container: 'body'
        });
    })
    // ============ ./userCtrl 加载逻辑及方法 =============
    
    
    
    
    
    
    
    
    // ========  各种全局方法 =============
    
    // 全局： 合并子页面语言包
    // ====子页面语言包：$rootScope.langs
    $rootScope.setLang = function (pageLangFile) {
        $http.get(getLangUrl(pageLangFile)).then(function (response) {
            angular.extend($rootScope.langs, $rootScope.langBase, response.data);
            //console_log("基础语言包信息：$rootScope.langBase", $rootScope.langBase);
            //console_log("子页面语言包信息：langPage", langPage);
            console_log('子页面语言包：$rootScope.langs', $rootScope.langs);
        });
    };
    
    // 全局：生成子页面导航栏
    // ====子页面导航栏数组：$rootScope.navtag
    $rootScope.breadcrumb = function () {
        
        // 获取子页面访问路径
        $rootScope.get_pathUrl();
        // 第一次展示后台页面的菜单栏调整
        $rootScope.menu_init();
        
        var len = $rootScope.pathUrl.length;
        $rootScope.navtag = new Array();
        switch (len){
            case 3:
                $rootScope.navtag.push($rootScope.langs['tree_' + $rootScope.pathUrl[0]]);
                $rootScope.navtag.push($rootScope.langs['tree_' + $rootScope.pathUrl[2]]);
                $rootScope.navtag.push($rootScope.langs['tree_' + $rootScope.pathUrl[0]+'.'+ $rootScope.pathUrl[1]]);
                break;
            case 1:
                $rootScope.navtag.push($rootScope.langs['tree_' + $rootScope.pathUrl[0]]);
                break;
            case 2:
                $rootScope.navtag.push($rootScope.langs['tree_' + $rootScope.pathUrl[0]]);
                $rootScope.navtag.push($rootScope.langs['tree_' + $rootScope.pathUrl[0]+'.'+ $rootScope.pathUrl[1]]);
                break;
        }
        console_log('子页面导航栏：$rootScope.navtag', $rootScope.navtag);
    };
    
    // 全局： 获取子页面访问路径
    // ====访问的子页面路径数组：$rootScope.pathUrl    ( admin permission_user 7 )
    // ====访问的子页面路径字符：$rootScope.menuMethod ( admin.permission_user )
    $rootScope.get_pathUrl = function() {
        $rootScope.pathUrl = $location.url().split('/').splice(1);
        $rootScope.menuMethod = "";
        if ($rootScope.pathUrl[1]) {
            $rootScope.menuMethod = $rootScope.pathUrl[0] + '.' + $rootScope.pathUrl[1];
            console_log('访问的子页面路径字符：$rootScope.menuMethod', $rootScope.menuMethod);
        }
        console_log('访问的子页面路径数组：$rootScope.pathUrl', $rootScope.pathUrl);
    }
    
    // 全局：根据访问路径地址，打开对应菜单项
    $rootScope.menu_init = function() {
        if (!$rootScope.menu_init_done) {
            if ($rootScope.pathUrl[0]) {
                // console_log('pathUrl_0', '#menu_'+$rootScope.pathUrl[0]);
                $('#menu_'+$rootScope.pathUrl[0]).click();
            }
            if ($rootScope.pathUrl[2]) {
                // console_log('pathUrl_2', '#menu_id_'+$rootScope.pathUrl[2]);
                $('#menu_id_'+$rootScope.pathUrl[2]).click();
            }
        }
        $rootScope.menu_init_done = true;
    }
    
    
    
    // 全局：显示成功的模态框
    $rootScope.modal_succ = function(info, callback) {
        $rootScope.modalClass = {btn: 'btn-success', info: 'text-success', title: 'text-success'};
        $rootScope.langs.modal_title = $rootScope.langs.succ_title;
        $rootScope.langs.modal_info = info;
        
        if (typeof(callback) != 'undefined') {
            $('#modal_info').off('hidden.bs.modal').on('hidden.bs.modal', callback);
        }
        $('#modal_info').modal('show');
    }
    
    // 全局：显示失败的模态框
    $rootScope.modal_err = function(errCode, callback) {
        $rootScope.modalClass = {btn: 'btn-danger', info: 'text-danger', title: 'text-danger'};
        $rootScope.langs.modal_title = $rootScope.langs.error_title;
        
        var err_lang_key = 'error_' + errCode;
        if (typeof($rootScope.langs[err_lang_key]) != 'undefined') {
            $rootScope.langs.modal_info = $rootScope.langs[err_lang_key];
        }
        else {
            $rootScope.langs.modal_info = errCode;
        }
        
        // 特殊处理未登录情况
        if (parseInt(errCode) == 100002 || parseInt(errCode) == 100006) {
            callback = function(){
                window.location.href = "index.html?platid=" + platid;
            };
        }
        
        if (typeof(callback) != 'undefined') {
            $('#modal_info').off('hidden.bs.modal').on('hidden.bs.modal', callback);
        }
        $('#modal_info').modal('show');
    }
    
    // 全局：获取平台信息方法
    // ====平台信息数组：$rootScope.platforms
    $rootScope.getPlatforms = function(forceUpdate) {
        if (typeof($rootScope.platforms) == 'undefined' || forceUpdate == 1) {
            $http.post(getApiUrl('common.platforms')).then(function(response){
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode);
                }
                $rootScope.platforms = response.data.data;
                console_log('所有平台配置信息：$rootScope.platforms', $rootScope.platforms);
            });
        }
        else {
            console_log('所有平台配置信息（已获取过）：$rootScope.platforms', $rootScope.platforms);
        }
    }
    
    // 全局：获取区服信息方法
    // ====平台信息数组：$rootScope.servers
    $rootScope.getServers = function(forceUpdate) {
        if (typeof($rootScope.servers) == 'undefined' || forceUpdate == 1) {
            $http.post(getApiUrl('common.servers')).then(function(response){
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode);
                }
                $rootScope.servers = response.data.data;
                console_log('所有区服配置信息：$rootScope.servers', $rootScope.servers);
            });
        }
        else {
            console_log('所有区服配置信息（已获取过）：$rootScope.servers', $rootScope.servers);
        }
    }

    // 全局：获取所有后台账号信息方法
    // ====所有后台账号信息：$rootScope.admin_users
    $rootScope.getAdminUsers = function() {
        if (typeof($rootScope.admin_users) == 'undefined') {
            $http.post(getApiUrl('common.admin_users')).then(function (response) {
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode);
                } else {
                    $rootScope.admin_users = response.data.data;
                    console_log('所有后台账号信息：$rootScope.admin_users', $rootScope.admin_users);
                }
            });
        }
    }
    
    // 全局：判断是否有某个权限方法
    $rootScope.getPrivileges = function(uri) {
        var priRes = false;
        if (typeof($rootScope.hidden) != 'undefined') {
            angular.forEach($rootScope.hidden, function(data, index, array) {
                if (data.uri == uri) {
                    priRes = true;
                }
            });
        }
        return priRes;
    }
    
    // 全局：搜索框modal标题切换到“未提交状态”方法
    $rootScope.modal_search_title_change_to_not_submit = function() {
        if (!$("#modal_search_title").hasClass("modal_search_header_not_submit")) {
            $("#modal_search_title").addClass("modal_search_header_not_submit");
            $("#modal_search_title").text($rootScope.langs.modal_search_header_not_submit);
        }
    }
    
    // 全局：搜索框modal标题切换到“正常状态”方法
    $rootScope.modal_search_title_change_to_nomarl = function() {
        if ($("#modal_search_title").hasClass("modal_search_header_not_submit")) {
            $("#modal_search_title").removeClass("modal_search_header_not_submit");
            $("#modal_search_title").text($rootScope.langs.modal_search_header);
        }
    }
    
    // 全局：搜索功能提交后重置表单方法
    $rootScope.modal_search_reset = function() {
        $rootScope.modal_search_title_change_to_nomarl();
    }

    // 全局：搜索下拉框发生变化，但是搜索功能未提交时绑定的功能方法
    $rootScope.modal_search_title_not_submit = function() {
        $rootScope.modal_search_title_change_to_not_submit();
    }
    
    
    
    
    // 全局：dateRangePicker插件相关
    // ====全局：date_range_picker_ranges初始化方法
    $rootScope.getDateRangePickerRanges = function() {
        if (typeof($rootScope.date_range_picker_ranges) == 'undefined') {
            $rootScope.date_range_picker_ranges = {};
            $rootScope.date_range_picker_ranges[$rootScope.langs.date_range_picker_Today] = [moment(), moment()];
            $rootScope.date_range_picker_ranges[$rootScope.langs.date_range_picker_Yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
            $rootScope.date_range_picker_ranges[$rootScope.langs.date_range_picker_Last7Days] = [moment().subtract(6, 'days'), moment()];
            $rootScope.date_range_picker_ranges[$rootScope.langs.date_range_picker_Last30Days] = [moment().subtract(29, 'days'), moment()];
            $rootScope.date_range_picker_ranges[$rootScope.langs.date_range_picker_ThisMonth] = [moment().startOf('month'), moment().endOf('month')];
            $rootScope.date_range_picker_ranges[$rootScope.langs.date_range_picker_LastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
            
            console_log('日期选择页签：$rootScope.date_range_picker_ranges', $rootScope.date_range_picker_ranges);
        }
    }
    // ====全局：date_range_picker_locale初始化方法
    $rootScope.getDateRangePickerLocale = function() {
        if (typeof($rootScope.date_range_picker_locale) == 'undefined') {
            $rootScope.date_range_picker_locale = {
                applyLabel : $rootScope.langs.date_range_picker_applyLabel,
                cancelLabel : $rootScope.langs.date_range_picker_cancelLabel2,
                fromLabel : $rootScope.langs.date_range_picker_fromLabel,
                toLabel : $rootScope.langs.date_range_picker_toLabel,
                customRangeLabel : $rootScope.langs.date_range_picker_customRangeLabel,
                daysOfWeek : $rootScope.langs.date_range_picker_daysOfWeek,
                monthNames : $rootScope.langs.date_range_picker_monthNames,
                format: 'YYYY-MM-DD', // 控件中from和to 显示的日期格式  
                firstDay : 1
            };
        }
    }
    // ====全局：daterangepicker时间范围插件初始化方法
    $rootScope.daterangepicker_init = function(element_id) {
        $rootScope.getDateRangePickerRanges();
        $rootScope.getDateRangePickerLocale();
        $('#'+element_id).daterangepicker({
            ranges: $rootScope.date_range_picker_ranges,
            locale : $rootScope.date_range_picker_locale,
            linkedCalendars: false,
            alwaysShowCalendars: false,
            autoUpdateInput: false
        });
    }
    // ./全局：dateRangePicker插件相关
    
    
    
    
    // 全局：bootstrapValidator插件相关
    // ====全局：bootstrapValidator表单验证初始化方法
    $rootScope.form_bootstrapValidator_excluded = ':disabled, :hidden, :not(:visible)';
    $rootScope.form_bootstrapValidator_init = function(element_id, fields, excluded) {
        $('#'+element_id).bootstrapValidator({
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: fields,
            excluded: excluded
        })
        .on('success.field.bv', function(e, data) {
            // $(e.target)  --> The field element
            // data.bv      --> The BootstrapValidator instance
            // data.field   --> The field name
            // data.element --> The field element
            
            // 表单必须所有字段全部通过验证，提交按钮才能使用
            if (data.bv.getInvalidFields().length > 0) {
                data.bv.disableSubmitButtons(true);
            }
        })
        .on('error.validator.bv', function(e, data) {
            // $(e.target)    --> The field element
            // data.bv        --> The BootstrapValidator instance
            // data.field     --> The field name
            // data.element   --> The field element
            // data.validator --> The current validator name
            
            // 每次只显示1条错误信息
            data.element
                .data('bv.messages')
                // Hide all the messages
                .find('.help-block[data-bv-for="' + data.field + '"]').hide()
                // Show only message associated with current validator
                .filter('[data-bv-validator="' + data.validator + '"]').show();
        });
    }
    // ./全局：bootstrapValidator插件相关
    
    
    // 全局：angular-ui-grid
    $rootScope.ui_grid = {
        // ====全局：获取当前语言配置
        get_i18n: function() {
            return langBase.toLowerCase();
        },
        // ====全局：获取参数gridOptions初始化配置
        init: function() {
            return {
                // 水平scrollBar
                //enableHorizontalScrollbar: 0,
                // 垂直scrollBar
                enableVerticalScrollbar: 0,
                
                // 不显示表格前面的勾选框，配合HTML属性data-ui-grid-selection
                enableRowHeaderSelection: false,
                
                // 外部排序
                //useExternalSorting: true,
                //onRegisterApi: function( gridApi ) {
                //    $scope.gridApi = gridApi;
                //    $scope.gridApi.core.on.sortChanged( $scope, $scope.sortChanged );
                //    $scope.sortChanged($scope.gridApi.grid, [ $scope.gridOptions.columnDefs[1] ] );
                //},
                
                // 显示grid菜单
                enableGridMenu: true,
                
                //导出配置
                exporterMenuCsv : false,  
                exporterOlderExcelCompatibility: true,
                exporterMenuPdf : false,
            };
        },
        
        // ====全局：获取columnDefs[序号]栏位配置
        get_seq: function() {
            return {
                field: 'seq', displayName: $rootScope.langs.seq,
                minWidth: 54, pinnable: true, enableSorting: false, enableFiltering: false,
                cellTemplate: '<div class="ui-grid-cell-contents">{{grid.renderContainers.body.visibleRowCache.indexOf(row)+1+(grid.appScope.data.page_current-1)* grid.appScope.data.items_per_page}}</div>'
            };
        },
        // ====全局：获取exportColumnDefs[序号]导出配置
        get_export_seq: function( grid, row, col, input, $filter ) {
            return grid.renderContainers.body.visibleRowCache.indexOf(row)+1+(grid.appScope.data.page_current-1)* grid.appScope.data.items_per_page;
        },
        
        // ====全局：获取columnDefs的[id流水]栏位配置
        get_id: function(data_key, lang_key, visible) {
            return { field: data_key, displayName: $rootScope.langs[lang_key], minWidth: 90, visible: visible };
        },
        
        // ====全局：获取columnDefs的[时间戳]栏位转换配置
        get_ts: function(data_key, lang_key, visible) {
            return {
                field: data_key, displayName: $rootScope.langs[lang_key], minWidth: 135,
                cellTemplate: '<div class="ui-grid-cell-contents">{{ row.entity.'+data_key+'*1000 ? (row.entity.'+data_key+'*1000 | date:"yyyy-MM-dd HH:mm:ss") : "" }}</div>',
                visible: visible
            };
        },
        // ====全局：获取exportColumnDefs的[时间戳]导出配置
        get_export_ts: function( grid, row, col, input, $filter ) {
            if (input*1000) {
                return $filter('date')(input*1000, 'yyyy-MM-dd HH:mm:ss');
            }
            return "";
        },
        
        // ====全局：获取columnDefs的[时间戳]栏位转换日期天配置
        get_ts_date: function(data_key, lang_key, visible) {
            return {
                field: data_key, displayName: $rootScope.langs[lang_key], minWidth: 100,
                cellTemplate: '<div class="ui-grid-cell-contents">{{ row.entity.'+data_key+'*1000 ? (row.entity.'+data_key+'*1000 | date:"yyyy-MM-dd") : "" }}</div>',
                visible: visible
            };
        },
        // ====全局：获取exportColumnDefs的[时间戳]栏位转换日期天导出配置
        get_export_ts_date: function( grid, row, col, input, $filter ) {
            if (input*1000) {
                return $filter('date')(input*1000, 'yyyy-MM-dd');
            }
            return "";
        },
        
        // ====全局：获取columnDefs的[IP]栏位配置
        get_ip: function(data_key, lang_key, visible) {
            return {
                field: data_key, displayName: $rootScope.langs[lang_key], minWidth: 95, visible: visible
            };
        },
        
        // ====全局：获取columnDefs的[ADMIN_USERID]栏位配置
        get_user: function(data_key, lang_key, visible, toolip) {
            $rootScope.getAdminUsers();
            return {
                field: data_key,
                displayName: $rootScope.langs[lang_key],
                minWidth: 90,
                cellTemplate: '<div class="ui-grid-cell-contents">{{row.entity.'+data_key+'}} : {{grid.appScope.admin_users[row.entity.'+data_key+']["name"] }}</div>',
                visible: visible
            };
        },
        // ====全局：获取exportClumnDefs的[ADMIN_USERID]导出配置
        get_export_user: function( grid, row, col, input, $filter ) {
            return input + ':' + grid.appScope.admin_users[input]["name"];
        },
        
        // ====全局：获取columnDefs的[PLATFORM]栏位配置
        get_platform: function(data_key, lang_key, visible) {
            $rootScope.getPlatforms();
            return {
                field: data_key, displayName: $rootScope.langs[lang_key], minWidth: 100,
                cellTemplate: '<div class="ui-grid-cell-contents">{{row.entity.'+data_key+'}} : {{grid.appScope.platforms[row.entity.'+data_key+']["name"] || grid.appScope.langs["plat_0"]}}</div>',
                visible: visible
            };
        },
        // ====全局：获取columnDefs的[PLATFORM]导出配置
        get_export_platform: function( grid, row, col, input, $filter ) {
            return input + ':' + (input != '0' ? grid.appScope.platforms[input]["name"] : grid.appScope.langs["plat_0"]);
        }
        
    }
    //console_log('$rootScope.ui_grid', $rootScope.ui_grid);
    // 全局：./angular-ui-grid
    
    
    // 全局：正则表达
    $rootScope.regexp = {
        ip: /^[0-9\.]{1,15}$/,
        ip_regexp: /^[0-9\.\*]{1,15}$/,
        daterange: /^([0-9]{3}[1-9]|[0-9]{2}[1-9][0-9]{1}|[0-9]{1}[1-9][0-9]{2}|[1-9][0-9]{3})-(((0[13578]|1[02])-(0[1-9]|[12][0-9]|3[01]))|((0[469]|11)-(0[1-9]|[12][0-9]|30))|(02-(0[1-9]|[1][0-9]|2[0-9])))\s-\s([0-9]{3}[1-9]|[0-9]{2}[1-9][0-9]{1}|[0-9]{1}[1-9][0-9]{2}|[1-9][0-9]{3})-(((0[13578]|1[02])-(0[1-9]|[12][0-9]|3[01]))|((0[469]|11)-(0[1-9]|[12][0-9]|30))|(02-(0[1-9]|[1][0-9]|2[0-9])))$/,
        password: /(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])([\w\+=~!@#$%^&*]{5,})/,
        safekey: /^[a-zA-Z0-9]*$/,
        platid: /^[1-9]\d*$/,
        
        test: ""
    };
    
    // 全局：分页分类
    $rootScope.page_number = ["10", "25", "50", "100"];
    $rootScope.get_default_page_number = function () {
        var cookie_page_number = $.cookie('data_page_number');
        return parseInt(cookie_page_number) > 0 ? cookie_page_number : $rootScope.page_number[0];
    }
    $rootScope.set_default_page_number = function (page_num) {
        return $.cookie('data_page_number', page_num);
    }
    
    // 全局：通用select2插件初始化
    $rootScope.select2_init = function (page_num) {
        $('.select2').select2();
    }
    //全局：通用区服选择相关
    //模态框弹出
    $rootScope.srv_sel_modal_show = function (page_num) {
        $('#modal_srv_sel').modal('show')
    }
    $rootScope.srv_sel_init = function () {
        // 加载语言包
        var srv_api_name = 'servers.manager';
        $rootScope.setLang(srv_api_name.replace(/\./, '/')+'.json');
        // ====默认分页数量
        $rootScope.srv_length_select = $rootScope.get_default_page_number();
        
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
        $rootScope.ui_grid_style = {};
        // ====gridOptions初始化
        $rootScope.gridOptions = $rootScope.ui_grid.init();
        $rootScope.gridOptions.enableRowHeaderSelection = true;
        // ====gridOptions.columnDefs初始化(语言包加载完成后执行)
        $rootScope.unWatch = $rootScope.$watch('langs.copy_add_server', function(){
            if ($rootScope.langs.copy_add_server) {
                $rootScope.unWatch();
                $rootScope.gridOptions.columnDefs = [
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
//                    
//                    { field: 'opver', displayName: $rootScope.langs['opver'] ,visible: false, minWidth:80 },
//                    { field: 'srv_ip', displayName: $rootScope.langs['srv_ip'], minWidth:120 },
//                    { field: 'srv_ip2', displayName: $rootScope.langs['srv_ip2'], visible: false, minWidth:120 },
//                    $rootScope.ui_grid.get_srv_platids('platids', 'platids'),
//                    //{ field: 'platids', displayName: $rootScope.langs['platids'], minWidth:120 },
//                    
//                    $rootScope.ui_grid.get_state_info('msg', 'msg', false),
//                    $rootScope.ui_grid.get_state_info('close', 'close', false),
//
//                    { field: 'gamedb_ip', displayName: $rootScope.langs['gamedb_ip'], visible: false, minWidth:110 },//cquser数据库配置
//                    { field: 'gamedb_name', displayName: $rootScope.langs['gamedb_name'], visible: false, minWidth:110 },
//                    { field: 'gamedb_user', displayName: $rootScope.langs['gamedb_user'], visible: false, minWidth:165 },
//                    { field: 'gamedb_pwd', displayName: $rootScope.langs['gamedb_pwd'], visible: false, minWidth:150 },
//                    
//
//                    { field: 'logdb_ip', displayName: $rootScope.langs['logdb_ip'], visible: false, minWidth:110 },//cqlog数据库配置
//                    { field: 'logdb_name', displayName: $rootScope.langs['logdb_name'], visible: false, minWidth:110 },
//                    { field: 'logdb_user', displayName: $rootScope.langs['logdb_user'], visible: false, minWidth:165 },
//                    { field: 'logdb_pwd', displayName: $rootScope.langs['logdb_pwd'], visible: false, minWidth:150 },
//
//                    { field: 'paydb_ip', displayName: $rootScope.langs['paydb_ip'], visible: false, minWidth:110 },//cqpay数据库配置
//                    { field: 'paydb_name', displayName: $rootScope.langs['paydb_name'], visible: false, minWidth:110 },
//                    { field: 'paydb_user', displayName: $rootScope.langs['paydb_user'], visible: false, minWidth:165 },
//                    { field: 'paydb_pwd', displayName: $rootScope.langs['paydb_pwd'], visible: false, minWidth:150 },
//                    
//                    { field: 'kfdb_ip', displayName: $rootScope.langs['kfdb_ip'], visible: false, minWidth:110 },//cqkf数据库配置
//                    { field: 'kfdb_name', displayName: $rootScope.langs['kfdb_name'], visible: false, minWidth:110 },
//                    { field: 'kfdb_user', displayName: $rootScope.langs['kfdb_user'], visible: false, minWidth:165 },
//                    { field: 'kfdb_pwd', displayName: $rootScope.langs['logdb_pwd'], visible: false, minWidth:150 },
//                    
//
//                    { field: 'cq_root', displayName: $rootScope.langs['cq_root'], visible: false, minWidth:240 },
//                    { field: 'static_url', displayName: $rootScope.langs['static_url'], visible: false, minWidth:240  },
//                    { field: 'web_static', displayName: $rootScope.langs['web_static'], visible: false, minWidth:240  },
//                    { field: 'ropass', displayName: $rootScope.langs['ropass'], visible: false , minWidth:120 },
//                    $rootScope.ui_grid.get_state_info('def', 'def', false),
//                    $rootScope.ui_grid.get_state_info('new', 'new', false),
//                    $rootScope.ui_grid.get_state_info('recomm', 'recomm', false),
//                    //运营状态
//                    {
//                        field: 'srv_status', displayName: $rootScope.langs['srv_status'],
//                        cellTemplate: '<label class="ui-grid-cell-contents" data-ng-class="{\'text-success\':row.entity.srv_status==0,\'text-primary\':row.entity.srv_status==1,\'text-yellow\':row.entity.srv_status==2,\'text-red\':row.entity.srv_status==3,\'text-black\':row.entity.srv_status==4}">{{ grid.appScope.langs["srv_status_x"][row.entity.srv_status] }}</label>',
//                        visible: false,
//                        minWidth:100 
//                    }, 

                    
                ];
               
            }
        });
        // ====注入事件处理方法
        $rootScope.gridOptions.onRegisterApi = function( gridApi ) {
            $rootScope.srvgridApi = gridApi;
            $rootScope.srvgridApi.core.on.sortChanged( $rootScope, $rootScope.sortChanged );
            $rootScope.srvgridApi.selection.on.rowSelectionChanged( $rootScope, $rootScope.selectionChanged );
            $rootScope.srvgridApi.selection.on.rowSelectionChangedBatch( $rootScope, $rootScope.selectionChangedBatch );
            $rootScope.srvgridApi.core.on.rowsRendered( $rootScope, $rootScope.selectedChange );
        };
        
        $rootScope.selected_srvs = {};
        // ===选中/取消事件
        $rootScope.selectionChanged = function(row) {
            if (row.isSelected) {
                $rootScope.selected_srvs[row.entity.sid] = row.entity;
            }
            else {
                delete $rootScope.selected_srvs[row.entity.sid];
            }
            console_log('选择的区服',$rootScope.selected_srvs);
        }
        // ===批量选中/取消事件
        $rootScope.selectionChangedBatch = function(rows) {
            for (var i in rows) {
                var row = rows[i];
                if (row.isSelected) {
                    $rootScope.selected_srvs[row.entity.sid] = row.entity;
                }
                else {
                    delete $rootScope.selected_srvs[row.entity.sid];
                }
            }
            console_log('选择的区服',$rootScope.selected_srvs);
        }
        // ===重新数据变化时已选择区服自动选中
        $rootScope.selectedChange = function() {
            for (var i in $rootScope.gridOptions.data) {
                if (typeof($rootScope.selected_srvs[$rootScope.gridOptions.data[i].sid]) != 'undefined') {
                    $rootScope.srvgridApi.selection.selectRow($rootScope.gridOptions.data[i]);
                }
                else {
                    $rootScope.srvgridApi.selection.unSelectRow($rootScope.gridOptions.data[i]);
                }
            }
            //刷新显示
            $rootScope.srvgridApi.core.queueRefresh();
        }
        // ===删除选中区服事件
        $rootScope.selection_srv_del = function(sid) {
            console.log(sid);
            delete $rootScope.selected_srvs[sid];
            console_log('选择的区服',$rootScope.selected_srvs);
        }
        // ===全部删除选中区服事件
        $rootScope.selection_srv_del_all = function() {
            $rootScope.selected_srvs = {};
            console_log('选择的区服',$rootScope.selected_srvs);
        }
        // ===是否选择区服判断
        $rootScope.is_selected_srv = function(srvs) {
            if (typeof srvs == 'undefinde') {
                srvs = $rootScope.selected_srvs;
            }
            if (typeof(srvs) != 'object'){
                return false;
            }
                
            return Boolean(Object.keys(srvs).length);
        }
        // ===是否选择区服判断
        $rootScope.search_plat_change = function(platid) {
            if (platid) {
                $('#srv_search_platid').prop('disabled', false).val(platid).prop('disabled', true);
                //直接使用trigger触发改变事件会报错，设置延时操作正常，原因待研究
                setTimeout(function(){
                    $('#srv_search_platid').trigger('change')
                }, 10);
            }
            else {
                $('#srv_search_platid').prop('disabled', false);
            }
        }
        
        // ====外部排序
        // ========开启
        $rootScope.gridOptions.useExternalSorting = true;
        // ========事件处理
        $rootScope.sortChanged = function ( grid, sortColumns ) {
            $rootScope.srv_orderby = {};
            $rootScope.srv_orderby.orderby = {};
            if (sortColumns.length > 0) {
                angular.forEach(sortColumns, function(data){
                    $rootScope.srv_orderby.orderby[data.field] = data.sort.direction;
                });
            }
            // 重新获取数据
            $rootScope.get_data();
        };
        
        
        // ==============数据获取=================
        $rootScope.get_data = function(page) {
            var post_data = { a:'get', page:page, num:$rootScope.srv_length_select };
            // 合并查询条件
            angular.extend(post_data, $rootScope.srv_search, $rootScope.srv_orderby);
            
            // 获取第几页数据
            $http.post(getApiUrl(srv_api_name), post_data).then(function (response) {
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode);
                } else {
                    $rootScope.r = 1;
                    $rootScope.data = response.data.data;
                    
                    
                    // 显示数据绑定
                    $rootScope.ui_grid_style.height = (parseInt($rootScope.data.items.length) + 1)*30 + 'px';
                    $rootScope.gridOptions.data = $rootScope.data.items;
                    $rootScope.go_page=$rootScope.data.page_current;
                    // 搜索条件样式重置
                    $rootScope.modal_search_reset();
                }
            });
        }

        // 翻页跳转功能
        $rootScope.jump = function (page) {
            // GO跳转/分页长度调整
            $rootScope.get_data(page);
        }

        $rootScope.get_data(); // 页面初始化，先获取第一页数据
        
        // 页面加载完成后的通用处理
        $scope.$watch('$viewContentLoaded', function(){
            $('#selected_srvs').boxWidget();
        });
    }
    // ========  ./各种全局方法 =============
    
});
