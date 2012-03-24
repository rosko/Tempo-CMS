{if $error}
    <h2>{t text='Error'}</h2>
    <p>{$error}</p>
    <p>{link text={t text='Profiles list'} url="view/index?pageId={$page.id}&alias={$page.alias}&url={$page.url}"}</p>
{else}
    {if $table}
        <h2>{$widget.title}</h2>
        {$table}
    {else}
        <p>{link text={t text='Profiles list'} url="view/index?pageId={$page.id}&alias={$page.alias}&url={$page.url}"}</p>
        {if $details}
            <h2>{t text="User"} {$profile.login}</h2>
            {dynamic callback=array('WidgetProfiles', 'dynamicEditProfileLink') id=$profile.id}
            {$details}
            {dynamic callback=array('WidgetProfiles', 'dynamicFeedbackForm') id=$profile.id feedback_form=$content.feedback_form pageWidgetId=$pageWidget.id}
        {/if}
    {/if}
{/if}