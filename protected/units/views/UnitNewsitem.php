<?php if ($unit->title) { ?>
    <h2><?=$unit->title?></h2>
<?php } ?>

<?php if ($content->date) { ?>
    <p>Дата: <?=Yii::app()->dateFormatter->format("d MMMM yyyy", strtotime($content->date))?></p>
<?php } ?>

<?=$content->text?>


<?php if ($content->source) { ?>
    <p>Источник: <?php if ($content->url) {
        ?><a href="<?=$content->url?>"><?php }
        ?><?=$content->source?><?php if ($content->url) {
        ?></a><?php }
        ?></p>
<?php } ?>
