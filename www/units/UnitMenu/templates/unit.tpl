{registercss file="menu.css"}
{registerjs file="menu.js"}
<!--[if lte IE 7]>
<style type="text/css">
    ul.dropdown ul li   { display: inline; width: 100%; }
</style>
<![endif]-->

{function name=tree tree=[] path='0,1' recursive=0}
{if $tree[$path]}
    {foreach $tree[$path] as $item}

    <li>{link text=$item.title url="page/view?id={$item.id}&alias={$item.alias}&url={$item.url}"}

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