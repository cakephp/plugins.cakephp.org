<nav class="main-nav">
	<ul>
		<li><?php echo $this->Html->link(
			__('Packages'),
			'#',
			array('class' => 'non-button'));
		?>
			<ul>
				<li><?php echo $this->Html->link(
					__('Browse All'),
					array('controller' => 'packages', 'action' => 'index', 'plugin' => null, 'admin' => false));
				?></li>
				<li><?php echo $this->Html->link(
					__('Suggest One'),
					array('controller' => 'packages', 'action' => 'suggest', 'plugin' => null, 'admin' => false));
				?></li>
			</ul>
		</li>
		<li><?php echo $this->Html->link(
			__('Coders'),
			array('controller' => 'maintainers', 'action' => 'index', 'plugin' => null, 'admin' => false));
		?></li>
	</ul>
</nav>
