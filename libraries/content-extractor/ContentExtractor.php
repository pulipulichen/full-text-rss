<?php
/**
 * Content Extractor
 * 
 * Uses patterns specified in site config files and auto detection (hNews/PHP Readability) 
 * to extract content from HTML files.
 * 
 * @version 0.8
 * @date 2012-02-21
 * @author Keyvan Minoukadeh
 * @copyright 2011 Keyvan Minoukadeh
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPL v3
 */
 
include("simple_html_dom.php");

class ContentExtractor
{
	protected static $tidy_config = array(
				 'clean' => true,
				 'output-xhtml' => true,
				 'logical-emphasis' => true,
				 'show-body-only' => false,
				 'new-blocklevel-tags' => 'article, aside, footer, header, hgroup, menu, nav, section, details, datagrid',
				 'new-inline-tags' => 'mark, time, meter, progress, data',
				 'wrap' => 0,
				 'drop-empty-paras' => true,
				 'drop-proprietary-attributes' => false,
				 'enclose-text' => true,
				 'enclose-block-text' => true,
				 'merge-divs' => true,
				 'merge-spans' => true,
				 'char-encoding' => 'utf8',
				 'hide-comments' => true
				 );
	protected $html;
	protected $config;
	protected $title;
	protected $author = array();
	protected $language;
	protected $date;
	protected $body;
	protected $success = false;
	public $fingerprints = array();
	public $readability;
	public $debug = false;
        
        // 20131011 Pulipuli Chen
        // 加入記錄
        private $path;
        private $fallback;
        
        public $next_page_deep_count = 0;
        public $next_pages = array();
        
        /**
         * 設定檔的參數
         * @var Array
         */
        private $options;

	function __construct($path, $fallback=null) {
		SiteConfig::set_config_path($path, $fallback);
                
		// 20131011 Pulipuli Chen
		// 加入記錄
		$this->path = $path;
		$this->fallback = $fallback;
		
		require(dirname(__FILE__).'/../../config.php');
		$this->options = $options;
	}
	
	protected function debug($msg) {
		if ($this->debug) {
			$mem = round(memory_get_usage()/1024, 2);
			$memPeak = round(memory_get_peak_usage()/1024, 2);
			echo '* ',$msg;
			echo ' - mem used: ',$mem," (peak: $memPeak)\n";	
			ob_flush();
			flush();
		}
	}
	
	public function reset() {
		$this->html = null;
		$this->readability = null;
		$this->config = null;
		$this->title = null;
		$this->body = null;
		$this->author = array();
		$this->language = null;
		$this->date = null;
		$this->success = false;
	}

	public function findHostUsingFingerprints($html) {
		$this->debug('Checking fingerprints...');
		$head = substr($html, 0, 8000);
		foreach ($this->fingerprints as $_fp => $_fphost) {
			$lookin = 'html';
			if (is_array($_fphost)) {
				if (isset($_fphost['head']) && $_fphost['head']) {
					$lookin = 'head';
				}
				$_fphost = $_fphost['hostname'];
			}
			if (strpos($$lookin, $_fp) !== false) {
				$this->debug("Found match: $_fphost");
				return $_fphost;
			}
		}
		return false;
	}
	
	public function throw_exception ($message) {
		throw new Exception($message);
	}
	
	// returns true on success, false on failure
	// $smart_tidy indicates that if tidy is used and no results are produced, we will
	// try again without it. Tidy helps us deal with PHP's patchy HTML parsing most of the time
	// but it has problems of its own which we try to avoid with this option.
	public function process($html, $url, $smart_tidy=true) {
		$this->reset();
		// extract host name
		$host = @parse_url($url, PHP_URL_HOST);
		if (!($this->config = SiteConfig::build($host))) {
			// no match, check HTML for fingerprints
			if (!empty($this->fingerprints) && ($_fphost = $this->findHostUsingFingerprints($html))) {
				$this->config = SiteConfig::build($_fphost);
			}
			unset($_fphost);
			if (!$this->config) {
				// no match, so use defaults
				$this->config = new SiteConfig();
			}
		}
		
		// ---------------------------
		//throw new Exception(implode("|", $this->config->body));
		$needle = "phantomjs ";
		$phantomjs_body = null;
		if (count($this->config->body) > 0 && strpos($this->config->body[0], $needle) === 0) {
			$code = implode("", $this->config->body);
			$func = substr($code, strpos($code, " "), strrpos($code, "};")-strpos($code, " ")+2);
			
			$selector = substr($code, strpos($code, "};") + 2, strlen($code)-(strpos($code, "};") + 2));
			$selector = trim($selector);
			if ($selector !== "") {
				$selector = ' "'.$selector . '"';
			}
			
			//throw new Exception($code);
			eval('$func = ' . $func);
			//throw new Exception($func("t"));
			
			//$this->throw_exception($url);
			$cmd = '/var/www/phantomjs-2.1.1-linux-i686/bin/phantomjs /var/www/phantomjs-2.1.1-linux-i686/bin/phantomjs-exec.js "' . $url . '"' . $selector;
			
			//$this->throw_exception($cmd);
			
			exec($cmd, $output);
			
			
			//$output = implode("\n", $output);
			$output = file_get_contents("/tmp/phantomjs_cache/phantomjs_output.html");
			$phantomjs_body = $func($output);
			//$this->throw_exception($phantomjs_body);
		}
		// ---------------------------
		
		//echo count($this->config->body);
		// store copy of config in our static cache array in case we need to process another URL
		SiteConfig::add_to_cache($host, $this->config);
		
		// do string replacements
		foreach ($this->config->replace_string as $_repl) {
			$html = str_replace($_repl[0], $_repl[1], $html);
		}
		unset($_repl);
		
		// use tidy (if it exists)?
		// This fixes problems with some sites which would otherwise
		// trouble DOMDocument's HTML parsing. (Although sometimes it
		// makes matters worse, which is why you can override it in site config files.)
		$tidied = false;
		if ($this->config->tidy && function_exists('tidy_parse_string') && $smart_tidy) {
			$this->debug('Using Tidy');
			$tidy = tidy_parse_string($html, self::$tidy_config, 'UTF8');
			if (tidy_clean_repair($tidy)) {
				$original_html = $html;
				$tidied = true;
				$html = $tidy->value;
			}
			unset($tidy);
		}
		
		// load and parse html
		$this->readability = new Readability($html, $url);		
		
		// we use xpath to find elements in the given HTML document
		// see http://en.wikipedia.org/wiki/XPath_1.0
		$xpath = new DOMXPath($this->readability->dom);

		// try to get title
		foreach ($this->config->title as $pattern) {
			$elems = @$xpath->evaluate($pattern, $this->readability->dom);
			if (is_string($elems)) {
				$this->debug('Title expression evaluated as string');
				$this->title = trim($elems);
				break;
			} elseif ($elems instanceof DOMNodeList && $elems->length > 0) {
				$this->debug('Title matched');
				$this->title = $elems->item(0)->textContent;
				// remove title from document
				try {
					$elems->item(0)->parentNode->removeChild($elems->item(0));
				} catch (DOMException $e) {
					// do nothing
				}
				break;
			}
		}
		
		// try to get author (if it hasn't already been set)
		if (empty($this->author)) {
			foreach ($this->config->author as $pattern) {
				$elems = @$xpath->evaluate($pattern, $this->readability->dom);
				if (is_string($elems)) {
					$this->debug('Author expression evaluated as string');
					if (trim($elems) != '') {
						$this->author[] = trim($elems);
						break;
					}
				} elseif ($elems instanceof DOMNodeList && $elems->length > 0) {
					foreach ($elems as $elem) {
						if (!isset($elem->parentNode)) continue;
						$this->author[] = trim($elem->textContent);
					}
					if (!empty($this->author)) break;
				}
			}
		}
		
		// try to get language
		$_lang_xpath = array('//html[@lang]/@lang', '//meta[@name="DC.language"]/@content');
		foreach ($_lang_xpath as $pattern) {
			$elems = @$xpath->evaluate($pattern, $this->readability->dom);
			if (is_string($elems)) {
				if (trim($elems) != '') {
					$this->language = trim($elems);
					break;
				}
			} elseif ($elems instanceof DOMNodeList && $elems->length > 0) {
				foreach ($elems as $elem) {
					if (!isset($elem->parentNode)) continue;
					$this->language = trim($elem->textContent);
				}
				if ($this->language) break;
			}
		}
		
		// try to get date
		foreach ($this->config->date as $pattern) {
			$elems = @$xpath->evaluate($pattern, $this->readability->dom);
			if (is_string($elems)) {
				$this->debug('Date expression evaluated as string');
				$this->date = strtotime(trim($elems, "; \t\n\r\0\x0B"));
			} elseif ($elems instanceof DOMNodeList && $elems->length > 0) {
				$this->debug('Date matched');
				$this->date = $elems->item(0)->textContent;
				$this->date = strtotime(trim($this->date, "; \t\n\r\0\x0B"));
				// remove date from document
				// $elems->item(0)->parentNode->removeChild($elems->item(0));
			}
			if (!$this->date) {
				$this->date = null;
			} else {
				break;
			}
		}

		// strip elements (using xpath expressions)
		foreach ($this->config->strip as $pattern) {
			$elems = @$xpath->query($pattern, $this->readability->dom);
			// check for matches
			if ($elems && $elems->length > 0) {
				$this->debug('Stripping '.$elems->length.' elements (strip)');
				for ($i=$elems->length-1; $i >= 0; $i--) {
					$elems->item($i)->parentNode->removeChild($elems->item($i));
				}
			}
		}
		
		// strip elements (using id and class attribute values)
		foreach ($this->config->strip_id_or_class as $string) {
			$string = strtr($string, array("'"=>'', '"'=>''));
			$elems = @$xpath->query("//*[contains(@class, '$string') or contains(@id, '$string')]", $this->readability->dom);
			// check for matches
			if ($elems && $elems->length > 0) {
				$this->debug('Stripping '.$elems->length.' elements (strip_id_or_class)');
				for ($i=$elems->length-1; $i >= 0; $i--) {
					$elems->item($i)->parentNode->removeChild($elems->item($i));
				}
			}
		}
		
		// strip images (using src attribute values)
		foreach ($this->config->strip_image_src as $string) {
			$string = strtr($string, array("'"=>'', '"'=>''));
			$elems = @$xpath->query("//img[contains(@src, '$string')]", $this->readability->dom);
			// check for matches
			if ($elems && $elems->length > 0) {
				$this->debug('Stripping '.$elems->length.' image elements');
				for ($i=$elems->length-1; $i >= 0; $i--) {
					$elems->item($i)->parentNode->removeChild($elems->item($i));
				}
			}
		}
		// strip elements using Readability.com and Instapaper.com ignore class names
		// .entry-unrelated and .instapaper_ignore
		// See https://www.readability.com/publishers/guidelines/#view-plainGuidelines
		// and http://blog.instapaper.com/post/730281947
		$elems = @$xpath->query("//*[contains(concat(' ',normalize-space(@class),' '),' entry-unrelated ') or contains(concat(' ',normalize-space(@class),' '),' instapaper_ignore ')]", $this->readability->dom);
		// check for matches
		if ($elems && $elems->length > 0) {
			$this->debug('Stripping '.$elems->length.' .entry-unrelated,.instapaper_ignore elements');
			for ($i=$elems->length-1; $i >= 0; $i--) {
				$elems->item($i)->parentNode->removeChild($elems->item($i));
			}
		}
		
		// strip elements that contain style="display: none;"
		$elems = @$xpath->query("//*[contains(@style,'display:none')]", $this->readability->dom);
		// check for matches
		if ($elems && $elems->length > 0) {
			$this->debug('Stripping '.$elems->length.' elements with inline display:none style');
			for ($i=$elems->length-1; $i >= 0; $i--) {
				$elems->item($i)->parentNode->removeChild($elems->item($i));
			}
		}
		
		// try to get body
		if ($phantomjs_body === null) {
			foreach ($this->config->body as $pattern) {
				$elems = @$xpath->query($pattern, $this->readability->dom);
				// check for matches
							//echo "elems->length: [" . $pattern. "]\n\n";
							//echo "elems->length: [" . $this->readability->dom->innerHTML. "]\n\n";
				if ($elems && $elems->length > 0) {
								
					//echo "elems->length matched: [" . $pattern. "]\n\n";
					
					//print_r($next_page_pattern);
								
					$this->body = $this->getMatchedBody($elems);
								
					//$next_page_content = $this->retrieveNextPage($xpath, $url);
					//if ($next_page_content !== FALSE) {
					//    $body->appendChild($next_page_content);
						//$next_page_content = $this->retrieveNextPage($xpath, $body, $url);
					//}
				   
					//$this->body = $body;
					
					if ($elems->length === 1) {	
                                            break;
					}
				}
			}	
		}
		else {
			
			//echo $dom->find("div.lucy-content")[0] . "\n\n\n";
			//$this->throw_exception("!");
			/*
			$dom = $this->readability->dom->createElement('div');
			$dom1 = new DOMDocument;
			$dom1->loadHTML($html->save());
			foreach ($dom1->childNodes as $node) {
				$node = $this->readability->dom->importNode($node);
				$dom->appendChild($node);
			}*/
			/*
			//libxml_use_internal_errors(true);
			$dom2 = new DOMDocument;
			$field_html = $dom2->createDocumentFragment(); // create fragment
			//echo $phantomjs_body . "\n\n\n";
			$field_html->appendXML($phantomjs_body);
			foreach ($field_html->childNodes as $node) {
				$node = $this->readability->dom->importNode($node);
				$dom->appendChild($node);
			}
			//@$dom->appendChild($field_html);
			*/
			/*
			//
			$tmpDoc = $this->readability->dom->createDocumentFragment();
			$tmpDoc->loadHTML($phantomjs_body);
			foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
				$node = $this->readability->dom->importNode($node);
				$dom->appendChild($node);
			}
			*/
			/*
			$code = implode("", $this->config->body);
			$code = substr($code, strpos($code, "};") + 2, strlen($code)-(strpos($code, "};") + 2));
			$code = trim($code);
			if ($code !== "") {
				$dom = str_get_html($phantomjs_body);
				$this->body = $dom->find($code)[0]->outertext;
			}
			else {
				$this->body = $dom->save();
			}
			*/
			//$dom = str_get_html($phantomjs_body);	//
			//$this->body = $dom->save();
			$this->body = $phantomjs_body;
			
			//$this->body = $phantomjs_body;
			//$doc = new DOMDocument();
			//$doc->loadHTML($phantomjs_body);
			//$elems = @$xpath->query("//div[@itemprop = 'GN-lbox3B']", (new Readability($phantomjs_body, $url))->dom);
			//$this->body = $this->getMatchedBody($elems);
			
			//$this->throw_exception("!!" . $phantomjs_body);
		}
                
                //echo "auto detect之前： [" . $this->body->innerHTML . "]\n\n";
		
		// auto detect?
		$detect_title = $detect_body = $detect_author = $detect_date = false;
		// detect title?
		if (!isset($this->title)) {
			if (empty($this->config->title) || $this->config->autodetect_on_failure) {
				$detect_title = true;
			}
		}
		// detect body?
		//if (!empty($this->config->body)) {
		//	throw new Exception("有設定: " . json_encode($this->config->body));
		//}
		if (!isset($this->body)) {
			if (empty($this->config->body) || $this->config->autodetect_on_failure) {
				$detect_body = true;
			}
		}
		// detect author?
		if (empty($this->author)) {
			if (empty($this->config->author) || $this->config->autodetect_on_failure) {
				$detect_author = true;
			}
		}
		// detect date?
		if (!isset($this->date)) {
			if (empty($this->config->date) || $this->config->autodetect_on_failure) {
				$detect_date = true;
			}
		}

		// check for hNews
		if ($detect_title || $detect_body) {
			// check for hentry
			$elems = @$xpath->query("//*[contains(concat(' ',normalize-space(@class),' '),' hentry ')]", $this->readability->dom);
			if ($elems && $elems->length > 0) {
				$this->debug('hNews: found hentry');
				$hentry = $elems->item(0);
				
				if ($detect_title) {
					// check for entry-title
					$elems = @$xpath->query(".//*[contains(concat(' ',normalize-space(@class),' '),' entry-title ')]", $hentry);
					if ($elems && $elems->length > 0) {
						$this->debug('hNews: found entry-title');
						$this->title = $elems->item(0)->textContent;
						// remove title from document
						$elems->item(0)->parentNode->removeChild($elems->item(0));
						$detect_title = false;
					}
				}
				
				if ($detect_date) {
					// check for time element with pubdate attribute
					$elems = @$xpath->query(".//time[@pubdate] | .//abbr[contains(concat(' ',normalize-space(@class),' '),' published ')]", $hentry);
					if ($elems && $elems->length > 0) {
						$this->debug('hNews: found publication date');
						$this->date = strtotime(trim($elems->item(0)->textContent));
						// remove date from document
						//$elems->item(0)->parentNode->removeChild($elems->item(0));
						if ($this->date) {
							$detect_date = false;
						} else {
							$this->date = null;
						}
					}
				}

				if ($detect_author) {
					// check for time element with pubdate attribute
					$elems = @$xpath->query(".//*[contains(concat(' ',normalize-space(@class),' '),' vcard ') and (contains(concat(' ',normalize-space(@class),' '),' author ') or contains(concat(' ',normalize-space(@class),' '),' byline '))]", $hentry);
					if ($elems && $elems->length > 0) {
						$this->debug('hNews: found author');
						$author = $elems->item(0);
						$fn = @$xpath->query(".//*[contains(concat(' ',normalize-space(@class),' '),' fn ')]", $author);
						if ($fn && $fn->length > 0) {
							foreach ($fn as $_fn) {
								if (trim($_fn->textContent) != '') {
									$this->author[] = trim($_fn->textContent);
								}
							}
						} else {
							if (trim($author->textContent) != '') {
								$this->author[] = trim($author->textContent);
							}
						}
						$detect_author = empty($this->author);
					}
				}
				
				// check for entry-content.
				// according to hAtom spec, if there are multiple elements marked entry-content,
				// we include all of these in the order they appear - see http://microformats.org/wiki/hatom#Entry_Content
				if ($detect_body) {
					$elems = @$xpath->query(".//*[contains(concat(' ',normalize-space(@class),' '),' entry-content ')]", $hentry);
					if ($elems && $elems->length > 0) {
						$this->debug('hNews: found entry-content');
						if ($elems->length == 1) {
							// what if it's empty? (some sites misuse hNews - place their content outside an empty entry-content element)
							$e = $elems->item(0);
							if (($e->tagName == 'img') || (trim($e->textContent) != '')) {
								$this->body = $elems->item(0);
                                                                //echo "elems->item: [" . $this->body->innerHTML . "]\n\n";
                                                                
								// prune (clean up elements that may not be content)
								if ($this->config->prune) {
									$this->debug('Pruning content');
									$this->readability->prepArticle($this->body);
								}
								$detect_body = false;
							} else {
								$this->debug('hNews: skipping entry-content - appears not to contain content');
							}
							unset($e);
						} else {
							$this->body = $this->readability->dom->createElement('div');
                                                        //echo "elems->item: [" . $this->body->innerHTML . "]\n\n";
							$this->debug($elems->length.' entry-content elems found');
							foreach ($elems as $elem) {
								if (!isset($elem->parentNode)) continue;
								$isDescendant = false;
								foreach ($this->body->childNodes as $parent) {
									if ($this->isDescendant($parent, $elem)) {
										$isDescendant = true;
										break;
									}
								}
								if ($isDescendant) {
									$this->debug('Element is child of another body element, skipping.');
								} else {
									// prune (clean up elements that may not be content)
									if ($this->config->prune) {
										$this->debug('Pruning content');
										$this->readability->prepArticle($elem);
									}								
									$this->debug('Element added to body');									
									$this->body->appendChild($elem);
								}
							}
                                                        echo "elems->item: [" . $this->body->innerHTML . "]\n\n";
							$detect_body = false;
						}
					}
				}
			}
		}
                //echo "elems->item: [" . $this->body->innerHTML . "]\n\n";

		// check for elements marked with instapaper_title
		if ($detect_title) {
			// check for instapaper_title
			$elems = @$xpath->query("//*[contains(concat(' ',normalize-space(@class),' '),' instapaper_title ')]", $this->readability->dom);
			if ($elems && $elems->length > 0) {
				$this->debug('title found (.instapaper_title)');
				$this->title = $elems->item(0)->textContent;
				// remove title from document
				$elems->item(0)->parentNode->removeChild($elems->item(0));
				$detect_title = false;
			}
		}
		// check for elements marked with instapaper_body
		if ($detect_body) {
			$elems = @$xpath->query("//*[contains(concat(' ',normalize-space(@class),' '),' instapaper_body ')]", $this->readability->dom);
			if ($elems && $elems->length > 0) {
				$this->debug('body found (.instapaper_body)');
				$this->body = $elems->item(0);
				// prune (clean up elements that may not be content)
				if ($this->config->prune) {
					$this->debug('Pruning content');
					$this->readability->prepArticle($this->body);
				}
				$detect_body = false;
			}
		}
		//echo "after detect_body: [" . $this->body->innerHTML . "]\n\n";
		
		// Find author in rel="author" marked element
		// We only use this if there's exactly one.
		// If there's more than one, it could indicate more than
		// one author, but it could also indicate that we're processing
		// a page listing different articles with different authors.
		if ($detect_author) {
			$elems = @$xpath->query("//a[contains(concat(' ',normalize-space(@rel),' '),' author ')]", $this->readability->dom);
			if ($elems && $elems->length == 1) {
				$this->debug('Author found (rel="author")');
				$author = trim($elems->item(0)->textContent);
				if ($author != '') {
					$this->author[] = $author;
					$detect_author = false;
				}
			}
		}

		// Find date in pubdate marked time element
		// For the same reason given above, we only use this
		// if there's exactly one element.
		if ($detect_date) {
			$elems = @$xpath->query("//time[@pubdate]", $this->readability->dom);
			if ($elems && $elems->length == 1) {
				$this->debug('Date found (pubdate marked time element)');
				$this->date = strtotime(trim($elems->item(0)->textContent));
				// remove date from document
				//$elems->item(0)->parentNode->removeChild($elems->item(0));
				if ($this->date) {
					$detect_date = false;
				} else {
					$this->date = null;
				}
			}
		}

		// still missing title or body, so we detect using Readability
		if ($detect_title || $detect_body) {
			$this->debug('Using Readability');
			// clone body if we're only using Readability for title (otherwise it may interfere with body element)
			if (isset($this->body) && is_object($this->body) ) {
				$this->body = $this->body->cloneNode(true);
			}
			$success = $this->readability->init();
		}
		if ($detect_title) {
			$this->debug('Detecting title');
			$this->title = $this->readability->getTitle()->textContent;
		}
                
                //echo "before detect body success [" . $this->body->innerHTML . "]\n\n";
		if ($detect_body && $success) {
			$this->debug('Detecting body');
			$this->body = $this->readability->getContent();
                        //echo "getContent() : [" . $this->body->innerHTML . "] \n\n" ;
			if ($this->body->childNodes->length == 1 && $this->body->firstChild->nodeType === XML_ELEMENT_NODE) {
				$this->body = $this->body->firstChild;
			}
			// prune (clean up elements that may not be content)
			if ($this->config->prune) {
				$this->debug('Pruning content');
				$this->readability->prepArticle($this->body);
			}
		}
                
                //echo "如果沒有Body [" . $this->body->innerHTML . "]\n\n";
		if (isset($this->body)) {
			// remove scripts
			if (is_object($this->body)) {
				$this->readability->removeScripts($this->body);
			}
			// remove any h1-h6 elements that appear as first thing in the body
			// and which match our title
			if (isset($this->title) && ($this->title != '')) {
				$firstChild = $this->body->firstChild;
				while ($firstChild->nodeType && ($firstChild->nodeType !== XML_ELEMENT_NODE)) {
					$firstChild = $firstChild->nextSibling;
				}
				if (($firstChild->nodeType === XML_ELEMENT_NODE)
					&& in_array(strtolower($firstChild->tagName), array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'))
					&& (strtolower(trim($firstChild->textContent)) == strtolower(trim($this->title)))) {
						$this->body->removeChild($firstChild);
				}
			}
			$this->success = true;
		}
                
                //echo "下一頁之前： [" . $this->body->innerHTML . "]\n\n";
                
                // 20131011 要實作下一頁的偵測！！！
                if (isset($this->body)) {
                    //$elems = @$xpath->query("//a[starts-with(@href, /?p=) and &page=2']", $this->readability->dom);
                    $next_page_pattern = $this->options->next_page_pattern;
                    //echo $next_page_pattern;
                    //$next_page_pattern = "//a[contains(@href, '&page=')]";
                    //$next_page_pattern = "//a";
                    $elems = @$xpath->query($next_page_pattern, $this->readability->dom);
                    
                    ////echo $elems->length;
                    //$link = @$xpath->query("//a[contains(@href, '&page=')]/@href", $this->readability->dom);
                    //if ($link, $) {
                    if ($elems && $elems->length > 0) {
                        try {
                            @$elem = $this->readability->dom->createElement('div', $elems->item(0)->getAttribute("href"));
                        }
                        catch (Exception $e) {
                            
                        } 
                        
                        $elem = $this->readability->dom->createElement('div', "aaa");
                        
                        
                        $attributes = $elems->item($elems->legnth)->attributes; 
                        $href = $attributes->getNamedItem("href")->value;
                        
                        if (substr($href, 0, 4) !== "http") {
                            //echo $href;
                            $url_component = parse_url($url);
                            //$href = urlencode($href);
                            //$elem = $this->readability->dom->createElement('div', $href);

                            //$this->body = $elem;

                            $permalink = $url_component["scheme"]."://".$url_component["host"].$href;
                        }
                        else {
                            $permalink = $href;
                        }
                        //$permalink = $this->getNextPagePermalink($elems);
                        
                        //echo $permalink;
                        //echo "[[[[".$permalink."]]]]";
                        //$permalink = "http://blog.soft.idv.tw/?p=1606&page=2";
                        
                        
                        $extractor = new ContentExtractor(dirname(__FILE__).'/site_config/custom', dirname(__FILE__).'/site_config/standard');
                        $extractor->fingerprints = $options->fingerprints;
                        
                        $elem = new ContentExtractor($this->path, $this->fallback);
                        $extractor->fingerprints = $this->fingerprints;
                        
                        $http = new HumbleHttpAgent();
                        $response = $http->get($permalink, true);
                        //echo 'status_code: '. $response['status_code'] . "\n\n";
                        if ($permalink && ($response = $http->get($permalink, true)) 
                                && ($response['status_code'] < 300 || $response['status_code'] > 400)) {
                            $html = $response['body'];
                            //echo "html: " .$html;
                            // remove strange things
                            $html = str_replace('</[>', '', $html);
                            $html = convert_to_utf8($html, $response['headers']);
                            $extract_result = $extractor->process($html, $permalink);
                            //$readability = $extractor->readability;
                            $content_block = ($extract_result) ? $extractor->getContent() : null;
                            //echo "content_block->innerHTML: ". $content_block->innerHTML . "\n\n";
                            //$this->body->appendChild($elem);
                            
                        }
                        
                        //$doc = $this->readability->dom->("<node>".$content_block->C14N()."</node>");
                        //$content = $content_block->
                        //$content = $this->readability->dom->createElement('div', $content_block->innerHTML);
                        
                        $doc = new DOMDocument();
                        if (@$doc->loadHTML($content_block->innerHTML)) {
                            $doc->saveHTML();
                            //$content = $this->readability->dom->loadHTML($content_block->innerHTML);
                            $content = $this->readability->dom->createElement('div', $content_block->innerHTML);
                            $content = $this->readability->dom->importNode($content_block, true);
                            $this->body->appendChild($content);
                            //$this->body->appendChild($doc);

                            //$xpath = new DOMXPath($this->readability->dom);
                            //$elems = @$xpath->query($extract_pattern, $content_block);
                            //$this->body->appendChild($content_block);
                            //$this->body = $content_block;
                        }
                    }
                    
                }
		
		// if we've had no success and we've used tidy, there's a chance
		// that tidy has messed up. So let's try again without tidy...
		if (!$this->success && $tidied && $smart_tidy) {
			$this->debug('Trying again without tidy');
			$this->process($original_html, $url, false);
		}

		return $this->success;
	}
	
	private function isDescendant(DOMElement $parent, DOMElement $child) {
		$node = $child->parentNode;
		while ($node != null) {
			if ($node->isSameNode($parent))	return true;
			$node = $node->parentNode;
		}
		return false;
	}

	public function getContent() {
		return $this->body;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function getAuthors() {
		return $this->author;
	}
	
	public function getLanguage() {
		return $this->language;
	}
	
	public function getDate() {
		return $this->date;
	}
	
	public function getSiteConfig() {
		return $this->config;
	}
        
        public function getMatchedBody($elems) {
            $this->debug('Body matched');
            $body;
            if ($elems->length == 1) {				
                    $body = $elems->item(0);
                    // prune (clean up elements that may not be content)
                    if ($this->config->prune) {
                            $this->debug('Pruning content');

                            /**
                             * @version 20140428 Pudding Chen
                             * 禁止重新整理
                             */
                            //$this->readability->prepArticle($this->body);
                    }
                    //break;
            } else {
                    $body = $this->readability->dom->createElement('div');
                    $this->debug($elems->length.' body elems found');
                    foreach ($elems as $elem) {
                            if (!isset($elem->parentNode)) continue;
                            $isDescendant = false;
                            foreach ($body->childNodes as $parent) {
                                    if ($this->isDescendant($parent, $elem)) {
                                            $isDescendant = true;
                                            break;
                                    }
                            }
                            if ($isDescendant) {
                                    $this->debug('Element is child of another body element, skipping.');
                            } else {
                                    // prune (clean up elements that may not be content)
                                    if ($this->config->prune) {
                                            $this->debug('Pruning content');
                                            /**
                                             * @version 20140428 Pudding Chen
                                             * 禁止重新整理
                                             */
                                            //$this->readability->prepArticle($elem);
                                    }
                                    $this->debug('Element added to body');
                                    $body->appendChild($elem);
                            }
                    }
            }
            
            return $body;
        }
        
        public function retrieveNextPage($xpath, $url) {
            /**
             * @author Pudding 20170423
             * 停止使用
             */
            return "";
            
            //$xpath = new DOMXPath($body);
            $next_page_pattern = $this->options->next_page_pattern;
            $next_page_elems = $xpath->query($next_page_pattern, $this->body);
            
            $content = FALSE;
            if ($next_page_elems && $next_page_elems->length > 0) {
                
                $permalink = $this->getNextPagePermalink($next_page_elems, $url);
                                
                $content = $this->readability->dom->createElement('div', "未完待續");
                $content = $this->extractContentBlock($permalink);
                if ($content !== FALSE) {
                    $this->body->appendChild($content);
                }
                //echo $content->innerHTML;
            }
            return $content;
        }
        
        public function getNextPagePermalink($elems, $url) {
             //$elem = $this->readability->dom->createElement('div', $elems->item(0)->getAttribute("href"));

            $attributes = $elems->item($elems->legnth)->attributes; 
            $href = $attributes->getNamedItem("href")->value;

            if (substr($href, 0, 4) !== "http") {
                //echo $href;
                $url_component = parse_url($url);
                //$href = urlencode($href);
                //$elem = $this->readability->dom->createElement('div', $href);

                //$this->body = $elem;

                $permalink = $url_component["scheme"]."://".$url_component["host"].$href;
            }
            else {
                $permalink = $href;
            }
            
            return $permalink;
        }
        
        public function extractContentBlock($permalink) {
                        $extractor = new ContentExtractor(dirname(__FILE__).'/site_config/custom', dirname(__FILE__).'/site_config/standard');
                        //$extractor = $this;
                        
                        $extractor->next_page_deep_count = $this->next_page_deep_count + 1;
                        $extractor->next_pages = $this->next_pages;
                        if (in_array($permalink, $extractor->next_pages)) {
                            return FALSE;
                        }
                        $extractor->next_pages[] = $permalink;
                        
                        if ($extractor->next_page_deep_count > 3) {
                            return FALSE;
                        }
            
                        $extractor->fingerprints = $this->options->fingerprints;
                        
                        $elem = new ContentExtractor($this->path, $this->fallback);
                        $extractor->fingerprints = $this->fingerprints;
                        
                        $http = new HumbleHttpAgent();
                        $response = $http->get($permalink, true);
            
                        //echo 'status_code: '. $response['status_code'] . "\n\n";
                        
                        if ($permalink && ($response = $http->get($permalink, true)) 
                                && ($response['status_code'] < 300 || $response['status_code'] > 400)) {
                            $html = $response['body'];
                            //echo "html: " .$html;
                            // remove strange things
                            $html = str_replace('</[>', '', $html);
                            $html = convert_to_utf8($html, $response['headers']);
                            
                            if (function_exists('mb_convert_encoding')) {
                                $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
                            }
                            
                            $extract_result = $extractor->process($html, $permalink);
                            //$readability = $extractor->readability; 
                            
                            $content_block = ($extract_result) ? $extractor->getContent() : null;
                            
                            //echo "content_block->innerHTML: ". $content_block->innerHTML . "\n\n";
                            //$this->body->appendChild($elem);
                        }
                        
            $doc = new DOMDocument();
                        if (@$doc->loadHTML($content_block->innerHTML)) {
                            $doc->saveHTML();
                            //$content = $this->readability->dom->loadHTML($content_block->innerHTML);
                            $content = $this->readability->dom->createElement('div', $content_block->innerHTML);
                            $content = $this->readability->dom->importNode($content_block, true);
                            
                            return $content;
                        }
                        else {
                            return FALSE;
                        }
            return FALSE;
            //return $content_block;
        }
}
?>