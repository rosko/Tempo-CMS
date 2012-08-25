<?php

class Select2 extends CInputWidget
{
    public $related;
    public $showAttribute = 'name';
    public $searchAttribute;

    public $classNames;

    public $options = array();
    public $data = array();

    public function run()
    {
        list($name, $id) = $this->resolveNameID();

        if (isset($this->htmlOptions['id'])) {
            $id = $this->htmlOptions['id'];
        } else {
            $this->htmlOptions['id'] = $id;
        }

        if (isset($this->htmlOptions['name'])) {
            $name = $this->htmlOptions['name'];
        } else {
            $this->htmlOptions['name'] = $name;
        }

        $defaultOptions = array(
            'width' => '100%',
        );

        if (!$this->searchAttribute) $this->searchAttribute = $this->showAttribute;
        $element = '';

        if ($this->hasModel()) {

            // Если используется совместно с relations
            if ($this->related != null) {

                $relations = $this->model->relations();
                if (isset($relations[$this->related])) {

                    $relation = $relations[$this->related];

                    $items = $this->model->getRelated($this->related);

                    // Находим список значений поля
                    $data = array();
                    $selected = array();
                    $showAttributes = explode(',', $this->showAttribute);
                    $showAttributes = array_map('trim', $showAttributes);
                    if (is_array($items)) {

                        foreach ($items as $item) {
                            $d = array('id' => $item->id);
                            foreach ($showAttributes as $attr) {
                                if ($item->hasAttribute($attr) || $item->hasProperty($attr)) {
                                    $d[$attr] = $item->{$attr};
                                }
                            }
                            $data[] = $d;
                            $selected[] = $item->id;
                        }
                        $this->options['multiple'] = true;

                    } else {
                        $selected[] = $items->attributes;
                    }

                    $defaultOptions['ajax'] = self::initAjax();

                    $this->options['ajax']['url'] = Yii::app()->createAbsoluteUrl('records/search', array(
                        'className' => $relation[1],
                        'fieldName' => $this->searchAttribute,
                    ));

                    self::setDefaultFormat($showAttributes);
                    self::setInitSelection($data);

                    $element = CHtml::hiddenField($name, implode(',', $selected), $this->htmlOptions);

                }

            }

            $element = $element ? $element : CHtml::activeDropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions);

        } else {

            if (!empty($this->classNames) && is_array($this->classNames)) {

                $this->options['multiple'] = true;
                $defaultOptions['ajax'] = self::initAjax();
                $this->options['ajax']['url'] = Yii::app()->createAbsoluteUrl('records/multiSearch', array(
                    'classNames' => serialize($this->classNames),
                ));

                self::setDefaultFormat(array('text'));
                self::setInitSelection($this->data);

                $selected = array();
                foreach ($this->data as $item) {
                    $selected[] = $item['id'];
                }

                $element = CHtml::hiddenField($name, implode(',', $selected), $this->htmlOptions);

            }

            $this->htmlOptions['id'] = $this->id;
            $element = $element ? $element : CHtml::dropDownList($this->name, $this->value, $this->data, $this->htmlOptions);

        }
        echo $element;

        $this->options = CMap::mergeArray($defaultOptions, $this->options);
        Yii::app()->clientScript->registerPackage('select2');

        $options = $this->options ? CJavaScript::encode($this->options) : '';
        Yii::app()->clientScript->registerScript('Select2#' . $id, "jQuery('#{$id}').select2({$options});");



    }

    protected function initAjax()
    {
        return array(
            'dataType' => 'json',
            'data' => 'js:'.<<<DATA
    function(term,page) {
        return {
            searchValue: term,
            page: page
        };
    }
DATA
        ,
            'results' => 'js:'.<<<DATA
    function (data,page) {
        return {results: data.results, more: data.more};
    }
DATA
        );
    }

    protected function setDefaultFormat($showAttributes)
    {
        $formatAttributes = implode(' + ", " + object.', $showAttributes);
        $this->options['formatResult'] = 'js:'.<<<DATA
                        function (object, container, query) {
                            return object.{$formatAttributes};
                        }
DATA;
        $this->options['formatSelection'] = 'js:'.<<<DATA
                        function (object, container, query) {
                            return object.{$formatAttributes};
                        }
DATA;

    }

    public function setInitSelection($data)
    {
        $jsonEncodedData = CJavaScript::encode($data);
        $this->options['initSelection'] = 'js:'.<<<DATA
                        function (element, callback) {
                            var data = {$jsonEncodedData};
                            callback(data);
                        }
DATA;
    }

}
