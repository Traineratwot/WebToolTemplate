<?php
	include_once 'core/engine.php';
    /** @var Core $core */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>You shel not passs</title>
	<?= file_get_contents('core/templates/head.tpl') ?>
</head>
<body>

<h1>Error.log</h1>
<pre>
<?= @file_get_contents('core/cache/error.log') ?>
    </pre>
<?= phpinfo() ?>
</body>
</html>