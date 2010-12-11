<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>{$title}</title>

{if $description}
	<meta name="description" content="{$description}" />
{/if}
{if $keywords}
	<meta name="keywords" content="{$keywords}" />
{/if}

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="{$themeBaseUrl}/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="{$themeBaseUrl}/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="{$themeBaseUrl}/css/ie.css" media="screen, projection" />
	<![endif]-->

    <link rel="stylesheet" type="text/css" href="{$themeBaseUrl}/css/main.css" />
	<link rel="stylesheet" type="text/css" href="/css/form.css" />

</head>

<body>

<div class="container" id="page">

	<div id="header">
		<div id="logo">{link text=$sitename url="page/view"}</div>
	</div><!-- header -->

	<div id="mainmenu">
        {area name="top"}
	</div><!-- mainmenu -->

    <div class="container">
        <div id="content">
            {$content}
            {area name="main"}
        </div><!-- content -->
    </div>

	<div id="footer">
		Copyright &copy; {dateformat pattern="yyyy"}<br/>
		All Rights Reserved
        {if !$editMode}
            <br/>{link text="Управление сайтом" url="site/login"}
        {/if}
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>