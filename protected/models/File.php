<?php
class File extends ActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'files';
	}

    public function scheme()
    {
        return array(
            'filename' => 'string',
            'path' => 'string',
            'md5' => 'char(32)',
            'author_id'=>'integer unsigned',
            'editor_id'=>'integer unsigned',
            'title' => 'string',
            'description' => 'text',
            'filesize' => 'integer unsigned', // размер файла
            'size' => 'char(32)',// размер мультимеда файлв (размер картинки, длина видео/аудио файла)
            'type' => 'char(32)',
            'extension' => 'char(32)',
        );
    }
    
}