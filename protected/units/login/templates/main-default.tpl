{if $isGuest}
    <div id="LoginForm{$widget.id}" {if $doRemember}style="display:none;"{/if}>
        <h3>{$widget.title}</h3>
        {form className='LoginForm' buttons=$formButtons}
        <a href="#" onclick="$('#LoginForm{$widget.id}').slideUp();$('#RememberForm{$widget.id}').slideDown();return false;">{t text="Forgot password?"}</a>
    </div>
    <div id="RememberForm{$widget.id}" {if !$doRemember}style="display:none;"{/if}>
        <h3>{t text="Password reset"}</h3>
        {if $doneRemember}
            <p>{t text="Instructions to reset your password sent to your email"}</p>
        {else}
            {form className='RememberForm'}
        {/if}
        <a href="#" onclick="$('#RememberForm{$widget.id}').slideUp();$('#LoginForm{$widget.id}').slideDown();return false;">{t text="Back to login form"}</a>
    </div>
{else}
    {dynamic callback=array('WidgetLogin', 'dynamicGreetings')}
    {form method='POST'}
        <input type="submit" name="logout" value="{t text='Logout'}" />
    </form>
{/if}
<hr />
