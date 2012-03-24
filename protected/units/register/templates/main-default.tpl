{if $justRegistered}
    <h3>{t text='Your registration done. Thank you. Have a nice time.'}</h3>
{else}
    {if $waitingAuthCode}
        <h3>{t text='To complete registration you need to follow a link you receive on your email address.'}</h3>
    {else}
        {if $confirmedAuthCode}
            <h3>{t text='Your registration confirmed. Thank you. Have a nice time.'}</h3>
        {else}
            {if $faultAuthCode}
                <h3 class="error">{t text='Your code is wrong. Try again, please.'}</h3>
            {else}
                {if ($isGuest || $editMode) && $doParam != 'edit'}

<h2>{t text='Registration'}</h2>
{if $content.agreement}
    <div id="RegisterAgreement{$widget.id}">
        <h2>{t text="User agreement"}</h2>
        <div style="border:1px solid black;padding:10px;width:90%;height:300px;overflow:auto;">{$content.agreement}</div>
        <button onclick="$('#User_agreed').attr('checked', true);$('#RegisterAgreement{$widget.id}').slideUp();$('#RegisterForm{$widget.id}').slideDown();">{t text="I agree"}</button>
    </div>
    <div id="RegisterForm{$widget.id}" style="display: none;">
{/if}
    {$content.text}
    {form className="User" elements=$formElements enableAjax='validate' scenario="register" rules=$formRules submitLabel={t text='Sign up'}}
{if $content.agreement}
    </div>
{/if}

                {else}

                    {if $user && $formElements}
                        {if $profileWidgetUrl}
                            <p>{link text={t text='View profile'} url=$profileWidgetUrl params=$profileWidgetUrlParams}</p>
                        {/if}
                        <h2>{t text="Editing profile"} {$user.login}</h2>
                        {form className="User" id=$user.id elements=$formElements enableAjax=true scenario="update" rules=$formRules submitLabel={t text='Save'}}
                    {else}
                        {if $accessDenied}{accessdenied}{/if}
                    {/if}

                {/if}
            {/if}
        {/if}
    {/if}

{/if}