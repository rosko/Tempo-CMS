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
        
        if ($recordExample->hasAttribute('widget_id')) {

            $dataProvider=new CActiveDataProvider($this->className, array(
                'criteria'=> array(
                    'condition'=> $this->foreignAttribute . ' = :id',
                    'with' => 'widget',
                    'params'=>array(
                        ':id' => $this->model->id
                    ),
                ),
                'sort' => array(
                    'attributes' => array(
                        'title'=> array(
                            'asc' => 'widget.'.Widget::getI18nFieldName('title', 'Widget'),
                            'desc' => 'widget.'.Widget::getI18nFieldName('title', 'Widget').' DESC',
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
        $pageWidgetId = 0;
        $widgetId = 0;
        if ($this->model && $this->model->hasAttribute('widget_id')) {
            $pageWidget = PageWidget::model()->find('`widget_id` = :widget_id', array(':widget_id'=>$this->model->widget_id));
            if ($pageWidget) {
                $pageWidgetId = $pageWidget->id;
                $widgetId = $this->model->widget_id;
                $pageId = $pageWidget->page_id;
                $area = $pageWidget->area;
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
    cmsReloadPageWidget({$pageWidgetId}, '.cms-pagewidget[rev={$widgetId}]');
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
                $recordExample->hasAttribute('widget_id') ? array(
                    array(
                        'name'=>'title',
                        'type'=>'raw',
                        'header'=>Yii::t('cms', 'Title'),
                        'value'=> 'CHtml::link(CHtml::encode($data->widget->title), "#", array("onclick" => "js:javascript:cmsRecordEditForm({$data->id}, \'".get_class($data)."\', \'".$data->widget->id."\', \''.$id.'\');return false; ", "title"=>"'.Yii::t('cms','Edit').'", "ondblclick"=>""))',
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
                                'visible' => '$data->hasAttribute("widget_id") && isset($data->widget)',
                            ),
                            'update'=>array(
                                'url' => '"javascript:cmsRecordEditForm({$data->id}, \'".get_class($data)."\', \'".($data->hasAttribute("widget_id") && isset($data->widget) ? $data->widget->id : 0)."\', \''.$id.'\');"',
                            ),
                            'del'=>array(
                                'label'=>Yii::t('cms', 'Delete'),
                                'imageUrl'=>'/images/delete.png',
                                'url'=>'"javascript:cmsRecordDelete({$data->id}, \'".get_class($data)."\', \'".($data->hasAttribute("widget_id") && isset($data->widget) ? $data->widget->id : 0)."\', \''.$id.'\')"',
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
            'pageWidgetId' => $pageWidgetId,
            'widgetId' => $widgetId,
        ));

    }

    public function registerClientScript()
    {
        //$cs=Yii::app()->getClientScript();
        //$cs->registerScriptFile('/js/jquery.ba-bbq.js');

    }
}
