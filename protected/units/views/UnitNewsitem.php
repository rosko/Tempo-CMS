<?php if ($unit->title) { ?>
    <?php if ($pageunit->page_id != $page->id) { ?>
        <h2><a href="<?=$content->getUnitUrl()?>"><?=$unit->title?></a></h2>
    <?php } else { ?>
        <h2><?=$unit->title?></h2>
    <?php } ?>
<?php } ?>


<?php if ($content->date) { ?>
    <p>Дата: <?=Yii::app()->dateFormatter->format("d MMMM yyyy", strtotime($content->date))?></p>
<?php } ?>

<?=$content->text?>

<ul>
<?php if ($content->section && (!isset($in_section) || !$in_section)) { ?>
    <li>Раздел: <a href="<?=$content->section->getUnitUrl()?>"><?=$content->section->unit->title?></a></li>
<?php } ?>

<?php if ($content->source || $content->url) { ?>
    <li>
        <?php if ($content->source) { ?>
        Источник:
        <?php } ?>

        <?php if ($content->url) { ?>
            <a href="<?=$content->url?>">
        <?php } ?>

            <?php if ($content->source != '') { ?>
                <?=$content->source?>
            <?php } else { ?>
                Источник
            <?php } ?>

        <?php if ($content->url) {?>
            </a>
        <?php } ?>
    </li>
<?php } ?>
</ul>
