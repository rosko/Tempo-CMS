<?php

class RecordsGrid extends CInputWidget
{
    public $addButtonTitle;
    public $className;
    public $makePage = false;
    public $foreignAttribute;
    public $sectionType='';
    public $sectionId=0;
    public $columns=array();
    public $order;

    public function run()
    {
        if($this->hasModel()===false && $this->sectionType)  {
            $this->model = call_user_func(array($this->sectionType, 'model'))->findByPk($this->sectionId);
        }

        if ($this->model instanceof Content) {

            $dataProvider=new CActiveDataProvider($this->className, array(
                'criteria'=> array(
                    'condition'=> $this->foreignAttribute . ' = :id',
                    'with' => 'unit',
                    'params'=>array(
                        ':id' => $this->model->id
                    ),
                ),
                'sort' => array(
                    'attributes' => array(
                        'title'=> array(
                            'asc' => 'unit.'.Unit::getI18nFieldName('title', 'Unit'),
                            'desc' => 'unit.'.Unit::getI18nFieldName('title', 'Unit').' DESC',
                            'label' => 'Title',
                        ),
                        '*'
                    ),
                    'defaultOrder' => $this->order,
                ),
                'pagination'=> array(
                    'pageSize' => Yii::app()->settings->getValue('defaultsPerPage')
                ),
            ));

        } else {
            
            $dataProvider=new CActiveDataProvider($this->className, array(
                'sort' => array(
                    'defaultOrder' => $this->order,
                ),
                'pagination'=> array(
                    'pageSize' => Yii::app()->settings->getValue('defaultsPerPage')
                ),
            ));

        }

        if ($this->model) {
            $id = __CLASS__.'_'.get_class($this->model).'_'.$this->model->id;
        } else {
            $id = __CLASS__.$this->className;
        }

        $this->registerClientScript();


        $recordsGrid = $this->widget('zii.widgets.grid.CGridView', array(
            'id'=>$id,
            'dataProvider'=>$dataProvider,
            'ajaxUpdate'=> $id,
            'ajaxVar' =>$id,
            'selectableRows' => 2,
            'selectionChanged' => <<<EOD
js:function(id) {
    var settings = $.fn.yiiGridView.settings[id];
    $('#'+id+' .'+settings.tableClass+' > tbody > tr').each(function(i){
            $(this).find('input[type=checkbox]:eq(0)').attr('checked', $(this).hasClass('selected'));
    });
    var sel = $.fn.yiiGridView.getSelection(id);
    //alert(sel);
    return true;
}
EOD
,
            'columns'=> array_merge(
                array(
                    array(
                        'class'=>'CCheckBoxColumn',
                        'id'=>$id.'_check',
                    )
                ),
                $this->model instanceof Content ? array(
                    array(
                        'name'=>'title',
                        'type'=>'raw',
                        'header'=>Yii::t('cms', 'Title'),
                        'value'=> 'CHtml::link(CHtml::encode($data->unit->title), "#", array("onclick" => "js:javascript:recordEditForm({$data->id}, \'".get_class($data)."\', \'".$ddata->unit->id."\', \''.$id.'\');return false; ", "title"=>"'.Yii::t('cms','Edit').'", "ondblclick"=>""))',
                    ),
                ) : array(),
                $this->columns,
                array(
                    array(
                        'class'=>'CButtonColumn',
                        'template'=>'{view} {update} {del}',
                        'buttons'=>array(
                            'view'=>array(
                                'label'=>Yii::t('cms', 'Go to page'),
                                'url' => '"javascript:gotoRecordPage({$data->id}, \'".get_class($data)."\')"',
                                'visible' => 'isset($data->unit)',
                            ),
                            'update'=>array(
                                'url' => '"javascript:recordEditForm({$data->id}, \'".get_class($data)."\', \'".(isset($data->unit) ? $data->unit->id : 0)."\', \''.$id.'\');"',
                            ),
                            'del'=>array(
                                'label'=>Yii::t('cms', 'Delete'),
                                'imageUrl'=>'/images/delete.png',
                                'url'=>'"javascript:recordDelete({$data->id}, \'".get_class($data)."\', \'".(isset($data->unit) ? $data->unit->id : 0)."\', \''.$id.'\')"',
                            ),
                        ),
                    ),
                )
            )
        ), true);

        $pageId = 0;
        $area = '';
        $type = '';
        $pageUnitId = 0;
        $unitId = 0;
        if ($this->model->unit_id) {
            $pageUnit = PageUnit::model()->find('`unit_id` = :unit_id', array(':unit_id'=>$this->model->unit_id));
            if ($pageUnit) {
                $pageUnitId = $pageUnit->id;
                $unitId = $this->model->unit_id;
                $pageId = $pageUnit->page_id;
                $area = $pageUnit->area;
                $type = Unit::getUnitTypeByClassName($this->className);
            }
        }

        $this->render('RecordsGrid', array(
            'id' => $id,
            'foreignAttribute' => $this->foreignAttribute,
            'addButtonTitle' => $this->addButtonTitle,
            'pageId' => $pageId,
            'area' => $area,
            'type' => $type,
            'className' => $this->className,
            'recordsGrid' => $recordsGrid,
            'sectionId' => $this->model->id,
            'sectionType' => get_class($this->model),
            'pageUnitId' => $pageUnitId,
            'unitId' => $unitId,
        ));

    }

    public function registerClientScript()
    {
        $cs=Yii::app()->getClientScript();
        //$cs->registerScriptFile('/js/jquery.ba-bbq.js');

    }
}
