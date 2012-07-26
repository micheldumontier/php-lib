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
 * An RDF API for PHP
 * @version 1.0
 * @author Michel Dumontier
 * @description 
*/
require_once('application.php');
require_once('ns.php');
//require_once('/code/arc2/ARC2.php');

class RDFFactory extends Application
{
	private $buf = '';
	private $ns = null;
	private $types = null;
	
	function __construct()
	{
		parent::__construct();
		$this->ns = new CNamespace();
	}
	
	function GetNS() {return $this->ns;}
	
	function GetRDF() {return $this->buf;}
	function AddRDF($buf) {$this->buf .= $buf;return TRUE;}
	function DeleteRDF() {$this->buf = '';return TRUE;}
	function WriteRDF($fp) {
		fwrite($fp,$this->buf);
		$this->DeleteRDF();
		return TRUE;
	}
	
	
	function Quad($s_uri, $p_uri, $o_uri, $g_uri = null)
	{
		return "<$s_uri> <$p_uri> <$o_uri> ".(isset($g_uri)?"<$g_uri>":"")." .".PHP_EOL;
	}

	function QuadL($s_uri, $p_uri, $literal, $lang = null, $lt_uri = null, $g_uri = null)
	{
		if(isset($lang) && isset($lt_uri)) {
			trigger_error("Literal can only hold a language tag *or* datatype", E_USER_ERROR);
			return FALSE;
		}
		return "<$s_uri> <$p_uri> \"$literal\"".(isset($lang)?"@$lang ":'').(isset($lt_uri)?"^^<$lt_uri>":'').(isset($g_uri)?" <$g_uri>":'')." .".PHP_EOL;
	}
	
	function QuadText($s_uri, $p_uri, $text, $lang = null, $g_uri = null)
	{
		if(isset($lang) && isset($lt_uri)) {
			trigger_error("Literal can only hold a language tag *or* datatype", E_USER_ERROR);
			return FALSE;
		}
		return "<$s_uri> <$p_uri> \"\"\"$text\"\"\"".(isset($lang)?"@$lang ":'').(isset($g_uri)?" <$g_uri>":'')." .".PHP_EOL;
	}
	
	
	function QQuad($s,$p,$o,$g = null)
	{
		$s_uri = $this->ns->getFQURI($s);
		$p_uri = $this->ns->getFQURI($p);
		$o_uri = $this->ns->getFQURI($o);
		$g_uri = null;
		if(isset($g)) $g_uri = $this->ns->getFQURI($g);
		
		return $this->Quad($s_uri,$p_uri,$o_uri,$g_uri);
	}
	
	function QQuadL($s,$p,$l,$lang=null,$lt=null,$g=null)
	{
		$s_uri = $this->ns->getFQURI($s);
		$p_uri = $this->ns->getFQURI($p);
		
		$lt_uri = null;
		if(isset($lt)) $lt_uri = $this->ns->getFQURI($lt);		
		$g_uri = null;
		if(isset($g)) $g_uri = $this->ns->getFQURI($g);
		
		return $this->QuadLiteral($s_uri,$p_uri,$l,$lang,$lt_uri,$g_uri);		
	}
	
	function QQuadText($s,$p,$l,$lang=null,$g=null)
	{
		$s_uri = $this->ns->getFQURI($s);
		$p_uri = $this->ns->getFQURI($p);
		
		$lt_uri = null;
		if(isset($lt)) $lt_uri = $this->ns->getFQURI($lt);		
		$g_uri = null;
		if(isset($g)) $g_uri = $this->ns->getFQURI($g);
		
		return $this->QuadText($s_uri,$p_uri,$l,$lang,$g_uri);		
	}
	
	function QQuadO_URL($s,$p,$o_uri,$g=null) 
	{
		$s_uri = $this->ns->getFQURI($s);
		$p_uri = $this->ns->getFQURI($p);
		$g_uri = null;
		if(isset($g)) $g_uri = $this->ns->getFQURI($g);
		
		return $this->Quad($s_uri,$p_uri,$o_uri,$g_uri);
	}
	
	function SafeLiteral($s)
	{
		return str_replace(array("\r","\n",'"'),array('','\n','\"'), $s);
	}

	
}

