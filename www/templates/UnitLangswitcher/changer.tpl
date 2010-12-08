<h4>{$unit.title}</h4>
<p>
{foreach $languages as $symbol => $language}
    {link text={t text=$language cat='languages'} url="page/view?id={$page.id}&language={$symbol}"}
{/foreach}
</p>
