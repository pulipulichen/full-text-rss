// ./phantomjs phantomjs-exec.js "http://blog.pulipuli.info/2017/03/apache-tika-using-apache-tika-extract.html" "div.entry-container"

var config = {};
config.enable_cache = true;

// --------------------------------

var webpage = require('webpage').create();
var webpage2 = require('webpage').create();
var execFile = require('child_process').execFile;
var fs = require('fs');
system = require('system');


config.cache_dir = fs.workingDirectory + "/phantomjs_cache/";
config.output = config.cache_dir + "/phantomjs_output.html";
config.url;

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
};

// --------------------------------

var _mkdir_cache = function (config, _callback) {
	
	if(fs.isDirectory(config.cache_dir) === false) {
		execFile("mkdir", ["-p", "/tmp/phantomjs_cache/"], null, function () {
			execFile("chmod", ["777", "/tmp/phantomjs_cache/"], null, function () {
				_callback();
			});
		});
	}
	else {
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
			var htmlContent = eval('webpage.evaluate(function () {'
				+ '	htmlContent = $(document).find("' + config.selector + '").html();'
				+ '	return htmlContent;'
			+ '})');
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
				var readyState = webpage.evaluate(function () {
					return document.readyState;
				});

				if ("complete" === readyState) {
					onPageReady();
				} else {
					checkReadyState();
				}
			}, 100);
		}

		checkReadyState();
	});
};

var _finish = function (config, htmlContent, _callback) {
	
	var content = htmlContent;
	console.log(content);
	
	fs.write(config.output, content, 'w');
			
	if (config.enable_cache === false) {
		return _callback();
	}
	
	var path = _cache_filepath(config);
	fs.write(path, content, 'w');
	
	return _callback();
};

// ----------------------------

_get_url();
_mkdir_cache(config, function () {
	_read_cache(config, function () {
		_open_webpage(config, function () {
			phantom.exit();
		});
	});
});