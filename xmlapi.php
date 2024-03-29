<?php
/**
Copyright (C) 2012 Michel Dumontier

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

/**
 * An XML API for PHP
 * @version 1.0
 * @author Michel Dumontier
 * @description 
*/

class CXML 
{
	private $fp;
	private $xmlroot = '';
	private $header = '';

	private $zip_basename;

	private $z; 
	
	function __construct($filepath,$file = null) 
	{
		if(strstr($filepath,".zip")) {
			$this->z = new ZipArchive();
			if ($this->z->open($filepath) == FALSE) {
				trigger_error("Unable to open $filepath", E_USER_ERROR);
				return FALSE;
			}
			if(!isset($file)) {
				$nozip = substr($filepath,0,strrpos($filepath,".zip"));
				$zip_basename = basename($nozip); // Only filename, relative to archive, not file-system is used.
			} else $zip_basename = $file;
			$this->zip_basename = $zip_basename;
			$this->fp = $this->z->getStream($zip_basename); 
			if($this->fp === FALSE) {
				trigger_error("unable to open $filepath",E_USER_ERROR);
				exit;
			}
		} else {
			$this->fp = gzopen($filepath,"r");
			if($this->fp === FALSE) {
				trigger_error("unable to open $filepath", E_USER_ERROR);
				exit;
			}
		}
	}
	
	function __destruct()
	{
		gzclose($this->fp);
	}
	
	function Parse($elementToParse = null)
	{
		$level = 0;
		$content = '';
		$body = '';
		$parsing = false;
		while(($l = gzgets($this->fp, 80000)) !== FALSE) {
			if($elementToParse == null) {$content .= $l; continue;}
			else if($this->header == '') {$this->header = trim($l); continue;}
			else if($this->header != '' && strstr($l,"?>")) {
				$this->header .= " ".trim($l); continue;} // still some xml header stuff 
			if(!isset($exception)) {
				$this->header .= PHP_EOL;
			}
			$exception = false;
			if( strstr($l,"<".$elementToParse.">") && strstr($l,"</".$elementToParse.">")) {
				$exception = true;
			}
			if($exception == false && $parsing == true && strstr($l,"</".$elementToParse.">") && $level == 1) {
				$body .= $l;
				$this->xmlroot = simplexml_load_string($this->header.$body, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);
				if($this->xmlroot === FALSE) {
					trigger_error("Error in loading XML");
					foreach(libxml_get_errors() as $error) {
						echo "\t", $error->message;
					}	
				}
				$body = ''; 
				$parsing = false;
				return TRUE;
			} else {
				if($parsing == true) {
					if(strstr($l,"<$elementToParse>") || strstr($l,"<$elementToParse ") || strstr($l,"<$elementToParse\n") || strstr($l,"<$elementToParse\r\n")) $level++;
					if(strstr($l,"</".$elementToParse.">")) $level--;
					$body .= $l;
				} else {
					if(strstr($l,"<$elementToParse>") || strstr($l,"<$elementToParse ") || strstr($l,"<$elementToParse\n") || strstr($l,"<$elementToParse\r\n")) {
						$parsing = true;
						$body .= $l;
						$level ++;
					}
				}
			}
		}
		
		if($elementToParse == null) {
			$this->xmlroot = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);
			if($this->xmlroot === FALSE) {
				trigger_error("Error in loading XML");
				foreach(libxml_get_errors() as $error) {
					echo "\t", $error->message;
				}	
			}
		}
	}
	
	function GetXMLRoot()
	{
		return $this->xmlroot;
	}
	
	function GetAttributeValue($node, $name)
	{
		return($node->attributes()->$name);
	}
	
}
