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

Maintainer
==================
LeKoala - thomas@lekoala.be