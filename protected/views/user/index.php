<img style="float:left;margin-right:1em;" valign="baseline" src="/images/icons/fatcow/32x32/user.png" />
<h3><?=Yii::t('cms', 'Users')?></h3>
<?php

$this->widget('RecordsGrid', array(
    'class_name' => 'User',
    'columns' => array(
        'id',
        'login',
        'email',
        'name',
    ),
    
));
?>