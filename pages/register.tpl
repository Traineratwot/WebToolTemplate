{extends file='base.tpl'}
{block name='content'}
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
{/block}