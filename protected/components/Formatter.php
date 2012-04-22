<?php

class Formatter extends CFormatter
{
    /**
     * @var array the format used to format size (bytes). Two elements may be specified: "base" and "decimals".
     * They correspond to the base at which KiloByte is calculated (1000 or 1024) bytes per KiloByte and
     * the number of digits after decimal point.
     */
    public $sizeFormat=array('base'=>1024,'decimals'=>1);


    /**
     * formatS the value as a size in human readable form.
     * @params integer value to be formatted
     * @return string the formatted result
     */
    public function formatSize($value,$units=null)
    {
        if ($units===null) {
            $units=array(
                '{n} byte|{n} bytes',
                '{n} KB|{n} KB',
                '{n} MB|{n} MB',
                '{n} GB|{n} GB',
                '{n} TB|{n} TB'
            );
        }
        $base = $this->sizeFormat['base'];
        for($i=0; $base<=$value; $i++) $value=$value/$base;
        return Yii::t('cms', $units[$i], round($value, $this->sizeFormat['decimals']));
    }

    public function formatBoolean($value)
    {
        return $value ? Yii::t('cms', $this->booleanFormat[1]) : Yii::t('cms', $this->booleanFormat[0]);
    }
}