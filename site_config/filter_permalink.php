<?php

function filter_permalink($permalink, $item, $url) {
    if (strpos($permalink, "http://www.incognitomail.com/?m=") === 0) {
        /**
         * @version 20140626 布丁
         * 針對incognitomail特殊的設置
         */
        $a = substr($url, strpos($url, "&a=")+3);
        //echo $a;
        $permalink = $permalink . $a;
    }
    else if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_236436558020.xml") {
        // Feed Title: FB-RSS feed for 天瓏資訊圖書
        // Feed URL: https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_236436558020.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_236436558020.xml&max=2&links=preserve&exc=&submit=Create+Feed
        //$permalink = filter_link_from_desc($permalink, $item, "https://www.tenlong.com.tw/products/");
    }
    else if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_169635866411766.xml") {
        // Feed Title: FB-RSS feed for 原價屋coolpc
        // Feed URL: https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_169635866411766.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_169635866411766.xml&max=2&links=preserve&exc=&submit=Create+Feed
        $permalink = filter_link_from_desc($permalink, $item, "http://www.coolpc.com.tw/phpBB2/viewtopic.php");
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
                if (strpos($u_url, "https://www.facebook.com/") === FALSE ) {
                    $permalink = $u_url;
                    break;
                }
            }
            //echo $permalink;
        }
    }
    else if (strpos($_GET["url"], "www.reddit.com") !== FALSE
            && strpos($_GET["url"], "googleplaydeals") !== FALSE ) {
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
    else if (startsWith($_GET["url"], "www.plurk.com/")) {
        // Feed Title: ckhung0
        // Feed URL: http://www.plurk.com/ckhung0.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=www.plurk.com/ckhung0.xml&max=2&links=preserve&exc=&submit=Create+Feed

        $permalink = filter_link_from_a_tag($permalink, $item);
    }
    
    return $permalink;
}

function filter_link_from_desc($permalink, $item, $needle = NULL) {
    $desc = htmlspecialchars_decode($item->get_description());
    $pos1 = 0;
    $pos2 = 0;
    $pos3 = 0;
    if ($needle !== NULL) {
        $pos1 = strpos($desc, $needle);
        $pos2 = strpos($desc, "<br", $pos1+2);
        $pos3 = strpos($desc, "\n", $pos1+2);
        
        if ($pos2 === FALSE && $pos3 !== FALSE) {
            $pos2 = $pos3;
        }
    }
    if ($pos1 > 0 && $pos2 > 0) {
        $permalink = substr($desc, $pos1, ($pos2 - $pos1));
        //echo $permalink;
    }
    else if ($pos1 > 0 && $pos3 > 0) {
        $permalink = substr($desc, $pos1, ($pos3 - $pos1));
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
    return $permalink;
}

function filter_link_from_a_tag($permalink, $item, $needle = '<a href="http') {
    $desc = $item->get_description();
    //$needle = '<a href="https://www.4gamers.com.tw/news/detail/';
    //echo "[[[[" . $desc . "]]]]";
    if (strrpos($desc, $needle) !== FALSE) {
        $pos = strrpos($desc, $needle);
        $permalink = substr($desc, $pos);
        $permalink = substr($permalink, strpos($permalink, '"') + 1);
        $permalink = substr($permalink, 0, strpos($permalink, '"'));
    }
    return $permalink;
}

function has_link_from_a_tag($item, $needle = '<a href="http') {
    $desc = $item->get_description();
    //$needle = '<a href="https://www.4gamers.com.tw/news/detail/';
    //echo "[[[[" . $desc . "]]]]";
    return (strrpos($desc, $needle) !== FALSE);
}