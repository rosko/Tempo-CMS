<?php
	
	$dir = '/home/web/redactor/';

	$_FILES['file']['type'] = strtolower($_FILES['file']['type']);

	if ($_FILES['file']['type'] == 'image/png' 
	|| $_FILES['file']['type'] == 'image/jpg' 
	|| $_FILES['file']['type'] == 'image/gif' 
	|| $_FILES['file']['type'] == 'image/JPG' 
	|| $_FILES['file']['type'] == 'image/pjpeg')
	{	
		copy($_FILES['file']['tmp_name'], $dir.'tmp/1.jpg');
		echo 'http://redactor/tmp/1.jpg';
	}
?>
