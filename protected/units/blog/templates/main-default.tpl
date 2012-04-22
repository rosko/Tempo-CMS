<h2>{$widget.title}</h2>

{$pager}

{foreach $entries as $entry}
    
    <h2><a href="{$entry.url}">{$entry.title}</a></h2>
    
    {if $entry.date}
    <p>{dateformat pattern="d MMMM yyyy HH:mm" time=$entry.date}</p>
    {/if}
    
    {if $entry.image.filename}
    <img align="left" style="margin:0 10px 10px 0;" src="{$entry.image.filename}" width="50%" />
    {/if}
    {$entry.annotation}
        
    {if !$entry@last}
        <hr />
    {/if}

    
    
{/foreach}

{if count($entries)>=5}
    {$pager}
{/if}
