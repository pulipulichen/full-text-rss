// ./phantomjs phantomjs-exec.js "https://gnn.gamer.com.tw/3/144513.html" "div.GN-lbox3B"

var config = {};
config.enable_cache = true;
config.cache_dir = "/tmp/phantomjs_cache/";
config.output = "/tmp/phantomjs_cache/phantomjs_output.html";
//console.log("00000");
//phantom.exit();
// --------------------------------

var webpage = require('webpage').create();
var webpage2 = require('webpage').create();
var execFile = require('child_process').execFile;
var fs = require('fs');
system = require('system');

config.url;	// = 'https://gnn.gamer.com.tw/3/144513.html';
//global selector;

// --------------------------------

var _get_url = function (_callback) {
	if (typeof(system.args[1]) === 'string') {
		config.url = system.args[1];
	}
	else {
		phantom.exit();
	}
	
	if (typeof(system.args[2]) === 'string') {
		config.selector = system.args[2];
	}
	
	//JSON.toString(system.args);
	//phantom.exit();
};

// --------------------------------

var _mkdir_cache = function (config, _callback) {
	/*
	if (config.enable_cache === false) {
		return _callback();
	}
	*/

	if(fs.isDirectory(config.cache_dir) === false) {
		execFile("mkdir", ["-p", "/tmp/phantomjs_cache/"], null, function () {
			//console.log("ok1");
			//phantom.exit();
			execFile("chmod", ["777", "/tmp/phantomjs_cache/"], null, function () {
				_callback();
			});
		});
	}
	else {
		//console.log("ok2");
		//phantom.exit();
		_callback();
	}

};

var _cache_filepath = function (config) {
	var _path = escape(config.url);
	_path = _path.replace(/\//g, '')
	return config.cache_dir + _path + ".html";
};

var _read_cache = function (config, _callback) {
	if (config.enable_cache === false) {
		return _callback();
	}
	
	var _file = _cache_filepath(config);
	if (fs.exists(_file)) {
		content = fs.read(_file);
		console.log(content);
		fs.write(config.output, content, 'w');
		phantom.exit();
	}
	else {
		_callback();
	}
};


// ----------------------------

var _open_webpage = function (config, _callback) {
	
	var onPageReady = function() {
		if (config.selector !== undefined) {
			//console.log(1);
			var htmlContent = eval('webpage.evaluate(function () {'
				+ '	htmlContent = $(document).find("' + config.selector + '").html();'
				+ '	return htmlContent;'
			+ '})');
			//console.log(htmlContent);
		}
		else {
			var htmlContent = webpage.evaluate(function () {
				return document.documentElement.outerHTML;
			});
		}
		_finish(config, htmlContent, _callback);
	};
	
	console.log("Load: " + config.url);
	webpage.open(config.url, function() {
		webpage.injectJs('jquery.js');
		function checkReadyState() {
			setTimeout(function () {
				//console.log("0");
				var readyState = webpage.evaluate(function () {
					return document.readyState;
				});

				if ("complete" === readyState) {
					onPageReady();
				} else {
					checkReadyState();
				}
			}, 500);
		}

		checkReadyState();
	});
};

var _finish = function (config, htmlContent, _callback) {
	
	var content = htmlContent;
	console.log(content);
	
	//fs.remove(config.output);
	fs.write(config.output, content, 'w');
			
	if (config.enable_cache === false) {
		return _callback();
	}
	
	var path = _cache_filepath(config);
	//var content = ;
	fs.write(path, content, 'w');
	
	return _callback();
};

// ----------------------------

_get_url();
//console.log("c");
_mkdir_cache(config, function () {
	//console.log("a");
	_read_cache(config, function () {
		//console.log("b");
		_open_webpage(config, function () {
			phantom.exit();
		});
	});
});
