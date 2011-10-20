<div id="footer">
	<div id="footer-container">
		<div class="copyright">
			<p class="copyright">
				<?php 
					echo sprintf(
						__('Powered by %s', true),
						$this->Html->link('Cakepackages', 'http://github.com/cakephp/cakepages')
					) .
					' &copy; 2010 - ' . date('Y') . ' ' .
					$this->Html->link('Jose Diaz Gonzalez', 'http://josediazgonzalez.com', array('target' => '_blank')) .
					'<br />CakePHP Pakages website &copy; 2010 - ' . date('Y') . ' ' .
					$this->Html->link('Cake Software Foundation, Inc.', 'http://cakefoundation.org', array('target' => '_blank'));
				?>
			</p>
		</div>
	</div>
</div>