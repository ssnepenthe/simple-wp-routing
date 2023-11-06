<?php

namespace TwrTestPlugin;

use RuntimeException;
use ToyWpRouting\DefaultInvocationStrategy;
use ToyWpRouting\Exception\MethodNotAllowedHttpException;
use ToyWpRouting\Exception\NotFoundHttpException;
use ToyWpRouting\NullCallableResolver;
use ToyWpRouting\Orchestrator;
use ToyWpRouting\Responder\JsonResponder;
use ToyWpRouting\Responder\Partial\HeadersPartial;
use ToyWpRouting\Responder\QueryResponder;
use ToyWpRouting\Responder\RedirectResponder;
use ToyWpRouting\Responder\TemplateResponder;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteCollectionCache;
use ToyWpRouting\Router;

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
            $rewrites = $this->createRewrites();
        }

        (new Orchestrator($rewrites, new DefaultInvocationStrategy(), new NullCallableResolver()))->initialize();
    }

    public function createCache(): RewriteCollectionCache
    {
        return new RewriteCollectionCache(__DIR__ . '/var/cache', $this->getCacheFileName());
    }

    abstract public function createRewrites(): RewriteCollection;
    abstract protected function getCacheFileName(): string;
}

class HttpMethodsGroup extends TestGroup
{
    public function createRewrites(): RewriteCollection
    {
        $router = new Router();
        $router->setPrefix('httpmethod_');

        $router->any('http-method/any', function () {});
        $router->delete('http-method/delete', function () {});
        $router->get('http-method/get', function () {});
        $router->options('http-method/options', function () {});
        $router->patch('http-method/patch', function () {});
        $router->post('http-method/post', function () {});
        $router->put('http-method/put', function () {});

        return $router->rewriteCollection();
    }

    protected function getCacheFileName(): string
    {
        return 'http-method-cache.php';
    }
}

class OrchestratorGroup extends TestGroup
{
    public function createRewrites(): RewriteCollection
    {
        $router = new Router();
        $router->setPrefix('orchestrator_');

        $router->get('orchestrator/active/{activeVar}', function () {});

        $router->get('orchestrator/inactive/{inactiveVar}', function () {
            add_action('twr_test_data', function () {
                echo '<span class="twr-orchestrator-inactive"></span>';
            });
        })->setIsActiveCallback('__return_false');

        $router->get('orchestrator/responder', function () {
            return new JsonResponder('hello from the orchestrator responder route');
        });

        $router->get('orchestrator/hierarchical-responder', function () {
            $responder = new JsonResponder('hello from the orchestrator hierarchical responder route');

            // We return the headers partial - expectation is that orchestrator traverses back up to the JsonResponder.
            return $responder->getPartialSet()->get(HeadersPartial::class);
        });

        return $router->rewriteCollection();
    }

    protected function getCacheFileName(): string
    {
        return 'orchestrator-cache.php';
    }
}

class ResponderGroup extends TestGroup
{
    public function createRewrites(): RewriteCollection
    {
        $router = new Router();
        $router->setPrefix('responders_');

        $router->get('responders/http-exception/not-found', function () {
            // @todo custom additional headers
            throw new NotFoundHttpException();
        });

        $router->get('responders/http-exception/method-not-allowed', function () {
            // @todo custom additional headers, custom theme template (body class and title), ensure query flags are reset
            throw new MethodNotAllowedHttpException(['POST', 'PUT']);
        });

        $router->get('responders/json', function () {
            // @todo custom status codes, error vs success status codes, json options, non-enveloped response, custom additional headers
            return new JsonResponder('hello from the json responder route');
        });

        $router->get('responders/query', function () {
            // @todo overwrite query variables
            add_action('twr_test_data', function () {
                global $wp;

                printf('<span class="query-responder-dump">%s</span>', json_encode($wp->query_vars));
            });

            return new QueryResponder(['custom-query-variable' => 'from-the-query-route']);
        });

        $router->get('responders/redirect', function () {
            // @todo custom status code, custom redirect-by, external (unsafe) redirect both allowed and not, custom headers
            return new RedirectResponder('/responders/query/');
        });

        $router->get('responders/template', function () {
            // @todo body class, document title, enqueue assets, dequeue assets, custom headers, query vars, query flags
            return new TemplateResponder(__DIR__ . '/templates/hello-world.php');
        });

        return $router->rewriteCollection();
    }

    protected function getCacheFileName(): string
    {
        return 'responder-cache.php';
    }
}
