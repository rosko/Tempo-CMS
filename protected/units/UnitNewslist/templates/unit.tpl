{if $unit.title}
<h3>{$unit.title}</h3>
{/if}

{if count($items)}

    {foreach $items as $item}
        {$item}
    {/foreach}


{else}

    <h2>{t text='empty'}</h2>

{/if}
