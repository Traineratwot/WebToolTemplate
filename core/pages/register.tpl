{extends file='base.tpl'}
{block name='content'}
	<form class="" id="reg" method="POST" action="register">
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
		$('#reg').on('success', function() {
			console.log(arguments)
			PNotify.success({
				title: 'Complete',
				text: 'You has been registered',
				icon: 'fa fa-envelope'
			})
			setTimeout(function() {
				document.location.href = '/profile'
			}, 1000)
		})
		$('#reg').on('failure', function(e, d) {
			console.log(arguments)
			PNotify.error({
				title: 'failure',
				text: d['message'],
				icon: 'fa fa-envelope'
			})
		})
	</script>
{/block}