<div class="cms-caption">
<img style="float:left;margin-right:1em;" valign="baseline" src="<?=call_user_func(array($className, 'icon'));?>" />
<h3><?=$title?></h3>
</div>

<?php
$this->widget('RecordsGrid', array(
        'id' => 'RecordsGrid_'.$className,
        'className' => $className,
    )
);
?>
