@echo off
set _pub_date=%DATE:~0,4%_%DATE:~5,2%_%DATE:~8,2%
set _pub_proj=api

set _put_time_h=%TIME:~0,1%
if "%_put_time_h%"== " " (
    set _put_time=0%TIME:~1,1%%TIME:~2,9%
) else (
    set _put_time=%TIME%
)

echo %_pub_date% %_put_time% > version_gmd.txt

if not exist .\publish mkdir .\publish
if exist .\publish\gmd rmdir /s/q .\publish\gmd
mkdir .\publish\gmd

@rem common复制
xcopy .\common .\publish\gmd\common\ /y/e/r/exclude:exclude.txt
@rem common其他清理
for /R .\publish\gmd\common /D %%i in (*cmd*) do rd /s /q %%i
for /R .\publish\gmd\common /D %%i in (*dmd*) do rd /s /q %%i

@rem conf复制，一般发布不需要
@rem xcopy .\conf\gmdconfig.php .\publish\gmd\conf\ /y/e/r/exclude:exclude.txt

@rem do复制
xcopy .\do\gmd .\publish\gmd\do\gmd\ /y/e/r/exclude:exclude.txt

@rem 删除测试文件
del /q .\publish\gmd\do\gmd\test.php

@rem lang复制
cd lang
for /D %%i in (*gmd*) do xcopy ..\lang\%%i ..\publish\gmd\lang\%%i\ /y/e/r/exclude:..\exclude.txt
cd ..

@rem 根目录文件复制
xcopy apig.php .\publish\gmd\

@rem 版号号复制
xcopy version_gmd.txt .\publish\gmd\


cd .\publish\gmd
del /q ..\%_pub_proj%_cn_gmd_%_pub_date%_all.zip
"C:\Program Files\WinRAR\WinRAR.exe" a -r ..\%_pub_proj%_cn_gmd_%_pub_date%_all.zip *.*


echo *
echo *
echo =============================
echo 请复制对应的Game-Server配置文件gmdconfig.php 到 conf\目录下
echo !!!!!!
echo !!!!!! 台湾平台需要注意：看兑换比例是否正确！！！
echo !!!!!!! 检查一下gmdconfig.php的
echo !!!!!!!!! define('PAY_CHARGE_RATE', 1)
echo =============================
echo *
echo *

pause
