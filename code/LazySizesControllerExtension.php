<?php

/**
 * LazySizesControllerExtension
 *
 * @author lekoala
 */
class LazySizesControllerExtension extends Extension
{
    protected static $_alreadyIncluded = false;

    function onAfterInit()
    {
        if (LazySizesImageExtension::config()->always_load) {
            self::requireLazySizes();
        }
    }

    /**
     * Helper method to include lazysizes. Prevent multiple inclusions.
     */
    static function requireLazySizes()
    {
        if (self::$_alreadyIncluded) {
            return false;
        }
        $basePath = LazySizesImageExtension::config()->js_path;

        Requirements::customScript(<<<JS
window.lazySizesConfig = {
    addClasses: true
};
JS
        );
        Requirements::javascript($basePath.'/lazysizes.min.js');
        self::$_alreadyIncluded = true;
    }

    /**
     * A Placehold.it helper
     *
     * @link http://placehold.it/
     * @param string $size
     * @param string $set
     * @param boolean $lazyload
     * @return string
     */
    function PlaceholdIt($size = '300x200', $set = null, $lazyload = true)
    {
        $html = '<img data-sizes="auto" src="http://placehold.it/'.$size.'"';
        if ($set) {
            $parts  = explode(',', $set);
            $srcset = array();
            foreach ($parts as $part) {
                $dim      = LazySizesImageExtension::parseDimensions($part);
                $srcset[] = 'http://placehold.it/'.$part.' '.$dim[0].'w';
            }

            $html .= ' data-srcset="'.implode(',', $srcset).'"';
        }
        if ($lazyload) {
            $html .= ' class="lazyload"';
        }
        $html .= '/>';
        return $html;
    }
}