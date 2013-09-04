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


require_once('application.php');
require_once('registry.php');

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
	
	public function __construct()
	{
		parent::__construct();
		$this->registry = new CRegistry();
	}
	
	/** 
	 * Get the namespace object
	 * @return object the namespace object
	 */
	public function getRegistry() 
	{
		return $this->registry;
	}
	
	/** get the RDF buffer */
	public function getRDF() {return $this->buf;}
	/** add RDF to the string buffer */
	public function addRDF($buf) {$this->buf .= $buf;return TRUE;}
	/** clear the RDF string buffer */
	public function hasRDF(){if($this->buf != '') return TRUE;return FALSE;}
	public function deleteRDF() {$this->buf = '';return TRUE;}

	/** Set the default graph URI in order to generate quads
	 * @param string $graph_uri The Graph URI to set
	 */
	public function setGraphURI($graph_uri) {$this->graph_uri = $graph_uri;}
	/** Get the default graph URI
	 * @return string the default graph uri
	 */
	public function getGraphURI() {return $this->graph_uri;}
	
	/** Set the read file */
	public function setReadFile($file,$gzcompress=false)
	{
		$this->read_file = new FileFactory($file,$gzcompress);
		return $this->read_file;
	}
	/** get the read file */
	public function getReadFile()
	{	
		return $this->read_file;
	}
	/** set a base write file pattern */
	public function setWriteFilePath($path)
	{
		$this->writeFilePath = $path;
		return $this;
	}
	public function getWriteFilePath() {return $this->writeFilePath;}
	
	/** ask if the write file is set */
	function writeFileExists()
	{
		if(isset($this->write_file)) return TRUE;
		return FALSE;
	}
	/** set the write file and mode */
	public function setWriteFile($file,$gzcompress=false)
	{
		$this->write_file = new FileFactory($file,$gzcompress);
		return $this->write_file;
	}
	/** get the write file */
	public function getWriteFile()
	{
		return $this->write_file;
	}
	/** write the RDF buffer to the write file */
	public function writeRDFBufferToWriteFile() 
	{
		if($this->writeFileExists() === FALSE) {
			trigger_error("Write file not set!");
			return FALSE;
		} 
		$this->getWriteFile()->write($this->buf);
		$this->deleteRDF();
		return TRUE;
	}
	
	/** Generate a n-triple or n-quad */
	public function Quad($s_uri, $p_uri, $o_uri, $g_uri = null)
	{
		$graph_uri = '';
		if(isset($g_uri)) $graph_uri = "<$g_uri>";
		elseif(isset($this->graph_uri) && $this->graph_uri != '') $graph_uri = "<".$this->graph_uri.">";
		
		return "<$s_uri> <$p_uri> <$o_uri> $graph_uri .".PHP_EOL;
	}

	/** Generate a n-triple or n-quad with a literal value */
	public function QuadL($s_uri, $p_uri, $literal, $lang = null, $lt_uri = null, $g_uri = null)
	{
		if(!is_string($literal)) {
			trigger_error("\$literal is not a literal",E_USER_ERROR);
			return null;
		}
		$l = $this->safeLiteral($literal);
		$graph_uri = '';
		if(isset($g_uri)) $graph_uri = "<$g_uri>";
		elseif(isset($this->graph_uri) && $this->graph_uri != '') $graph_uri = "<".$this->graph_uri.">";
		return "<$s_uri> <$p_uri> \"$l\"".(isset($lang)?"@$lang ":'').((!isset($lang) && isset($lt_uri))?"^^<$lt_uri>":'')." $graph_uri .".PHP_EOL;
	}
	
	/** Generate a n-triple or n-quad using registry qualified names (qname) for the subject, predicate and object */
	public function QQuad($s,$p,$o,$g = null)
	{
		if(strstr($s,"://")) $s_uri = $s;
		else $s_uri = $this->getRegistry()->getFQURI($s);
		
		if(strstr($p,"://")) $p_uri = $p;
		else $p_uri = $this->getRegistry()->getFQURI($p);
		
		if(strstr($o,"://")) $o_uri = $o;
		else $o_uri = $this->getRegistry()->getFQURI($o);
		
		$g_uri = null;
		if(isset($g)) $g_uri = $this->getRegistry()->getFQURI($g);
		else if(isset($this->graph_uri) && $this->graph_uri != '') {
			$g_uri = $this->getRegistry()->getFQURI($this->graph_uri);
		}
		
		return $this->Quad($s_uri,$p_uri,$o_uri,$g_uri);
	}
	
	/** Generate a n-triple or n-quad with literal value using registry qualified names (qname) for the subject and predicate */
	public function QQuadL($s,$p,$l,$lang=null,$lt=null,$g=null)
	{
		if(strstr($s,"://")) $s_uri = $s;
		else $s_uri = $this->getRegistry()->getFQURI($s);
		
		if(strstr($p,"://")) $p_uri = $p;
		else $p_uri = $this->getRegistry()->getFQURI($p);
		
		$lt_uri = null;
		if(isset($lt)) {
			if(strstr($lt,"://")) $lt_uri = $lt;
			else $lt_uri = $this->getRegistry()->getFQURI($lt,"provider-uri");		
		}
		
		$g_uri = null;
		if(isset($g)) $g_uri = $this->getRegistry()->getFQURI($g);
		else if(isset($this->graph_uri) && $this->graph_uri != '') {
			$g_uri = $this->getRegistry()->getFQURI($this->graph_uri);
		}
		
		return $this->QuadL($s_uri,$p_uri,$l,$lang,$lt_uri,$g_uri);		
	}
	
	/** Generate a n-triple or n-quad with a fully qualified uri as the object and a qname subject and predicate  */
	public function QQuadO_URL($s,$p,$o_uri,$g=null) 
	{
		if(strstr($s,"://")) $s_uri = $s;
		else $s_uri = $this->getRegistry()->getFQURI($s);
		$p_uri = $this->getRegistry()->getFQURI($p);
		$g_uri = null;
		if(isset($g)) $g_uri = $this->getRegistry()->getFQURI($g);
		else if(isset($this->graph_uri) && $this->graph_uri != '') {
			$g_uri = $this->getRegistry()->getFQURI($this->graph_uri);
		}
		
		return $this->Quad($s_uri,$p_uri,$o_uri,$g_uri);
	}

	/** Generate a safe literal using a special escape */
	public static function safeLiteral($s)
	{
		$s_noslash = stripslashes($s);
		return addcslashes($s_noslash, "\\\'\"\n\r\t");
	}
	
	/** the special escape for n-triples */
	public static function specialEscape($str){
		$s_noslash = stripslashes($str);
		return addcslashes($s_noslash, "\\\'\"\n\r\t");
	}

}
