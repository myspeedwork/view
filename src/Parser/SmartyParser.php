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

use Smarty;

/**
 * Smarty parser.
 *
 * @author sankar <sankar.suda@gmail.com>
 */
class SmartyParser
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
        'config',
        'view',
    ];

    /**
     * Constructor.
     *
     * @param ParserInterface $parser [description]
     * @param Smarty          $smarty [description]
     */
    public function __construct(ParserInterface $parser, Smarty $smarty)
    {
        $this->engine = $smarty;
        $this->parser = $parser;
    }

    public function register()
    {
        $this->engine->registerPlugin('modifier', 'todate', [$this->parser, 'toDate']);
        $this->engine->registerPlugin('modifier', 'status', [$this->parser, 'status']);
        $this->engine->registerPlugin('modifier', 'slug', [$this->parser, 'slug']);

        $this->engine->registerPlugin('function', 'speed', [$this, 'execute']);
    }

    public function execute($params)
    {
        $method = key($params);

        if ($method && in_array($method, $this->allowed)) {
            return $this->$method($params);
        }
    }

    /**
     * Add Translation Support to engine
     * {speed trans="my sting"}.
     *
     * @param array $params Smarty Params
     *
     * @return string Final translated string
     */
    protected function trans($params = [])
    {
        $string = $params['trans'];

        return $this->parser->trans($string, $params);
    }

    /**
     * Template engine to render links
     * {speed link="index.php?option=content"}.
     *
     * @param array $params Smarty params
     *
     * @return string Results
     */
    protected function link($params = [])
    {
        return $this->parser->link($params['link']);
    }

    /**
     * Template engine to include layout
     * {speed layout="component.folder.layout" type="module"}.
     *
     * @param array $params Smarty params
     *
     * @return string Results
     */
    protected function layout($params = [])
    {
        return $this->parser->layout($params['layout'], $params['type']);
    }

    /**
     * Template engine to render view with data
     * {speed view="component.folder.layout" data=$row, type="module"}.
     *
     * @param array $params Smarty params
     *
     * @return string Results
     */
    protected function view($params = [])
    {
        return $this->parser->view($params['view'], $params, $params['type']);
    }

    /**
     * Request componets or modules
     * {speed request="books" type="component"}.
     *
     * @param array $params Smarty params
     *
     * @return string Results
     */
    protected function request($params = [])
    {
        return $this->parser->request($params['request'], [], $params['type']);
    }

    /*
     * To run functions in template class
     * {speed theme="setMeta" params="viewport','320"}
     */
    protected function theme($params = [])
    {
        $method = $params['theme'];

        return $this->parser->theme($method);
    }

    /**
     * Render Template Render Methods.
     * {speed render="component|module|position", name="com"}.
     *
     * @param array $params Smarty prams
     *
     * @return string Output of the method
     */
    protected function render($params = [])
    {
        return $this->parser->render($params['render'], $params['name'], $params);
    }

    /**
     * Read configuration from smarty
     * {speed config="key"}.
     *
     * @param array $params Smarty prams
     *
     * @return string Output of the method
     */
    protected function config($params = [])
    {
        return $this->parser->config($params['config']);
    }
}
