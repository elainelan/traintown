app.controller("admin.permission_role.ctrl", function ($scope, focus, $rootScope, $http) {

    var api_name = 'admin.permission_role';
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
    
    $scope.headfunc = {};// title
    //$scope.headfunc.manager = $rootScope.getPrivileges(api_name+'.manager');
    $scope.pri = {};//功能
    $scope.pri.manager = $rootScope.getPrivileges(api_name+'.manager');

    // ==============数据获取=================
    $scope.get_data = function (page) {
        var post_data = {a: 'get', page: page, num: $scope.length_select};
        // 合并查询条件
        angular.extend(post_data, $scope.search);
        // 获取第几页数据
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
    // 分页跳转
    $scope.jump = function (page) {
        $scope.get_data(page);
    };
    $scope.get_data(); // 页面初始化，先获取第一页数据

    // ==============./数据获取=================

    // ==================查找=================
    $scope.modal_search = function () {
        $('#modal_search').modal("show");
    };
    $scope.modal_search_ok = function () {
        // 确认搜索
        $scope.get_data(1);
        $('#modal_search').modal("hide");
    };
    // ==================./查找===============
    // ===================删除=================
    $scope.modal_del = function (role_id, idname) {
        // 弹出删除确认框
        $rootScope.langs.modal_del_body = $rootScope.langs.confirm_del_user;
        $scope.delobj = {role_id: role_id, user_id: idname.split(',')[0]};
        $('#modal_del').modal("show");
    };
    $scope.modal_del_ok = function () {
        var post_data = {a: 'manager', b: 'del'};
        // 合并删除条件
        // var del_data = {user_id: $scope.delobj.user_id, role_id: parseInt($scope.delidname)};
        angular.extend(post_data, $scope.delobj);
        // 确认删除
        $('#modal_del').modal("hide");
        $http.post(getApiUrl(api_name), post_data).then(function (response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            }
            else {
                $scope.jump($scope.data.page_current);
            }
        });
    };
    // =================./删除================

    // ===================添加===================
    $scope.modal_add = function (obj) {
        // 弹出添加框
        $http.post(getApiUrl(api_name), {a: 'manager', b: 'add_get_users', role_id: obj.role_id}).then(function (response) {
            if (response.data.data == 0) {      // 返回 0 时,是所有的角色都已经有了
                $rootScope.modal_err($rootScope.langs['no_role_to_user']);
            } else {
                $scope.addobj = angular.copy(obj);
                $scope.users = response.data.data;
                // 弹出添加框
                $('#modal_add').modal('show');
            }
        });
    };
    $scope.modal_add_ok = function () {
        // 确认添加
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
            }
        });
    };

    $scope.modal_add_checked = function ($event, obj) {
        var checkbox = $event.target;
        var checked = checkbox.checked;
        if (checked) {
            $scope.add.user_ids.push(obj.id);
        } else {
            $scope.add.user_ids.splice($scope.add.user_ids.indexOf(obj.id), 1);
        }
    };

    $scope.add = {};
    $scope.add.user_ids = [];
    $scope.isSelected = function (id) {
        return $scope.add.user_ids.indexOf(id) >= 0;
    };

    // ===================./添加==================


    // ===================模态框回调===============
    $('#modal_search').on("show.bs.modal", function () {
        $scope.ext_load();
    });
    $('#modal_add').on("show.bs.modal", function () {
        $scope.ext_load();
        $scope.add.role_id = $scope.addobj.role_id;
    });
    $('#modal_add').on('hidden.bs.modal', function () {
        delete $scope.addobj;
        $scope.add = {};
        $scope.add.user_ids = [];
        $scope.add.role_id = null;
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

    $scope.ext_load = function () {
        if (typeof($scope.ext_loaded) == 'undefined') {
            // 选择插件
            $('.select2').select2();
            $scope.ext_loaded = true;
        }
    }
});
