<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Register</title>
	<?= file_get_contents('core/templates/head.tpl') ?>
</head>
<body>
<div class="container">
	<form class="" id="test" method="POST" action="/ajax/register.php">
		<h2>Register</h2>
		<div class="mb-3">
			<label for="exampleFormControlInput1" class="form-label">Email address</label>
			<input name="email" type="email" class="form-control" id="exampleFormControlInput1"
			       placeholder="name@example.com">
		</div>
		<div class="mb-3">
			<label for="exampleFormControlTextarea1" class="form-label">Example textarea</label>
			<input name="password" type="password" class="form-control" id="exampleFormControlTextarea1"
			       rows="3">
		</div>
		<button type="submit" class="btn btn-primary">Submit</button>
	</form>
	<script>
		$('#test').on('success', function() {
			console.log(arguments)
			PNotify.success({
				title: 'Complete',
				text: 'You has been registered',
				icon: 'fa fa-envelope'
			});
		})
		$('#test').on('failure', function(e,d) {
			console.log(arguments)
			PNotify.failure({
				title: 'failure',
				text: d['message'],
				icon: 'fa fa-envelope'
			});
		})
	</script>
</div>
</body>
</html>