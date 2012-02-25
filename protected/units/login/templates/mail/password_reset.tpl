<h3>Dear {$model.name}</h3>

<p>You have requested the password reset on the site {link url="view/index" text=$settings.sitename}.
To set a new password click on the following link:<br />
{assign var="link" value={link url="view/index?pageId={$page.id}&alias={$page.alias}&url={$page.url}&authcode={$model.authcode}"}}{link url=$link text=$link}</p>

<p>If you did not request a password reset, please ignore this message.</p>

<p>Sincerely,<br />
site administration.</p>