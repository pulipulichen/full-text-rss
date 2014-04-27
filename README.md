Full-Text RSS
=============

### NOTE 備註

This is a our public version of Full-Text RSS available to download for free from <http://code.fivefilters.org>.

This version has been enhanced by <a href="http://pulipuli.blogspot.tw">Pulipuli Chen</a> with:
- Multi-pages integration / 多頁面整合
- Part of the interface support Traditional Chinese / 部分介面提供正體中文

For best extraction results, and to help us sustain the project, you can purchase the most up-to-date version at <http://fivefilters.org/content-only/#download> - so if you like this free version, please consider supporting us by purchasing the latest release. 

If you have no need for the latest release, but would still like to contribute something, you can donate via [Gittip](https://www.gittip.com/fivefilters/) or [Flattr](https://flattr.com/profile/k1m).

### ERROR REPORT 錯誤回報

If you always get RSS feed extraction fail, please report the URL of RSS feed to [GitHub Issue](https://github.com/pulipulichen/full-text-rss/issues/new). (Registation needed)
/ 如果RSS feed資訊來源一直無法順利抽取出全文，請回報該RSS feed資訊來源的網址到[GitHub Issue](https://github.com/pulipulichen/full-text-rss/issues/new)中（需要註冊，免費）

### About 關於

See <http://fivefilters.org/content-only/> for a description of the code.

### Installation 安裝方法

1. Extract the files in [this ZIP](https://github.com/pulipulichen/full-text-rss/archive/master.zip) archive to a folder on your computer. 
/ 請[下載壓縮檔](https://github.com/pulipulichen/full-text-rss/archive/master.zip)，解壓縮到你的電腦中

2. FTP the files up to your server 
/ 將檔案上傳到你的伺服器

3. Access index.php through your browser. E.g. http://example.org/full-text-rss/index.php
/ 開啟index.php網頁，例如 http://example.org/full-text-rss/index.php

4. Enter a URL in the form field to test the code
/ 輸入網址遞交表單，測試看能不能順利運作

5. If you get an RSS feed with full-text content, all is working well. :)
/ 如果網頁正常顯示全文RSS資訊來源，那就是正常運作了！ :)

### Configuration (optional) 設定 (可選)

1. Save a copy of config.php as custom_config.php and edit custom_config.php

2. If you decide to enable caching, make sure the cache folder (and its 2 sub folders) is writable. (You might need to change the permissions of these folders to 777 through your FTP client.)

3. If extraction always failed, please try to increase "max_execution_time", "max_input_time", and "memory_limit" in your server's php.ini and restart server.
/ 如果文章抽取總是失敗，請嘗試設定php.ini的"max_execution_time"、"max_input_time"與"memory_limit"，並重新啟動伺服器。

### Site-specific extraction rules 特定網站的抽取規則

This free version does not contain the site config files we include with purchased copies, but these are now all available [online](https://github.com/fivefilters/ftr-site-config). If you'd like to keep yours up to date using Git, follow the steps below:

1. Change into the site_config/standard/ folder
2. Delete everything in there
3. Using the command line, enter: `git clone https://github.com/pulipulichen/full-text-rss.git .`
4. Git should now download the latest site config files for you.
5. To update the site config files again, you can simply run `git pull` from the directory.

### Code example 程式碼範例

If you're developing an application which requires content extraction, you can call Full-Text RSS as a web service from within your application. Here's how to do it in PHP:

	<?php
	// $ftr should be URL where you installed this application
	$ftr = 'http://example.org/full-text-rss/';
	$article = 'http://www.bbc.co.uk/news/world-europe-21936308';

	$request = $ftr.'makefulltextfeed.php?format=json&url='.urlencode($article);

	// Send HTTP request and get response
	$result = @file_get_contents($request);

	if (!$result) die('Failed to fetch content');

	$json = @json_decode($result);

	if (!$json) die('Failed to parse JSON');

	// What do we have?
	// var_dump($json);
	
	// Items?
	// var_dump($json->rss->channel->item);

	$title = $json->rss->channel->item->title;
	// Note: this works when you're processing an article.
	// If the input URL is a feed, ->item will be an array.

	echo $title;

### Different language? 搭配其他語言

Although we don't have examples in other programming languages, the essential steps should be:

1. Construct the request URL using URL where you installed Full-Text RSS and the article or feed URL (see $ftr, $article, $request in example above).

2. Fetch the resulting URL using an HTTP GET request.

3. Parse the HTTP response body as JSON and grab what you need.

### MEMO

設定偵測用的預設網址：/config.php
設定下一頁：/config.php
設定選擇範圍：/libraries/content-extractor/SiteConfig.php