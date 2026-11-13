<?php
declare(strict_types=1);

namespace App\Test\TestCase;

use App\Application;
use App\Command\SyncPackagesCommand;
use App\Event\AfterGithubIdentify;
use App\Island\NamespaceProfileIsland;
use App\Island\PublishersIsland;
use App\Model\Table\PackagesTable;
use App\Service\AwesomeListImporter;
use App\Service\GithubProfileService;
use App\Service\PackagistImporter;
use Cake\Core\Configure;
use Cake\Core\Container;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;
use Interact\Container\Services;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Autowiring preserves configured dependencies and fresh island state.
 */
class ApplicationServicesTest extends TestCase
{
    /**
     * Resolve the import command's complete graph without making remote requests.
     */
    public function testAutowiringResolvesApplicationServices(): void
    {
        $container = new Container();
        new Application(dirname(__DIR__, 2) . '/config')->services($container);
        $this->assertSame($this->fetchTable(PackagesTable::class), $container->get(PackagesTable::class));
        $this->assertInstanceOf(PackagistImporter::class, $container->get(PackagistImporter::class));
        $this->assertInstanceOf(AwesomeListImporter::class, $container->get(AwesomeListImporter::class));
        $this->assertInstanceOf(SyncPackagesCommand::class, $container->get(SyncPackagesCommand::class));
        $this->assertInstanceOf(AfterGithubIdentify::class, $container->get(AfterGithubIdentify::class));
    }

    /**
     * Autowired services still receive explicit HTTP overrides and scalar configuration.
     */
    public function testAutowiringRespectsConfiguredDependencies(): void
    {
        Configure::write('GitHub.apiToken', 'application-test-token');
        $container = new Container();
        new Application(dirname(__DIR__, 2) . '/config')->services($container);
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('get')->with(
            'https://api.github.com/users/autowiring-example',
            [],
            $this->callback(static fn(array $options): bool => $options['headers']['Authorization'] === 'Bearer application-test-token'),
        )->willReturn(new Response(
            ['HTTP/1.1 200 OK', 'Content-Type: application/json'],
            '{"login":"autowiring-example","bio":"Configured dependencies"}',
        ));
        $container->addShared(Client::class, $client, true);
        $profile = $container->get(GithubProfileService::class)->profile('autowiring-example');
        $this->assertSame('Configured dependencies', $profile['bio']);
    }

    /**
     * Production reuses autowired services while development disables the cache.
     */
    #[DataProvider('debugModes')]
    public function testAutowiringCachesServicesInProduction(bool $debug): void
    {
        Configure::write('debug', $debug);
        $container = new Container();
        new Application(dirname(__DIR__, 2) . '/config')->services($container);
        $profile = $container->get(GithubProfileService::class);
        $otherProfile = $container->get(GithubProfileService::class);
        if ($debug) {
            $this->assertNotSame($profile, $otherProfile);
        } else {
            $this->assertSame($profile, $otherProfile);
        }
    }

    /**
     * Interact must never reuse mutable state between island constructions.
     */
    #[DataProvider('debugModes')]
    public function testIslandsRemainFresh(bool $debug): void
    {
        Configure::write('debug', $debug);
        $container = new Container();
        new Application(dirname(__DIR__, 2) . '/config')->services($container);
        $publishers = Services::fresh($container, PublishersIsland::class);
        $publishers->search = 'vendor';
        $otherPublishers = Services::fresh($container, PublishersIsland::class);
        $this->assertNotSame($publishers, $otherPublishers);
        $this->assertSame('', $otherPublishers->search);
        $profile = Services::fresh($container, NamespaceProfileIsland::class);
        $profile->namespace = 'vendor';
        $otherProfile = Services::fresh($container, NamespaceProfileIsland::class);
        $this->assertNotSame($profile, $otherProfile);
        $this->assertSame('', $otherProfile->namespace);
    }

    /**
     * @return array<string, array{bool}>
     */
    public static function debugModes(): array
    {
        return [
            'development' => [true],
            'production' => [false],
        ];
    }
}
