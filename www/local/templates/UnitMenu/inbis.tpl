<a href='/' {if $page.id == 1}class="active"{/if}>{t text='Home'}</a>
{$path = '0,1'}
{if $tree[$path]}
    {foreach $tree[$path] as $item}
    {if !$item.virtual}
        {if $item.id == $page.id}
        {link text=$item.title url="view/index?pageId={$item.id}&alias={$item.alias}&url={$item.url}" options=[class=>active]}
        {else}
        {link text=$item.title url="view/index?pageId={$item.id}&alias={$item.alias}&url={$item.url}"}
        {/if}
    {/if}
    {/foreach}
{/if}
