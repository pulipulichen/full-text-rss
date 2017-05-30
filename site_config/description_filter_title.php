<?php
        // Feed Title: 
        // Feed URL: 
        // FTR URL: 

function filter_title_by_url($title, $url, $item, $html = NULL) {
    if (startsWith($url, "http://www.plurk.com/")) {
        $title = substr($title, strpos($title, " "));
        $title = trim($title);
        return $title;
    }
    else if (startsWith($url, "http://subscription-airiti-library.blogspot.com/") 
            || startsWith($url, "http://subscription-airiti-library.blogspot.tw/")) {
        // http://subscription-airiti-library.blogspot.com/2017/04/airiti-library.html
        $tr_start_pos = strpos($html, '<tr bgcolor="#AAAAAA" align="center">');
        $tr_end_pos = strpos($html, '</a>', $tr_start_pos);
        $title = substr($html, $tr_start_pos, $tr_end_pos-$tr_start_pos);
        $title = substr($title, strrpos($title, '">')+2);
        $title = trim($title);
        $title = $title;
    }
    else if (startsWith($url, "http://www.emeraldinsight.com/")) {
        // http://www.emeraldinsight.com/doi/abs/10.1108/JD-06-2016-0073?af=R
        $title = htmlspecialchars_decode($item->get_title());
        //$title = $title;
    }
    else if (startsWith($url, "http://olw-issue-20151220.blogspot.tw/")) {
        // http://www.emeraldinsight.com/doi/abs/10.1108/JD-06-2016-0073?af=R
        $title = htmlspecialchars_decode($item->get_title());
        $title = str_replace("[OpenLiveWriter/OpenLiveWriter] ", "", $title);
        //$title = $title;
    }
    else if (startsWith($url, "http://sub-jetns-2016.blogspot.tw/")) {
        // http://www.emeraldinsight.com/doi/abs/10.1108/JD-06-2016-0073?af=R
        $title = htmlspecialchars_decode($item->get_title());
        $title = str_replace("ETS TOC Alert: Journal of Educational Technology & Society", "ETS TOC:", $title);
        //$title = $title;
    }
    else if (startsWith($_GET["url"], "https://www.wallflux.com/atom/") ) {
        $title = htmlspecialchars_decode($item->get_title());
        if (startsWith($title, "Photo - ")) {
            $len = strlen("Photo - ");
            $title = substr($title, $len);
        }
        if (startsWith($title, "Video - ")) {
            $title = substr($title, strlen("Video - "));
        }
        if (startsWith($title, "Group wall post by ")) {
            $title = substr($title, strlen("Group wall post by "));
        }
        
        if ($html !== NULL) {
            $abs = htmlspecialchars_decode($item->get_description());
            if (strpos($abs, '<div class="like">') > 0) {
                $abs = substr($abs, 0, strpos($abs, '<div class="like">'));
            }
            if (strpos($abs, '<div class="time">') > 0) {
                $abs = substr($abs, 0, strpos($abs, '<div class="time">'));
            }
            $abs = strip_tags($abs);
            $abs = trim($abs);
            if ($abs !== "") {
                if (mb_strlen($abs, "UTF-8") > 50) {
                    $abs = mb_substr($abs, 0, 50, "UTF-8") . "...";
                } 
                
                $padding = "";
                while (mb_substr($title,0,1,"UTF-8") === mb_substr($abs,0,1,"UTF-8")) {
                    $padding .= mb_substr($title,0,1,"UTF-8");
                    $title = mb_substr($title,1,mb_strlen($title,"UTF-8")-1,"UTF-8");
                    $abs = mb_substr($abs,1,mb_strlen($abs,"UTF-8")-1,"UTF-8");
                }
                $title = $padding.$title.$abs;
            }
            if (strpos($title, ":") > 0) {
                $pos = strpos($title, ":")+1;
                $title = substr($title, $pos);
            }
        }
        
        $title = str_replace("爐石戰記 魔獸英雄傳＿技術 閒聊 交流區.:", "", $title);
        $title = str_replace("[爐石]", "", $title);
        //echo "[" . $title . "]";
    }
    else if (startsWith($url, "http://i.imgur.com/")) {
        $title = htmlspecialchars_decode($item->get_title());
    }
    else if (startsWith($url, "https://www.damanwoo.com/node/")) {
        $title = strip_postfix_to($title, " | 大人物");
    }
    else if (startsWith($url, "http://www.ipc.me/")) {
        $title = strip_postfix_to($title, " | iPc.me");
    }
    else if ($_GET["url"] === "https://www.oschina.net/news/rss") {
        // Feed Title: 开源中国社区最新新闻
        // Feed URL: https://www.oschina.net/news/rss
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https%3A%2F%2Fwww.oschina.net%2Fnews%2Frss&max=2&links=preserve&exc=&submit=Create+Feed
        $title = strip_postfix_to($title, " - ");
    }
    else if (startsWith($url, "http://isvincent.pixnet.net/blog/post/")) {
        $title = strip_postfix_to($title, " @ 學不完．教不停．用不盡 :: 痞客邦 PIXNET ::");
    }
    else if (startsWith($url, "http://www.u-acg.com/archives/")) {
        $title = strip_postfix_to($title, " | U-ACG");
    }
    else if (startsWith($url, "http://www.netadmin.com.tw/")) {
        $title = strip_postfix_to($title, " - 網管人NetAdmin");
        $title = strip_postfix_to($title, " - ");
    }
    else if (startsWith($url, "https://axiang.cc/")) {
        $title = strip_postfix_to($title, " | 阿祥的網路筆記本");
    }
    else if (startsWith($url, "https://lwn.net/")) {
        $title = strip_prefix_to($title, "[$] ");
    }
    else if (startsWith($url, "http://sub-fju-2017.blogspot")) {
        $title = strip_prefix_to($title, "輔大公告信：");
        //$title = 'aaa' . $title;
    }
    else if (startsWith($url, "https://udn.com/news/story/")) {
        $title = strip_postfix_to($title, " | ");
        $title = strip_postfix_to($title, " | ");
    }
    else if (startsWith($url, "http://bbs.onyx-international.com.cn")) {
        // http://bbs.onyx-international.com.cn/forum.php?mod=viewthread&tid=24528
        // 国产用心的大屏厂家才是真爱 - ONYX新闻 - ONYX BOOX BBS
        $title = strip_postfix_to($title, " - ");
        $title = strip_postfix_to($title, " - ");
    }
    else if (startsWith($url, "https://www.bnext.com.tw/")) {
        $title = strip_postfix_to($title, "｜");
    }
    else if (startsWith($url, "http://life.tw/")) {
        $title = strip_postfix_to($title, " LIFE生活網");
    }
    else if (startsWith($url, "https://ez3c.tw/")) {
        // Feed Title: 哇哇3C日誌
        // Feed URL: https://easylife.tw/rss
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https://easylife.tw/rss&max=2&links=preserve&exc=&submit=Create+Feed
        $title = strip_postfix_to($title, " :: 哇哇3C日誌");
    }
    else if (startsWith($url, "http://www.eprice.com.tw/")) {
        $title = strip_postfix_to($title, " | ");
        $title = strip_postfix_to($title, " - ");
    }
    else if (startsWith($url, "http://www.gameapps.hk/news/") 
            || startsWith($url, "http://youxiputao.com/articles/") ) {
        // http://youxiputao.com/articles/
        $title = strip_postfix_to($title, " - ");
    }
    else if (startsWith($_GET["url"], "https://gnn.gamer.com.tw/rss.xml")) {
        // Feed Title: 巴哈姆特 GNN 新聞網
        // Feed URL: https://gnn.gamer.com.tw/rss.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https://gnn.gamer.com.tw/rss.xml&max=2&links=preserve&exc=&submit=Create+Feed
        $title = strip_postfix_to($title, " - 巴哈姆特");
    }
    else if (startsWith($_GET["url"], "http://www.gameapps.hk/rss")) {
        // Feed Title: 香港手機遊戲網
        // Feed URL: http://www.gameapps.hk/rss
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=http://www.gameapps.hk/rss&max=2&links=preserve&exc=&submit=Create+Feed
        $title = strip_postfix_to($title, " - 香港手機遊戲網 GameApps.hk");
    }
    else if (startsWith($_GET["url"], "rss.tgbus.com/hs_news.xml")
            || strpos($url, ".tgbus.com/") > 0) {
        // Feed Title: 电玩巴士-hs频道-新闻
        // Feed URL: rss.tgbus.com/hs_news.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=rss.tgbus.com/hs_news.xml&max=1&links=preserve&exc=&submit=Create+Feed
        //$title = strip_postfix_to($title, "_炉石传说 魔兽英雄传_电玩巴士炉石传说专区");
        $title = strip_postfix_to($title, "_");
        $title = strip_postfix_to($title, "_");
    }
    else if (startsWith($_GET["url"], "www.reddit.com/r/googleplaydeals/.rss")) {
        // Feed Title: Google Play Deals
        // ATOM URL: http://www.reddit.com/r/googleplaydeals/.rss
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=exp-full-text-rss-2013.dlll.nccu.edu.tw%2Ffull-text-rss%2Fatom2rss%2Findex.php%3Fatom%3Dhttp%3A%2F%2Fwww.reddit.com%2Fr%2Fgoogleplaydeals%2F.rss&max=10&links=preserve&exc=&submit=Create+Feed
        $title = $item->get_title();
        $title = strip_postfix_to($title, " : googleplaydeals");
        
        if (strpos($title, " Free)") > 0) {
            $title = '[Free]'.$title;
        }
    }
    else if (startsWith($_GET["url"], "ccsx.tw/feed/")) {
        // Feed Title: CCSX Makes ACG NEWS 支店
        // ATOM URL: http://ccsx.tw/feed/
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=ccsx.tw%2Ffeed%2F&max=10&links=preserve&exc=&submit=Create+Feed
        $title = strip_prefix_to($title, "–");
        $title = trim($title);
    }
    else if (startsWith($_GET["url"], "www.hearthpwn.com/news.rss")) {
        // Feed Title: Hearthstone News from HearthPwn
        // ATOM URL: www.hearthpwn.com/news.rss
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=www.hearthpwn.com/news.rss&max=2&links=preserve&exc=&submit=Create+Feed
        $title = strip_prefix_to($title, " - News");
    }
    
    // -------------------
    // 所有用FB-RSS的都要經過這個檢查
    if (startsWith($_GET["url"], "https://fbrss.com/feed/")) {
        $title = fb_rss_filter_by_url($title, $url, $item, $html);
    }
    
    // -------------------------
    // 所有用wallflux的都要經過這個檢查
    if (startsWith($_GET["url"], "https://www.wallflux.com/feed/")) {
        $title = wallflux_filter_by_url($title, $url, $item, $html);
    }
    
    if (startsWith($_GET["url"], "www.plurk.com/")) {
        $title = plurk_filter_by_url($title, $url, $item, $html);
    }
    
    // 如果$title沒有資料
    if ($title === NULL || trim($title) === "") {
        $title = htmlspecialchars_decode($item->get_description());
        if (strlen($title) > 100) {
            $title = substr($title, 0, 100) . "...";
        }
    }
    
    
    return trim($title);
}

// ----------------------------------

function fb_rss_filter_by_url($title, $url, $item, $html) {
    // https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_543966649035348.xml
    // https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_141456499343573.xml
    // 
    $title = htmlspecialchars_decode($item->get_title());
    $title = trim($title);

    if ($title === "") {
        $title = htmlspecialchars_decode($item->get_description());
        $title = trim($title);
    }
    if (startsWith($title, "Photo - ")) {
        $len = strlen("Photo - ");
        $title = substr($title, $len);
        $title = trim($title);
    }
    if (startsWith($title, "Video - ")) {
        $title = substr($title, strlen("Video - "));
        $title = trim($title);
    }
    if (startsWith($title, "Group wall post by ")) {
        $title = substr($title, strlen("Group wall post by "));
        $title = trim($title);
    }

    if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_1417505918524114.xml") {
        // Feed Title: 黑特政大 FB
        // Feed URL: https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_1417505918524114.xml
        // FTR URL: 

        $title = str_replace("<br>", "\n", $title);
        $title = strip_prefix_to($title, "\n");
        $title = strip_postfix_to($title, "Submitted:");
    }
    else if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_175159599316564.xml") {
        // Feed Title: FB-RSS feed for 4Gamers 
        // Feed URL: https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_175159599316564.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_175159599316564.xml&max=1&links=preserve&exc=&submit=Create+Feed
        $changed_title = filter_title_from_a_tag($title, $item, '<a href="https://www.4gamers.com.tw/news/detail/');
        if ($changed_title !== $title) {
            $title = $changed_title;
            $title = strip_postfix_to($title, "|");
        }

        while (strpos($title, "——") !== FALSE) {
            $title = str_replace("——", "—", $title);
        }
    }
    else if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_236436558020.xml") {
        // Feed Title: FB-RSS feed for 天瓏資訊圖書
        // Feed URL: https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_236436558020.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_236436558020.xml&max=2&links=preserve&exc=&submit=Create+Feed
        $changed_title = filter_title_from_a_tag($title, $item, '<a href="https://www.tenlong.com.tw/products/');
        if ($changed_title !== $title) {
            $title = $changed_title;
            $title = strip_prefix_to($title, " | ");
        }
    }
    else if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_158495994283.xml") {
        // Feed Title: 數位時代 FB
        // Feed URL: https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_158495994283.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_158495994283.xml&max=2&links=preserve&exc=&submit=Create+Feed
        $changed_title = filter_title_from_a_tag($title, $item, '<a href="https://www.bnext.com.tw/');
        if ($changed_title !== $title) {
            $title = $changed_title;
            $title = strip_postfix_to($title, "｜數位時代");
        }
    }
    else if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_6723083591.xml") {
        // Feed Title: FB-RSS feed for Ubuntu
        // Feed URL: https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_6723083591.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https%3A%2F%2Ffbrss.com%2Ffeed%2F2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_6723083591.xml&max=2&links=preserve&exc=&submit=Create+Feed
        $title = filter_title_from_a_tag($title, $item);
    }
    else if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_119279178101235.xml") {
        // Feed Title: Will 保哥的技術交流中心
        // Feed URL: https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_119279178101235.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_119279178101235.xml&max=2&links=preserve&exc=&submit=Create+Feed
        $title = filter_title_from_a_tag($title, $item);
    }
    if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_863393060409175.xml") {
        // Feed Title: 靠北圖書館 [FB]
        // Feed URL: https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_863393060409175.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_863393060409175.xml&max=2&links=preserve&exc=&submit=Create+Feed

        $title = str_replace("<br>", "\n", $title);
        $title = strip_prefix_to($title, "\n");
        $title = strip_postfix_to($title, "Submitted:");

        $title = trim($title);
        if (strpos($title, "#") === 0) {
            $title = substr($title, strpos($title, " ") + 1);
        }
        //echo "[". strpos($title, "#") ."[" . $title ."]]]";
    }
    
    return $title;
}

// ----------------------------------

function wallflux_filter_by_url($title, $url, $item, $html) {

    // https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_543966649035348.xml
    // https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_141456499343573.xml
    // 
    $title = htmlspecialchars_decode($item->get_title());
    if ($title === "") {
        $title = htmlspecialchars_decode($item->get_description());
    }
    if (startsWith($title, "Photo - ")) {
        $len = strlen("Photo - ");
        $title = substr($title, $len);
    }
    if (startsWith($title, "Photo: ")) {
        $len = strlen("Photo: ");
        $title = substr($title, $len);
    }
    if (startsWith($title, "Link: ")) {
        $len = strlen("Link: ");
        $title = substr($title, $len);
    }
    if (startsWith($title, "Video - ")) {
        $title = substr($title, strlen("Video - "));
    }
    if (startsWith($title, "Group wall post by ")) {
        $title = substr($title, strlen("Group wall post by "));
    }

    $desc = $item->get_description();
    $pos = strpos($desc, "'s wall: ");
    if ($pos > 0) {
        $pos = $pos + strlen("'s wall: ");
        $title = substr($desc, $pos);
    }
    
    return $title;
}

function plurk_filter_by_url($title, $url, $item, $html) {
    // Feed Title: ckhung0
    // Feed URL: http://www.plurk.com/ckhung0.xml
    // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=www.plurk.com/ckhung0.xml&max=2&links=preserve&exc=&submit=Create+Feed
    $title = filter_title_from_a_tag($title, $item);
    
    return $title;
}

function filter_title_from_a_tag($title, $item, $needle = '<a href="http') {
    $desc = htmlspecialchars_decode($item->get_description());
    //$needle = '<a href="https://www.4gamers.com.tw/news/detail/';
    //echo "[[[[" . $desc . "]]]]";
    if (strrpos($desc, $needle) !== FALSE) {
        $pos = strrpos($desc, $needle);
        $title = substr($desc, $pos);
        $title = substr($title, strpos($title, ">") + 1);
        $title = substr($title, 0, strpos($title, "<"));
    }
    return $title;
}