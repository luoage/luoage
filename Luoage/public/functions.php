<?php
/**
 * 定义公用函数
 * @author luoage@msn.cn
 * @version $$version
 */
if (!defined('LUOAGE')) exit;

//获取用户IP的函数
function getIp() {
    $onlineip = '';
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $onlineip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $onlineip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    }
    return $onlineip;
}


function base64Encode($string,$data=''){
    $data = $data ? $data : c('base64table');
    $string = $string.'';
    $strLength = strlen($string);
    $i = 0;
    $base64 = '';

    while($i<$strLength){

        $a = ord($string[$i++]);

        $base64 .= $data[$a>>2];
        if($i === $strLength){
            $base64 .= $data[($a&0x3)<<4];
            break;
        }

        $b = ord($string[$i++]);
        $base64 .= $data[(($a&0x3)<<4)|($b>>4)];

        if($i === $strLength){
            $base64 .= $data[(($b&0x0f)<<2)];
            break;
        }

        $c = ord($string[$i++]);
        
        $base64 .= $data[(($b&0x0f)<<2)|($c>>6)];
        $base64 .= $data[$c&0x3f];

    }
    return $base64;

}

// 解码 
function base64Decode($string,$data=''){
    $data = $data ? $data : c('base64table');

    $dataArray = array();
    for($i=0,$j=strlen($data);$i<$j;$i++){
        $dataArray[$data[$i]] = $i;
    }
    $data = $dataArray;


    $strLength = strlen($string);
    $i = 0;
    $base = '';

    while($i<$strLength){

        $a = $data[$string[$i++]];
        if($i == $strLength) break;

        $b = $data[$string[$i++]];

        $base .= chr((($a&0x3f)<<2)|(($b&0x30)>>4));
        if($i == $strLength) break;

        $c = $data[$string[$i++]]&0xff;
        
        $base .= chr((($b&0xf)<<4)|(($c&0x3c)>>2));

        if($i == $strLength) break;

        $d = $data[$string[$i++]];

        $base .= chr( (($c&0x3)<<6)|($d&0x3f) );
    }
    return $base;
}

// 62进制数字转换成字符串
function band62Encode($num){
    $basic=c('band62table');
    $flag=1;
    $i=0;
    while($flag){
        $num=intval($num);
        $int=floor($num/62);//取得整数部分
        $mod[$i]=$num%62;//取得余数部分
        if($int==0){//当被除数为0时候结束
            $flag=0;
        }
        $num=$int;
        $i++;
    }
    $numarray=array_reverse($mod);//反转数组，因为余数是反过来的
    foreach($numarray as $k=>$v){
        $shortUrl[$k]=$basic[$v];//62位数字对应basic62个数据，转换数字为字母
    }
    return implode('', $shortUrl);
}
// 62进制字符串转换成数字@todo


?>