<?php if ($unit->title) { ?>
    <h2><?=$unit->title?></h2>
<?php } ?>

<?=$content->text?>

<?php if ($content->author) { ?>
    <p>Автор: <?=$content->author?></p>
<?php } ?>
