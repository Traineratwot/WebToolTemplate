<?php
/* Smarty version 3.1.39, created on 2021-07-06 22:09:18
  from 'C:\OpenServer\projects\pattern\pages\profile.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.39',
  'unifunc' => 'content_60e4aa5e2c4e17_17380454',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '06bbfb30993abb0e9814747195665d35e694c558' => 
    array (
      0 => 'C:\\OpenServer\\projects\\pattern\\pages\\profile.tpl',
      1 => 1625598556,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_60e4aa5e2c4e17_17380454 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>

<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_67552101660e4aa5e2bee10_84711119', 'content');
$_smarty_tpl->inheritance->endChild($_smarty_tpl, 'base.tpl');
}
/* {block 'content'} */
class Block_67552101660e4aa5e2bee10_84711119 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'content' => 
  array (
    0 => 'Block_67552101660e4aa5e2bee10_84711119',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'C:\\OpenServer\\projects\\pattern\\vendor\\smarty\\smarty\\libs\\plugins\\modifier.regex_replace.php','function'=>'smarty_modifier_regex_replace',),1=>array('file'=>'C:\\OpenServer\\projects\\pattern\\vendor\\smarty\\smarty\\libs\\plugins\\modifier.capitalize.php','function'=>'smarty_modifier_capitalize',),));
?>

    <?php if ($_smarty_tpl->tpl_vars['user']->value != null) {?>
        <h3> Hi "<?php echo smarty_modifier_capitalize(smarty_modifier_regex_replace($_smarty_tpl->tpl_vars['user']->value->get('email'),'/@.{0,}/',''));?>
"</h3>
    <?php } else { ?>
        <h3>Pleas Login</h3>
    <?php }
}
}
/* {/block 'content'} */
}
