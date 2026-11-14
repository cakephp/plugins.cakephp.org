<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use ADmad\SocialAuth\Middleware\SocialAuthMiddleware;
use App\Event\AfterGithubIdentify;
use App\Http\RateLimit\AtomicFixedWindowRateLimiter;
use App\Middleware\ThemeCookieMiddleware;
use App\Model\Table\PackagesTable;
use App\Model\Table\UsersTable;
use App\Policy\RequestPolicy;
use App\Service\GithubApiService;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Exception\MissingIdentityException;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use Authorization\Policy\MapResolver;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Datasource\RepositoryInterface;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Client;
use Cake\Http\Client\ClientEvent;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\Middleware\RateLimitMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Http\ServerRequest;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use League\Container\ReflectionContainer;
use Packagist\Api\Client as PackagistClient;
use Psr\Http\Message\ServerRequestInterface;
use SocialConnect\OAuth2\Provider\GitHub;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI !== 'cli') {
            FactoryLocator::add(
                'Table',
                new TableLocator()->allowFallbackClass(false),
            );
        }
    }

    /**
     * Resolve authentication event listeners through CakePHP's container.
     *
     * @return list<class-string<\Cake\Event\EventListenerInterface>>
     */
    public function eventListeners(): array
    {
        return [AfterGithubIdentify::class];
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $csrf = new CsrfProtectionMiddleware([
            'httponly' => true,
        ]);

        // Catch any exceptions in the lower layers and make an error page/response.
        $middlewareQueue->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this));

        // Production serves symlinked plugin assets directly through the web server.
        if (Configure::read('debug')) {
            $middlewareQueue->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]));
        }

        $middlewareQueue
            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/5/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            // Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/5/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
            ->add($csrf)

            ->add(new ThemeCookieMiddleware())

            ->add(new SocialAuthMiddleware([
                'finder' => 'active',
                'loginUrl' => '/',
                'userId' => true,
                'sessionKey' => 'AuthId',
                // SocialConnect Auth service's providers config. https://github.com/SocialConnect/auth/blob/master/README.md
                'serviceConfig' => [
                    'provider' => [
                        GitHub::NAME => [
                            'applicationId' => Configure::read('GitHub.applicationId', ''),
                            'applicationSecret' => Configure::read('GitHub.applicationSecret', ''),
                            'scope' => [
                                'user:email',
                                'read:org',
                            ],
                            'options' => [
                                'identity.fields' => [
                                    'email',
                                ],
                                'fetch_emails' => true,
                            ],
                        ],
                    ],
                ],
            ]))

            ->add(new AuthenticationMiddleware($this))
            ->add(new AuthorizationMiddleware($this))
            ->add(new RequestAuthorizationMiddleware([
                'unauthorizedHandler' => [
                    'className' => 'Authorization.Redirect',
                    'exceptions' => [MissingIdentityException::class],
                    'url' => '/',
                    'queryParam' => 'redirect',
                ],
            ]));

        $requestProtection = (array)Configure::read('PublicRequestProtection', []);
        if ($requestProtection['rateLimitEnabled'] ?? false) {
            $middlewareQueue->insertBefore(
                RoutingMiddleware::class,
                new RateLimitMiddleware([
                    'cache' => 'rate_limit',
                    'headers' => true,
                    'strategyClass' => AtomicFixedWindowRateLimiter::class,
                    'limit' => $requestProtection['limit'] ?? 6000,
                    'window' => $requestProtection['window'] ?? 60,
                    'ipHeader' => 'remote_addr',
                    'skipCheck' => static fn(ServerRequestInterface $request): bool => $request->getMethod() !== 'POST'
                        || rtrim($request->getUri()->getPath(), '/') !== '/_interact/update',
                ]),
            );
        }

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/5/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
        // Interact constructs fresh island roots separately from cached service dependencies.
        $container->delegate(new ReflectionContainer(cacheResolutions: !Configure::read('debug')));
        $container->addShared(PackagesTable::class, static fn(): RepositoryInterface => FactoryLocator::get('Table')->get(PackagesTable::class));
        $container->addShared(Client::class);
        $container->addShared(GithubApiService::class)->addArguments([
            Client::class,
            (string)Configure::read('GitHub.apiToken', ''),
        ]);
        $container->addShared('packagist.http', static function (): Client {
            $client = new Client();
            $userAgent = (string)env('PACKAGIST_USER_AGENT', 'plugins.cakephp.org (https://plugins.cakephp.org; mailto=security@cakephp.org)');
            // Packagist's PSR-18 transport bypasses Cake's default headers and protocol.
            $client->getEventManager()->on('HttpClient.beforeSend', static function (ClientEvent $event) use ($userAgent): void {
                $event->setRequest($event->getRequest()->withHeader('User-Agent', $userAgent)->withProtocolVersion('2'));
            });

            return $client;
        });
        $container->addShared(PackagistClient::class)->addArgument('packagist.http');
    }

    /**
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $service = new AuthenticationService();

        // Define where users should be redirected to when they are not authenticated
        $service->setConfig([
            'unauthenticatedRedirect' => [
                'prefix' => false,
                'plugin' => false,
                'controller' => 'Packages',
                'action' => 'index',
            ],
            'queryParam' => 'redirect',
        ]);

        // Resolve current access on each request; legacy full-identity sessions are ignored.
        $service->loadAuthenticator('Authentication.PrimaryKeySession', [
            'sessionKey' => 'AuthId',
            'identifier' => [
                'className' => 'Authentication.Token',
                'tokenField' => 'id',
                'dataField' => 'key',
                'resolver' => [
                    'className' => 'Authentication.Orm',
                    'userModel' => UsersTable::class,
                    'finder' => 'active',
                ],
            ],
        ]);

        return $service;
    }

    /**
     * Share request policies between admin routes and island updates.
     */
    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $resolver = new MapResolver();
        $resolver->map(ServerRequest::class, RequestPolicy::class);

        return new AuthorizationService($resolver);
    }
}
