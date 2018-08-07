app.controller('export.table.ctrl', function ($scope, focus, $filter, $rootScope, $http) {

    var api_name = 'export.table';
    
    // 生成面包屑导航
    $rootScope.breadcrumb();
    
    // 合并页面语言包
    $rootScope.setLang(api_name.replace(/\./, '/')+'.json');
    
    // 页面变量初始化
    $scope.search = {sids:{}};
    
    $scope.db_table_conf = {};
    $http.post(getApiUrl(api_name), {a:'get_conf'}).then(function(response){
        if (response.data.r == 0) {
            $rootScope.modal_err(response.data.errCode);
        }
        $scope.db_table_conf = response.data.data;
        console_log('数据库配置信息：$scope.db_table_conf', $scope.db_table_conf);
    });

    $scope.download = function() {
    	var data={a:'download'};
    	$scope.search.sids = {};
    	for (var sid in $rootScope.selected_srvs) {
    		$scope.search.sids[sid] = true;
    	}
    	$scope.search.sids
    	angular.extend(data, $scope.search);
    	$rootScope.is_downloading = true;
    	$('#modal_export').modal('show');
        $http.post(getApiUrl(api_name), data).then(function(response){
            if (response.data.r == 0) {
                $rootScope.modal_err(response.data.errCode);
            } else {
                window.location.href = getExport(response.data.data);
            }
            $rootScope.is_downloading = false;
        	$('#modal_export').modal('hide');
        });
    }
    

//    //区服全选/反选
//    $scope.checkbox_select_all = function (classname) {
//    	$.each($('.'+classname), function(){
//    		var val = $(this).val();
//    		$scope.search.sids[val] = !$scope.search.sids[val];
//    		//$(this).prop('checked', !$(this).prop('checked'));
//    	});
//    }
//    
//    //检测是否选择区服
//    $scope.checkbx_check = function (data) {
//    	for (var i in data) {
//    		if (data[i]) {
//    			return true;
//    		}
//    	}
//    	return false;
//    }
//    
//    //切换平台
//    $scope.modal_search_platform = function (classname) {
//    	var tmp = {};
//    	$.each($rootScope.servers, function(){
//    		if (this.platids.indexOf($scope.search.platid) == -1) {
//    			$scope.search.sids[this.sid] = false;
//    		}
//    	});
//    	
//    }
    
});