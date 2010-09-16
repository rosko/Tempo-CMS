<?php
/*
		imgTools Class
			PHP 5
			Supported formats:
				1) jpg
				2) gif
				3) png
			Description: base image manipulation
				1) resize
				2) crop
				3) flip
				4) rotate
				5) convert based on change the mime type
		ver. 0.0.1
		1:20 01.09.2009
*/

class imgTools {
	protected $imgFile = ''; // Image file paths
	protected $imgResource = false; // Image resource
	protected $imgSize = array(); // Out of getimagesize() function
	protected $keepProportions = true; // Keep the proportions of image
	protected $maxImageSize = 0; // Set max image width or height on resize or crop
	protected $increaseSmallImage = true; // Set increase small image state on resize when destination width or height large that sourse width ro height
	protected $overwrite = false; // Owerwrite the exist file
	protected $ready = false; // The image ready to process
	protected $sendHeader = true; // If image out to browser then send or not send the require header
	protected $suportedMime = array('image/jpeg', 'image/gif', 'image/png'); // Supported mime type of images
	protected $lastError = false; // Last error message
	protected $buildInRotate = true; // Indicate the image rotation function is bild in PHP

	protected function updateImageSize () {
		/*
			Update the image size
		*/

		$this->imgSize[0] = imagesx($this->imgResource);
		$this->imgSize[1] = imagesy($this->imgResource);
	}

	public function loadImage ($file) {
		/*
			Open the image and create the resource
		*/

		// Check if the GD extension is loaded
		if (!extension_loaded('gd')) {
			if (!dl('gd.'.PHP_SHLIB_SUFFIX)) {
				$this->lastError = 'GD is not loaded';
				return false;
			}
		}

		if ( (is_string($file) === true) && (is_file($file) === true) && (file_exists($file) === true) && (is_readable($file) === true) ) {
			$file = realpath($file);
			$imgSize = getimagesize($file);
			if ($imgSize !== false) {
				// Get the image from file
				if ($imgSize['mime'] === 'image/jpeg') {
					$resource = imagecreatefromjpeg($file);
				} else if ($imgSize['mime'] === 'image/gif') {
					$resource = imagecreatefromgif($file);
				} else if ($imgSize['mime'] === 'image/png') {
					$resource = imagecreatefrompng($file);
				} else if ($imgSize['mime'] === 'image/bmp') {
					$resource = imagecreatefromgd($file);
				} else {
					$this->lastError = 'Not supported format to load';
					return false;
				}
				$this->imgResource = $resource;
				$this->imgFile = $file;
				$this->imgSize = $imgSize;
				$this->ready = true;
				return true;
			}
			 $this->lastError = 'File is not image';
			return false;
		}
		$this->lastError = 'File load problem (not exist/not file/not readable)';
		return false;
	}

	public function getReady () {
		/*
			Get object ready
		*/

		return $this->ready;
	}

	public function getWidth () {
		/*
			Get image width
		*/

		if ($this->ready === false) {
			 $this->lastError = 'The image not ready';
			return false;
		}

		return $this->imgSize[0];
	}

	public function getHeight () {
		/*
			Get image height
		*/

		if ($this->ready === false) {
			 $this->lastError = 'The image not ready';
			return false;
		}

		return $this->imgSize[1];
	}

	public function getMime () {
		/*
			Get image mime type
		*/

		if ($this->ready === false) {
			 $this->lastError = 'The image not ready';
			return false;
		}

		return $this->imgSize['mime'];
	}

	public function getLastError () {
		/*
			Get last error message
		*/

		return $this->lastError;
	}

	public function outputImage ($file = null, $quality = 75) {
		/*
			Outpur result image to file or browser
			if file not specified then output to browser
			$quality - quality 0-100 for JPEG and PNG files
		*/

		if ($this->ready === false) {
			 $this->lastError = 'The image not ready';
			return false;
		}

		if (empty($file)) {
			$file = null;
		} else {
			$file = realpath($file);
		}

		if (!empty($file)) {
			$filePath = substr($file, 0, strrpos($file, '/'));
			if (!is_writable($filePath) && !file_exists($file)) {
				$this->lastError = 'Destination folder is not writable';
				return false;
			} else if ( ($this->overwrite === true) && file_exists($file) && !is_writable($file)) {
				$this->lastError = 'File is write protect';
				return false;
			} else if ( ($this->overwrite === false) && file_exists($file) ) {
				$this->lastError = 'File exist but overwrite is false';
				return false;
			}
		}

		//Normalize quality
		$quality = ($quality > 100) ? 100 : (($quality < 0) ? 0 : $quality);

		// Output image to browser
		if (empty($file) && $this->sendHeader) {
			header('Content-Type: '.$this->imgSize['mime']);
		}

		// Output the image
		if ($this->imgSize['mime'] === 'image/jpeg') {
			$result = imagejpeg($this->imgResource, $file, $quality);
		} else if ($this->imgSize['mime'] === 'image/gif') {
			$result = imagegif($this->imgResource, $file);
		} else if ($this->imgSize['mime'] === 'image/png') {
			$quality = (int) (10 - round($quality/10));
			$quality = ($quality === 10) ? 9 : $quality;
			imagesavealpha($this->imgResource, true);
			$result = imagepng($this->imgResource, $file, $quality);
		} else {
			$this->lastError = 'Not supported format to output';
			return false;
		}

		// Free resource
		// imagedestroy($this->imgResource);

		return $result;
	}

	public function setSendHeader ($state = true) {
		/*
			Set the state of sending header to browser
			$state - new state
		*/

		$this->sendHeader = (boolean) $state;
		return true;
	}

	public function setKeepProportions ($state = true) {
		/*
			Set the state of keep the proportions of image
			$state - new state
		*/

		$this->keepProportions = (boolean) $state;
		return true;
	}

	public function setMaxImageSize ($size = 0) {
		/*
			Set the state of keep the proportions of image
			$state - new state
		*/

		$this->maxImageSize = (int) $size;
		return true;
	}

	public function setIncreaseSmallImage ($state = true) {
		/*
			Set the state of increase small images
			$state - new state
		*/

		$this->increaseSmallImage = (boolean) $state;
		return true;
	}

	public function setMime ($mime = '') {
		/*
			Set image mime type
		*/

		if ($this->ready === false) {
			 $this->lastError = 'The image not ready';
			return false;
		}

		if (!empty($mime) || !is_string($mime)) {
			$this->lastError = 'The mime is empty or not string';
			return false;
		}

		$mime = strtolower($mime);
		if (in_array($mime, $this->suportedMime)) {
			$this->imgSize['mime'] = (string) $mime;
			return true;
		} else {
			$this->lastError = 'Mime type is not support';
			return false;
		}
	}

	public function setOverwrite ($state = false) {
		/*
			Set the state of overwrite the image file
			$state - new state
		*/

		$this->overwrite = (boolean) $state;
		return true;
	}

	public function flip ($type = -1) {
		/*
			Flip the image
			$type - type of flip
				-1	-	dont flip
				0	-	horisontal
				1	-	vertical
				2	-	both
		*/

		if ($this->ready === false) {
			 $this->lastError = 'The image not ready';
			return false;
		}

		// Normalize the type
		settype($type, 'integer');
		$type = ( ($type !== -1) && ($type !== 1) && ($type !== 2) ) ? 0 : $type;
		
		if ($type === -1) {
			// Nothing to do
			return true;
		}

		$srcWidth = $this->imgSize[0];
		$srcHeight = $this->imgSize[1];
		$dstWidth = $srcWidth;
		$dstHeight = $srcHeight;
		$srcX = 0;
		$srcY = 0;

		// Create empty image
		if (function_exists('imagecreatetruecolor') && ($this->imgSize['mime'] !== 'image/gif') ) {
		$dstImg = imagecreatetruecolor($srcWidth, $srcHeight);
		} else {
			$dstImg = imagecreate($srcWidth, $srcHeight);
		}

		// Save transparent for png
		$transparentColor = imagecolorallocatealpha($dstImg, 255,255, 255, 127);
		imagefill($dstImg, 0, 0, $transparentColor);
		imagecolortransparent($dstImg, $transparentColor);

		if ( ($type === 0) || ($type === 2) ) {
			// Flip horisontal
			$srcX = $dstWidth - 1;
			$srcWidth = -$dstWidth;
		}
		if ( ($type === 1) || ($type === 2) ) {
			// Flip vertical
			$srcY = $dstHeight - 1;
			$srcHeight = -$dstHeight;
		}

		// Flip image
		if (function_exists('imagecopyresampled') ) {
			$result = imagecopyresampled($dstImg, $this->imgResource, 0, 0, $srcX, $srcY, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
		} else {
			$result = false;
		}

		if ($result === true) {
			$this->imgResource = $dstImg;
			return true;
		} else {
			$this->lastError = 'Some error on flip';
			return false;
		}
	}

	public function rotate ($angle = 0) {
		/*
			Rotate image clockwise to $angle degree
		*/

		if ($this->ready === false) {
			 $this->lastError = 'The image not ready';
			return false;
		}

		// Normalize the angle
		settype($angle, 'integer');
		$angle %= 360;
		$angle = ($angle < 0) ? $angle + 360 : $angle;

		if ($angle === 0) {
			// Nothing to do
			return true;
		}

		// Loading imagerotate equivalent if original function not exist
		if(!function_exists('imagerotate')) {
			require_once('../include/image_rotate_equivalent.php');
			$this->buildInRotate = false;
		}

		// Change  the direction of rotation to clockwise
		if ($this->buildInRotate) {
			$angle = 360 - $angle;
		}

		if (function_exists('imagerotate') ) {
			// 16777215 - white color
			$dstImg = imagerotate($this->imgResource, $angle, 16777215);
		} else {
			 $this->lastError = 'function imagerotate() not exist';
			return false;
		}

		if (is_resource($dstImg) === false) {
			$this->lastError = 'Some error on rotate'.var_dump($dstImg).$this->imgFile;
			return $dstImg;
		}

		$this->imgResource = $dstImg;

		// Update the image size
		$this->updateImageSize();

		return true;
	}

	public function resize ($dstWidth = 0, $dstHeight = 0) {
		/*
			Resize image to $width x $height
			if $width or $height not set or 0 then wil be calculated
		*/

		if ($this->ready === false) {
			 $this->lastError = 'The image not ready';
			return false;
		}

		// Normalize the 
		settype($dstWidth, 'integer');
		settype($dstHeight, 'integer');

		if ( ($dstWidth <= 0) && ($dstHeight <= 0) ) {
			$this->lastError = 'Zero size';
			return false;
		}

		$srcWidth = $this->imgSize[0];
		$srcHeight = $this->imgSize[1];

		// Calculate the missing Argument
		if ($dstWidth <= 0) {
			$dstWidth = round(($srcWidth/$srcHeight) * $dstHeight);
		}
		if ($dstHeight <= 0) {
			$dstHeight = round(($srcHeight/$srcWidth) * $dstWidth);
		}

		// New size calculation acording the aspect ratio
		if ($this->keepProportions === true) {
			if ($dstWidth === max($dstWidth, $dstHeight)) {
				$dstHeight = round(($srcHeight/$srcWidth) * $dstWidth);
			} else {
				$dstWidth = round(($srcWidth/$srcHeight) * $dstHeight);
			}
		}

		// New max size calculation
		if ( ($this->maxImageSize > 0) && (max($dstWidth, $dstHeight) > $this->maxImageSize) ) {
			if ($dstWidth === max($dstWidth, $dstHeight)) {
				$dstHeight = round(($dstHeight/$dstWidth) * $this->maxImageSize);
				$dstWidth = $this->maxImageSize;
			} else {
				$dstWidth = round(($dstWidth/$dstHeight) * $this->maxImageSize);
				$dstHeight = $this->maxImageSize;
			}
		}

		if ( ($srcWidth === $dstWidth) && ($srcHeight === $dstHeight) ) {
			// Nothing to do
			return true;
		}

		// Dont increase size more that original
		if ( ($this->increaseSmallImage === false) && ( ($dstWidth > $srcWidth) || ($dstHeight > $srcHeight) ) ) {
			if ($dstWidth > $srcWidth) {
				$dstWidth = $srcWidth;
			}
			if ($dstHeight > $srcHeight) {
				$dstHeight = $srcHeight;
			}
		}

		// New size calculation acording the aspect ratio
		if ($this->keepProportions === true) {
			if ($dstWidth === max($dstWidth, $dstHeight)) {
				$dstHeight = round(($srcHeight/$srcWidth) * $dstWidth);
			} else {
				$dstWidth = round(($srcWidth/$srcHeight) * $dstHeight);
			}
		}

		// Create empty image
		if (function_exists('imagecreatetruecolor') && ($this->imgSize['mime'] !== 'image/gif') ) {
			$dstImg = imagecreatetruecolor($dstWidth, $dstHeight);
		} else {
			$dstImg = imagecreate($dstWidth, $dstHeight);
		}

		// Save transparent for png
		$transparentColor = imagecolorallocatealpha($dstImg, 255,255, 255, 127);
		imagefill($dstImg, 0, 0, $transparentColor);
		imagecolortransparent($dstImg, $transparentColor);

		// Resize image
		if (function_exists('imagecopyresampled') ) {
			$result = imagecopyresampled($dstImg, $this->imgResource, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
		} else {
			$result = imagecopyresized($dstImg, $this->imgResource, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
		}

		if ($result === true) {
			$this->imgResource = $dstImg;

			// Update the image size
			$this->updateImageSize();

			return true;
		} else {
			$this->lastError = 'Some error on resize';
			return false;
		}
	}

	public function crop ($srcX = 0, $srcY = 0, $dstWidth = 0, $dstHeight = 0) {
		/*
			Crop image from $src_x X $src_y with $width x $height - width and heigth
			if $width or $height not set or 0 then wil be calculated to max size
		*/

		if ($this->ready === false) {
			 $this->lastError = 'The image not ready';
			return false;
		}

		// Normalize the 
		settype($srcX, 'integer');
		settype($srcY, 'integer');
		settype($dstWidth, 'integer');
		settype($dstHeight, 'integer');

		$srcWidth = $this->imgSize[0];
		$srcHeight = $this->imgSize[1];

		if ( ( ($srcX === $srcWidth) && ($srcY === $srcHeight) ) || ( ($dstWidth <= 0) && ($dstHeight <= 0) ) ) {
			$this->lastError = 'Zero size';
			return false;
		}

		// Check the crop range
		$srcX = ($srcX > 0) ? $srcX : 0;
		$srcY = ($srcY > 0) ? $srcY : 0;

		// Calculate the missing Argument
		if ($dstWidth <= 0) {
			$dstWidth = $srcWidth - $srcX;
		}
		if ($dstHeight <= 0) {
			$dstHeight = $srcHeight - $srcY;
		}

		if ($dstWidth > $srcWidth - $srcX) {
			$dstWidth = $srcWidth - $srcX;
		}
		if ($dstHeight > $srcHeight - $srcY) {
			$dstHeight = $srcHeight - $srcY;
		}

		// New max size calculation
		if ( ($this->maxImageSize > 0) && ($this->maxImageSize < max($dstWidth, $dstHeight)) ) {
			if ($dstWidth === max($dstWidth, $dstHeight)) {
				$dstHeight = round(($dstHeight/$dstWidth) * $this->maxImageSize);
				$dstWidth = $this->maxImageSize;
			} else {
				$dstWidth = round(($dstWidth/$dstHeight) * $this->maxImageSize);
				$dstHeight = $this->maxImageSize;
			}
		}

		if ( ($srcWidth === $dstWidth) && ($srcHeight === $dstHeight) ) {
			// Nothing to do
			return true;
		}

		// New size calculation
		if ($this->keepProportions === true) {
			if ($dstWidth === max($dstWidth, $dstHeight)) {
				$dstHeight = round(($srcHeight/$srcWidth) * $dstWidth);
			} else {
				$dstWidth = round(($srcWidth/$srcHeight) * $dstHeight);
			}
		}

		// Create empty image
		if (function_exists('imagecreatetruecolor') && ($this->imgSize['mime'] !== 'image/gif') ) {
			$dstImg = imagecreatetruecolor($dstWidth, $dstHeight);
		} else {
			$dstImg = imagecreate($dstWidth, $dstHeight);
		}

		// Save transparent for png
		$transparentColor = imagecolorallocatealpha($dstImg, 255,255, 255, 127);
		imagefill($dstImg, 0, 0, $transparentColor);
		imagecolortransparent($dstImg, $transparentColor);

		// Crop image
		$result = imagecopy($dstImg, $this->imgResource, 0, 0, $srcX, $srcY, $dstWidth, $dstHeight);

		if ($result === true) {
			$this->imgResource = $dstImg;

			// Update the image size
			$this->updateImageSize();

			return true;
		} else {
			$this->lastError = 'Some error on crop';
			return false;
		}
	}

}
?>