<?php
class ResourceHelper extends AppHelper {
	var $helpers = array('Html');

	function package($name, $maintainer) {
		return $this->Html->link($name, array(
			'plugin' => null,
			'controller' => 'packages',
			'action' => 'view',
			'package' => $name,
			'maintainer' => $maintainer));
	}

	function maintainer($name = null, $username = null) {
		$name = (!empty($name)) ? $name : $username;
 		return $this->Html->link($name, array(
			'plugin' => null,
			'controller' => 'maintainers',
			'action' => 'view',
			$username));
	}

	function repository($maintainer, $name) {
		return "git://github.com/{$maintainer}/{$name}.git";
	}

	function description($text) {
		$text = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">Link</a>", $text);  
		return $this->truncate($text, 100, array('html' => true));
	}

	function searchableMaintainer($data, $options) {
		$data = json_decode($data, true);
		return $this->maintainer($data[$options['primary']], $data[$options['fallback']]);
	}

/**
 * Truncates text.
 *
 * Cuts a string to the length of $length and replaces the last characters
 * with the ending if the text is longer than length.
 *
 * ### Options:
 *
 * - `ending` Will be used as Ending and appended to the trimmed string
 * - `exact` If false, $text will not be cut mid-word
 * - `html` If true, HTML tags would be handled correctly
 *
 * @param string  $text String to truncate.
 * @param integer $length Length of returned string, including ellipsis.
 * @param array $options An array of html attributes and options.
 * @return string Trimmed string.
 * @access public
 */
	function truncate($text, $length = 100, $options = array()) {
		$default = array(
			'ending' => '...', 'exact' => true, 'html' => false
		);
		$options = array_merge($default, $options);
		extract($options);

		if ($html) {
			if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			$totalLength = mb_strlen(strip_tags($ending));
			$openTags = array();
			$truncate = '';

			preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
			foreach ($tags as $tag) {
				if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
					if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
						array_unshift($openTags, $tag[2]);
					} else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
						$pos = array_search($closeTag[1], $openTags);
						if ($pos !== false) {
							array_splice($openTags, $pos, 1);
						}
					}
				}
				$truncate .= $tag[1];

				$contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
				if ($contentLength + $totalLength > $length) {
					$left = $length - $totalLength;
					$entitiesLength = 0;
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
						foreach ($entities[0] as $entity) {
							if ($entity[1] + 1 - $entitiesLength <= $left) {
								$left--;
								$entitiesLength += mb_strlen($entity[0]);
							} else {
								break;
							}
						}
					}

					$truncate .= mb_substr($tag[3], 0 , $left + $entitiesLength);
					break;
				} else {
					$truncate .= $tag[3];
					$totalLength += $contentLength;
				}
				if ($totalLength >= $length) {
					break;
				}
			}
		} else {
			if (mb_strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = mb_substr($text, 0, $length - mb_strlen($ending));
			}
		}
		if (!$exact) {
			$spacepos = mb_strrpos($truncate, ' ');
			if (isset($spacepos)) {
				if ($html) {
					$bits = mb_substr($truncate, $spacepos);
					preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
					if (!empty($droppedTags)) {
						foreach ($droppedTags as $closingTag) {
							if (!in_array($closingTag[1], $openTags)) {
								array_unshift($openTags, $closingTag[1]);
							}
						}
					}
				}
				$truncate = mb_substr($truncate, 0, $spacepos);
			}
		}
		$truncate .= $ending;

		if ($html) {
			foreach ($openTags as $tag) {
				$truncate .= '</'.$tag.'>';
			}
		}

		return $truncate;
	}
}
?>