<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\PackagesTable;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Routing\Attribute\Extensions;
use Cake\Routing\Attribute\Route;

/**
 * Hosts the public catalogue; islands own subsequent interactions.
 */
class PackagesController extends AppController
{
    private const string PACKAGE_SEGMENT = '[A-Za-z0-9][A-Za-z0-9_.-]*';

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
    #[Route('/', name: 'plugin:index', methods: ['GET'])]
    #[Route('/packages', methods: ['GET'])]
    #[Extensions(['json'])]
    public function index(): void
    {
    }

    /**
     * Host a scoped catalogue with totals that do not change while filtering.
     */
    #[Route('/plugins/{namespace}', name: 'plugin:namespace', methods: ['GET'], patterns: ['namespace' => self::PACKAGE_SEGMENT])]
    public function byNamespace(string $namespace): ?Response
    {
        $canonicalNamespace = strtolower($namespace);
        $packages = $this->fetchTable(PackagesTable::class);
        $stats = $packages->unhydratedFind('namespaceStats', namespace: $canonicalNamespace)->firstOrFail();
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
        $githubUrl = $packages->unhydratedFind('namespaceGithubUrl', namespace: $namespace)->all()->first()['url'] ?? null;
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
    #[Route('/plugins/{namespace}/{name}', name: 'plugin:view', methods: ['GET'], patterns: ['namespace' => self::PACKAGE_SEGMENT, 'name' => self::PACKAGE_SEGMENT])]
    public function view(string $namespace, string $name): void
    {
        $package = $this->fetchTable(PackagesTable::class)->find('publicDetails')
            ->where([
                'Packages.package' => strtolower($namespace . '/' . $name),
            ])
            ->firstOrFail();
        $this->set(compact('package'));
    }
}
