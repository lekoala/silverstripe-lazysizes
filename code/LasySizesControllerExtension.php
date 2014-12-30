<?php

/**
 * LasySizesControllerExtension
 *
 * @author lekoala
 */
class LasySizesControllerExtension extends Extension
{

    function onAfterInit()
    {
        self::requireLasySizes();
    }

    static function requireLasySizes()
    {
        Requirements::customScript(<<<JS
function __loadJS(u){var r = document.getElementsByTagName( "script" )[ 0 ], s = document.createElement( "script" );s.src = u;r.parentNode.insertBefore( s, r );}

window.lazySizesConfig = {
    addClasses: true
};

if(!window.HTMLPictureElement){
    __loadJS("lasysizes/javascript/respimage.min.js");
}
JS
        );
        Requirements::javascript(LASYSIZES_PATH.'/javascript/lazysizes.min.js');
    }
}