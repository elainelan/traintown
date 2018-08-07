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

@rem common����
xcopy .\common .\publish\gmd\common\ /y/e/r/exclude:exclude.txt
@rem common��������
for /R .\publish\gmd\common /D %%i in (*cmd*) do rd /s /q %%i
for /R .\publish\gmd\common /D %%i in (*dmd*) do rd /s /q %%i

@rem conf���ƣ�һ�㷢������Ҫ
@rem xcopy .\conf\gmdconfig.php .\publish\gmd\conf\ /y/e/r/exclude:exclude.txt

@rem do����
xcopy .\do\gmd .\publish\gmd\do\gmd\ /y/e/r/exclude:exclude.txt

@rem ɾ�������ļ�
del /q .\publish\gmd\do\gmd\test.php

@rem lang����
cd lang
for /D %%i in (*gmd*) do xcopy ..\lang\%%i ..\publish\gmd\lang\%%i\ /y/e/r/exclude:..\exclude.txt
cd ..

@rem ��Ŀ¼�ļ�����
xcopy apig.php .\publish\gmd\

@rem ��źŸ���
xcopy version_gmd.txt .\publish\gmd\


cd .\publish\gmd
del /q ..\%_pub_proj%_cn_gmd_%_pub_date%_all.zip
"C:\Program Files\WinRAR\WinRAR.exe" a -r ..\%_pub_proj%_cn_gmd_%_pub_date%_all.zip *.*


echo *
echo *
echo =============================
echo �븴�ƶ�Ӧ��Game-Server�����ļ�gmdconfig.php �� conf\Ŀ¼��
echo !!!!!!
echo !!!!!! ̨��ƽ̨��Ҫע�⣺���һ������Ƿ���ȷ������
echo !!!!!!! ���һ��gmdconfig.php��
echo !!!!!!!!! define('PAY_CHARGE_RATE', 1)
echo =============================
echo *
echo *

pause
