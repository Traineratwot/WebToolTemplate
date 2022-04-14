{extends file='base.tpl'}
{block name='content'}
    {if $user != null}
		<h3> Hi "{$user->get('email')|regex_replace:'/@.{0,}/':""|capitalize}"</h3>
		<div style="width: 100%; min-height:100px" id="test">

		</div>
		<script>
			$().ready(() => {
				wt.renderer.render(
					'#test',
					'chunk:chunks/test.tpl',
					{
						w: window.innerWidth,
						h: window.innerHeight
					})
			})
			$(window).resize(() => {
				wt.renderer.render(
					'#test',
					'chunk:chunks/test.tpl',
					{
						w: window.innerWidth,
						h: window.innerHeight
					})
			})
		</script>
    {else}
		<h3>Pleas Login</h3>
    {/if}
{/block}