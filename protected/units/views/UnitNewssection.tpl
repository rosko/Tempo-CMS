<h2>{$unit.title}</h2>

{$pager}

{foreach $items as $item}
    {$item}
{/foreach}

{if count($items)>=5}
    {$pager}
{/if}