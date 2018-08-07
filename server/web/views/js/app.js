

// 初始化
var app = angular.module("myApp", [
                                   "ui.router",
                                   "oc.lazyLoad",
                                   "ngTouch",
                                   'ui.grid', 'ui.grid.selection', 'ui.grid.edit', 'ui.grid.exporter', 'ui.grid.pagination', 'ui.grid.resizeColumns', 'ui.grid.autoResize', 'ui.grid.moveColumns', 'ui.grid.pinning'
                                   ], function ($httpProvider) {
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

//获取焦点 service
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

// ng-repeate完成后扩展指令
// <div ng-repeat="i in arr" repeat-finish="repeatDone();">
app.directive('repeatFinish',function(){
    return {
        link: function(scope,element,attr){
            //console_log('ng-repeat(index)', scope.$index);
            if(scope.$last == true){
                scope.$eval( attr.repeatFinish );
            }
        }
    }
});


// 过滤器：路径生成（.转换成/）
app.filter('topath', function () {
    return function (input) {
        return input.replace(/\./, '/');
    }
});
// 过滤器：id生成（.转换成_）
app.filter('to_id', function () {
    return function (input) {
        return input.replace(/\./, '_');
    }
});
// 过滤器：逗号分隔a1,a2转换成[a1]a2
app.filter('diantokuo',function () {
    return function (input) {
        $arr = input.split(',');
        return '['+$arr[0]+']'+$arr[1];
    }
});
// 过滤器：是否是保留(id,name,baoliu)
app.filter('baoliu', function () {
    return function (input) {
        $arr = input.split(',');
        return $arr[2];
    }
});

//分类加载控制
app.config(["$provide", "$compileProvider", "$controllerProvider", "$filterProvider",
    function ($provide, $compileProvider, $controllerProvider, $filterProvider) {
        app.controller = $controllerProvider.register;
        app.directive = $compileProvider.directive;
        app.filter = $filterProvider.register;
        app.factory = $provide.factory;
        app.service = $provide.service;
        app.constant = $provide.constant;
    }
]);

// 支持跨域发送cookie
if (cross_domain == true) {
    app.config(function ($httpProvider) {
        $httpProvider.defaults.withCredentials = true;
    });
}