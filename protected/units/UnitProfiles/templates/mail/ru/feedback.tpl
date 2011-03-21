<h3>Здравствуйте, {$profile.name}</h3>

<p>Только что кто-то заполнил Вашу форму обратной связи на сайте
{link url="page/view" text=$settings.sitename}.</p>

<p>Вот информация:</p>
<table>
{foreach $fields as $field => $value}{if $value}
<tr><td><b>{$field}:</b></td>
    <td>{$value}</td>
</tr>
{/if}{/foreach}
</table>

<p>С уважением,<br />
администрация сайта.</p>

{if $profileEditUrl}
<p><small>P.S. Форму обратной связи можно отключить в Вашем личном профиле на странице
{assign var="link" value={link url=$profileEditUrl params=$profileEditUrlParams}}{link url=$link text=$link}</small></p>
{/if}