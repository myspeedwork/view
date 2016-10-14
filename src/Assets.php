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

use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\HttpAsset;
use Assetic\AssetManager;
use Assetic\AssetWriter;
use Assetic\Cache\FilesystemCache;
use Assetic\Filter\CssImportFilter;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\JSMinFilter;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Assets
{
    /**
     * Array of linked scripts.
     *
     * @var array
     */
    protected $scriptUrls = [];

    /**
     * Array of linked style sheets.
     *
     * @var array
     */
    protected $styleSheets = [];

    /**
     * Array of included style declarations.
     *
     * @var array
     */
    protected $styles = [];

    /**
     * Array of scripts placed in the header.
     *
     * @var array
     */
    protected $scripts = [];

    /**
     * HTML Link tags.
     *
     * @var array
     */
    protected $linkTags = [];

    /**
     * Any other custom tags.
     *
     * @var array
     */
    protected $customTags = [];

    /**
     * Convert given relative path to url.
     *
     * @param string $path Path of the file
     *
     * @return string Url of the give file
     */
    protected function cleanUrls($urls = [])
    {
        $files = [];
        foreach ($urls as $url) {
            list($type, $file) = explode('::', $url);
            if ($type && $file) {
                $files[] = path($type, true).$file;
            } else {
                $files[] = $url;
            }
        }

        return $files;
    }

    /**
     * Add Multiple types of assets to assets manager.
     *
     * @param array $paths List of files with complete path
     */
    public function add($paths = [])
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            list($ext, $version) = explode('?', strtolower(strrchr($path, '.')));

            if ($ext == '.js') {
                $this->addScript($path);
            }

            if ($ext == '.css') {
                $this->addStyleSheet($path);
            }
        }

        unset($version);

        return $this;
    }

    /**
     * Adds a linked script to the page.
     *
     * @param string $url      URL to the linked script
     * @param string $position
     */
    public function addScript($url, $position = 'footer')
    {
        $this->scriptUrls[$position][] = $url;

        return $this;
    }

    /**
     * Adds a linked stylesheet to the page.
     *
     * @param string $url   URL to the linked style sheet
     * @param string $type  Mime encoding type
     * @param string $media Media type that this stylesheet applies to
     */
    public function addStyleSheet($url, $position = 'header')
    {
        $this->styleSheets[$position][] = $url;

        return $this;
    }

    /**
     * Adds a script to the page.
     *
     * @param string $content Script
     * @param string $type    Scripting mime (defaults to 'text/javascript')
     */
    public function addScriptDeclaration($content, $position = 'footer')
    {
        $this->scripts[$position] .= "\n".$content."\n";

        return $this;
    }

    /**
     * Adds a stylesheet declaration to the page.
     *
     * @param string $content Style declarations
     * @param string $type    Type of stylesheet (defaults to 'text/css')
     */
    public function addStyleDeclaration($content, $position = 'header')
    {
        $this->styles[$position] .= "\n".$content."\n";

        return $this;
    }

    /**
     * Adds a custom html string to the head block.
     *
     * @param string The html to add to the head
     */
    public function addCustomTag($html, $position = 'footer')
    {
        $this->customTags[$position][] = trim($html);

        return $this;
    }

    /**
     * Adds <link> tags to the head of the document.
     *
     * <p>$relType defaults to 'rel' as it is the most common relation type used.
     * ('rev' refers to reverse relation, 'rel' indicates normal, forward relation.)
     * Typical tag: <link href="index.php" rel="Start"></p>
     *
     * @param string $href       The link that is being related
     * @param string $relation   Relation of link
     * @param string $relType    Relation type attribute.  Either rel or rev (default: 'rel')
     * @param array  $attributes Associative array of remaining attributes
     */
    public function addLinkTag($href, $relation, $attribs = [], $relType = 'rel')
    {
        $attribs = $this->generateAttrib($attribs);
        $href    = str_replace('\\', '/', $href);

        $tag = '<link href="'.$href.'" '.$relType.'="'.$relation.'" '.$attribs.' />';

        $this->linkTags[] = $tag;

        return $this;
    }

    /**
     * Adds a shortcut icon (favicon).
     *
     * <p>This adds a link to the icon shown in the favorites list or on
     * the left of the url in the address bar. Some browsers display
     * it on the tab, as well.</p>
     *
     * @param string $href     The link that is being related
     * @param string $type     File type
     * @param string $relation Relation of link
     */
    public function addFavicon($href, $type = 'image/x-icon', $relation = 'shortcut icon')
    {
        return $this->addLinkTag($href, $relation, ['type' => $type]);
    }

    public function renderLinks()
    {
        $html = '';
        // Generate link declarations
        foreach ($this->linkTags as $link) {
            $html .= $link."\n";
        }

        return $html;
    }

    public function renderStyles($position = 'footer')
    {
        $lnEnd  = "\n";
        $html   = '';
        $tagEnd = ' />';

        $styles = $this->styleSheets[$position];

        if (is_array($styles)) {
            $styles = $this->cleanUrls($styles);
            $styles = $this->assetic($styles, 'css');

            foreach ($styles as $style) {
                $html .= '<link rel="stylesheet" href="'.$style.'" type="text/css"';
                $html .= $tagEnd.$lnEnd;
            }
        }

        if ($this->styles[$position]) {
            // Generate stylesheet declarations
            $html .= '<style type="text/css">'.$lnEnd;
            $html .= $this->styles[$position].$lnEnd;
            $html .= '</style>'.$lnEnd;
        }

        return $html;
    }

    public function renderScript($position = 'footer')
    {
        $lnEnd = "\n";
        $html  = '';

        $scripts = $this->scriptUrls[$position];

        if (is_array($scripts)) {
            $scripts = $this->cleanUrls($scripts);
            $scripts = $this->assetic($scripts, 'js');

            foreach ($scripts as $script) {
                $html .= '<script type="text/javascript" src="'.$script.'"';
                $html .= '></script>'.$lnEnd;
            }
        }
        // Generate script declarations
        if ($this->scripts[$position]) {
            $html .= '<script type="text/javascript">'.$lnEnd;
            $html .= 'jQuery(document).ready(function(){'.$lnEnd;
            $html .= $this->scripts[$position].$lnEnd;
            $html .= '});'.$lnEnd;
            $html .= '</script>'.$lnEnd;
        }

        if (is_array($this->customTags[$position])) {
            foreach ($this->customTags[$position] as $custom) {
                $html .= $custom.$lnEnd;
            }
        }

        return $html;
    }

    protected function generateAttrib($attribs = [])
    {
        if (!is_array($attribs)) {
            return;
        }

        $attr = '';
        foreach ($attribs as $key => $value) {
            $attr .= $key.'="'.$value.'" ';
        }

        return $attr;
    }

    protected function assetic($files, $type)
    {
        $urls = [];

        foreach ($files as $key => $file) {
            $assetType = $this->parseInput($file);
            if ($assetType == 'http') {
                $urls[] = $file;
                unset($files[$key]);
            }
        }

        if (empty($files)) {
            return $urls;
        }

        $cachePath = path('pcache');
        $cache     = path('cache');

        $aw = new AssetWriter($cachePath);
        $am = new AssetManager();

        // Create the collection
        $collection = new AssetCollection();
        // Create the cache
        $cache = new FilesystemCache($cache);

        foreach ($files as $file) {
            $assetType = $this->parseInput($file);

            // Create the asset
            if ($assetType == 'file') {
                $asset = new FileAsset($file);
            } elseif ($assetType == 'glob') {
                $asset = new GlobAsset($file);
            } elseif ($assetType == 'http') {
                $asset = new HttpAsset($file);
            } elseif ($assetType == 'reference') {
                $asset = new AssetReference($am, substr($file, 1));
            }

            $filters = $this->getFilters($file);
            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    // Add the filter
                    $asset->ensureFilter($filter);
                }
            }

            // Cache the asset so we don't have to reapply filters on future page loads
            $cachedAsset = new AssetCache($asset, $cache);

            // Add the cached asset to the collection
            $collection->add($cachedAsset);
        }

        $name = md5(implode(',', $files)).'.'.$type;
        $file = $cachePath.$name;
        if (!file_exists($file) || $collection->getLastModified() > filemtime($file)) {
            $am->set($type, $collection);
            $am->get($type)->setTargetPath($name);
            $aw->writeManagerAssets($am);
        }

        $urls[] = path('cache', true).$name;

        return $urls;
    }

    protected function parseInput($file)
    {
        if ('@' == $file[0]) {
            return 'reference';
        }

        if (false !== strpos($file, '://') || substr($file, 0, 2) == '//') {
            return 'http';
        }

        if (false !== strpos($file, '*')) {
            return 'glob';
        }

        return 'file';
    }

    protected function getFilters($file)
    {
        list($ext, $version) = explode('?', strtolower(strrchr($file, '.')));
        unset($version);

        if ($ext == '.css') {
            return [
                new CssImportFilter(),
                new CssMinFilter(),
            ];
        }

        if ($ext == '.js') {
            return [
                new JSMinFilter(),
            ];
        }
    }
}
