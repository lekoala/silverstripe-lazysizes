<?php

/**
 * LazySizesImageExtension
 *
 * @author lekoala
 */
class LazySizesImageExtension extends DataExtension
{
    /**
     * @var array
     */
    protected static $_responsiveSetsCache = null;

    /**
     * @var Config_ForClass A cached copy of the config object for LazySizesImageExtension
     */
    protected static $_configCache = null;

    /**
     * Config accessor
     * 
     * @return Config_ForClass
     */
    public static function config()
    {
        if (!self::$_configCache) {
            self::$_configCache = Config::inst()->forClass(__CLASS__);
        }
        return self::$_configCache;
    }

    /**
     * A wildcard method for handling responsive sets as template functions,
     * e.g. $MyImage.ResponsiveSet1
     *
     * @param string $method The method called
     * @param array $args The arguments passed to the method
     * @return SSViewer
     */
    public function __call($method, $args)
    {
        $config = $this->getConfigForSet($method);
        if ($config !== false) {
            return $this->createResponsiveSet($config, $args, $method);
        }
    }

    /**
     * Requires the necessary JS and sends the required HTML structure to the template
     * for a responsive image set
     *
     * @param array $config The configuration of the responsive image set from the config
     * @param array $args The arguments passed to the responsive image method, e.g. $MyImage.ResponsiveSet1(800x600)
     * @param string $method The method, or responsive image set, to generate
     * @return SSViewer
     */
    protected function createResponsiveSet($config, $args, $method)
    {
        LazySizesControllerExtension::requireLazySizes();

        if (!isset($config['sizes']) || !is_array($config['sizes'])) {
            throw new Exception("Responsive set $method does not have sizes defined in its config.");
        }

        // Resolve size
        if (isset($args[0])) {
            $defaultDimensions = $args[0];
        } elseif (isset($config['default_size'])) {
            $defaultDimensions = $config['default_size'];
        } else {
            $defaultDimensions = self::config()->default_size;
        }

        // Resolve method name
        if (isset($args[1])) {
            $methodName = $args[1];
        } elseif (isset($config['method'])) {
            $methodName = $config['method'];
        } else {
            $methodName = self::config()->default_method;
        }

        $srcset = $this->owner->srcset($config['sizes'], false, $methodName);

        list($default_width, $default_height) = $this->parseDimensions($defaultDimensions);

        // Render template
        $template = 'LazySizesImage';

        // If we have a srcset, use a template according to pattern
        if (!empty($srcset)) {
            $template .= ucfirst(self::config()->pattern);
        }

        // When using lqip, render a low res image
        $srclqip = '';
        if (self::config()->pattern == 'lqip') {
            $lqip    = self::config()->lqip_multiplier;
            $srclqip = $this->owner->getFormattedImage($methodName,
                    $default_width * $lqip, $default_height * $lqip)->Link();
        }

        return $this->owner->customise(array(
                'ImageSrcSet' => $srcset,
                'SrcLqip' => $srclqip,
                'DefaultImage' => $this->owner->getFormattedImage($methodName,
                    $default_width, $default_height)
            ))->renderWith($template);
    }

    /**
     * Look for the config in the responsiveSetsCache array
     *
     * @param string $setName The name of the responsive image set to get
     * @return array
     */
    protected function getConfigForSet($setName)
    {
        $sets = $this->getResponsiveSets();
        if (isset($sets[strtolower($setName)])) {
            return $sets[strtolower($setName)];
        }
        return false;
    }

    /**
     * An accessor for $_responsiveSetsCache. Stores cache if not set
     *
     * @param boolean $keys
     * @return array
     */
    protected function getResponsiveSets($keys = false)
    {
        if (!self::$_responsiveSetsCache) {
            $list = array();
            foreach (self::config()->sets as $set => $conf) {
                $list[strtolower($set)] = $conf;
            }
            self::$_responsiveSetsCache = $list;
        }
        if ($keys) {
            return array_keys(self::$_responsiveSetsCache);
        }
        return self::$_responsiveSetsCache;
    }

    /**
     * Parses a string such as "400" or "400x600" and returns width and height values
     *
     * @param string $size The string to parse
     * @return array
     */
    public static function parseDimensions($size)
    {
        $trigger = null;
        if ($pos     = strpos($size, ' ') !== false) {
            $size = explode(' ', $size);
            $trigger = $size[1];
            $size    = $size[0];
        }
        $width  = $size;
        $height = null;
        if (strpos($size, 'x') !== false) {
            $size = explode("x", $size);
            $width = $size[0];
            $height = $size[1];
        }
        if (!$trigger) {
            $trigger = $width.'w';
        }
        return array($width, $height, $trigger);
    }

    /**
     * Defines all the methods that can be called in this class
     *
     * @return array
     */
    public function allMethodNames()
    {
        $methods = array('createresponsiveset', 'srcset');
        return array_merge($methods, $this->getResponsiveSets(true));
    }

    /**
     * Helper method to return a src set
     *
     * @param string $sizes
     * @param boolean $lastOriginal
     * @param string $methodName
     * @return string
     */
    public function srcset($sizes, $lastOriginal = true, $methodName = null)
    {
        if (is_array($sizes)) {
            $parts = $sizes;
        } else {
            $parts = explode(',', $sizes);
        }

        $srcset = array();

        if ($methodName === null) {
            $methodName = self::config()->default_method;
        }

        $lastElement = null;
        if ($lastOriginal) {
            $lastElement = end($parts);
            reset($parts);
        }

        foreach ($parts as $size) {
            $dim = self::parseDimensions($size);
            if ($lastElement == $dim[0]) {
                $srcset[] = $this->owner->Link().' '.$dim[2];
            } else {
                $srcset[] = $this->owner->getFormattedImage($methodName,
                        $dim[0], $dim[1])->Link().' '.$dim[2];
            }
        }

        return implode(',', $srcset);
    }
}
