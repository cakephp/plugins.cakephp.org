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
				<th><?php __('Commit'); ?></th>
				<?php if (isset($characters)): ?>
					<th><?php __('Information'); ?></th>
				<?php endif; ?>
				<th><?php __('Updated'); ?></th>
			</tr>
		<?php $i = 0; foreach ($items as $item): ?>
			<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : ''; ?>>
				<td>
					<a href="<?php echo $item['Link']['href']; ?>"<?php if(isset($target)) echo ' target="'.$target.'"';?>>
						<?php echo $item['title']; ?>
					</a>
				</td>
				<?php if (isset($characters)): ?>
					<td><?php echo substr($item['content']['value'], 0, $characters).'...'; ?>&nbsp;</td>
				<?php endif; ?>
				<td><?php echo $time->timeAgoInWords($item['updated']); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>
<?php endif; ?>