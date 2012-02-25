<h3>Здравствуйте, {$model.name}</h3>

<p>Вы запросили восстановление пароля на сайте {link url="view/index" text=$settings.sitename}.
Для установки нового пароля пройдите по следующей ссылке:<br />
{assign var="link" value={link url="view/index?pageId={$page.id}&alias={$page.alias}&url={$page.url}&authcode={$model.authcode}"}}{link url=$link text=$link}</p>

<p>Если вы не делали запрос о сбросе пароля, пожалуйста, проигнорируйте это сообщение.</p>

<p>С уважением,<br />
администрация сайта.</p>