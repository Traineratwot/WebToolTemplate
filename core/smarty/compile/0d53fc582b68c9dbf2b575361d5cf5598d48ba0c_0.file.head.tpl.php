<?php
/* Smarty version 3.1.39, created on 2021-07-06 21:33:12
  from 'C:\OpenServer\projects\pattern\core\templates\head.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.39',
  'unifunc' => 'content_60e4a1e81d7a20_63861374',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0d53fc582b68c9dbf2b575361d5cf5598d48ba0c' => 
    array (
      0 => 'C:\\OpenServer\\projects\\pattern\\core\\templates\\head.tpl',
      1 => 1625343008,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_60e4a1e81d7a20_63861374 (Smarty_Internal_Template $_smarty_tpl) {
?><meta charset="UTF-8">
<link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
<link href="/node_modules/@pnotify/core/dist/PNotify.css" rel="stylesheet" type="text/css"/>
<link href="/node_modules/@pnotify/mobile/dist/PNotifyMobile.css" rel="stylesheet" type="text/css"/>
<link href="/node_modules/@pnotify/core/dist/BrightTheme.css" rel="stylesheet" type="text/css"/>
<link href="/node_modules/@pnotify/core/dist/Material.css" rel="stylesheet" type="text/css"/>
<link href="/node_modules/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<?php echo '<script'; ?>
 type="text/javascript" src="/node_modules/@pnotify/core/dist/PNotify.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript" src="/node_modules/@pnotify/mobile/dist/PNotifyMobile.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript" src="/node_modules/jquery/dist/jquery.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
	if(window.hasOwnProperty('PNotify')) {
		PNotify.defaults.styling = 'material'
		PNotify.defaults.icons = 'brighttheme';
	}
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript" src="/assets/js/main.js"><?php echo '</script'; ?>
><?php }
}
