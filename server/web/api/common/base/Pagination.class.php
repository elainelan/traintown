<?php
/**
 * 分页数据
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 分页数据类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class Pagination
{

    /**
     * 格式化输出数据
     *
     * @param int $page_current
     *        当前页码
     * @param int $page_total
     *        总计页码
     * @param int $items_per_page
     *        每页数据数
     * @param int $items_total
     *        数据总数
     * @param array $items
     *        需要显示的数据
     */
    public static function formatData($page_current, $page_total, $items_per_page, $items_total, $items)
    {
        if ($page_total == 0) {
            $page_current = 0;
        }
        $res = array(
            'page_current'  =>  $page_current,      // 当前页数
            'page_total'    =>  $page_total,        // 总页数
            'items_per_page'=>  $items_per_page,    // 每页数据量
            'items_total'   =>  $items_total,       // 总数据量
            'items'         =>  $items,             // 当前页数据
        );
        return $res;
    }

    /**
     * 分隔数据
     *
     * @param array $data
     *        所有数据
     * @param int $page_current
     *        需要第几页数据
     * @param int $items_per_page
     *        每页多少数据来分隔
     */
    public static function chunkData($data, $page_current, $items_per_page)
    {
        $items_total = count($data);
        $page_total = ceil($items_total / $items_per_page);
        
        if ($page_total == 1) {
            $page_current = 1;
            $items = $data;
        }
        else {
            if (empty($page_current)) {
                $page_current = 1;
            }
            else if ($page_current > $page_total) {
                $page_current = $page_total;
            }
            
            $chunk_data = array_chunk($data, $items_per_page);
            $chunk_data_idx = $page_current - 1;
            $items = isset($chunk_data[$chunk_data_idx]) ? $chunk_data[$chunk_data_idx] : null;
        }
        
        return self::formatData($page_current, $page_total, $items_per_page, $items_total, $items);
    }
}