<?php

class RecordsGrid extends CInputWidget
{
    public $class_name;
    public $foreign_attribute;
    public $section_type='';
    public $section_id=0;
    public $columns;
    public $order;

    public function run()
    {
        if($this->hasModel()===false)  {
            $this->model = $section_type::model()->findByPk($section_id);
        }

        $dataProvider=new CActiveDataProvider($this->class_name, array(
            'criteria'=> array(
                'condition'=> $this->foreign_attribute . ' = :id',
                'with' => 'unit',
                'params'=>array(
                    ':id' => $this->model->id
                ),
            ),
            'sort' => array(
                'attributes' => array(
                    'title'=> array(
                        'asc' => 'unit.title',
                        'desc' => 'unit.title DESC',
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

        $id = __CLASS__.'_'.get_class($this->model).'_'.$this->model->id;

        $this->getController()->layout = 'blank';
        $this->registerClientScript();


        $records_grid = $this->widget('zii.widgets.grid.CGridView', array(
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
                array(
                    array(
                        'name'=>'title',
                        'type'=>'raw',
                        'header'=>'Название',
                        'value'=> 'CHtml::link(CHtml::encode($data->unit->title), "#", array("onclick" => "js:recordEditForm({$data->id}, \'".get_class($data)."\', \'".$ddata->unit->id."\', \''.$id.'\');return false; ", "title"=>"Редактировать", "ondblclick"=>""))',
                    ),
                ),
                $this->columns,
                array(
                    array(
                        'class'=>'CButtonColumn',
                        'template'=>'{view} {update} {del}',
                        'buttons'=>array(
                            'view'=>array(
                                'label'=>'Перейти на страницу',
                                'url' => '"javascript:gotoRecordPage({$data->id}, \'".get_class($data)."\')"',
                                'visible' => '$data->unit',
                            ),
                            'update'=>array(
                                'url' => '"javascript:recordEditForm({$data->id}, \'".get_class($data)."\', \'".$data->unit->id."\', \''.$id.'\')"',
                            ),
                            'del'=>array(
                                'label'=>'Удалить',
                                'imageUrl'=>'/images/delete.png',
                                'url'=>'"javascript:recordDelete({$data->id}, \'".get_class($data)."\', \'".$data->unit->id."\', \''.$id.'\')"',
                            ),
                        ),
                    ),
                )
            )
        ), true);

        $page_id = 0;
        $area = '';
        $type = '';
        if ($this->model->unit_id) {
            $pageunit = PageUnit::model()->find('`unit_id` = :unit_id', array(':unit_id'=>$this->model->unit_id));
            $page_id = $pageunit->page_id;
            $area = $pageunit->area;
            $type = Unit::getUnitTypeByClassName($this->class_name);
        }

        $this->render('RecordsGrid', array(
            'id' => $id,
            'foreign_attribute' => $this->foreign_attribute,
            'page_id' => $page_id,
            'area' => $area,
            'type' => $type,
            'class_name' => $this->class_name,
            'records_grid' => $records_grid,
            'section_id' => $this->model->id,
            'section_type' => get_class($this->model),
        ));

    }

    public function registerClientScript()
    {
        $cs=Yii::app()->getClientScript();
        $cs->registerScriptFile('/js/jquery.ba-bbq.js');

    }
}

?>