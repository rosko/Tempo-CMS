<?php

class ModelBlog_Entry extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/newspaper_add.png';
    }

    public function modelName($language=null)
    {
        return Yii::t('UnitBlog.main', 'Blog/news entry', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'blog_entries';
	}

	public function rules()
	{
		return $this->localizedRules(array(
			array('title', 'required'),
			array('blog_id', 'numerical', 'integerOnly'=>true),
			array('source', 'length', 'max'=>64, 'encoding'=>'UTF-8'),
            array('url', 'length', 'max'=>255, 'encoding'=>'UTF-8'),
            array('image, date, text, annotation', 'safe'),
		));
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
            'title' => Yii::t('UnitBlog.main', 'Title'),
			'text' => Yii::t('UnitBlog.main', 'Content'),
			'date' => Yii::t('UnitBlog.main', 'Date'),
			'source' => Yii::t('UnitBlog.main', 'Source'),
			'url' => Yii::t('UnitBlog.main', 'Link to source'),
            'blog_id' => Yii::t('UnitBlog.main', 'Blog/news section'),
            'annotation' => Yii::t('UnitBlog.main', 'Annotation'),
            'image' => Yii::t('UnitBlog.main', 'Image'),
		);
	}

    public function i18n()
    {
        return array('title', 'text', 'source', 'annotation', 'image');
    }

    public function relations()
    {
        return array_merge(parent::relations(), array(
			'section'=>array(self::BELONGS_TO, 'ModelBlog', 'blog_id'),
        ));
    }

    public static function form()
	{
        $sectionsArray = ModelBlog::getSectionsArray();
        
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitBlog.main', 'Annotation')),
                'title'=>array(
                    'type'=>'text',
                    'size'=>40,
                ),
				'annotation'=>array(
					'type'=>'TextEditor',
                    'kind'=>'ck',
                    'config'=>array(
                        'toolbar'=>'Basic',
                    ),
				),
                'image' => array(
                    'type' => 'FileManager',
                    'width' => 900,
                    'height' => 350,
                    'options' => array(
                        'onlyMimes' => array('image/jpeg', 'image/gif', 'image/png'),
                    ),
                ),
                Form::tab(Yii::t('UnitBlog.main', 'Entry')),
                'blog_id'=> !empty($sectionsArray) ? array(
                    'type'=>'ComboBox',
                    'array'=>$sectionsArray,
                ) : '',
				'date'=>array(
//					'type'=>'DateTimePicker',
                    'type'=>'text',
				),
				'text'=>array(
					'type'=>'TextEditor',
                    'kind'=>'ck',
                    'config'=>array(
                        'toolbar'=>'Basic',
                    ),
				),
                Form::tab(Yii::t('UnitBlog.main', 'Source')),
				'source'=>array(
					'type'=>'text',
					'maxlength'=>64
				),
				'url'=>array(
					'type'=>'Link',
                    'showFileManagerButton'=>false,
                    'showUploadButton'=>false
					//'maxlength'=>255
				),                
			),
		);
	}

    public function behaviors()
    {
        return array(
            'CSerializeBehavior' => array(
                'class' => 'application.behaviors.CSerializeBehavior',
                'serialAttributes' => array('image'),
            ),
        );
    }

    public function scheme()
    {
        return array(
            'title' => 'string',
            'annotation' => 'text',
            'image' => 'text',
            'text' => 'text',
            'date' => 'timestamp',
            'source' => 'string',
            'url' => 'string',
            'blog_id' => 'integer unsigned',
        );
    }

    public function scopesLabels()
    {
        return array(
            'public' => Yii::t('UnitBlog.main', 'Published only'),
            'imported' => Yii::t('UnitBlog.main', 'With source'),
            'recently' => array(
                Yii::t('UnitBlog.main', 'Recent'),
                'limit' => Yii::t('UnitBlog.main', 'Quantity'),
             ),
            'section' => array(
                Yii::t('UnitBlog.main', 'From section'),
                'blog_id' => ''
            )
        );
    }
	
	public function scopes()
	{
		return array(
			'public' => array(
				'order'=>'date DESC',
				'condition'=>'date <= NOW()'
			),
			'imported'=>array(
				'condition'=>'source <> "" OR url <> ""'
			),
		);
	}

    public function hiddenScopes()
    {
        return array('public');
    }
	
	public function namedScopes()
	{
		return array(
			'recently'=>array(
				'limit'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 1,
						'max' => 20,
					)
				)
            ),
			'section'=>array(
				'blog_id'=>array(
					'type'=>'ComboBox',
                    'array'=>ModelBlog::getSectionsArray(),
				)
			)
		);
	}

	public function recently($limit=5)
	{
		$this->getDbCriteria()->mergeWith(array(
			'limit'=>$limit,
            'order'=>'date DESC',
		));
		return $this;
	}
	
    public function section($id=0)
    {
		$this->getDbCriteria()->mergeWith(array(
			'condition'=>'blog_id = :id',
            'params' => array(':id' => $id),
		));
		return $this;
    }

    public function getLink()
    {
        return $this->section->widget->getWidgetUrl(true, array(
            'view' => $this->id . '_' . Page::sanitizeAlias($this->title),
        ));
    }

    public function feedItem()
    {
        return array(
            'title' => null, // тогда используется widget->title,
            'description' => 'text',
            'updated' => 'date',
            'link' => 'link',
        );
    }

    public function templateVars()
    {
        return array(
            '{$widgetUrl}' => Yii::t('UnitBlog.main', 'Link to blog/news entry (in case, when blog/news entry showed as a part of list or blog/news section)'),
            '{$sectionUrl}' => Yii::t('UnitBlog.main', 'Link to blog/news section'),
            '{$sectionTitle}' => Yii::t('UnitBlog.main', 'Name of blog/news section'),
        );
    }

    public function beforeSave()
    {
        if ($this->hasAttribute('date') && !$this->date) {
            $this->date = new CDbExpression('NOW()');
        }
        return parent::beforeSave();
    }

    public function listColumns()
    {
        return array(
            array(
                'name'=>Yii::app()->language.'_title',
                'type'=>'raw',
                'value'=> 'CHtml::link(CHtml::encode($data->title), "#", array("onclick" => "js:javascript:cmsRecordEditForm({$data->id}, \'".get_class($data)."\', 0, \'".$this->grid->id."\');return false; ", "title"=>"'.Yii::t('cms','Edit').'", "ondblclick"=>""))',
            ),
            array(
                'name'=>'blog_id',
                'value' => '$data->section->widget->title',
            ),
            'date',
        );
    }

    public function listDefaultOrder()
    {
        return 'date DESC';
    }

    public function listOperations()
    {
        $sectionsArray = ModelBlog::getSectionsArray();
        $sectionHtml = array();
        foreach ($sectionsArray as $id => $title) {
            $sectionHtml[] = '<option value="'.intval($id).'">'.$title.'</option>';
        }
        $sectionHtml = implode('', $sectionHtml);
        $okButton = Yii::t('UnitBlog.main', 'Move');

        return array(
            'move' => array(
                'title' => Yii::t('UnitBlog.main', 'Move to'),
                'click' => 'js:'.<<<JS
function(gridId, elem) {
                    $('#'+gridId+'_footeradv').html('<select id="'+gridId+'_section">{$sectionHtml}</select> <input id="'+gridId+'_sectionselect" type="button" value="{$okButton}" />');
                    $('#'+gridId+'_sectionselect').click(function(){
                        var sectionId = $('#'+gridId+'_section').val();
                        var ids = $.fn.yiiGridView.getSelection(gridId);
                        cmsAjaxSave('/?r=records/massUpdate&className=ModelBlog_Entry&'+$.param({id: ids})+'&fieldName=blog_id&fieldValue='+sectionId, '', 'GET', function(){
                            $.fn.yiiGridView.update(gridId);
                        });
                    });
                    return false;
}
JS
            ),
        );
    }

}
