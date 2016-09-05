<?php $calendarHeader = [
	'Calendar' => [
		'class' => 'menu-title mt30',
		'icon' => 'fa fa-menu-title fa-calendar-o',
		'url' => '#'
	],
];
$menu = array_merge($this->Menu->helpAndSupportItems())
?>

<ul class="footer-menu">
	<li class="menu-title"><i class="fa fa-menu-title fa-comments-o"></i><?= __('Help & Support')?></li>
	<?= $this->App->menuItems($menu); ?>
</ul>
