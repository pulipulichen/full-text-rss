<?php
/**
 * 避免抓取以下網頁
 * @param String $permalink
 * @return boolean
 */
function is_in_blacklist($permalink) {
    $permalink_blacklist = array(
        /**
         * @author Pulipuli Chen <pulipuli.chen@gmail.com> 20170612
         * 資料庫
         */
        "www.emeraldinsight.com",
        "www.reddit.com/r/googleplaydeals"
    );
    
    // 不抽取黑名單的內容
    $is_in_blacklist = false;
    if (!url_allowed($permalink)) {
        $is_in_blacklist = true;
    }
    else {
        foreach ($permalink_blacklist AS $keyword) {
            if (strpos($permalink, $keyword) !== FALSE) {
                $is_in_blacklist = true;
                break;
            }
        }
    }
    
    //if ($is_in_blacklist === true) {
    //    throw new Exception("In Black List");
    //}
    
    return $is_in_blacklist;
}