<footer>
	<div class="container">
		<div class="copyright">
			<?php 
				echo sprintf(
					__('Powered by %s'),
					$this->Html->link('CakePackages', 'http://github.com/cakephp/cakepackages')
				) .
				' &copy; 2009 - ' . date('Y') . ' ' .
				$this->Html->link('Jose Diaz Gonzalez', 'http://josediazgonzalez.com', array('target' => '_blank')) .
				'<br />CakePHP Package Indexer &copy; 2010 - ' . date('Y') . ' ' .
				$this->Html->link('Cake Software Foundation, Inc.', 'http://cakefoundation.org', array('target' => '_blank'));
			?>
		</div>
	</div>
</footer>