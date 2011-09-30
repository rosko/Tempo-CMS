var cmsI18n = {};
cmsI18n.cms = {
    savingError: "<?=Yii::t('cms', 'Saving error. Attempt after ')?>",
    retryNow: "<?=Yii::t('cms', 'Retry now')?>",
    deleteWarning: "<?=Yii::t('cms', 'Are you really want delete?')?>",
    deletePageWarning: "<?=Yii::t('cms', 'Are you really want delete page?')?>",
    saved: "<?=Yii::t('cms', 'Saved')?>",
    addAnotherUnit: "<?=Yii::t('cms', 'Add another unit')?>",
    addUnit: "<?=Yii::t('cms', 'Add unit')?>",
    deleteUnitWarning: "<?=Yii::t('cms', 'Are you really want delete this unit?')?>",
    editing: "<?=Yii::t('cms', 'Editing')?>",
    deletingUnit: "<?=Yii::t('cms', 'Unit deleting')?>",
    deleteUnitRecordConfirm: "<?=Yii::t('cms', 'There are some units on page {page} where\'s located this unit.')?>",
    deleteUnitOnly: "<?=Yii::t('cms', 'Delete the unit only')?>",
    deleteUnitAndPage: "<?=Yii::t('cms', 'Delete the unit and the whole page (where\'s unit located)')?>",
    deleteRecordWarning: "<?=Yii::t('cms', 'Are you really want delete this record?')?>"
};

function cmsTransliterate(str) {
    var ret = str;
<?php
$transliteration = Page::transliteration();
if (is_array($transliteration) && !empty($transliteration)) { ?>
    var _from = <?=CJavaScript::encode($transliteration[0]);?>;
    var _to = <?=CJavaScript::encode($transliteration[1]);?>;
    ret = cmsStrReplace(_from, _to, str);
<?php } ?>
    return ret;
}