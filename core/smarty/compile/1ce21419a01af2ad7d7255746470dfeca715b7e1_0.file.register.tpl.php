<?php
/* Smarty version 3.1.39, created on 2021-07-06 21:36:29
  from 'C:\OpenServer\projects\pattern\pages\register.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.39',
  'unifunc' => 'content_60e4a2ad36feb6_82375163',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '1ce21419a01af2ad7d7255746470dfeca715b7e1' => 
    array (
      0 => 'C:\\OpenServer\\projects\\pattern\\pages\\register.tpl',
      1 => 1625594035,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_60e4a2ad36feb6_82375163 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>

<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_39564845660e4a2ad36f260_93440027', 'content');
$_smarty_tpl->inheritance->endChild($_smarty_tpl, 'base.tpl');
}
/* {block 'content'} */
class Block_39564845660e4a2ad36f260_93440027 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'content' => 
  array (
    0 => 'Block_39564845660e4a2ad36f260_93440027',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

	<form class="" id="reg" method="POST" action="/ajax/register.php">
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
	<?php echo '<script'; ?>
>
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
	<?php echo '</script'; ?>
>
<?php
}
}
/* {/block 'content'} */
}
