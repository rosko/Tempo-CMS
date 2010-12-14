<h2><?=Yii::t('cms', 'Units installation')?></h2>
<div>
<?=CHtml::form()?>
<?php foreach($errors as $error) { ?>
<p class="errorMessage"><?=$error?></p>
<?php } ?>
<table>
<?php foreach ($units as $className => $unit) { ?>
    <tr>
        <td width="32"><img src="<?=$unit['icon']?>" /></td>
        <td width="32"><?=CHtml::checkBox('Units['.$className.']', $unit['installed'], array(
            'id' => 'Install_'.$className
        ))?></td>
        <td><?=CHtml::label($unit['name'], 'Install_'.$className)?></td>
    </tr>
<?php } ?>
</table>
<input type="submit" name="save" value="<?=Yii::t('cms', 'Save')?>" />
</form>
</div>