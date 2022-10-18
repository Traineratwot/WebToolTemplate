{extends file='base.tpl'}
{block name="head"}

{/block}
{block name='content'}
    {if $msg}
        {$msg}
    {else}
		Доступ запрещен
    {/if}
{/block}