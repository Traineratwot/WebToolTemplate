<?php
/* Smarty version 3.1.39, created on 2021-07-06 21:33:12
  from 'C:\OpenServer\projects\pattern\core\templates\base.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.39',
  'unifunc' => 'content_60e4a1e81d2895_72639331',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '635b47e7b5933bce2a580852a0a7330d8613fa93' => 
    array (
      0 => 'C:\\OpenServer\\projects\\pattern\\core\\templates\\base.tpl',
      1 => 1625504890,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:head.tpl' => 1,
    'file:navbar.tpl' => 1,
  ),
),false)) {
function content_60e4a1e81d2895_72639331 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</title>
    <?php $_smarty_tpl->_subTemplateRender('file:head.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
</head>
<body>

<div class="container">
    <?php $_smarty_tpl->_subTemplateRender('file:navbar.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
    <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_185255413160e4a1e81d22e6_60552209', 'content');
?>

</div>

</body>
</html><?php }
/* {block 'content'} */
class Block_185255413160e4a1e81d22e6_60552209 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'content' => 
  array (
    0 => 'Block_185255413160e4a1e81d22e6_60552209',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <?php
}
}
/* {/block 'content'} */
}
