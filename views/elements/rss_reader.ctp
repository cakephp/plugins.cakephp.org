<?php
if (!isset($limit) || $limit < 2) $limit = 3;

$key = MiCache::key(md5($url));
$items =  MiCache::read('rss' . DS . $key);
if (!$items) {
	App::import('Core', 'HttpSocket');
	$HttpSocket = new HttpSocket();
	$result = $HttpSocket->request(array('uri' => $url));
	if ($result) {
		App::import('Xml');
		$result = new Xml($result);
		$result = $result->toArray();

		if (isset($result['Feed']['Entry']) && !empty($result['Feed']['Entry'])) {
			if (isset($result['Feed']['Entry'][0])) {
				$items = array_slice($result['Feed']['Entry'], 0, $limit, true);
			} else {
				$items[0] = $result['Feed']['Entry'];
			}
		}
	}
	if (is_array($result) && isset($result['Html'])) {
		MiCache::write('rss' . DS . $key, 'error');
	} else {
		MiCache::write('rss' . DS . $key, $items);
	}
}
?>
<?php if (!empty($items) && is_array($items)) : ?>
<h4><?php __('Recent Activity');?></h4>
<table cellpadding="0" cellspacing="0" class="rss_feed">
	<tr>
		<?php if (isset($user)) : ?>
			<th><?php __('Activity'); ?></th>
			<th><?php __('Date'); ?></th>
		<?php else : ?>
			<th><?php __('Commit'); ?></th>
			<th><?php __('Updated'); ?></th>
		<?php endif; ?>
	</tr>
<?php $i = 0; foreach ($items as $item): ?>
	<?php $hash = explode("Commit/", $item['id']); ?>
	<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : ''; ?>>
		<td class="commit_msg" rel="<?php echo end($hash) ;?>">
			<?php echo $this->Html->link($item['title'], $item['Link']['href'], array('target' => '_blank')); ?>
		</td>
		<td class="commit_date">
			<?php echo $time->timeAgoInWords($item['updated']); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>