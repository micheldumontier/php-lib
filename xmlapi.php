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
	
	function __construct($dir,$file) 
	{
		if(strstr($file,".zip")) {
			$z = new ZipArchive();
			if ($z->open($dir.$file) == FALSE) {
				trigger_error("Unable to open $infile", E_USER_ERROR);
				return FALSE;
			}
			$nozip = substr($file,0,strrpos($file,".zip"));
			$this->fp = $z->getStream($nozip);
		} else {
			$this->fp = gzopen($dir.$file,"r");
			if($this->fp === FALSE) {
				trigger_error("unable to open $dir$file");
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
		$content = '';
		$body = '';
		$parsing = false;
		while(($l = gzgets($this->fp, 80000)) !== FALSE) {
			if($elementToParse == null) {$content .= $l; continue;}
			else if($this->header == '') {$this->header = $l; continue;}
		
			$exception = false;
			if( strstr($l,"<".$elementToParse.">") && strstr($l,"</".$elementToParse.">")) {
				$exception = true;
			}
			if($exception == false && $parsing == true && strstr($l,"</".$elementToParse.">")) {
				$body .= $l;

				$this->xmlroot = simplexml_load_string($this->header.$body);
				$body = ''; 
				$parsing = false;
				return TRUE;
			} else {
				if($parsing == true) {
					$body .= $l;
				} else {
					if(strstr($l,"<$elementToParse>") || strstr($l,"<$elementToParse ")) {
						$parsing = true;
						$body .= $l;
					}
				}
			}
		}
		
		if($elementToParse == null) {
			$this->xmlroot = simplexml_load_string($content);
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