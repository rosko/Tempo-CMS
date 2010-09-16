<?php
/********************************************************************
 * imgLib v0.1.1 03.02.2010
 * Contact me at dev@imglib.endofinternet.net
 * Site: http://www.imglib.endofinternet.net/
 * This copyright notice MUST stay intact for use.
 *
 * This library gives you the possibility to upload, browse, manipulate and select
 * images on your webserver.
 *
 * Requirements:
 * - PHP 4.1.x or later
 ********************************************************************/

function get_float_size ($size = 0) {
	/*
		Get float size like 12.5Mb from size in bytes
		@size - the size in bytes
		return string containt size in float format
	*/
		settype($size, 'integer');
		if ($size < 1024) {
			$size = ceil(sprintf('%.2f' , $size)).' b';
		} elseif ($size < (1024*1024)) {
			$size = ceil(sprintf('%.2f' , $size/1024)).' Kb';
		} elseif ($size < (1024*1024*1024)) {
			$size = ceil(sprintf('%.2f' , $size/(1024*1024))).' Mb';
		} else {
			$size = ceil(sprintf('%.2f' , $size/(1024*1024*1024))).' Gb';
		}
		return $size;
}

function get_http_langs () {
	/*
		Get associative array from $_SERVER['HTTP_ACCEPT_LANGUAGE'] with lang name and weight
			Array
			(
				[en] => 0.3
				[en-us] => 0.5
				[de] => 1
			)
		Using loop like this to select language
		foreach($langs as $key => $val) {
			if (strpos($key, 'de') === 0) {
				$lang = 'de';
			} else if (strpos($key, 'en') === 0) {
				$lang = 'en';
			}
		}
		$lang = (isset($lang)) ? $lang : 'en';
	*/

	$langs_t = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$langs = array();
	foreach($langs_t as $val) {
		// Set the default language name and weight
		$lang_name = $val;
		$lang_weight = 1;
		// Search the q=lang_weight
		if ( ($pos = strpos($val, ';')) !== false) {
			list($lang_name, $lang_weight) = explode(';q=', $val);
		}
		$langs[$lang_name] = $lang_weight;
	}
	// Sort array based on lang weight
	asort($langs, SORT_NUMERIC);
	return $langs;
}

function is_subdir ($root = '', $path='') {
	/*
		Return false if $path is not a sub dir of $root
		Return true if $path = $root
	*/
	return (strpos(realpath($path), realpath($root)) === 0) ? true : false;
}

function fix_path ($path, $first_slash = true) {
	/*
		Fixing path to folder
		Sample "/var/www" -> "/var/www/"
	*/

	if ( !empty($path) ) {
		// Delete double slash in path
		while (strpos($path, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) !== false) {
			$path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
		}
		// Add first and end slashes
		$need_first_slash = ( (substr($path, 0, 1) !== DIRECTORY_SEPARATOR) && (substr($path, 0, 2) !== '.'.DIRECTORY_SEPARATOR) && (substr($path, 0, 3) !== '..'.DIRECTORY_SEPARATOR) ) ? true : false;

		// On MS Windows - no need first slash
		if (strpos(strtolower(PHP_OS), 'win') === 0) {
			$need_first_slash = false;
		}

		// Add end slash
		 if (substr($path, -1, 1) !== DIRECTORY_SEPARATOR) {
			$path = $path.DIRECTORY_SEPARATOR;
		}
		// Add or delete the first slash
		$path = ( ($first_slash === true) && $need_first_slash ) ? DIRECTORY_SEPARATOR.$path : $path;
		$path = ( ($first_slash !== true) && (substr($path, 0, 1) === DIRECTORY_SEPARATOR) ) ? substr($path, 1) : $path;
		return $path;
	} else {
		return $path;
	}
}

function fix_name($name = '') {
	/*
		Fixing file name - remove the NUL, \t, \r, \n, \x0B, " / \ * ? < > | : symbols and set max length to 255 chars
		return fixed name
	*/

	global $CFG;

	$illegalChars = array("\0", "\t", "\r", "\n", "\x0B", '"', '/', '\\'. '*', '?', '<', '>', '|', ':');
	settype($name, 'string');

	// Remove illegal chars
	$name = str_replace($illegalChars, '', trim($name));


	// If unicode name per file is disabled - then urlencode() it
	if (!$CFG->unicodeNames) {
		$name = str_replace(' ', '_', $name);
		$name = urlencode($name);
	}

	// Set max length to 255 chars
	if (strlen($name) > 255) {
		$file_ext = pathinfo($name, PATHINFO_EXTENSION);

		if (!$CFG->unicodeNames) {
			$name = urldecode($name);
		}
		// Get only the file name without the extension
		$name = substr($name, 0, 255 - strlen($file_ext) + 1);

		// Check if the file name still is bigger that 255 alfer urlencode()
		if ( (!$CFG->unicodeNames) && (strlen(urlencode($name)) > (255 - (strlen($file_ext) + 1))) ) {
			while ( (strlen(urlencode($name)) > (255 - (strlen($file_ext) + 1))) && (strlen($name) > 0)) {
				// Delete one symbol at end and try again
				$name = substr($name, 0, -1);
			}
			$name = urlencode($name);
		}

		// Combine the file name and extension
		$name = $name.'.'.$file_ext;
	}

	return $name;
}

function get_free_file_name ($file_path = '') {
	/*
		Get the path to non exist file name in dir, if target file exist - then add the rotable suffix and try again
		return the path to non exist file name or false
		$file_path - target file path
	*/

	global $CFG;

	$path = fix_path(dirname($file_path));
	$name = fix_name(basename($file_path));
	$file_ext = pathinfo($name, PATHINFO_EXTENSION);
	$name_wo_ext = substr($name, 0, -(strlen($file_ext) + 1)); // File name without the extension

	if (!file_exists($path) || !is_dir($path)) {
		return false;
	}
	if (!$CFG->unicodeNames) {
		$name = urldecode($name);
	}

	// Rotate the file name and check if exist
	$i = 1;
	while (file_exists($path.$name) && $i < 2147483647) {
		$name_suffix = date(sprintf($CFG->fileNameSuffix, $i));
		// If new file name bigger that the max then - trim it
		if (strlen($name_wo_ext.$name_suffix.'.'.$file_ext) > 255) {
			$name = fix_name(substr($name_wo_ext, 0, -strlen($name_suffix))).$name_suffix.'.'.$file_ext;
		} else {
			$name = fix_name($name_wo_ext).$name_suffix.'.'.$file_ext;
		}
		$i++;
	}

	if ($i >= 2147483647) {
		return false;
	}

	return $path.$name;
}

function u_remove ($path = '') {
	/*
	Remove file or folder from disk
	@path - path to removing element
	Return code
		true - remove sucsess
		some string - cant remove this file or folder
		false - cant remmove file or folder
	*/

	if (!file_exists($path)) return true;

	if (empty($path) || ($path === '.') || ($path === '..')) return;

	if (is_file($path)) {
		return unlink($path);
	} else if (is_dir($path) && is_writable($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					$result = u_remove($path.DIRECTORY_SEPARATOR.$file);
					if ($result !== true) {
						return $result;
					}
				}
			}
		}
		closedir($handle);
		return rmdir($path);
	} else {
		return $path;
	}
}

function u_copy ($src = '', $dst = '', $owerwrite = false) {
	/*
	Copy file or files from folder from $src to $dst
	Folder myst be have a end slash
	Return code
		true - copy sucsess
		some string - cant copy this file
		0 - source not exist
		1 - destination exist and owerwrite = false
		2 - copy folder to him subdir (sample copy /var/ to /var/tmp/)
		3 - cant create dst folder
		4 - if dst exist and copy file to folder or folder to file
		5 - src or dst not file or folder
	*/
	global $CFG;
	if (!file_exists($src)) return 0;
	// Get file name
	$src_name = basename($src);
	// Check the owerwrite premission
	if (!$owerwrite && is_file($src)) {
		if (is_file($dst)) return 1;
		if (is_dir($dst)) {
			if (file_exists($dst.DIRECTORY_SEPARATOR.$src_name)) return 1;
		}
	}
	if (is_file($src) && is_readable($src) && ( (file_exists($dst) && is_file($dst)) || is_dir($dst) || !file_exists($dst)) ) {
		if (is_dir($dst)) $dst = $dst.$src_name;
		$result = copy($src, $dst);
		// If error return file name
		return ($result !== true) ? $src : true;
	} else if (is_dir($src) && (!file_exists($dst) || (file_exists($dst) && is_dir($dst)) ) ) {
		if (strpos(realpath($dst), realpath($src)) === 0) return 2;
		$src = realpath($src).DIRECTORY_SEPARATOR;
		$dst = realpath($dst).DIRECTORY_SEPARATOR.$src_name.DIRECTORY_SEPARATOR;
		if (file_exists($dst) || (mkdir($dst, $CFG->dirPremision) === true) ) {
			if ($handle = opendir($src)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						$result = u_copy($src.$file, $dst, $owerwrite);
						if ($result !== true) {
							return $result;
						}
					}
				}
			}
			closedir($handle);
			return true;
		} else {
			return 3;
		}
	} else {
		return (file_exists($src) && file_exists($dst) && ( (!is_file($src) && is_file($dst)) || (!is_dir($src) && is_dir($dst)) )) ? 4 : 5;
	}
}

function u_move ($src = '', $dst = '', $owerwrite = false) {
	/*
	Move file or files from folder from $src to $dst
	Return code
		true - move sucsess
		some string - cant move this file
		0 - source not exist
		1 - destination exist and owerwrite = false
		2 - move folder to him subdir (sample copy /var/ to /var/tmp/)
		3 - cant create dst folder
		4 - if dst exist and move file to folder or folder to file
		5 - src or dst not file or folder
	*/
	global $CFG;
	if (!file_exists($src)) return 0;
	// Get file name
	$src_name = basename($src);
	// Check the owerwrite premission
	if (!$owerwrite && is_file($src)) {
		if (is_file($dst)) return 1;
		if (is_dir($dst)) {
			if (file_exists($dst.DIRECTORY_SEPARATOR.$src_name)) return 1;
		}
	}
	if (is_file($src) && is_readable($src) && ( (file_exists($dst) && is_file($dst)) || is_dir($dst) || !file_exists($dst)) ) {
		if (is_dir($dst)) $dst = $dst.$src_name;
		$result = rename($src, $dst);
		// If error return file name
		return ($result !== true) ? $src : true;
	} else if (is_dir($src) && (!file_exists($dst) || (file_exists($dst) && is_dir($dst)) ) ) {
		if (strpos(realpath($dst), realpath($src)) === 0) return 2;
		$src = realpath($src).DIRECTORY_SEPARATOR;
		$dst = realpath($dst).DIRECTORY_SEPARATOR.$src_name.DIRECTORY_SEPARATOR;
		if (file_exists($dst) || (mkdir($dst, $CFG->dirPremision) === true) ) {
			if ($handle = opendir($src)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						$result = u_move($src.$file, $dst, $owerwrite);
						if ($result !== true) {
							return $result;
						}
					}
				}
			}
			closedir($handle);
			u_remove($src);
			return true;
		} else {
			return 3;
		}
	} else {
		return (file_exists($src) && file_exists($dst) && ( (!is_file($src) && is_file($dst)) || (!is_dir($src) && is_dir($dst)) )) ? 4 : 5;
	}
}

function u_mkdir ($path = '') {
	/*
	Create full path to $path with all folders
		@$path - absolute path to end folder (example "/var/www/my_site/dir_1/dir_2/dir_3")
	Return code
		true - create sucsess
		false - can't create
		some string - cant create this directory
	*/
	if (file_exists($path) && is_dir($path)) {
		return true;
	}
	/* Not Work
	if (!is_writable($path)) {
		return false;
	}
	*/
	global $CFG;
	if (intval(phpversion()) >=5) {
		 if (!file_exists($path) && (mkdir($path, $CFG->dirPremision, true) !== true) ) {
			return false;
		} else {
			return true;
		}
	} else {
		$paths_el = explode(DIRECTORY_SEPARATOR, $path);
		$paths_len = count($paths_el);
		$cur_path = DIRECTORY_SEPARATOR;
		for ($i=0; $i<$paths_len; $i++) {
			if ( (file_exists($cur_path.$paths_el[$i]) && is_dir($cur_path.$paths_el[$i])) || (!file_exists($cur_path.$paths_el[$i]) && is_writable($cur_path) && (mkdir($cur_path.$paths_el[$i], $CFG->dirPremision) === true)) ) {
			} else {
				return $cur_path.$paths_el[$i];
			}
			$cur_path = realpath($cur_path.DIRECTORY_SEPARATOR.$paths_el[$i]).DIRECTORY_SEPARATOR;
		}
		return true;
	}
	return false;
}

function check_file_ext ($ext = '') {
	/*
		Check if the file extension is suported by manager
	*/
	global $CFG;
	if (in_array(strtolower(pathinfo($ext, PATHINFO_EXTENSION)), $CFG->fileExt)) {
		return true;
	}
	return false;
}

function folder_is_empty ($path = '') {
	/*
		Check if the directory is empty
		$path - the path to folder
	*/

	if (file_exists($path) && is_dir($path) && is_readable($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					return false;
				}
			}
			closedir($handle);
		}
		return true;
	} else if (file_exists($path) && !is_dir($path)) {
		return false;
	}
	return true;
}

function m($text) {
/*
	Return translated text if found
*/
	global $msg;
	if ( (isset($msg[$text])) && !empty($msg[$text]) ) {
		return $msg[$text];
	} else {
		return $text;
	}
}

function rename_file_obj ($src = '', $dst = '') {
	/*
	Reanme file or Folder
	Dont lost file ext
	Param
		$src - path to renaming file or folder
		$dst - new name of renaming element
	ReturnCode
		true - sucsess
		false - some error
		1 - renamed file not exist
		2 - new name is exist
		3 - Not standart type (Dir or File)
		4 - Write protect
		5 - Bad names (empty name or "." or "..") or lost name for file and leave only ext
		6 - rename not enable in config
	*/
	global $CFG;
	if ($CFG->allowRename === true) {
		// Enable thumbnail operation
		$thumb_enabled = false;
		if ( !empty($CFG->thumbDirName) && (intval($CFG->thumbWidth) > 0) && (intval($CFG->thumbHeight) > 0) ) {
			$thumb_enabled = true;
		}
		$dst = fix_name(urldecode($dst));

		$src = realpath($CFG->imgUploadDir.$src);

		if (!file_exists($src)) return 1;
		if (is_subdir($CFG->imgUploadDir, $src) === false) return 5;

		$src_type = (is_file($src)) ? 'file' : ( (is_dir($src)) ? 'folder' : 'unknown' );

		// Get the path and name of source
		if ($src_type === 'file') {
			$src_path = dirname($src);
			$src_name = basename($src);
		} else {
			$src_path = substr($src, 0, strrpos($src, DIRECTORY_SEPARATOR));
			$src_name = substr($src, strrpos($src, DIRECTORY_SEPARATOR) + 1);
		}

		$src_path = fix_path($src_path);
		$dst = $src_path.$dst;

		// Rename file to itself
		if ($src === $dst) return true;

		if ($thumb_enabled) {
			/*
			// Prevent access to thumbnail directory
			if ( (is_subdir($CFG->imgUploadDir.$CFG->thumbDirName, $src_path) === true) || ( ($src_type === 'folder') && (is_subdir($CFG->imgUploadDir.$CFG->thumbDirName, $src) === true) ) ) {
				return 5;
			}
			*/
			// Source and destination of thumbnail files
			$thumb_src = str_replace($src_path, $src_path.$CFG->thumbDirName.DIRECTORY_SEPARATOR, $src);
			$thumb_dst = str_replace($src_path, $src_path.$CFG->thumbDirName.DIRECTORY_SEPARATOR, $dst);
		}

		if (file_exists($dst)) return 2;
		// Check to lost filename and leave only extension
		$is_lost_name = false;
		if ($src_type === 'file') {
			$ext = strtolower(substr($src_name, strrpos($src_name, '.')+1));
			$is_lost_name = (!empty($ext) && ($dst === '.'.$ext) ) ? true : false;
		}
		if (!empty($src_name) && ($src_name !== '.') && ($src_name !== '..') && !empty($dst) && ($dst !== '.') && ($dst !== '..') && ($is_lost_name !== true) ) {
			if (is_writable($src_path)) {
				if (is_file($src) || is_dir($src) ) {
					if (rename($src, $dst) === true) {
						if ($thumb_enabled && file_exists($thumb_src)) {
							// Rename thumbnail files
							if (rename($thumb_src, $thumb_dst) !== true) {
								return false;
							}
						}
						return true;
					}
					return false;
				}
				return 3;
			}
			return 4;
		}
		return 5;
	}
	return 6;
}

function move_file_obj ($src = '', $dst = '', $type = 'copy', $overwrite = false) {
	/*
	Move or copy dir or folder
	Parameters
		$src - source path to element
		$dst - path of destination element
		$type - (copy or move) type of operation
	Return Code
		true - operation sucsess
		false - some error
		0 - Source file not exist
		1 - Destination exist
		2 - move folder to him subdir (sample copy /var/ to /var/tmp/)
		3 - write protect
		4 - Cant copy or move folder to file;
		5 - Not standart type (Dir or File)
		8 - operation nor enable in config
		9 - Bad names (empty name or "." or "..")
	*/
	global $CFG;
	if ($CFG->allowFileCopy=== true) {
		// Enable thumbnail operation
		$thumb_enabled = false;
		if ( !empty($CFG->thumbDirName) && (intval($CFG->thumbWidth) > 0) && (intval($CFG->thumbHeight) > 0) ) {
			$thumb_enabled = true;
		}
		$src = realpath($CFG->imgUploadDir.$src);
		$dst = realpath($CFG->imgUploadDir.$dst);

		if (!file_exists($src)) return 0;

		$src_type = (is_file($src)) ? 'file' : ( (is_dir($src)) ? 'folder' : 'unknown' );
		$dst_type = (is_file($dst)) ? 'file' : ( (is_dir($dst)) ? 'folder' : 'unknown' );
		if ( ($src_type === 'unknown') || ($dst_type === 'unknown') ) return 5;

		// Get the path to source and destination
		if ($src_type === 'file') {
			$src_path = dirname($src);
		} else {
			$src_path = $src;
		}
		if ($dst_type === 'file') {
			$dst_path = substr($dst, 0, strrpos($dst, DIRECTORY_SEPARATOR));
		} else {
			$dst_path = $dst;
			$dst = fix_path($dst);
		}
		$src_path = fix_path($src_path);
		$dst_path = fix_path($dst_path);

		if ( is_subdir($CFG->imgUploadDir, $src_path) && is_subdir($CFG->imgUploadDir, $dst_path) ) {
			if (!file_exists($src)) return 0;

			if ($src_type === 'folder' && $dst_type === 'file') return 4;
			// Fix type
			$type = ( ($type === 'move') && ( ( ($src_type === 'folder') && is_writable($src_path) ) || ( ($src_type === 'file') && is_writable($src_path) ) ) ) ? 'move' : 'copy';

			if (realpath(fix_path($src)) === realpath(fix_path($dst))) return true;

			if (!is_writable($dst_path) ) return 3;

			// Recursion copy
			if (is_subdir($src_path, $dst_path) && ($src_path === $dst_path)) return 2;
/**/			if ( ($src_type === 'folder') && ($dst_type === 'folder') && false) {
				$dst_dir_name = substr($src_path, 0, strrpos($src_path, DIRECTORY_SEPARATOR));
				$dst_dir_name = substr($dst_dir_name, strrpos($dst_dir_name, DIRECTORY_SEPARATOR) + 1);
				if (true !== ($result = create_folder(fix_path(str_replace($CFG->imgUploadDir, '', realpath($dst_path))), $dst_dir_name)) ) {
					return $result;
				}
				$dst = $dst_path.$dst_dir_name.DIRECTORY_SEPARATOR;
			}
			if ($thumb_enabled) {
				// Create the thumbnail folder if need
				if (true !== ($result = u_mkdir($dst.$CFG->thumbDirName.DIRECTORY_SEPARATOR))) {
					return $result;
				}
			}
			// Add file name to destination path
			if ($src_type === 'file') {
					$dst .= substr($src, strrpos($src, DIRECTORY_SEPARATOR) + 1);
			}
			if ($thumb_enabled) {
				// Source and destination of thumbnail files
				$thumb_src = str_replace($src_path, $src_path.DIRECTORY_SEPARATOR.$CFG->thumbDirName, $src);
				$thumb_dst = str_replace($dst_path, $dst_path.DIRECTORY_SEPARATOR.$CFG->thumbDirName, $dst);
			}

			if ($type==='move') {
				if (true === ($result = u_move($src, $dst, $overwrite)) ) {
					if ($thumb_enabled && ($src_type === 'file') && file_exists($thumb_src)) {
						// Move thumbnail files
						//return u_move($thumb_src, $thumb_dst, $overwrite);
					}
					return $result;
				} else {
					return $result;
				}
			} else if ($type==='copy') {
				if (true === ($result = u_copy($src, $dst, $overwrite)) ) {
					if ($thumb_enabled && ($src_type === 'file') && file_exists($thumb_src)) {
						// Copy thumbnail files
						//return u_copy($thumb_src, $thumb_dst, $overwrite);
					}
					return $result;
				} else {
					return $result;
				}
			}
			return false;
		}
		return 9;
	}
	return 8;
}

function remove_file_obj ($path='', $rec = true) {
	/*
		Remove dir or folder
		$path - path to deleting folder with element
		$rec - recursive delete folder
		Return code
			true - Sucsess deleting
			Some string - Can`t delete this element
			-1 - File extension is not suported
			0 - File is no deleted
			1 - Not standart type (Dir or File) or dir not empty and recursive = false
			2 - Write protect
			3 - Bad names (empty name or "." or "..")
			4 - Deleting not enabled in config
			11 - non-exist file or directory
	*/
	global $CFG;
	if ($CFG->allowDelete === true) {
		$thumb_enabled = false;
		if ( !empty($CFG->thumbDirName) && (intval($CFG->thumbWidth) > 0) && (intval($CFG->thumbHeight) > 0) ) {
			$thumb_enabled = true;
		}
		$path = realpath($CFG->imgUploadDir.$path);

		if (!file_exists($path)) return 11;
		if (is_subdir($CFG->imgUploadDir, $path) === false) return 3;

		$dst_type = (is_file($path)) ? 'file' : ( (is_dir($path)) ? 'folder' : 'unknown' );
		$dst_path = substr(fix_path($path), 0, -1);
		$dst_path = ($dst_type === 'file') ? dirname($dst_path) : $path;
		if ($thumb_enabled) {
			// Destination of thumbnail files
			$thumb_path = str_replace(dirname($path), dirname($path).DIRECTORY_SEPARATOR.$CFG->thumbDirName, $path);

		}
		if (!empty($path) && ($path !== '.') && ($path !== '..')) {
			// Check the premission
			if (is_writable($dst_path)) {
				if ( ($dst_type === 'folder') && ( $rec || folder_is_empty($path) ) ) {
					if (true === ($result = u_remove($path)) ) {
						if ($thumb_enabled && file_exists($thumb_path)) {
							// Remove thumbnail directory
							$result = u_remove($thumb_path);
						}
						if ($result === true) {
							return true;
						}
					}
					return $result;
				} else if ($dst_type === 'file') {
						if ( (check_file_ext($path) === true) && (true === ($result = u_remove($path)) ) ) {
							if ($thumb_enabled && file_exists($thumb_path)) {
								// Remove thumbnail file
								$result = u_remove($thumb_path);
							}
							if ($result === true) {
								return true;
							}
							return $result;
						} else if (check_file_ext($path) !== true) {
							return -1;
						}
					return 0;
				}
				return 1;
			}
			return 2;
		}
		return 3;
	}
	return 4;
}

function get_thumb_path ($path) {
	/*
		Return the relative (from root path) path to thumbnail file or empty string if file not found and support or path to file if not support
		if thumbnail file not exist - try to create it
		This function work with CLEAR path (check the path in outside the function)
	*/
	global $CFG;

	//realpath() can return the flase if file not exist
	//$thumb_file = realpath(dirname($path).DIRECTORY_SEPARATOR.$CFG->thumbDirName.DIRECTORY_SEPARATOR.basename($path));
	$thumb_file = dirname($path).DIRECTORY_SEPARATOR.$CFG->thumbDirName.DIRECTORY_SEPARATOR.basename($path);
	$thumb_file_path = str_replace($CFG->imgUploadDir, '', $thumb_file);

	// On MS Windows replace the '\' to '/'
	if (DIRECTORY_SEPARATOR !== '/') {
		$thumb_file_path = str_replace(DIRECTORY_SEPARATOR, '/', $thumb_file_path);
	}
	if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $CFG->thumbFileExt) ) {
		if ( !empty($CFG->thumbDirName) && (intval($CFG->thumbWidth) > 0) && (intval($CFG->thumbHeight) > 0) ) {
			// Check and create thumbnail for file if no exist
			if ( !(file_exists($thumb_file) || (($CFG->thumbAutoCreate === true) && create_file_thumb($path) && file_exists($thumb_file))) ) {
				$thumb_file_path = '';
			}
		} else {
			$thumb_file_path = '';
		}
	} else {
		$thumb_file_path = '';
	}
	return $thumb_file_path;
}

function create_file_thumb ($path = '') {
	/*
		Create thumbnail for file
		$path - real path to file
		This function work with CLEAR path and files (check the path in outside the function)
		Return code
			true - Sucsess сreate
			false - Can`t сreate this thumbnail
			4 - Create thumbnail not enabled in config
			5 - Not File
			11 - non-exist file or directory
	*/
	global $CFG;

	if ( !empty($CFG->thumbDirName) && (intval($CFG->thumbWidth) > 0) && (intval($CFG->thumbHeight) > 0) ) {
		if (!file_exists($path)) return 11;

		$sc_type = (is_file($path)) ? 'file' : ( (is_dir($path)) ? 'folder' : 'unknown' );
		if ($sc_type !== 'file') return 5;

		require_once('img_function.php');
		// Destination of thumbnail files
		$thumb_file = dirname($path).DIRECTORY_SEPARATOR.$CFG->thumbDirName.DIRECTORY_SEPARATOR.basename($path);

		// Delete exist thumbnail file
		if (file_exists($thumb_file) && is_writable(dirname($thumb_file))) {
			unlink($thumb_file);
		}

		if ( (u_mkdir(dirname($path).DIRECTORY_SEPARATOR.$CFG->thumbDirName.DIRECTORY_SEPARATOR) === true) && (create_thumb($path, $thumb_file, $CFG->thumbWidth, $CFG->thumbHeight, $CFG->thumbQuality) === true) ) {
			// Increase the time limit
			// set_time_limit(10);
			return true;
		} else {
			return false;
		}
	}
	return 4;
}

function create_folder ($path = '', $name = '', $thumb_access = false) {
	/*
		Create new directory on $path
		$path - relative path to destination folder
		$name - name of new folder
		$thumb_access - can create folder in thumbnail directory
		Return code
			true - Sucsess creating
			Some string - Can`t delete this element
			0 - Failed on create dir
			1 - Destination folder is not exist
			2 - Write protect or file with the same name is exist
			3 - Bad names (empty name or "." or "..")
			4 - Not enabled in config
	*/
	global $CFG;

	if ($CFG->allowCreateDir === true) {
		$path = realpath($CFG->imgUploadDir.$path);
		$name = fix_name(urldecode($name));

		if (!file_exists($path) || !is_dir($path)) return 1;

		if (is_subdir($CFG->imgUploadDir, $path) === false) return 3;

		$src_type = (is_file($path)) ? 'file' : ( (is_dir($path)) ? 'folder' : 'unknown' );

		$path = fix_path($path);
		if ($thumb_access === false) {
			// Prevent access to thumbnail directory
			if ( (is_subdir($CFG->imgUploadDir.$CFG->thumbDirName, $path) === true) || ( ($src_type === 'folder') && (is_subdir($CFG->imgUploadDir.$CFG->thumbDirName, $path.$name) === true) ) ) {
				return 3;
			}
		}
		if (!empty($name) && ($name !== '.') && ($name !== '..')) {
			// Check the premission
			if (is_writable($path) && !file_exists($path.$name) ) {
				if (mkdir($path.$name, $CFG->dirPremision) === true) {
					return true;
				}
				return 0;
			} else if (file_exists($path.$name) && is_dir($path.$name) ) {
				return true;
			}
			return 2;
		}
		return 3;
	}
	return 4;
}

function get_dir_content ($path = '', $options='') {
	/*
		Get the content from dir
		@path - path to directory
		@options - associative array with options to out
			options[dirs] - bool get information about folders default true
			options[dir_col] - array specify information to get for dir, posible values - empty, readable, sample - array('empty', 'readable', 'date') default null
			options[files] - bool get information about files default true
			options[file_col] - array specify information to get for file, posible values - filesize, date, img_size, thumb, sample - array('filesize', 'date', 'img_size', 'thumb') default null
				options[file_col][thumb] - path to thumbnail of file accerding of curent configuration, if file not found and it support - return empty strinf, if not support - return path to file
			options[sort] - string sort type (date, filesize) default null
			options[sort_r] - bool Descending of sorting default false
			options[allowed_ext] - array containt the extension of files to out, sample - array('gz', 'zip') default all out
			options[clear] - bool using clearstatcache(); function before read the dir default true
			options['extra_inf'] - associative array with additional info to out
				extra_inf['is_writable'] - get the write premission on current path, default false
		return a array('dir', 'files', 'inf') or if the options[file_col] specify the out array('dirs', 'files'=>array('name', specified colum)), where 'extra_inf' array key - key containt additional info such as write premmision as 'inf'=>array('is_writable'=>1, 'option_name'=>option_value,...)
	*/
	// Apply options
	if (isset($options) && !empty($options) && is_array($options) ) {
		$get_dirs = array_key_exists('dirs', $options) ? (bool) $options['dirs'] : true;
		$dir_col = (array_key_exists('dir_col', $options) && (is_array($options['dir_col']) || is_string($options['dir_col'])) ) ? $options['dir_col'] : null;
		$get_files = array_key_exists('files', $options) ? (bool) $options['files'] : true;
		$file_col = (array_key_exists('file_col', $options) && (is_array($options['file_col']) || is_string($options['file_col'])) ) ? $options['file_col'] : null;
		$sort_type = array_key_exists('sort', $options) ? $options['sort'] : null;
		$sort_reverse = array_key_exists('sort_r', $options) ? (bool) $options['sort_r'] : false;
		$allowed_ext = (array_key_exists('allowed_ext', $options) && is_array($options['allowed_ext']) && !empty($options['allowed_ext']) ) ? $options['allowed_ext'] : null;
		$clear_cache = array_key_exists('clear', $options) ? (bool) $options['clear'] : true;
		// Additional info array
		if (isset($options['extra_inf']) && !empty($options['extra_inf']) && is_array($options['extra_inf']) ) {
			$extra_inf = $options['extra_inf'];
			$extra_inf['is_writable'] = array_key_exists('is_writable', $extra_inf) ? (bool) $extra_inf['is_writable'] : false;
		}
	} else {
		$get_dirs = true;
		$dir_col = null;
		$get_files = true;
		$file_col = null;
		$sort_type = null;
		$sort_reverse = false;
		$allowed_ext = null;
		$clear_cache = true;
		$extra_inf['is_writable'] = false;
	}
	if ($clear_cache) {
		clearstatcache();
	}

	$dirs = array(); // Direcrotys in dir
	$files = array(); // Files in dir
	$inf= array(); // Additional information array

	$files_size = array(); // File size information array
	$files_date = array(); // File date information array
	$imgs_size = array(); // Image size information array
	$thumb = array(); // Image thumbnail information array

	/*------- Check must be a external and function must accept evrefing - in theory -----------------*/
	global $CFG;
	$path = realpath($path).DIRECTORY_SEPARATOR;
	if (!is_subdir($CFG->imgUploadDir, $path)) {
		// Exit if try to read above root dir
		return;
	}

	if ( is_readable($path) && ($handle = opendir($path)) ) {
		$n = 0;
		while (false !== ($file = readdir($handle))) {
			if ($file === '.' || $file === '..') continue;
			$n++;
			if ( $get_dirs && is_dir($path.$file)) {
				if(isset($sort_type) && $sort_type=='date') {
					$key = filemtime($path.$file).'_'.$n;
				} else {
					$key = $n;
				}
				$dirs[$key] = $file;
				if (isset($dir_col)) {
					if ( (is_array($dir_col) && in_array('empty', $dir_col) ) || ($dir_col === 'empty') ) {
						$dirs_empty[$file] = intval(folder_is_empty($path.$file));
					}
					if ( (is_array($dir_col) && in_array('readable', $dir_col) ) || ($dir_col === 'readable') ) {
						$dirs_readable[$file] = intval(is_readable($path.$file));
					}
					if ( (is_array($dir_col) && in_array('date', $dir_col) ) || ($dir_col === 'date') ) {
						$dirs_date[$file] = intval(filemtime($path.$file));
					}
				}
			} else if ($get_files && is_file($path.$file) && (!isset($allowed_ext) || in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $allowed_ext))) {
				if(isset($sort_type) && $sort_type == 'date') {
					$key = filemtime($path.$file).'_'.$n;
				} elseif (isset($sort_type) && $sort_type == 'filesize') {
					$key = filesize($path.$file).'_'.$n;
				} else {
					$key = $n;
				}
				$files[$key] = $file;
				if (isset($file_col)) {
					if ( (is_array($file_col) && in_array('filesize', $file_col) ) || ($file_col === 'filesize') ) {
						$files_size[$file] = filesize($path.$file);
					}
					if ( (is_array($file_col) && in_array('date', $file_col) ) || ($file_col === 'date') ) {
						$files_date[$file] = filemtime($path.$file);
					}
					if ( (is_array($file_col) && in_array('img_size', $file_col) ) || ($file_col === 'img_size') ) {
						if (is_readable($path.$file) && extension_loaded('gd') && function_exists('getimagesize')) {
							$img_size = getimagesize($path.$file);
							if ($img_size !== false) {
								$imgs_size[$file] = $img_size;
							}
						}
					}
					if ( (is_array($file_col) && in_array('thumb', $file_col) ) || ($file_col === 'thumb') ) {
						$file_thumb = get_thumb_path($path.$file);
						if (!empty($file_thumb)) {
							$thumb[$file] = $file_thumb;
						}
					}
				}
			}
		}
		closedir($handle);
	}
	// Sort
	if(isset($sort_type) && $sort_type === 'date') {
		ksort($dirs, SORT_NUMERIC);
		ksort($files, SORT_NUMERIC);
	}
	elseif(isset($sort_type) && $sort_type === 'filesize') {
		natcasesort($dirs);
		ksort($files, SORT_NUMERIC);
	}
	else {
		natcasesort($dirs);
		natcasesort($files);
	}
	// Order
	if ($sort_reverse && isset($sort_type) && $sort_type !== 'filesize') {
		$dirs = array_reverse($dirs);
	}
	if ($sort_reverse) {
		$files = array_reverse($files);
	}
	// Rebild index
	$dirs = array_values($dirs);
	if (isset($dir_col)) {
		$n=0;
		$dir = array();
		foreach($dirs as $key=>$val) {
			$dir[$n]['name'] = $dirs[$key];
			if ( (is_array($dir_col) && in_array('empty', $dir_col) ) || ($dir_col === 'empty') ) {
				$dir[$n]['empty'] = $dirs_empty[$val];
			}
			if ( (is_array($dir_col) && in_array('readable', $dir_col) ) || ($dir_col === 'readable') ) {
				$dir[$n]['readable'] = $dirs_readable[$val];
			}
			if ( (is_array($dir_col) && in_array('date', $dir_col) ) || ($dir_col === 'date') ) {
				$dir[$n]['date'] = $dirs_date[$val];
			}
			$n++;
		}
	$dirs = $dir;
	unset($dir);
	}
	$files = array_values($files);
	if (isset($file_col)) {
		$n=0;
		 $file= array();
		foreach($files as $key => $val) {
			$file[$n]['name'] = $files[$key];
			if (isset($files_size[$val])) {
				$file[$n]['filesize'] = $files_size[$val];
			}
			if (isset($files_date[$val])) {
				$file[$n]['date'] = $files_date[$val];
			}
			if (isset($imgs_size[$val]) && is_array($imgs_size[$val])) {
				$file[$n]['img_size'] = $imgs_size[$val][0].'x'.$imgs_size[$val][1];
			}
			if (isset($thumb[$val]) && !empty($thumb[$val])) {
				$file[$n]['thumb'] = $thumb[$val];
			}
			$n++;
		}
	$files = $file;
	unset($file);
	}

	if ( isset($extra_inf) && !empty($extra_inf) && is_array($extra_inf) ) {
			if ( (in_array('is_writable', $extra_inf) ) && ($extra_inf['is_writable'] === true) ) {
				$inf['is_writable'] = (int) is_writable($path);
			}
	unset($extra_inf);
	}

	return array('dirs' => $dirs, 'files' => $files, 'inf' => $inf);
}

function get_xml_dir_content ($path = '', $options = '') {
	global $CFG;
	$eol = "\n";
	$tab ="\t";
	// If not debug - compact the xml
	if ($CFG->debug === false) {
		$eol = '';
		$tab = '';
	}
	$return_str = '<?xml version="1.0" encoding="utf-8"?>'.$eol.'<response xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">'.$eol;

	$path = realpath($CFG->imgUploadDir.$path);
	// Check path to target dir
	if ( ((file_exists($path) && is_dir($path) && (is_subdir($CFG->imgUploadDir, $path) === true)) === false) || ($CFG->browseSubDir === false)) {
		$path = $CFG->imgUploadDir;
	}
	if ( ($CFG->browseSubDir === false) && isset($options) && is_array($options) ) {
		$options['dirs'] = false;
	}
	$cur_path = str_replace($CFG->imgUploadDir, '', realpath($path));
	$return_str .= $tab.'<path>';
	$dirnames = explode(DIRECTORY_SEPARATOR, $cur_path);
	foreach($dirnames as $key=>$val) {
		if (!empty($val)) {
			$return_str .= $eol.$tab.$tab.'<dir name="'.htmlentities(htmlentities($val, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8').'" />';
		}
	}
	unset($dirnames);
	$return_str .= $eol.$tab.'</path>';

	$dir_content = get_dir_content($path, $options);

	if (!empty($dir_content['inf'])) {
		// Process additional info
		$return_str .= $eol.$tab.'<inf';
		foreach ($dir_content['inf'] as $key=>$val) {
			$return_str .= ' '.$key.'="'.htmlentities($val, ENT_QUOTES, 'UTF-8').'"';
		}
		$return_str .= ' />';
	}

	if (!empty($dir_content['dirs'])) {
		// Process dir
		$return_str .= $eol.$tab.'<dirs>';
		foreach ($dir_content['dirs'] as $val) {
			$return_str .= $eol.$tab.$tab.'<dir';
			if (is_array($val)) {
				foreach ($val as $k => $v) {
					$return_str .= ' '.$k.'="'.htmlentities(htmlentities($v, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8').'"';
				}
				$el_name = $val['name'];
			} else {
				$el_name = $val;
			}
			$return_str .= ' />';
		}
		$return_str .= $eol.$tab.'</dirs>';
	}
	if (!empty($dir_content['files'])) {
		// Process files
		$return_str .= $eol.$tab.'<files>';
		foreach ($dir_content['files'] as $val) {
			$return_str .= $eol.$tab.$tab.'<file';
			if (is_array($val)) {
				foreach ($val as $k => $v) {
					$return_str .= ' '.$k.'="'.htmlentities(htmlentities($v, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8').'"';
				}
				$el_name = $val['name'];
			} else {
				$el_name = $val;
			}
			$return_str .= ' />';
		}
		$return_str .= $eol.$tab.'</files>';
	}
	$return_str .= $eol.'</response>';
	return $return_str;
}

function process_upload () {
	/*
		Process the upload file
	*/
	global $CFG;
	$ret = array();

	// Select the upload dir
	$upl_dir = $CFG->imgUploadDir;
	if (isset($_POST['dir']) && $_POST['dir'] !== '') {
		$upl_dir = realpath($CFG->imgUploadDir.$_POST['dir']);
		if ((file_exists($upl_dir) && is_dir($upl_dir) && (is_subdir($CFG->imgUploadDir, $upl_dir) === true)) === false) {
			$upl_dir = $CFG->imgUploadDir;
		}
	}
	$upl_dir = fix_path($upl_dir);

	// Create the list of uploaded files, support the one and couple files inputs as array (name like "file[1]")
	if (!is_array($_FILES['file']['name'])) {
		$upl_files[1] =$_FILES['file'];
	} else {
		$arr_len = count($_FILES['file']['name']);
		foreach($_FILES['file'] as $key => $val) {
			$i = 1;
			foreach($val as $v) {
				$upl_files[$i][$key] = $v;
				$i++;
			}
		}
	}

	print_r($upl_files);

	// Process upload for all uploaded files
	foreach ($upl_files as $key => $upl_file) {
		// Allow process upload for new file in list
		$upload = true;

		// Fix the upload file name
		$upload_file = fix_name(strtolower(basename($upl_file['name'])));
		$file_ext = pathinfo($upload_file, PATHINFO_EXTENSION);

		// Get file name without the ext
		$name_wo_ext = (empty($file_ext)) ? $upload_file : substr($upload_file, 0, -(strlen($file_ext) + 1));

		// Get the target upload file path
		if (!empty($CFG->uploadNameFormat)) {
			$upload_file_path = $upl_dir.str_replace('n', $name_wo_ext, date($CFG->uploadNameFormat)).'.'.$file_ext;
		} else {
			$upload_file_path = $upl_dir.$upload_file;
		}

		// Check if tagret file exist and create owerwrite is disabled - then grenerate the new file name
		if (!$CFG->overwriteFile  && file_exists($upload_file_path)) {
			$upload_file_path = get_free_file_name($upload_file_path);
			// If can't get free file name - stop upload
			if ($upload_file_path === false) {
				$upload = false;
			}
		}

		// Check file extension
		if (!in_array($file_ext, $CFG->uploadExt)) {
			$upload = false;
		}

		// Get max upload file size
		$phpmaxsize = trim(ini_get('upload_max_filesize'));
		$last = strtolower($phpmaxsize{strlen($phpmaxsize) - 1});
		switch($last) {
			case 'g':
				$phpmaxsize *= 1024;
			case 'm':
				$phpmaxsize *= 1024;
			case 'k':
				$phpmaxsize *= 1024;
		}
		$cfgmaxsize = trim($CFG->maxUploadFileSize);
		$last = strtolower($cfgmaxsize{strlen($cfgmaxsize) - 1});
		switch($last) {
			case 'g':
				$cfgmaxsize *= 1024;
			case 'm':
				$cfgmaxsize *= 1024;
			case 'k':
				$cfgmaxsize *= 1024;
		}
		$cfgmaxsize = (int) $cfgmaxsize;
		// Check upload file size
		if ( ( ($cfgmaxsize > 0) && ($upl_file['size'] > $cfgmaxsize) ) || ($upl_file['size'] > $phpmaxsize) || ($upl_file['size'] > disk_free_space($upl_dir)) ) {
			$upload = false;
		}
		

		// Check upload dir is writable
		if (!is_writable($upl_dir)) {
			$upload = false;
		}

		// If all OK then move upload file
		if($upload) {
			move_uploaded_file($upl_file['tmp_name'], $upload_file_path);
			$ret[] = $upload_file_path;

			// Resize section
			if (isset($_POST['resize'][$key]) && $_POST['resize'][$key] !== '') {
				$newsize = $_POST['resize'][$key] ;
				settype($newsize, 'integer');
				$newsize = ($newsize < 0) ? ($newsize * -1) : $newsize;
				if ($newsize > $CFG->maxImgResize) $newsize = $CFG->maxImgResize;
				if ($newsize > 0) {
					if (!function_exists('resize_img')) {
						require_once('img_function.php');
					}
					if (function_exists('resize_img')) {
						resize_img($upload_file_path, $upload_file_path, $newsize);
					}
				}
			}
		} else {
		}
	}
	return $ret;
}

function getClientConfig () {
	/*
		Get JavaScript configuration for client-side part
		Return the string with required options
	*/

	global $CFG;

	if($CFG->enableUpload) {
		// Get the upload limits for configuraion
		$phpmaxsize = trim(ini_get('upload_max_filesize'));
		$last = strtolower($phpmaxsize{strlen($phpmaxsize) - 1});
		switch($last) {
			case 'g':
				$phpmaxsize *= 1024;
			case 'm':
				$phpmaxsize *= 1024;
			case 'k':
				$phpmaxsize *= 1024;
		}
		$cfgmaxsize = trim($CFG->maxUploadFileSize);
		$last = strtolower($cfgmaxsize{strlen($cfgmaxsize) - 1});
		switch($last) {
			case 'g':
				$cfgmaxsize *= 1024;
			case 'm':
				$cfgmaxsize *= 1024;
			case 'k':
				$cfgmaxsize *= 1024;
		}
		$cfgmaxsize = (int) $cfgmaxsize;
		if ($cfgmaxsize > 0) {
			$maxsize = $cfgmaxsize;
		} else {
			$maxsize = $phpmaxsize;
		}
	} else {
		$maxsize = 0;
		$phpmaxsize = 0;
	}


	// Script Messages

	$lang_inc_path = 'include'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR;
	// Chose language
	$langs = get_http_langs();
	foreach($langs as $key => $val) {
		if (strpos($key, 'uk') === 0) {
			$lang_file = $lang_inc_path.'lang_uk.php';
		} else if (strpos($key, 'ru') === 0) {
			$lang_file = $lang_inc_path.'lang_ru.php';
		} else if (strpos($key, 'fr') === 0) {
			$lang_file = $lang_inc_path.'lang_fr.php';
		} else if (strpos($key, 'de') === 0) {
			$lang_file = $lang_inc_path.'lang_de.php';
		} else if (strpos($key, 'en') === 0) {
			$lang_file = $lang_inc_path.'lang_en.php';
		}
	}
	// Use default language if nothing find
	$lang_file = (isset($lang_file)) ? $lang_file : $lang_inc_path.'lang_en.php';
	// Include language file
	if (file_exists($lang_file) && is_file($lang_file)) {
		global $msg;
		include($lang_file);
	}

	// Get configuration for clients scripts
	ob_start();
	?>
/* Translated messages */
var fileSizeName = [
	'<?php echo m('b'); ?>',
	'<?php echo m('Kb'); ?>',
	'<?php echo m('Mb'); ?>',
	'<?php echo m('Gb'); ?>',
	'<?php echo m('Tb'); ?>',
	'<?php echo m('Eb'); ?>'
	],
	messages = {
		rootPathName: '<?php echo m('Root'); ?>',
		ajaxLoadingText: '<?php echo m('Loading...'); ?>',
		moveToUpDirText: '<?php echo m('Up'); ?>',
		delNonEmptyFolderPromt: '<?php echo m('Remove not empty folder %1?'); ?>',
		delFileObjPromt: '<?php echo m('Remove %1?'); ?>',
		enterNewDirNameStr: '<?php echo m('Enter name of new directory'); ?>',
		defaultNewDirNameStr: '<?php echo m('New Folder'); ?>',
		enterNewNameWoExtPromt: '<?php echo m('Enter new name of %1:\n(file extension is add automatic)'); ?>',
		operationFailedStr: '<?php echo m('Operation failed. Error code is %1.'); ?>',
		ajaxIsReguire: '<?php echo m('This script reguire browser that support the AJAX tehnology!'); ?>',
		noChange: '<?php echo m('No change!'); ?>',
		/* Context Menu */
		newDirTitle: '<?php echo m('Create folder'); ?>',
		openTitle: '<?php echo m('Open'); ?>',
		browseTitle: '<?php echo m('Browse'); ?>',
		copyTitle: '<?php echo m('Copy'); ?>',
		cutTitle: '<?php echo m('Cut'); ?>',
		pastTitle: '<?php echo m('Paste'); ?>',
		deleteTitle: '<?php echo m('Delete'); ?>',
		renameTitle: '<?php echo m('Rename'); ?>',
		reloadDirTitle: '<?php echo m('Reload curent directory'); ?>',
		fileNameTitle: '<?php echo m('File name'); ?>',
		fileSizeTitle: '<?php echo m('File size'); ?>',
		fileDateTitle: '<?php echo m('File date'); ?>',
		dateTitle: '<?php echo m('Date'); ?>',
		sizeTitle: '<?php echo m('Size'); ?>',
		imageSizeTitle: '<?php echo m('Image size'); ?>',
		viewTitle: '<?php echo m('View'); ?>',
		thumbnailTitle: '<?php echo m('Thumbnail'); ?>',
		listTitle: '<?php echo m('List'); ?>',
		tableTitle: '<?php echo m('Table'); ?>',
		searchTitle: '<?php echo m('Search'); ?>',
		enterSearchTitle: '<?php echo m('Enter part of file name:'); ?>',
		sortTitle: '<?php echo m('Sort'); ?>',
		sortByNameTitle: '<?php echo m('By name'); ?>',
		sortBySizeTitle: '<?php echo m('By size'); ?>',
		sortByDateTitle: '<?php echo m('By date'); ?>',
		uploadTitle: '<?php echo m('Upload'); ?>',
		cancelTitle: '<?php echo m('Cancel'); ?>',
		writeProtectTitle: '<?php echo m('Directory is write protected!'); ?>',
		addFieldTitle: '<?php echo m('Add more field'); ?>',
		delFieldTitle: '<?php echo m('Delete field'); ?>',
		selectFirstFileTitle: '<?php echo m('Select first file'); ?>',
		allowExtTitle: '<?php echo m('Allowed extension'); ?>',
		maxUploadSizeTitle: '<?php echo m('Max upload size (total/file)'); ?>',
		pathTitle: '<?php echo m('Path'); ?>'
	},
	user_context_items = [/* Arrays with the image tools context menu items objects */
	<?php
	if ($CFG->enableImgTools) {
	?>
		{
			text: '<?php echo m('Edit'); ?>', /* The display item text */
			handle: openImgTools, /* Handle of function to extcute when the item click */
			cssClass: '', /* css class of icons, empty or not set to disable */
			showOn: 2, /* Show on 0 - on files and folders, 1 - on folders, 2 - on files, 3 - on free spaces */
			disableOnPaste: 0, /* Disable item if can`t paste the file or folder */
			disableOnFileOp: 1, /* Disable item if can`t some file operation */
			disabled: !true, /* Dont show item */
			defaultItem: 0 /* Show item as default (bold text), dont use in users items */
		}
	<?php
	}
	?>
	],
	imgLibConf = {
		idName: 'imgLib', /* Main id */
		reqURL: '<?php echo $_SERVER['PHP_SELF']; ?>',
		bindKeys: '<?php echo $CFG->bindKeys; ?>',
		enableUpload: <?php echo $CFG->enableUpload; ?>,
		uploadPath: '<?php echo $CFG->imgURL; ?>',
		allowedExt: ['<?php echo implode('\', \'', $CFG->uploadExt); ?>'],
		maxUploadSize: <?php echo $phpmaxsize; ?>,
		maxUploadFileSize: <?php echo $maxsize; ?>,
		maxFileNameLen: <?php echo $CFG->fileNameLen; ?>,
		enableBrowseSubdir: <?php echo ($CFG->browseSubDir) ? 'true': 'false'; ?>,
		enableFileOperation: <?php echo ($CFG->allowFileCopy) ? 'true': 'false'; ?>,
		enableCreateDir: <?php echo ($CFG->allowCreateDir) ? 'true': 'false'; ?>,
		enableRename: <?php echo ($CFG->allowRename) ? 'true': 'false'; ?>,
		enableDelete: <?php echo ($CFG->allowDelete) ? 'true': 'false'; ?>,
		thumbnailDir: '<?php echo (!empty($CFG->thumbDirName) && (intval($CFG->thumbWidth) > 0) && (intval($CFG->thumbHeight) > 0)) ? $CFG->thumbDirName: ''; ?>',
		onSelect: imgLibManager.onSelect,
		onDblSelect: imgLibManager.select,
		onDeselect: imgLibManager.onDeselect,
		viewType: '<?php echo $CFG->defaultViewType; ?>',
		messages: messages,
		contextMenuItems: user_context_items
	};

	<?php
	if ($CFG->enableImgTools) {
	?>
/* Image Tools */
var img_src = '';
function getImgToolsData() {
	return {
			src: img_src,
			action: 'imgtools.php',
			srcArg: 'src',
			dstArg: 'dst',
			previewArg: 'preview',
			resizeArg: 'resize',
			cropArg: 'crop',
			rotateArg: 'rotate',
			flipArg: 'flip',
			loadingIndicator: '../img/loading.gif',
			msg: { /* Messages strings */
				errorLoadData: '<?php echo m('Error loading data!'); ?>',
				cropImageWait: '<?php echo m('Crop image. Please wait.'); ?>',
				resizeImageWait: '<?php echo m('Resize image. Please wait.'); ?>',
				rotateFlipImageWait: '<?php echo m('Rotate/Flip image. Please wait.'); ?>',
				loadingPreview: '<?php echo m('Loading preview....'); ?>',
				errorPrefix: '<?php echo m('Error: '); ?>',
				loading: '<?php echo m('Loading....'); ?>',
				labels: [
					{id: 'resize_content_tab', prop:'<?php echo m('Resize image'); ?>'},
					{id: 'crop_content_tab', prop: '<?php echo m('Crop image'); ?>'},
					{id: 'rotate_content_tab', prop: '<?php echo m('Rotate/Flip image'); ?>'},
					{id: 'close_button', prop: '<?php echo m('Close'); ?>'},
					{id: 'size_label', prop: '<?php echo m('Size'); ?>'},
					{id: 'apply_resize', prop: {value: '<?php echo m('Apply'); ?>', title: '<?php echo m('Save changes'); ?>'}},
					{id: 'reset_resize', prop: {value: '<?php echo m('Reset'); ?>', title: '<?php echo m('Reset to original size'); ?>'}},
					{id: 'save_prop_label', prop: '<?php echo m('Save proportions'); ?>'},
					{id: 'width_label', prop: {innerHTML: '<?php echo m('Width'); ?>', title: '<?php echo m('Width'); ?>'}},
					{id: 'height_label', prop: {innerHTML: '<?php echo m('Height'); ?>', title: '<?php echo m('Height'); ?>'}},
					{id: 'crop_sel_from_label', prop: {innerHTML: '<?php echo m('Selected area: from'); ?>'}},
					{id: 'crop_sel_to_label', prop: '<?php echo m('to'); ?>'},
					{id: 'apply_crop', prop: {value: '<?php echo m('Apply crop'); ?>'}},
					{id: 'rotate_label', prop: '<?php echo m('Rotate'); ?>'},
					{id: 'rotate_ccw_label', prop: {innerHTML: '<?php echo m('90&deg; CCW'); ?>', title: '<?php echo m('Rotate 90 degrees counterclockwise'); ?>'}},
					{id: 'rotate_cw_label', prop: {innerHTML: '<?php echo m('90&deg; CW'); ?>', title: '<?php echo m('Rotate 90 degrees clockwise'); ?>'}},
					{id: 'flip_label', prop: '<?php echo m('Flip'); ?>'},
					{id: 'flip_h_label', prop: {innerHTML: '<?php echo m('horisontal'); ?>', title: '<?php echo m('horisontal'); ?>'}},
					{id: 'flip_v_label', prop: {innerHTML: '<?php echo m('vertical'); ?>', title: '<?php echo m('vertical'); ?>'}},
					{id: 'reset_rotate_flip', prop: {value: '<?php echo m('Reset'); ?>'}},
					{id: 'apply_rotate_flip', prop: {value: '<?php echo m('Apply'); ?>'}},
					{id: 'cor_label', prop: '<?php echo m('Image Tools for'); ?>'}
				]
			}
		};
};

function openImgTools() {
	var url = 'imgtools/index.html',
		selElement = imgLib.getSelectedItem()
		;
	if (selElement) {
		var ext_prop = imgLib.getItemInfo(selElement.type, selElement.index),
			path = '/' + imgLib.getDirContent().path.join('/')
		;
		if (path !== '/') {
			path += '/';
		}
		if (selElement.type == 2) {
			img_src = getURIEncPath(HTMLDecode(imgLibConf.uploadPath + path + ext_prop.name));
			var imgToolsWindow = window.open( url,'imgtools', 'width=750, height=500, location=0, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes');
		}
	}
}
	<?php
	}
	?>
imgLib.init(imgLibConf);

/* Translate page labels */

addEvent(window, 'load', function () {translateLabels([{id: 'select_file_label', prop:'<?php echo m('Selected file'); ?>'},{id: 'select', prop: '<?php echo m('Select'); ?>'},{id: 'cancel', prop: '<?php echo m('Cancel'); ?>'}]);});

<?php
	// Get text output
	$script_text = ob_get_contents();
	ob_end_clean();
	if ($CFG->debug !== true) {
		$script_text = str_replace(array("\t", "\r", "\n"), '', $script_text);
	}
	return $script_text;
}


?>