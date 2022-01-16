<!DOCTYPE html>
<html lang="en">
<head>
	<script type="text/javascript">
		const Translations = JSON.parse('{json_encode($core->translation,256)}')
	</script>
	<script type="text/javascript" src="/assets/js/gettext.js"></script>
	<title>{$title}</title>
    {include 'head.tpl'}
</head>
<body>

<div class="container">
    {include 'navbar.tpl'}
    {block name='content'}

    {/block}
</div>

</body>
</html>