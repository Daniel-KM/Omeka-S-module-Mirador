Mirador Viewer (module for Omeka S)
===================================

> __New versions of this module and support for Omeka S version 3.0 and above
> are available on [GitLab], which seems to respect users and privacy better
> than the previous repository.__

[Mirador Viewer] is a module for [Omeka S] that integrates [Mirador], an
advanced online viewer for images (version 2), audio and video (version 3), so
it can display books, images, maps, etc. via the [IIIF] standard. Common plugins
are included.

[Mirador] is an open-source, web based, multi-window image viewing platform with
the ability to zoom, display, compare and annotate images from around the world.
It's configurable and fully extensible via plugins. The version 2.7 and 3.0 are
available.

It uses the resources of any [IIIF] compliant server. The full specification of
the "International Image Interoperability Framework" standard is supported
(level 2). If you don’t have an IIIF-compatible image server, like [Cantaloupe]
or [IIP Image] server, Omeka S can be one! Just install the modules [IIIF Server]
and [Image Server].

It’s an alternative to the [Universal Viewer] or the lighter [Diva Viewer].

For an example, see [Corpus du Louvre].


Installation
------------

See general end user documentation for [installing a module].

First, if wanted, install the two optional modules [Generic] and [Blocks Disposition].

The module uses multiple external js library, [Mirador] itself and its plugins,
in version 2.7 or 3, so use the release zip to install it, or use and init the
source.

* From the zip

Download the last release [Mirador.zip] from the list of releases (the master
does not contain the dependency), and uncompress it in the `modules` directory.

* From the source and for development:

If the module was installed from the source, rename the name of the folder of
the module to `Mirador`, and go to the root module. Mirador 2 and Mirador 3 are
installed separately, so you can skip the one or the other if you are sure to
not use it. No check is done inside Omeka.

  - Install Mirador 2

```sh
# To clean previous development, **even** with --no-cache.
# Warning: it will remove directory "vendor".
rm -rf vendor && rm -rf asset/vendor

# Install mirador 2.7.0 in asset/vendor/mirador-2 and plugins in asset/vendor/mirador-2-plugins.
# The option "--no-cache" allows to have last versions.
composer install --no-dev --no-cache
```

  - Install Mirador 3

  Mirador 3 is based on [react], a js framework managed by facebook, so a
  complex install is required to manage it and plugins should be compiled and
  minified with Mirador itself. Furthermore, the plugin [Annotations] is heavy,
  but may not be used.

  So, to simplify installation of Mirador 3 and plugins and to keep it as small
  as possible, Mirador is managed as a separate repository [Mirador integration Omeka].

  This repository provides three versions of Mirador: vanilla Mirador, Mirador
  with common plugins, Mirador with all plugins. The choice is transparent
  inside Omeka and the appropriate bundle is included according to the selected
  plugins in main settings or in site settings.

  Development of a specific version of Mirador 3 requires npm. If you want to
  remove plugins or to include new plugins from the bundle, update the files
  [vendor/projectmirador/mirador-integration/package.json] and [vendor/projectmirador/mirador-integration/src/index.js].
  See more information in the [included package]. You may have to fork the
  repository and to set it in package.json. The file gulpfile.js inside the
  present module is used only as a shortcut to it.

  See [official documentation about Mirador plugins].

```sh
# Install mirador-integration with composer if no done above.
# To clean previous development, **even** with --no-cache.
# Warning: it will remove directory "vendor".
rm -rf vendor && rm -rf asset/vendor
composer install --no-dev --no-cache
# Compile the three versions of Mirador 3, minify and copy them in asset/vendor/mirador. gulp/gulp-cli can be used too.
# It will takes somes minutes.
npm install
npx gulp
```

  If it doesn't work, clone the repository [Mirador-integration-Omeka] somewhere,
  follow its readme, then copy build files inside "asset/vendor/mirador".

  Some plugins are not included in the bundle for various reasons, but they are
  listed in the file package.json under key "[unmanaged]".

* Access to IIIF images

Mirador is based on IIIF, so the images should be available through an image
server compliant with this protocol. So for you own images, you need either
[Cantaloupe] or [IIP Image] or the module [Image Server].

With [Image Server], if you need to display big images (bigger than 1 to 10 MB
according to your server, your network, and your users), you need to create
tiles. The tiling means that big images like maps and deep paintings, and any
other images, are converted into tiles in order to load and zoom them instantly.


Usage
-----

### Configuration

The url of the manifest of the items should be set inside the property specified
in the config form of the module. If you don’t have an IIIF Server, install the
module [IIIF Server].

The other ones can be set differently for each site:

- in site settings via the plugin select. See [below](#plugins).
- in site settings via a json object, that will be merged with the default
  config of the viewer, and with the options set directly in theme, if any.
- via the theme of the site: copy file `view/common/helper/mirador.phtml` in
  your theme and customize it;
- via the theme of the site and the assets (`asset/vendor/mirador`).

#### Mirador 2.7 (deprecated)

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

See below for a fix to get the [list of images in fullscreen].

#### Mirador 3.0

The parameters used to config the viewer can be found in in the [recipes] and in
the details of the file [settings.js].

***Warning***: The config should be json, not js, so use double quotes, remove
comments and trailing comma, etc. Check your json on a site such [jsonformatter.org].

For example, this params can be set to display an item in Zen mode, in French,
with upper menu bar:

```json
{
    "language": "fr",
    "window": {
        "allowClose": false,
        "allowFullscreen": true,
        "allowMaximize": false,
        "allowTopMenuButton": true,
        "allowWindowSideBar": true,
        "sideBarPanel": "info",
        "defaultSideBarPanel": "attribution",
        "sideBarOpenByDefault": false,
        "defaultView": "single",
        "forceDrawAnnotations": false,
        "hideWindowTitle": true,
        "highlightAllAnnotations": false,
        "showLocalePicker": true,
        "sideBarOpen": false,
        "switchCanvasOnSearch": true,
        "panels": {
            "info": true,
            "attribution": true,
            "canvas": true,
            "annotations": true,
            "search": true,
            "layers": true
        }
    },
    "thumbnailNavigation": {
        "defaultPosition": "off",
        "displaySettings": true
    },
    "workspace": {
        "showZoomControls": true,
        "type": "mosaic",
        "allowNewWindows": false,
        "isWorkspaceAddVisible": false
    },
    "workspaceControlPanel": {
        "enabled": false
    }
}
```

### Display

If the [IIIF Server] is installed, all resources of Omeka S are automatically
available by the viewer, else the url of the manifest should be set in the
configured property.

The viewer is always available at `http://www.example.com/item-set/{item-set id}/mirador`
and `http://www.example.com/item/{item id}/mirador`. Furthermore, it is
automatically embedded in "item-set/{id}" and "item/{id}" show and/or browse
pages. This can be disabled via the module [Blocks Disposition] for each site.
Finally, a block layout is available to add the viewer in any standard page.

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

### Plugins

Plugins add small features to the Mirador viewer.

Some plugins require json options to work. Some plugins may not work with the
integrated version of Mirador. Cross compatibility and options has not been
checked, so add them one by one and only the needed ones.

To add and manage a new plugin automatically, fill the file `data/plugins/plugins.php`
and the file `view/common/helper/mirador-plugins.phtml` and the respective ones
for Mirador v2, `data/plugins/plugins-mirador-2.php` and `view/common/helper/mirador-2-plugins.phtml`.

#### Plugins for Mirador 2

- [Crosslink]
- [DBMDZ]: Plugins of the Digital Library / Munich Digitization Centre at the Bavarian State Library
- [Disable-zoom]
- [Drag-n-drop link]
- [Jump-to-page]
- [LDN]
- [Metadata]
- [Metadata Tab]
- [Ruler v2]
- [Share workspace]
- [Sync windows]
- [UCD]: Plugins of the University College Dublin

#### Plugins for Mirador 3

- [Annotations]: Note: only two backends are supported currently, local storage
  (inside browser persistent cache; it **requires a `https` site** for security),
  and [Annotot] (requires its endpoint).
- [Download]
- [Image Tools]
- [OCR Helper]
- [Ruler]
- [Share]
- [Text overlay]


List of images in fullscreen in Mirador 2
-----------------------------------------

This fix is needed only for Mirador 2.

The standard Mirador doesn’t allow to have the bottom sidebar (list of images)
in fullscreen, so it’s hard to browse. It’s not a bug, it’s a [feature].
To fix it without patching and recompilation, just run this replacement command
from the root of the module:

```sh
rpl 'toggleFullScreen:function(){OpenSeadragon.isFullScreen()?(this.element.find(".mirador-osd-fullscreen i").removeClass("fa-expand").addClass("fa-compress"),this.element.find(".mirador-osd-toggle-bottom-panel").hide(),this.eventEmitter.publish("SET_BOTTOM_PANEL_VISIBILITY."+this.id,!1)):(this.element.find(".mirador-osd-fullscreen i").removeClass("fa-compress").addClass("fa-expand"),this.element.find(".mirador-osd-toggle-bottom-panel").show(),this.eventEmitter.publish("SET_BOTTOM_PANEL_VISIBILITY."+this.id,!0))},' 'toggleFullScreen:function(){OpenSeadragon.isFullScreen()?(this.element.find(".mirador-osd-fullscreen i").removeClass("fa-expand").addClass("fa-compress")/*,this.element.find(".mirador-osd-toggle-bottom-panel").hide(),this.eventEmitter.publish("SET_BOTTOM_PANEL_VISIBILITY."+this.id,!1)*/):(this.element.find(".mirador-osd-fullscreen i").removeClass("fa-compress").addClass("fa-expand")/*,this.element.find(".mirador-osd-toggle-bottom-panel").show(),this.eventEmitter.publish("SET_BOTTOM_PANEL_VISIBILITY."+this.id,!0)*/)},' asset/vendor/mirador/mirador.min.js
```

If wanted, you can fix this feature in the Mirador 2.7.0 source file too:
comment lines 42779, 42780, 42783, and 42784.


TODO
----

- [ ] Support module [Annotate] as backend for annotations.
- [ ] Split Mirador plugins for dynamic lazy load (see https://v4.webpack.js.org/guides/code-splitting/).


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitLab.


License
-------

This module is published under the [CeCILL v2.1] license, compatible with
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

[Mirador] is published under the [Apache 2] license.
Each Mirador plugin has a license. See each repository for more information.


Copyright
---------

Widget [Mirador]:

* Copyright 2018 The Board of Trustees of the Leland Stanford Junior University

Module Mirador for Omeka S:

* Copyright Daniel Berthereau, 2018-2021

First version of this module was built for [Fachhochschule Nordwestschweiz],
University of Applied Sciences and Arts, Basel Academy of Music, Academy of Music,
[Schola Cantorum Basiliensis].


[Mirador Viewer]: https://gitlab.com/Daniel-KM/Omeka-S-module-Mirador
[Mirador]: https://projectmirador.org
[Omeka S]: https://omeka.org/s
[Omeka]: https://omeka.org
[Corpus du Louvre]: https://corpus.louvre.fr/
[IIIF Server]: https://gitlab.com/Daniel-KM/Omeka-S-module-IiifServer
[Image Server]: https://gitlab.com/Daniel-KM/Omeka-S-module-ImageServer
[IIIF]: http://iiif.io
[Cantaloupe]: https://cantaloupe-project.github.io
[IIP Image]: http://iipimage.sourceforge.net
[Universal Viewer]: https://gitlab.com/Daniel-KM/Omeka-S-module-UniversalViewer
[Diva Viewer]: https://gitlab.com/Daniel-KM/Omeka-S-module-Diva
[Mirador.zip]: https://gitlab.com/Daniel-KM/Omeka-S-module-Mirador/-/releases
[iiif specifications]: http://iiif.io/api/
[jQuery extend]: https://api.jquery.com/jQuery.extend
[wiki]: https://github.com/ProjectMirador/mirador/wiki/Configuration-Guides
[api]: https://github.com/ProjectMirador/mirador/wiki/Complete-Configuration-API
[tutorial]: http://projectmirador.org/docs/docs/getting-started.html
[recipes]: https://github.com/ProjectMirador/mirador/wiki/M3-Configuration-Recipes
[settings.js]: https://github.com/ProjectMirador/mirador/blob/master/src/config/settings.js
[Installing a module]: http://dev.omeka.org/docs/s/user-manual/modules/#installing-modules
[Generic]: https://gitlab.com/Daniel-KM/Omeka-S-module-Generic
[Blocks Disposition]: https://gitlab.com/Daniel-KM/Omeka-S-module-BlocksDisposition
[react]: https://reactjs.org
[Mirador integration Omeka]: https://gitlab.com/Daniel-KM/Mirador-integration-Omeka
[List of images in fullscreen]: #list-of-images-in-fullscreen-in-mirador-2
[vendor/projectmirador/mirador-integration/package.json]: https://gitlab.com/Daniel-KM/Mirador-integration-Omeka/-/blob/master/package.json
[vendor/projectmirador/mirador-integration/src/index.js]: https://gitlab.com/Daniel-KM/Mirador-integration-Omeka/-/blob/master/src/index.js
[included package]: https://gitlab.com/Daniel-KM/Mirador-integration-Omeka
[jsonformatter.org]: https://jsonformatter.org
[official documentation about Mirador plugins]: https://github.com/ProjectMirador/mirador/wiki/Mirador-3-plugins
[Annotate]: https://gitlab.com/Daniel-KM/Omeka-S-module-Annotate
[Annotot]: https://rubygems.org/gems/annotot
[included repository]: https://gitlab.com/Daniel-KM/Omeka-S-module-Mirador/-/blob/master/mirador-integration/README.md
[Crosslink]: https://github.com/ArchiveLabs/mirador-crosslink
[dbmdz]: https://github.com/dbmdz/mirador-plugins
[Disable-zoom]: https://github.com/UCLALibrary/mirador-disable-zoom
[Drag-n-drop link]: https://github.com/2SC1815J/mirador-dragndrop-link-plugin
[jump-to-page]: https://github.com/sul-dlss/mirador-jump-to-page
[ldn]: https://github.com/jeffreycwitt/mirador-ldn-plugin
[Metadata]: https://github.com/jazahn/mirador-metadata
[Metadata Tab]: https://gitlab.com/Daniel-KM/Mirador-plugin-MetadataTab
[Ruler v2]: https://github.com/UCLALibrary/mirador-ruler
[Share workspace]: https://github.com/UCLALibrary/mirador-share-workspace
[Sync windows]: https://github.com/UCLALibrary/mirador-sync-windows
[ucd]: https://github.com/jbhoward-dublin/mirador-plugins-ucd
[Annotations]: https://github.com/ProjectMirador/mirador-annotations
[Download]: https://github.com/ProjectMirador/mirador-dl-plugin
[Image Tools]: https://github.com/ProjectMirador/mirador-image-tools
[OCR Helper]: https://www.npmjs.com/package/@4eyes/mirador-ocr-helper
[Ruler]: https://www.npmjs.com/package/mirador-ruler-plugin
[Share]: https://github.com/ProjectMirador/mirador-share-plugin
[Text overlay]: https://www.npmjs.com/package/mirador-textoverlay
[Zen mode]: https://github.com/ProjectMirador/mirador/wiki/Configuration-Guides#zen-mode
[feature]: https://github.com/ProjectMirador/mirador/pull/1235
[module issues]: https://gitlab.com/Daniel-KM/Omeka-S-module-Mirador/-/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Apache 2]: http://www.apache.org/licenses/LICENSE-2.0
[Fachhochschule Nordwestschweiz]: https://www.fhnw.ch
[Schola Cantorum Basiliensis]: https://www.fhnw.ch/en/about-fhnw/schools/music/schola-cantorum-basiliensis
[GitLab]: https://gitlab.com/Daniel-KM
[Daniel-KM]: https://gitlab.com/Daniel-KM "Daniel Berthereau"
