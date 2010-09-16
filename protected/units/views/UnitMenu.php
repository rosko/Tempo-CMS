<!--[if lte IE 7]>
<style type="text/css">
    ul.dropdown ul li	{ display: inline; width: 100%; }
</style>
<![endif]-->

<?php

$cs = Yii::app()->getClientScript();
$cs->registerScript('UnitMenu'.$content->id, <<<EOD

$(function(){

    $("ul.dropdown li").hover(function(){
    
        $(this).addClass("hover");
        $('ul:first',this).css('visibility', 'visible');
    
    }, function(){
    
        $(this).removeClass("hover");
        $('ul:first',this).css('visibility', 'hidden');
    
    });
    
    $("ul.dropdown li ul li:has(ul)").find("a:first").append(" &raquo; ");

});

EOD
);

?>

<ul class="dropdown">
<?php
$tree = Page::model()->getTree();
function showBranch($tree, $path, $ul_class='') {
    if ($tree[$path])
	foreach ($tree[$path] as $p) {
		echo '<li><a href="' . Yii::app()->controller->createUrl('page/view', array('id'=>$p->id)) . '">' . $p->title . "</a>\n";
		if ($tree[$path.','.$p->id]) {
			echo "<ul class='{$ul_class}'>\n";
			showBranch($tree, $path.','.$p->id);
			echo "</ul>\n";
		}
		echo "</li>\n";
	}
}
showBranch($tree, "0,1", 'sub_menu');

?></ul>
