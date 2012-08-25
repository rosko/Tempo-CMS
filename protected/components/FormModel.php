<?php

class FormModel extends CFormModel
{
    private $_attributes=array();

    public static function localizedForm($form)
    {
        $f = array();
        foreach ($form['elements'] as $k => $v) {
            $f[$k] = $v;
            if (is_array($v) && in_array($k, self::i18n())) {
                foreach (array_keys(I18nActiveRecord::getLangs(Yii::app()->language)) as $language)
                    $f[$language.'_'.$k] = $v;
            }
        }
        $form['elements'] = $f;
        return $form;
    }

    public static function localizedRules($rules)
    {
        $ret = array();
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        foreach ($rules as $rule) {
            $fields = explode(',',$rule[0]);
            $f = array();
            foreach ($fields as $field) {
                $field = trim($field);
                if (in_array($field, self::i18n())) {
                    foreach ($langs as $lang)
                        $f[] = $lang.'_'.$field;
                }
            }
            $rule[0] = implode(',',array_merge($fields, $f));
            $ret[] = $rule;
        }
        return $ret;
    }

    public static function localizedLabels($labels)
    {
        $l = array();
        $langs = I18nActiveRecord::getLangs(Yii::app()->language);
        foreach ($labels as $k => $v) {
            $l[$k] = $v;
            if (in_array($k, self::i18n())) {
                foreach (array_keys($langs) as $lang)
                    $l[$lang.'_'.$k] = $v . ' [' . Yii::t('languages', $langs[$lang]) . ']';;
            }
        }
        return $l;
    }

    public function __get($name)
    {
        if (in_array($name, $this->i18n())) {
            $attr = Yii::app()->language . '_' . $name;
            return $this->$attr;
        } else if(isset($this->_attributes[$name]))
            return $this->_attributes[$name];
    }

    public function __set($name,$value)
    {
        if (in_array($name, $this->i18n())) {
            $attr = Yii::app()->language . '_' . $name;
            $this->$attr = $value;
        } else $this->setAttribute($name,$value);
    }

    public function __isset($name)
    {
        return isset($this->_attributes[$name]);
    }

    public function __unset($name)
    {
        if(isset($this->_attributes[$name]))
            unset($this->_attributes[$name]);
    }

    public function setAttribute($name,$value)
    {
        if(property_exists($this,$name))
            $this->$name=$value;
        else
            $this->_attributes[$name]=$value;
        return true;
    }

    public function getAttributes($names=null)
    {
        $ret = parent::getAttributes($names);
        foreach ($this->i18n() as $field) {
            $ret[$field] = $this->$field;
        }
        return $ret;
        //return $this->_attributes;
    }

    public function  attributeNames()
    {
        return array_keys($this->_attributes);
    }

    public static function i18n()
    {
        return array();
    }

}