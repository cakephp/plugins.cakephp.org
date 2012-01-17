<?php
/**
 * LazyHelperView
 *
 * Provides automatic loading, or "lazy loading" of heleprs for the `View`
 * class.
 *
 * The `load` setting will hint to the LazyHelperView that a Helper should
 * be loaded or should be skipped
 *
 *
 *   public $helpers = array(
 *     'DebugKit.Toolbar' => array('load' => false)
 *     'Html' => array('load' => false),
 *     'Form',
 *     'Js' => array('load' => true),
 *     'AssetCompress.AssetCompress'
 *   );
 *
 * In the above example, the `HtmlHelper`, `FormHelper` and
 * `DebugKit.ToolbarHelper` will be lazy loaded, while the `JsHelper` and
 * `AssetCompressHelper` will defer loading until they are called within a
 * view. This is because plugins are not lazy-loaded by default, while
 * Helpers from the App are lazy loaded by default.
 *
 * Calling `View::loadHelper()` will force the loading of a helper.
 *
 * Setting the `load` setting to `true` will force the loading of a helper.
 *
 * All helpers MUST be specified within the Controller as normal, otherwise
 * it will not be lazy-loaded.
 *
 * To lazy-load a Plugin Helper, you may defer loading using the `load` setting,
 * and then refer to the Helper as normal within the view.
 *
 * This View also supports Themes, as the ThemeView normally does
 *
 * @author Jose Diaz-Gonzalez <support@savant.be>
 */
class LazyHelperView extends View {

/**
 * Stores our array of known helpers.
 *
 * @var array
 * @access protected
 */
    protected $_helpers = array();

/**
 * Constructor for LazyHelperView sets $this->theme.
 *
 * @param Controller $controller Controller object to be rendered.
 */
	public function __construct($controller) {
		parent::__construct($controller);
		if ($controller && !empty($controller->theme)) {
			$this->theme = $controller->theme;
		}
	}

/**
 * Interact with the HelperCollection to load all the helpers.
 *
 * @return void
 */
	public function loadHelpers() {
		$this->_helpers = HelperCollection::normalizeObjectArray($this->helpers);

		foreach ($this->_helpers as $name => $properties) {
			list($plugin, $class) = pluginSplit($properties['class']);

			if (isset($properties['settings']['load']) && $properties['settings']['load'] === false) {
				continue;
			}

			if ($plugin) {
				$this->{$class} = $this->Helpers->load($properties['class'], $properties['settings']);
			} else if (isset($properties['settings']['load']) && $properties['settings']['load'] === true) {
				$this->{$class} = $this->Helpers->load($properties['class'], $properties['settings']);
			}
		}

		$this->_helpersLoaded = true;
	}

/**
 * Called when a request for a non-existant member variable is caught.
 * If the requested $variable matches a known helper we will attempt to
 * load it up for the caller.
 *
 * @param string $variable
 * @return mixed
 * @access public
 */
	public function __get($helperName) {
		if (isset($this->_helpers[$helperName])) {
			return $this->Helpers->load(
				$this->_helpers[$helperName]['class'],
				$this->_helpers[$helperName]['settings']
			);
		}
	}


/**
 * Return all possible paths to find view files in order
 *
 * @param string $plugin The name of the plugin views are being found for.
 * @param boolean $cached Set to true to force dir scan.
 * @return array paths
 * @todo Make theme path building respect $cached parameter.
 */
	protected function _paths($plugin = null, $cached = true) {
		$paths = parent::_paths($plugin, $cached);
		$themePaths = array();

		if (!empty($this->theme)) {
			$count = count($paths);
			for ($i = 0; $i < $count; $i++) {
				if (strpos($paths[$i], DS . 'Plugin' . DS) === false
					&& strpos($paths[$i], DS . 'Cake' . DS . 'View') === false) {
						if ($plugin) {
							$themePaths[] = $paths[$i] . 'Themed'. DS . $this->theme . DS . 'Plugin' . DS . $plugin . DS;
						}
						$themePaths[] = $paths[$i] . 'Themed'. DS . $this->theme . DS;
					}
			}
			$paths = array_merge($themePaths, $paths);
		}
		return $paths;
	}

}