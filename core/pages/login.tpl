{extends file='base.tpl'}
{block name='content'}
	<form class="" id="login" method="POST" action="login">
		<h2>{t}login{/t}</h2>
		<div class="mb-3">
			<label for="exampleFormControlInput1" class="form-label">{t}email{/t}</label>
			<input
					name="email" type="email" class="form-control" id="exampleFormControlInput1"
					placeholder="email@example.com"
					oninput="$(forgot_password).attr('href','/forgot_password?email='+this.value)"
			>
		</div>
		<div class="mb-3">
			<label for="exampleFormControlTextarea1" class="form-label">{t}password{/t}</label>
			<input
					name="password" type="password" class="form-control" id="exampleFormControlTextarea1"
					rows="3"
			>
		</div>
		<button type="submit" class="btn btn-primary">{t}submit{/t}</button>
		<a id="forgot_password" href="/forgot_password">Forgot password?</a>
		<script>
			$('#login').on('success', function() {
				console.log(arguments)
				PNotify.success({
					title: 'Готово',
					text: 'You has been login',
					icon: 'fa fa-envelope'
				})
				setTimeout(function() {
					document.location.href = '/profile'
				}, 1000)
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