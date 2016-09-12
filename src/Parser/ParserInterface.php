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

/**
 * Interface for Parsing templates.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
interface ParserInterface
{
    /**
     * Read Configuration.
     *
     * @param string $key Configuration key
     *
     * @return string Config value
     */
    public function config($key);

    /**
     * Translate string.
     *
     * @param string $string  Translation string
     * @param array  $replace Dynamic Values in string
     *
     * @return string Final translation string
     */
    public function trans($string, $replace = []);

    /**
     * Url to convert into proper link.
     *
     * @param string $url Url
     *
     * @return string Final converted url
     */
    public function link($url);

    /**
     * Convert given string into slug.
     *
     * @param string $string  String to convert
     * @param string $replace Replace spaces with
     *
     * @return string Final slug
     */
    public function slug($string, $replace = '-');

    /**
     * Convert give time into date time formart.
     *
     * @param mixed  $time   timestamp or date to convert
     * @param string $format Desired outout format
     *
     * @return string Converted string
     */
    public function toDate($time, $format = 'M d, Y h:i A');

    /**
     * Layout render from modules, components and themes.
     *
     * @param string $name Name of the layout with module
     * @param string $type Type of the layout
     *
     * @return string Layout complete path
     */
    public function layout($name, $type = 'component');

    /**
     * Request modules or components.
     *
     * @param string $name Name of the component or module
     * @param string $type Type of the request
     *
     * @return string Complete response with data
     */
    public function request($name, $args = [], $type = 'component');

    /**
     * Render theme render methods.
     *
     * @param string $type  Type of method
     * @param string $name  Name of the request
     * @param array  $attrs Additional arguments
     *
     * @return string Compiled output of the request
     */
    public function render($type, $name = null, $attrs = []);

    /**
     * Render theme assets.
     *
     * @param string $method Name of the method
     * @param array  $args   Additional arguments
     *
     * @return string Compiled output of the request
     */
    public function theme($method, $args = []);

    /**
     * Display status string in beautiful way.
     *
     * @param string $status Status Code
     * @param string $theme  Design to display
     *
     * @return sting Formated html
     */
    public function status($status, $theme = null);
}
