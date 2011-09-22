<div class="cms-caption">
<img style="float:left;margin-right:1em;" valign="baseline" src="/images/icons/fatcow/32x32/user.png" />
<h3><?=Yii::t('cms', 'Users')?></h3>
</div>
<?php

$this->widget('RecordsGrid', array(
    'className' => 'User',
    'columns' => array(
        'id',
        'login',
        'email',
        'displayname',
    ),
    
));
?>