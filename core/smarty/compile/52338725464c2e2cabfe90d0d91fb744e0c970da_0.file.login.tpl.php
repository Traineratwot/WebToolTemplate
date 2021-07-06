<?php
/* Smarty version 3.1.39, created on 2021-07-06 21:33:12
  from 'C:\OpenServer\projects\pattern\pages\login.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.39',
  'unifunc' => 'content_60e4a1e81c5715_10408344',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '52338725464c2e2cabfe90d0d91fb744e0c970da' => 
    array (
      0 => 'C:\\OpenServer\\projects\\pattern\\pages\\login.tpl',
      1 => 1625594345,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_60e4a1e81c5715_10408344 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>

<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_144525812560e4a1e81c4374_75917320', 'content');
$_smarty_tpl->inheritance->endChild($_smarty_tpl, 'base.tpl');
}
/* {block 'content'} */
class Block_144525812560e4a1e81c4374_75917320 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'content' => 
  array (
    0 => 'Block_144525812560e4a1e81c4374_75917320',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <form class="" id="login" method="POST" action="/ajax/login.php">
        <h2>Login</h2>
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
        <?php echo '<script'; ?>
>
			$('#login').on('success', function() {
				console.log(arguments)
				PNotify.success({
					title: 'Complete',
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
					title: 'failure',
					text: d['message'],
					icon: 'fa fa-envelope'
				})
			})
        <?php echo '</script'; ?>
>
    </form>
<?php
}
}
/* {/block 'content'} */
}
