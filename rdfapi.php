<?php
/**
Copyright (C) 2012-2013 Michel Dumontier

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


require('application.php');
require('registry.php');

/**
 * An RDF API for PHP
 *
 * @author Michel Dumontier 
 * @version 1.0
*/
class RDFFactory extends Application
{
	private $buf = '';
	private $registry = null;
	private $types = null;
	private $read_file=null;
	private $write_file=null;
	private $graph_uri = null;
	private $dataset_uri = null;
	private $declared = null;
	
	function __construct()
	{
		parent::__construct();
		$this->registry = new CRegistry();
	}
	
	
	/** Get the namespace object
	 * @return object the namespace object
	 */
	function getRegistry() 
	{
		return $this->registry;
	}
	
	function GetRDF() {return $this->buf;}
	function AddRDF($buf) {$this->buf .= $buf;return TRUE;}
	function DeleteRDF() {$this->buf = '';return TRUE;}

	/** Set the default graph URI in order to generate quads
	 * @param string $graph_uri The Graph URI to set
	 */
	function SetGraphURI($graph_uri) {$this->graph_uri = $graph_uri;}
	/** Get the default graph URI
	 * @return string the default graph uri
	 */
	function GetGraphURI() {return $this->graph_uri;}
	
	function SetReadFile($file,$gzcompress=false)
	{
		$this->read_file = new FileFactory($file,$gzcompress);
		return $this->read_file;
	}
	function GetReadFile()
	{	
		return $this->read_file;
	}
	function WriteFileExists()
	{
		if(isset($this->write_file)) return TRUE;
		return FALSE;
	}
	function SetWriteFile($file,$gzcompress=false)
	{
		$this->write_file = new FileFactory($file,$gzcompress);
		return $this->write_file;
	}
	function GetWriteFile()
	{
		return $this->write_file;
	}
	function WriteRDFBufferToWriteFile() 
	{
		if($this->WriteFileExists() === FALSE) {
			trigger_error("Write file not set!");
			return FALSE;
		} 
		$this->GetWriteFile()->Write($this->buf);
		$this->DeleteRDF();
		return TRUE;
	}
	
	function Quad($s_uri, $p_uri, $o_uri, $g_uri = null)
	{
		$graph_uri = '';
		if(isset($g_uri)) $graph_uri = "<$g_uri>";
		elseif(isset($this->graph_uri)) $graph_uri = "<".$this->graph_uri.">";
		
		return "<$s_uri> <$p_uri> <$o_uri> $graph_uri .".PHP_EOL;
	}

	function QuadL($s_uri, $p_uri, $literal, $lang = null, $lt_uri = null, $g_uri = null)
	{
		$graph_uri = '';
		if(isset($g_uri)) $graph_uri = "<$g_uri>";
		elseif(isset($this->graph_uri)) $graph_uri = "<".$this->graph_uri.">";
		return "<$s_uri> <$p_uri> \"$literal\"".(isset($lang)?"@$lang ":'').((!isset($lang) && isset($lt_uri))?"^^<$lt_uri>":'')." $graph_uri .".PHP_EOL;
	}
		
	function QQuad($s,$p,$o,$g = null)
	{
		$s_uri = $this->getRegistry()->getFQURI($s);
		$p_uri = $this->getRegistry()->getFQURI($p);
		$o_uri = $this->getRegistry()->getFQURI($o);
		$g_uri = null;
		if(isset($g)) $g_uri = $this->getRegistry()->getFQURI($g);
		
		return $this->Quad($s_uri,$p_uri,$o_uri,$g_uri);
	}
	
	function QQuadL($s,$p,$l,$lang=null,$lt=null,$g=null)
	{
		$s_uri = $this->getRegistry()->getFQURI($s);
		$p_uri = $this->getRegistry()->getFQURI($p);
		
		$lt_uri = null;
		if(isset($lt)) $lt_uri = $this->getRegistry()->getFQURI($lt);		
		$g_uri = null;
		if(isset($g)) $g_uri = $this->getRegistry()->getFQURI($g);
		
		return $this->QuadL($s_uri,$p_uri,$l,$lang,$lt_uri,$g_uri);		
	}
	
	function QQuadO_URL($s,$p,$o_uri,$g=null) 
	{
		$s_uri = $this->getRegistry()->getFQURI($s);
		$p_uri = $this->getRegistry()->getFQURI($p);
		$g_uri = null;
		if(isset($g)) $g_uri = $this->getRegistry()->getFQURI($g);
		
		return $this->Quad($s_uri,$p_uri,$o_uri,$g_uri);
	}

	function SafeLiteral($s)
	{
		return $this->specialEscape($s);
	}
	
	function specialEscape($str){
		$s_noslash = stripslashes($str);
		return addcslashes($s_noslash, "\\\'\"\n\r\t");
	}

}

