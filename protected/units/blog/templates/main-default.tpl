{if $entries}
    
<h2>{$unit.title}</h2>

{$pager}

{foreach $entries as $entry}
    
    <h2><a href="{$entry.url}">{$entry.title}</a></h2>
    
    {if $entry.date}
    <p>{dateformat pattern="d MMMM yyyy HH:mm" time=$entry.date}</p>
    {/if}
    
    {if $entry.image}
    <img align="left" style="margin:0 10px 10px 0;" src="{$entry.image}" width="50%" />
    {/if}
    {$entry.annotation}
        
    {if !$entry@last}
        <hr />
    {/if}

    
    
{/foreach}

{if count($entries)>=5}
    {$pager}
{/if}

{else}
    
    <h4><a href="{$url}">{$unit.title}</a></h4>

    <h2>{$entry.title}</h2>

    {if $entry.date}
    <p>{t text='Date'}: {dateformat pattern="d MMMM yyyy" time=$entry.date}</p>
    {/if}

    {$entry.text}    

    {if $entry.source || $entry.url}
        <li>
            {if $entry.source}{t text='Source'}:{/if}

            {if $entry.url}<a href="{$entry.url}">{/if}

            {if $entry.source}
                {$entry.source}
            {else}
                {t text='Source'}
            {/if}

            {if $entry.url}</a>{/if}
        </li>
    {/if}

    
{/if}