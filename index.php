<?php
	include_once 'core/engine.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>You shel not passs</title>
</head>
<body style="padding: 5px;margin: 0">
    <h1>Error.log</h1>
    <pre>
<?= @file_get_contents('core/cache/error.log') ?>
    </pre>
<?=phpinfo()?>
</body>
</html>