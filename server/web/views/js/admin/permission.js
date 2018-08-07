app.controller("admin.permission.ctrl", function ($scope,$q, focus, $rootScope, $http) {

    var api_name = 'admin.permission';
    // 生成面包屑导航
    $rootScope.breadcrumb();

    // 合并页面语言包
    $rootScope.setLang(api_name.replace(/\./, '/') + '.json');

    // 页面变量初始化
    $scope.r = 0; // 获取结果状态
    
    // ====分页种类 如果需要自定义，可以在这里重新写
    //$scope.page_number = $rootScope.page_number;
    // ====默认分页数量
    $scope.length_select = $rootScope.get_default_page_number();
    
    $scope.add = {};
    
    $scope.headfunc = {};// 功能
    $scope.headfunc.manager = $rootScope.getPrivileges(api_name+'.manager');

    // 获取权限treeids 生成权限树（有管理权限的才需要）
    if ($scope.headfunc.manager) {
        $http.post(getApiUrl(api_name), {a: 'manager', b: 'treeids'}).then(function (response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            } else {
                $scope.zNodes = response.data.data;
                angular.forEach($scope.zNodes, function (value, index) {         // 根据treeid 将获取 name
                    if (value.uri) {
                        value['name'] = $rootScope.langs['tree_' + value.uri];
                    } else {
                        value['name'] = $rootScope.langs['tree_' + value.id];
                    }
                });
            }

        });
    }
    

    // 获取所有角色信息
    var post_data = {a: 'get', b: 'admin_roles'};
    $http.post(getApiUrl(api_name), post_data).then(function (response) {
        if (response.data.r == 0) {
            $rootScope.modal_err(response.data.errCode);
        }
        else {
            $scope.admin_roles = response.data.data;
        }
    });

    // 页面获取数据
    $scope.get_data = function (page) {
        var post_data = {a: 'get', page: page, num: $scope.length_select};
        angular.extend(post_data, $scope.search);
        $http.post(getApiUrl(api_name), post_data).then(function (response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            } else {
                $scope.r = 1;
                $scope.data = response.data.data;
                $scope.go_page = $scope.data.page_current;
                // 搜索条件样式重置
                $scope.modal_search_reset();
            }
        });
    };
    $scope.jump = function (page) {
        if (!$scope.is_moded()){
            // GO跳转/分页长度调整
            $scope.get_data(page);
        }
    }
    $scope.get_data(); // 页面初始化，先获取第一页数据


    // ==================查找=================
    $scope.modal_search = function () {
        if (!$scope.is_moded()){
            $('#modal_search').modal("show");
        }
    };
    $scope.modal_search_ok = function () {
        // 确认搜索
        $scope.get_data(1);
        $('#modal_search').modal("hide");
    };
    // ==================./查找===============

    // ===================删除=================
    $scope.modal_del = function(x) {
        if (!$scope.is_moded()) {
            // 弹出删除确认框
            $rootScope.langs.modal_del_body = $rootScope.langs.confirm_del_oldrole;
            $scope.delobj = {role_id: x};
            $('#modal_del').modal("show");
        }
    };
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
                if ($scope.data.items.length == 1 && $scope.data.page_current > 1) {
                    $scope.jump($scope.data.page_current - 1);
                } else {
                    $scope.jump($scope.data.page_current);
                }
            }
        });
    };
    // =================./删除================

    // ================管理角色权限=============
    $scope.manager = function (x) {
        if (!$scope.is_moded()){
            $scope.addobj = {};
            $scope.addobj.role_id = x.id;
            $scope.addobj.oldtreeids = x.treeids;
            var tmpzNodes = angular.copy($scope.zNodes);
            angular.forEach(tmpzNodes, function (value, index) {
                if (x.treeids.indexOf(value.id) >= 0) {
                    value.checked = true;
                }
            });
            var setting = {
                check: {
                    enable: true,
                    autoCheckTrigger: true
                },
                data: {
                    simpleData: {
                        enable: true
                    }
                },
                callback: {
                    onClick: function (e, treeId, treeNode, clickFlag) {
                        zTree.checkNode(treeNode, !treeNode.checked, true);
                    }
                }, // 点击文字勾选
                view: {showIcon: false} //不显示图标
            };
            $.fn.zTree.init($("#treeDemo"), setting, tmpzNodes);
            var zTree = $.fn.zTree.getZTreeObj("treeDemo");
            zTree.setting.check.chkboxType = {"Y": 'ps', "N": 'ps'};
            var nodes = zTree.getCheckedNodes(true);
            for (var i = 0, l = nodes.length; i < l; i++) {
                zTree.checkNode(nodes[i], true, true);
            }
            $("#modal_manager").modal('show');
        }
    };

    $scope.manager_ok = function () {
        $scope.addobj.newtreeids = new Array();
        var zTree = $.fn.zTree.getZTreeObj("treeDemo");
        var nodes = zTree.getCheckedNodes(true);  // 获取以勾选的
        angular.forEach(nodes, function (value, index) {
            if (value.pId != null) {
                switch (value.getParentNode().check_Child_State) {
                    case 1:
                        $scope.addobj.newtreeids.push(value.id);            //  当该节点父节点为部分子节点被选，放入treeids
                        break;
                    default:
                        break;
                }
            } else {
                $scope.addobj.newtreeids.push(value.id);
            }
            if (value.isParent) {
                switch (value.check_Child_State) {      // 当该节点本身为父节点时，如果是本位节点被选，则删除该节点
                    case 1:
                        $scope.addobj.newtreeids.splice($scope.addobj.newtreeids.indexOf(value.id), 1);
                        break;
                    default:
                        break;
                }
            }
        });
        // 如果 treeids 没有变化直接关闭模态框
        if ($scope.addobj.oldtreeids.sort().toString() != $scope.addobj.newtreeids.sort().toString()) {
            var post_data = {a:'manager',b:'update'};
            angular.extend(post_data,$scope.addobj);
            $http.post(getApiUrl(api_name), post_data).then(function (response) {
                if (response.data.r == 0) {
                    $rootScope.modal_err(response.data.errCode);
                }
                else {
                    $scope.jump($scope.data.page_current);
                }
            });
        }
        $("#modal_manager").modal('hide');
    };

    // ===================./管理角色权限===================.

    // ===================添加===================
    $scope.modal_add = function () {
        if (!$scope.is_moded()){
            // 弹出添加框
            $('#modal_add').modal('show');
        }
    };
    
    $scope.modal_add_reset = function () {
        // 新增重置功能
        $scope.add = {};
        focus('role_name');
        
        // 表单验证重新处理
        $('#modal_add').bootstrapValidator('resetForm', 'true');
        $('#modal_add').data('bootstrapValidator').validate();
    }
    
    $scope.modal_add_check = function() {
        // 添加数据验证
        var defer=$q.defer();  //声明延后执行
        var data = {a: 'manager', b: 'check', role_name: $scope.add.role_name};
        $http.post(getApiUrl(api_name), data).then(function (response) {
            if (response.data.data.length > 0) {
                defer.resolve(false);
            } else {
                defer.resolve(true);
            }
        });
        return defer.promise;
    };
    $scope.modal_add_ok = function () {
        var promise = $scope.modal_add_check();
        promise.then(function (data) {
            //确认添加
            if (data) {
                $('#modal_add').modal('hide');
                var post_data = {a: 'manager', b: 'add'};
                // 合并添加数据
                angular.extend(post_data, $scope.add);
                $http.post(getApiUrl(api_name), post_data).then(function (response) {
                    if (response.data.r == 0) {
                        $rootScope.modal_err(response.data.errCode);
                    }
                    else {
                        $scope.jump($scope.data.page_current);
                        $scope.modal_add_reset();
                    }
                });
            }else{
                focus('role_name');
//                $scope.add.name_flag = 1;
            }
        })
    };

    // ===================修改===================
    $scope.is_moded = function() {
        // 是否正在修改
        if (typeof($scope.modobj) != 'undefined' && $scope.modobj) {
            $rootScope.modal_err($rootScope.langs.has_moded, function () {
                focus("mod_desc");
            });
            return true;
        }
        return false;
    };
    $scope.mod_cancel = function(obj) {
        // 点击修改取消（关闭保存和取消按钮，显示修改按钮）
        obj.edit = false;
        obj.desc = $scope.modobj.desc;
        delete $scope.modobj;
    };
    $scope.moded_add = function(obj) {
        // 点击修改按钮（关闭修改按钮，显示保存和取消按钮）
        obj.edit = true;
        $scope.modobj = angular.copy(obj);
        focus("mod_desc");
    };
    $scope.mod = function (obj) {
        // 点击修改按钮
        if (!$scope.is_moded()) {
            $scope.moded_add(obj);
        }
    };
    $scope.mod_ok = function (obj) {
        if ($scope.modobj.desc != obj.desc ){
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
        
    });
    $('#modal_search').on("show.bs.modal", function(){
        $scope.search_ext_load();
    });

    $('#modal_add').on("shown.bs.modal", function(){
        focus('role_name');
        // 表单内容验证
        $("#modal_add").data('bootstrapValidator').validate();
        if ($("#modal_add").data('bootstrapValidator').isValid()) {
            $('#modal_add').bootstrapValidator('disableSubmitButtons', false);
        }
    });
    $('#modal_add').on("show.bs.modal", function(){
        //$scope.add = {};
        $scope.add_ext_load();
    });
    $('#modal_add').on('hidden.bs.modal', function () {
        delete $scope.addobj;
        //$scope.add = {};
        $scope.add.role_ids = [];
    });
    $('#modal_manager').on('hidden.bs.modal', function () {
        delete $scope.addobj;
        //$scope.add = {};
        $scope.add.role_ids = [];
    });
    // ===================./模态框回调=============

    // 获取所有角色信息
    var post_data = {a: 'get', b: 'admin_roles'};
    $http.post(getApiUrl(api_name), post_data).then(function (response) {
        if (response.data.r == 0) {
            $rootScope.modal_err(response.data.errCode);
        }
        else {
            $scope.admin_roles = response.data.data;
        }
    });

    
    $scope.search_ext_load = function() {
        if (typeof($scope.search_ext_loaded) == 'undefined') {
            
            // 选择插件
            $('.select2').select2();
            
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
                role_name: {
                    message: $rootScope.langs.add_role_name_invalid,
                    validators: {
                        stringLength: {
                            message: $rootScope.langs.add_role_name_stringLength,
                            max: 30
                        },
                        remote: {
                            message: $rootScope.langs.add_role_name_exist,
                            url: getApiUrl(api_name),
                            type: 'POST',
                            data: {a: 'manager', b: 'check', data_type: 'bootstrapValidator'}
                        },
                        notEmpty: {
                            message: $rootScope.langs.add_role_name_not_empty
                        }
                    }
                },
                add_role_desc: {
                    message: $rootScope.langs.add_role_desc_invalid,
                    validators: {
                        stringLength: {
                            message: $rootScope.langs.add_role_desc_stringLenth,
                            max: 100
                        },
                        notEmpty: {
                            message: $rootScope.langs.add_role_desc_not_empty
                        }
                    }
                }
            };
            // ====初始化
            $rootScope.form_bootstrapValidator_init($scope.modal_add_id, $scope.modal_add_fields, $rootScope.form_bootstrapValidator_excluded);
            
            
            $scope.add_ext_loaded = true;
        }
    }
});