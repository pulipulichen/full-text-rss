<?php

function filter_skip_item($item) {
    if (startsWith($_GET["url"], "https://www.wallflux.com/atom/") &&
            (trim($item->get_title()) === "RSS feeds for Facebook pages and group"
            || trim($item->get_title()) === "Wallflux Atom Feed Demonstration"
            || trim($item->get_title()) === "") ) {
        return false;
    }
    else if (startsWith($_GET["url"], "https://www.wallflux.com/atom/") 
            && strpos($item->get_title(), " - Wallflux Group info") > 0) {
        return false;
    }
    else if (startsWith($_GET["url"], "https://www.wallflux.com/feed/") 
            && (strpos($item->get_title(), "Wallflux") > -1 || strpos($item->get_description(), "Wallflux") > -1)
            ) {
        return false;
    }
    else if (startsWith($_GET["url"], "www.reddit.com/r/googleplaydeals/.rss")
            && (strpos($item->get_title(), "[Meta] Removing the 1,000 downloads rule") > -1)) {
        // Feed Title: Google Play Deals
        // ATOM URL: http://www.reddit.com/r/googleplaydeals/.rss
        // FTR URL: http://exp-full-text-rss-2013.dlll.nccu.edu.tw/full-text-rss/makefulltextfeed.php?url=exp-full-text-rss-2013.dlll.nccu.edu.tw%2Ffull-text-rss%2Fatom2rss%2Findex.php%3Fatom%3Dhttp%3A%2F%2Fwww.reddit.com%2Fr%2Fgoogleplaydeals%2F.rss&max=10&links=preserve&exc=&submit=Create+Feed
        return false;
    }
    
    return true;
}