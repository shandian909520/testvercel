<?php
// 这是系统自动生成的公共文件

/**
 * 检查参数
 * @param string $type  类型
 * @param string $str   参数
 */
function checkParams($type, $str)
{
    $arr = explode(',', $str);
    foreach ($arr as  $item) {
        if (empty(input($type . '.' . $item))) {
            return true;
        }
    }
    return false;
}

function xml_to_arr($xml)
{
    $objectxml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA); //将文件转换成 对象
    $xmljson = json_encode($objectxml); //将对象转换个JSON
    $xmlarray = json_decode($xmljson, true); //将json转换成数组
    return $xmlarray;
}
