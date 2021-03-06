<?php

class PageUrlValidator extends CUniqueValidator
{
	public $encoding=false;

    public function restrictedUrls()
    {
        return array(
            '/site/captcha',
            '/site/login',
            '/site/logout',
            '/widget/edit',
            '/view/widget',
            '/view/page',
            '/login',
            '/users',
            '/feed',
        );
    }

    protected function validateAttribute($object,$attribute)
	{
        if ($object->id == 1) $this->allowEmpty = true;
        $langs = array_keys(I18nActiveRecord::getLangs());
        $attr = $attribute;
        $p = explode('_',$attribute);
        if (in_array($p[0], $langs)) {
            $attr = $p[1];
        }
        foreach ($langs as $lang) {
            $this->attributeName = $lang.'_'.$attr;
            $value = $object->{$this->attributeName};
            $p = explode('/', $value);

            if ((!$this->allowEmpty && ($value == '' || empty($p[count($p)-1]))) ||
                 in_array($value, $this->restrictedUrls())) {
                $this->addError(
                    $object, $attribute,
                    Yii::t('yii','{attribute} is invalid.')
                );
            }
            parent::validateAttribute($object, $attribute);
        }
    }
    
}