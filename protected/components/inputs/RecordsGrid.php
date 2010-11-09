<?php

class RecordsGrid extends CInputWidget
{
    public $className;
    public $foreignAttribute;
    public $sectionType='';
    public $sectionId=0;
    public $columns;
    public $order;

    public function run()
    {
        if($this->hasModel()===false)  {
            $this->model = $sectionType::model()->findByPk($sectionId);
        }

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
        $this->widget('zii.widgets.grid.CGridView', array(
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
                        'updateButtonUrl'=>'"javascript:recordEditForm({$data->id}, \'".get_class($data)."\', \'".$ddata->unit->id."\', \''.$id.'\')"',
                        'viewButtonLabel'=>'Перейти на страницу',
                        'viewButtonUrl'=>'"javascript:gotoRecordPage({$data->id}, \'".get_class($data)."\')"',
                        'deleteButtonUrl'=>'"javascript:recordDelete({$data->id}, \'".get_class($data)."\', \'".$ddata->unit->id."\', \''.$id.'\')"',
                    ),
                )
            )
        ));
?>
<script type="text/javascript">
$('#<?=$id?>_check input').live('click', function() {
    var check = $(this).attr('checked');
    var settings = $.fn.yiiGridView.settings['<?=$id?>'];
    $('#<?=$id?> .'+settings.tableClass+' > tbody > tr').each(function(i){
        if (check) {
            $(this).addClass('selected');
        } else {
            $(this).removeClass('selected');
        }
    });
});
</script>
<div id="<?=$id?>_footer">

</div>
<?php

    }

    public function registerClientScript()
    {
        $cs=Yii::app()->getClientScript();
        $cs->registerScriptFile('/js/jquery.ba-bbq.js');

    }
}

?>