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
    else if (startsWith($_GET["url"], "www.reddit.com/r/googleplaydeals/.rss")) {
        // Feed Title: Google Play Deals
        // ATOM URL: http://www.reddit.com/r/googleplaydeals/.rss
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=exp-full-text-rss-2013.dlll.nccu.edu.tw%2Ffull-text-rss%2Fatom2rss%2Findex.php%3Fatom%3Dhttp%3A%2F%2Fwww.reddit.com%2Fr%2Fgoogleplaydeals%2F.rss&max=10&links=preserve&exc=&submit=Create+Feed
        
        // 抓取 <a>
        /*
        $pos = strpos($html, "https://play.google.com/store/apps/details?id=");
        $link = "";
        if ($pos > 0) {
            $link = substr($html, $pos);
            $link = substr($link, 0, strpos($link, '"'));
        }
        
        $html = '<h1><a href="'.$link.'" target="_blank">Google Play: ' . $item->get_title() . '</a></h1>'
                . get_original_html($item);
        */
        $html = get_original_html($item);
        //echo "11212";
    }
    
    if (startsWith($_GET["url"], "https://fbrss.com/feed/")) {
        // https://fbrss.com/feed/2ea5083c0ced7a05bb4ab03f65ba32c12fb6e0b8_543966649035348.xml
        $original_html = htmlspecialchars_decode($item->get_description());
        
        if ($has_extract === true) {
            $html = $original_html . "<br />\n<hr />\n<br />\n" . $html;
        }
    }
    
    if (startsWith($_GET["url"], "www.plurk.com/")) {
        // Feed Title: ckhung0
        // Feed URL: http://www.plurk.com/ckhung0.xml
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=www.plurk.com/ckhung0.xml&max=2&links=preserve&exc=&submit=Create+Feed
        
        $original_html = htmlspecialchars_decode($item->get_description());
        
        if ($has_extract === true) {
            $html = $original_html . "<br />\n<hr />\n<br />\n" . $html;
        }
    }
    
    
    return $html;
}

function get_original_html ($item) {
    return htmlspecialchars_decode($item->get_description());
}