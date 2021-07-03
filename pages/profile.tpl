{extends file='base.tpl'}
{block name='content'}
    {if $user != null}
        <h3> Hi "{$user->get('email')}"</h3>
    {else}
        <h3>Pleas Login</h3>
    {/if}
{/block}