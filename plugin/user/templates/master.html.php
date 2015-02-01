<?php namespace Inkwell\HTML; ?>

<!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?= $this('title') ?: 'Welcome to inKWell' ?></title>
	</head>
	<body>
		<?php $this->insert('content') ?>
	</body>
</html>
