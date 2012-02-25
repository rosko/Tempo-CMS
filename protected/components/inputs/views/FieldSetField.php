    <div class="FieldSet_field" rel="<?=$k?>">
        <span class="ui-icon ui-icon-arrowthick-2-n-s" style="float:left;"></span>
        <input rel="type" type="hidden" name="<?=$name?>[<?=$k?>][type]" value="<?=$field['type']?>" />
        <input rel="name" type="hidden" name="<?=$name?>[<?=$k?>][name]" value="<?=$field['name']?>" />
        <fieldset>
            <legend>
                <strong><?php $l = $FS->typesLabels(); echo $l[$field['type']];?></strong><br />
                <input type="text" name="<?=$name?>[<?=$k?>][label][<?=Yii::app()->language?>]" value="<?=$field['label'][Yii::app()->language]?>" />
                <span class="cms-icon-inline cms-icon-small-settings"></span>
                <a href="#" class="FieldSet_field_toggleoptions"><?=Yii::t('cms', 'Show options')?></a>
                <span class="cms-icon-inline cms-icon-small-delete"></span>
                <a href="#" class="FieldSet_field_delete"><?=Yii::t('cms', 'Delete this field')?></a>
            </legend>
            <div class="cms-hidden FieldSet_field_options">
                <?php foreach($langs as $lang=>$language) { ?>
                <div>
                    <input type="text" name="<?=$name?>[<?=$k?>][label][<?=$lang?>]" value="<?=$field['label'][$lang]?>" />
                    [<?=Yii::t('languages', $language)?>]
                </div>
                <?php } ?>

                <a href="#" class="FieldSet_field_togglehint"><?=Yii::t('cms', 'Hint')?></a>
                <fieldset class="cms-hidden FieldSet_field_hint">
                    <legend>
                        <input type="text" name="<?=$name?>[<?=$k?>][hint][<?=Yii::app()->language?>]" value="<?=$field['hint'][Yii::app()->language]?>" />
                    </legend>
                    <?php foreach($langs as $lang=>$language) { ?>
                    <div>
                        <input type="text" name="<?=$name?>[<?=$k?>][hint][<?=$lang?>]" value="<?=$field['hint'][$lang]?>" />
                        [<?=Yii::t('languages', $language)?>]
                    </div>
                <?php } ?>
                </fieldset>

<?php if (!empty($FS->attributes) && is_array($FS->attributes[$field['type']])) { ?>
                <br /><a href="#" class="FieldSet_field_toggleattrs"><?=Yii::t('cms', 'Params')?></a>
                <fieldset class="FieldSet_field_attrs">
                    <?php
                        $form_array = $FS->attributes[$field['type']];
                        foreach ($form_array as $_k => $_v) {
                            $form_array[$_k]['label'] = Yii::t('cms', ucfirst($_k));
                            if (isset($field[$_k]))
                                $form_array[$_k]['default'] = $field[$_k];
                        }
                        $vm = new VirtualModel($form_array);
                        $form = new CForm($vm->formMap, $vm);
                        $newName = $name.'['.$k.']';
                        echo str_replace('VirtualModel_', str_replace(array('[',']','-'),'_',$newName).'_', str_replace('VirtualModel[', $newName.'[', str_ireplace('</form>', '', preg_replace('/<form([^>]*)>/msi', '', $form->render()))));
                    ?>
                </fieldset>
<?php } ?>

                <br /><a href="#" class="FieldSet_field_togglerules"><?=Yii::t('cms', 'Validation rules')?></a>
                <ul class="cms-hidden FieldSet_field_rules">
    <?php $rules = $FS->getTypeRules($field['type']);
        $form_array = array();
        $i=0;
        foreach ($rules as $rule) {

$checked=false;
if (is_array($field['rules']))
foreach ($field['rules'] as $r) {
    if ($r[0]==$rule) { $checked=true;break; }
}
            ?>

                <li><input name="<?=$name?>[<?=$k?>][rules][<?=$i?>][0]" value="<?=$rule?>" <?php if ($checked) { echo 'checked'; } ?> type="checkbox"
                           onclick="$(this).nextAll('fieldset:eq(0)').slideToggle('normal', function() {checkHeight<?=$id?>(this);});" />
                    <a onclick="$(this).prev().click();"><?=Yii::t('cms', ucfirst($rule));?></a>
<?php if (!empty($FS->validators[$rule])) { ?>
                <fieldset <?php if (!$checked) { ?>class="cms-hidden"<?php } ?>>
<?php       $form_array = $FS->validators[$rule];
            foreach ($form_array as $_k => $_v) {
                $form_array[$_k]['label'] = Yii::t('cms', ucfirst($_k));
                if ($checked) {
                    $form_array[$_k]['default'] = $r[$_k];
                }
            }
            $vm = new VirtualModel($form_array);
            $form = new CForm($vm->formMap, $vm);
            $newName = $name.'['.$k.'][rules]['.$i.']';
            echo str_replace('VirtualModel_', str_replace(array('[',']','-'),'_',$newName).'_', str_replace('VirtualModel[', $newName.'[', str_ireplace('</form>', '', preg_replace('/<form([^>]*)>/msi', '', $form->render()))));
            ?>
                </fieldset>
<?php } ?>
                </li>

    <?php $i++; } ?>
                </ul>
            </div>
        </fieldset>
    </div>
