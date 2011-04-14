{if $error}
    <h2>{t text='Error'}</h2>
    <p>{$error}</p>
    <p>{link text={t text='Profiles list'} url="page/view?id={$page.id}&alias={$page.alias}&url={$page.url}"}</p>
{else}
    {if $table}
        <h2>{$unit.title}</h2>
        {$table}
    {else}
        <p>{link text={t text='Profiles list'} url="page/view?id={$page.id}&alias={$page.alias}&url={$page.url}"}</p>
        {if $details}
            <h2>{t text="User"} {$profile.login}</h2>
            {dynamic callback=array('UnitProfiles', 'dynamicEditProfileLink') id=$profile.id}
            {$details}
            {dynamic callback=array('UnitProfiles', 'dynamicFeedbackForm') id=$profile.id feedback_form=$content.feedback_form pageunit_id=$pageunit.id}
        {/if}
    {/if}
{/if}