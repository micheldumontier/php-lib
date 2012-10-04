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
 * Application class
 * @version 1.0
 * @author Michel Dumontier
 * @description 
*/
require('utils.php');
require('fileapi.php');

function error_handler($level, $message, $file, $line, $context) {
	//Handle user errors, warnings, and notices ourself	
	// if(_DEBUG_) {
		if($level === E_USER_ERROR) {
			debug_print_backtrace();
			return(true); //And prevent the PHP error handler from continuing
		} else if($level === E_USER_WARNING) {
			echo "**Warning** $message";
			return (true);
		} else if($level === E_USER_NOTICE) {
			echo $message.PHP_EOL;
			return(true);
		}
	//}
	return(false); //Otherwise, use PHP's error handler
}
	
	
class Application
{
	private $name = '';
	private $parameters = '';
	
	public function __construct() 
	{
		
	}
	/**
	 * Add a software parameter
	 *
	 * @version     1.0
	 * @author      Michel Dumontier <michel.dumontier@gmail.com>
	 * @param		string	$name		The name of the parameter to set
	 * @param		string	$list		A restricted list of potential values
	 * @param		string	$default	The default value for the parameter
	 * @param		bool	$mandatory	Whether the parameter must be set by the user
	 * @param		string	$description	A description of the parameter
	 * @return      bool     Returns TRUE on success, FALSE on failure
	*/
	public function AddParameter($key, $mandatory = false, $list = '', $default = '', $description = '')
	{
		if(!isset($key) || $key == '') {
			trigger_error('Please specify a parameter name', E_USER_ERROR);
			return FALSE;
		}
		if($mandatory != true && $mandatory != false) {
			trigger_error('mandatory setting must either be true or false', E_USER_ERROR);
			return FALSE;
		}
		$this->parameters[$key] = array('mandatory' => $mandatory, 'list' => $list, 'default' => $default, 'description' => $description);
		return TRUE;
	}
	
	/**
	 * Set parameters from command line arguments
	 *
	 * @version     1.0
	 * @author      Michel Dumontier <michel.dumontier@gmail.com>
	 * @param       object   $argv    The command line arguments
	 * @return      bool     Returns TRUE on success, FALSE on failure
	*/
	public function SetParameters($argv)
	{
		// get rid of the script argument
		$this->name = $argv[0];
		array_shift ($argv);
		
		// build a new parameter - value array
		foreach($argv AS $value) {
			list($key,$value) = explode("=",$value);
			if(!isset($this->parameters[$key])) {
				trigger_error("Invalid parameter - $key", E_USER_WARNING);
				return FALSE;
			}
			if($value == '') {
				trigger_error("No value for mandatory parameter $key", E_USER_WARNING);
				return FALSE;
			}
			$myargs[$key] = $value;
		}

		// now iterate over all parameters in the option block and set their user/default value
		foreach($this->parameters AS $key => $a) {
			if(isset($myargs[$key])) {
				// use the supplied value
				$user_entries = explode(",",$myargs[$key]);
				// check that each is a valid choice
				if($this->parameters[$key]['list']) {
					$m = explode('|',$this->parameters[$key]['list']);
					foreach($user_entries AS $user_entry) {
						if(!in_array($user_entry,$m)) {
							trigger_error("$user_entry for $key parameter does not match any of the listed options: ".$this->parameters[$key]['list'], E_USER_WARNING);
							return FALSE;
						}
					}
				}
				$this->parameters[$key]['value'] = $myargs[$key];				
			} else if(!isset($myargs[$key]) && $this->parameters[$key]['mandatory']) {
				trigger_error("$key is a mandatory argument!", E_USER_WARNING);
				return FALSE;
			} else {
				// use the default
				$this->parameters[$key]['value'] = $this->parameters[$key]['default'];
			}
			if($this->parameters[$key]['value'] === 'true')  $this->parameters[$key]['value'] = true;
			if($this->parameters[$key]['value'] === 'false') $this->parameters[$key]['value'] = false;
		}
		return TRUE;
	}
	
	public function SetParameterValue($key,$value)
	{
		$this->parameters[$key]['value'] = $value;
	}
	
	public function GetParameterValue($key) 
	{
		if(!isset($this->parameters[$key])) {
			trigger_error("Invalid parameter - $key", E_USER_ERROR);
			return FALSE;
		}
		return $this->parameters[$key]['value'];
	}
	
	public function GetParameterList($key) 
	{
		if(!isset($this->parameters[$key])) {
			trigger_error("Invalid parameter - $key", E_USER_ERROR);
			return FALSE;
		}
		return $this->parameters[$key]['list'];
	}
	
	public function PrintParameters()
	{
		echo PHP_EOL;
		echo "Usage: php ".$this->name.PHP_EOL;
		echo "  Allowed or mandatory (*) parameters and their restricted and default values".PHP_EOL;
		foreach($this->parameters AS $key => $a) {
			echo '  ';
			if($a['mandatory'] == true) echo "*";
			echo $key."=";
			if($a['list'] != '') echo $a['list'];
			if($a['description'] != '') echo PHP_EOL.'    description: '.$a['description'];
			if($a['default'] != '') echo PHP_EOL.'    default='.$a['default'];
			echo PHP_EOL;
		}
		return TRUE;
	}
	
	public function CreateDirectory($dir)
	{
		if(!is_dir($dir)) {
			if(@mkdir($dir,0777,true) === FALSE) {
				trigger_error("Unable to create $dir");
				return FALSE;
			}
		}
		return TRUE;
	}
	
	public function ProgressMeter($current,$total,$percent_interval, $fnx)
	{
		$check = round($percent_interval/100*$total);
		if($current % $check == 0) {
			$fnx();
			echo round($check/$current*100)."% ...";
		}
	}
}
