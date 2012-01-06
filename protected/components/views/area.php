<?php if (!$readOnly) { ?>
<div id="cms-area-<?=$name?>" class="<?php if ($editArea) { ?>cms-area <?php } ?>area" >
<?php } ?>

<?=$output?>

<?php if (!$readOnly) { ?>
</div>
<?php } ?>