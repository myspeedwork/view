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

use Speedwork\Core\Di;
use Speedwork\Core\Traits\HttpTrait;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Template extends Di
{
    use HttpTrait;

    protected $breadcrumbs = [];

    /**
     * Document base URL.
     *
     * @var string
     */
    protected $baseLink = '';

    /**
     * Document base URL.
     *
     * @var string
     */
    protected $baseTarget = '_self';

    /**
     * Array of meta tags.
     *
     * @var array
     */
    protected $metaTags = [];

    /**
     * Add breadCrumb item to breadCrumb.
     *
     * @param string $text Text of the bread crumb
     * @param string $href url for bread crumb
     *
     * @return bool Retuns true on success and false on fail
     */
    public function breadCrumbItem($text, $href = '')
    {
        if ($text) {
            $this->breadcrumbs[] = ['href' => $href, 'text' => $text];
        }

        return $this;
    }

    /**
     * Ganerate bread crumb from items added from breadCrumbItem method.
     *
     * @param string $separator Item seperator. default space
     *
     * @return string Generated bread crumb
     */
    public function breadCrumb($separator = '&nbsp;')
    {
        $files = [];
        if (is_array($this->breadcrumbs)) {
            foreach ($this->breadcrumbs as $file) {
                if (!$file['text']) {
                    continue;
                }
                $href = '';
                if ($file['href']) {
                    $href = (strpos($file['href'], 'link:') !== false) ?
                                        $this->link(str_replace('link:', '', $file['href'])) : $file['href'];
                }

                $files[] = '<span>'.(($href) ? ' <a href="'.$href.'"> ' : '').$file['text'].(($href) ? '</a>' : ' ').'</span>';
            }
        }

        if (count($files) == 0) {
            return false;
        }

        return implode($separator, $files);
    }

    /**
     * Gets a meta tag.
     *
     * @param string $name       Value of name or http-equiv tag
     * @param bool   $http_equiv META type "http-equiv" defaults to null
     *
     * @return string
     */
    public function getMetaData($name, $http_equiv = false)
    {
        $result = '';
        if ($http_equiv === true) {
            $result = $this->metaTags['http-equiv'][$name];
        } elseif ($http_equiv !== false) {
            $result = $this->metaTags[$http_equiv][$name];
        } else {
            $result = $this->metaTags['standard'][$name];
        }

        return $result;
    }

    /**
     * Sets or alters a meta tag.
     *
     * @param string $name      Value of name or http-equiv tag
     * @param string $content   Value of the content tag
     * @param bool   $httpEquiv META type "http-equiv" defaults to null
     */
    public function setMeta($name, $content, $httpEquiv = false)
    {
        if ($httpEquiv === true) {
            $this->metaTags['http-equiv'][$name] = $content;
        } elseif ($httpEquiv && $httpEquiv !== false) {
            $this->metaTags[$httpEquiv][$name] = $content;
        } else {
            $this->metaTags['standard'][$name] = $content;
        }

        return $this;
    }

    /**
     * Set the page title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        return $this->setMeta('title', $title);
    }

    /**
     * Set the page description.
     *
     * @param string $description
     */
    public function setDescn($description)
    {
        return $this->setMeta('description', $description);
    }

    /**
     * Set the page keywords.
     *
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        return $this->setMeta('keywords', $keywords);
    }

    /**
     * Sets the base URI of the document.
     *
     * @param string $base
     */
    public function setBase($base, $target = '_self')
    {
        $this->baseLink   = $base;
        $this->baseTarget = $target;

        return $this;
    }

    /**
     * Return the base URI of the document.
     *
     * @return string
     */
    public function getBase()
    {
        return $this->baseLink;
    }

    public function setDefaults()
    {
        $app    = $this->config('app');
        $device = $app['device'];
        $meta   = $app['meta'];

        $location = $this->config('location');

        if (is_array($meta)) {
            foreach ($meta as $name => $content) {
                list($name, $httpEquiv) = explode('::', $name);
                $this->setMeta($name, $content, $httpEquiv);
            }
        }

        $this->setMeta('csrf-token', $this->get('token'));
        $this->setMeta('Content-Type', 'text/html; utf-8', true);

        //define global javascript var
        $html = '<script type="text/javascript">';
        $html .= '  var is_user_logged_in = '.(($this->get('is_user_logged_in')) ? 'true' : 'false').';';
        $html .= '  var url = "'.$app['url'].'";';
        $html .= '  var base_url = "'.$location['url'].'";';
        $html .= '  var public_url = "'.$location['public'].'";';
        $html .= '  var theme_url = "'.$location['theme'].'";';
        $html .= '  var image_url = "'.$location['images'].'";';
        $html .= '  var media_url = "'.$location['media'].'";';
        $html .= '  var static_url = "'.$location['static'].'";';
        $html .= '  var seo_urls = '.((config('router.seo.enable')) ? 'true' : 'false').';';
        $html .= '  var device = "'.$device['name'].'";';
        $html .= '  var serverTime = '.(time() * 1000).';';
        $html .= '</script>';

        $this->get('assets')->addCustomTag($html, 'header');
    }

    /**
     * Generates the head html and return the results as a string.
     *
     * @return string
     */
    public function fetchHead()
    {
        $html = '';
        // Generate base tag (need to happen first)
        if ($this->getBase()) {
            $html .= '<base ';
            $html .= ($this->getBase()) ? 'href="'.$this->getBase().'" ' : ' ';
            $html .= 'target="'.$this->baseTarget.'"'." />\n";
        }

        $html .= $this->generateMeta();
        $html .= $this->get('assets')->renderlinks();
        $html .= $this->get('assets')->renderStyles('header');
        $html .= $this->get('assets')->renderScript('header');

        return $html;
    }

    protected function generateMeta()
    {
        $html = '';
        // Generate META tags (needs to happen as early as possible in the head)
        foreach ($this->metaTags as $type => $tag) {
            foreach ($tag as $name => $content) {
                if ($type == 'http-equiv') {
                    $html .= '<meta http-equiv="'.$name.'" content="'.$content.'"'." />\n";
                } elseif ($name == 'title') {
                    $html .= '<title>'.$content."</title>\n";
                } elseif ($type == 'standard') {
                    $html .= '<meta name="'.$name.'" content="'.str_replace('"', "'", $content).'"'." />\n";
                } else {
                    $html .= '<meta name="'.$name.'" '.$type.'="'.$name.'" content="'.str_replace('"', "'", $content).'"'." />\n";
                }
            }
        }

        return $html;
    }

    /**
     * Generates the footer html and return the results as a string.
     *
     * @return string
     */
    public function fetchFooter()
    {
        $html = $this->get('assets')->renderScript('footer');
        $html .= $this->get('assets')->renderStyles('footer');

        return $html;
    }

    /**
     * Render and output the document template.
     *
     * @param string $_template The template folder
     * @param string $file      | optional template file
     *
     * @return The parsed contents of the template
     */
    private function fetchTemplate($file = '')
    {
        $layout = $this->config('view.theme.layout');
        $layout = $layout ?: 'index';
        $file   = $file ?: $layout;

        $extensions = $this->config('view.extensions');
        $extensions = $extensions ?: ['tpl'];

        $files = [
            $this->app['path.theme'].$file,
            $this->app['path.themes'].'system'.DS.$file,
            $this->app['path.themes'].'system'.DS.'index',
        ];

        $template = '';
        foreach ($files as $file) {
            foreach ($extensions as $extension) {
                if (is_file($file.'.'.$extension) && file_exists($file.'.'.$extension)) {
                    $template = $file.'.'.$extension;
                    break 2;
                }
            }
        }

        if ($template) {
            return $this->parseTemplate($this->get('engine')->create($template)->render());
        }

        return '';
    }

    /**
     * Identify the request type.
     */
    protected function checkRequestType()
    {
        //check that is ajax request
        if ($this->input('_request') == 'iframe'
            || $this->input('_request') == 'ajax'
            || strtolower(env('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest'
            || ($this->get('type')) || ($this->get('layout')) || ($this->get('format'))
        ) {
            $this->sets('is_ajax_request', true);
        }

        if ($this->input('_request') == 'iframe') {
            $this->sets('is_iframe_request', true);
        }
    }

    /**
     * Check is application offline.
     *
     * @return bool
     */
    protected function isOffline()
    {
        //check whether this site is in offline
        if ($this->config('app.offline.enable')) {
            if ($this->input('allowme')) {
                $this->get('session')->set('allowme', $this->input('allowme'));
            }

            $allow = $this->get('session')->get('allowme');
            $key   = $this->config('app.offline.key');

            if ($allow && $allow === $key) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Check is allowed to view the requested page.
     *
     * @return string|array
     */
    protected function isNotGranted()
    {
        $is_logged_in = $this->get('is_user_logged_in');
        $isAjax       = $this->get('is_ajax_request');

        $next = $isAjax ? '' : $this->get('request')->fullUrlWithQuery([]);
        $next = $this->input('next') ?: $next;

        if ($isAjax) {
            if ($this->type == 'html' || $this->format == 'html') {
                if (!$is_logged_in) {
                    return '<div class="alert alert-info text-bold">Please <a data-next="'.$next.'" href="'.$this->link('members/login?next='.urldecode($next)).'" role="login">login</a> to your account.</div>';
                }

                return '<div class="alert alert-danger text-bold">'.trans('Your don\'t have sufficient permissions.').'</div>';
            }

            $status           = [];
            $status['status'] = 'INFO';
            if (!$is_logged_in) {
                $status['login']   = true;
                $status['next']    = $next;
                $status['message'] = trans('Please login to your account.');
            } else {
                $status['message'] = trans('Your don\'t have sufficient permissions.');
            }

            return $status;
        }

        //for gusets
        if (!$is_logged_in) {
            $link = $this->config('auth.account.guest');
            if (empty($link)) {
                $link = 'members/login';
            }
            $link .= '?next='.urlencode($next);

            return $this->redirect($link);
        }

        return $this->redirect('errors/denied');
    }

    /**
     * Render the request.
     *
     * @return string|array
     */
    public function render()
    {
        if ($this->isOffline()) {
            return $this->fetchTemplate('offline');
        }

        $this->checkRequestType();

        $rule      = $this->get('rule');
        $isGranted = $this->get('acl')->isGranted($rule);
        $isAjax    = $this->get('is_ajax_request');

        if (!$isGranted) {
            return $this->isNotGranted();
        }

        //check that is ajax request
        if ($isAjax) {
            return $this->renderAjax();
        }

        return $this->fetchTemplate();
    }

    /**
     * Render the ajax request.
     *
     * @return string|array
     */
    protected function renderAjax()
    {
        $formats = ['raw', 'xml', 'rss', 'json', 'js', 'jsonp', 'script'];
        $route   = $this->get('route');
        $format  = $this->get('format');
        $type    = $this->get('type');
        $layout  = $this->get('layout');

        if ($format == 'js' || $format == 'script') {
            $this->setHeader('Content-Type', 'application/javascript');
        }

        if ($format == 'xml') {
            $this->setHeader('Content-Type', 'text/xml');
        }

        if ($type == 'module' && empty($format)) {
            return $this->get('resolver')->module($route);
        }

        if ($type == 'widget') {
            return $this->get('resolver')->widget($route, [], true);
        }

        if ($type == 'html' || $format == 'html') {
            $file = 'html';

            return $this->fetchTemplate($file);
        }

        if ($type || $format || $layout) {
            if ((!$format)) {
                $file = ($layout) ? str_replace('..', '', $layout) : 'component';

                return $this->fetchTemplate($file);
            }

            if (in_array($type, $formats) || in_array($format, $formats)) {
                if ($type == 'module') {
                    $response = $this->get('resolver')->loadModuleController($route);
                } else {
                    $response = $this->get('resolver')->loadController($route);
                }

                if ($format == 'json' || $format == 'jsonp') {
                    if (!is_array($response)) {
                        $response = [];
                    }

                    $redirect = $this->get('redirect');

                    if ($redirect) {
                        $response['redirect'] = $redirect;
                    }

                    if ($this->is_iframe_request) {
                        $html = '<textarea>';
                        $html .= json_encode($response);
                        $html .= '</textarea>';

                        return $html;
                    } else {
                        return $response;
                    }
                }
            }

            return '';
        }
    }

    /**
     * Parse a document template.
     *
     * @param string $data The data too parse
     *
     * @return The parsed contents of the template
     */
    protected function parseTemplate($data)
    {
        $replace = [];
        $matches = [];
        if (preg_match_all('#<speed:include type="([^"]+)" (.*)\/>#iU', $data, $matches)) {
            foreach ($matches[1] as $k => $v) {
                if ($v == 'header') {
                    unset($matches[1][$k]);
                    $matches[1][98] = $v;
                    $matches[2][98] = $matches[2][$k];
                    unset($matches[2][$k]);
                }
                if ($v == 'footer') {
                    unset($matches[1][$k]);
                    $matches[1][99] = $v;
                    $matches[2][99] = $matches[2][$k];
                    unset($matches[2][$k]);
                }
                if ($v == 'component') {
                    unset($matches[1][$k]);
                    $matches[1][0] = $v;
                    $matches[2][0] = $matches[2][$k];
                    unset($matches[2][$k]);
                }
            }

            ksort($matches[1]);
            $matches[1] = array_values($matches[1]);
            ksort($matches[2]);
            $matches[2] = array_values($matches[2]);

            $count = count($matches[1]);

            $matche = [];

            for ($i = 0; $i < $count; ++$i) {
                $attribs = $this->parseAttributes($matches[2][$i]);
                $type    = $matches[1][$i];

                $name        = isset($attribs['name']) ? $attribs['name'] : null;
                $replace[$i] = $this->getBuffer($type, $name, $attribs);

                $matche[$i] = '<speed:include type="'.$type.'" '.$matches[2][$i].'/>';
            }

            $data = str_replace($matche, $replace, $data);
        }

        return $data;
    }

    /**
     * Get the contents of a document include.
     *
     * @param string $type    The type of renderer
     * @param string $name    The name of the element to render
     * @param array  $attribs Associative array of remaining attributes
     *
     * @return The output of the renderer
     */
    public function getBuffer($type = null, $name = null, $attribs = [])
    {
        $type = (!empty($type)) ? $type : 'component';

        $func = 'render'.ucfirst($type);

        return $this->$func($name, $attribs);
    }

    /**
     * Render module.
     *
     * @param string $name    name of the module
     * @param array  $attribs Extra attributes
     *
     * @return string
     */
    protected function renderModule($name, array $attribs = [])
    {
        return $this->get('resolver')->module($name, $attribs);
    }

    /**
     * Render modules in position.
     *
     * @param string $position
     *
     * @return string
     */
    protected function renderModules($position)
    {
        if (empty($position)) {
            return;
        }

        return $this->get('resolver')->modules($position);
    }

    /**
     * Render modules and components in position.
     *
     * @param string $position
     * @param array  $attribs
     *
     * @return string
     */
    protected function renderPosition($position, array $attribs = [])
    {
        if (empty($position)) {
            return;
        }

        $modules = $this->config('view.modules');
        if (!is_array($modules) || !isset($modules[$position])) {
            return;
        }

        $modules = $modules[$position];

        $content = '';
        foreach ($modules as $module) {
            $attribs = array_merge($attribs, $module);
            if ($module['type'] == 'component') {
                $content .= $this->renderComponent($module['option'], $attribs);
            } else {
                $content .= $this->renderModule($module['option'], $attribs);
            }
        }

        return $content;
    }

    /**
     * Render component.
     *
     * @param string|null $name    name of the route
     * @param array       $attribs Any extra attribs
     *
     * @return string
     */
    protected function renderComponent($name, array $attribs = [])
    {
        $name = $name ?: $this->get('route');

        return $this->get('resolver')->component($name, $attribs);
    }

    /**
     * Render the header position.
     *
     * @return string
     */
    protected function renderHeader()
    {
        return $this->fetchHead();
    }

    /**
     * Render footer position.
     *
     * @return string
     */
    protected function renderFooter()
    {
        return $this->fetchFooter();
    }

    /**
     * Method to extract key/value pairs out of a string with xml style attributes.
     *
     * @param string $string String containing xml style attributes
     *
     * @return array Key/Value pairs for the attributes
     */
    public function parseAttributes($string)
    {
        $attribs = [];
        // Lets grab all the key/value pairs using a regular expression
        preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $matches);

        if (is_array($matches)) {
            $numPairs = count($matches[1]);
            for ($i = 0; $i < $numPairs; ++$i) {
                $attribs[$matches[1][$i]] = $matches[2][$i];
            }
        }

        return $attribs;
    }
}
