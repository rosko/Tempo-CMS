<h3>Здравствуйте, господин главный администратор</h3>

<p>Только что на вашем сайте {link url="view/index" text=$settings.sitename} зарегистрировался
новый пользователь. Вот его данные:</p>

<table>
    <tr>
        <td>IP:</td>
        <td>{$userHostAddress}</td>
    </tr>
    <tr>
        <td>Логин:</td>
        <td>{$model.login}</td>
    </tr>
    <tr>
        <td>Электронная почта:</td>
        <td>{$model.email}</td>
    </tr>
    <tr>
        <td>Имя</td>
        <td>{$model.name}</td>
    </tr>
</table>

<p>Прошу любить и жаловать.</p>

<p>С уважением,<br />
робот сайта.</p>

<p><small>P.S. Подобные уведомления можно отключить в настройках блока "Форма регистрации" на странице
{assign var="link" value={link url="view/index?pageId={$page.id}&alias={$page.alias}&url={$page.url}"}}{link url=$link text=$link}</small></p>