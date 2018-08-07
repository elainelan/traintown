app.controller("admin.modpwd_self.ctrl", function ($scope, $rootScope, $http) {
    $rootScope.breadcrumb();   // 生成面包屑导航
    $rootScope.setLang('admin/modpwd_self.json'); //设置语言包
    
    
    // 监听语言包加载完成后，执行表单验证初始化，同时关闭监听
    $scope.unWatch = $scope.$watch('langs.oldpwd_invalid', function(){
        if ($rootScope.langs.oldpwd_invalid) {
            $scope.unWatch();
            $scope.form_valid_init();
        }
    });
    
    // 修改状态数组
    $scope.form_changed = {
        "password":0, // 密码是否修改过
        "safekey":0   // 安全码是否修改过
    };
    
    var form_id = 'myForm'; // 表单ID
    
    // 表单验证初始化
    $scope.form_valid_init = function() {
        // 表单验证规则
        var fields = 
        {
            oldpwd: {
                enabled: false,
                message: $rootScope.langs.oldpwd_invalid,
                validators: {
                    stringLength: {
                        message: $rootScope.langs.pwd_stringLength,
                        min: 8,
                        max: 12
                    },
                    regexp: {
                        regexp: $rootScope.regexp.password,
                        message: $rootScope.langs.pwd_regexp
                    },
                    notEmpty: {
                        message: $rootScope.langs.oldpwd_not_empty
                    }
                }
            },
            newpwd: {
                enabled: false,
                message: $rootScope.langs.newpwd_invalid,
                validators: {
                    stringLength: {
                        message: $rootScope.langs.pwd_stringLength,
                        min: 8,
                        max: 12
                    },
                    regexp: {
                        regexp: $rootScope.regexp.password,
                        message: $rootScope.langs.pwd_regexp
                    },
                    different: {
                        field: 'oldpwd',
                        message: $rootScope.langs.newpwd_not_same
                    },
                    notEmpty: {
                        message: $rootScope.langs.newpwd_not_empty
                    }
                }
            },
            vpwd: {
                enabled: false,
                message: $rootScope.langs.vpwd_invalid,
                validators: {
                    identical: {
                        field: 'newpwd',
                        message: $rootScope.langs.vpwd_invalid
                    },
                    notEmpty: {
                        message: $rootScope.langs.vpwd_not_empty
                    }
                }
            },
            oldsk: {
                enabled: false,
                message: $rootScope.langs.oldsk_invalid,
                validators: {
                    regexp: {
                        regexp: $rootScope.regexp.safekey,
                        message: $rootScope.langs.safekey_regexp
                    },
                    stringLength: {
                        message: $rootScope.langs.safekey_stringLength,
                        min: 4,
                        max: 6
                    },
                    notEmpty: {
                        message: $rootScope.langs.oldsk_not_empty
                    }
                }
            },
            newsk: {
                enabled: false,
                message: $rootScope.langs.newsk_invalid,
                validators: {
                    stringLength: {
                        message: $rootScope.langs.safekey_stringLength,
                        min: 4,
                        max: 6
                    },
                    regexp: {
                        regexp: $rootScope.regexp.safekey,
                        message: $rootScope.langs.safekey_regexp
                    },
                    different: {
                        field: 'oldsk',
                        message: $rootScope.langs.newsk_not_same
                    },
                    notEmpty: {
                        message: $rootScope.langs.newsk_not_empty
                    }
                }
            },
            vsk: {
                enabled: false,
                message: $rootScope.langs.vsk_invalid,
                validators: {
                    identical: {
                        field: 'newsk',
                        message: $rootScope.langs.vsk_invalid
                    },
                    notEmpty: {
                        message: $rootScope.langs.vsk_not_empty
                    }
                }
            }
        };
        
        // 初始化时更新配置，只有disabled属性的不做验证，隐藏元素不排除，通过validatos的enable/disable控制是否验证
        // 如果不使用这个方法，默认hidden也会排除验证，第一次ng-view的时候，是没有办法做验证的。只有ng-view完成以后，才可以做验证
        $rootScope.form_bootstrapValidator_init(form_id, fields, ':disabled');
        
        // 初始化时关闭“提交”按钮
        $('#'+form_id).bootstrapValidator('disableSubmitButtons', true);
        
    }
    
    // 输入原密码时
    $scope.change_password = function() {
        if ($scope.form_changed.password == 0) {
            // 设置状态
            $scope.form_changed.password = 1;
            // 开启验证
            $('#'+form_id)
            .bootstrapValidator('enableFieldValidators', 'oldpwd')
            .bootstrapValidator('enableFieldValidators', 'newpwd')
            .bootstrapValidator('enableFieldValidators', 'vpwd');
        }
        // 重新验证
        $('#'+form_id)
        .bootstrapValidator('revalidateField', 'oldpwd')
        .bootstrapValidator('revalidateField', 'newpwd')
        .bootstrapValidator('revalidateField', 'vpwd');
    }
    
    // 输入原安全码时
    $scope.change_safekey = function() {
        if ($scope.form_changed.safekey == 0) {
            // 设置状态
            $scope.form_changed.safekey = 1;
            // 开启验证
            $('#'+form_id)
            .bootstrapValidator('enableFieldValidators', 'oldsk')
            .bootstrapValidator('enableFieldValidators', 'newsk')
            .bootstrapValidator('enableFieldValidators', 'vsk');
        }
        // 重新验证
        $('#'+form_id)
        .bootstrapValidator('revalidateField', 'oldsk')
        .bootstrapValidator('revalidateField', 'newsk')
        .bootstrapValidator('revalidateField', 'vsk');
    }
    
    // 提交请求
    $scope.submit_form = function () {
        $('#'+form_id).data('bootstrapValidator').validate();
        if ($('#'+form_id).data('bootstrapValidator').isValid()) {
            var data = {oldpwd: $scope.oldpwd, newpwd: $scope.newpwd, oldsk: $scope.oldsk, newsk: $scope.newsk};
            $http.post(getApiUrl('admin.modpwd_self'), data).then(function (response) {
                if (response.data.r == 1) {
                    $rootScope.modal_succ($rootScope.langs['mod_admin_user_pwd_success'], function(){
                        window.location.reload();
                    });
                } else {
                    console.log(response.data);
                    $rootScope.modal_err(response.data.errCode);
                }
            });
        }
        return false;
    }
    
});
