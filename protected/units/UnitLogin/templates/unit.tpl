{if $isGuest}
    <h3>{$unit.title}</h3>
    {form className='LoginForm' buttons=$formButtons}
{else}
    <h3>{t text='Hello'}, {$user.name}!</h3>
    {form method='POST'}
        <input type="submit" name="logout" value="{t text='Logout'}" />
    </form>
{/if}
<hr />
