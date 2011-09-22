{if $unit.title}
    {if $pageUnit.page_id != $page.id}
        <h2><a href="{$unitUrl}">{$unit.title}</a></h2>
    {else}
    <h2>{$unit.title}</h2>
    {/if}
{/if}

<ul>
{if $sectionUrl && (!isset($in_section) || !$in_section)}
    <li>{t text='Section'}: <a href="{$sectionUrl}">{$sectionTitle}</a></li>
{/if}

{if $content.source || $content.url}
    <li>
        {if $content.source}{t text='Source'}:{/if}

        {if $content.url}<a href="{$content.url}">{/if}

        {if $content.source}
            {$content.source}
        {else}
            {t text='Source'}
        {/if}

        {if $content.url}</a>{/if}
    </li>
{/if}

</ul>

{if $pageUnit.page_id != $page.id && $content.annotation}
    {if $content.image}
    <table width="100%" border="0">
        <tr>
            <td style="width:50%;" valign="top">
            <a href="{$unitUrl}"><img src="{$content.image}" width="100%" /></a>
            </td>
            <td style="padding-left:15px;width:50%;" valign="top">
   {/if}
{if $content.date}
    <p>{t text='Date'}: {dateformat pattern="d MMMM yyyy" time=$content.date}</p>
{/if}


                {$content.annotation}
    {if $content.image}
                {if $content.text && $content.text != $content.annotation}
                <p><a href="{$unitUrl}">{t text="More"}</a></p>
                {/if}
            </td>
        </tr>
    </table>
    {/if}
    
{else}
    {$content.text}
{/if}

