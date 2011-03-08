<h3>Dear {$model.name}</h3>

<p>You or someone else just registered on the web-site
{link url="page/view" text=$settings.sitename}.</p>

{if $model.login}
<p>Your username: {$model.login}
{else}
<p>Your e-mail: {$model.email}
{/if}
{if $generatedPassword}
<br />System generated password: {$generatedPassword}</p>

<p>Be sure to change your password at next logon to the site.
{/if}</p>

<p>We are very pleased to welcome you. We hope that the use of the
site will be useful for you.</p>

<p>Sincerely,<br />
site administration.</p>