app.controller("welcome.ctrl", function ($scope, $rootScope, $http) {

    var api_name = 'welcome';
    
    // 生成面包屑导航
    $rootScope.breadcrumb();
   
    // 合并页面语言包
    $rootScope.setLang(api_name.replace(/\./, '/')+'.json');
});