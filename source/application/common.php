<?php

// 应用公共函数库文件

use think\Request;
use think\Log;

/**
 * 打印调试函数
 * @param $content
 * @param $is_die
 */
function pre($content, $is_die = true)
{
    header('Content-type: text/html; charset=utf-8');
    echo '<pre>' . print_r($content, true);
    $is_die && die();
}

/**
 * 驼峰命名转下划线命名
 * @param $str
 * @return string
 */
function toUnderScore($str)
{
    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
        return '_' . strtolower($matchs[0]);
    }, $str);
    return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
}

/**
 * 生成密码hash值
 * @param $password
 * @return string
 */
function yoshop_hash($password)
{
    return md5(md5($password) . 'yoshop_salt_SmTRx');
}

/**
 * 获取当前域名及根路径
 * @return string
 */
function base_url()
{
    static $baseUrl = '';
    if (empty($baseUrl)) {
        $request = Request::instance();
        $subDir = str_replace('\\', '/', dirname($request->server('PHP_SELF')));
        $baseUrl = $request->scheme() . '://' . $request->host() . $subDir . ($subDir === '/' ? '' : '/');
    }
    return $baseUrl;
}

/**
 * 写入日志 (废弃)
 * @param string|array $values
 * @param string $dir
 * @return bool|int
 */
function write_log($values, $dir)
{
    if (is_array($values))
        $values = print_r($values, true);
    // 日志内容
    $content = '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $values . PHP_EOL . PHP_EOL;
    try {
        // 文件路径
        $filePath = $dir . '/logs/';
        // 路径不存在则创建
        !is_dir($filePath) && mkdir($filePath, 0755, true);
        // 写入文件
        return file_put_contents($filePath . date('Ymd') . '.log', $content, FILE_APPEND);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * 写入日志 (使用tp自带驱动记录到runtime目录中)
 * @param $value
 * @param string $type
 */
function log_write($value, $type = 'yoshop-info')
{
    $msg = is_string($value) ? $value : var_export($value, true);
    Log::record($msg, $type);
}

/**
 * curl请求指定url (get)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curl($url, $data = [])
{
    // 处理get数据
    if (!empty($data)) {
        $url = $url . '?' . http_build_query($data);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

/**
 * curl请求指定url (post)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curlPost($url, $data = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

if (!function_exists('array_column')) {
    /**
     * array_column 兼容低版本php
     * (PHP < 5.5.0)
     * @param $array
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    function array_column($array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {
            if (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 多维数组合并
 * @param $array1
 * @param $array2
 * @return array
 */
function array_merge_multiple($array1, $array2)
{
    $merge = $array1 + $array2;
    $data = [];
    foreach ($merge as $key => $val) {
        if (
            isset($array1[$key])
            && is_array($array1[$key])
            && isset($array2[$key])
            && is_array($array2[$key])
        ) {
            $data[$key] = array_merge_multiple($array1[$key], $array2[$key]);
        } else {
            $data[$key] = isset($array2[$key]) ? $array2[$key] : $array1[$key];
        }
    }
    return $data;
}

/**
 * 二维数组排序
 * @param $arr
 * @param $keys
 * @param bool $desc
 * @return mixed
 */
function array_sort($arr, $keys, $desc = false)
{
    $key_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $key_value[$k] = $v[$keys];
    }
    if ($desc) {
        arsort($key_value);
    } else {
        asort($key_value);
    }
    reset($key_value);
    foreach ($key_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * 数据导出到excel(csv文件)
 * @param $fileName
 * @param array $tileArray
 * @param array $dataArray
 */
function export_excel($fileName, $tileArray = [], $dataArray = [])
{
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 0);
    ob_end_clean();
    ob_start();
    header("Content-Type: text/csv");
    header("Content-Disposition:filename=" . $fileName);
    $fp = fopen('php://output', 'w');
    fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));// 转码 防止乱码(比如微信昵称)
    fputcsv($fp, $tileArray);
    $index = 0;
    foreach ($dataArray as $item) {
        if ($index == 1000) {
            $index = 0;
            ob_flush();
            flush();
        }
        $index++;
        fputcsv($fp, $item);
    }
    ob_flush();
    flush();
    ob_end_clean();
}

/**
 * 数据导出到excel(csv文件)
 * @param $fileName
 * @param array $tileArray
 * @param array $dataArray
 */
function export_excel2($fileName, $tileArray = [], $data = [])
{
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 0);
    ob_end_clean();
    ob_start();
    header("Content-Type: text/csv");
    header("Content-Disposition:filename=" . $fileName);
    $fp = fopen('php://output', 'w');
    fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));// 转码 防止乱码(比如微信昵称)
    fputcsv($fp, $tileArray);
    $index = 0;
    foreach($data as $dataArray){
        foreach ($dataArray as $item) {
            if ($index == 1000) {
                $index = 0;
                ob_flush();
                flush();
            }
            $index++;
            fputcsv($fp, $item);
        }
    }
    ob_flush();
    flush();
    ob_end_clean();
}

/**
 * 隐藏敏感字符
 * @param $value
 * @return string
 */
function substr_cut($value)
{
    $strlen = mb_strlen($value, 'utf-8');
    if ($strlen <= 1) return $value;
    $firstStr = mb_substr($value, 0, 1, 'utf-8');
    $lastStr = mb_substr($value, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', $strlen - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}

/**
 * 获取当前系统版本号
 * @return mixed|null
 * @throws Exception
 */
function get_version()
{
    static $version = null;
    if ($version) {
        return $version;
    }
    $file = dirname(ROOT_PATH) . '/version.json';
    if (!file_exists($file)) {
        throw new Exception('version.json not found');
    }
    $version = json_decode(file_get_contents($file), true);
    if (!is_array($version)) {
        throw new Exception('version cannot be decoded');
    }
    return $version['version'];
}

/**
 * 获取全局唯一标识符
 * @param bool $trim
 * @return string
 */
function getGuidV4($trim = true)
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        $charid = com_create_guid();
        return $trim == true ? trim($charid, '{}') : $charid;
    }
    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    // Fallback (PHP 4.2+)
    mt_srand((double)microtime() * 10000);
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace .
        substr($charid, 0, 8) . $hyphen .
        substr($charid, 8, 4) . $hyphen .
        substr($charid, 12, 4) . $hyphen .
        substr($charid, 16, 4) . $hyphen .
        substr($charid, 20, 12) .
        $rbrace;
    return $guidv4;
}

/**
 * 时间戳转换日期
 * @param $timeStamp
 * @return false|string
 */
function format_time($timeStamp)
{
    return date('Y-m-d H:i:s', $timeStamp);
}

/**
 * 过滤字符串
 * @param $str
 * @return string
 */
function str_filter($str){
    return addslashes(strip_tags(trim($str)));
}

/**
 * 过滤搜索字段
 * @param $str
 * @return string|string[]
 */
function search_filter($str){
    $str = str_filter($str);
    $str = str_replace('*','',$str);
    $str = str_replace('_','',$str);
    $str = str_replace('%','',$str);
    return $str;
}

function get_last_day_start_timestamp(){
    return strtotime(date('Y-m-d 00:00:00',strtotime('-1 day')));
}

function get_last_day_end_timestamp(){
    return strtotime(date('Y-m-d 23:59:59',strtotime('-1 day')));
}

/**
 * 获取今天开始的时间戳
 * @return false|int
 */
function get_day_start_timestamp(){
    return mktime(0,0,0,date('m'),date('d'),date('Y'));
}

/**
 * 获取今天结束的时间戳
 * @return false|int
 */
function get_day_end_timestamp(){
    return mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
}

/**
 * 获取本月开始的时间戳
 * @return false|int
 */
function get_month_start_timestamp(){
    return strtotime(date('Y-m-01 00:00:01',time()));
}

/**
 * 获取上月开始的时间戳
 * @return false|string
 */
function get_last_month_start_timestamp(){
    return strtotime(date('Y-m-01 00:00:00',strtotime('-1 month')));
}

/**
 * 获取本月结束的时间戳
 * @return false|string
 */
function get_month_end_timestamp(){
    return mktime (23,59,59, date ( "m" ), date ( "t" ), date ( "Y" ) );
}

/**
 * 获取上月结束时间戳
 * @return false|string
 */
function get_last_month_end_timestamp(){
    return strtotime(date("Y-m-d 23:59:59", strtotime(-date('d').'day')));
}

/**
 * 创建邀请码
 * @param $user_id
 * @return string
 */
function createCode($user_id) {

    static $source_string = '6W3U4PV7SZ9YJ52AK1MTDGFOHNB8ELRICQX';

    $num = $user_id;

    $code = '';

    while ( $num > 0) {

        $mod = $num % 35;

        $num = ($num - $mod) / 35;

        $code = $source_string[$mod].$code;

    }

    if(empty($code[3]))

        $code = str_pad($code,4,'0',STR_PAD_LEFT);

    return $code;

}

/**
 * 解码邀请码
 * @param $code
 * @return float|int
 */
function decode($code) {

    static $source_string = '6W3U4PV7SZ9YJ52AK1MTDGFOHNB8ELRICQX';

    if (strrpos($code, '0') !== false)

        $code = substr($code, strrpos($code, '0')+1);

    $len = strlen($code);

    $code = strrev($code);

    $num = 0;

    for ($i=0; $i < $len; $i++) {

        $num += strpos($source_string, $code[$i]) * pow(35, $i);

    }

    return $num;

}

/**
 * 过滤通配符
 * @param $str
 * @return string|string[]
 */
function keywords_filter($str){
    $str = str_filter($str);
    $str = str_replace('*','',$str);
    $str = str_replace('%','',$str);
    $str = str_replace('_','',$str);
    return $str;
}

/**
 * 手机号****
 * @param $mobile
 * @return string|string[]
 */
function mobile_hide($mobile){
    return substr_replace($mobile,'****',3,4);
}

/**
 * 分类树
 * @param $array
 * @param int $pid
 * @param int $level
 * @return array
 */
function getTree($array, $pid =0, $level = 0){

    $f_name=__FUNCTION__; // 定义当前函数名

    //声明静态数组,避免递归调用时,多次声明导致数组覆盖
    static $list = [];

    foreach ($array as $key => $value){
        //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
        if ($value['pid'] == $pid){
            //父节点为根节点的节点,级别为0，也就是第一级
            $flg = str_repeat('|--',$level);
            // 更新 名称值
            $value['title'] = $flg.$value['title'];
            // 输出 名称
            //把数组放到list中
            $list[] = $value;
            //把这个节点从数组中移除,减少后续递归消耗
            unset($array[$key]);
            //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
            $f_name($array, $value['lesson_cate_id'], $level+1);
        }
    }
    return $list;
}

/**
 * 无限级分类树
 * @param $arr
 * @param int $pid
 * @return array
 */
function cateTree($arr, $pid=0, $field='id'){
    $list = [];
    foreach($arr as $key => $item){
        if($item['pid'] == $pid){
            $list[$item[$field]] = $item;
            unset($arr[$key]);
            $list[$item[$field]]['child'] = cateTree($arr, $item[$field], $field);
        }
    }
    return $list;
}

/**
 * 无限级分类树
 * @param $arr
 * @param int $pid
 * @return array
 */
function memberTree($arr, $pid=0, $field='id'){
    $list = [];
    foreach($arr as $key => $item){
        if($item['invitation_user_id'] == $pid){
            $item['label'] = $item['nickName'];
            $list[$item[$field]] = $item;
            unset($arr[$key]);
            $list[$item[$field]]['children'] = memberTree($arr, $item[$field], $field);
        }
    }
    return $list;
}

/**
 * 数组多条件排序
 * @return mixed|null
 * @throws Exception
 */
function sortArrByManyField(){

    $args = func_get_args(); // 获取函数的参数的数组

    if(empty($args)){

        return null;

    }

    $arr = array_shift($args);

    if(!is_array($arr)){

        throw new Exception("第一个参数不为数组");

    }

    foreach($args as $key => $field){

        if(is_string($field)){

            $temp = array();

            foreach($arr as $index=> $val){

                $temp[$index] = $val[$field];

            }

            $args[$key] = $temp;

        }

    }

    $args[] = &$arr;//引用值

    call_user_func_array('array_multisort',$args);

    return array_pop($args);

}

function getMillisecond() {
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

