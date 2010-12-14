{if $unit.title}
    {if $pageunit.page_id != $page.id}
        <h2><a href="{$unitUrl}">{$unit.title}</a></h2>
    {else}
    <h2>{$unit.title}</h2>
    {/if}
{/if}

{if $content.date}
    <p>{t text='Date'}: {dateformat pattern="d MMMM yyyy" time=$content.date}</p>
{/if}

{$content.text}

<ul>
{if $sectionUrl && (!isset($in_section) || !$in_section)}
    <li>{t text='Section'}: <a href="{$sectionUrl}">{$sectionTitle}</a></li>
{/if}

{if $content.source || $content.url}
    <li>
        {if $content.source}{t text='News source'}:{/if}

        {if $content.url}<a href="{$content.url}">{/if}

        {if $content.source}
            {$content.source}
        {else}
            {t text='News source'}
        {/if}

        {if $content.url}</a>{/if}
    </li>
{/if}

</ul>
