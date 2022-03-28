{extends file='base.tpl'}
{block name='content'}
	<form class="" id="login" method="POST" action="user/ChangePassword" data-before="validate">
		<h2>Изменить пароль</h2>
		<div class="mb-3">
			<label for="password1" class="form-label">Пароль</label>
			<input
					name="password" type="password" class="form-control" id="password1"
			>
		</div>
		<div class="mb-3">
			<label for="password2" class="form-label">Повторите пароль</label>
			<input
					type="password" class="form-control" id="password2"
			>
		</div>
		<button type="submit" class="btn btn-primary">Submit</button>
		<script>
			$('#login').on('success', function() {
				console.log(arguments)
				PNotify.success({
					title: 'Готово',
					text: 'Пароль изменен',
					icon: 'fa fa-envelope'
				})
				setTimeout(function() {
					document.location.href = '/user/login'
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
			window.validate = function() {
				if($(password1).val() != $(password2).val()) {
					PNotify.error({
						title: 'Ошибка',
						text: 'Пароли не совпадают',
						icon: 'fa fa-envelope'
					})
					return false
				}
				if($(password1).val().length < 6) {
					PNotify.error({
						title: 'Ошибка',
						text: 'Пароль должен быть длиннее 6',
						icon: 'fa fa-envelope'
					})
					return false
				}
				return true
			}
		</script>
	</form>
{/block}