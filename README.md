Silverstripe Lazysizes module
==================
Integrate https://github.com/aFarkas/lazysizes and https://github.com/aFarkas/respimage
to Silverstripe.

Code heavily inspired by https://github.com/heyday/silverstripe-responsive-images

All-in-one solution for:
- Lazy loading images
- Responsive images
- Placeholder images (using placehold.it)

Respimages is only loaded if the browser does not support native syntax.

Plugins
==================
This modules has already pre built plugins into a consolidated js file.

Plugins available by default are:

Lasysizes:
- unveilhooks
- print
- bgset
- include

Respimages:
- oldie
- intrinsic
- mutation

You can also define your own path to the libraries by adjusting the following path

	LazySizesImageExtension:
		js_path: 'lazysizes/javascript'

LazyLoading
==================

If you simply want to lazy load your image, you can use any of three default
methods which specify no additional sizes.

Example:

	$Image.Lazy(720x250)
	$Image.LazyCrop(720x250)
	$Image.LazyCropFocus(720x250)

Responsive sets
==================

You can define your own responsive sets. You can see the bundled configuration
for the ResponsiveDefault set which set 4 breakpoints.
You can define your own sets, change sizes, method and default size to be used.

All sets use lazysizes data-sizes="auto" by default, meaning that the width
of the parent is used to determine which size needs to be loaded.

Retina is also supported.

Config example:

	LazySizesImageExtension:
		ResponsiveDefault:
			sizes: [320x213 320w,640x426 640w,960x639 960w,1280x852 1280w]
		ResponsiveDefaultSquare:
			sizes: [320x320 320w,640x640 640w,960x960 960w,1280x1280 1280w]
		ResponsiveDefaultRetina:
			sizes: [400x300 1x, 800x600 2x]

Usage example in template:

	$Image.ResponsiveDefault

Using placeholders
==================

The controller extension provides a helper method to easily create placeholders
using PlaceholdIt. You can define a base size and a src set.

Example:

	$PlaceholdIt(175x75,'200x100,500x300,800x400')

SrcSet
==================

Sometimes, you just need to output a list of srcset paths (for instance, to define
a bgset). The last element of the set is never resized (always using the original image at maximum size).
You can prevent this from happening by setting the second argument to false.

Example:

	<body id="$ClassName" class="typography lazyload" data-bgset="$SiteConfig.RandomBackgroundImage.SrcSet('480,800')">

Compatibility
==================
Tested with 3.1

Installation
==================

composer require lekoala/silverstripe-lazysizes

Maintainer
==================
LeKoala - thomas@lekoala.be