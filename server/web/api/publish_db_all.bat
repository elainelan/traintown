@echo off
set _pub_date=%DATE:~0,4%_%DATE:~5,2%_%DATE:~8,2%
set _pub_proj=api

set _put_time_h=%TIME:~0,1%
if "%_put_time_h%"== " " (
    set _put_time=0%TIME:~1,1%%TIME:~2,9%
) else (
    set _put_time=%TIME%
)

echo %_pub_date% %_put_time% > version_dmd.txt

if not exist .\publish mkdir .\publish
if exist .\publish\dmd rmdir /s/q .\publish\dmd
mkdir .\publish\dmd

@rem common复制
xcopy .\common .\publish\dmd\common\ /y/e/r/exclude:exclude.txt
@rem common其他清理
for /R .\publish\dmd\common /D %%i in (*cmd*) do rd /s /q %%i
for /R .\publish\dmd\common /D %%i in (*gmd*) do rd /s /q %%i

@rem conf复制，一般发布不需要
@rem xcopy .\conf\dmdconfig.php .\publish\dmd\conf\ /y/e/r/exclude:exclude.txt

@rem do复制
xcopy .\do\dmd .\publish\dmd\do\dmd\ /y/e/r/exclude:exclude.txt

@rem 删除测试文件
del /q .\publish\dmd\do\dmd\test.php

@rem lang复制
cd lang
for /D %%i in (*dmd*) do xcopy ..\lang\%%i ..\publish\dmd\lang\%%i\ /y/e/r/exclude:..\exclude.txt
cd ..

@rem 根目录文件复制
xcopy apid.php .\publish\dmd\

@rem 版号号复制
xcopy version_dmd.txt .\publish\dmd\


cd .\publish\dmd
del /q ..\%_pub_proj%_cn_dmd_%_pub_date%_all.zip
"C:\Program Files\WinRAR\WinRAR.exe" a -r ..\%_pub_proj%_cn_dmd_%_pub_date%_all.zip *.*


echo *
echo *
echo =============================
echo 请复制对应的配置文件dmdconfig.php 到 conf\目录下
echo !!!!!!
echo =============================
echo *
echo *

pause
