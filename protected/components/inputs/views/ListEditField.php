    <div class="ListEdit_field" rel="<?=$i?>">
        <span class="ui-icon ui-icon-arrowthick-2-n-s" style="float:left;"></span>
        <fieldset>
            <legend>
                <?php if ($LE->i18n) { ?>
                    <input type="text" name="<?=$name?>[<?=$i?>][<?=Yii::app()->language?>]" value="<?=$item[Yii::app()->language]?>" />
                    <span class="cms-icon-inline cms-icon-small-settings"></span>
                    <a href="#" class="ListEdit_field_toggleoptions"><?=Yii::t('cms', 'Show translates')?></a>
                <?php } else { ?>
                    <input type="text" name="<?=$name?>[<?=$i?>]" value="<?=$item?>" />
                <?php } ?>
                <span class="cms-icon-inline cms-icon-small-delete"></span>
                <a href="#" class="ListEdit_field_delete"><?=Yii::t('cms', 'Delete this item')?></a>
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

