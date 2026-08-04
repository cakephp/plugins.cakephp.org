<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Cake\Http\Exception\BadRequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Reject malformed and high-cost public package search requests.
 */
class PublicRequestGuardMiddleware implements MiddlewareInterface
{
    private const INDEX_PARAMETERS = [
        'search',
        'cakephp_slugs',
        'php_slugs',
        'sort',
        'direction',
        'page',
    ];

    private const AUTOCOMPLETE_PARAMETERS = ['q'];

    private const SORT_FIELDS = ['downloads', 'stars', 'latest_stable_release_date'];

    private const DIRECTIONS = ['asc', 'desc'];

    private const MAX_SEARCH_LENGTH = 100;

    private const MAX_PAGE = 100;

    private readonly int $maxFilterValues;

    /**
     * @param array{maxFilterValues?: int} $config Middleware configuration.
     */
    public function __construct(array $config = [])
    {
        $this->maxFilterValues = max(1, (int)($config['maxFilterValues'] ?? 3));
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $handler->handle($request);
        }

        $path = $this->endpointPath($request);
        if ($path === '' || $path === '/packages') {
            $request = $this->validateIndexRequest($request);
        } elseif ($path === '/autocomplete') {
            $this->validateAutocompleteRequest($request);
        }

        return $handler->handle($request);
    }

    /**
     * Validate and normalize package listing parameters.
     */
    private function validateIndexRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $query = $request->getQueryParams();
        $this->assertAllowedParameters($query, self::INDEX_PARAMETERS);

        if (isset($query['search'])) {
            $this->assertString($query['search'], 'search');
            if (mb_strlen($query['search']) > self::MAX_SEARCH_LENGTH) {
                throw new BadRequestException('Search query is too long.');
            }
        }

        foreach (['cakephp_slugs' => 'cakephp', 'php_slugs' => 'php'] as $key => $prefix) {
            if (!isset($query[$key])) {
                continue;
            }
            $query[$key] = $this->normaliseSlugValues($query[$key], $prefix);
        }

        if (isset($query['sort'])) {
            $this->assertString($query['sort'], 'sort');
            if (!in_array($query['sort'], self::SORT_FIELDS, true)) {
                throw new BadRequestException('Unsupported sort field.');
            }
        }
        if (isset($query['direction'])) {
            $this->assertString($query['direction'], 'direction');
            if (!in_array($query['direction'], self::DIRECTIONS, true)) {
                throw new BadRequestException('Unsupported sort direction.');
            }
        }
        if (isset($query['page'])) {
            $this->assertStringOrInteger($query['page'], 'page');
            if (!preg_match('/^[1-9][0-9]*$/', (string)$query['page']) || (int)$query['page'] > self::MAX_PAGE) {
                throw new BadRequestException('Invalid page number.');
            }
        }

        return $request->withQueryParams($query);
    }

    /**
     * Validate package autocomplete parameters.
     */
    private function validateAutocompleteRequest(ServerRequestInterface $request): void
    {
        $query = $request->getQueryParams();
        $this->assertAllowedParameters($query, self::AUTOCOMPLETE_PARAMETERS);

        if (isset($query['q'])) {
            $this->assertString($query['q'], 'q');
            if (mb_strlen($query['q']) > self::MAX_SEARCH_LENGTH) {
                throw new BadRequestException('Search query is too long.');
            }
        }
    }

    /**
     * @param array<string, mixed> $query Query parameters.
     * @param list<string> $allowed Allowed parameter names.
     */
    private function assertAllowedParameters(array $query, array $allowed): void
    {
        foreach (array_keys($query) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new BadRequestException('Unsupported query parameter.');
            }
        }
    }

    /**
     * @return list<string>
     */
    private function normaliseSlugValues(mixed $value, string $prefix): array
    {
        $values = is_array($value) ? array_values($value) : [$value];
        if (count($values) > $this->maxFilterValues) {
            throw new BadRequestException('Too many filter values.');
        }

        $normalised = [];
        foreach ($values as $slug) {
            $this->assertString($slug, 'filter');
            if (!preg_match('/^' . preg_quote($prefix, '/') . '-[1-9][0-9]*-[0-9]+$/', $slug)) {
                throw new BadRequestException('Invalid filter value.');
            }
            $normalised[$slug] = $slug;
        }

        return array_values($normalised);
    }

    /**
     * Require a query parameter to be a string.
     */
    private function assertString(mixed $value, string $parameter): void
    {
        if (!is_string($value)) {
            throw new BadRequestException(sprintf('Invalid %s parameter.', $parameter));
        }
    }

    /**
     * Require a query parameter to be a scalar page number.
     */
    private function assertStringOrInteger(mixed $value, string $parameter): void
    {
        if (!is_string($value) && !is_int($value)) {
            throw new BadRequestException(sprintf('Invalid %s parameter.', $parameter));
        }
    }

    /**
     * Return the route path without an optional JSON extension.
     */
    private function endpointPath(ServerRequestInterface $request): string
    {
        $path = rtrim($request->getUri()->getPath(), '/');

        return str_ends_with($path, '.json') ? substr($path, 0, -5) : $path;
    }
}
