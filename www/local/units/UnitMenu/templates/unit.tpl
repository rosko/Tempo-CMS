{registercss file="menu.css"}
{registerjs file="menu.js"}
<!--[if lte IE 7]>
<style type="text/css">
    ul.dropdown ul li   { display: inline; width: 100%; }
</style>
<![endif]-->
<style type="text/css">
ul.dropdown, ul.dropdown ul {
	background:white url({publish file='bg.gif'}) repeat left top;
}

ul.dropdown li {
	background:white url({publish file='bg.gif'}) repeat left top;
}
</style>

{function name=tree tree=[] path='0,1' recursive=0}
{if $tree[$path]}
    {foreach $tree[$path] as $item}
    {if !$item.virtual}
    <li>{link text=$item.title url="view/index?pageId={$item.id}&alias={$item.alias}&url={$item.url}"}

        {$p = "{$path},{$item.id}"}

        {if $tree[$p] && $recursive > 1}
        <ul class="submenu">
            {tree tree=$tree path=$p recursive=$recursive-1}
        </ul>
        {/if}

    </li>
    {/if}
    {/foreach}
{/if}
{/function}


<ul class="dropdown">
    {tree tree=$tree recursive=$content.recursive}
</ul>