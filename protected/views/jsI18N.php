var cmsI18n = {};
cmsI18n.cms = {
    savingError: "<?=Yii::t('cms', 'Saving error. Attempt after ')?>",
    retryNow: "<?=Yii::t('cms', 'Retry now')?>",
    deleteWarning: "<?=Yii::t('cms', 'Are you really want delete?')?>",
    deletePageWarning: "<?=Yii::t('cms', 'Are you really want delete page?')?>",
    saved: "<?=Yii::t('cms', 'Saved')?>",
    addAnotherWidget: "<?=Yii::t('cms', 'Add another widget')?>",
    addWidget: "<?=Yii::t('cms', 'Add widget')?>",
    deleteWidgetWarning: "<?=Yii::t('cms', 'Are you really want delete this widget?')?>",
    editing: "<?=Yii::t('cms', 'Editing')?>",
    deletingWidget: "<?=Yii::t('cms', 'Widget deleting')?>",
    deleteWidgetRecordConfirm: "<?=Yii::t('cms', 'There are some wigets on page {page} where\'s located this widget.')?>",
    deleteWidgetOnly: "<?=Yii::t('cms', 'Delete the widget only')?>",
    deleteWidgetAndPage: "<?=Yii::t('cms', 'Delete the widget and the whole page (where\'s widget located)')?>",
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