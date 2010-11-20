<!--[if lte IE 7]>
<style type="text/css">
    ul.dropdown ul li	{ display: inline; width: 100%; }
</style>
<![endif]-->
<script type="text/javascript">
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
</script>

{function name=tree tree=[] path='0,1' recursive=0}
{if $tree[$path]}
    {foreach $tree[$path] as $item}

    <li>{link text=$item.title url="page/view?id={$item.id}"}

        {$p = "{$path},{$item.id}"}

        {if $tree[$p] && $recursive > 1}
        <ul class="submenu">
            {tree tree=$tree path=$p recursive=$recursive-1}
        </ul>
        {/if}

    </li>

    {/foreach}
{/if}
{/function}


<ul class="dropdown">
    {tree tree=$tree recursive=$content.recursive}
</ul>