<?php
/**
 * This is the configuration for generating message translations
 * for the Yii requirement checker. It is used by the 'yiic message' command.
 */
return array(
	'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'messagePath'=>dirname(__FILE__),
	'languages'=>array('ru'),
	'fileTypes'=>array('php'),
	'exclude'=>array(
		'.svn',
		'/messages',
		'/views',
	),
);