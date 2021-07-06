<?php
/* Smarty version 3.1.39, created on 2021-07-06 21:33:12
  from 'C:\OpenServer\projects\pattern\core\templates\navbar.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.39',
  'unifunc' => 'content_60e4a1e81de754_37053249',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'cba4ba63d4bec6dff220e8a8df09bd5b01af950a' => 
    array (
      0 => 'C:\\OpenServer\\projects\\pattern\\core\\templates\\navbar.tpl',
      1 => 1625593806,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_60e4a1e81de754_37053249 (Smarty_Internal_Template $_smarty_tpl) {
?><nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">Menu</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($_smarty_tpl->tpl_vars['isAuthenticated']->value) {?>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/profile">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/links">Links</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/register">Register</a>
                    </li>
                <?php }?>
            </ul>
            <?php if ($_smarty_tpl->tpl_vars['isAuthenticated']->value) {?>
                <form class="d-flex" action="/ajax/logout.php" id="Logout">
                    <button class="btn btn-outline-info" type="submit">Logout</button>
                </form>
            <?php }?>
        </div>
        <?php echo '<script'; ?>
>
			$('#Logout').on('success', function() {
				document.location.href = '/'
			})
        <?php echo '</script'; ?>
>
    </div>
</nav>
<?php }
}
