<?php
/**
 * Package Shell
 *
 * PHP version 5
 *
 * @category Package
 * @package  cakepackages
 * @version  0.1
 * @author   Jose Diaz-Gonzalez <support@savant.be>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.josediazgonzalez.com
 */

class PackageShell extends Shell {

/**
 * Main shell logic.
 *
 * @return void
 * @author John David Anderson
 */
    function main() {
        if (!empty($this->params[0])) {
            $this->command = $this->params[0];
        }

        $this->api = 'http://api.cakepackages.com';
        $this->{$this->command}();
    }

/**
 * Help
 *
 * @return void
 * @access public
 */
    function help() {
        $this->out('Package Shell - http://cakepackages.com');
        $this->hr();
        $this->out('This shell is a helper shell for many different kinds of tasks that might be performed by CakePackages');
        $this->out('');
        $this->hr();
        $this->out("Usage: cake package");
        $this->out("       cake package installed");
        $this->out("       cake package verify");
        $this->out("       cake package search");
        $this->out("       cake package install package_name");
        $this->out("       cake package install maintainer_name/package_name");
        $this->out("       cake package install maintainer_name/package_name -version 1.0");
        $this->out("       cake package install maintainer_name/package_name -folder package_alias");
        $this->out("       cake package install maintainer_name/package_name -plugin_dir global");
        $this->out("       cake package remove package_name");
        $this->out("       cake package remove package_name -alias true");
        $this->out("       cake package remove maintainer_name/package_name");
        $this->out("       cake package show package_name");
        $this->out('');
    }

    function installed() {
        $this->out("Installed packages");
        if (Configure::load('package') === false) {
            $this->out("No packages installed");
            return;
        }

        $this->nl();
        foreach (Configure::read('packages') as $package_alias => $config) {
            $this->out(sprintf("%s: Version %s", $config['name'], $config['version']));
        }
    }

    function verify() {
        $this->out("Installed packages");
        if (Configure::load('package') === false) {
            $this->out("No packages installed");
            return;
        }

        $this->nl();
        foreach (Configure::read('packages') as $package_alias => $config) {
            if (!$this->__verify($package_alias, $config)) {
                $this->out(sprintf("Error validating package %s: Version %s", $config['name'], $config['version']));
            } else {
                $this->out(sprintf("Verified %s: Version %s", $config['name'], $config['version']));
            }
        }
    }

    function __verify($package_alias, $config) {
        if (($packageConfig = $this->_load(sprintf("%s.config", $package_alias))) === false) {
            return false;
        }

        if ($config['version'] != $packageConfig['version']) {
            return false;
        }
        return true;
    }

    function search() {
        $this->_socket();

        $query = $this->in(__("Enter a search term or 'q' or nothing to exit", true), null, 'q');
        $this->out("Searching all plugins for query...");
        $plugins = $this->_search($query);

        if (empty($plugins)) {
            $this->out("No results found. Sorry.");
        } else {
            foreach ($plugins as $key => $result) {
                $name = str_replace('-', '_', $result['name']);
                $name = Inflector::humanize($name);
                if (substr_count($name, 'Plugin') > 0) {
                    $name = substr_replace($name, '', strrpos($name, ' Plugin'), strlen(' Plugin'));
                }
                $this->out(sprintf("%d. %s Plugin", $key + 1, $name));
            }
        }
    }

    function _search($query) {
        $results = array();

        Cache::set(array('duration' => '+7 days'));
        if (($results = Cache::read('Plugins.server.query.' . $query)) === false) {
            $results = json_decode($this->Socket->get(sprintf("%s/search/%s", $this->api, $query)));
            Cache::set(array('duration' => '+7 days'));
            Cache::write('Plugins.server.query.' . $query, $results);
        }

        return $results;
    }

    function _socket() {
        if (empty($this->Socket)) {
            $this->Socket = new HttpSocket();
        }
    }

/**
 * Loads a file from app/config/configure_file.php.
 * Config file variables should be formated like:
 *  `$config['name'] = 'value';`
 *
 * - To load config files from app/config use `this->load('configure_file');`.
 * - To load config files from a plugin `this->load('plugin.configure_file');`.
 *
 * @link http://book.cakephp.org/view/929/load
 * @param string $fileName name of file to load, extension must be .php and only the name
 *     should be used, not the extenstion
 * @return mixed false if file not found, array of config
 * @access private
 */
    function _load($fileName) {
        $found = $plugin = $pluginPath = false;
        list($plugin, $fileName) = pluginSplit($fileName);
        if ($plugin) {
            $pluginPath = App::pluginPath($plugin);
        }
        $pos = strpos($fileName, '..');

        if ($pos === false) {
            if ($pluginPath && file_exists($pluginPath . 'config' . DS . $fileName . '.php')) {
                include($pluginPath . 'config' . DS . $fileName . '.php');
                $found = true;
            } elseif (file_exists(CONFIGS . $fileName . '.php')) {
                include(CONFIGS . $fileName . '.php');
                $found = true;
            } elseif (file_exists(CACHE . 'persistent' . DS . $fileName . '.php')) {
                include(CACHE . 'persistent' . DS . $fileName . '.php');
                $found = true;
            } else {
                foreach (App::core('cake') as $key => $path) {
                    if (file_exists($path . DS . 'config' . DS . $fileName . '.php')) {
                        include($path . DS . 'config' . DS . $fileName . '.php');
                        $found = true;
                        break;
                    }
                }
            }
        }

        if (!$found) {
            return false;
        }

        if (!isset($config)) {
            trigger_error(sprintf(__('Configure::load() - no variable $config found in %s.php', true), $fileName), E_USER_WARNING);
            return false;
        }
        return $config;
    }
}