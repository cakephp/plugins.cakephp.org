<?php
if (!isset($limit) || $limit < 2) $limit = 5;

$contents = null;
App::import('Core', 'HttpSocket');
$HttpSocket = new HttpSocket();
$result = $HttpSocket->request(array('uri' => $url));
if ($result) :
	App::import('Xml');
	$xml = new Xml($result);
	$rss = $xml->toArray();

	if (isset($rss['Feed']['Entry']) && !empty($rss['Feed']['Entry'])) :
		if (isset($rss['Feed']['Entry'][0])) :
			$items = array_slice($rss['Feed']['Entry'],0,$limit,true);
		else :
			$items[0] = $rss['Feed']['Entry'];
		endif;
	?>
		<h3><?php __('Recent Activity');?></h3>
		<table cellpadding = "0" cellspacing = "0">
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
			<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : ''; ?>>
				<td>
					<a href="<?php echo $item['Link']['href']; ?>"<?php if(isset($target)) echo ' target="'.$target.'"';?>>
						<?php echo $item['title']; ?>
					</a>
				</td>
				<td><?php echo $time->timeAgoInWords($item['updated']); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>
<?php endif; ?>