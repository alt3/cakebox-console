<?php
/**
 * Top navigation for non-authenticated users.
 */
?>

<nav class="navbar navbar-inverse" role="navigation">

	<div class="container">
		<div class="navbar-header">
			<?php
                echo $this->Html->link(
                    "Cakebox Dashboard<span class='logo-version'>v$version</span>",
                    '',
                    ['class' => 'navbar-brand', 'escape' => false]
                );
            ?>
		</div>
	</div>

</nav>
