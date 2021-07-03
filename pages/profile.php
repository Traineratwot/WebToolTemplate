<?php
	/** @var \core\Core $core */
	if (!$core->user) {
		header('HTTP/1.1 401 Access Denied');
		die;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
	<?= file_get_contents('core/templates/head.tpl') ?>
</head>
<body>
<div class="container">
    <h3> Hi "<?= $core->user->get('email') ?>"</h3>

    <script>
		$('#test').on('success', function() {
			console.log(arguments)
			PNotify.success({
				title: 'Complete',
				text: 'You has been registered',
				icon: 'fa fa-envelope'
			})
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
</div>
</body>
</html>