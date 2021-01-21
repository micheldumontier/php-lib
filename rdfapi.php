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
	private $testIRI = TRUE;
	private $safeIRI = FALSE;
	
	public function __construct()
	{
		parent::__construct();
		$this->registry = new CRegistry();
	}


	/** set a boolean flag (true/false) as to whether to make a FQURI safe */
	public function setSafeIRI($bool)
	{	$this->safeIRI = $bool; }
	/** get the status of the boolean flag as to whether to make a FQURI safe */
	public function getSafeIRI()
	{	return $this->safeIRI; }

	public function setTestIRI($bool)
	{	$this->testIRI = $bool;}

	public function getTestIRI()
	{	return $this->testIRI; }
	
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
		if(!isset($g_uri) and $this->graph_uri != '') $g_uri = $this->graph_uri;
		
		if($this->safeIRI === TRUE) {
			$s_uri = $this->makeSafeIRI($s_uri);
			$p_uri = $this->makeSafeIRI($p_uri);
			$o_uri = $this->makeSafeIRI($o_uri);
			$g_uri = $this->makeSafeIRI($g_uri); 
		}

		if($this->testIRI === TRUE) {
			$this->checkIRI($s_uri);
			$this->checkIRI($p_uri);
			$this->checkIRI($o_uri);
			if(isset($g_uri)) $this->checkIRI($g_uri);
		}

		return "<$s_uri> <$p_uri> <$o_uri> ".(isset($g_uri)?"<$g_uri> ":"").".".PHP_EOL;
	}

	/** Generate a n-triple or n-quad with a literal value */
	public function QuadL($s_uri, $p_uri, $literal, $lang = null, $lt_uri = null, $g_uri = null)
	{
		if(!is_string($literal)) {
			trigger_error("\$literal is not a literal",E_USER_ERROR);
			return null;
		}
		$l = $this->safeLiteral($literal);
		if(!isset($g_uri) and $this->graph_uri != '') $g_uri = $this->graph_uri;

		if($this->safeIRI === TRUE) {
			$s_uri = $this->makeSafeIRI($s_uri);
			$p_uri = $this->makeSafeIRI($p_uri);
			$g_uri = $this->makeSafeIRI($g_uri); 
		}

		if($this->testIRI === TRUE) {
			$this->checkIRI($s_uri);
			$this->checkIRI($p_uri);
			if(isset($g_uri)) $this->checkIRI($g_uri);
		}
		return "<$s_uri> <$p_uri> \"$l\"".(isset($lang)?"@$lang ":'').((!isset($lang) && isset($lt_uri))?"^^<$lt_uri>":'').(isset($g_uri)?"<$g_uri> ":"").".".PHP_EOL;
	}
	
	/** Generate a n-triple or n-quad using registry qualified names (qname) for the subject, predicate and object */
	public function QQuad($s,$p,$o,$g = null)
	{
		if($s == null or $s == '' or $p == null or $p == '' or $o == null or $o == '') return '';
		
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
		if($s == null or $s == '' or $p == null or $p == '' or $l == null or $l == '') return '';
		
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
	/*
	https://www.w3.org/TR/n-triples/#grammar-production-STRING_LITERAL_QUOTE
	STRING_LITERAL_QUOTE 	::= 	'"' ([^#x22#x5C#xA#xD] | ECHAR | UCHAR)* '"'
	ECHAR 	::= 	'\' [tbnrf"'\]
	UCHAR 	::= 	'\u' HEX HEX HEX HEX | '\U' HEX HEX HEX HEX HEX HEX HEX HEX
	HEX 	::= 	[0-9] | [A-F] | [a-f]

	#x5C = \ - reverse solidus (ascii 92)
	#x22 = " - quotation mark (ascii 34)
	#xA = \n - line feed (ascii 10)
	#xD = \r - carriage return  (ascii 13)

	### notes
	* despite the specification, most n-triple parsers barf when the single quote ' is escaped in a double quoted literal
    * @todo validate hex encoding
	*/	
	public static function safeLiteral($str)
	{
		$patterns = array(
			'/\\\\/', '/[\x5c]/', '/[\x22]/', '/[\xA]/', '/[\xD]/', '/[\b]/', '/[\t]/', '/[\f]/'
		);
		$replacements = array(
			'/\\\\\\/', '\\', '\"', '\n', '\r', '\b', '\t', '\f'
		);
		$str = preg_replace($patterns, $replacements, $str); 
		return $str;
	}
	
	/** the special escape for n-triples */
	public static function specialEscape($str){
		return safeLiteral($str);
	}

	/** implode the parsed url array */
	function unparse_url($parsed_url) {
		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		$pass     = ($user || $pass) ? "$pass@" : '';
		$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
		$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
		return "$scheme$user$pass$host$port$path$query$fragment";
	} 

	/** construct a safe IRI */
	function makeSafeIRI($str)
	{
		if(!isset($str)) return null;
		$p = parse_url(trim($str));
		if($p === false) {
			trigger_error("Error in parsing $str as URL", E_USER_ERROR);
			return false;
        }

		$p['path']     = isset($p['path'])?     str_replace('%2F','/',urlencode($p['path'])) : null;
		$p['query']    = isset($p['query'])?    str_replace('%2F','/',urlencode($p['query'])) : null;
		$p['fragment'] = isset($p['fragment'])? str_replace('%2F','/',urlencode($p['fragment'])) : null;

		return $this->unparse_url($p);
    }
	
	/** check if an IRI is valid */
	function checkIRI($str)
	{
		$url_regex = "/(([a-zA-Z][0-9a-zA-Z+\-\.]*:)?\/{0,2}[0-9a-zA-Z;\/?:@&=+$\.\-_!~*'()%]+)?(#[0-9a-zA-Z;\/?:@&=+$\.\-_!~*'()%]+)?/";
		preg_match($url_regex,$str, $match);
		if($match[0] != $str) {
			trigger_error("IRI $str is not valid!",E_USER_ERROR);	
			return FALSE;
		}
		return TRUE;
	}
}
