<?php

/**
 * This file is part of the Speedwork framework.
 *
 * @link http://github.com/speedwork
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Speedwork\View;

use Gigablah\Silex\View\Engine\AuraEngine;
use Gigablah\Silex\View\Engine\DelegatingEngine;
use Gigablah\Silex\View\Engine\LazyEngineResolver;
use Gigablah\Silex\View\Engine\MustacheEngine;
use Gigablah\Silex\View\Engine\PhpEngine;
use Gigablah\Silex\View\Engine\PlatesEngine;
use Gigablah\Silex\View\Engine\SmartyEngine;
use Gigablah\Silex\View\Engine\StringEngine;
use Gigablah\Silex\View\Engine\TwigEngine;
use Gigablah\Silex\View\EventListener\ArrayToViewListener;
use Gigablah\Silex\View\Logger\ViewLogger;
use Gigablah\Silex\View\Template\TemplateResolver;
use Speedwork\Core\Container;
use Speedwork\Core\ServiceProvider;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * ViewServiceProvider registers the view factory for wrapping responses with views.
 *
 * @author sankar <sankar.suda@gmail.com>
 */
class ViewServiceProvider implements ServiceProvider
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

        $app['view.default_engine'] = 'html';

        $app['view.listener_priority'] = -20;

        $app['view'] = function ($app) {
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
            return new AuraEngine($app['aura.template']);
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

    public function boot(Application $app)
    {
        $app['dispatcher']->addListener(KernelEvents::VIEW, [$app['view.array_to_view_listener'], 'onKernelView'], $app['view.listener_priority']);
    }
}
