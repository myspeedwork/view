<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\View;

use Smarty;
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
use Speedwork\View\Logger\ViewLogger;
use Speedwork\View\Parser\Parser;
use Speedwork\View\Parser\SmartyParser;
use Speedwork\View\Parser\TwigParser;
use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * ViewServiceProvider registers the view factory for wrapping responses with views.
 *
 * @author sankar <sankar.suda@gmail.com>>
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

        $app['view.engine'] = function ($app) {
            $factory = $app['debug'] && $app['logger'] ? $app['view.factory.debug'] : $app['view.factory'];
            $factory->getSharedBag()->add($app['view.globals']);

            return $factory;
        };

        $app['engine'] = function ($app) {
            return $app['view.engine'];
        };

        $app['view.factory'] = function ($app) {
            return new ViewFactory($app['view.engine_delegate']);
        };

        $app['view.factory.debug'] = function ($app) {
            return new LoggableViewFactory($app['view.engine_delegate'], $app['view.logger']);
        };

        $app['view.engine_delegate'] = function ($app) {
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

        $app['view.logger'] = function ($app) {
            $stopwatch = isset($app['debug.stopwatch']) ? $app['debug.stopwatch'] : null;

            return new ViewLogger($app['logger'], $stopwatch);
        };

        $app['view.finder'] = function ($app) {
            $paths = $app['config']['view.paths'];

            return new FileViewFinder($app['files'], $paths);
        };

        $app['view.parser'] = function ($app) {
            return new Parser($app);
        };

        $this->registerSmartyEngine($app);
        $this->registerTwigEngine($app);
    }

    protected function registerSmartyEngine(Container $di)
    {
        $di['smarty'] = function ($app) {
            $smarty = new Smarty();
            $smarty->setTemplateDir(STORAGE);
            $smarty->setCompileDir(STORAGE.'views'.DS);
            $smarty->setCacheDir(CACHE);

            $parser = new SmartyParser($app['view.parser'], $smarty);
            $parser->register();

            return $smarty;
        };
    }

    protected function registerTwigEngine(Container $di)
    {
        $di['twig'] = function ($app) {
            $loader = new Twig_Loader_Filesystem('/');
            $twig   = new Twig_Environment(
                $loader, [
                'cache'       => STORAGE.'views'.DS,
                'debug'       => true,
                'auto_reload' => true,
                'autoescape'  => false,
                ]
            );

            $parser = new TwigParser($app['view.parser'], $twig);
            $parser->register();

            return $twig;
        };
    }
}
