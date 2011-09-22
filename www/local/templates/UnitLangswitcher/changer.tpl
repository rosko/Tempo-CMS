<h4>{$unit.title}</h4>
<p>
{foreach $languages as $symbol => $language}
    {if $page.parent_id == 0}
        {link text={t text=$language cat='languages' lang=$symbol} url="view/index?language={$symbol}&{$getParams}"}
    {else}
        {$alias_param="{$symbol}_alias"}
        {$url_param="{$symbol}_url"}
        {link text={t text=$language cat='languages' lang=$symbol} url="view/index?pageId={$page.id}&language={$symbol}&alias={$page.$alias_param}&url={$page.$url_param}&{$getParams}"}
    {/if}
{/foreach}
</p>

