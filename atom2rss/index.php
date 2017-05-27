<?php

if (isset($_GET["atom"]) === FALSE) {
    exit();
}
$atom = $_GET["atom"];
// uncomment extension=php_xsl.dll on windows to activate it in your php.ini. Then restart your webserver to refresh php.
$chan = new DOMDocument(); $chan->load($atom); /* load channel */
$sheet = new DOMDocument(); $sheet->load('atom2rss.xsl'); /* use stylesheet from this page */
$processor = new XSLTProcessor();
$processor->registerPHPFunctions();
$processor->importStylesheet($sheet);
$result = $processor->transformToXML($chan); /* transform to XML string (there are other options - see PHP manual)  */
header("Content-Type: application/rss+xml; charset=utf-8");
echo $result;

// http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/atom2rss/?atom=https://www.reddit.com/r/googleplaydeals/.rss