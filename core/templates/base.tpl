<!DOCTYPE html>
<html lang="en">
<head>
    <title>{$title}</title>
    {include 'head.tpl'}
</head>
<body>

<div class="container">
    {include 'navbar.tpl'}
    {block name='content'}
    {/block}
</div>
<script>
	$('#test').on('success', function() {
		console.log(arguments)
		PNotify.success({
			title: 'Complete',
			text: 'You has been login',
			icon: 'fa fa-envelope'
		})
		setTimeout(function() {
			document.location.href = '/profile'
		}, 1000)
	})
	$('#test').on('failure', function(e, d) {
		console.log(arguments)
		PNotify.failure({
			title: 'failure',
			text: d['message'],
			icon: 'fa fa-envelope'
		})
	})
</script>
</body>
</html>