<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\PackagesTable;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Hosts the public catalogue; islands own subsequent interactions.
 */
class PackagesController extends AppController
{
    /**
     * Allow anonymous catalogue visits.
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticated(['index', 'view', 'byNamespace']);
    }

    /**
     * Render the catalogue island in the normal application layout.
     */
    public function index(): void
    {
    }

    /**
     * Host a scoped catalogue with totals that do not change while filtering.
     */
    public function byNamespace(string $namespace): ?Response
    {
        $canonicalNamespace = strtolower($namespace);
        $packages = $this->fetchTable(PackagesTable::class);
        $stats = $packages->namespaceStats($canonicalNamespace);
        if ($stats['plugins'] === 0) {
            throw new NotFoundException('Namespace not found.');
        }
        if ($namespace !== $canonicalNamespace) {
            return $this->redirect([
                '_name' => 'plugin:namespace',
                'namespace' => $canonicalNamespace,
                '?' => $this->getRequest()->getQueryParams(),
            ], 301);
        }
        $githubUrl = $packages->namespaceGithubUrl($namespace);
        $backUrl = $this->getRequest()->referer();
        if ($backUrl === null || parse_url($backUrl, PHP_URL_PATH) === $this->getRequest()->getPath()) {
            $backUrl = ['_name' => 'plugin:index'];
        }
        $this->set(compact('namespace', 'stats', 'githubUrl', 'backUrl'));

        return null;
    }

    /**
     * Provide a normal detail URL for direct links and browsers without JavaScript.
     */
    public function view(string $namespace, string $name): void
    {
        $package = $this->fetchTable(PackagesTable::class)->find()
            ->where([
                'Packages.package' => strtolower($namespace . '/' . $name),
            ])
            ->contain(['Tags', 'Categories'])
            ->firstOrFail();
        $this->set(compact('package'));
    }
}
