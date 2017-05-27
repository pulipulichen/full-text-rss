<?php

function filter_permalink($permalink, $item, $url) {
    /**
     * @version 20140626 布丁
     * 針對incognitomail特殊的設置
     */
    if (strpos($permalink, "http://www.incognitomail.com/?m=") === 0) {
        $a = substr($url, strpos($url, "&a=")+3);
        //echo $a;
        $permalink = $permalink . $a;
    }
    else if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_169635866411766.xml") {
        /**
         * @author Pulipuli Chen <pulipuli.chen@gmail.com> 20170423
         * FB-RSS feed for 原價屋coolpc
         */
        $desc = $item->get_description();
        $pos1 = strpos($desc, "http://www.coolpc.com.tw/phpBB2/viewtopic.php");
        $pos2 = strpos($desc, "<br", $pos1+2);
        if ($pos1 > 0 && $pos2 > 0) {
            $permalink = substr($desc, $pos1, $pos2);
        }
        else {
            preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $desc, $match);
            if (count($match) > 0 && count($match[0]) > 0 && isset($match[0][0])) {
                for ($u = count($match[0])-1; $u >=0; $u--) {
                    $u_url = $match[0][$u];
                    if (strpos($u_url, "https://www.facebook.com/") === FALSE) {
                        $permalink = $u_url;
                        break;
                    }
                }
            }
        }
        //echo "[" . $pos1 ."-" . $pos2 ."]";
        //echo $permalink;
        // $item->get_permalink()
    }
    else if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_137698833067234.xml") {
        /**
         * @author Pulipuli Chen <pulipuli.chen@gmail.com> 20170519
         * FB-RSS 資訊視覺化
         */
        //echo "11212";
        $desc = $item->get_description();
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $desc, $match);
        if (count($match) > 0 && count($match[0]) > 0 && isset($match[0][0])) {
            //echo "match!!!!";
            for ($u = count($match[0])-1; $u >=0; $u--) {
                $u_url = $match[0][$u];
                if (strpos($u_url, "https://www.facebook.com/data.visualize/") === FALSE) {
                    $permalink = $u_url;
                    break;
                }
            }
            //echo $permalink;
        }
    }
    else if (startsWith($_GET["url"], "www.reddit.com/r/googleplaydeals/.rss")) {
        // Feed Title: Google Play Deals
        // ATOM URL: http://www.reddit.com/r/googleplaydeals/.rss
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=exp-full-text-rss-2013.dlll.nccu.edu.tw%2Ffull-text-rss%2Fatom2rss%2Findex.php%3Fatom%3Dhttp%3A%2F%2Fwww.reddit.com%2Fr%2Fgoogleplaydeals%2F.rss&max=10&links=preserve&exc=&submit=Create+Feed
        
        $html = get_original_html($item);
        $pos = strpos($html, "https://play.google.com/store/apps/details?id=");
        if ($pos > 0) {
            $link = substr($html, $pos);
            $link = substr($link, 0, strpos($link, '"'));
            $permalink = $link;
        }
    }
    
    return $permalink;
}