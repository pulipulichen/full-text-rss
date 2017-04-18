<?php
function filter_description_by_url($html, $url, $item) {
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
        $html = htmlspecialchars_decode($item->get_description());
        $html = str_replace("<br/>  Purpose", "\n<br/>  <strong>Purpose: </strong> ", $html);
        $html = str_replace("   Design/methodology/approach ", "\n<br/>  <strong>Design/methodology/approach:</strong> ", $html);
        $html = str_replace("   Findings ", "\n<br/>  <strong>Findings:</strong> ", $html);
        $html = str_replace("   Research limitations/implications ", "\n<br/>  <strong>Research limitations/implications:</strong> ", $html);
        $html = str_replace("   Originality/value ", "\n<br/>  <strong>Originality/value:</strong> ", $html);
        
        //$title = $title;
    }
    else if (startsWith($url, "http://sub-jetns-2016.blogspot.tw/")
            || startsWith($url, "http://olw-issue-20151220.blogspot.tw/")) {
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
    
    return $html;
}

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
    return $title;
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