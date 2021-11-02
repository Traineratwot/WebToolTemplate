{extends file='base.tpl'}
{block name='content'}
	<form class="" id="login" method="POST" action="forgotpassword">
		<h2>forgot password</h2>
		<div class="mb-3">
			<label for="exampleFormControlInput1" class="form-label">Email address</label>
			<input
					name="email" type="email" class="form-control" id="exampleFormControlInput1"
					placeholder="email@example.com"
					value="{$_GET["email"]}"
			>
		</div>
		<button type="submit" class="btn btn-primary">Submit</button>
		<script>
			$('#login').on('success', function(e, d) {
				console.log(arguments)
				PNotify.success({
					title: 'Готово',
					text: d['message'],
					icon: 'fa fa-envelope'
				})
			})
			$('#login').on('failure', function(e, d) {
				console.log(arguments)
				PNotify.error({
					title: 'Ошибка',
					text: d['message'],
					icon: 'fa fa-envelope'
				})
			})
		</script>
	</form>
{/block}