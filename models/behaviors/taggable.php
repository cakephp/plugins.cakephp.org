<?php
class TaggableBehavior extends ModelBehavior {

	private $toSave = array();
	private $tagIdsAdd = array();
	private $tagIdsSubtract = array();

	public function beforeSave(&$model) {
		if (isset($model->data[$model->alias]['tags']) and !empty($model->data[$model->alias]['tags'])) {
			$tags = explode(" ", $model->data[$model->alias]['tags']);
			$i = 1;
			$this->toSave = array();
			$this->tagIds = array();
			if (isset($model->data[$model->alias][$model->primaryKey])) {
				//existing package, lets diff the tags
				$rec = $model->find('first', array(
					'conditions' => array(
						"{$model->alias}.{$model->primaryKey}" => $model->data[$model->alias][$model->primaryKey]),
					'contain' => array(
						'Tag')));
				$existingTags = array();
				if (!empty($existingTags)) {
					foreach ($rec['Tag'] as $aTag) {
						$existingTags[] = $aTag['id'];
					}
					$existingTags = array_combine($existingTags, $existingTags);
				}
				foreach ($tags as $tag) {
					$tag = preg_replace("/[^a-zA-Z0-9\s]/", "", $tag);
					$tag_id = $model->Tag->lookup($tag);
					$this->toSave[$i]['tag_id'] = $tag_id;
					if (!in_array($tag_id, $existingTags)) {
						$this->tagIdsAdd[] = $tag_id;
					} else {
						unset($existingTags[$tag_id]);
					}
					$i++;
				}
				foreach ($existingTags as $existingTag) {
					$this->tagIdsSubtract[] = $existingTag;
				}
			} else {
				foreach($tags as $tag) {
					$tag = preg_replace("/[^a-zA-Z0-9\s]/", "", $tag);
					$tag_id = $model->Tag->lookup($tag);
					$this->toSave[$i]['tag_id'] = $tag_id;
					$this->tagIdsAdd[] = $tag_id;
					$i++;
				}
			}
		}
		return true;
	}

	public function afterSave(&$model, $created) {
		if ($created) {
			$model->data[$model->alias][$model->primaryKey] = $model->id;
		} else {
			$model->PackagesTag->deleteAll(array("PackagesTag.package_id" => $model->data[$model->alias][$model->primaryKey]));
		}

		$records = $this->toSave;
		foreach($records as  &$record) {
			$record['package_id'] = $model->id;
		}

		if (count($this->tagIdsAdd) > 0) {
			$model->Tag->updateAll(array('Tag.packages_count' => 'Tag.packages_count+1'), array('Tag.id' => $this->tagIdsAdd));
		}
		if (count($this->tagIdsSubtract) > 0) {
			$model->Tag->updateAll(array('Tag.packages_count' => 'Tag.packages_count-1'), array('Tag.id' => $this->tagIdsSubtract));
		}
		return $model->PackagesTag->saveAll($records);
	}

	public function afterFind(&$model, $results) {
		foreach ($results as &$result) {
			if (isset($result['Tag'])) {
				$count = count($result['Tag']) - 1;
				$result[$model->alias]['tags'] = '';
				foreach ($result['Tag'] as $key => &$tag) {
					$result[$model->alias]['tags'] .= ($count != $key) ? "{$tag['name']} " : $tag['name'];
				}
			}
		}
		return $results;
	}
}
?>