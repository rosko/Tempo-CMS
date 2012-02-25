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
