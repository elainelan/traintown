@echo off
set _pub_date=%DATE:~0,4%_%DATE:~5,2%_%DATE:~8,2%
set _pub_proj=api

set _put_time_h=%TIME:~0,1%
if "%_put_time_h%"== " " (
    set _put_time=0%TIME:~1,1%%TIME:~2,9%
) else (
    set _put_time=%TIME%
)
echo %_pub_date% %_put_time% > version_cmd.txt

if not exist .\publish mkdir .\publish
if exist .\publish\cmd rmdir /s/q .\publish\cmd
mkdir .\publish\cmd

@rem common复制
xcopy .\common .\publish\cmd\common\ /y/e/r/exclude:exclude.txt
@rem common其他清理
for /R .\publish\cmd\common /D %%i in (*gmd*) do rd /s /q %%i
for /R .\publish\cmd\common /D %%i in (*dmd*) do rd /s /q %%i

@rem conf复制，一般发布不需要
@rem xcopy .\conf\cmdconfig.php .\publish\cmd\conf\ /y/e/r/exclude:exclude.txt

@rem do复制
xcopy .\do\cmd .\publish\cmd\do\cmd\ /y/e/r/exclude:exclude.txt

@rem 删除测试文件
del /q .\publish\cmd\do\cmd\test.php

@rem lang复制
cd lang
for /D %%i in (*cmd*) do xcopy ..\lang\%%i ..\publish\cmd\lang\%%i\ /y/e/r/exclude:..\exclude.txt
cd ..

@rem 根目录文件复制
xcopy apic.php .\publish\cmd\

@rem 版号号复制
xcopy version_cmd.txt .\publish\cmd\


cd .\publish\cmd
del /q ..\%_pub_proj%_cn_cmd_%_pub_date%_all.zip
"C:\Program Files\WinRAR\WinRAR.exe" a -r ..\%_pub_proj%_cn_cmd_%_pub_date%_all.zip *.*

echo *
echo *
echo =============================
echo 请复制对应的Center配置文件cmdconfig.php 到 conf\目录下
echo =============================
echo *
echo *

pause
