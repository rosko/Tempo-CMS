<h1>{$unit.title}</h1>

{$pager}

{foreach $items as $item}
    {$item}
    <hr />
{/foreach}

{if count($items)>=5}
    {$pager}
{/if}