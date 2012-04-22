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

        if (!$this->order) {
            if (method_exists($recordExample, 'listDefaultOrder')) {
                $this->order = call_user_func(array($recordExample, 'listDefaultOrder'));
            } elseif ($recordExample->hasAttribute('create')) {
                $this->order = $recordExample->hasAttribute('create') . ' DESc';
            }
        }

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

            $config = array(
                'sort' => array(
                    'defaultOrder' => $this->order,
                ),
                'pagination'=> array(
                    'pageSize' => Yii::app()->settings->getValue('defaultsPerPage')
                ),
            );
            if ($this->hasModel() && $this->foreignAttribute) {
                $config['criteria'] = array(
                    'condition' => $this->foreignAttribute . ' = :id',
                    'params' => array(
                        ':id' => $this->model->id
                    ),
                );

            }
            $dataProvider=new CActiveDataProvider($this->className, $config);

        }

        if ($this->hasModel()) {
            $id = __CLASS__.'_'.get_class($this->model).'_'.$this->model->id;
        } else {
            $id = __CLASS__.$this->className;
        }

        $pageId = 0;
        $area = '';
        $pageWidgetId = 0;
        $widgetId = 0;
        if ($this->hasModel() && $this->model->hasAttribute('widget_id')) {
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
            'columns'=> array_merge(
                array(
                    array(
                        'class'=>'CCheckBoxColumn',
                        'id'=>$id.'_check',
                    )
                ),
                method_exists($recordExample, 'listColumns') ? call_user_func(array($recordExample, 'listColumns'))
                  : ($recordExample->hasAttribute('widget_id') ? array(
                    array(
                        'name'=>'title',
                        'type'=>'raw',
                        'header'=>Yii::t('cms', 'Title'),
                        'value'=> 'CHtml::link(CHtml::encode($data->widget->title), "#", array("onclick" => "js:javascript:cmsRecordEditForm({$data->id}, \'".get_class($data)."\', \'".$data->widget->id."\', \''.$id.'\');return false; ", "title"=>"'.Yii::t('cms','Edit').'", "ondblclick"=>""))',
                    ),
                ) : ($recordExample->hasAttribute(Yii::app()->language.'_title') ? array(
                    array(
                        'name'=>'title',
                        'type'=>'raw',
                        'header'=>Yii::t('cms', 'Title'),
                        'value'=> 'CHtml::link(CHtml::encode($data->title), "#", array("onclick" => "js:javascript:cmsRecordEditForm({$data->id}, \'".get_class($data)."\', \'0\', \''.$id.'\');return false; ", "title"=>"'.Yii::t('cms','Edit').'", "ondblclick"=>""))',
                    ),
                ) : array())),
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
            'recordExample' => $recordExample,
            'className' => $this->className,
            'recordsGrid' => $recordsGrid,
            'model' => $this->model,
            'pageWidgetId' => $pageWidgetId,
            'widgetId' => $widgetId,
        ));

    }

}
