<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>{$title}</title>

{if $description}
	<meta name="description" content="{$description}" />
{/if}
{if $keywords}
	<meta name="keywords" content="{$keywords}" />
{/if}


    <link rel="stylesheet" type="text/css" href="{$themeBaseUrl}/css/style.css" />
	<link rel="stylesheet" type="text/css" href="{$cssUrl}/form.css" />


</head>
<body>
<div class="all">
	<div class="box">
<!-- The menu, tabs -->
	  <div class="menu">{area name="menu"}</div>
<!-- The header -->
		<div class="header">{area name="top"}
		  <div class="clearfix"></div>
	  </div>
<!-- the news bar, or right hand column -->
		<div class="newsbar">
            {area name="right"}
		</div>
<!-- main content area-->
		<div class="content">
            {$content}
            {area name="main"}
        </div>
		<div class="clearfix"></div>
<!-- footer, copyright area - please do not remove!-->
		<div class="footer">{area name="footer"}</div>
	</div>
</div>

</body>
</html>