<?php
$header = $content->header ? $content->header : 'h2';
?>
<?php if ($unit->title) { ?>
<<?=$header?>><?=$unit->title?></<?=$header?>>
<?php } ?>