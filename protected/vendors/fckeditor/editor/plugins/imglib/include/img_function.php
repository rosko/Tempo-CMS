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

function get_image ($file) {
	/*
		Create a resource from image file and return it with getimagesize() array
		$file - image file or resource
		return array('res'=>$image_resource, 'imgsize'=>getimagesize($file))
	*/
	if ( (is_file($file) === true) && (file_exists($file) === true) && (is_readable($file) === true) ) {
		$img_size = getimagesize($file);
		if ($img_size !== false) {
			$im_mime = $img_size['mime'];
			// Get the image from file
			if ($im_mime === 'image/jpeg') {
				$res = imagecreatefromjpeg($file);
			} else if ($im_mime === 'image/gif') {
				$res = imagecreatefromgif($file);
			} else if ($im_mime === 'image/png') {
				$res = imagecreatefrompng($file);
			} else {
				// Exit if not supported format
				return false;
			}
			return array('res' => $res, 'imgsize' => $img_size);
		}
	}
	return false;
}

function resize_img($in_file = '', $out_file = '', $dst_size = '', $q = 80) {
/*
	Resize the input image file to a output file with the fill to $dst_size
	$in_file, $out_file - input and output files
	$dst_size - max dimension of ouptut widht or height
	$q - quality 0-100 for JPEG and PNG files
*/
	if (!extension_loaded('gd')) {
		if (!dl('gd.'.PHP_SHLIB_SUFFIX)) {
			return false;
			//exit;
		}
	}
	settype($dst_size, 'integer');
	$in_file = realpath($in_file);
	$out_file = realpath($out_file);
	if ($dst_size <= 0) {
		// Zero size
		return false;
	}
	settype($q, 'integer');
	if ( (is_file($in_file) === true) && (file_exists($in_file) === true) && (is_readable($in_file) === true) ) {
		$image = get_image($in_file);
		if ($image !== false) {
			$imgsize = $image['imgsize'];
			$src_img = $image['res'];
			$src_w = $imgsize[0];
			$src_h = $imgsize[1];
			$src_mime = $imgsize['mime'];
			$src_max = max($src_w, $src_h);
			$src_ratio = max($src_w, $src_h)/min($src_w, $src_h);
			$dst_scale = $src_max/$dst_size;
			$dst_w = round($src_w/$dst_scale);
			$dst_h = round($src_h/$dst_scale);
			// Create empty image
			if (function_exists('imagecreatetruecolor') && ($src_mime !== 'image/gif') ) {
				$dst_img = imagecreatetruecolor($dst_w, $dst_h);
			} else {
				$dst_img = imagecreate($dst_w, $dst_h);
			}
			if (function_exists('imagecopyresampled') ) {
				$result = imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
			} else {
				$result = imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
			}
			if ($result !== true) {
				return $result;
			}
			$out_file_path = substr($out_file, 0, strrpos($out_file, DIRECTORY_SEPARATOR));
			if (!is_writable($out_file_path) && !file_exists($out_file)) {
				// Read protect
				return false;
			}
			// Output the image
			if ($src_mime === 'image/jpeg') {
				$result = imagejpeg($dst_img, $out_file, $q);
			} else if ($src_mime === 'image/gif') {
				$result = imagegif($dst_img, $out_file);
			} else if ($src_mime === 'image/png') {
				$quality = (int) (10 - round($quality/10));
				$quality = ($quality === 10) ? 9 : $quality;
				$result = imagepng($dst_img, $out_file, $q);
			}
			imagedestroy($src_img);
			imagedestroy($dst_img);
			return $result;
		} else {
			// Cant open image
			return false;
		}
	} else {
		// File Not found
		return false;
	}
}

function create_thumb ($in_file = '', $out_file = '', $dst_w = 0, $dst_h = 0, $q = 75) {
/*
	Create a thumbas from $file to $out_file with max size size $dst_w x $dst_h
	$in_file, $out_file - input and output files
	$q - quality 0-100 for JPEG and PNG files
*/
	if (!extension_loaded('gd')) {
		if (!dl('gd.'.PHP_SHLIB_SUFFIX)) {
			return false;
			//exit;
		}
	}
	settype($dst_w, 'integer');
	settype($dst_h, 'integer');
	settype($q, 'integer');
	if ( ($dst_w <= 0) || ($dst_h <= 0) ) {
		// Zero size
		return false;
	}

	if (!is_writable(dirname($out_file)) || file_exists($out_file)) {
		// Read protect
		return false;
	}
	if ( (is_file($in_file) === true) && (file_exists($in_file) === true) && (is_readable($in_file) === true) ) {
		$image = get_image($in_file);
		if ($image !== false) {
			$imgsize = $image['imgsize'];
			$src_img = $image['res'];
			$src_w = $imgsize[0];
			$src_h = $imgsize[1];
			$src_mime = $imgsize['mime'];

			$dst_ratio = ($src_w/$dst_w > $src_h/$dst_h) ? ($src_w/$dst_w) : ($src_h/$dst_h);

			$dst_w = round($src_w/$dst_ratio);
			$dst_h = round($src_h/$dst_ratio);
			// Create empty image
			if (function_exists('imagecreatetruecolor') && ($src_mime !== 'image/gif') ) {
				$dst_img = imagecreatetruecolor($dst_w, $dst_h);
			} else {
				$dst_img = imagecreate($dst_w, $dst_h);
			}
			if (function_exists('imagecopyresampled') ) {
				$result = imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
			} else {
				$result = imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
			}
			if ($result !== true) {
				return $result;
			}
			// Output the image
			if ($src_mime === 'image/jpeg') {
				$result = imagejpeg($dst_img, $out_file, $q);
			} else if ($src_mime === 'image/gif') {
				$result = imagegif($dst_img, $out_file);
			} else if ($src_mime === 'image/png') {
				$q = ($q >= 10) ? (round($q/10) - 1) : round($q/10);
				$result = imagepng($dst_img, $out_file, $q);
			}
			imagedestroy($src_img);
			imagedestroy($dst_img);
			return $result;
		} else {
			// Cant open image
			return false;
		}
	} else {
		// File Not found
		return false;
	}
}
?>