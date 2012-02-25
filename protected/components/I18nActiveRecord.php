<?php
class I18nActiveRecord extends ActiveRecord
{
    public function i18n()
    {
        return array();
    }

    public function __get($name)
    {
        if (in_array($name, $this->i18n())) {
            $attr = Yii::app()->language . '_' . $name;
            return $this->$attr;
        } else return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->i18n())) {
            $attr = Yii::app()->language . '_' . $name;
            $this->$attr = $value;
        } else parent::__set($name,$value);
    }

    public function localized()
    {
        $fields = array('*');
        $language = Yii::app()->language;
        foreach ($this->i18n() as $field) {
            $fields[] = "`{$language}_{$field}` as `{$field}`";
        }
        $this->getDbCriteria()->mergeWith(array(
            'select'=>$fields
        ));
        return $this;
    }

    public function getAttributes($names=null)
    {
        $ret = parent::getAttributes($names);
        foreach ($this->i18n() as $field) {
            $ret[$field] = $this->$field;
        }
        return $ret;
    }

    public static function getLangs($language='')
    {
        $langs = Language::loadConfig();
        if ($language)
            unset($langs['languages'][$language]);
        return $langs['languages'];

    }

    public function generateAttributeLabel($name)
	{
        $lang = substr($name,0,2);
        $langs = self::getLangs();
        if (in_array($lang, array_keys($langs))) {
            return $this->getAttributeLabel(substr($name,3)) . ' [' . Yii::t('languages', $langs[$lang]) . ']';
        } else {
            return parent::generateAttributeLabel($name);
        }
    }

    public function localizedRules($rules)
    {
        $ret = array();
        $langs = array_keys(self::getLangs(Yii::app()->language));
        foreach ($rules as $rule) {
            $fields = explode(',',$rule[0]);
            $f = array();
            foreach ($fields as $field) {
                $field = trim($field);
                if (in_array($field, $this->i18n())) {
                    foreach ($langs as $lang)
                        $f[] = $lang.'_'.$field;
                }
            }
            $rule[0] = implode(',',array_merge($fields, $f));
            $ret[] = $rule;
        }
        return $ret;
    }

    public function getI18nFieldName($attr, $className='', $language='')
    {
        if (isset($this) && !$className)
            $className = get_class($this);
        if (!$language)
            $language = Yii::app()->language;
        if (in_array($attr, call_user_func(array($className, 'i18n'))))
            $attr = $language . '_' . $attr;
        return $attr;
    }

    public function setI18nFieldValue($attr, $value)
    {
        $className = get_class($this);
        foreach (self::getLangs() as $symbol => $language) {
            $attribute = $this->getI18nFieldName($attr, $className, $symbol);
            $this->$attribute = Yii::t($className.'.unit', $value, array(), Yii::app()->sourceLanguage, $symbol);
        }
    }

    public function getAll($condition = '', $params = array(), $columns = '*')
    {
        if (method_exists($this, 'i18n')) {
            $l10nColumns = array();
            foreach ($this->i18n() as $column) {
                $l10nColumns[] = '`' . $this->getI18nFieldName($column) . '` as `' . $column . '`';
            }
        }
        if (is_string($columns)) {
            $columns .= ', ' . implode(',', $l10nColumns);
        } elseif (is_array($columns)) {
            $columns = array_merge($columns, $l10nColumns);
        }
        return parent::getAll($condition, $params, $columns);
    }
    
}