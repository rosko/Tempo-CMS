<?php
/*******************************************************************
	imagerotate equivalent if original function not exist from imagerotate php.net manual page
*******************************************************************/

if(!function_exists('imagerotate')) {
	function imagerotate($srcImg, $angle, $bgcolor = 0, $ignore_transparent = 0) {
		return imagerotateEquivalent::rotate($srcImg, $angle, $bgcolor, $ignore_transparent);
	}
}

/*
	Imagerotate replacement. ignore_transparent is work for png images
	Also, have some standart functions for 90, 180 and 270 degrees.
	Rotation is clockwise
		Algorithm benchmark (small - better)
			1) standart (not standart angle) - 100%
			2) fast (90 and 270 degre) - 20%
			3) super fast (180 degre only) - 3%
*/

class imagerotateEquivalent {

	static private function rotateX($x, $y, $theta){
		return $x * cos($theta) - $y * sin($theta);
	}

	static private function rotateY($x, $y, $theta){
		return $x * sin($theta) + $y * cos($theta);
	}

	public static function rotate($srcImg, $angle, $bgcolor = 0, $ignore_transparent = 0) {
		$srcw = imagesx($srcImg);
		$srch = imagesy($srcImg);

		// Set rotate to clockwise
		$angle = -$angle;
		// Normalize angle
		$angle %= 360;
		$angle = ($angle < 0) ? $angle + 360 : $angle;

		if($angle == 0) {
			if ($ignore_transparent == 0) {
				imagesavealpha($srcImg, true);
			}
			return $srcImg;
		}

		// Standart case of rotate
		if ($angle === 90) {
			$width = $srch;
			$height = $srcw;
			$minX = 0;
			$maxX = $width;
			$minY = -$height + 1;
			$maxY = 1;
		} else if ($angle === 270) {
			$width = $srch;
			$height = $srcw;
			$minX = -$width + 1;
			$maxX = 1;
			$minY = 0;
			$maxY = $height;
		} else if ($angle === 180) {
			$width = $srcw;
			$height = $srch;
			$minX = -$width + 1;
			$maxX = 1;
			$minY = -$height + 1;
			$maxY = 1;
		} else {
			// Convert the angle to radians
			$theta = deg2rad ($angle);
			// Calculate the width of the destination image.
			$temp = array (
				self::rotateX(0, 0, 0 - $theta),
				self::rotateX($srcw, 0, 0 - $theta),
				self::rotateX(0, $srch, 0 - $theta),
				self::rotateX($srcw, $srch, 0 - $theta)
			);
			$minX = floor(min($temp));
			$maxX = ceil(max($temp));
			$width = $maxX - $minX;

			// Calculate the height of the destination image.
			$temp = array (
				self::rotateY(0, 0, 0 - $theta),
				self::rotateY($srcw, 0, 0 - $theta),
				self::rotateY(0, $srch, 0 - $theta),
				self::rotateY($srcw, $srch, 0 - $theta)
			);
			$minY = floor(min($temp));
			$maxY = ceil(max($temp));
			$height = $maxY - $minY;
		}

		//Create destination image
		$destimg = imagecreatetruecolor($width, $height);
		if ($ignore_transparent == 0) {
			$temp = imagecolorallocatealpha($destimg, 255,255, 255, 127);
			imagefill($destimg, 0, 0, $temp);
			imagecolortransparent($destimg, $temp);
			// If set the default color or white or magic pink then use transparent color
			if ( ($bgcolor == 0) || ($bgcolor == 16777215) || ($bgcolor == 16711935) ) {
				$bgcolor = $temp;
			}
			imagesavealpha($destimg, true);
		}

		if ($angle === 90) {
			for($x = 0; $x < $width; $x++) {
				for($y = 0; $y < $height; $y++) {
					imagesetpixel($destimg, $x, $height-$y-1, imagecolorat($srcImg, $y, $x));
				}
			}
		} else if ($angle === 180) {
			// Use flip image to vertical and horisontal
			imagecopyresampled($destimg, $srcImg, 0, 0, $width - 1, $height - 1, $width, $height, -$width, -$height);
		} else if ($angle === 270) {
			for($x = 0; $x < $width; $x++) {
				for($y = 0; $y < $height; $y++) {
					imagesetpixel($destimg, $width - $x - 1, $y, imagecolorat($srcImg, $y, $x));
				}
			}
		} else {
			// Not standart case, sets all pixels in the new image
			for($x = $minX; $x < $maxX; $x++) {
				for($y = $minY; $y < $maxY; $y++) {
					// Fetch corresponding pixel from the source image
					$srcX = round(self::rotateX($x, $y, $theta));
					$srcY = round(self::rotateY($x, $y, $theta));
					if($srcX >= 0 && $srcX < $srcw && $srcY >= 0 && $srcY < $srch) {
						$color = imagecolorat($srcImg, $srcX, $srcY );
					} else {
						$color = $bgcolor;
					}
					imagesetpixel($destimg, $x - $minX, $y - $minY, $color);
				}
			}
		}
		return $destimg;
	}
}
/*******************************************************************/
?>