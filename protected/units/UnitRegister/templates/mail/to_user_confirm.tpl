<h3>Dear {$model.name}</h3>

<p>You are registered on the site {link url="view/index" text=$settings.sitename}.
To confirm registration is necessary to pass on the following link:<br />
{assign var="link" value={link url="view/index?pageId={$page.id}&alias={$page.alias}&url={$page.url}&authcode={$model.authcode}"}}{link url=$link text=$link}</p>

{if $generatedPassword}
<p>Your e-mail: {$model.email}<br />
System generated password: {$generatedPassword}</p>

<p>Be sure to change your password at next logon to the site.</p>
{/if}

<p>Sincerely,<br />
site administration.</p>