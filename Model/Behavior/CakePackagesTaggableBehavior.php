<?php
App::uses('TaggableBehavior', 'Tags.Model/Behavior');
App::uses('CakeSession', 'Model/Datasource');

/**
 * Cake Packages Version of Taggable Behavior
 */
class CakePackagesTaggableBehavior extends TaggableBehavior {

/**
 * If logged in user is an admin
 *
 * @var boolean
 */
	protected $_isAdmin = false;

/**
 * Tags that are not allowed
 *
 * @var array
 */
	protected $_blacklistedTags = array(
		'ahole', 'anus', 'ash0le', 'ash0les', 'asholes', 'ass', 'Ass Monkey',
		'Assface', 'assh0le', 'assh0lez', 'asshole', 'assholes', 'assholz',
		'asswipe', 'azzhole', 'bassterds', 'bastard', 'bastards', 'bastardz',
		'basterds', 'basterdz', 'Biatch', 'bitch', 'bitches', 'Blow Job',
		'boffing', 'butthole', 'buttwipe', 'c0ck', 'c0cks', 'c0k',
		'Carpet Muncher', 'cawk', 'cawks', 'Clit', 'cnts', 'cntz', 'cock',
		'cockhead', 'cock-head', 'cocks', 'CockSucker', 'cock-sucker', 'crap',
		'cum', 'cunt', 'cunts', 'cuntz', 'dick', 'dild0', 'dild0s', 'dildo',
		'dildos', 'dilld0', 'dilld0s', 'dominatricks', 'dominatrics',
		'dominatrix', 'dyke', 'enema', 'f u c k', 'f u c k e r', 'fag', 'fag1t',
		'faget', 'fagg1t', 'faggit', 'faggot', 'fagit', 'fags', 'fagz', 'faig',
		'faigs', 'fart', 'flipping the bird', 'fuck', 'fucker', 'fuckin',
		'fucking', 'fucks', 'Fudge Packer', 'fuk', 'Fukah', 'Fuken', 'fuker',
		'Fukin', 'Fukk', 'Fukkah', 'Fukken', 'Fukker', 'Fukkin', 'g00k', 'gay',
		'gayboy', 'gaygirl', 'gays', 'gayz', 'God-damned', 'h00r', 'h0ar',
		'h0re', 'hells', 'hoar', 'hoor', 'hoore', 'jackoff', 'jap', 'japs',
		'jerk-off', 'jisim', 'jiss', 'jizm', 'jizz', 'knob', 'knobs', 'knobz',
		'kunt', 'kunts', 'kuntz', 'Lesbian', 'Lezzian', 'Lipshits', 'Lipshitz',
		'masochist', 'masokist', 'massterbait', 'masstrbait', 'masstrbate',
		'masterbaiter', 'masterbate', 'masterbates', 'Motha Fucker',
		'Motha Fuker', 'Motha Fukkah', 'Motha Fukker', 'Mother Fucker',
		'Mother Fukah', 'Mother Fuker', 'Mother Fukkah', 'Mother Fukker',
		'mother-fucker', 'Mutha Fucker', 'Mutha Fukah', 'Mutha Fuker',
		'Mutha Fukkah', 'Mutha Fukker', 'n1gr', 'nastt', 'nigger;', 'nigur;',
		'niiger;', 'niigr;', 'orafis', 'orgasim;', 'orgasm', 'orgasum',
		'oriface', 'orifice', 'orifiss', 'packi', 'packie', 'packy', 'paki',
		'pakie', 'paky', 'pecker', 'peeenus', 'peeenusss', 'peenus', 'peinus',
		'pen1s', 'penas', 'penis', 'penis-breath', 'penus', 'penuus', 'Phuc',
		'Phuck', 'Phuk', 'Phuker', 'Phukker', 'polac', 'polack', 'polak',
		'Poonani', 'pr1c', 'pr1ck', 'pr1k', 'pusse', 'pussee', 'pussy', 'puuke',
		'puuker', 'queer', 'queers', 'queerz', 'qweers', 'qweerz', 'qweir',
		'recktum', 'rectum', 'retard', 'sadist', 'scank', 'schlong', 'screwing',
		'semen', 'sex', 'sexy', 'Sh!t', 'sh1t', 'sh1ter', 'sh1ts', 'sh1tter',
		'sh1tz', 'shit', 'shits', 'shitter', 'Shitty', 'Shity', 'shitz', 'Shyt',
		'Shyte', 'Shytty', 'Shyty', 'skanck', 'skank', 'skankee', 'skankey',
		'skanks', 'Skanky', 'slut', 'sluts', 'Slutty', 'slutz', 'son-of-a-bitch',
		'tit', 'turd', 'va1jina', 'vag1na', 'vagiina', 'vagina', 'vaj1na',
		'vajina', 'vullva', 'vulva', 'w0p', 'wh00r', 'wh0re', 'whore', 'xrated',
		'xxx', 'b!+ch', 'bitch', 'blowjob', 'clit', 'arschloch', 'fuck', 'shit',
		'ass', 'asshole', 'b!tch', 'b17ch', 'b1tch', 'bastard', 'bi+ch',
		'boiolas', 'buceta', 'c0ck', 'cawk', 'chink', 'cipa', 'clits', 'cock',
		'cum', 'cunt', 'dildo', 'dirsa', 'ejakulate', 'fatass', 'fcuk', 'fuk',
		'fux0r', 'hoer', 'hore', 'jism', 'kawk', 'l3itch', 'l3i+ch', 'lesbian',
		'masturbate', 'masterbat', 'masterbat3', 'motherfucker', 's.o.b.',
		'mofo', 'nazi', 'nigga', 'nigger', 'nutsack', 'phuck', 'pimpis',
		'pusse', 'pussy', 'scrotum', 'sh!t', 'shemale', 'shi+', 'sh!+', 'slut',
		'smut', 'teets', 'tits', 'boobs', 'b00bs', 'teez', 'testical',
		'testicle', 'titt', 'w00se', 'jackoff', 'wank', 'whoar', 'whore',
		'damn', 'dyke', 'fuck', 'shit', '@$$', 'amcik', 'andskota', 'arse',
		'assrammer', 'ayir', 'bi7ch', 'bitch', 'bollock', 'breasts',
		'butt-pirate', 'cabron', 'cazzo', 'chraa', 'chuj', 'Cock', 'cunt',
		'd4mn', 'daygo', 'dego', 'dick', 'dike', 'dupa', 'dziwka', 'ejackulate',
		'Ekrem', 'Ekto', 'enculer', 'faen', 'fag', 'fanculo', 'fanny', 'feces',
		'feg', 'Felcher', 'ficken', 'fitt', 'Flikker', 'foreskin', 'Fotze',
		'Fu(', 'fuk', 'futkretzn', 'gay', 'gook', 'guiena', 'h0r', 'h4x0r',
		'hell', 'helvete', 'hoer', 'honkey', 'Huevon', 'hui', 'injun', 'jizz',
		'kanker', 'kike', 'klootzak', 'kraut', 'knulle', 'kuk', 'kuksuger',
		'Kurac', 'kurwa', 'kusi', 'kyrpa', 'lesbo', 'mamhoon', 'masturbat',
		'merd', 'mibun', 'monkleigh', 'mouliewop', 'muie', 'mulkku', 'muschi',
		'nazis', 'nepesaurio', 'nigger', 'orospu', 'paska', 'perse', 'picka',
		'pierdol', 'pillu', 'pimmel', 'piss', 'pizda', 'poontsee', 'poop',
		'porn', 'p0rn', 'pr0n', 'preteen', 'pula', 'pule', 'puta', 'puto',
		'qahbeh', 'queef', 'rautenberg', 'schaffer', 'scheiss', 'schlampe',
		'schmuck', 'screw', 'sh!t', 'sharmuta', 'sharmute', 'shipal', 'shiz',
		'skrib',
	);

/**
 * Check Auth is user is admin
 */
	public function setup(Model $model, $settings = array()) {
		parent::setup($model, $settings);
		if (CakeSession::check('Auth')) {
			$this->_isAdmin = CakeSession::read('Auth.User.is_admin') ? true : false;
		}
	}

	public function isAdmin($value = null) {
		if ($value === null) {
			return $this->_isAdmin;
		}

		$this->_isAdmin = $value;
	}

/**
 * Override to check if !admin for identifiers and blacklisted tags
 *
 * @param Model $model
 * @param string $string
 * @param string $separator
 * @return array
 */
	public function disassembleTags(Model $model, $string = '', $separator = ',') {
		$tags = parent::disassembleTags($model, $string, $separator);
		if (!$this->_isAdmin) {
			foreach ($tags['tags'] as $key => $val) {
				$tags['tags'][$key]['identifier'] = null;
			}
			foreach ($tags['identifiers'] as $key => $val) {
				$tags['identifiers'][$key] = array(null);
			}
			foreach ($tags['tags'] as $key => $val) {
				if (in_array($val['keyname'], $this->_blacklistedTags)) {
					unset($tags['tags'][$key]);
				}
			}
			foreach ($tags['identifiers'] as $key => $val) {
				if (in_array($key, $this->_blacklistedTags)) {
					unset($tags['identifiers'][$key]);
				}
			}
		}
		return $tags;
	}

/**
 * Rebuild `contains` field into `tags` with identifiers
 *
 * @param Model $model
 * @return boolean
 */
	public function beforeSave(Model $model, $options = array()) {
		if (!empty($model->data[$model->alias]['contains'])) {
			$contains = array_filter($model->data[$model->alias]['contains']);
			if (!empty($contains)) {
				$contains = ', contains:' . implode(', contains:', $contains);
				$model->data[$model->alias]['tags'] .= $contains;
			}
			unset($model->data[$model->alias]['contains']);
		}
		return true;
	}

/**
 * Rebuild `tags` without identifiers and ones with `contains` set each
 * legacy `contains_` fields
 *
 * @param Model $model
 * @param array $results
 * @param boolean $primary
 */
	public function afterFind(Model $model, $results, $primary = false) {
		$validTypes = !empty($model->validTypes) ? $model->validTypes : array();
		extract($this->settings[$model->alias]);
		foreach ($results as $key => $row) {
			foreach ($validTypes as $type) {
				if (isset($row[$model->alias]['contains_' . $type])) {
					$results[$key][$model->alias]['contains_' . $type] = false;
				}
			}
			if (!empty($row['Tag'])) {
				$tags = array();
				foreach ($row['Tag'] as $tag) {
					if (empty($tag['identifier'])) {
						$tags[] = $tag['name'];
					} elseif ($tag['identifier'] == 'contains') {
						$results[$key][$model->alias]['contains_' . $tag['keyname']] = true;
					}
				}
				sort($tags);
				$tags = join($separator . ' ', $tags);
				$results[$key][$model->alias][$field] = $tags;
			}
		}
		return $results;
	}

}
