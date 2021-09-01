{extends file='base.tpl'}
{block name='content'}
    {if $user != null}
        <h3> Hi "{$user->get('email')|regex_replace:'/@.{0,}/':""|capitalize}"</h3>
        TEST: {$a = '1'|user}
        {$a->get('email')}
    {else}
        <h3>Pleas Login</h3>
    {/if}
{/block}