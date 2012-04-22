<div class="<?=$ownerId?>_fileform cms-filemanager-fileitem ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <?php if ($multiple) { ?>
    <a title="<?=Yii::t('cms','Delete');?>" href="#" class="<?=$ownerId?>_filedelete cms-filemanager-filedelete">&times;</a>
    <?php } ?>
    <a title="<?=Yii::t('cms','Edit');?>" href="#" class="<?=$ownerId?>_fileedit cms-filemanager-fileedit">&hellip;</a>
<?php
    $filename = is_array($value) ? $value['filename'] : $value;
    $basename = $basename ? $basename : (is_string($filename) ? urldecode(basename($filename)) : '');
    $extension = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
    $isImage = in_array($extension, array('jpg', 'png', 'jpeg', 'gif'));
    $iconUrl = '/images/icons/fatcow/32x32/document_empty.png';
    $iconWidth = 32;
    if ($isImage) {
        $iconUrl = $filename;
        $iconWidth = 48;
    } else {
        if (is_file(Yii::getPathOfAlias('webroot.images.icons.fatcow.32x32').DIRECTORY_SEPARATOR.'file_extension_'.$extension.'.png')) {
            $iconUrl = '/images/icons/fatcow/32x32/file_extension_'.$extension.'.png';
        }
    }
    if (empty($filesize)) {
        if (substr($filename, 0, 1) == '/') {
            $path = Yii::getPathOfAlias('webroot').$filename;
            if (is_file($path)) {
                $filesize = Yii::app()->format->size(filesize($path));
                if ($isImage) {
                    $s = getimagesize($path);
                    $filesize .= '<br />'.$s[0].'&times;'.$s[1];
                }
            }
        }
    }
    if (!$htmlOptions['id']) $htmlOptions['id'] = '%id%';
    $htmlOptions['id'] .= '_filename';
    echo CHtml::hiddenField(($name ? $name : '%name%') .'[filename]', $filename, $htmlOptions);
?>
    <img class="<?=$ownerId?>_fileimage cms-filemanager-fileimage" src="<?=$iconUrl?>" width="<?=$iconWidth?>" />
    <?php if ($config) { ?>
        <a href="#" class="<?=$ownerId?>_filename cms-filemanager-filename"><?=$basename?></a>
    <?php } else { ?>
        <span class="<?=$ownerId?>_filename cms-filemanager-filename"><?=$basename?></span>
    <?php } ?>
    <span class="<?=$ownerId?>_filesize cms-filemanager-filesize"><?=$filesize?></span>
    <div class="<?=$ownerId?>_filedata cms-filemanager-filedata" style="display:none;">
<?php
    if ($result) {
        echo $result;
    } else {
        $this->widget('Fields', array(
                'id' => $id.'_'.data,
                'name' => $name.'[data]',
                'config' => $config,
                'value' => is_array($value) ? $value['data'] : '',
            ));
    }
?>
    </div>
</div>
