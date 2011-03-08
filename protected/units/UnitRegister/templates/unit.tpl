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
                {if $isGuest || $editMode}

{if $content.agreement}
    <div id="RegisterAgreement{$unit.id}">
        <h2>{t text="User agreement"}</h2>
        <div style="border:1px solid black;padding:10px;width:90%;height:300px;overflow:auto;">{$content.agreement}</div>
        <button onclick="$('#User_agreed').attr('checked', true);$('#RegisterAgreement{$unit.id}').slideUp();$('#RegisterForm{$unit.id}').slideDown();">{t text="I agree"}</button>
    </div>
    <div id="RegisterForm{$unit.id}" style="display: none;">
{/if}
    {$content.text}
    {form className="User" elements=$formElements enableAjax='validate' scenario="register" rules=$formRules}
{if $content.agreement}
    </div>
{/if}

                {else}
                    {redirect to="{link url='/'}"}
                {/if}
            {/if}
        {/if}
    {/if}

{/if}