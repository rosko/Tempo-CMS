<h3>Здравствуйте, {$model.name}</h3>

<p>Только что Вы или кто-то другой зарегистрировался на сайте
{link url="view/index" text=$settings.sitename}.</p>

{if $model.login}
<p>Ваш логин: {$model.login}
{else}
<p>Ваша электронная почта: {$model.email}
{/if}
{if $generatedPassword}
<br />Сгенерированный системой пароль: {$generatedPassword}</p>

<p>При следующем входе на сайт обязательно смените пароль.
{/if}</p>

<p>Мы очень рады приветствовать Вас. Надеемся использование сайта
принесет Вам много пользы.</p>

<p>С уважением,<br />
администрация сайта.</p>