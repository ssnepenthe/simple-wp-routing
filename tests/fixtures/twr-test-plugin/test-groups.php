<?php

namespace TwrTestPlugin;

use LogicException;
use SimpleWpRouting\Exception\MethodNotAllowedHttpException;
use SimpleWpRouting\Exception\NotFoundHttpException;
use SimpleWpRouting\Responder\JsonResponder;
use SimpleWpRouting\Responder\Partial\HeadersPartial;
use SimpleWpRouting\Responder\QueryResponder;
use SimpleWpRouting\Responder\RedirectResponder;
use SimpleWpRouting\Responder\TemplateResponder;
use SimpleWpRouting\Router;

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

    public function refreshCache(): void
    {
        $router = new Router();
        $router->setPrefix($this->getPrefix());
        $router->enableCache($this->getCacheDirectory(), $this->getCacheFileName());
        $router->getRewriteCollectionCache()->delete();
        $this->registerRoutes($router);
        $router->getRewriteCollectionCache()->put($router->getRewriteCollection());
    }

    public function initialize(): void
    {
        $router = new Router();
        $router->setPrefix($this->getPrefix());

        if (filter_var($_REQUEST['twr_enable_cache'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $router->enableCache($this->getCacheDirectory(), $this->getCacheFileName());

            if (! $router->getRewriteCollectionCache()->exists()) {
                throw new LogicException('Test plugin cache does not exist - run the bin/regenerate-test-fixtures.php script');
            }
        }

        $router->initialize(fn ($r) => $this->registerRoutes($r));
    }

    protected function getCacheDirectory(): string
    {
        return __DIR__ . '/var/cache';
    }

    abstract protected function getCacheFileName(): string;
    abstract protected function getPrefix(): string;
    abstract protected function registerRoutes(Router $router): void;
}

class HttpMethodsGroup extends TestGroup
{
    protected function getCacheFileName(): string
    {
        return 'http-method-cache.php';
    }

    protected function getPrefix(): string
    {
        return 'httpmethod_';
    }

    protected function registerRoutes(Router $router): void
    {
        $router->any('http-method/any', function () {});
        $router->delete('http-method/delete', function () {});
        $router->get('http-method/get', function () {});
        $router->options('http-method/options', function () {});
        $router->patch('http-method/patch', function () {});
        $router->post('http-method/post', function () {});
        $router->put('http-method/put', function () {});
    }
}

class OrchestratorGroup extends TestGroup
{
    protected function getCacheFileName(): string
    {
        return 'orchestrator-cache.php';
    }

    protected function getPrefix(): string
    {
        return 'orchestrator_';
    }

    protected function registerRoutes(Router $router): void
    {
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
    }
}

class ResponderGroup extends TestGroup
{
    protected function getCacheFileName(): string
    {
        return 'responder-cache.php';
    }

    protected function getPrefix(): string
    {
        return 'responders_';
    }

    protected function registerRoutes(Router $router): void
    {
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
    }
}
