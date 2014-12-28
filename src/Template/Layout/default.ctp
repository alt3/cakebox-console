<?php
/**
 * Default Bootstrap 3.x layout
 */
$cakeDescription = 'Cakebox Admin';

?>
<?= $this->Html->docType('html5') ?>

<html>
<head>
	<?= $this->Html->charset() ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>
		<?= $cakeDescription ?>:
		<?= $this->fetch('title') ?>
	</title>
	<?= $this->Html->meta('icon') ?>

	<!-- Bootstrap Core CSS (v3.3.1)-->
	<?= $this->Html->css('bootstrap/bootstrap.min') ?>
	<?= $this->Html->css('fonts/font-awesome-4.1.0/css/font-awesome.min') ?>
	<?= $this->Html->css('https://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600') ?>

	<!-- Base Admin theme -->
	<?= $this->Html->css('ui-lightness/jquery-ui-1.10.0.custom.min') ?>
	<?= $this->Html->css('theme/base-admin-3') ?>
	<?= $this->Html->css('theme/base-admin-3-responsive') ?>

	<!-- Conditional page-specific css -->
	<?php
		if ($this->request->here == '/') {
			echo $this->Html->css('pages/signin');
		}
	?>

	<!-- Cakebox overrides -->
	<?= $this->Html->css('cakebox') ?>



	<!-- Bootstrap Core JS (v3.3.1) -->
	<?= $this->Html->script('jquery.js') ?>
	<?= $this->Html->script('bootstrap.js') ?>
	<?= $this->Html->script('cakebox.js') ?>

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

	<!-- Load extra meta, css and scripts IF defined in the used View as blocks -->
	<?= $this->fetch('meta') ?>
	<?= $this->fetch('css') ?>
	<?= $this->fetch('script') ?>
</head>
<body>
	<!-- @todo: hook into Auth -->
	<?php
	 	if ($this->request->here == '/') {
			echo $this->element('top-anonymous');
		}
	?>

	<div class="container">
		<div id="content">
			<?= $this->Flash->render() ?>
			<div id="row">
				<?= $this->fetch('content') ?>
			</div>
		</div>
	</div>

	<?= $this->element('beta') ?>

</body>
</html>
