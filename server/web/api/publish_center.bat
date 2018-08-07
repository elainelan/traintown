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

@rem common����
xcopy .\common .\publish\cmd\common\ /y/e/r/exclude:exclude.txt
@rem common��������
for /R .\publish\cmd\common /D %%i in (*gmd*) do rd /s /q %%i
for /R .\publish\cmd\common /D %%i in (*dmd*) do rd /s /q %%i

@rem conf���ƣ�һ�㷢������Ҫ
@rem xcopy .\conf\cmdconfig.php .\publish\cmd\conf\ /y/e/r/exclude:exclude.txt

@rem do����
xcopy .\do\cmd .\publish\cmd\do\cmd\ /y/e/r/exclude:exclude.txt

@rem ɾ�������ļ�
del /q .\publish\cmd\do\cmd\test.php

@rem lang����
cd lang
for /D %%i in (*cmd*) do xcopy ..\lang\%%i ..\publish\cmd\lang\%%i\ /y/e/r/exclude:..\exclude.txt
cd ..

@rem ��Ŀ¼�ļ�����
xcopy apic.php .\publish\cmd\

@rem ��źŸ���
xcopy version_cmd.txt .\publish\cmd\


cd .\publish\cmd
del /q ..\%_pub_proj%_cn_cmd_%_pub_date%_all.zip
"C:\Program Files\WinRAR\WinRAR.exe" a -r ..\%_pub_proj%_cn_cmd_%_pub_date%_all.zip *.*

echo *
echo *
echo =============================
echo �븴�ƶ�Ӧ��Center�����ļ�cmdconfig.php �� conf\Ŀ¼��
echo =============================
echo *
echo *

pause
