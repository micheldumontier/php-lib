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
 * General Utilities 
 * @version 1.0
 * @author Michel Dumontier
 * @description 
*/

class Utils
{
	public static function OpenReadFile($file)
	{
		if(!file_exists($file)) {
			trigger_error("$in_file does not exist",E_USER_ERROR);
			return FALSE;
		}
		$fp = fopen($file,"r");
		if($fp === FALSE) {
			trigger_error("Unable to open $file",E_USER_ERROR);
			return FALSE;
		}
		return $fp;
	}
	
	public static function OpenWriteFile($file)
	{
		$fp = fopen($file,"w");
		if($fp === FALSE) {
			trigger_error("Unable to open $file to write",E_USER_ERROR);
			return FALSE;
		}
		return $fp;
	}
	
	public static function CloseFile($fp)
	{
		return fclose($fp);
	}

	public static function Download($host,$files,$ldir)
	{
		foreach($files AS $filepath) {
			@mkdir($ldir,null,true);
			Utils::BreakPath($filepath, $dir, $file);

			trigger_error("Downloading $host$dir$file ... ", E_USER_NOTICE);
			if(!copy($host.$dir.$file,$ldir.$file)) {
				$errors= error_get_last();
				trigger_error($errors['type']." : ".$errors['message'], E_USER_ERROR);
				return FALSE;
			} else {
				trigger_error("$file copied to $ldir", E_USER_NOTICE);
			}
		}
		return TRUE;
	} 
	
	public static function FTPDownload($host,$files,$ldir, $user='anonymous',$pass='myemail@email.com',$passive_mode = true)
	{
		$ftp = ftp_connect($host);
		if(!$ftp) {
			trigger_error("Unable to connect to $host");
			return FALSE;
		}
		$login = ftp_login($ftp, $user, $pass);
		if ((!$ftp) || (!$login)) { 
			trigger_error("Unable to login to $host with user:$user and pass:$pass");
			return FALSE;
		} else {
			echo "Connected to $host ...";
		}
		ftp_pasv ($ftp, $passive_mode) ;				
				
		// download
		foreach($files AS $filepath) {
			if(($pos = strrpos($filepath,'/')) === FALSE) {
				$rdir = '';
				$file = $filepath;
			} else {
				$rdir = substr($filepath,0,$pos);
				ftp_chdir($ftp,$rdir);
				$file = substr($filepath,$pos+1);
			}

			echo "Downloading $file ...";
			$ret = ftp_nb_get($ftp, $ldir.$file, $file , FTP_BINARY);
//			while(($ret=ftp_nb_continue($ftp))==FTP_MOREDATA){}
//			while(($ret=ftp_nb_continue($ftp))==FTP_MOREDATA){}
//			while(($ret=ftp_nb_continue($ftp))==FTP_MOREDATA){}
			if ($ret != FTP_FINISHED) {
			   echo "Error in downloading $file...";
				return FALSE;
			}
		}
		if(isset($ftp)) ftp_close($ftp);
		echo "success!".PHP_EOL;
	}
	
	public static function BreakPath($path,&$dir,&$file)
	{
		$rpos = strrpos($path,'/');
		if($rpos !== FALSE) {
			$dir = substr($path,0,$rpos+1);
			$file = substr($path,$rpos+1);
		} else {
			$dir = "";
			$file = $path;
		}
		return TRUE;
	}
	

	public static function GetDirFiles($dir,$pattern = null)
	{
		if(!is_dir($dir)) {
			echo "$dir not a directory".PHP_EOL;
			return 1;
		}
		$files = array();

		$dh = opendir($dir);
		while (($file = readdir($dh)) !== false) {
			if($file == '.' || $file == '..') continue;
			if(isset($pattern)) {
				preg_match($pattern,$file,$m);
				if(isset($m[0])) {
					foreach($m AS $file) {
						$files[] = $file;
					}
				}
			} else {
				$files[] = $file;
			}
		}
		sort($files);
		closedir($dh);
		return $files; 
	}
	
	/**
	 * Download a file from $url and place it in $lfile
	 * This is similar to file_put_contents ($lfile, file_get_contents ($url)),
	 * but with built-in error handling.
	 *
	 * If the target url does not exist, or if the file could not be written,
	 * an error is triggered (E_USER_ERROR).
	 * A file will not be created if the download failed.
	 */
	public static function DownloadSingle ($url, $lfile, $return_on_fail = false, $context = null)
	{
		trigger_error("downloading $url...",E_USER_NOTICE);
		/*
		$context = stream_context_create(
			array(
				'http' => array(
					'header'  => "Authorization: Basic " . base64_encode("$username:$password"),
					'follow_location' => 1,
					'max_redirects' => '20',
					'ignore_errors' => false
				)
		));		
		*/
				
		if(isset($context)) {
			$fpRead = fopen($url,"rb",false, $context);
			# var_dump(stream_get_meta_data($fpRead));
		} else $fpRead = fopen($url, "rb");
		if ($fpRead === false) {
			if($return_on_fail === false) {
				trigger_error ("Unable to get $url", E_USER_WARNING);
				return false;
			} else {
				trigger_error ("Unable to get $url", E_USER_WARNING);
				return false;
			}
		}

		if ( !($fpWrite = fopen($lfile, "wb")) )
		{
			if($return_on_fail === false) {
				trigger_error ("Unable to write to $lfile", E_USER_WARNING);
				return false;
			} else {
				trigger_error ("Unable to get $url", E_USER_WARNING);
				return false;
			}
		}

		stream_copy_to_stream ($fpRead, $fpWrite);

		fclose($fpWrite);
		fclose($fpRead);

		trigger_error("done!",E_USER_NOTICE);
		return true;
	}	

	
	/**
	 * Copy a file, or recursively copy a folder and its contents
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.0.1
	 * @link        http://aidanlister.com/repos/v/function.copyr.php
	 * @param       string   $source    Source path
	 * @param       string   $dest      Destination path
	 * @return      bool     Returns TRUE on success, FALSE on failure
	 */
	static function copyr($source, $dest)
	{
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        copyr("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
}

}
