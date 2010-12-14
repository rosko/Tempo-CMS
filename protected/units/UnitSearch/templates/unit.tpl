{form action={link url="page/view?id={$page.id}&alias={$page.alias}&url={$page.url}&i=search"} method="GET"}
    <input type="text" name="q" value="{$q}" />
    <input type="submit" value="{t text='Search'}" />
</form>
