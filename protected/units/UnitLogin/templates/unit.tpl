{if $isGuest}
    <div id="LoginForm{$unit.id}" {if $doRemember}style="display:none;"{/if}>
        <h3>{$unit.title}</h3>
        {form className='LoginForm' buttons=$formButtons}
        <a href="#" onclick="$('#LoginForm{$unit.id}').slideUp();$('#RememberForm{$unit.id}').slideDown();return false;">{t text="Forgot password?"}</a>
    </div>
    <div id="RememberForm{$unit.id}" {if !$doRemember}style="display:none;"{/if}>
        <h3>{t text="Password reset"}</h3>
        {if $doneRemember}
            <p>{t text="Instructions to reset your password sent to your email"}</p>
        {else}
            {form className='RememberForm'}
        {/if}
        <a href="#" onclick="$('#RememberForm{$unit.id}').slideUp();$('#LoginForm{$unit.id}').slideDown();return false;">{t text="Back to login form"}</a>
    </div>
{else}
    {dynamic callback=array('UnitLogin', 'dynamicGreetings')}
    {form method='POST'}
        <input type="submit" name="logout" value="{t text='Logout'}" />
    </form>
{/if}
<hr />
