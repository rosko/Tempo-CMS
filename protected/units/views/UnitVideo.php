<?php
$video = UnitVideo::getHtmlByUrl($content->video, $content->width, $content->height, $unit->title);
if ($video !== false) {
    echo $video;
    if ($content->show_link) {
        $title = $unit->title ? $unit->title : $content->video;
        echo '<p><a target="_blank" href="'.$content->video.'">'.$title.'</a></p>';
    }
} elseif ($content->html) {
    echo $content->html;
} else {

    if (!Yii::app()->user->isGuest) {
    ?>

<p>
Видео отсутствует или ссылка нераспознана. Многие видеосайты (видеохостинги)
предоставляют возможность вставки видео на другие сайты с помощью HTML-кода.
Попробуйте эту возможность. На сайте, где размещено видео, скопируйте HTML-код
и вставьте в этот блок в соответствующее поле.
</p>

    <?php
    }



}

?>