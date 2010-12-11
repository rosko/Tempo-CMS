<p>
{foreach $languages as $symbol => $language}
    {if $page.parent_id == 0}
        {link text={t text=$language cat='languages' lang=$symbol} url="page/view?language={$symbol}"}
    {else}
        {$alias_param="{$symbol}_alias"}
        {$url_param="{$symbol}_url"}
        {link text={t text=$language cat='languages' lang=$symbol} url="page/view?id={$page.id}&language={$symbol}&alias={$page.$alias_param}&url={$page.$url_param}"}
    {/if}
{/foreach}
</p>