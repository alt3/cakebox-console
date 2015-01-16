<?php

/**
 * Default layout using Bootstrap 3.x
 */
$cakeDescription = 'Cakebox Admin';

?>
<?= $this->Html->docType('html5') ?>

<html>
<head>
	<?= $this->Html->charset() ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?= $cakeDescription ?>: <?= $this->fetch('title') ?></title>
	<?= $this->Html->meta('icon') ?>

	<?php
		// Generate page specific dotted filename (without extension) so it can
		// be used to load page specific Stylesheet and/or Javascript
		if ($this->request->here == '/')
		{
			$pageAsset = 'login';
		}
		else
		{
			$pageAsset = preg_replace('/^\//', '', $this->request->here);
			$pageAsset = preg_replace('/\//', '.', $pageAsset);
		}

		// Load stylesheets in this order
		$stylesheets = [
			'cdn-fallback/bootstrap/bootstrap.min',
			'cdn-fallback/font-awesome/css/font-awesome.min',
			'cdn-fallback/jquery-plugins/msgGrowl/msgGrowl',
			'theme/base-admin-3',
			'theme/base-admin-3-responsive',
			'app',
		];

		if (file_exists(WWW_ROOT . 'css' . DS . 'pages' . DS . $pageAsset . '.css'))
		{
			$stylesheets[] = "pages/$pageAsset";
		}

		foreach ($stylesheets as $stylesheet)
		{
			echo $this->Html->css($stylesheet);
		}

		// Load Javascripts in this order
		$scripts = [
			'cdn-fallback/jquery/jquery.min',
			'cdn-fallback/jquery-ui/jquery-ui.min',
			'cdn-fallback/bootstrap/bootstrap.min',
			'cdn-fallback/string/string.min',
			'cdn-fallback/jquery-plugins/msgGrowl/msgGrowl.min',
			'cdn-fallback/jquery-plugins/flot/jquery.flot.min',
			'cdn-fallback/jquery-plugins/flot/jquery.flot.pie.min',
			'cdn-fallback/jquery-plugins/flot/jquery.flot.resize.min',
			'app'
		];

		if (file_exists(WWW_ROOT . 'js' . DS . 'pages' . DS . $pageAsset . '.js'))
		{
			$scripts[] = "pages/$pageAsset";
		}

		foreach ($scripts as $script)
		{
			echo $this->Html->script($script);
		}
	?>

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

	<?php
		// Loads extra meta, css and scripts but only if they are defined in
		// the used View as blocks
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<?php
		// @todo: hook into Auth
	 	if ($this->request->here == '/') {
			echo $this->element('top-anonymous');
		} else {
			echo $this->element('top-authenticated');
		}
	?>

	<div class="main">
		<div class="container">
			<?= $this->Flash->render() ?>
			<div class="row">
				<?= $this->fetch('content') ?>
			</div>
		</div>
	</div>

	<?= $this->element('beta') ?>

</body>
</html>
