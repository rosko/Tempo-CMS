<?php

// Set the flag of using imgtools
$use_imgtools = true;

// Require config and functions files
$include_path = '..'.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR;
require($include_path.'config.php');
require($include_path.'function.php');
// Require the image edit class
require('imgtools_class.php');


//Preview of image
if (isset($_GET['preview']) && isset($_GET['src']) && (isset($_GET['flip']) || isset($_GET['rotate']) )) {

	// Normalize the varitable
	$src_img = (string) $_GET['src'];
	$flip_type = (isset($_GET['flip'])) ? (int) $_GET['flip'] : -1;
	$rotation_angle = (isset($_GET['rotate'])) ? (int) $_GET['rotate'] : 0;

	// Fix and check the path
	$src_img = realpath(str_replace($CFG->imgURL, $CFG->imgUploadDir, $src_img));
	if (!in_array(strtolower(pathinfo($src_img, PATHINFO_EXTENSION)), $CFG->fileExt) ) {
		echo 'File not allow to edit: '.str_replace($CFG->imgUploadDir, $CFG->imgURL, $src_img)."\n";
		exit;
	}

	// Only edit exist image
	$dst_img = $src_img;
	if (!(file_exists($src_img) && is_file($src_img) && (is_subdir($CFG->imgUploadDir, $src_img) === true))) {
		echo 'File not found: '.str_replace($CFG->imgUploadDir, $CFG->imgURL, $src_img)."\n";
		exit;
	}

	$myImgTools = new imgTools();
	$myImgTools->setIncreaseSmallImage(false);
	$myImgTools->loadImage($src_img);

	if ($myImgTools->getReady()) {
		$myImgTools->resize(500, 500);
		// If image is ready
		if ($flip_type !== -1) {
			// Flip
			$myImgTools->flip($flip_type);
		}
		if ($rotation_angle !== 0) {
			// Rotate
			$myImgTools->rotate($rotation_angle);
		}

		// Save the image
		$myImgTools->outputImage('', 30);
	}
	$last_error = $myImgTools->getLastError();
	if (empty($last_error)) {
		echo 'OK';
	} else {
		echo 'Last error = '.$last_error;
	}
}

// Image process change
if ( (isset($_POST['cmd']) && ($_POST['cmd'] === 'edit')) && isset($_POST['src']) && isset($_POST['dst'])) {

	// Normalize the varitable
	$src_img = (string) $_POST['src'];
	$dst_img = (string) $_POST['dst'];
	$flip_type = (isset($_POST['flip'])) ? (int) $_POST['flip'] : -1;
	$rotation_angle = (isset($_POST['rotate'])) ? (int) $_POST['rotate'] : 0;

	// Fix and check the path
	$src_img = realpath(str_replace($CFG->imgURL, $CFG->imgUploadDir, $src_img));
	// Only edit exist image
	$dst_img = $src_img;

	if (!in_array(strtolower(pathinfo($src_img, PATHINFO_EXTENSION)), $CFG->fileExt) ) {
		echo 'File not allow to edit: '.str_replace($CFG->imgUploadDir, $CFG->imgURL, $src_img)."\n";
		exit;
	}

	if (!(file_exists($src_img) && is_file($src_img) && (is_subdir($CFG->imgUploadDir, $src_img) === true))) {
		echo 'File not found: '.str_replace($CFG->imgUploadDir, $CFG->imgURL, $src_img)."\n";
		exit;
	}

	if (isset($_POST['resize'])) {
		// Input like "width,height"
		$resize_size = explode(',', $_POST['resize']);
		$resize_size = array_pad($resize_size, 2, 0);

		$resize_size[0] = (int) $resize_size[0];
		$resize_size[1] = (int) $resize_size[1];
		if (($resize_size[0] <= 0) && ($resize_size[1] <= 0)) {
			// Notfing to resize
			$resize_size = null;
		}
	}

	if (isset($_POST['crop'])) {
		// Input like "X1,Y1,width,height"
		$crop = explode(',', $_POST['crop']);
		$crop = array_pad($crop, 4, 0);

		for ($i=0; $i<4;$i++) {
			$crop[$i] = (int) $crop[$i];
			$crop[$i] = ($crop[$i] < 0) ? -$crop[$i] : $crop[$i];
		}

		// Check the width and height
		if ( ($crop[2] <= 0) || ($crop[3] <= 0) ) {
			// Notfing to resize
			$crop = null;
		}
	}

	$myImgTools = new imgTools();
	$myImgTools->setOverwrite(1);
	$myImgTools->setKeepProportions(0);
	$myImgTools->loadImage($src_img);

	if ($myImgTools->getReady()) {
		// If image is ready

		if (!empty($crop)) {
			// Crop
			$myImgTools->crop($crop[0], $crop[1], $crop[2], $crop[3]);
			$myImgTools->setKeepProportions(1);
		}

		if (!empty($resize_size)) {
			// Resize
			$myImgTools->resize($resize_size[0], $resize_size[1]);
		}

		if ($flip_type !== -1) {
			// Flip
			$myImgTools->flip($flip_type);
		}

		if ($rotation_angle !== 0) {
			// Rotate
			$myImgTools->rotate($rotation_angle);
		}

		// Save the image
		$myImgTools->outputImage($dst_img, $CFG->imgQuality);

		// Create the thumbails if enabled in config
		if ($CFG->thumbAutoCreate === true) {
			create_file_thumb($dst_img);
		}
	}
	$last_error = $myImgTools->getLastError();
	if (empty($last_error)) {
		echo 'OK';
	} else {
		echo $last_error;
	}
}

/* Thumbnail creation example
	$myImgTools = new imgTools();
	$myImgTools->setOverwrite(1);
	$myImgTools->setMaxImageSize(90);
	$myImgTools->loadImage($_GET['src']);
	$myImgTools->resize(90);
	$myImgTools->outputImage($_GET['dst'], 40);
*/
?>