<?php

function strip_postfix($str, $postfix) {
    if (mb_strrpos($str, $postfix) > 0) {
        $str = mb_substr($str, 0, mb_strrpos($str, $postfix, "UTF-8"), "UTF-8");
    }
    return $str;
}

function strip_prefix($str, $prefix) {
    if (mb_strpos($str, $prefix) > 0) {
        $str = mb_substr($str, 0, mb_strpos($str, $prefix, "UTF-8"), "UTF-8");
    }
    return $str;
}

/**
 * 刪除字串的結尾到指定字串
 * strip_postfix_to("123-45", "-") -> 123
 * @param String $str
 * @param String $postfix_to
 * @return String
 */
function strip_postfix_to($str, $postfix_to) {
    $pos = strrpos($str, $postfix_to);
    if ($pos > 0) {
        $str = substr($str, 0, $pos);
    }
    return $str;
}

function strip_prefix_to($str, $to) {
    $pos = strpos($str, $to);
    if ($pos > 0) {
        $pos = $pos + strlen($to);
        $len = strlen($str);
        $str = substr($str, $pos, $len-$pos);
    }
    return $str;
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}