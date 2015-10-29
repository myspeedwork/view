<?php

/**
 * This file is part of the Speedwork package.
 *
 * @link http://github.com/speedwork
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Speedwork\View;

use Speedwork\Config\Configure;
use Speedwork\Core\Application;
use Speedwork\Core\Di;
use Speedwork\Core\Registry;
use Speedwork\Util\Utility;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Template extends Di
{
    protected $_footer = [];
    protected $_header = [];

    protected $breadcrumbs = [];
    protected $_device     = 'computer';
    protected $url         = _URL;

    /**
     * File extension. Defaults to template ".tpl".
     *
     * @var string
     */
    protected $ext = '.tpl';

    /**
     * Document title.
     *
     * @var string
     */
    protected $_title = '';

    /**
     * Document description.
     *
     * @var string
     */
    protected $_description = '';

    /**
     * Document description.
     *
     * @var string
     */
    protected $_keywords = '';

    /**
     *  Array of linked links.
     *
     * @var array
     */
    protected $_links = [];

    /**
     * Document base URL.
     *
     * @var string
     */
    protected $_base = '';

    /**
     * Document base URL.
     *
     * @var string
     */
    protected $_basetarget = '_self';

    /**
     * Array of linked scripts.
     *
     * @var array
     */
    protected $_scripts = [];

    /**
     * Array of scripts placed in the header.
     *
     * @var array
     */
    protected $_script = [];

    /**
     * Array of linked style sheets.
     *
     * @var array
     */
    protected $_styleSheets = [];

    /**
     * Array of included style declarations.
     *
     * @var array
     */
    protected $_style = [];

    /**
     * Array of meta tags.
     *
     * @var array
     */
    protected $_metaTags = [];

    protected $_custom = [];
    /**
     * Contains the document language setting.
     *
     * @var string
     */
    protected $_language = 'en';

    /**
     * Contains the document direction setting.
     *
     * @var string
     */
    protected $_direction = 'ltr';

    /**
     * Document generator.
     *
     * @var string
     */
    protected $_generator = 'Speedwork';

    /**
     * Contains the character encoding string.
     *
     * @var string
     */
    protected $_charset = 'utf-8';

    /**
     * Document mime type.
     *
     * @var string
     */
    protected $_mime = 'text/html';

    /**
     * set the full of the script or styles.
     *
     * @var string
     */
    protected $path;
    protected $_author       = '';
    protected $_copyright    = '';
    protected $_robots       = 'index,follow';
    protected $_cachecontrol = 'max-age=30';

    public function setPath($path)
    {
        $this->path = $path;
    }

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
            $this->breadcrumbs[] = ['href' => $href,'text' => $text];
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

        if (count($files)  == 0) {
            return false;
        }

        return implode($separator, $files);
    }

    /**
     * Used to include component javascript files.
     *
     * @param string $component Name of the component
     * @param string $file      Javascript file name
     * @param array  $attr      Any custom attributes
     */
    public function addComponentScript($component, $file, $attr = [])
    {
        $url  = $this->get('resolver')->url($component);
        $path = $url.'components/'.$component.'/assets/'.$file;
        $this->addScriptUrl($path, $attr);

        return $this;
    }

    /**
     * This function is defined to include javascript files of the modules.
     *
     * @params string $module, string $incJSFile, bool $head(default false)
     * @ return NULL
     * $head is used to tell the script include js files in footer section of the template or header section
     **/
    public function addModuleScript($module, $file, $attr = [])
    {
        $url  = $this->get('resolver')->url($module, 'module');
        $path = $url.'modules/'.$module.'/assets/'.$file;
        $this->addScriptUrl($path, $attr);

        return $this;
    }

    /**
     * Used to include javascript files from path, default active theme will be used.
     *
     * @param string $filename Javascript file name.
     * @param string $path     Path of the file.
     * @param array  $attr     Any custom attributes.
     *
     * @return boolen True on success and false on fail.
     */
    public function script($filename, $path = '', $attr = [])
    {
        if ($path == 'bower') {
            $path             = _STATIC;
            $attr['position'] = ($attr['position']) ? $attr['position'] : 'footer';
        }
        $path = (!$path) ? _TMP_URL.'js/' : $path;
        $this->addScriptUrl($path.$filename, $attr);

        return $this;
    }

    /**
     * Used to include style sheets from path, default active theme will be used.
     *
     * @param string $filename Name of the style sheet.
     * @param string $path     Path of the file.
     * @param array  $attribs  Any custom attiributes for stylesheet.
     *
     * @return bool True on success and false on fail
     */
    public function styleSheet($filename, $path = '', $attribs = [])
    {
        if ($path == 'bower') {
            $path = _STATIC;
        }

        $path = (!$path) ? _TMP_URL.'css/' : $path;
        $path = $path.$filename;
        $this->addStyleSheetUrl($path, $attribs);

        return $this;
    }

    /**
     * Adds a linked script to the page.
     *
     * @param string $url  URL to the linked script
     * @param string $type Type of script. Defaults to 'text/javascript'
     */
    public function addScriptUrl($url, $attribs = [], $type = 'text/javascript')
    {
        $position = 'footer';
        if (is_array($attribs)) {
            $position = ($attribs['position']) ? $attribs['position'] : $position;
            unset($attribs['position']);
        }

        $this->_scripts[$position][$url]['mime']    = $type;
        $this->_scripts[$position][$url]['attribs'] = $attribs;

        return $this;
    }

    /**
     * Adds a linked stylesheet to the page.
     *
     * @param string $url   URL to the linked style sheet
     * @param string $type  Mime encoding type
     * @param string $media Media type that this stylesheet applies to
     */
    public function addStyleSheetUrl($url, $attribs = [], $media = null, $type = 'text/css')
    {
        $position = ($attribs['position']) ? $attribs['position'] : 'header';
        if (is_array($attribs)) {
            unset($attribs['position']);
        }

        $this->_styleSheets[$position][$url]['mime']    = $type;
        $this->_styleSheets[$position][$url]['media']   = $media;
        $this->_styleSheets[$position][$url]['attribs'] = $attribs;

        return $this;
    }

    /**
     * Adds a linked script to the page.
     *
     * @param string $filename
     * @param string $type     Type of script. Defaults to 'text/javascript'
     */
    public function addScript($filename, $attribs = [], $type = 'text/javascript')
    {
        if (is_string($attribs)) {
            if ($attribs == 'bower') {
                $attribs = _STATIC;
            }
            $url     = $attribs.$filename;
            $attribs = [];
        } else {
            $url = $this->path.$filename;
        }

        $this->addScriptUrl($url, $attribs, $type);

        return $this;
    }

    /**
     * Adds a linked stylesheet to the page.
     *
     * @param string $filename
     * @param string $type     Mime encoding type
     * @param string $media    Media type that this stylesheet applies to
     */
    public function addStyleSheet($filename, $attribs = [], $media = null, $type = 'text/css')
    {
        $url = $this->path.$filename;
        $this->addStyleSheetUrl($url, $attribs, $media, $type);

        return $this;
    }

    /**
     * Adds a script to the page.
     *
     *
     * @param string $content Script
     * @param string $type    Scripting mime (defaults to 'text/javascript')
     */
    public function addScriptDeclaration($content, $options = [], $type = 'text/javascript')
    {
        $position = ($options['position']) ? $options['position'] : 'footer';
        $this->_script[$position][strtolower($type)] .= chr(13).$content."\n";

        return $this;
    }

    /**
     * Adds a stylesheet declaration to the page.
     *
     * @param string $content Style declarations
     * @param string $type    Type of stylesheet (defaults to 'text/css')
     */
    public function addStyleDeclaration($content, $type = 'text/css')
    {
        $position = ($options['position']) ? $options['position'] : 'header';
        $this->_style[$position][strtolower($type)] .= chr(13).$content."\n";

        return $this;
    }

    /**
     * Adds a custom html string to the head block.
     *
     * @param string The html to add to the head
     */
    public function addCustomTag($html, $position = 'footer')
    {
        $this->_custom[$position][] = trim($html);

        return $this;
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
        $name   = strtolower($name);
        if ($name == 'generator') {
            $result = $this->getGenerator();
        } elseif ($name == 'description') {
            $result = $this->getDescription();
        } else {
            if ($http_equiv == true) {
                $result = @$this->_metaTags['http-equiv'][$name];
            } else {
                $result = @$this->_metaTags['standard'][$name];
            }
        }

        return $result;
    }

    /**
     * Sets or alters a meta tag.
     *
     * @param string $name       Value of name or http-equiv tag
     * @param string $content    Value of the content tag
     * @param bool   $http_equiv META type "http-equiv" defaults to null
     */
    public function setMetaData($name, $content, $http_equiv = false)
    {
        $name = strtolower($name);
        if ($name == 'generator') {
            $this->setGenerator($content);
        } elseif ($name == 'description') {
            $this->setDescription($content);
        } else {
            if ($http_equiv === true) {
                $this->_metaTags['http-equiv'][$name] = $content;
            } elseif ($http_equiv != false) {
                $this->_metaTags[$http_equiv][$name] = $content;
            } else {
                $this->_metaTags['standard'][$name] = $content;
            }
        }

        return $this;
    }

    /**
     * Sets the document charset.
     *
     * @param string $type Charset encoding string
     */
    public function setCharset($type = 'utf-8')
    {
        $this->_charset = $type;

        return $this;
    }

    /**
     * Returns the document charset encoding.
     *
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
    }

    /**
     * Sets the base URI of the document.
     *
     * @param string $base
     */
    public function setBase($base, $target = '_self')
    {
        if ($base != 'false' && $base != false) {
            $this->_base = $base;
        } else {
            $this->_base = null;
        }

        $this->_basetarget = $target;

        return $this;
    }

    /**
     * Return the base URI of the document.
     *
     * @return string
     */
    public function getBase()
    {
        return $this->_base;
    }

    /**
     * Sets the description of the document.
     *
     * @param string $title
     */
    public function setDescription($description)
    {
        $this->_description = $description;

        return $this;
    }

    /**
     * Return the title of the page.
     *
     * @return string
     */
    public function getDescription()
    {
        return ($this->_description) ? $this->_description : Configure::read('app.descn');
    }

    /**
     * Sets the title of the document.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;

        return $this;
    }

    /**
     * Return the title of the document.
     *
     * @return string
     */
    public function getTitle()
    {
        return ($this->_title) ? $this->_title : Configure::read('app.title');
    }

    /**
     * Sets the description of the document.
     *
     * @param string $title
     */
    public function setKeywords($keywords)
    {
        $this->_keywords = $keywords;

        return $this;
    }

    /**
     * Return the title of the page.
     *
     * @return string
     */
    public function getKeywords()
    {
        return ($this->_keywords) ? $this->_keywords : Configure::read('app.keywords');
    }

    /**
     * Sets the document generator.
     *
     * @param   string
     */
    public function setGenerator($generator)
    {
        $this->_generator = $generator;

        return $this;
    }

    /**
     * Returns the document generator.
     *
     *
     * @return string
     */
    public function getGenerator()
    {
        return $this->_generator;
    }

    /**
     * Sets the document MIME encoding that is sent to the browser.
     *
     * <p>This usually will be text/html because most browsers cannot yet
     * accept the proper mime settings for XHTML: application/xhtml+xml
     * and to a lesser extent application/xml and text/xml. See the W3C note
     * ({@link http://www.w3.org/TR/xhtml-media-types/
     * http://www.w3.org/TR/xhtml-media-types/}) for more details.</p>
     *
     * @param string $type
     */
    public function setMimeEncoding($type = 'text/html')
    {
        $this->_mime = strtolower($type);

        return $this;
    }

    /**
     * Adds <link> tags to the head of the document.
     *
     * <p>$relType defaults to 'rel' as it is the most common relation type used.
     * ('rev' refers to reverse relation, 'rel' indicates normal, forward relation.)
     * Typical tag: <link href="index.php" rel="Start"></p>
     *
     *
     * @param string $href       The link that is being related.
     * @param string $relation   Relation of link.
     * @param string $relType    Relation type attribute.  Either rel or rev (default: 'rel').
     * @param array  $attributes Associative array of remaining attributes.
     */
    public function addHeadLink($href, $relation, $relType = 'rel', $attribs = [])
    {
        $attribs        = Utility::parseAttributes($attribs);
        $generatedTag   = '<link href="'.$href.'" '.$relType.'="'.$relation.'" '.$attribs;
        $this->_links[] = $generatedTag;

        return $this;
    }

    /**
     * Adds a shortcut icon (favicon).
     *
     * <p>This adds a link to the icon shown in the favorites list or on
     * the left of the url in the address bar. Some browsers display
     * it on the tab, as well.</p>
     *
     * @param string $href     The link that is being related.
     * @param string $type     File type
     * @param string $relation Relation of link
     */
    public function addFavicon($href, $type = 'image/x-icon', $relation = 'shortcut icon')
    {
        $href           = str_replace('\\', '/', $href);
        $this->_links[] = '<link href="'.$href.'" rel="'.$relation.'" type="'.$type.'"';
    }

    public function beforeRender()
    {
        //set device
        $device = Configure::read('device');
        if ($device['name']) {
            $this->_device = $device['name'];
        }

        $app = Configure::read('app');

        $this->setMimeEncoding();
        $this->setTitle($app['title']);
        $this->setBase($this->url);
        $this->setKeywords(trim($app['keywords']));
        $this->setDescription(trim($app['descn']));

        $seo = Configure::read('seo.seo.enable');

        $prefix = Registry::get('url_prefix');
        //define global javascript var
        $html = '<script type="text/javascript">';
        $html .= '  var is_user_logged_in = '.(($this->get('is_user_logged_in')) ? 'true' : 'false').';';
        $html .= '  var url = "'.$this->format(_URL).'";';
        $html .= '  var base_url = "'.$this->format(rtrim(_URL.$prefix, '/')).'";';
        $html .= '  var public_url = "'._PUBLIC.'";';
        $html .= '  var theme_url = "'._TMP_URL.'";';
        $html .= '  var image_url = "'._UPLOAD.'";';
        $html .= '  var media_url = "'._MEDIA.'";';
        $html .= '  var static_url = "'._STATIC.'";';
        $html .= '  var seo_urls = '.(($seo) ? 'true' : 'false').';';
        $html .= '  var sys_url = "'.$this->format(_SYSURL).'";';
        $html .= '  var device = "'.$this->_device.'";';
        $html .= '  var serverTime = '.(time() * 1000).';';
        $html .= '</script>';

        $this->addCustomTag($html, 'header');
    }

    /**
     * Generates the head html and return the results as a string.
     *
     *
     * @return string
     */
    public function fetchHead()
    {
        // get line endings

        $lnEnd  = "\n";
        $tagEnd = ' />';
        $html   = '';

        // Generate base tag (need to happen first)
        if ($this->getBase() || $this->_basetarget) {
            $html  .= '<base ';
            $html  .= ($this->getBase()) ? 'href="'.$this->format($this->getBase()).'" ' : ' ';
            $html  .= 'target="'.$this->_basetarget.'"'.$tagEnd.$lnEnd;
        }

        $app = Configure::read('app');

        $append = $app['append_sitename_title'];
        $title  = htmlspecialchars($this->getTitle());

        if ($append == 'before') {
            $title = $app['name'].' | '.$title;
        }

        if ($append == 'after') {
            $title = $title.' | '.$app['name'];
        }
        $title = trim($title, ' | ');

        $html .= '<title>'.$title.'</title>'.$lnEnd;
        $html .= '<meta name="description" content="'.htmlspecialchars($this->getDescription()).'"'.$tagEnd.$lnEnd;
        $html .= '<meta name="keywords" content="'.htmlspecialchars($this->getKeywords()).'" '.$tagEnd.$lnEnd;
        $html .= '<meta name="generator" content="'.$this->_generator.'"'.$tagEnd.$lnEnd;
        $html .= '<meta name="author" content="'.$this->_author.'" '.$tagEnd.$lnEnd;
        $html .= '<meta name="copyright" content="'.$this->_copyright.'"'.$tagEnd.$lnEnd;
        $html .= '<meta name="robots" content="'.$this->_robots.'"'.$tagEnd.$lnEnd;
        $html .= '<meta name="csrf-token" content="'.$this->get('token').'"'.$tagEnd.$lnEnd;
        $html .= '<meta http-equiv="cache-control" content="'.$this->_cachecontrol.'"'.$tagEnd.$lnEnd;
        $html .= '<meta http-equiv="Content-Type" content="'.$this->_mime.'; charset='.$this->getCharset().'"'.$tagEnd.$lnEnd;
        // Generate META tags (needs to happen as early as possible in the head)
        foreach ($this->_metaTags as $type => $tag) {
            foreach ($tag as $name => $content) {
                if ($type == 'http-equiv') {
                    $html .= '<meta http-equiv="'.$name.'" content="'.$content.'"'.$tagEnd.$lnEnd;
                } elseif ($type == 'standard') {
                    $html .= '<meta name="'.$name.'" content="'.str_replace('"', "'", $content).'"'.$tagEnd.$lnEnd;
                } else {
                    $html .= '<meta  name="'.$name.'" '.$type.'="'.$name.'" content="'.str_replace('"', "'", $content).'"'.$tagEnd.$lnEnd;
                }
            }
        }

        // Generate link declarations
        foreach ($this->_links as $link) {
            $html .= $link.$tagEnd.$lnEnd;
        }

        $html .= $this->renderStyles('header');
        $html .= $this->renderScript('header');

        return $html;
    }

    public function format($url)
    {
        return str_replace('http://', '//', $url);
    }
    /**
     * Generates the footer html and return the results as a string.
     *
     *
     * @return string
     */
    public function fetchFooter()
    {
        $html = $this->renderScript('footer');
        $html .= $this->renderStyles('footer');

        return $html;
    }

    public function renderStyles($position = 'footer')
    {
        $lnEnd  = "\n";
        $html   = '';
        $tagEnd = ' />';

        $styles = $this->_styleSheets[$position];

        if (is_array($styles)) {
            $mini = Configure::read('minifier');
            if ($mini['css']) {
                $minify = $this->get('resolver')->helper('minifier');
                if ($mini['css']['cdnify']) {
                    $styles = $minify->cdnify($styles);
                }
                if ($mini['css']['minify']) {
                    $styles = $minify->minify($styles);
                }
            }

            foreach ($styles as $strSrc => $attr) {
                $html .= '<link rel="stylesheet" href="'.$this->format($strSrc).'" type="'.$attr['mime'].'"';
                if (!is_null($attr['media'])) {
                    $html .= ' media="'.$attr['media'].'" ';
                }
                if ($temp = Utility::parseAttributes($attr['attribs'])) {
                    $html .= ' '.$temp;
                }
                $html .= $tagEnd.$lnEnd;
            }
        }

        if (is_array($this->_style[$position])) {
            // Generate stylesheet declarations
            foreach ($this->_style[$position] as $type => $content) {
                $html .= '<style type="'.$type.'">'.$lnEnd;

                // This is for full XHTML support.
                if ($this->_mime == 'text/html') {
                    $html .= '<!--'.$lnEnd;
                } else {
                    $html .= '<![CDATA['.$lnEnd;
                }

                $html .= $content.$lnEnd;

                // See above note
                if ($this->_mime == 'text/html') {
                    $html .= '-->'.$lnEnd;
                } else {
                    $html .= ']]>'.$lnEnd;
                }
                $html .= '</style>'.$lnEnd;
            }
        }

        return $html;
    }

    public function renderScript($position = 'footer')
    {
        $lnEnd = "\n";
        $html  = '';

        $scripts = $this->_scripts[$position];

        if (is_array($scripts)) {
            $mini = Configure::read('minifier');
            if ($mini['js']) {
                $minify = $this->get('resolver')->helper('minifier');
                if ($mini['js']['cdnify']) {
                    $scripts = $minify->cdnify($scripts);
                }
                if ($mini['js']['minify']) {
                    $scripts = $minify->minify($scripts);
                }
            }

            // Generate script file links
            foreach ($scripts as $src => $attr) {
                $html .= '<script type="'.$attr['mime'].'" src="'.$this->format($src).'"';
                if ($temp = Utility::parseAttributes($attr['attribs'])) {
                    $html .= ' '.$temp;
                }
                $html .= '></script>'.$lnEnd;
            }
        }

        // Generate script declarations
        if (is_array($this->_script[$position])) {
            foreach ($this->_script[$position] as $type => $content) {
                $html .= '<script type="'.$type.'">'.$lnEnd;

                // This is for full XHTML support.
                if ($this->_mime != 'text/html') {
                    $html .= '<![CDATA['.$lnEnd;
                }
                $html .= 'jQuery(document).ready(function(){'.$lnEnd;
                $html .= $content.$lnEnd;
                $html .= '});'.$lnEnd;
                // See above note
                if ($this->_mime != 'text/html') {
                    $html .= '// ]]>'.$lnEnd;
                }
                $html .= '</script>'.$lnEnd;
            }
        }

        if (is_array($this->_custom[$position])) {
            foreach ($this->_custom[$position] as $custom) {
                $html .= $custom.$lnEnd;
            }
        }

        return $html;
    }

    /**
     * Render and output the document template.
     *
     *
     * @param string $_template The template folder
     * @param string $file      | optional template file
     *
     * @return The parsed contents of the template
     */
    private function fetchTemplate($file = '')
    {
        $view = _TMP_VIEW;
        $view = ($view) ? $view : 'index';
        $file = $file ? $file : $view;
        $file = $file.$this->ext;

        $files = [
            _TMP_PATH.$file,
            _TMP_SYSTEM.'system'.DS.$file,
            _TMP_SYSTEM.'system'.DS.'index'.$this->ext,
        ];

        $template = '';
        foreach ($files as $file) {
            if (file_exists($file)) {
                $template = $file;
                break;
            }
        }

        echo $this->parseTemplate($this->get('engine')->create($template)->render());

        return true;
    }

    public function render($file = '')
    {
        $this->onBeforeRenderTemplate($file);
    }

    private function onBeforeRenderTemplate($file = '')
    {
        //check that is ajax request
        if ($this->data['_request'] == 'iframe'
                || $this->data['_request'] == 'ajax'
                || strtolower($this->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
                || ($this->type) || ($this->tpl) || ($this->format)) {
            $this->sets('is_ajax_request', true);
        }

        if ($this->data['_request'] == 'iframe') {
            $this->sets('is_iframe_request', true);
        }

        if ($this->get['allowme']) {
            $this->get('session')->set('allowme', $this->get['allowme']);
        }

        $allow = $this->get('session')->get('allowme');
        $key   = Configure::read('offline.key');
        //check whether this site is in offline
        if (Configure::read('offline.is_offline') && (empty($allow) || $allow != $key)) {
            return $this->fetchTemplate('offline');
        }

        $is_logged_in = $this->get('is_user_logged_in');

        // default allow to every one
        $allowed = $this->get('acl')->isAllowed($this->option, $this->view, $this->task);

        if (!$allowed && $this->get('is_ajax_request')) {
            if ($this->type == 'html' || $this->format == 'html') {
                echo  'Your don\'t have sufficient permissions..';
            } else {
                $status           = [];
                $status['status'] = 'INFO';
                if (!$is_logged_in) {
                    $status['login']   = true;
                    $status['message'] = 'Please login to your account.';
                } else {
                    $status['message'] = 'Your don\'t have sufficient permissions..';
                }
                echo json_encode($status);
            }

            return false;
        }

        //for gusets
        if (!$allowed && !$is_logged_in) {
            $link = Configure::read('members.guest');
            if (empty($link)) {
                $link = 'members/login';
            }

            $this->redirect($link);

            return false;
        }

        //for already loggedin users
        if (!$allowed && $is_logged_in) {
            echo '<div class="alert alert-danger">Your don\'t have sufficient permissions.. </div>';
            $this->redirect('errors/denied');

            return false;
        }

        //check that is ajax request
        if ($this->get('is_ajax_request')) {
            $this->renderAjax();

            return false;
        }

        return $this->fetchTemplate($file);
    }

    public function renderAjax()
    {
        $formats = ['raw', 'xml', 'rss', 'json', 'js', 'jsonp', 'script'];

        if ($this->format == 'js' || $this->format == 'script') {
            header('Content-Type: application/javascript');
        }

        if ($this->format == 'xml') {
            header('Content-Type: text/xml');
        }

        if ($this->type == 'module' && empty($this->format)) {
            return $this->get('resolver')->module($this->option, $this->view);
        }

        if ($this->type == 'widget') {
            return $this->get('resolver')->widget($this->option, [], true);
        }

        if ($this->type == 'captcha') {
            $captcha = new Securimage();
            $captcha->show();

            return true;
        }

        if ($this->type == 'html' || $this->format == 'html') {
            $file = 'html';

            return $this->fetchTemplate($file);
        }

        if (($this->type) || ($this->format) || ($this->tpl)) {
            if ((!$this->format)) {
                $file = ($this->tpl) ? str_replace('..', '', $this->tpl) : 'component';

                return $this->fetchTemplate($file);
            }

            if (in_array($this->type, $formats) || in_array($this->format, $formats)) {
                if ($this->type == 'module') {
                    $response = $this->get('resolver')->loadModuleController($this->option, $this->view);
                } else {
                    $response = $this->get('resolver')->loadController($this->option, $this->view);
                }

                if ($this->format == 'json' || $this->format == 'jsonp') {
                    if (!is_array($response)) {
                        $response = $this->release('status');
                    }

                    if (!is_array($response)) {
                        $response = [];
                    }

                    $redirect = $this->get('redirect');

                    if ($redirect) {
                        $response['redirect'] = $redirect;
                    }

                    if ($this->is_iframe_request) {
                        echo '<textarea>';
                        echo json_encode($response);
                        echo '</textarea>';
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode($response);
                    }
                }
            }

            return true;
        }
    }

    /**
     * Parse a document template.
     *
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
     *
     * @param string $type    The type of renderer
     * @param string $name    The name of the element to render
     * @param array  $attribs Associative array of remaining attributes.
     *
     * @return The output of the renderer
     */
    public function getBuffer($type = null, $name = null, $attribs = [])
    {
        $type = (!empty($type)) ? $type : 'component';

        $func = 'render'.ucfirst($type);
        ob_start();
        echo $this->$func($name, $attribs);
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    protected function renderModule($name, $attribs)
    {
        return $this->get('resolver')->module($name, $attribs['view'], $attribs);
    }

    protected function renderModules($position)
    {
        if (empty($position)) {
            return;
        }

        return $this->get('resolver')->modules($position);
    }

    protected function renderPosition($position, $attribs = [])
    {
        if (empty($position)) {
            return;
        }

        $modules = Configure::read('theme_modules');
        if (!is_array($modules) || !isset($modules[$position])) {
            return;
        }

        $modules = $modules[$position];

        foreach ($modules as $module) {
            $attribs = array_merge($attribs, $module);
            if ($module['type'] == 'component') {
                $this->renderComponent($module['option'], $attribs);
            } else {
                $this->renderModule($module['option'], $attribs);
            }
        }
    }

    protected function renderComponent($option, $attribs)
    {
        $option = ($option) ? $option : $this->option;
        $view   = ($attribs['view']) ? $attribs['view'] : $this->view;

        unset($attribs['name'], $attribs['view']);

        return $this->get('resolver')->component($option, $view, $attribs);
    }

    protected function renderHeader()
    {
        return $this->fetchHead();
    }

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
        //Initialize variables
        $attr     = [];
        $retarray = [];

        // Lets grab all the key/value pairs using a regular expression
        preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);

        if (is_array($attr)) {
            $numPairs = count($attr[1]);
            for ($i = 0; $i < $numPairs; ++$i) {
                $retarray[$attr[1][$i]] = $attr[2][$i];
            }
        }

        return $retarray;
    }

    protected function geneareAttributes($data = [])
    {
        if (!is_array($data)) {
            return;
        }

        foreach ($data as $key => $value) {
            $attr .= $key.'="'.$value.'" ';
        }

        return $attr;
    }
}
