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

/*
 * Put here you user login verification function
 * Sompfing like this
 *	if (!isset($_SESSION['login']) || empty($_SESSION['login'])) {
 *		exit;
 *	}
 */

// Initialize config
$CFG = (object) true;

/*
 * Enable debug output to browser
 * In work configuration must be false with security reasons
 */
$CFG->debug = true;

/*
 * Full path to a directory which holds the images.
 */
$CFG->imgUploadDir = $_SERVER['DOCUMENT_ROOT'] . '/files/';

/*
 * An absolute or relative URL to the image folder WITHOUT end slash.
 * This url is used to generate the source URL of the image.
 */
$CFG->imgURL = '/files';


/*
 * Use all unicode symbols for file name like the й or else
 * Need the test to enable in each case
 * Enable at you risk, curently not work on MS Windows
 */
$CFG->unicodeNames = false;

/*
 * Define the extentions you want to
 * user can upload to your image folders and delete from it.
 */
$CFG->uploadExt = 'gif|png|jpeg|jpg|bmp|doc|xls|docx|xlsx|odt|ods|pdf|rar|zip|txt|htm|html|swf|flv';

/*
 * Define the extentions you want to show within the
 * directory listing.
 * For enable all files - set string empty
 */
$CFG->fileExt =   'gif|png|jpeg|jpg|bmp|doc|xls|docx|xlsx|odt|ods|pdf|rar|zip|txt|htm|html|swf|flv';

/*
 * Format who support the creating of thumbnail
 */
 $CFG->thumbFileExt = 'gif|png|jpeg|jpg';

/*
 * If enabled users will be able to upload
 * files to any viewable directory. You should really only enable
 * this if the area this script is in is already password protected.
 */
$CFG->enableUpload = true;

/*
 * Maximum size of upload file
 * Format:
 * 800000 - in bytes
 * '800k' - 800 kilo bytes
 * '1m' - 1 Mega bytes
 * '2g' - 2 Giga bytes
 * This options "overwrite" the 'upload_max_filesize' php directive.
 * Large value - large WWW-Server load
 * Set to 0 to user 'upload_max_filesize' php directive.
*/
$CFG->maxUploadFileSize = 0;

/*
 * Upload file name format in date() function format
 * If empty then using original file name (Danger)
 * Use '\n' to insert the urlencoded original file name
 * Or use this simple template to name files on date and time of upload 'Y_m_d_H_i_s'
 */
$CFG->uploadNameFormat = '\n';

/*
 * File name suffix added if the file is exist on upload or else file operation
 * You can use the date() function format string to add date or time to copy of file
 * %1$d - its iteration number
 * Examples:
 * 1) $CFG->fileNameSuffix = '_copy_of_Y.m.d_H.i.s_[%1$d]'; - create copy file of file "my_file.jpg" with date and time of copying and iteration number in name like "my_file_copy_of_2010.02.14_10.44.56_[1].jpg"
 * 2) $CFG->fileNameSuffix = '_copy_of_Y.m.d_H.i.s'; - create copy file of file "my_file.jpg" with  with date and time of copying in name like "my_file_copy_of_2010.02.14_10.44.56.jpg"
 */
$CFG->fileNameSuffix = '_[%1$d]';

/*
 * If a user uploads a file with the same
 * name as an existing file do you want the existing file
 * to be overwritten? Typical the script rename the upload files if exist.
 */
$CFG->overwriteFile = false;

/*
 * Bind keyboard shorcuts to some actions
 * Default actions is:
 * - Delete element on 'Del'
 * - Open file in new window on 'Shift+Enter'
 * - Rename file on 'F2'
 * - Create directory on 'F7'
 * - Show upload form on 'Insert'
 * - Cut file on 'Ctrl+X'
 * - Copy file on 'Ctrl+C'
 * - Paste file on 'Ctrl+V'
 */
$CFG->bindKeys = true;

/*
 * Allow your users to browse the subdir of the defined basedir.
 * This only disable browse notfing more!
 */
$CFG->browseSubDir = true;

/*
 * If enabled users will be able to create new directory.
 * You should really only enable this if the area this script
 * is in is already password protected.
 */
$CFG->allowCreateDir = true;

/*
 * Premission to new created directory
 * Default 0755 - yuo good, oters - only view
 * On 744 you can not see thumbnails
 */
$CFG->dirPremision = 0755;

/*
 * If enabled users will be able to delete directory and files.
 * You should really only enable this if the area this script
 * is in is already password protected.
 */
$CFG->allowDelete = true;

/*
 * If enabled users will be able to rename directory and files.
 * You should really only enable this if the area this script
 * is in is already password protected.
 */
$CFG->allowRename = true;

/*
 * If enabled browse sub directory users will be able to copy or move directory and files.
 * You should really only enable this if the area this script
 * is in is already password protected.
 * If you want to disable this use false as value
 */
$CFG->allowFileCopy = ($CFG->browseSubDir) ? true : false;

/*
 * Get upload premision from php.ini file
 */
$CFG->phpEnableUpload = (bool) ini_get('file_uploads');

/*
 * Image quality 0-100 for JPEG and PNG files on save
 */
$CFG->imgQuality = 80;

/*
 * Maximum size to resized images
 * Large value - large WWW-Server load
 * Set to 0 to disable image resize
 */
$CFG->maxImgResize = 4096;

/*
 * Thumbnail directory name, if empty the creating of thumbnail is disabled
 */
$CFG->thumbDirName = 'thmb';

/*
 * Length of file or folder name string on file list
 */
$CFG->fileNameLen = 25;

/*
 * Default view type of file list in imgLib
 * Posible options: list, thumbnail, table
 */
$CFG->defaultViewType = 'thumbnail';

/*
 * Auto create thumbnail for file if no exist
 */
$CFG->thumbAutoCreate = true;

/*
 * Thumbnail max width in px
 */
$CFG->thumbWidth = 120;

/*
 * Thumbnail max height in px
 */
$CFG->thumbHeight = 90;

/*
 * Thumbnail quality 0-100 for JPEG and PNG files
 */
$CFG->thumbQuality = 75;

/*
 * Enable the imgTools - simple image edit tool integrated to context meny
 */
$CFG->enableImgTools = true;

/*
 * Time zone and charser settings
 */
date_default_timezone_set('Europe/London');
ini_set('default_charset', 'UTF-8');

/*
 * Aply some configuration options
 */
// If use imgTools and the upload path is relative - then add updir '../' to start the path
if ( (strpos($CFG->imgUploadDir, '..'.DIRECTORY_SEPARATOR) === 0) && (isset($use_imgtools) && ($use_imgtools === true)) ) {
	$CFG->imgUploadDir = '..'.DIRECTORY_SEPARATOR .$CFG->imgUploadDir;
}
$CFG->imgUploadDir = realpath($CFG->imgUploadDir);
$CFG->thumbDirName = urlencode(trim(urldecode($CFG->thumbDirName)));

// Create the allowed extension arrays
$CFG->uploadExt = explode('|', $CFG->uploadExt);
if (!empty($CFG->fileExt)) {
	$CFG->fileExt = explode('|', $CFG->fileExt);
} else {
	$CFG->fileExt = array();
}
$CFG->thumbFileExt = explode('|', $CFG->thumbFileExt);

if (!$CFG->phpEnableUpload) {
	$CFG->enableUpload = false;
}


if ( (isset($CFG->debug)) && ($CFG->debug === true) ) {
	error_reporting(E_ALL | E_STRICT | E_NOTICE);
	ini_set('display_errors', '1');
} else {
	error_reporting(0);
	ini_set('display_errors', '0');
}
?>