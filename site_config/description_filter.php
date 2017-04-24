<?php
function filter_description_by_url($html, $url, $item, $has_extract) {
    if (startsWith($url, "https://www.bnext.com.tw")) {
        $start_pos = strpos($html, '<article class="article_summary">');
        $end_pos = strpos($html, '<div id="oneadIRMIRTag">', $start_pos);
        if ($start_pos !== false && $end_pos !== false) {
            $html = substr($html, $start_pos, ($end_pos - $start_pos));
            $html = "<div>" . $html;
        }
    }
    else if (startsWith($url, "http://www.emeraldinsight.com/")) {
        // http://www.emeraldinsight.com/doi/abs/10.1108/JD-06-2016-0073?af=R
        
        // http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=http%3A%2F%2Fwww.emeraldinsight.com%2Faction%2FshowFeed%3Ftype%3Detoc%26feed%3Drss%26jc%3Dprog&amp;max=10&amp;links=preserve&amp;exc=&amp;submit=Create+Feed
        $html = htmlspecialchars_decode($item->get_description());
        $html = "<h1>". $item->get_title() . "</h1>\n<br />" . $html;
        $html = str_replace("<br/>  Purpose", "\n<br/>  \n<br/>  <strong>Purpose: </strong> ", $html);
        $html = str_replace("   Design/methodology/approach ", "\n<br/>  \n<br/>  <strong>Design/methodology/approach:</strong> ", $html);
        $html = str_replace("   Findings ", "\n<br/>  \n<br/>  <strong>Findings:</strong> ", $html);
        $html = str_replace("   Research limitations/implications ", "\n<br/>  \n<br/>  <strong>Research limitations/implications:</strong> ", $html);
        $html = str_replace("   Originality/value ", "\n<br/>  \n<br/>  <strong>Originality/value:</strong> ", $html);
        
        //$title = $title;
    }
    else if (startsWith($url, "http://sub-jetns-2016.blogspot.tw/")
            || startsWith($url, "http://olw-issue-20151220.blogspot.tw/")
            || startsWith($url, "http://i.imgur.com/")) {
        // http://www.emeraldinsight.com/doi/abs/10.1108/JD-06-2016-0073?af=R
        $html = htmlspecialchars_decode($item->get_description());
        //$title = $title;
    }
    else if (startsWith($url, "http://www.sciencedirect.com/")) {
        // http://rss.sciencedirect.com/action/redirectFile?&zone=main&currentActivity=feed&usageType=outward&url=http:%2F%2Fwww.sciencedirect.com%2Fscience%3F_ob%3DGatewayURL%26_origin%3DIRSSSEARCH%26_method%3DcitationSearch%26_piikey%3DS0360131517300751%26_version%3D1%26md5%3Da2478e39caa911aa82e8fcbdae17ead8
        //$html = htmlspecialchars_decode($item->get_description());
        $html = substr($html, strpos($html, '<div id="frag_1" '));
        $html = str_replace('<dt class="label">•</dt>', "", $html);
        //$title = $title;
        //<dt class="label">•</dt>
    }
    else if (startsWith($url, "https://www.youtube.com/watch?v=")) {
        //$html = htmlspecialchars_decode($item->get_item_tags("group", "media:group")) . "aaa";
        
        // <iframe width="560" height="315" src="https://www.youtube.com/embed/RBHUEIJMpCY" frameborder="0" allowfullscreen></iframe>
        $id = substr($url, strpos($url, "v=") + 2);
        $html = '<div>' . $html . '</div><iframe width="560" height="315" src="https://www.youtube.com/embed/' . $id . '" frameborder="0" allowfullscreen></iframe>';
        $html = $html . '<div>Preview thumbnail: <br /><img src="https://img.youtube.com/vi/' . $id .'/default.jpg" /></div>';
    }
    else if (startsWith($_GET["url"], "https://www.wallflux.com/atom/") ) {
        $original_html = htmlspecialchars_decode($item->get_description());
        if (strpos($original_html, '<div class="message">') > 0) {
            $pos = strpos($original_html, '<div class="message">');
            $original_html = substr($original_html, $pos, strlen($original_html)-$pos);
        }
        $original_html = str_replace( '<img class="thumb" src="//www.wallflux.com/image/like.png">', "Like: ", $original_html);
        
        if ($has_extract === true) {
            $html = $original_html . "<br /><br />" . $html;
        }
        else {
            $html = $original_html;
        }
    }
    else if (startsWith($_GET["url"], "https://www.wallflux.com/feed/")) {
        // https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_543966649035348.xml
        $original_html = htmlspecialchars_decode($item->get_description());
        
        $desc = $html;
        $pos = strpos($desc, "'s wall: ");
        if ($pos > 0) {
            $pos = $pos + strlen("'s wall: ");
            $html = substr($desc, $pos);
        }
        
        //$html = strip_prefix_to($html, $to);
        
        $desc = $original_html;
        $pos = strpos($desc, "'s wall: ");
        if ($pos > 0) {
            $pos = $pos + strlen("'s wall: ");
            $original_html = substr($desc, $pos);
        }
        
        $pos = strpos($html, '<div class="clearfix _ikh _fbEventsPermalink__layout">');
        if ($pos > 0) {
            $pos = $pos + strlen('<div class="clearfix _ikh _fbEventsPermalink__layout">');
            $pos2 = strpos($html, '<div id="pageFooter" data-referrer="page_footer">', $pos);
            $pos2 = $pos2 - 5;
            $html = substr($html, $pos, $pos2-$pos);
            // 
        }
        
        if ($has_extract === true) {
            $html = $original_html . "<br /><br />" . $html;
        }
        //$html = htmlspecialchars_decode($html);
    }
    else if (startsWith($url, "http://www.eprice.com.tw/")) {
        $html = str_replace('.tmp" data-original="', '" data-original="', $html);
    }
    else if (startsWith($_GET["url"], "https://fbrss.com/feed/")) {
        // https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_543966649035348.xml
        $original_html = htmlspecialchars_decode($item->get_description());
        
        if ($has_extract === true) {
            $html = $original_html . "<br /><br />" . $html;
        }
    }
    
    return $html;
}

// ----------------------------------------------------------------

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
    else if (startsWith($url, "https://udn.com/news/story/")) {
        $title = strip_postfix_to($title, " | ");
        $title = strip_postfix_to($title, " | ");
    }
    else if (startsWith($url, "http://bbs.onyx-international.com.cn")
            || strpos($url, ".tgbus.com/") > -1) {
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
    else if (startsWith($url, "http://www.eprice.com.tw/")) {
        $title = strip_postfix_to($title, " | ");
        $title = strip_postfix_to($title, " - ");
    }
    else if (startsWith($url, "http://www.gameapps.hk/news/") 
            || startsWith($url, "http://youxiputao.com/articles/") ) {
        // http://youxiputao.com/articles/
        $title = strip_postfix_to($title, " - ");
    }
    else if (startsWith($_GET["url"], "https://fbrss.com/feed/")) {
        
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
        if (startsWith($title, "Video - ")) {
            $title = substr($title, strlen("Video - "));
        }
        if (startsWith($title, "Group wall post by ")) {
            $title = substr($title, strlen("Group wall post by "));
        }
        
        if ($_GET["url"] === "https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_1417505918524114.xml") {
            // 黑特政大 FB
            // https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_1417505918524114.xml
            $title = strip_prefix_to($title, "\n");
            $title = strip_postfix_to($title, "Submitted:");
        }
    }
    else if (startsWith($_GET["url"], "https://www.wallflux.com/feed/")) {
        
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
    }
    
    
    return trim($title);
}

// ---------------------------------------------------------

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