<?php
$csrfTokenName = Yii::app()->getRequest()->csrfTokenName;
$csrfToken = Yii::app()->getRequest()->getCsrfToken();
?>

<?php if ($selectFiles) { ?>
<div id="<?=$id?>_filelist" class="cms-filemanager-filelist">
    <?php if ($multiple && is_array($value)) { ?>
    <?php foreach ($value as $key => $val) { ?>
        <?php Yii::app()->controller->renderPartial('application.components.inputs.views.FileManagerFile', array(
                'ownerId' => $id,
                'ownerName' => $name,
                'multiple' => $multiple,
                'id' => $id.'_'.$key,
                'name' => $name . '[' . $key . ']',
                'config' => $this->element,
                'value' => $val,
                'htmlOptions' => array(
                    'id' => $id.'_'.$key,
                    'name' => $name . '[' . $key . ']',
                ),
            )); ?>
        <?php } ?>
    <?php } else { ?>
    <?php Yii::app()->controller->renderPartial('application.components.inputs.views.FileManagerFile', array(
            'ownerId' => $id,
            'ownerName' => $name,
            'multiple' => $multiple,
            'id' => $id,
            'name' => $name,
            'config' => $this->element,
            'value' => $value,
            'htmlOptions' => array(
                'id' => $id,
                'name' => $name,
            ),
        )); ?>
    <?php } ?>
</div>
<?php }

if ($multiple) { ?>
<script type="text/javascript">
    $('#<?=$id?>_filelist').sortable({
        cursor: 'hand'
    });
</script>
<?php } ?>

<?php if ($selectFiles) { ?>
<a href="#" id="<?=$id?>_divtgl"><?php
    echo $showFileManagerByDefault ? Yii::t('cms', 'Hide filemanager') : Yii::t('cms', 'Select file');
    ?></a>
<div id="<?=$id?>_div" <?php if (!$showFileManagerByDefault) { ?>style="display: none;"<?php } ?>>
    <?=Yii::t('cms', 'Click twice on file to select.');?>
    <?php } ?>

    <div id="<?=$id?>_fm" class="<?=$cssClassName?>" style="height:<?=$height?>px"></div>

<?php if ($selectFiles) { ?>
</div>

<script type="text/javascript">
    $('.<?=$id?>_filename').die('click').live('click', function(){
        $(this).siblings('.<?=$id?>_filedata').toggle();
        return false;
    });
    $('.<?=$id?>_fileform').die('dblclick').live('dblclick', function(){
        var val = $(this).find('input:hidden').val();
        $(this).find('.<?=$id?>_filename').html('<input class="cms-filemanager-filenamedit" size="30" type="text" value="'+val+'" />');
        return false;
    });
    $('.<?=$id?>_fileedit').die('click').live('click', function(){
        var $fileform = $(this).parents('.<?=$id?>_fileform:eq(0)');
        if ($fileform.find('.<?=$id?>_filename input.cms-filemanager-filenamedit').length) {
            $fileform.click();
        } else {
            $fileform.dblclick();
        }
        return false;
    });
    $('.<?=$id?>_fileform').die('click').live('click', function(){
        if ($(this).find('.<?=$id?>_filename input.cms-filemanager-filenamedit').length) {
            var $this = $(this);
            var input = $this.find('.<?=$id?>_filename input.cms-filemanager-filenamedit');
            var val = input.val();
            $this.find('input[type=hidden]').val(val);
            input.remove();
            $this.find('.<?=$id?>_filename').html(cmsFileBaseName(val));
            $this.find('.<?=$id?>_filesize').html('');
            cmsFileSize(val, function(size) {
                if (size) {
                    $this.find('.<?=$id?>_filesize').html(cmsReadableFileSize(size)+'<br />');
                }
                var extension = cmsStrToLower(cmsFileExtension(val));
                if (extension == 'jpeg' || extension == 'jpg' || extension == 'png' || extension == 'gif') {
                    $this.find('.<?=$id?>_fileimage').attr('src', val).attr('width', 48);
                    var img = new Image();
                    img.src = val;
                    img.onload = function() {
                        $this.find('.<?=$id?>_filesize').html($this.find('.<?=$id?>_filesize').html() + this.width+'&times;'+this.height);
                    }
                } else {
                    $this.find('.<?=$id?>_fileimage').attr('src', '/images/icons/fatcow/32x32/document_empty.png').attr('width', 32);
                }
            });
        }
    });
    $('.<?=$id?>_filedelete').die('click').live('click', function(){
        $(this).parent('.<?=$id?>_fileform:eq(0)').remove();
        return false;
    });
    $('#<?=$id?>_divtgl').click(function() {
        if ($('#<?=$id?>_div').css('display') == 'none') {
            $('#<?=$id?>_divtgl').text('<?=Yii::t('cms', 'Hide filemanager')?>');
            $('#<?=$id?>_div').slideDown(function() {
                cmsDialogResize(this);
            });
        } else {
            $('#<?=$id?>_divtgl').text('<?=Yii::t('cms', 'Select file')?>');
            $('#<?=$id?>_div').slideUp(function() {
                cmsDialogResize(this);
            });
        }
        return false;
    });
</script>
<?php } ?>
