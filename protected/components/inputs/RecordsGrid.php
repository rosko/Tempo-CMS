<?php

class RecordsGrid extends CInputWidget
{
    public $addButtonTitle;
    public $className;
    public $foreignAttribute;
    public $columns=array();
    public $order;

    public function run()
    {
        $recordExample = new $this->className;
        
        if ($recordExample->hasAttribute('unit_id')) {

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

        $pageId = 0;
        $area = '';
        $pageUnitId = 0;
        $unitId = 0;
        if ($this->model && $this->model->hasAttribute('unit_id')) {
            $pageUnit = PageUnit::model()->find('`unit_id` = :unit_id', array(':unit_id'=>$this->model->unit_id));
            if ($pageUnit) {
                $pageUnitId = $pageUnit->id;
                $unitId = $this->model->unit_id;
                $pageId = $pageUnit->page_id;
                $area = $pageUnit->area;
            }
        }


        $recordsGrid = $this->widget('zii.widgets.grid.CGridView', array(
            'id'=>$id,
            'dataProvider'=>$dataProvider,
            'ajaxUpdate'=> $id,
            'ajaxVar' =>$id,
            'selectableRows' => 2,
            'afterAjaxUpdate' => 'js:function(id, data)'.<<<JS
{
    cmsReloadPageUnit({$pageUnitId}, '.cms-pageunit[rev={$unitId}]');
}
JS
,
            'selectionChanged' => 'js:function(id)'.<<<JS
 {
    var settings = $.fn.yiiGridView.settings[id];
    $('#'+id+' .'+settings.tableClass+' > tbody > tr').each(function(i){
            $(this).find('input[type=checkbox]:eq(0)').attr('checked', $(this).hasClass('cms-selected'));
    });
    var sel = $.fn.yiiGridView.getSelection(id);
    return true;
}
JS
,
            'columns'=> array_merge(
                array(
                    array(
                        'class'=>'CCheckBoxColumn',
                        'id'=>$id.'_check',
                    )
                ),
                $recordExample->hasAttribute('unit_id') ? array(
                    array(
                        'name'=>'title',
                        'type'=>'raw',
                        'header'=>Yii::t('cms', 'Title'),
                        'value'=> 'CHtml::link(CHtml::encode($data->unit->title), "#", array("onclick" => "js:javascript:cmsRecordEditForm({$data->id}, \'".get_class($data)."\', \'".$data->unit->id."\', \''.$id.'\');return false; ", "title"=>"'.Yii::t('cms','Edit').'", "ondblclick"=>""))',
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
                                'url' => '"javascript:cmsGotoRecordPage({$data->id}, \'".get_class($data)."\')"',
                                'visible' => '$data->hasAttribute("unit_id") && isset($data->unit)',
                            ),
                            'update'=>array(
                                'url' => '"javascript:cmsRecordEditForm({$data->id}, \'".get_class($data)."\', \'".($data->hasAttribute("unit_id") && isset($data->unit) ? $data->unit->id : 0)."\', \''.$id.'\');"',
                            ),
                            'del'=>array(
                                'label'=>Yii::t('cms', 'Delete'),
                                'imageUrl'=>'/images/delete.png',
                                'url'=>'"javascript:cmsRecordDelete({$data->id}, \'".get_class($data)."\', \'".($data->hasAttribute("unit_id") && isset($data->unit) ? $data->unit->id : 0)."\', \''.$id.'\')"',
                            ),
                        ),
                    ),
                )
            )
        ), true);

        $this->render('RecordsGrid', array(
            'id' => $id,
            'foreignAttribute' => $this->foreignAttribute,
            'addButtonTitle' => $this->addButtonTitle,
            'pageId' => $pageId,
            'area' => $area,
            'className' => $this->className,
            'recordsGrid' => $recordsGrid,
            'recordExample' => $recordExample,
            'model' => $this->model,
            'pageUnitId' => $pageUnitId,
            'unitId' => $unitId,
        ));

    }

    public function registerClientScript()
    {
        //$cs=Yii::app()->getClientScript();
        //$cs->registerScriptFile('/js/jquery.ba-bbq.js');

    }
}
