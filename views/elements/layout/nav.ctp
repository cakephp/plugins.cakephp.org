<nav class="main-nav">
	<ul>
		<li><?php echo $this->Html->link(
			__('Packages', true),
			'#',
			array('class' => 'non-button'));
		?>
			<ul>
				<li><?php echo $this->Html->link(
					__('Browse All', true),
					array('controller' => 'packages', 'action' => 'index', 'plugin' => null, 'admin' => false));
				?></li>
				<li><?php echo $this->Html->link(
					__('Suggest One', true),
					array('controller' => 'packages', 'action' => 'suggest', 'plugin' => null, 'admin' => false));
				?></li>
			</ul>
		</li>
		<li><?php echo $this->Html->link(
			__('Coders', true),
			array('controller' => 'maintainers', 'action' => 'index', 'plugin' => null, 'admin' => false));
		?></li>
	</ul>
</nav>
