{function name=pagelist items=[] recursive=0}
{if is_array($items)}

<ul>

    {foreach $items as $item}
    {if !$item.virtual}
    <li>{link text=$item.title url="page/view?id={$item.id}&alias={$item.alias}&url={$item.url}"}

        {if $item.description && $content.length}
        <p>{$item.description|strip_tags|truncate:{$content.length}|nl2br}</p>
        {/if}
        {if $recursive>1 && is_array($item.children) && count($item.children)}
            {pagelist items=$item.children recursive=$recursive-1}
        {/if}
        
    </li>
    {/if}
    {/foreach}

</ul>

{/if}
{/function}

{if $count_items}

    {if $content.show_title}
        <h3>{$title}</h3>
    {/if}

    {$pager}

    {pagelist items=$items recursive=$content.recursive}

    {if $count_items > 10}
        {$pager}
    {/if}


{/if}
