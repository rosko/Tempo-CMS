<h3><?=Yii::t('cms', 'This page consists children pages')?></h3>
<?=CHtml::form($this->createAbsoluteUrl('page/pageDelete', array('id'=>$model->id)));?>
<ul>
    <li><input class="cms-button cms-button-create" type="submit" id="deletechildren" name="deletechildren" value="<?=Yii::t('cms', 'Delete with all children pages')?>" /></li>
    <li><input class="cms-button cms-button-create" type="submit" id="movechildren" name="movechildren" value="<?=Yii::t('cms', 'Delete this page, and move children pages on other place')?>" /></a>
<br />
<?=Yii::t('cms', 'To page')?>:
<br />
<?php
$this->widget('PageSelect', array(
        'model'=>$model,
        'attribute'=>'parent_id',
        'name'=>'newParent',
        'size'=>60
    ));
?>
    
    </li>
</ul>


</form>
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
