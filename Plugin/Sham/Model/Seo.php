<?php
class Seo extends ShamAppModel {

	public function retrieveBySlug($slug, $options = array()) {
		$options = array_merge(array(
			'record' => false,
			'seo_only' => false,
			'skip' => array(),
		), (array)$options);

		$hash = md5(serialize($options));
		if (($record = Cache::read('seo.slug.' . $hash)) !== false) {
			return $record;
		}

		$seo = $this->findBySlug($slug);
		if (!$seo) {
			return null;
		}

		if ($options['seo_only']) {
			$keys = array('title_for_layout', 'description', 'keywords', 'canonical', 'h2_for_layout');
			$seo[$this->alias] = array_intersect_key($seo[$this->alias], array_combine($keys, $keys));
		}

		if (!empty($options['skip'])) {
			$seo[$this->alias] = array_diff_key($seo[$this->alias], (array)$options['skip']);
		}

		if ($options['record']) {
			Cache::write('seo.slug.' . $hash, $seo);
			return $seo;
		}

		Cache::write('seo.slug.' . $hash, $seo[$this->alias]);
		return $seo[$this->alias];
	}

}
