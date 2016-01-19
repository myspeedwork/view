<?php

namespace Speedwork\View;

use Speedwork\Container\Container;
use Speedwork\Container\ServiceProvider;
use Speedwork\View\Engine\AuraEngine;
use Speedwork\View\Engine\DelegatingEngine;
use Speedwork\View\Engine\LazyEngineResolver;
use Speedwork\View\Engine\MustacheEngine;
use Speedwork\View\Engine\PhpEngine;
use Speedwork\View\Engine\PlatesEngine;
use Speedwork\View\Engine\SmartyEngine;
use Speedwork\View\Engine\StringEngine;
use Speedwork\View\Engine\TwigEngine;
use Speedwork\View\EventListener\ArrayToViewListener;
use Speedwork\View\Logger\ViewLogger;
use Speedwork\View\Template\TemplateResolver;

/**
 * ViewServiceProvider registers the view factory for wrapping responses with views.
 *
 * @author Chris Heng <bigblah@gmail.com>
 */
class ViewServiceProvider extends ServiceProvider
{
    public function register(Container $app)
    {
        $app['view.globals'] = [];

        $app['view.engines'] = [
            'mustache' => 'view.engine.mustache',
            'ms'       => 'view.engine.mustache',
            'tpl'      => 'view.engine.smarty',
            'twig'     => 'view.engine.twig',
            'php'      => 'view.engine.php',
            'html'     => 'view.engine.string',
        ];

        $app['view.default_engine'] = 'tpl';

        $app['view.listener_priority'] = -20;

        $app['engine'] = function ($app) {
            $factory = $app['debug'] && $app['logger'] ? $app['view.factory.debug'] : $app['view.factory'];
            $factory->getSharedBag()->add($app['view.globals']);

            return $factory;
        };

        $app['view.factory'] = function ($app) {
            return new ViewFactory($app['view.engine']);
        };

        $app['view.factory.debug'] = function ($app) {
            return new LoggableViewFactory($app['view.engine'], $app['view.logger']);
        };

        $app['view.engine'] = function ($app) {
            return new DelegatingEngine($app['view.engine_resolver']);
        };

        $app['view.engine.string'] = function ($app) {
            return new StringEngine();
        };

        $app['view.engine.php'] = function ($app) {
            return new PhpEngine();
        };

        $app['view.engine.aura'] = function ($app) {
            return new AuraEngine($app['aura']);
        };

        $app['view.engine.plates'] = function ($app) {
            return new PlatesEngine($app['plates']);
        };

        $app['view.engine.mustache'] = function ($app) {
            return new MustacheEngine($app['mustache']);
        };

        $app['view.engine.smarty'] = function ($app) {
            return new SmartyEngine($app['smarty']);
        };

        $app['view.engine.twig'] = function ($app) {
            return new TwigEngine($app['twig']);
        };

        $app['view.engine_resolver'] = function ($app) {
            return new LazyEngineResolver($app, $app['view.engines'], $app['view.default_engine']);
        };

        $app['view.template_resolver'] = function ($app) {
            return new TemplateResolver($app['view.default_engine']);
        };

        $app['view.array_to_view_listener'] = function ($app) {
            return new ArrayToViewListener($app['view'], $app['view.template_resolver']);
        };

        $app['view.logger'] = function ($app) {
            $stopwatch = isset($app['debug.stopwatch']) ? $app['debug.stopwatch'] : null;

            return new ViewLogger($app['logger'], $stopwatch);
        };
    }
}
