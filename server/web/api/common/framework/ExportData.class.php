<?php
/**
 * 数据导出
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 数据导出类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class ExportData
{
    
    // 导出目录
    public static $folder = 'export/';
    
    // 包含导出目录的完整导出路径
    private static $path;
    
    // 是否已经添加了CSV文件标题头
    private static $addCsvHeader = 0;
    
    // CSV文件标题头
    private static $csvHeader;

    /**
     * 创建导出目录
     *
     * @return void
     */
    static private function _makeFolder()
    {
        if (!self::$path) {
            self::$path = CV_ROOT . self::$folder;
        }
        if (!is_dir(self::$path)) {
            if (!mkdir(self::$path)) {
                ResultParser::error(ErrorCode::EXPORT_FOLDER_CREATE_FAILED);
            }
        }
    }

    /**
     * 设置导出CSV文件的栏位数组
     *
     * @param array $csv_header
     *        栏位名称设置
     * @return void
     */
    static public function setCsvHeader($csv_header = array())
    {
        self::$csvHeader = $csv_header;
        self::$addCsvHeader = 0;
    }

    /**
     * 写入数据到CSV文件
     *
     * @param string $file
     *        导出文件名
     * @param array $data_ary
     *        导出数据数组
     * @param array $title_ary
     *        导出栏位语言数组
     * @return void
     */
    static public function addCsv($file, $data_ary, $title_ary = array())
    {
        if (!$data_ary) {
            return;
        }
        self::_makeFolder();
        
        // 打开CSV导出文件
        $file = self::$path . $file;
        $f_res = CVFile::getInstance($file);

        $content = '';
        // CSV标题头处理
        if (!self::$addCsvHeader) {
            if (!self::$csvHeader) {
                // 如果没有设置导出CSV的标题，尝试使用导出数据的数组KEY作为标题
                $csvHeader = array_keys($data_ary[0]);
                if ($title_ary) {
                    foreach ($csvHeader as & $csv_h) {
                        if ($title_ary[$csv_h]) {
                            $csv_h = $title_ary[$csv_h];
                        }
                    }
                }
                self::$csvHeader = $csvHeader;
            }
            $content .= implode(",", self::$csvHeader) . "\r\n";
            self::$addCsvHeader = 1;
        }
        
        // 数据格式化
        foreach ($data_ary as $data) {
            foreach ($data as &$v) {
                $v = '"' . str_replace('"', '""', $v) . '"';
            }
            $content .= implode(",", $data) . "\r\n";
        }
        
        // 添加BOM，避免乱码的问题出现
        $f_res->append(chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // 数据写入
        if ($f_res->append($content) === false) {
            $f_res->closed();
            ResultParser::error(ErrorCode::EXPORT_FILE_WRITE_FAILED);
        }
        
        // 关闭文件
        $f_res->closed();
    }

    /**
     * 压缩导出后的文件
     *
     * @param string $file
     *        导出的文件名
     * @return string|false 失败返回false，成功返回压缩后的文件名称
     */
    static public function zip($file)
    {
        if (file_exists($file) && exec('zip ')) { // 有文件并且有zip扩展
            exec("zip -j {$file}.zip $file");
            if (file_exists("{$file}.zip")) {
                return "{$file}.zip";
            }
        }
        return false;
    }

}


