{if $unit.title}
    {if $pageunit.page_id != $page.id}
        <h2><a href="{$unitUrl}">{$unit.title}</a></h2>
    {else}
    <h2>{$unit.title}</h2>
    {/if}
{/if}

{if $content.date}
    <p>Дата: {dateformat pattern="d MMMM yyyy" time=$content.date}</p>
{/if}

{$content.text}

<ul>
{if $sectionUrl && (!isset($in_section) || !$in_section)}
    <li>Раздел: <a href="{$sectionUrl}">{$sectionTitle}</a></li>
{/if}

{if $content.source || $content.url}
    <li>
        {if $content.source}Источник:{/if}

        {if $content.url}<a href="{$content.url}">{/if}

        {if $content.source}
            {$content.source}
        {else}
            Источник
        {/if}

        {if $content.url}</a>{/if}
    </li>
{/if}

</ul>
