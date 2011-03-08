<h3>Здравствуйте, {$model.name}</h3>

<p>Вы зарегистрировались на сайте {link url="page/view" text=$settings.sitename}.
Для подтверждения регистрации необходимо пройти по следующей ссылке:<br />
{assign var="link" value={link url="page/view?id={$page.id}&alias={$page.alias}&url={$page.url}&authcode={$model.authcode}"}}{link url=$link text=$link}</p>

{if $generatedPassword}
<p>Ваша электронная почта: {$model.email}<br />
Сгенерированный системой пароль: {$generatedPassword}</p>

<p>При следующем входе на сайт обязательно смените пароль.</p>
{/if}

<p>С уважением,<br />
администрация сайта.</p>