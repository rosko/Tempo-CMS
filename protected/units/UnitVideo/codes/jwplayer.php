<?php
    $id = str_replace(array('?', '=', '&'),
                      array('%3F', '%3D', '%26'), $id);
    $jwplayerPath=Yii::app()->params['_path']['jwplayer'] = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.vendors.jwplayer'));
?>
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="<?=$width?>" height="<?=$height?>">
    <param name="movie" value="<?=$jwplayerPath?>/player.swf" />
    <param name="allowfullscreen" value="true" />
    <param name="allowscriptaccess" value="always" />
    <param name="wmode" value="opaque" />
    <param name="flashvars" value="file=<?=$id?>&bufferlength=3&dock=false&controlbar.idlehide=true&controlbar.position=over" />
    <embed
        type="application/x-shockwave-flash"
        src="<?=$jwplayerPath?>/player.swf"
        width="<?=$width?>"
        height="<?=$height?>"
        allowscriptaccess="always"
        allowfullscreen="true"
        wmode="opaque"
        flashvars="file=<?=$id?>&bufferlength=3&dock=false&controlbar.idlehide=true&controlbar.position=over"
    />
</object>