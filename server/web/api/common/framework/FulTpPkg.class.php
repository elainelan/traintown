<?php
/**
 * 封装full_type_package数据解码与编码文件
 *
 * game的full_type_package数据解码编码操作
 *
 * @author dragonets
 * @version 20160129
 * @package common
 * @subpackage game
 */


/**
 * full_type_pkg数据编码解码类
 *
 * @author dragonets
 * @version 20160129
 * @package common
 * @subpackage game
 */
class FulTpPkg
{

    /**
     * full_type_pkg二进制数据起始偏移
     *
     * @var int
     */
    private $data_pos = 0;

    /**
     * 解码full_type_package数据
     *
     * 递归调用解码
     *
     * @todo PHP 左移32位有问题
     *      
     * @param string $ful_tp_pkg
     *        full_type_package二进制数据
     * @param array $decode_data
     *        存放解码数据的数组内存地址(指针)
     * @param array $decode_desc
     *        存放编码数据所需信息的数组内存地址(指针)
     * @return void
     */
    public function decode($ful_tp_pkg, &$decode_data, &$decode_desc)
    {
        // 位置变量超过二进制数据最大长度（解码完毕），或者二进制数据是空的，返回
        if ($this->data_pos == 0 && strlen($ful_tp_pkg) > 0) {
            $gzflag = unpack('C', substr($ful_tp_pkg, 0, 1));
            $gzflag = $gzflag[1];
            if ($gzflag == 0xff) {
                // 经过了gzip压缩数据，先解压数据
                $ful_tp_pkg = gzuncompress(substr($ful_tp_pkg, 1));
            }
        }
        if ($this->data_pos > strlen($ful_tp_pkg) || strlen($ful_tp_pkg) == 0) {
            return;
        }
        
        // 取数据header的flag, 先读取1byte(unsigned char)
        $unpack1 = unpack('C', substr($ful_tp_pkg, $this->data_pos));
        $mark_1 = $unpack1[1];
        $this->data_pos += 1;
        
        // 高位数据低位存储，低位数据高位存储 
        // |header(1-3byte)|value|
        $flag = $mark_1 & 0x0f; // 高4位数据
        

        switch ($flag) {
            case '0':
                // bool
                // |flag(4bits)|bvalue(1bit)|unused(3bit)|
                // saved_header: 000(unused)1(bvalue) 0000(flag)
                $decode_data = (bool)(($mark_1 & 0x10) >> 4);
                break;
            case '1':
                // INT值
                // |flag(4bits)|issigned(1bit)|numsize(3bits)|value(1/2/4/8 bytes)|
                // saved_header: 000(numsize)1(issigned) 0001(flag)
                $is_signed = $mark_1 & 0x10; // 有无符号
                $num_size = ($mark_1 >> 5); // INT类型
                

                if ($is_signed) {
                    switch ($num_size) {
                        case 2: // 4 bytes signed int
                            $r_res = unpack('l', substr($ful_tp_pkg, $this->data_pos));
                            $decode_data = $r_res[1];
                            $this->data_pos += 4;
                            break;
                        case 1: // 2 bytes signed int
                            $r_res = unpack('s', substr($ful_tp_pkg, $this->data_pos));
                            $decode_data = $r_res[1];
                            $this->data_pos += 2;
                            break;
                        case 0: // 1 byte signed int
                            $r_res = unpack('c', substr($ful_tp_pkg, $this->data_pos));
                            $decode_data = $r_res[1];
                            $this->data_pos += 1;
                            
                            break;
                        case 3: // 8 bytes signed int
                            // TODO: PHP 左移32位有问题
                            $this->data_pos += 8;
                            $decode_data = 0; // 数据暂时为0
                            

                            //$r_res = unpack('V', substr($ful_tp_pkg, $this->data_pos));
                            //$r = $r_res[1];
                            //$this->data_pos += 4;
                            

                            //$r_res = unpack('l', substr($ful_tp_pkg, $this->data_pos));
                            //$r2 = $r_res[1];
                            //$this->data_pos += 4;
                            

                            //$r = ($r2 << 32) | $r1 ;
                            //echo 'r:'.$r;
                            break;
                        default: // error
                            break;
                    }
                }
                else { // 无符号INT
                    switch ($num_size) {
                        case 2: // 4 bytes unsigned int
                            $r_res = unpack('V', substr($ful_tp_pkg, $this->data_pos));
                            $decode_data = $r_res[1];
                            $this->data_pos += 4;
                            break;
                        case 1: // 2 bytes unsigned int
                            $r_res = unpack('v', substr($ful_tp_pkg, $this->data_pos));
                            $decode_data = $r_res[1];
                            $this->data_pos += 2;
                            break;
                        case 0: // 1 byte unsigned int
                            $r_res = unpack('C', substr($ful_tp_pkg, $this->data_pos));
                            $decode_data = $r_res[1];
                            $this->data_pos += 1;
                            
                            break;
                        case 3: // 8 bytes int
                            // TODO: PHP 左移32位有问题
                            

                            $this->data_pos += 8;
                            $decode_data = 0;
                            
                            //$r_res = unpack('V', substr($ful_tp_pkg, $this->data_pos));
                            //$r1 = $r_res[1];
                            //$this->data_pos += 4;
                            

                            //$r_res = unpack('V', substr($ful_tp_pkg, $this->data_pos));
                            //$r2 = $r_res[1];
                            //$this->data_pos += 4;
                            

                            //$r = ($r2 << 32) | $r1 ;
                            //echo 'r:'.$r;
                            break;
                        default: // error
                            break;
                    }
                }
                break;
            
            case '2':
                // float值
                // |flag(4bits)|issigned(1bit)|numsize(3bits)|value(4/8 bytes)|
                // 符号包含在value里存储
                // saved_header: 000(numsize)1(issigned) 0010(flag)
                $num_size = ($mark_1 >> 5);
                switch ($num_size) {
                    case 2: // 4 bytes float
                        $r_res = unpack('f', substr($ful_tp_pkg, $this->data_pos));
                        $decode_data = $r_res[1];
                        $this->data_pos += 4;
                        break;
                    case 3: // 8 bytes float
                        $r_res = unpack('d', substr($ful_tp_pkg, $this->data_pos));
                        $decode_data = $r_res[1];
                        $this->data_pos += 8;
                        break;
                    default: // error
                        break;
                }
                break;
            
            case '3':
                // string字符串值
                // |flag(4bits)|length(28bits)|value(length bytes)|
                // 字符串长度length包含结尾字符’\0’在内
                // saved_header: 0001(4bit length) 0011(flag) 00000000...(24bit length)
                $mark_2_res = unpack('C', substr($ful_tp_pkg, $this->data_pos)); // read mark_2
                $mark_2 = $mark_2_res[1];
                $this->data_pos += 1;
                
                $mark_3_res = unpack('v', substr($ful_tp_pkg, $this->data_pos)); // read mark_3
                $mark_3 = $mark_3_res[1];
                $this->data_pos += 2;
                
                $length = ($mark_3 << 16) | ($mark_2 << 4) | ($mark_1 >> 4);
                $decode_data = substr($ful_tp_pkg, $this->data_pos, $length);
                // 除去字符串后面的\0
                $decode_data = str_replace("\0", '', $decode_data);
                $decode_desc['flag'] = 3;
                $this->data_pos += $length;
                break;
            
            case '4':
                // table 键值是string的table(key:value)
                // |flag(4bits)|length(28bits)|value(length (key:ft_nodes))|
                // saved_header: 0001(4bit length) 0100(flag) 00000000...(24bit length)
                $mark_2_res = unpack('C', substr($ful_tp_pkg, $this->data_pos)); // read mark_2
                $mark_2 = $mark_2_res[1];
                $this->data_pos += 1;
                
                $mark_3_res = unpack('v', substr($ful_tp_pkg, $this->data_pos)); // read mark_3
                $mark_3 = $mark_3_res[1];
                $this->data_pos += 2;
                $length = ($mark_3 << 16) | ($mark_2 << 4) | ($mark_1 >> 4);
                
                $decode_data = array();
                
                for ($i = 0; $i < $length; ++$i) {
                    $key_len_res = unpack('C', substr($ful_tp_pkg, $this->data_pos));
                    $key_len = $key_len_res[1];
                    $this->data_pos += 1;
                    
                    $key = substr($ful_tp_pkg, $this->data_pos, $key_len);
                    $this->data_pos += $key_len;
                    // value
                    self::decode($ful_tp_pkg, $decode_data[$key], $decode_desc[$key]);
                    if (is_null($decode_desc[$key])) {
                        unset($decode_desc[$key]);
                    }
                }
                // 记录该array的类型，为编码记录存储结构信息
                $decode_desc['flag'] = 4;
                break;
            
            case '5':
                // array
                // |flag(4bits)|length(28bits)|value(length ft_nodes)|
                // saved_header: 0001(4bit length) 0101(flag) 00000000...(24bit length)
                $mark_2_res = unpack('C', substr($ful_tp_pkg, $this->data_pos)); // read mark_2
                $mark_2 = $mark_2_res[1];
                $this->data_pos += 1;
                $mark_3_res = unpack('v', substr($ful_tp_pkg, $this->data_pos)); // read mark_3
                $mark_3 = $mark_3_res[1];
                $this->data_pos += 2;
                
                $length = ($mark_3 << 16) | ($mark_2 << 4) | ($mark_1 >> 4);
                $decode_data = array();
                
                for ($i = 0; $i < $length; ++$i) {
                    self::decode($ful_tp_pkg, $decode_data[$i], $decode_desc[$i]);
                    if (is_null($decode_desc[$i])) {
                        unset($decode_desc[$i]);
                    }
                }
                $decode_desc['flag'] = 5;
                break;
            
            case '6':
                // 内存块值
                // |flag(4bits)|length(28bits)|value(length bytes)|
                // saved_header: 0001(4bit length) 0110(flag) 00000000...(24bit length)
                $mark_2_res = unpack('C', substr($ful_tp_pkg, $this->data_pos)); // read mark_2
                $mark_2 = $mark_2_res[1];
                $this->data_pos += 1;
                
                $mark_3_res = unpack('v', substr($ful_tp_pkg, $this->data_pos)); // read mark_3
                $mark_3 = $mark_3_res[1];
                $this->data_pos += 2;
                
                $length = ($mark_3 << 16) | ($mark_2 << 4) | ($mark_1 >> 4);
                $decode_data = (string)(substr($ful_tp_pkg, $this->data_pos, $length));
                $this->data_pos += $length;
                $decode_desc['flag'] = 6;
                break;
            
            case '7':
                // table 键值是INT的table
                // |flag(4bits)|length(28bits)|value(length (key:ft_nodes))|
                // saved_header: 0001(4bit length) 0111(flag) 00000000...(24bit length)
                $mark_2_res = unpack('C', substr($ful_tp_pkg, $this->data_pos)); // read mark_2
                $mark_2 = $mark_2_res[1];
                $this->data_pos += 1;
                
                $mark_3_res = unpack('v', substr($ful_tp_pkg, $this->data_pos)); // read mark_3
                $mark_3 = $mark_3_res[1];
                $this->data_pos += 2;
                
                $length = ($mark_3 << 16) | ($mark_2 << 4) | ($mark_1 >> 4);
                
                $decode_data = array();
                
                for ($i = 0; $i < $length; ++$i) {
                    $intkey_res = unpack('l', substr($ful_tp_pkg, $this->data_pos));
                    $intkey = $intkey_res[1];
                    $this->data_pos += 4;
                    self::decode($ful_tp_pkg, $decode_data[$intkey], $decode_desc[$intkey]);
                    if (is_null($decode_desc[$intkey])) {
                        unset($decode_desc[$intkey]);
                    }
                }
                
                $decode_desc['flag'] = 7;
                break;
        }
    }

    /**
     * 编码full_type_package数据
     *
     * 递归调用编码
     *
     * @param mixed $encode_data
     *        (array|int|bool|string|float)需要编码的数据
     * @param array $encode_desc
     *        (array)数据结构数组
     * @param string $ful_tp_pkg
     *        (string)编码后的full_type_package数据
     * @return void
     */
    public function encode($encode_data, $encode_desc, &$ful_tp_pkg)
    {
        if (is_int($encode_data)) {
            // flag = 1
            // |flag(4bits)|issigned(1bit)|numsize(3bits)|value(1/2/4/8 bytes)|
            if ($encode_data >= 0) {
                // 数据>0 ，无符号INT
                // issigned = 0
                if ($encode_data <= 0xff) {
                    // 1 byte unsigned int
                    // numsize = 0
                    // header: 000(numsize)0(issigned) 0001(flag)
                    $ful_tp_pkg .= pack("C", 0x01) . pack("C", $encode_data);
                }
                elseif ($encode_data <= 0xffff) {
                    // 2 byte unsigned int
                    // numsize = 1
                    // header: 001(numsize)0(issigned) 0001(flag)
                    $ful_tp_pkg .= pack("C", 0x21) . pack("v", $encode_data);
                }
                elseif ($encode_data <= 0xffffffff) {
                    // 4 byte unsigned int
                    // numsize = 2
                    // header: 010(numsize)0(issigned) 0001(flag)
                    $ful_tp_pkg .= pack("C", 0x41) . pack("V", $encode_data);
                }
                // 8 byte PHP解析会出错
            }
            else {
                // 数据<0 ，有符号int
                // issigned = 1
                if ($encode_data >= -0x7f) {
                    // 1 byte signed int
                    // numsize = 0
                    // header: 000(numsize)1(signed) 0001(flag)
                    $ful_tp_pkg .= pack("C", 0x11) . pack("c", $encode_data);
                }
                elseif ($encode_data >= -0x7fff) {
                    // 2 byte signed int
                    // numsize = 1
                    // header: 001(numsize)1(signed) 0001(flag)
                    $ful_tp_pkg .= pack("C", 0x31) . pack("s", $encode_data);
                }
                elseif ($encode_data >= -0x7fffffff) {
                    // 4 byte signed int
                    // numsize = 2
                    // header: 010(numsize)1(signed) 0001(flag)
                    $ful_tp_pkg .= pack("C", 0x51) . pack("l", $encode_data);
                }
                // 8 byte PHP 解析会出错
            }
        }
        elseif (is_array($encode_data)) {
            // flag = 4,5,7
            // 新增数据(flag 4,5,7)处理方法：在desc_ary中添加的array的flag标记
            $length = count($encode_data);
            $flag = $encode_desc['flag'];
            
            // saved_header: 0001(4bit length) 0000(flag)  00000000...(24bit length)
            $m1 = $length & 0x0000000f; // 4 bit
            $m2 = ($length >> 4) & 0x000000ff; // 8 bit
            $m3 = ($length >> 16) & 0x0000ffff; // 16 bit
            

            switch ($flag) {
                case '4':
                    // 键值是string的table(key:value),键值最大255个字符
                    // |flag(4bits)|length(28bits)|value(length (key:ft_nodes))|
                    // |key_len(1byte)|key_str(key_len bytes)|ft_node|
                    // flag = 4(4bit) ,length = ?(28bit)
                    // saved_header: m1 0100(flag) m2 m3
                    $ful_tp_pkg .= pack("C", ($m1 << 4) | 0x04) . pack("C", $m2) . pack("v", $m3);
                    
                    foreach ($encode_data as $key => $val) {
                        // key_len(1byte),key_str(key_len bytes)
                        // key_len为键字符串长度(不包含结尾字符’/0’)
                        $ful_tp_pkg .= pack("C", strlen($key)) . $key;
                        self::encode($val, $encode_desc[$key], $ful_tp_pkg);
                    }
                    break;
                case '5':
                    // array
                    // |flag(4bits)|length(28bits)|value(length ft_nodes)|
                    // flag = 5(4bit)
                    // saved_header: m1 0101(flag) m2 m3
                    $ful_tp_pkg .= pack("C", ($m1 << 4) | 0x05) . pack("C", $m2) . pack("v", $m3);
                    
                    foreach ($encode_data as $key => $val) {
                        self::encode($val, $encode_desc[$key], $ful_tp_pkg);
                    }
                    break;
                case '7':
                    // table 键值是INT的table
                    // |flag(4bits)|length(28bits)|value(length (key:ft_nodes))|
                    // |key(4bytes)|ft_node|
                    // flag = 7
                    $ful_tp_pkg .= pack("C", ($m1 << 4) | 0x07) . pack("C", $m2) . pack("v", $m3);
                    
                    foreach ($encode_data as $key => $val) {
                        $ful_tp_pkg .= pack("l", $key);
                        self::encode($val, $encode_desc[$key], $ful_tp_pkg);
                    }
                    break;
            }
        }
        elseif (is_bool($encode_data)) {
            // flag = 0
            // |flag(4bits)|bvalue(1bit)|
            if ($encode_data == true) {
                // 1(value)000(unused) 0000(flag)
                $ful_tp_pkg .= pack("C", 0x10);
            }
            else {
                // 0(value)000(unused) 0000(flag)
                $ful_tp_pkg .= pack("C", 0x00);
            }
        }
        elseif (is_string($encode_data)) {
            // flag = 6(内存块值) flag = 3(字符串值)
            // |flag(4bits)|length(28bits)|value(length bytes)|
            // saved_header: length(4bit) | flag(4bit)
            $length = strlen($encode_data);
            $flag = $encode_desc['flag'];
            
            $m1 = $length & 0x0000000f; // 4 bit
            $m2 = ($length >> 4) & 0x000000ff; // 8 bit
            $m3 = ($length >> 16) & 0x0000ffff; // 16 bit
            

            if ($flag == 3) {
                $ful_tp_pkg .= pack("C", ($m1 << 4) | 0x03);
            }
            else {
                $ful_tp_pkg .= pack("C", ($m1 << 4) | 0x06);
            }
            $ful_tp_pkg .= pack("C", $m2) . pack("v", $m3) . $encode_data;
        }
        elseif (is_float($encode_data)) {
            // flag = 2
            // TODO: 如何区分单双精度浮点数？
            $ful_tp_pkg .= pack('f', $encode_data);
        }
    }
}

?>