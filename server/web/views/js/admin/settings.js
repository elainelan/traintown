app.controller('admin.settings.ctrl', function ($scope, focus, $filter, $rootScope, $http) {

    var api_name = 'admin.settings';
    
     // 生成面包屑导航
    $rootScope.breadcrumb();
    
    // 合并页面语言包
    $rootScope.setLang(api_name.replace(/\./, '/')+'.json');
    
    
    $scope.add = {};
    $scope.headfunc = {};// 功能
    $scope.headfunc.manager = $rootScope.getPrivileges(api_name+'.manager');

    // 二级栏位展开/折叠点击事件处理
    $('.panel-collapse').on('show.bs.collapse', function () {
        $(this).prev().find('i').removeClass().addClass('fa fa-chevron-circle-down');
    }).on('hide.bs.collapse', function () {
        $(this).prev().find('i').removeClass().addClass('fa fa-chevron-circle-right');
    })
    
    // ==============数据获取=================
    $scope.get_data = function(page) {
        var post_data = { a:'get' };
        // 合并查询条件
        angular.extend(post_data, $scope.add);
        
        // 获取第几页数据
        $http.post(getApiUrl(api_name), post_data).then(function (response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            } else {
                $scope.r = 1;
                if (response.data.data) {
                    $scope.add = response.data.data;
                }
            }
        });
    }
    $scope.get_data(); // 页面初始化，先获取第一页数据
    // ==================./数据获取============
    
    
    // ================保存=============
    // 确认提交
    $scope.save_ok = function () {
        var post_data = { a:'manager', b:'manager'};
        // 合并条件
        angular.extend(post_data, $scope.add);

        $http.post(getApiUrl(api_name), post_data).then(function(response) {
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            }
            else {
                $rootScope.modal_succ($scope.langs.save_succ);
                $scope.get_data();
            }
        });
    };
    
    $scope.save = function() {
        $scope.save_ok();
    }
    
});

