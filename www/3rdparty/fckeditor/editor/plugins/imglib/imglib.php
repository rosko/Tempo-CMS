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

//sleep(1);

// Require config and functions files
require('include'.DIRECTORY_SEPARATOR.'config.php');
require('include'.DIRECTORY_SEPARATOR.'function.php');

// Create the upload dir if it not exist
if (!file_exists($CFG->imgUploadDir)) {
	u_mkdir($CFG->imgUploadDir);
}

/*
	Temporary disable prevent access to thumbnail directory
	Waring in this mode for the uploaded pictures thumbnail not created so this mode ONLY FOR CLEAR or other jobs NOT FOR STANDART USE
*/
if (isset($_SERVER['HTTP_REFERER'])) {
	$ref_query = parse_url($_SERVER['HTTP_REFERER']);
	$ref_query = isset($ref_query['query']) ? $ref_query['query'] : '';
	parse_str($ref_query, $ref_query);
}
/*-------------------------------- Show thumbail dir -----------------------------------------------*/
if ( (isset($_GET['showthumb']) && ($_GET['showthumb'] == 1)) || (isset($ref_query['showthumb']) && $ref_query['showthumb'] == 1) ) {
	$CFG->thumbDirName = '';
	$CFG->thumbAutoCreate = false;
}

/*------------------------------- POST Request -----------------------------------------------------*/
if (isset($_POST['cmd']) && !empty($_POST['cmd'])) {
	$command = $_POST['cmd'];
/*----------------------------- Get dir content in XML -------------------------------*/
	if ( ($command === 'list') && isset($_POST['src']) && !empty($_POST['src'])) {
		// Directory to list
		header('Content-Type: text/xml');
		echo get_xml_dir_content($_POST['src'], array('allowed_ext' => $CFG->fileExt, 'file_col' => array('filesize', 'date', 'img_size', 'thumb'), 'dir_col' => array('empty', 'readable', 'date'), 'extra_inf' => array('is_writable' => 1)));
	}
/*----------------------------- Copy or move file or directory -----------------------*/
	if ( ($command === 'copy' || $command === 'move') && isset($_POST['src']) && !empty($_POST['src'])) {
		// If error then out the error code
		if (true !== ($result = move_file_obj($_POST['src'], $_POST['dst'], $command)) ) {
			echo (int) $result;
		}
	}
/*----------------------------- Rename file or directory -----------------------------*/
	if ( ($command === 'rename') && isset($_POST['src']) && !empty($_POST['src'])) {
		// If error then out the error code
		if (true !== ($result = rename_file_obj($_POST['src'], $_POST['new_name'])) ) {
			echo (int) $result;
		}
	}
/*--------------------------------- Create dir ------------------------------------------------*/
	if ( ($command === 'mkdir') && isset($_POST['dst']) && !empty($_POST['dst'])) {
		// If error then out the error code
		if (true !== ($result = create_folder($_POST['dst'], $_POST['name'])) ) {
			echo (int) $result;
		}
	}
/*---------------------------------- Remove dir or folder ---------------------------------*/
	if ( ($command === 'rm') && isset($_POST['dst']) && !empty($_POST['dst']) ) {
		// If error then out the error code
		if (true !== ($result = remove_file_obj($_POST['dst'], ((isset($_POST['rec'])) ? true : false))) ) {
			echo (int) $result;
		}
	}
exit;
}
/*------------------------------ End post request --------------------------------------------------*/

if (isset($_FILES['NewFile']) && !empty($_FILES['NewFile'])) {
	$_FILES['file'] = $_FILES['NewFile'];
}

/*------------------------------ Upload request ----------------------------------------------------*/
if($CFG->enableUpload && isset($_FILES['file']) && !empty($_FILES['file'])) {
	// Upload file
	$ret = process_upload();
	
	if (isset($_GET['fckeditor']) && $ret) {
		echo <<<EOF
<script type="text/javascript">
(function(){var d=document.domain;while (true){try{var A=window.parent.document.domain;break;}catch(e) {};d=d.replace(/.*?(?:\.|$)/,'');if (d.length==0) break;try{document.domain=d;}catch (e){break;}}})();
EOF;
	
		$rpl = array( '\\' => '\\\\', '"' => '\\"' ) ;
		$name = basename(strtr( $ret[0], $rpl ));
		echo 'window.parent.OnUploadCompleted(0,"' . $CFG->imgURL . '/'.  $name . '","' . $name . '", "") ;' ;
		echo '</script>' ;
		exit ;		
	}
	exit;
}
/*-------------------------- End upload request and set upload limit -------------------------------*/


/*-------------------------------- Get request ------------------------------------------------------*/
if (isset($_GET['imglib_config'])) {

	/*------------------------------ Client-side configuration --------------------------------------*/
	echo getClientConfig();
}
/*------------------------------- End get request --------------------------------------------------*/
/*------------------------------- End script -------------------------------------------------------*/
?>