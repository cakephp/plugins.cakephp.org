<?php
/**
 * Copyright 2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="users view">
<h2><?php echo $user['User']['username'];?> <?php #echo $this->Html->link(__('View Full Profile'), '#', array('class' => 'full-profile'))?></h2>
	<div class='picture'>
		<?php echo $this->Gravatar->image($user['User']['email'], array('size' => '60', 'class' => 'avatar')); ?>
	</div>
	<p class="subheading">
		<?php __('Registered') ?> 
		<time><?php echo $this->Time->niceShort($user['User']['created']); ?><time>
	</p>
	<div class="details">
		<ul>
			<li><a href="#uploaded" class="no-icon"><?php printf(__('Uploaded (%d)'), $uploaded); ?></a></li>
			<li><a href="#liked" class="no-icon"><?php __('Liked'); ?></a></li>
		</ul>
		<div id="uploaded">
			<?php
				echo $this->requestAction(
					array(
						'controller' => 'videos',
						'action' => 'requestForUser'),
					array(
						'pass' => array($user['User']['id'])));
			?>
		</div>
		<div id="liked">
			<?php
				ClassRegistry::removeObject('view');
				echo $this->requestAction(
					array(
						'controller' => 'videos',
						'action' => 'liked'),
					array(
						'pass' => array($user['User']['id'], true)));
				ClassRegistry::addObject('view', $this);
			?>
		</div>
	</div>
	<div class="main-content">		
		<?php //TODO show something ?>
	</div>
	<aside class="sidebar">
		<div class="advertisement">
			<div class="adsense">
				<?php echo $this->Adsense->display('mixed', 'medium rectangle'); ?>
			</div>
			<div class="adsense">
				<?php echo $this->Adsense->display('text', 'medium rectangle'); ?>
			</div>
		</div>
	</aside>
</div>
<?php
	$metaTags = array();
	$metaTags = implode(', ', array_flip(array_flip($metaTags)));

	$this->Html->meta(array('name' => 'title', 'content' => 'CakePHP User profile for ' . $user['User']['username']), null, array('inline' => false));
	$this->Html->meta(array('name' => 'description', 'content' => 'CakePHP User profile for ' . $user['User']['username']), null, array('inline' => false));
	$this->Html->meta(array('name' => 'keywords', 'content' => $metaTags), null, array('inline' => false));
	$this->Html->meta(array('name' => 'abstract', 'content' => 'cakephp packages development programming framework mvc'), null, array('inline' => false));
	$this->Html->meta(array('name' => 'copyright', 'content' => 'Copyright ' . ((date('Y') > 2010) ? '2010-' . date('Y') : '2010') . ' Cake Software Foundation, Inc.'), null, array('inline' => false));
?>
<?php
	$this->Js->buffer('$(".details").tabs()');
?>