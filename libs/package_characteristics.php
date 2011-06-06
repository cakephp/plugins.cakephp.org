<?php
App::import('Core', array(
    'Object',
    'Controller',
    'Model',
    'DataSource',
    'View',
    'Helper',
    'Folder',
    'HttpSocket'
));
App::import('Shell', array('Schema'));
App::import('Helper', array('Form', 'Html', 'Paginator', 'Time'));
App::import('Lib', 'PackageFolder');
App::import('Model', 'ApiGenerator.ApiFile');
App::import('Controller', 'AppController');

$corePath = App::core('cake');
if (isset($corePath[0])) {
    define('TEST_CAKE_CORE_INCLUDE_PATH', rtrim($corePath[0], DS) . DS);
} else {
    define('TEST_CAKE_CORE_INCLUDE_PATH', CAKE_CORE_INCLUDE_PATH);
}

require_once CAKE_TESTS_LIB . 'cake_test_case.php';


class PackageCharacteristics extends ApiFile {

/**
 * Path to package on filesystem
 *
 * @var string
 */
    public $path = null;

/**
 * Array of characteristics in a particular package
 *
 * @var string
 */
    public $characteristics = array();

/**
 * A regexp for file names. (will be made case insenstive)
 *
 * @var string
 **/
    public $fileRegExp = '[a-z_\-0-9]+';

/**
 * Folder instance
 *
 * @var Folder
 **/
    protected $_Folder;

/**
 * A list of folders to ignore.
 *
 * @var array
 **/
    public $excludeDirectories = array('plugins', 'webroot');

/**
 * excludeMethods property
 *
 * @var array
 */
    public $excludeMethods = array();

/**
 * excludeProperties property
 *
 * @var array
 */
    public $excludeProperties = array();

/**
 * A list of files to ignore.
 *
 * @var array
 **/
    public $excludeFiles = array('index.php');

/**
 * a list of extensions to scan for
 *
 * @var array
 **/
    public $allowedExtensions = array('php');

/**
 * Array of class dependancies map
 *
 * @var array
 **/
    public $dependencyMap = array();

/**
 * Mappings of funny named classes to files
 *
 * @var string
 **/
    public $classMap = array();

    public function __construct($path) {
        if (file_exists($path)) {
            $this->path = $path;
        } else {
            throw new InvalidArgumentException(sprintf("Path %s does not exist", $path));
        }
        $this->_Folder = new PackageFolder(APP);
        $this->_Folder->folderExceptions = array(
            '.git',
            '.localhost',
        );
    }

    public function characterize() {
        $characteristics = array();
        $files = $this->fileList($this->path);
        foreach ($files as $file) {
            $characteristics = array_merge(
                $characteristics,
                $this->loadFile($file)
            );
        }
        // Other Characteristics
        $characteristics = array_merge(
            $characteristics,
            $this->loadRepo($this->path)
        );
        if (empty($characteristics)) return false;
        $contains = array(
            'model'     => 0,
            'view'      => 0,
            'controller'=> 0,
            'behavior'  => 0,
            'helper'    => 0,
            'shell'     => 0,
            'datasource'=> 0,
            'tests'     => 0,
            'themed'    => 0,
            'elements'  => 0,
            'vendor'    => 0,
            'lib'       => 0,
            'config'    => 0,
            'resource'  => 0,
        );
        $contains = array_merge($contains, $characteristics);

        $data = array();
        foreach ($contains as $field => $value) {
            $data['contains_' . $field] = $value;
        }
        return $data;
    }

/**
 * Recursive Read a path and return files and folders not in the excluded Folder list
 *
 * @param string $path The path you wish to read.
 * @return array
 **/
    public function fileList($path) {
        $this->_Folder->cd($path);
        $filePattern =  $this->fileRegExp . '\.' . implode('|', $this->allowedExtensions);
        $contents = $this->_Folder->findRecursive($filePattern);
        $this->_filterFolders($contents);
        $this->_filterFiles($contents);
        return $contents;
    }

    public function loadFile($filePath, $options = array()) {
        $docs = array('class' => array(), 'function' => array());
        if (preg_match('|\.\.|', $filePath)) {
            return $docs;
        }
        if (!defined('DISABLE_AUTO_DISPATCH')) {
            define('DISABLE_AUTO_DISPATCH', true);
        }
        if (!$this->isAllowed($filePath)) {
            throw new Exception(sprintf(__("No file with this name exists: %s", true), $filePath), true);
        }

        $contains = array();
        $types = array(
            'model'         => 'Model',
            'view'          => 'View',
            'controller'    => 'Controller',
            'behavior'      => 'ModelBehavior',
            'helper'        => 'Helper',
            'shell'         => 'Shell',
            'datasource'    => 'DataSource',
            'tests'         => 'CakeTestCase',
        );

        $this->_importCakeBaseClasses($filePath);
        $this->_resolveDependancies($filePath, $options);
        $this->_getDefinedObjects();
        $newObjects = $this->findObjectsInFile($filePath);
        foreach ($newObjects as $type => $objects) {
            if ($type != 'class') continue;

            foreach ($objects as $element) {
                $testClass = new ReflectionClass($element);
                foreach ($types as $contain => $type) {
                    if ($testClass->isSubclassOf($type)) {
                        $contains[$contain] = 1;
                    }
                }
                if (strstr($element, 'Component')) {
                    $contains['component'] = 1;
                }
            }
        }

        return $contains;
    }

/**
 * Fetches the class names and functions contained in the target file.
 * If first pass misses, a forceParse pass will be run.
 *
 * @param string $filePath Absolute file path to file you want to read.
 * @param boolean $forceParse Force the manual read of a file.
 * @return array
 **/
    public function findObjectsInFile($filePath) {
        $new = $tmp = array();
        $tmp['class'] = $this->_parseClassNamesInFile($filePath);

        $include = false;
        foreach ($tmp['class'] as $classInFile) {
            $include = false;
            if (!class_exists($classInFile, false)) {
                $include = true;
            }
        }

        if (!$include) {
            $new = $tmp;
        } else {
            ob_start();
            include_once $filePath;
            ob_clean();

            $new['class'] = array_diff(get_declared_classes(), $this->_definedClasses);
            $funcs = get_defined_functions();
        }
        return $new;
    }

    public function loadRepo($path) {
        $characteristics = array();
        $this->_Folder->cd($path);
        $contents = $this->_Folder->read(true, array('.', 'empty'));

        if (in_array('app', $contents[0])) {
            $characteristics[] = 'app';
            $path = $path . DS . 'app';
            $this->_Folder->cd($path);
            $contents = $this->_Folder->read(true, array('.', 'empty'));
        }
         return array_merge(
            $this->__classifyContents($path, $contents),
            $characteristics
        );
    }

/**
 * Classifies the contents of a repository based upon raw
 * Folder::cd() and Folder::read() methods
 *
 * @param string $path path to a git repository on disk
 * @param array $contents an array of files and folders in the base repository path
 * @return array Array of characteristics
 * @access protected
 */
    function __classifyContents($path, $contents = array()) {
        $characteristics = array();
        $resources = null;


        if (in_array('views', $contents[0])) {
            $this->_Folder->cd($path . DS . 'views');
            $view_contents = $this->_Folder->read(true, array('.', 'empty'));

            if (in_array('themed', $view_contents[0])) {
                $this->_Folder->cd($path . DS . 'views' . DS . 'themed');
                $theme_contents = $this->_Folder->read(true, array('.', 'empty'));
                if (!empty($theme_contents[0])) {
                    $characteristics['theme'] = 1;
                }
                $view_contents[0] = array_diff($view_contents[0], array('themed'));
            }
            if (in_array('elements', $view_contents[0])) {
                $view_contents[0] = array_diff($view_contents[0], array('elements'));
            }
        }

        if (in_array('vendors', $contents[0])) {
            $this->_Folder->cd($path . DS . 'vendors');
            $vendor_contents = $this->_Folder->read(true, array('.', 'empty'));

            if (!empty($vendor_contents[0]) || !empty($vendor_contents[1])) {
                $characteristics['vendor'] = 1;
            }
        }

        if (in_array('libs', $contents[0])) {
            $this->_Folder->cd($path . DS . 'libs');
            $lib_contents = $this->_Folder->read(true, array('.', 'empty'));
            if (!empty($lib_contents[1]) && (count($lib_contents[1]) != 1 || $lib_contents[1][0] != 'empty')) {
                $characteristics['lib'] = 1;
            }
        }
        if (in_array('config', $contents[0])) {
            $this->_Folder->cd($path . DS . 'config');
            $config_contents = $this->_Folder->read(true, array('.', 'empty'));
            if (!empty($config_contents[1]) && (count($config_contents[1]) != 1 || $config_contents[1][0] != 'empty')) {
                $characteristics['config'] = 1;
            }
        }

        if ($resources) $characteristics['resource'] = 1;
        return $characteristics;
    }

}