<h3>Удаляемая страница содержит дочерние страницы</h3>
<?=CHtml::form($this->createAbsoluteUrl('page/pageDelete', array('id'=>$model->id)));?>
<ul>
    <li><input class="cms-button cms-button-create" type="submit" id="deletechildren" name="deletechildren" value="Удалить со всеми дочерними страницами" /></li>
    <li><input class="cms-button cms-button-create" type="submit" id="movechildren" name="movechildren" value="Удалить, а дочерние страницы перенести в другое место" /></a>
<br />
На страницу:
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
