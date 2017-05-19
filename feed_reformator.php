<?php
/*
//$string = "The text you want to filter goes here. http://google.com https://www.youtube.com/watch?v=K_m7NEDMrV0,https://instagram.com/hellow/";
$string = "你知道地表上哪個國家最幸福嗎？ Hannah 將 2016 UN 世界幸福報告書視覺化呈現，各國的幸福指數與自由、健康與經濟等不同因子以類似南丁格爾玫瑰圖的方式呈現。

南丁格爾玫瑰圖在此扮演類似雷達圖的角色，作者將國家依區域分組以該圖表呈現，如此便可以快速的了解各區域的因子差異；整體來說，大多數國家似乎都不太自由.. XD

下圖為亞澳地區，圖表製作有待改進，但大致上來說，此圖從 12 點鐘方向切成六份，每份各代表一種指標，內有按色區分的許多國家。看起來台灣都不是很幸福的感覺...

圖表來源：https://medium.com/towards-data-science/in-pursuit-of-happiness-4283c8c335c4";

preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $string, $match);

print_r($match[0][0]); 
exit();
*/
if (isset($_GET["url"]) === false) {
    exit();
}

$url = $_GET["url"];
$feed = file_get_contents($url);

if (strpos($url, "www.fhm.com.tw") !== FALSE) {
    
    $desc_list = explode("<description>", $feed);
    
    foreach ($desc_list AS $key => $desc_item) {
        if ($key === 0) {
            continue;
        }
        $desc_parts = explode("</description>", $desc_item);
        $desc_parts[0] = htmlspecialchars($desc_parts[0]);
        $desc_list[$key] = $desc_parts[0] . '</description>' . $desc_parts[1];
    }
    $feed = implode("<description>", $desc_list);
    
    $feed = str_replace("<description>", "<description><![CDATA[", $feed);
    $feed = str_replace("</description>", "]]></description>", $feed);
}

echo $feed;