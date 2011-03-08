<h3>Hello, mr. administrator</h3>

<p>Just joined your site {link url="page/view" text=$settings.sitename} the new user. Here are his data:</p>

<table>
    <tr>
        <td>IP:</td>
        <td>{$userHostAddress}</td>
    </tr>
    <tr>
        <td>Username:</td>
        <td>{$model.login}</td>
    </tr>
    <tr>
        <td>E-mail:</td>
        <td>{$model.email}</td>
    </tr>
    <tr>
        <td>Name</td>
        <td>{$model.name}</td>
    </tr>
</table>

<p>I beg your love and favor.</p>

<p>Sincerely,<br />
site robot.</p>

<p><small>P.S. Such notification can be disabled in options block "Registration Form"on page
{assign var="link" value={link url="page/view?id={$page.id}&alias={$page.alias}&url={$page.url}"}}{link url=$link text=$link}</small></p>