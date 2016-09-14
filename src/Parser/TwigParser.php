<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\View\Parser;

use Twig_Environment;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * Twig parser.
 *
 * @author sankar <sankar.suda@gmail.com>
 */
class TwigParser
{
    protected $engine;
    protected $parser;
    protected $allowed = [
        'trans',
        'link',
        'layout',
        'request',
        'theme',
        'render',
    ];

    /**
     * Constructor.
     *
     * @param ParserInterface  $parser [description]
     * @param Twig_Environment $engine [description]
     */
    public function __construct(ParserInterface $parser, Twig_Environment $engine)
    {
        $this->engine = $engine;
        $this->parser = $parser;
    }

    public function register()
    {
        $this->engine->addFilter(new Twig_SimpleFilter('todate', [$this->parser, 'toDate']));
        $this->engine->addFilter(new Twig_SimpleFilter('slug', [$this->parser, 'slug']));
        $this->engine->addFilter(new Twig_SimpleFilter('status', [$this->parser, 'status']));

        $this->engine->addFunction(new Twig_SimpleFunction('layout', [$this->parser, 'layout']));
        $this->engine->addFunction(new Twig_SimpleFunction('trans', [$this->parser, 'trans']));
        $this->engine->addFunction(new Twig_SimpleFunction('link', [$this->parser, 'link']));
        $this->engine->addFunction(new Twig_SimpleFunction('request', [$this->parser, 'request']));
        $this->engine->addFunction(new Twig_SimpleFunction('render', [$this->parser, 'render']));
        $this->engine->addFunction(new Twig_SimpleFunction('theme', [$this->parser, 'theme']));
        $this->engine->addFunction(new Twig_SimpleFunction('config', [$this->parser, 'config']));
    }
}
