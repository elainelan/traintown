
// 获取url中的参数
function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg);  //匹配目标参数
    if (r != null) {
        return unescape(r[2]);
    }
    return null; //返回参数值
}
var platid = getUrlParam('platid');
$.cookie('platid', platid);

// 初始化
var app = angular.module("myApp", [], function ($httpProvider) {
    // Use x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

    /**
     * The workhorse; converts an object to x-www-form-urlencoded serialization.
     * @param {Object} obj
     * @return {String}
     */
    var param = function (obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

        for (name in obj) {
            value = obj[name];

            if (value instanceof Array) {
                for (i = 0; i < value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value instanceof Object) {
                for (subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value !== undefined && value !== null)
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function (data) {
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];
}); 

// 获取焦点 service
app.factory('focus', function($timeout, $window) {
    return function(id) {
        // timeout makes sure that it is invoked after any other event has been triggered.
        // e.g. click events that need to run before the focus or
        // inputs elements that are in a disabled state but are enabled when those events
        // are triggered.
        $timeout(function() {
            var element = $window.document.getElementById(id);
            if(element) {
                element.focus();
            }
        });
    };
});

// 获取焦点指令扩展
// <input type="email" id="email" class="form-control">
// <button event-focus="click" event-focus-id="email">Declarative Focus</button>
// <button ng-click="doSomething()">Imperative Focus</button>
app.directive('eventFocus', function(focus) {
    return function(scope, elem, attr) {
        elem.on(attr.eventFocus, function() {
            focus(attr.eventFocusId);
        });
        // Removes bound events in the element itself
        // when the scope is destroyed
        scope.$on('$destroy', function() {
            elem.off(attr.eventFocus);
        });
    };
});

// 支持跨域发送cookie
if (cross_domain == true) {
    app.config(function ($httpProvider) {
        $httpProvider.defaults.withCredentials = true;
    });
}


// 控制器：loginCtrl
app.controller("loginCtrl", function ($scope, focus, $rootScope, $http, $location) {

    $scope.getLang = function() {
        // 获取base语言包
        $http.get(getLangUrl('lang.base.json')).then(function (response) {
            $rootScope.langBase = response.data;
            $http.get(getLangUrl('lang.base.proj.json')).then(function (response) {
                $rootScope.langs = {};
                angular.extend($rootScope.langBase, response.data);
                //console_log("基础语言包信息：$rootScope.langBase", $rootScope.langBase);
                
                $rootScope.langs = response.data;
                
                // 合并页面语言包
                $http.get(getLangUrl('admin/index.json')).then(function(response){
                    angular.extend($rootScope.langs, $rootScope.langBase, response.data);
                });
            });
            
        });
    }
    $scope.getLang();
    
    // 初始化页面参数
    $scope.need_safekey = 0; // 是否需要填写/修改安全码，默认不需要
    $scope.language = langBase; // 默认语言
    focus('name'); // 默认账号获得焦点
    
    // 接口调用失败显示失败信息的方法，$rootScope.modal_err
    $scope.handle_err = function(errCode) {
        $scope.login_message_class = 'text-danger';
        var err_lang_key = 'error_' + errCode;
        if (typeof($rootScope.langs[err_lang_key]) != 'undefined') {
            $rootScope.langs.login_message = $rootScope.langs[err_lang_key];
        }
        else {
            $rootScope.langs.login_message = $rootScope.langs.error_code + errCode;
        }
    }
    
    // 响应登录回车事件
    $scope.loginKeyup = function(e){
        var keycode = window.event?e.keyCode:e.which;
        if (keycode==13) {
            $scope.login();
        }
    };
    
    // 响应修改密码回车事件
    $scope.changeKeyup = function(e){
        var keycode = window.event?e.keyCode:e.which;
        if (keycode==13) {
            $scope.forcepwd();
        }
    }
    
    // 检查登录表单
    $scope.checkform = function () {
        
        if (typeof($scope.name) == 'undefined' || $scope.name == '') {
            focus('name');
            $rootScope.login_message_class = "text-danger";
            $rootScope.langs.login_message = $rootScope.langs.name_input;
            return false;
        }
        if (typeof($scope.passwd) == 'undefined' || $scope.passwd == '') {
            $rootScope.login_message_class = "text-danger";
            $rootScope.langs.login_message = $rootScope.langs.passwd_input;
            focus('passwd');
            return false;
        }
        if ($scope.need_safekey == 1 && (typeof($scope.safekey) == 'undefined' || $scope.safekey == '')) {
            $rootScope.login_message_class = "text-danger";
            $rootScope.langs.login_message = $rootScope.langs.safekey_input;
            focus('safekey');
            return false;
        }
        return true;
    }
    
    // 登录提交
    $scope.login = function () {
        if ($scope.checkform()) {
            $scope.loginfunc();
        }
    }
    
    // 执行登录请求
    $scope.loginfunc = function () {
        $http.post(getApiUrl('admin.login'), {platid:platid, loginname:$scope.name, loginpwd:$scope.passwd, safekey:$scope.safekey}).then(function (response) {
            if (response.data.r == 1) {
                window.location.href = "main.html";
            }
            else {
                $scope.handle_err(response.data.errCode);
                if (response.data.errCode == 100004) {
                    $scope.need_safekey = 1;
                    focus('safekey');
                }
                else if (response.data.errCode == 100003) { // 首次修改密码
                    $http.get(getLangUrl('admin/modpwd_self.json')).then(function(response){
                        angular.extend($rootScope.langs, $rootScope.langBase, response.data);
                        $scope.newpasswd = "";
                        $scope.vnewpasswd = "";
                        $scope.newsafekey = "";
                        $scope.vnewsafekey = "";
                        $('#mpwd_modal').on('shown.bs.modal', function() {
                            focus('newpasswd');
                        });
                        $('#mpwd_modal').modal('show');
                    });
                    
                }
            }
        });
    }
    
    // 强制修改密码
    $scope.forcepwd = function() {
        if (typeof($scope.newpasswd) == 'undefined' || $scope.newpasswd == '' || $scope.newpasswd == $scope.passwd) {
            focus('newpasswd');
            return false;
        }
        if (typeof($scope.vnewpasswd) == 'undefined' || $scope.vnewpasswd == '' || $scope.vnewpasswd != $scope.newpasswd) {
            focus('vnewpasswd');
            return false;
        }
        var data = {oldpwd:$scope.passwd, newpwd:$scope.newpasswd};
        
        if ($scope.need_safekey) {
            if (typeof($scope.newsafekey) == 'undefined' || $scope.newsafekey == '' || $scope.newsafekey == $scope.safekey) {
                focus('newsafekey');
                return false;
            }
            if (typeof($scope.vnewsafekey) == 'undefined' || $scope.vnewsafekey == '' || $scope.vnewsafekey != $scope.newsafekey) {
                focus('vnewsafekey');
                return false;
            }
            data = {oldpwd:$scope.passwd, newpwd:$scope.newpasswd, oldsk:$scope.safekey, newsk:$scope.newsafekey};
        }
        
        $http.post(getApiUrl('admin.modpwd_self'), data).then(function(response){
            $('#mpwd_modal').modal('hide');
            if (response.data.r == 0) {
                $scope.handle_err(response.data.errCode);
            } else {
                $scope.passwd = $scope.newpasswd;
                if ($scope.need_safekey) {
                    $scope.safekey = $scope.newsafekey;
                }
                $scope.loginfunc();
            }
        })
    }
    
    // 选择语言
    $scope.changeLang = function() {
        setLang($scope.language);
        $scope.getLang();
    }
});

