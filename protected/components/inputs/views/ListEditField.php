    <div class="ListEdit_field" rel="<?=$i?>">
        <span class="ui-icon ui-icon-arrowthick-2-n-s" style="float:left;"></span>
        <fieldset>
            <legend>
                <?php if ($LE->i18n) { ?>
                    <input type="text" name="<?=$name?>[<?=$i?>][<?=Yii::app()->language?>]" value="<?=$item[Yii::app()->language]?>" />
                    <img style="vertical-align:middle;margin-left:15px;" src="<?=Toolbar::getIconUrlByAlias('settings', '', 'fatcow', '16x16')?>" />
                    <a href="#" class="ListEdit_field_toggleoptions"><?=Yii::t('cms', 'Show translates')?></a>
                <?php } else { ?>
                    <input type="text" name="<?=$name?>[<?=$i?>]" value="<?=$item?>" />
                <?php } ?>
                <img style="vertical-align:middle;margin-left:15px;" src="<?=Toolbar::getIconUrlByAlias('delete', '', 'fatcow', '16x16')?>" />
                <a href="#" class="ListEdit_field_delete"><?=Yii::t('cms', 'Delete this field')?></a>
            </legend>
            <?php if ($LE->i18n) { ?>
                <div class="hidden ListEdit_field_options">
                    <?php foreach($langs as $lang=>$language) { ?>
                    <div>
                        <input type="text" name="<?=$name?>[<?=$i?>][<?=$lang?>]" value="<?=$item[$lang]?>" />
                        [<?=Yii::t('languages', $language)?>]
                    </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </fieldset>
    </div>

