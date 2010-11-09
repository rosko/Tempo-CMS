<?php
$alt = $unit->title ? ' alt="'.$unit->title.'"' : '';
$title = $unit->title ? ' title="'.$unit->title.'"' : '';

$target = $content->target ? ' target="'.$content->target.'"' : '';

if ($content->url) { 
    ?><a href="<?=$content->url?>"<?=$title?><?=$target?>><?php
}

?><img src="<?=$content->image?>" width="<?=$content->width?>" height="<?=$content->height?>"<?=$alt?> /><?php

if ($content->url) { 
    ?></a><?php
} ?>
