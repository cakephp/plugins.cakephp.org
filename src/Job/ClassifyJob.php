<?php
namespace App\Job;

use App\Job\CloneJob;
use App\Job\PerformerTrait;
use App\Model\Entity\Package;
use App\Traits\LogTrait;
use Cake\Collection\Collection;
use Cake\Datasource\ModelAwareTrait;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Http\Client;
use Cake\Utility\Hash;
use josegonzalez\Queuesadilla\Engine\NullEngine;
use josegonzalez\Queuesadilla\Job\Base;
use Psr\Log\NullLogger;

class ClassifyJob
{
    use LogTrait;

    use ModelAwareTrait;

    use PerformerTrait;

    protected $_fileRegex = [
        'app' => [
            '/app\//',
        ],
        'auth-storage' => [
            '/Auth\/Storage\/([\w]+)Storage.php$/i',
        ],
        'authenticate' => [
            '/Auth\/([\w]+)Authenticate.php$/i',
        ],
        'authorize' => [
            '/Auth\/([\w]+)Authorize.php$/i',
        ],
        'behavior' => [
            '/Models?\/Behaviors?\/([\w]+)Behavior.php$/i',
        ],
        'cache-engine' => [
            '/Cache\/Engine\/([\w]+)Engine.php$/i',
        ],
        'cell' => [
            '/View\/Cell\/([\w\/]+).php$/i',
        ],
        'composer' => [
            '/composer.json$/i',
        ],
        'component' => [
            '/Controllers?\/Components?\/([\w]+)Controller.php$/i',
        ],
        'config' => [
            '/Config\/([\w_\/]+).php$/i',
        ],
        'controller' => [
            '/Controllers?\/([\w]+)Controller.php$/i',
        ],
        'datasource' => [
            '/Models?\/Datasources?\/([\w]+)(Source)?.php$/i',
            '/Models?\/Databases?\/([\w]+).php$/i',
        ],
        'elements' => [
            '/View\/Elements\/([\w\/]+).ctp$/i',
            '/Template\/Element\/([\w\/]+).ctp$/i',
        ],
        'entity' => [
            '/Model\/Entity\/([\w]+).php$/i',
        ],
        'fixture' => [
            '/Test\/Fixture\/([\w]+)Fixture.php$/i',
        ],
        'helper' => [
            '/Views?\/Helpers?\/([\w]+)(Helper)?.php$/i',
        ],
        'lib' => [
            '/Lib\/([\w\/]+).php$/i',
        ],
        'license' => [
            '/^LICENSE(?:\.txt)?$/i',
        ],
        'locale' => [
            '/Locale\/([\w\/]+).pot$/i',
            '/Locale\/([\w\/]+).po$/i',
        ],
        'log' => [
            '/Log\/Engine\/([\w]+).php$/i',
        ],
        'mail-transport' => [
            '/Mailer\/Transport\/([\w]+)Transport.php$/i',
        ],
        'middleware' => [
            '/Middleware\/([\w]+)Middleware.php$/i',
        ],
        'model' => [
            '/Models?\/([\w]+).php$/i',
        ],
        'panel' => [
            '/Lib\/Panel\/([\w]+)Panel.php$/i',
        ],
        'password-hasher' => [
            '/Auth\/([\w]+)Hasher.php$/i',
        ],
        'plugin' => [
        ],
        'readme' => [
            '/^README.(md|markdown|textile|rst)$/i',
        ],
        'resource' => [
            '/webroot\/([\w]+)\/([.]+).(bmp|css|gif|js|jpeg|jpg|png)$/i',
        ],
        'route-class' => [
            '/Routing\/Route\/([\w]+)Route.php$/i',
        ],
        'route-filter' => [
            '/Routing\/Filter\/([\w]+)Filter.php$/i',
        ],
        'shell' => [
            '/Console\/Command\/([\w]+)(Shell)?.php$/i',
            '/Shells?\/([\w]+)(Shell)?.php$/i',
            '/Shells?\/Tasks?\/([\w]+)(Task)?.php$/i',
        ],
        'table' => [
            '/Model\/Table\/([\w]+).php$/i',
        ],
        'tests' => [
            '/Tests?\/TestCases?\/([\w\/]+)(Test)?.php$/i',
            '/Tests?\/Cases?\/([\w\/]+)(Test)?.php$/i',
        ],
        'themed' => [
            '/View\/Themed\/([\w\/]+).ctp$/i',
            '/Template\/Themed\/([\w\/]+).ctp$/i',
        ],
        'travis' => [
            '/^.travis.yml$/i',
        ],
        'vendor' => [
            '/Vendor\/([\w]+).php$/i',
        ],
        'view' => [
            '/Views?\/([\w]+)(View)?.php$/i',
        ],
        'widget' => [
            '/View\/Widget\/([\w]+)Widget.php$/i',
        ],
    ];

    public function __construct()
    {
        $this->loadModel('Packages');
        $this->loadModel('Tagged');
        $this->loadModel('Tags');
    }

    public function perform(Base $job)
    {
        $packageId = $job->data('package_id');
        if (empty($packageId)) {
            $this->error('No package id specified');

            return false;
        }

        $package = $this->Packages->find()->contain([
            'Categories',
            'Maintainers',
        ])->where(['Packages.id' => $packageId])->first();

        if (empty($package)) {
            $this->error(sprintf('No package found in database for %d', $packageId));

            return false;
        }

        $this->info(sprintf('Updating: %s', $package->id));
        $skipTags = [
            'has:composer',
            'has:elements',
            'has:fixture',
            'has:lib',
            'has:license',
            'has:locale',
            'has:readme',
            'has:travis',
            'has:vendor',
        ];

        if (!$package->isCloned() && !$this->runJobInline(CloneJob::class, 'perform', ['package_id' => $packageId])) {
            $this->error(sprintf('Package is not cloned: %s', $package->id));

            return false;
        }

        list($files, $tags) = $this->classify($package);
        $this->Tagged->deleteAll([
            'foreign_key' => $package->id,
            'model' => 'Package',
        ]);
        foreach ($tags as $tagStr) {
            $tag = $this->Tags->add($tagStr);
            $this->Tagged->addTagToPackage($tag, $package);
        }
        $package->tags = implode(',', $tags);
        if (strlen($package->tags) >= 255) {
            $_tags = [];
            foreach ($tags as $tagStr) {
                if ($this->startsWith('keyword:', $tagStr)) {
                    continue;
                }
                if (in_array($tagStr, $skipTags)) {
                    continue;
                }
                $_tags[] = $tagStr;
            }
            $package->tags = implode(',', $_tags);
        }
        $tags = explode(',', $package->tags);
        sort($tags);
        $package->tags = implode(',', $tags);

        $this->info($package->tags);
        $this->Packages->save($package);
    }

    protected function classify($package)
    {
        $this->info('Classifying');
        $path = $package->cloneDir();

        $files = [];
        $client = new Client();
        $response = $client->get($package->cloneTreesUrl());
        if ($response->getStatusCode() == 200) {
            foreach (json_decode($response->getStringBody(), true)['tree'] as $object) {
                $files[$object['path']] = $this->fetchType($object['path']);
            }
        } else {
            $folder = new Folder($path);
            foreach ($folder->findRecursive() as $file) {
                if ($this->startsWith(sprintf('%s/.git/', $path), $file)) {
                    continue;
                }
                $files[$file] = $this->fetchType(str_replace($path . '/', '', $file));
            }
        }

        $tags = array_values($files);
        $composerData = $this->composerData($package, $path, $tags);
        $tags = $this->cleanupTags($path, $composerData);

        return [
            array_keys($files),
            $tags,
        ];
    }

    protected function cleanupTags($path, $composerData)
    {
        $tags = (array)(new Collection($composerData['tags']))
            ->reject(function ($tag) {
                return !is_string($tag);
            })
            ->reject(function ($tag) {
                return in_array($tag, ['keyword:cake', 'keyword:cakephp']) || preg_match('/\s/', $tag) || $tag === '';
            })
            ->map(function ($tag) {
                return strtolower($tag);
            })
            ->map(function ($tag) {
                return strpos($tag, ':') === false ? sprintf('has:%s', $tag) : $tag;
            })
            ->toArray();

        if ($composerData['version'] !== null) {
            $tags[] = sprintf('version:%s', $composerData['version']);
        }

        $license = $this->license($path, $composerData);
        if (!empty($license)) {
            $tags[] = $license;
        }

        $tags = array_flip($tags);
        if (!$composerData['composer'] && in_array('composer', $tags)) {
            unset($tags['composer']);
        }
        if (in_array('license', $tags)) {
            unset($tags['license']);
        }

        return array_unique(array_values(array_flip($tags)));
    }

    protected function license($path, $composerData)
    {
        if (!empty($composerData['license'])) {
            if ($composerData['license'] === 'lgpl-3.0+') {
                $composerData['license'] = 'lgpl-3.0';
            }

            return sprintf('license:%s', $composerData['license']);
        }

        if (!in_array('license', $composerData['tags'])) {
            return null;
        }

        $licenseFilenames = ['LICENSE', 'LICENSE.txt', 'LICENSE.TXT'];
        $licenses = ['apache', 'mit', 'bsd-3-clause', 'bsd-2-clause'];
        foreach ($licenseFilenames as $filename) {
            $licenseFile = sprintf('%s/%s', $path, $filename);
            if (!file_exists($licenseFile)) {
                continue;
            }

            $file = new File($licenseFile);
            $contents = strtolower($file->read());
            foreach ($licenses as $license) {
                if (strpos($contents, $license) !== false) {
                    return sprintf('license:%s', $license);
                }
            }
        }

        return null;
    }

    protected function version($package, $composerData, $composerContents)
    {
        if (strpos(Hash::get($composerContents, 'name', ''), 'cakephp/') === 0) {
            return '4';
        }

        $version = '1.3';
        // if there is composer data, then assume the minimum version is cake2
        if (Hash::get($composerContents, 'name', '') !== false) {
            $version = '2';
        }
        $cake2Tags = [
            'model', 'view', 'controller',
            'component', 'behavior', 'helper',
            'shell', 'themed', 'log', 'panel', 'config',
            'locale', 'datasource',
            'tests', 'fixture',
        ];
        $cake3Tags = [
            'auth-storage',
            'cell',
            'entity',
            'middleware',
            'route-filter',
            'table',
            'widget',
        ];
        foreach ($cake2Tags as $cake2Tag) {
            if (in_array($cake2Tag, $composerData['tags'])) {
                $version = '2';
            }
        }
        foreach ($cake3Tags as $cake3Tag) {
            if (in_array($cake3Tag, $composerData['tags'])) {
                $version = '3';
            }
        }

        if (in_array($version, ['1.3', '2'])) {
            $cake2Versions = ['2.x', '2.0', '2.1', '2.2', '2.3', '2.4', '2.5', '2.6', '2.7', '2.8', '2.9', '2.10'];
            foreach ($cake2Versions as $cake2Version) {
                if (strpos($package->description, $cake2Version) === false) {
                    continue;
                }
                $version = '2';
                break;
            }

            // this is a minimum, it could still be cake3 or cake4
            if (Hash::get($composerContents, 'type', '') === 'cakephp-plugin') {
                $version = '2';
            }

            $hasInstallerName = strlen(Hash::get($composerContents, 'extra.installer-name', '')) > 0;
            if ($hasInstallerName) {
                $version = '2';
            }

            $dependsOnComposerInstalllers = strlen(Hash::get($composerContents, 'require.composer/installers', '')) > 0;
            $devDependsOnComposerInstalllers = strlen(Hash::get($composerContents, 'require-dev.composer/installers', '')) > 0;
            if ($dependsOnComposerInstalllers || $devDependsOnComposerInstalllers) {
                $version = '2';
            }

            $cake3Identifiers = ['cakephp2', 'cakephp 2', 'cake2', 'cake 2'];
            foreach ($cake3Identifiers as $identifier) {
                if (strpos(strtolower($package->description), $identifier) !== false) {
                    $version = '2';
                }
            }

            $cake3Identifiers = ['cakephp3', 'cakephp 3', 'cake3', 'cake 3'];
            foreach ($cake3Identifiers as $identifier) {
                if (strpos(strtolower($package->description), $identifier) !== false) {
                    $version = '3';
                }
            }

            $cake4Identifiers = ['cakephp4', 'cakephp 4', 'cake4', 'cake 4'];
            foreach ($cake4Identifiers as $identifier) {
                if (strpos(strtolower($package->description), $identifier) !== false) {
                    $version = '4';
                }
            }
        }

        $dependsOnCake = '';
        foreach (['cakephp', 'core', 'utility'] as $packageName) {
            $dependsOnCake = Hash::get($composerContents, sprintf('require.cakephp/%s', $packageName), '');
            if ($dependsOnCake !== '') {
                break;
            }
        }

        $devDependsOnCake = '';
        foreach (['cakephp', 'core', 'utility'] as $packageName) {
            $devDependsOnCake = Hash::get($composerContents, sprintf('require-dev.cakephp/%s', $packageName), '');
            if ($devDependsOnCake !== '') {
                break;
            }
        }

        if (strlen($dependsOnCake) > 0 || strlen($devDependsOnCake) > 0) {
            $version = '2';
            $version3Starters = ['3.', '~3.', '^3.', '>=3.'];
            $version4Starters = ['4.', '~4.', '^4.', '>=4.'];

            foreach ([3 => $version3Starters, 4 => $version4Starters] as $starterVersion => $starters) {
                foreach ($starters as $starter) {
                    if ($this->startsWith($starter, $dependsOnCake)) {
                        $version = $starterVersion;
                    }
                }
                foreach ($starters as $starter) {
                    if ($this->startsWith($starter, $devDependsOnCake)) {
                        $version = $starterVersion;
                    }
                }
            }
        }

        return $version;
    }

    protected function composerContents($composerData, $composerPath)
    {
        if (!in_array('composer', $composerData['tags']) || !file_exists($composerPath)) {
            return [];
        }

        $createPath = false;
        $file = new File($composerPath, $createPath);
        $contents = json_decode($file->read(), true);

        if (empty($contents)) {
            return [];
        }

        return $contents;
    }

    protected function composerData($package, $path, $tags)
    {
        $composerPath = sprintf('%s/composer.json', $path);
        $composerData = [
            'path' => $path,
            'composer' => false,
            'license' => null,
            'tags' => $tags,
            'version' => null,
        ];

        $composerContents = $this->composerContents($composerData, $composerPath);
        if (Hash::get($composerContents, 'description', '') !== '') {
            $package->description = substr(Hash::get($composerContents, 'description', ''), 0, 250);
        }
        $version = $this->version($package, $composerData, $composerContents);
        $composerData['version'] = $version;

        if (empty($composerContents)) {
            return $composerData;
        }

        $license = Hash::get($composerContents, 'license');
        if (is_array($license)) {
            $license = $license[0];
        }
        $composerData['license'] = strtolower($license);
        $composerData['version'] = $version;

        $keywords = (new Collection(Hash::get($composerContents, 'keywords', [])))
            ->map(function ($tag) {
                return sprintf('keyword:%s', $tag);
            })
            ->toArray();

        $composerData['tags'] = array_merge($keywords, $composerData['tags']);

        return $composerData;
    }

    protected function fetchType($filename)
    {
        $type = null;
        foreach ($this->_fileRegex as $_type => $regexes) {
            if (empty($regexes)) {
                continue;
            }

            foreach ($regexes as $regex) {
                if (preg_match($regex, $filename)) {
                    $type = $_type;
                    break;
                }
            }
        }

        return $type;
    }

    /**
     * Returns if value starts with a value
     *
     * @param string $string The value to search for
     * @param string $line   The line to test
     *
     * @return bool Returns if the line starts with value
     */
    protected function startsWith($string, $line)
    {
        return $string === "" || strrpos($line, $string, -strlen($line)) !== false;
    }
}
