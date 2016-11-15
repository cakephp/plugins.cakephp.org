<?php
class DATABASE_CONFIG
{
    public $default = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'host' => 'localhost',
        'login' => 'user',
        'password' => 'password',
        'database' => 'cakepackages',
        'prefix' => '',
        'encoding' => 'utf8',
    );

    public $test = array(
        'database' => 'test',
    );

    public $development = array(
        'datasource' => 'Database/MysqlLog',
        'login' => 'user',
        'password' => 'password',
        'database' => 'cakepackages',
    );

    public $staging = array(
        'login' => 'cakepackages_sta',
        'password' => 'cakepackages_sta',
        'database' => 'cakepackages_staging',
    );

    public $production = array(
        'login' => 'cakepackages',
        'password' => 'cakepackages',
        'database' => 'cakepackages',
    );

    public $test_cakeusers = array(
    );

    public $development_cakeusers = array(
    );

    public $staging_cakeusers = array(
    );

    public $production_cakeusers = array(
        'database' => 'cakeusers',
    );

    public $github = array(
        'datasource' => 'GithubSource',
        'token' => null,
    );

    protected $skip = array(
        'skip', 'default', 'github', 'environments',
        'test_cakeusers', 'development_cakeusers',
        'staging_cakeusers', 'production_cakeusers',
    );

    protected $environments = array(
        'development' => array('development_cakeusers'),
        'staging' => array('staging_cakeusers'),
        'production' => array('production_cakeusers'),
        'test' => array('test_cakeusers'),
    );

/**
 * Generates a connection based on the current environment
 *
 * Does not account for multiple connections in an environment, ie. MySQL and Redis
 *
 * @todo Support multiple in-environment connections
 */
    public function __construct()
    {
        if (env('DATABASE_URL')) {
            $dsn = parse_url(env('DATABASE_URL'))
            $this->default['host'] = $dsn['host'];
            $this->default['login'] = $dsn['user'];
            $this->default['password'] = $dsn['pass'];
            $this->default['database'] = substr($dsn['path'], 1);
            return;
        }

        // once Environment has decided where we at, it will write the name into Configure.
        if ($environment = Configure::read('Environment.name')) {

            // Require that the environment have a database configuration
            if (!isset($this->{$environment})) {
                throw new RuntimeException(sprintf('Missing Database Configuration %s', $environment));
            }

            // Merge environment into defaults
            $this->default = array_merge($this->default, $this->{$environment});

            // Merge environment with the environment-specific configurations
            if (isset($this->environments[$environment])) {
                foreach ($this->environments[$environment] as $name) {
                    $this->$name = array_merge($this->default, $this->$name);
                }
            }
        }

        // if everything above went smooth, $this->default now has the correct login info.
        foreach (get_object_vars($this) as $name => $config) {
            if (in_array($name, $this->skip)) {
                continue;
            }

            $this->$name = array_merge($this->default, $config);
        }
    }
}
