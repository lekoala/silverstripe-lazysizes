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
        if ($config = $this->getConfigForSet($method)) {
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

        // Select which sizes are available
        $sizes = ArrayList::create();
        foreach ($config['sizes'] as $i => $arr) {
            if (!isset($arr['query'])) {
                throw new Exception("Responsive set $method does not have a 'query' element defined for size index $i");
            }
            if (!isset($arr['size'])) {
                throw new Exception("Responsive set $method does not have a 'size' element defined for size index $i");
            }
            list($width, $height) = $this->parseDimensions($arr['size']);
            $sizes->push(ArrayData::create(array(
                    'Image' => $this->owner->getFormattedImage($methodName,
                        $width, $height),
                    'Query' => $arr['query']
            )));
        }
        list($default_width, $default_height) = $this->parseDimensions($defaultDimensions);

        // Render template
        return $this->owner->customise(array(
                'Sizes' => $sizes,
                'DefaultImage' => $this->owner->getFormattedImage($methodName,
                    $default_width, $default_height)
            ))->renderWith('LazySizesImage');
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
        $width  = $size;
        $height = null;
        if (strpos($size, 'x') !== false) {
            return explode("x", $size);
        }
        return array($width, $height);
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
     * @return string
     */
    public function srcset($sizes, $lastOriginal = true)
    {
        $parts  = explode(',', $sizes);
        $srcset = array();

        $methodName = self::config()->default_method;

        foreach ($parts as $size) {
            $dim = self::parseDimensions($size);

            if ($lastOriginal == $dim[0]) {
                $srcset[] = $this->owner->Link().' '.$dim[0].'w';
            } else {
                $srcset[] = $this->owner->getFormattedImage($methodName,
                        $dim[0], $dim[1])->Link().' '.$dim[0].'w';
            }
        }

        return implode(',', $srcset);
    }
}