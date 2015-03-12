<?php

/**
 * Default layout using Bootstrap 3.x
 */
$cakeDescription = 'Cakebox Dashboard';

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
		if ($this->request->here == '/') {
			$pageAsset = 'login';
		} else {
			$pageAsset = preg_replace('/^\//', '', $this->request->here);
			$pageAsset = preg_replace('/\//', '.', $pageAsset);
		}

		// -----------------------------------------
		//  Metadata
		// -----------------------------------------
		echo $this->fetch('metadata');

		// -----------------------------------------
		//  Stylesheets
		// -----------------------------------------
		echo $this->Html->css([
			'cdn-fallback/bootstrap/bootstrap.min',
			'cdn-fallback/font-awesome/css/font-awesome.min',
			'cdn-fallback/jquery-plugins/msgGrowl/msgGrowl',
			'theme/base-admin-3',
			'theme/base-admin-3-responsive'
		]);
		echo $this->fetch('css'); // stylesheet block filled by views
		echo $this->Html->css('app'); // global application-wide stylesheet

		// load page specific stylesheet if there is one
		if (file_exists(WWW_ROOT . 'css' . DS . 'pages' . DS . $pageAsset . '.css')) {
			echo $this->Html->css("pages/$pageAsset");
		}

		// -----------------------------------------
		//  Scripts will be inserted bottom of page
		// -----------------------------------------
		// $scripts = [
		echo $this->Html->script([
			'cdn-fallback/jquery/jquery.min',
			'cdn-fallback/jquery-ui/jquery-ui.min',
			'cdn-fallback/bootstrap/bootstrap.min',
			'cdn-fallback/string/string.min',
			'cdn-fallback/bootbox/bootbox.min',
			'cdn-fallback/jquery-plugins/msgGrowl/msgGrowl.min'
		], ['block' => 'scriptsGlobal']);

		// global app.js appended to scriptBottom block (view added scripts first)
		echo $this->Html->script('app', ['block' => 'scriptBottom']);

		// append page specific script to scriptBottom if the page has one
		if (file_exists(WWW_ROOT . 'js' . DS . 'pages' . DS . $pageAsset . '.js')) {
			$this->Html->script("pages/$pageAsset", ['block' => 'scriptBottom']);
		}
	?>

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

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

	<?php
		echo $this->element('beta');
		echo $this->fetch('scriptsGlobal');
		echo $this->fetch('scriptBottom');
	?>

</body>
</html>
