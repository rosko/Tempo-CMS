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
            {if $user.id == $profile.id && $profileEditUrl}
                <p>{link text={t text='Edit profile'} url=$profileEditUrl}</p>
            {/if}
            {$details}
            {if $feedbackForm}
                <h3>{t text="Feedback form"}</h3>
                {if !$sent}
                    <div class="form">{$feedbackForm}</div>
                {else}
                    {t text='Your message was successfully sent'}
                {/if}
            {/if}
        {/if}
    {/if}
{/if}