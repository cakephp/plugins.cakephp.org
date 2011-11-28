<?php
$tabs = array(
	'name' =>  $this->Html->link(
		__('Alphabetical', true),
		array('sortby' => 'name') + $this->passedArgs,
		array('class' => 'alphabetical')
	),
	'most_comments' => $this->Html->link(
		__('Most Comments', true),
		array('sortby' => 'most_comments') + $this->passedArgs,
		array('class' => 'comments')
	),
	'most_uploads' => $this->Html->link(
		__('Most Uploads', true),
		array('sortby' => 'most_uploads') + $this->passedArgs,
		array('class' => 'uploads')
	),
	'popular' => $this->Html->link(
		__('Highest Ratings', true),
		array('sortby' => 'popular') + $this->passedArgs,
		array('class' => 'rating')
	)
);
?>
<div class="users index">
	<h1><?php __('Users'); ?></h1>
	<div class="wide-search search">
		<?php echo $this->Form->create(null, array('action' => 'index'));?>
		<?php
			echo $this->Form->input('search', array(
				'label' => __('Search Users', true),
				'div' => false,
				'placeholder' => __('Enter Keyword(s)', true)
			));
		?>
		<?php echo $this->Form->submit(__('Search', true), array('div' => false));?>
		<?php echo $this->Form->end();?>
	</div>
	<ul class="ui-tabs-nav">
		<?php foreach ($tabs as $type => $tab) :?>
			<?php
				$class = '';
				if (
					(empty($this->passedArgs['sortby']) && $type == 'name') ||
					(!empty($this->passedArgs['sortby']) && $type ==  $this->passedArgs['sortby'])
				) {
					$class = ' class="ui-tabs-selected"';
				}
			?>
			<li<?php echo $class ?>><?php echo $tab ?><li>
		<?php endforeach;?>
	</ul>
	<ul>
	<?php foreach ($users as $user): ?>
		<li class="user">
			<div class='picture'>
				<?php echo $this->Gravatar->image($user['PkgUser']['email'], array('size' => '60', 'class' => 'avatar')); ?>
			</div>
			<h3><?php echo $this->Html->link($user['PkgUser']['username'], array('action' => 'view', $user['PkgUser']['slug']))?></h3>
			<p class="subheading">
				<?php printf(__('%d Uploads', true), $user['Profile']['video_count']); ?> 
			</p>
			<p class="subheading">
				<?php printf(__('%d Comments', true), $user['Profile']['comment_count']); ?>
			</p>
		</li>
	<?php endforeach; ?>
	</ul>
	<?php echo $this->element('paging'); ?>
</div>
<?php
	$metaTags = Set::extract('/PkgUser/username', $users);
	$metaTags = implode(', ', array_flip(array_flip($metaTags)));

	$this->Html->meta(array('name' => 'title', 'content' => 'Cake Packages list of active members'), null, array('inline' => false));
	$this->Html->meta(array('name' => 'description', 'content' => 'CakePHP Packages list of active members'), null, array('inline' => false));
	$this->Html->meta(array('name' => 'keywords', 'content' => $metaTags), null, array('inline' => false));
	$this->Html->meta(array('name' => 'abstract', 'content' => 'cakephp training videos development programming framework mvc users list'), null, array('inline' => false));
	$this->Html->meta(array('name' => 'copyright', 'content' => 'Copyright ' . ((date('Y') > 2010) ? '2010-' . date('Y') : '2010') . ' Cake Software Foundation, Inc.'), null, array('inline' => false));
?>