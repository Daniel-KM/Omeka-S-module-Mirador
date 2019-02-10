Mirador Viewer (module for Omeka S)
===================================

[Mirador Viewer] is a module for [Omeka S] that integrates [Mirador], an
advanced online viewer for images, so it can display books, images, maps, etc.
via the [IIIF] standard.

[Mirador] is an open-source, web based, multi-window image viewing platform with
the ability to zoom, display, compare and annotate images from around the world.
It's configurable and fully extensible via plugins.

It uses the resources of any [IIIF] compliant server. The full specification of
the "International Image Interoperability Framework" standard is supported
(level 2). If you don’t have an [IIPImage] server, Omeka S can be one! Just
install the module [IIIF Server].

It’s an alternative to the [Universal Viewer] or the lighter [Diva Viewer].


Installation
------------

The module uses an external js library [Mirador], so use the release zip to
install it, or use and init the source.

* From the zip

Download the last release [`Mirador.zip`] from the list of releases (the
master does not contain the dependency), and uncompress it in the `modules`
directory.

* From the source and for development:

If the module was installed from the source, rename the name of the folder of
the module to `Mirador`, and go to the root module, and run:

```
    composer install
```

The next times:

```
    composer update
```

Then install it like any other Omeka module.

If you don’t have an IIIF Server, install the module [IIIF Server].

If you need to display big images (bigger than 1 to 10 MB according to your
server and your network), use an external image server, or create tiles with [IIIF Server].
The tiling means that big images like maps and deep paintings, and any other
images, are converted into tiles in order to load and zoom them instantly.


Usage
-----

### Configuration

Only one option can be set in the main config (the manifest property, if any).

The other ones can be set differently for each site:

- in site settings via a json object, that will be merged with the default
  config of the viewer, and with the options set directly in theme, if any.
- via the theme of the site: copy file `view/common/helper/mirador.phtml` in
  your theme and customize it;
- via the theme of the site and the assets (`asset/vendor/mirador`).

The parameters used to config the viewer can be found in the [wiki], in the
details of the [api] and in the examples of the [tutorial].

The following placeholders can be used (without quote or double quotes):
- `__manifestUri__`: first manifest uri (or collection uri),
- `__canvasID__`, first canvas uri of the manifest.

For example, this params can be set to display an item in [Zen mode]:

```json
{
    "mainMenuSettings": {
        "show": false
    },
    "windowObjects": [
        {
            "loadedManifest": __manifestUri__,
            "canvasID": __canvasID__,
            "viewType": "ImageView",
            "displayLayout": false,
            "bottomPanel": false,
            "sidePanel": false,
            "annotationLayer": false,
            "canvasControls": {
                "annotations": false,
                "imageManipulation": {
                    "manipulationLayer": false
                }
            }
        }
    ]
}
```

This config is for item, not for collection. This example is for the site
setting because it uses placeholders, so it should be adapted if used in theme
in order to take the default parameters in account. The keys `id` and `data` are
automatically filled, but may be overridden too.


### Display

If the [IIIF Server] is installed, all resources of Omeka S are automatically
available by Mirador.

The viewer is always available at `http://www.example.com/item-set/{item-set id}/mirador`
and `http://www.example.com/item/{item id}/mirador`. Furthermore, it is
automatically embedded in "item-set/{id}" and "item/{id}" show and/or browse
pages. This can be disabled in the settings of the site. Finally, a block layout
is available to add the viewer in any standard page.

To embed Mirador somewhere else, just use the helper:

```php
    // Display the viewer with the specified item set.
    echo $this->mirador($itemSet);

    // Display the viewer with the specified item and specified options.
    // The options for Mirador are directly passed to the partial, so they are
    // available in the theme and set for the viewer.
    echo $this->mirador($item, $options);

    // Display multiple resources (items and/or item sets).
    echo $this->mirador($resources);
```


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitHub.


License
-------

This module is published under the [CeCILL v2.1] licence, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

In consideration of access to the source code and the rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software’s author, the holder of the economic rights, and the
successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or
developing or reproducing the software by the user are brought to the user’s
attention, given its Free Software status, which may make it complicated to use,
with the result that its use is reserved for developers and experienced
professionals having in-depth computer knowledge. Users are therefore encouraged
to load and test the suitability of the software as regards their requirements
in conditions enabling the security of their systems and/or data to be ensured
and, more generally, to use and operate it in the same conditions of security.
This Agreement may be freely reproduced and published, provided it is not
altered, and that no provisions are either added or removed herefrom.

[Mirador] is published under the [Apache 2] licence.


Copyright
---------

Widget [Mirador]:

* Copyright 2018 The Board of Trustees of the Leland Stanford Junior University

Module Mirador for Omeka S:

* Copyright Daniel Berthereau, 2018-2019

First version of this module was built for [Fachhochschule Nordwestschweiz],
University of Applied Sciences and Arts, Basel Academy of Music, Academy of Music,
[Schola Cantorum Basiliensis].


[Mirador Viewer]: https://github.com/Daniel-KM/Omeka-S-module-Mirador
[Mirador]: https://projectmirador.org
[Omeka S]: https://omeka.org/s
[Omeka]: https://omeka.org
[IIIF Server]: https://github.com/Daniel-KM/Omeka-S-module-IiifServer
[IIIF]: http://iiif.io
[IIPImage]: http://iipimage.sourceforge.net
[Universal Viewer]: https://github.com/Daniel-KM/Omeka-S-module-UniversalViewer
[Diva Viewer]: https://github.com/Daniel-KM/Omeka-S-module-Diva
[`Mirador.zip`]: https://github.com/Daniel-KM/Omeka-S-module-Mirador/releases
[iiif specifications]: http://iiif.io/api/
[jQuery extend]: https://api.jquery.com/jQuery.extend
[wiki]: https://github.com/ProjectMirador/mirador/wiki/Configuration-Guides
[api]: https://github.com/ProjectMirador/mirador/wiki/Complete-Configuration-API
[tutorial]: http://projectmirador.org/docs/docs/getting-started.html
[Zen mode]: https://github.com/ProjectMirador/mirador/wiki/Configuration-Guides#zen-mode
[module issues]: https://github.com/Daniel-KM/Omeka-S-module-Mirador/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Apache 2]: http://www.apache.org/licenses/LICENSE-2.0
[Fachhochschule Nordwestschweiz]: https://www.fhnw.ch
[Schola Cantorum Basiliensis]: https://www.fhnw.ch/en/about-fhnw/schools/music/schola-cantorum-basiliensis
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
