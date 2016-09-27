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

use Speedwork\Container\Container;
use Speedwork\Core\Router;
use Speedwork\Util\Str;

/**
 * Parsing templates.
 *
 * @author sankar <sankar.suda@gmail.com>
 */
class Parser implements ParserInterface
{
    protected $container;

    /**
     * @param Container $app Application instance
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($string, $replace = [])
    {
        return trans($string, $replace);
    }

    /**
     * {@inheritdoc}
     */
    public function config($key)
    {
        return config($key);
    }

    /**
     * {@inheritdoc}
     */
    public function link($url)
    {
        return Router::link($url);
    }

    /**
     * {@inheritdoc}
     */
    public function layout($name, $type = 'component')
    {
        $type = $type ?: 'component';

        return $this->getContainer()->get('resolver')->requestLayout($name, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function view($name, $data = [], $type = 'component')
    {
        $type = $type ?: 'component';

        return $this->getContainer()->get('resolver')->loadView($name, $data, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function request($name, $args = [], $type = 'component')
    {
        $type = $type ?: 'component';

        $app = $this->getContainer()->get('resolver');
        if ($type == 'module') {
            return $app->module($name, $args);
        }

        return $app->component($name, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function render($type, $name = null, $attrs = [])
    {
        return $this->getContainer()->get('template')->getBuffer($type, $name, $attrs);
    }

    /**
     * {@inheritdoc}
     */
    public function theme($method, $args = [])
    {
        $theme = $this->getContainer()->get('template');
        call_user_func_array([$theme, $method], $args);
    }

    /**
     * {@inheritdoc}
     */
    public function asset($method, $args = [])
    {
        $asset = $this->getContainer()->get('asset');
        call_user_func_array([$asset, $method], $args);
    }

    /**
     * {@inheritdoc}
     */
    public function status($status, $theme = null)
    {
        if ($theme == 'approve') {
            $data = '<a data-status="1"';
            if ($status) {
                $data .= 'style="display:none"';
            }
            $data .= '>';
            $data .= '<i class="fa fa-ban fa-lg" role="tooltip" title="Click to Approve"></i></a>';

            $data .= '<a data-status="0"';
            if ($status == 0) {
                $data .= 'style="display:none"';
            }
            $data .= '>';
            $data .= '<i class="fa fa-check fa-lg" role="tooltip" title="Click to unapprove"></i></a>';

            return $data;
        }

        if ($status == 1) {
            return '<a><i class="fa fa-lg fa-check" role="tooltip" title="Active"></i></a>';
        }

        if ($status == 9) {
            return '<a><i class="fa fa-lg fa-trash-o" role="tooltip" title="Deleted"></i></a>';
        }

        return '<a><i class="fa fa-lg fa-ban" role="tooltip" title="InActive"></i></a>';
    }

    /**
     * {@inheritdoc}
     */
    public function slug($string, $seperator = '-')
    {
        return Str::slug($string, $seperator);
    }

    /**
     * {@inheritdoc}
     */
    public function toDate($time, $format = 'M d, Y h:i A')
    {
        if (empty($time) || $time == '0000-00-00') {
            return;
        }

        if (!is_numeric($time)) {
            $parts = explode('/', $time);

            if ($parts[0] && strlen($parts[0]) != 4) {
                $time = str_replace('/', '-', trim($time));
            }
            $time = strtotime($time);
        }

        return date($format, $time);
    }
}
