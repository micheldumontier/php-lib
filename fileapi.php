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
 * File Object
 * @version 1.0
 * @author Michel Dumontier
 * @description 
*/
class FileFactory
{
	private $filename = null;
	private $fp = null;
	
	function __construct($filename, $gzcompress = false)
	{
		if(!isset($filename) || $filename == '') {
			trigger_error("Invalid filename");
			return FALSE;
		}
		if($gzcompress !== true && $gzcompress !== false) {
			trigger_error("gzcompress value can only be true or false");
			return FALSE;
		}
		$this->gzcompress = $gzcompress;
		$this->filename = $filename;
		return $this;
	}
	
	function open($mode = "r")
	{
		if($this->gzcompress == true) {
			if($mode == "r") $mode = "rb";
			if($mode == "w") $mode = "wb";
			$this->fp = fopen("compress.zlib://".$this->filename,$mode);
		} else {
			$this->fp = fopen($this->filename,$mode);
		}
		if(FALSE === $this->fp) {
			trigger_error("Unable to open ".$this->filename,E_USER_ERROR);
			return FALSE;
		}
		return TRUE;
	}
	
	function getFileName(){
			return (string) $this->filename;
	}
	
	function setFilePointer($fp)
	{
		$this->fp = $fp;
		return $this;
	}
	function getFilePointer()
	{
		return $this->fp;
	}
	
	function read($size = null)
	{
		if(!isset($this->fp)) {
			$this->Open("r");
		}
		if(isset($size)) {
			return fgets($this->fp,$size);
		}
		return fgets($this->fp);
	}
	function write($buf)
	{
		if(!isset($this->fp)) {
			$this->Open("w");
		}
		return fwrite($this->fp,$buf);
	}
	function close()
	{
		$ret = TRUE;
		if(isset($this->fp)) {
			$ret = fclose($this->fp);
			$this->fp = null;
		}
		return $ret;
	}

}
?>
