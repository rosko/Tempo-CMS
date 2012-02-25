<h3>Dear {$profile.name}</h3>

<p>Someone filled out your feedback form on the site.
{link url="view/index" text=$settings.sitename}.</p>

<p>Here is the information:</p>
<table>
{foreach $fields as $field => $value}{if $value}
<tr><td><b>{$field}:</b></td>
    <td>{$value}</td>
</tr>
{/if}{/foreach}
</table>

<p>Sincerely,<br />
site administration.</p>

{if $profileEditUrl}
<p><small>P.S. You can disable feedback form in the personal profile on the page
{assign var="link" value={link url=$profileEditUrl params=$profileEditUrlParams}}{link url=$link text=$link}</small></p>
{/if}