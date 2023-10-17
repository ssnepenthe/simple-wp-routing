<?php

namespace TwrTestPlugin;

use RuntimeException;
use ToyWpRouting\Exception\MethodNotAllowedHttpException;
use ToyWpRouting\Exception\NotFoundHttpException;
use ToyWpRouting\Orchestrator;
use ToyWpRouting\Responder\JsonResponder;
use ToyWpRouting\Responder\Partial\HeadersPartial;
use ToyWpRouting\Responder\QueryResponder;
use ToyWpRouting\Responder\RedirectResponder;
use ToyWpRouting\Responder\TemplateResponder;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteCollectionCache;
use ToyWpRouting\RouteCollection;
use ToyWpRouting\RouteConverter;

abstract class TestGroup
{
    /**
     * @return TestGroup[]
     */
    public static function createTestGroups(): array
    {
        return [
            new HttpMethodsGroup(),
            new OrchestratorGroup(),
            new ResponderGroup(),
        ];
    }

    public function initialize(): void
    {
        if (filter_var($_REQUEST['twr_enable_cache'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $cache = $this->createCache();

            if (! $cache->exists()) {
                throw new RuntimeException();
            }

            $rewrites = $cache->get();
        } else {
            $converter = new RouteConverter();
            $rewrites = $converter->convertCollection($this->createRoutes());
        }

        (new Orchestrator($rewrites))->initialize();
    }

    public function createCache(): RewriteCollectionCache
    {
        return new RewriteCollectionCache(__DIR__ . '/var/cache', $this->getCacheFileName());
    }

    public function createRewrites(): RewriteCollection
    {
        return (new RouteConverter())->convertCollection($this->createRoutes());
    }

    abstract protected function createRoutes(): RouteCollection;
    abstract protected function getCacheFileName(): string;
}

class HttpMethodsGroup extends TestGroup
{
    protected function createRoutes(): RouteCollection
    {
        $routes = new RouteCollection('httpmethod_');

        $routes->any('http-method/any', function () {});
        $routes->delete('http-method/delete', function () {});
        $routes->get('http-method/get', function () {});
        $routes->options('http-method/options', function () {});
        $routes->patch('http-method/patch', function () {});
        $routes->post('http-method/post', function () {});
        $routes->put('http-method/put', function () {});

        return $routes;
    }

    protected function getCacheFileName(): string
    {
        return 'http-method-cache.php';
    }
}

class OrchestratorGroup extends TestGroup
{
    protected function createRoutes(): RouteCollection
    {
        $routes = new RouteCollection('orchestrator_');

        $routes->get('orchestrator/active/{activeVar}', function () {});

        $routes->get('orchestrator/inactive/{inactiveVar}', function () {
            add_action('twr_test_data', function () {
                echo '<span class="twr-orchestrator-inactive"></span>';
            });
        })->when('__return_false');

        $routes->get('orchestrator/responder', function () {
            return new JsonResponder('hello from the orchestrator responder route');
        });

        $routes->get('orchestrator/hierarchical-responder', function () {
            $responder = new JsonResponder('hello from the orchestrator hierarchical responder route');

            // We return the headers partial - expectation is that orchestrator traverses back up to the JsonResponder.
            return $responder->getPartialSet()->get(HeadersPartial::class);
        });

        return $routes;
    }

    protected function getCacheFileName(): string
    {
        return 'orchestrator-cache.php';
    }
}

class ResponderGroup extends TestGroup
{
    protected function createRoutes(): RouteCollection
    {
        $routes = new RouteCollection('responders_');

        $routes->get('responders/http-exception/not-found', function () {
            // @todo custom additional headers
            throw new NotFoundHttpException();
        });

        $routes->get('responders/http-exception/method-not-allowed', function () {
            // @todo custom additional headers, custom theme template (body class and title), ensure query flags are reset
            throw new MethodNotAllowedHttpException(['POST', 'PUT']);
        });

        $routes->get('responders/json', function () {
            // @todo custom status codes, error vs success status codes, json options, non-enveloped response, custom additional headers
            return new JsonResponder('hello from the json responder route');
        });

        $routes->get('responders/query', function () {
            // @todo overwrite query variables
            add_action('twr_test_data', function () {
                global $wp;

                printf('<span class="query-responder-dump">%s</span>', json_encode($wp->query_vars));
            });

            return new QueryResponder(['custom-query-variable' => 'from-the-query-route']);
        });

        $routes->get('responders/redirect', function () {
            // @todo custom status code, custom redirect-by, external (unsafe) redirect both allowed and not, custom headers
            return new RedirectResponder('/responders/query/');
        });

        $routes->get('responders/template', function () {
            // @todo body class, document title, enqueue assets, dequeue assets, custom headers, query vars, query flags
            return new TemplateResponder(__DIR__ . '/templates/hello-world.php');
        });

        return $routes;
    }

    protected function getCacheFileName(): string
    {
        return 'responder-cache.php';
    }
}
