<article class="paging">
	<?php if (isset($this->params['with'])) : ?>
		<?php echo $this->Paginator->counter(array(
			'format' => sprintf(__('Browsing packages for containing a <span class="highlight">%s</span>, page %%page%% of %%pages%%, showing results %%start%% to %%end%%', true), $search))); ?>
	<?php elseif (strlen($search)): ?>
		<?php echo $this->Paginator->counter(array(
			'format' => sprintf(__('Searching for packages with <span class="highlight">%s</span>, page %%page%% of %%pages%%, showing results %%start%% to %%end%%', true), $search))); ?>
	<?php else : ?>
		<?php echo $this->Paginator->counter(array(
			'format' => __('Browsing packages, page %page% of %pages%, showing results %start% to %end%', true))); ?>
	<?php endif; ?>
</article>
