Mirador Viewer (module for Omeka S)
===================================

> __New versions of this module and support for Omeka S version 3.0 and above
> are available on [GitLab], which seems to respect users and privacy better
> than the previous repository.__

[Mirador Viewer] is a module for [Omeka S] that integrates [Mirador], an
advanced online viewer for images (version 2), audio and video (versions 3 and
4), so it can display books, images, maps, etc. via the [IIIF] standard. Common
plugins are included.

[Mirador] is an open-source, web based, multi-window image viewing platform with
the ability to zoom, display, compare and annotate images from around the world.
It's configurable and fully extensible via plugins. The version 2.7, 3.4.3 and
4.0 are available. They are served locally, without third piracy services (cdn),
so they are fully GDPR-compliant.

It uses the resources of any [IIIF] compliant server. The full specification of
the "International Image Interoperability Framework" standard is supported
(level 2). If you don’t have an IIIF-compatible image server, like [Cantaloupe]
or [IIP Image] server, Omeka S can be one! Just install the modules [IIIF Server]
and [Image Server].

It’s an alternative to the [Universal Viewer] or the lighter [Diva Viewer].

For an example, see [Corpus du Louvre] or [Gaffurius codices].


Installation
------------

Before installation, note that an IIIF server is required, unless all your media
are stored outside.

### Access to IIIF images

Mirador is based on IIIF, so the images should be available through an image
server compliant with this protocol. So for you own images, you need either
[Cantaloupe] or [IIP Image] or the module [Image Server].

With [Image Server], if you need to display big images (bigger than 1 to 10 MB
according to your server, your network, and your users), you need to create
tiles. The tiling means that big images like maps and deep paintings, and any
other images, are converted into tiles in order to load and zoom them instantly.
And if you use the image engine [vips] with the module [Vips], tiles can be
created in real time in most of the common cases.

### Installation of the module

See general end user documentation for [installing a module].

The module [Common] must be installed first.

If you have an old theme without resource blocks, you may install the optional
module [Blocks Disposition].

* Composer (recommended, requires Omeka [pull request #2432])

Install the module from the root of Omeka S:

```sh
composer require daniel-km/omeka-s-module-mirador
```

The module is automatically downloaded in `composer-addons/modules/` and ready
to enable in the admin interface.

* From the zip

Download the last release [Mirador.zip] from the list of releases, and
uncompress it in the `modules` directory. Rename the name of the folder of the
module to `Mirador`

* From the source and for development

If the module was installed from the source, rename the name of the folder of
the module to `Mirador`, go to the root of the module, and run:

```sh
composer install --no-dev
```

* For test

The module includes a comprehensive test suite with unit and functional tests.
Run them from the root of Omeka:

```sh
vendor/bin/phpunit -c modules/Mirador/phpunit.xml --testdox
```


Installation for development
----------------------------

Because compiling old Mirador versions with unmaintained but stable plugins is
more complex with the time, the libraries for Mirador are provided through a zip
since module version 3.4.9.

So you don't need to install npm, node.js or anything else to install and to use
the module.

The compilation is only needed if you want specific features of Mirador or if
you want to use the dev version.

### Version 2.7

The version 2.7 and its plugins are compiled to fix an old issue and is
installed automatically via composer.

The js contains a fix to list of images in fullscreen in Mirador 2.

The standard Mirador doesn’t allow to have the bottom sidebar (list of images)
in fullscreen, so it’s hard to browse. It’s not a bug, it’s a [feature].
To fix it without patching and recompilation, just run this replacement command
from the root of the module:

```sh
rpl \
    'toggleFullScreen:function(){OpenSeadragon.isFullScreen()?(this.element.find(".mirador-osd-fullscreen i").removeClass("fa-expand").addClass("fa-compress"),this.element.find(".mirador-osd-toggle-bottom-panel").hide(),this.eventEmitter.publish("SET_BOTTOM_PANEL_VISIBILITY."+this.id,!1)):(this.element.find(".mirador-osd-fullscreen i").removeClass("fa-compress").addClass("fa-expand"),this.element.find(".mirador-osd-toggle-bottom-panel").show(),this.eventEmitter.publish("SET_BOTTOM_PANEL_VISIBILITY."+this.id,!0))},' \
    'toggleFullScreen:function(){OpenSeadragon.isFullScreen()?(this.element.find(".mirador-osd-fullscreen i").removeClass("fa-expand").addClass("fa-compress")/*,this.element.find(".mirador-osd-toggle-bottom-panel").hide(),this.eventEmitter.publish("SET_BOTTOM_PANEL_VISIBILITY."+this.id,!1)*/):(this.element.find(".mirador-osd-fullscreen i").removeClass("fa-compress").addClass("fa-expand")/*,this.element.find(".mirador-osd-toggle-bottom-panel").show(),this.eventEmitter.publish("SET_BOTTOM_PANEL_VISIBILITY."+this.id,!0)*/)},' \
    asset/vendor/mirador/mirador.min.js
```

If wanted, you can fix this feature in the Mirador 2.7.0 source file too:
comment lines 42779, 42780, 42783, and 42784.

### Version 3.4.3 (deprecated)

Mirador 3 is no more recommended: Mirador 4 uses a modern approach of js and
avoids to compile and to bundle all plugins in the same package. Mirador 4 looks
visually the same; all plugins for Mirador 3 works for Mirador 4 with small
changes.

So, this explanation for Mirador 3 is useful only if you want to compile Mirador
yourself.

Mirador 3 is based on [react], a js framework managed by facebook, so a complex
install is required to manage it and plugins should be compiled and minified
with Mirador itself. Furthermore, the plugin [Annotations] is heavy, but may not
be used.

So, to simplify installation of Mirador 3 and plugins and to keep it as small as
possible, Mirador is managed as a separate repository [Mirador integration Omeka].

Development of a specific version of Mirador 3 requires a specific version of
node and npm (node 16 and npm 8), so use nvm. If you want to remove plugins or
to include new plugins from the bundle, update the files [vendor/projectmirador/mirador-integration/package.json]
and [vendor/projectmirador/mirador-integration/src/index.js].

First, copy the node files package.json and gulpfile.js from version 3.4.11 of
the module, because they were removed in version 3.4.12.

To compile, you will probably need to use argument "legacy-peer-deps":

```sh
npm install --legacy-peer-deps
```

In case of difficulties, you may downgrade the plugin mirador-annotation to
0.4.0 in the included package if compilation is broken, even with "legacy-peer-deps"
or "force".

See more information in the [included package]. You may have to fork the
repository and to set it in package.json.

See [official documentation about Mirador plugins (v3)].

```sh
# Install mirador-integration with composer, including dev.
composer install
# Compile the three versions of Mirador 3, minify and copy them in asset/vendor/mirador.
# gulp/gulp-cli can be used too.
# It will takes somes minutes.
npm install
# if gulp is not installed, run first `npm install gulp`
npx gulp
```

If it doesn't work, clone the repository [Mirador-integration-Omeka] somewhere,
follow its readme, build files with webpack, then copy build files inside
"asset/vendor/mirador".

### Mirador 4.x

Since module version 3.4.12, Mirador 4 uses EcmaScript (ES) modules with
[import maps] instead of pre-compiled webpack bundles. Plugins are loaded
individually by the browser, so adding or removing a plugin no longer requires
recompilation of the whole viewer.

The build uses [Vite] with Rollup code-splitting: shared dependencies (React,
MUI, emotion, redux, OpenSeadragon, etc.) are extracted into a single `vendor.js`
chunk. Each plugin and Mirador core import from `vendor.js` via relative imports,
ensuring the browser loads each dependency only once (React singleton, MUI
singleton).

Mirador 4.0.0 ships its `dist/mirador.es.js` with OpenSeadragon 5.0.1 inline-
vendored. It is improved with last version of OpenSeadragon to fix issues and to
improve performance.

All built files are served locally from `asset/vendor/mirador-esm/`, so no third
piracy CDN is needed and it is GDPR-compliant. The directory `asset/vendor/mirador/`
is kept as fallback for some old themes that used them, but will be removed in
a future version.

The bootstrap `asset/js/mirador-4.mjs` injects `osdConfig.drawer: 'canvas'`
as a default before each viewer is instantiated. OSD 6 defaults to
`drawer: 'auto'` which tries WebGL first; on cross-origin IIIF images
served without the proper CORS headers the texture upload silently
fails with `Error creating texture in WebGL.` and OSD falls back to
the canvas drawer anyway. Pinning the canvas drawer up front skips
that wasted texture allocation and silences the log noise. Site
admins can override the choice via
`mirador_config_item.osdConfig.drawer` (e.g. `'webgl'`, `'auto'`).

To compile Mirador v4, Node.js 20+ and npm 11+ are needed. The source files are
in `asset/src/` and the compiled files are stored in `asset/vendor/mirador-esm/`.
The dependencies, the core, and the plugins are compiled as separated files. For
the plugin annotation, its own dependencies are compiled with it. In the
compiled files, `mirador-2.js` is the core and `mirador.js` is the entry that
allows reexports.


```sh
npm install
npx vite build
```

To add a new plugin to the build:

1. Install the npm package:
    ```sh
    npm install new-mirador-plugin
    ```
2. Create the entry file `asset/src/plugin-new.js`:
    ```js
    export { default } from 'new-mirador-plugin';
    ```
    Note: some plugins use named exports instead of a default export. Check the
    plugin's source and adapt accordingly (e.g.
    `export { myPlugin as default } from 'new-mirador-plugin';`).
3. Add the entry in `vite.config.js` under `rollupOptions.input`:
    ```js
    'plugin-new': resolve(__dirname, 'asset/src/plugin-new.js'),
    ```
4. Rebuild:
    ```sh
    npx vite build
    ```
5. Register the plugin in `data/plugins/plugins.php` (label for the settings
   form) and in `data/plugins/plugins-esm.php` (ESM metadata with package name
   and entry path).
6. Publish a new release with the built files.


Usage
-----

The module uses multiple external js library, [Mirador] itself and its plugins,
in version 2.7 (light), 3.4.3 (deprecated) or 4.0 (same ui and settings names,
but modern and full featured), so use the release zip to install it, or use and
init the source.

For version 2.x and version 4.x, plugins are loaded individually. For version
3.x, plugins are bundled with the core of Mirador. So version 3.x is not
recommended.

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

#### Mirador 2.7

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
    "windowSettings": {
        "osd": {
            "maxZoomPixelRatio": 10
        }
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

The params above includes an option to allow deeper zoom: by default, the
underlying OpenSeadragon viewer limits zoom to 1.1× the native image resolution
(`maxZoomPixelRatio`). For high-res photographs, manuscripts, etc., it is
recommended to render up each image pixel to 10 screen pixels (omeka admin
viewer uses 100).

#### Mirador 3.4.3 (deprecated)

The parameters used to config the viewer can be found in in the [recipes] and in
the details of the file [settings.js (v3)].

***Warning***: The config should be json, not js, so use double quotes, remove
comments and trailing comma, etc. Check your json on a site such [jsonformatter.org].

For example, this params can be set to display an item in Zen mode, in French,
with upper menu bar and a pixel ratio of 10:

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
    "osdConfig": {
        "maxZoomPixelRatio": 10
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

#### Mirador 4.0

The settings are similar to 3.4.3 (for example for max zoom pixel ratio), but
there are some changes and new ones.

The parameters used to config the viewer can be found in in the [recipes] and in
the details of the file [settings.js].

***Warning***: The config should be json, not js, so use double quotes, remove
comments and trailing comma, etc. Check your json on a site such [jsonformatter.org].

The specific keys of each plugin can be added to the config too.

### Display

If the [IIIF Server] is installed, all resources of Omeka S are automatically
available by the viewer, else the url of the manifest should be set in the
configured property.

The viewer is always available at `http://www.example.com/item-set/{item-set id}/mirador`
and `http://www.example.com/item/{item id}/mirador`. Furthermore, it is
automatically embedded in "item-set/{id}" and "item/{id}" show and/or browse
pages. The placement can be configured in site settings with the option "Display
Mirador viewer": item show, item browse, item set browse. By default, only the
item show page is enabled.

In Omeka S v4.0+, you can use the block in the resource page theme options. When
this feature is used, the viewer is automatically skipped on item show to avoid
duplication. The placement option remains useful for browse pages and for themes
that do not use resource page blocks (Omeka S < v4.0).

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

#### Plugins for Mirador 3 (deprecated)

- [Annotations]: Note: only two backends are supported currently, local storage
  (inside browser persistent cache; it **requires a `https` site** for security),
  and [Annotot] (requires its endpoint).
- [Download]
- [Image Tools]
- [OCR Helper]
- [Ruler]
- [Share]
- [Text overlay]

#### Plugins for Mirador 4

Available plugins:

- [Annotations]: Note: only two backends are supported currently, local storage
  (inside browser persistent cache; it **requires a `https` site** for security),
  and [Annotot] (requires its endpoint).
- [Download]
- [Image Cropper v4]: port of [@dbmdz/mirador-imagecropper] (Mirador 2) to
  Mirador 4 (React/MUI).
- [Image Tools]
- [OCR Helper v4]: port of [@4eyes/mirador-ocr-helper] (Mirador 3) to
  Mirador 4. Self-contained: ships its own settings bubble (visibility,
  text selection, opacity, palette) and works without mirador-textoverlay.
  Bidirectional highlighting between the OCR panel and the OSD image:
  clicking a line in the panel highlights the matching zone in the
  image, and clicking a zone on the image scrolls and highlights the
  corresponding line in the panel.
- [Physical Ruler]: by S. Nakamura.
- [Rotation]: by S. Nakamura ([repository][Rotation Plugin]).
- [Share]
- [Sync Windows]: by S. Nakamura.
- [Text Overlay v4]: dbmdz [mirador-textoverlay] branch `mirador4`.
- [Zoom Percentage]: bundled in this module.

To add a custom plugin, see the compilation instructions in the Installation
section above.


Patches and contributions
-------------------------

Mirador 4 is still young (4.0.0 is the only release at the time of
writing) and several upstream plugins were originally written for
Mirador 3 / OSD 5. The bundle in `asset/vendor/mirador-esm/` is
therefore rebuilt from source against patched copies of Mirador,
OpenSeadragon and a handful of plugins. The patches are minimal and
all aim at upstream parity — most of them either align with already
merged upstream master commits or correspond to follow-up PRs to the
relevant projects.

### Mirador core (`mirador@4.0.0`)

- `src/config/settings.js`: default `filteredMotivations` aligned to
  the upstream master value `[]` (was the 4.0.0 hardcoded list, which
  silently dropped every annotation whose `motivation` was
  `supplementing` — for instance every IIIF Cookbook OCR transcription
  recipe). Already merged upstream, awaiting a 4.0.x release.

### OpenSeadragon (forced override `^6.0.2`)

- `src/tiledimage.js#_setCoverage`: replace the 6.0.0 `warn + return`
  guard with an auto-init `coverage[level] = {}`. The original guard
  fired on every `requestAnimationFrame` when `_blendTile` reached an
  opaque tile on a level the current draw pass had not visited (typical
  cross-fade case), and silently dropped the bookkeeping it was meant
  to protect. Patch and PR description are kept in `_pr-osd/` for
  upstream submission.

### Plugins ported in-tree (Mirador 3 → 4)

- `mirador-image-cropper` (port of [@dbmdz/mirador-imagecropper] 2.4.6):
  fully rewritten against MUI 7 / React 18-19, region selection layer
  above OSD, IIIF URL builder, client-side preview canvas, HTTP
  clipboard fallback, responsive layout (preview right / form left in
  landscape, preview bottom on mobile).
- `mirador-rotation` (S. Nakamura): `useTranslation` migrated to
  Mirador 4, panel relocated bottom-right, slider opens upward, icons
  unified.
- `mirador-physical-ruler` (S. Nakamura): rebundled against Mirador 4
  ESM exports.
- `mirador-sync-windows` (S. Nakamura):
  - `MiradorSyncWindowsMenuItem`: `useTranslation` injected (was
    relying on the Mirador 3 auto-injected `t` prop).
  - `MiradorSyncWindows`: `withTranslation()` HOC added; `withSize`
    (which called the React-19-removed `findDOMNode`) dropped because
    it was imported but never used.
  - `MiradorSyncWindowsButton`: `crypto.randomUUID()` (secure-context
    only) replaced by a `getRandomValues`-based fallback so group
    creation works on plain HTTP development hosts; `t` removed from
    `propTypes`.
  - `MiradorSyncWindows.handleZoomChange`: defensive `windowsAll || {}`
    guard, the original code crashed at first render when
    `state.windows` was not yet hydrated.
- `mirador-ocr-helper`:
  - `state/sagas.js`: imports converted from default to named
    (`{ ActionTypes, MiradorCanvas, getCanvases, … }`), the broken
    default-import shape made `takeEvery(undefined)` loop and froze
    Mirador init for >25 s.
  - `OverlaySettings.jsx`: `useTranslation` injected (was waiting on a
    `t` prop that Mirador 4 no longer auto-injects). Selectable-text
    toggle re-added (parity with mirador-textoverlay) so the plugin
    can stand alone.
  - `index.js`: `OverlaySettings` re-registered on
    `OpenSeadragonViewer`, with its own `mapStateToProps` /
    `mapDispatchToProps`. Previously the bubble relied on
    mirador-textoverlay to be loaded as well; the plugin is now
    self-contained.
  - `PageTextDisplay.jsx`: every line rectangle gets a
    `data-line-key="${x}_${y}"` attribute and `pointer-events: fill`
    so it remains clickable when transparent. New imperative
    `highlightLine(line, options)` styles the targeted rect and
    backs up its original inline fill so a later clear restores the
    background instead of falling through to the SVG default `black`.
    Click handler installed on the box container forwards the line
    to the parent via `props.onLineClick`. `renderOpacity` is now
    `visible ? opacity : 0` so background rects stay transparent
    when the wrapper is forced visible by a highlight.
  - `MiradorTextOverlay.jsx`: forwards
    `onLineClick → doHighlightLine(canvasId, line, 'image')` so a
    click on a rect highlights the same line in the OCR panel
    (initiator `'image'` triggers panel scroll). New
    `findHighlightedLine` / `syncHighlightedLine` push the highlight
    state to the right `PageTextDisplay` via ref. New
    `state.hasHighlight` forces `display: null` on the wrapper so
    the imperatively-styled rect is visible even when the global
    text overlay is hidden (typical case: only the OCR panel is
    open). `lastHighlight` instance field tracks transitions
    because the reducer mutates `line.isHighlighted` in place
    (so `prevProps` and `props` share the same line objects).
  - `MiradorOcrWindowViewer.jsx`: `LineWrap` highlight uses
    `Math.max(lineOpacity ?? 0.5, 0.5)` so the panel highlight stays
    visible even when the global text-overlay opacity is `0` (the
    new default).
  - `state/selectors.js`: defaults aligned with stand-alone use —
    `opacity: 0` (overlay text hidden by default) and
    `selectable: false` (text not selectable until the user toggles
    the new bubble button).
  - `state/sagas.js`: dedup with sibling plugins. Before fetching
    OCR, `fetchAndProcessOcr` checks the shared `texts` Redux slice;
    if a sibling has already populated `text` for the same source,
    it returns immediately. If the sibling is still in flight
    (`isFetching: true`), it waits via
    `race({receive: take(...), failure: take(...)})` instead of
    duplicating the network call. Useful when the host project
    loads both `mirador-ocr-helper` and `mirador-textoverlay`.
  - `src/locales.js`: French/Italian/German/English translations
    completed (several keys previously kept the German text in
    `fr`/`it` slots). Added `overlayTextSelectable` and the
    textoverlay-side keys (`textSelect`, `textVisible`, `textOpacity`,
    `opacityCurrentValue`) so the French label wins when the two
    plugins coexist.
- `mirador-textoverlay` (branch `mirador4`):
  - `state/sagas.js`: `payload?.textOverlay` and
    `getWindowConfig(...)?.textOverlay` optional-chained — the
    Mirador 4 store can deliver the slice as `undefined` while a
    window is being created.
  - `OverlaySettings.jsx`: same `useTranslation` migration as
    ocr-helper.

### In-tree plugins (this module)

- `plugin-zoom-percent.jsx`:
  - Scope the zoom-in button lookup to the per-window root
    (`document.getElementById(windowId)` then `.mirador-window`)
    instead of `ownerDocument`. The previous fallback picked the
    first matching button in the page, so in a multi-window
    workspace every plugin instance attached its overlay to
    window 0.
  - Suppress placeholder-stage values (e.g. the spurious
    `73300%` flashed at load): the percentage is computed only
    after the first OSD `tile-loaded` event, the flag is reset on
    every `open` (canvas swap), and any rounded value above
    `MAX_REASONABLE_PCT` (5000%) is dropped as a defensive
    fallback. Without these guards `viewportToImageZoom` was
    sampled while OSD still held the placeholder
    `getContentSize()` and produced absurd ratios.

### Mirador 3 minified bundles (legacy)

The three legacy Mirador 3 builds in `asset/vendor/mirador-3/*.min.js`
have a `getRequiredStatement` null guard (`return e && e.getValues()`)
to avoid a `TypeError` on manifests without a `requiredStatement`.

TODO
----

- [ ] Support module [Annotate] as backend for annotations.
- [x] Split Mirador plugins for dynamic lazy load via ES modules and import maps.
- [ ] Remove dependency to IiifServer for block.
- [ ] Remove old directory asset/vendor/mirador.
- [ ] Drop the local OSD `_setCoverage` patch once the matching PR
      lands upstream (see `_pr-osd/`).
- [ ] Drop the local Mirador `filteredMotivations` patch once 4.0.x
      ships with the master alignment.


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

For Mirador 2 and Mirador 3 plugins (legacy), each repository keeps its own
license; see the linked repositories above.

Bundled Mirador 4 plugins keep their upstream license:

* [Annotations] ([mirador-annotation-editor]): MIT
* [Download] ([mirador-dl-plugin]): Apache 2
* [Image Cropper v4] (port of [@dbmdz/mirador-imagecropper]): MIT
* [Image Tools] ([mirador-image-tools]): Apache 2
* [OCR Helper v4] (port of [@4eyes/mirador-ocr-helper]): MIT
* [Physical Ruler]: MIT
* [Rotation] ([mirador-rotation-plugin]): Apache 2
* [Share] ([mirador-share-plugin]): Apache 2
* [Sync Windows]: Apache 2
* [Text Overlay v4] ([mirador-textoverlay] branch `mirador4`): MIT
* [Zoom Percentage]: bundled with this module (CeCILL v2.1)


Copyright
---------

Widget [Mirador]:

* Copyright 2018-2026 The Board of Trustees of the Leland Stanford Junior University

Bundled Mirador 4 plugins:

* [Annotations] ([mirador-annotation-editor]): copyright Project Mirador contributors
* [Download] ([mirador-dl-plugin]): copyright Project Mirador contributors
* [Image Cropper] (Mirador 2): copyright Matthias Lindinger / Bavarian State Library (dbmdz). Mirador 4 port: copyright Daniel Berthereau, 2026.
* [Image Tools] ([mirador-image-tools]): copyright Project Mirador contributors
* [OCR Helper] (Mirador 3): copyright 4eyes GmbH. Mirador 4 port: copyright Daniel Berthereau, 2026.
* [Physical Ruler]: copyright Satoru Nakamura, 2024-2026.
* [Rotation] ([mirador-rotation-plugin]): copyright Satoru Nakamura, 2024-2026.
* [Share] ([mirador-share-plugin]): copyright Project Mirador contributors
* [Sync Windows]: copyright Satoru Nakamura, 2024-2026.
* [Text Overlay] ([mirador-textoverlay]): copyright Bavarian State Library / dbmdz, [Johannes Baiter] and contributors.
* [Zoom Percentage]: copyright Daniel Berthereau, 2026.

Module Mirador for Omeka S:

* Copyright Daniel Berthereau, 2018-2026

First version of this module was built for [Fachhochschule Nordwestschweiz],
University of Applied Sciences and Arts, Basel Academy of Music, Academy of Music,
[Schola Cantorum Basiliensis]. Many improvements were done for various projects.


[Mirador Viewer]: https://gitlab.com/Daniel-KM/Omeka-S-module-Mirador
[Mirador]: https://projectmirador.org
[Omeka S]: https://omeka.org/s
[Omeka]: https://omeka.org
[Corpus du Louvre]: https://corpus.louvre.fr
[Gaffurius codices]: https://www.gaffurius-codices.ch
[IIIF Server]: https://gitlab.com/Daniel-KM/Omeka-S-module-IiifServer
[Image Server]: https://gitlab.com/Daniel-KM/Omeka-S-module-ImageServer
[vips]: https://libvips.github.io/libvips
[Vips]: https://gitlab.com/Daniel-KM/Omeka-S-module-Vips
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
[recipes]: https://github.com/ProjectMirador/mirador/wiki/Configuration-Recipes
[settings.js]: https://github.com/ProjectMirador/mirador/blob/master/src/config/settings.js
[settings.js (v3)]: https://github.com/ProjectMirador/mirador/blob/v3.4.3/src/config/settings.js
[installing a module]: https://omeka.org/s/docs/user-manual/modules/#installing-modules
[Generic]: https://gitlab.com/Daniel-KM/Omeka-S-module-Generic
[Blocks Disposition]: https://gitlab.com/Daniel-KM/Omeka-S-module-BlocksDisposition
[pull request #2432]: https://github.com/omeka/omeka-s/pull/2432
[react]: https://reactjs.org
[Vite]: https://vite.dev
[import maps]: https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/script/type/importmap
[Mirador integration Omeka]: https://gitlab.com/Daniel-KM/Mirador-integration-Omeka
[Mirador-integration-Omeka]: https://gitlab.com/Daniel-KM/Mirador-integration-Omeka
[List of images in fullscreen]: #list-of-images-in-fullscreen-in-mirador-2
[vendor/projectmirador/mirador-integration/package.json]: https://gitlab.com/Daniel-KM/Mirador-integration-Omeka/-/blob/master/package.json
[vendor/projectmirador/mirador-integration/src/index.js]: https://gitlab.com/Daniel-KM/Mirador-integration-Omeka/-/blob/master/src/index.js
[included package]: https://gitlab.com/Daniel-KM/Mirador-integration-Omeka
[jsonformatter.org]: https://jsonformatter.org
[official documentation about Mirador plugins (v3)]: https://github.com/ProjectMirador/mirador/wiki/Mirador-3-plugins
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
[OCR Helper v4]: https://github.com/Daniel-KM/Omeka-S-module-Mirador
[@4eyes/mirador-ocr-helper]: https://www.npmjs.com/package/@4eyes/mirador-ocr-helper
[Ruler]: https://www.npmjs.com/package/mirador-ruler-plugin
[Share]: https://github.com/ProjectMirador/mirador-share-plugin
[mirador-share-plugin]: https://github.com/ProjectMirador/mirador-share-plugin
[Text overlay]: https://www.npmjs.com/package/mirador-textoverlay
[Text Overlay v4]: https://github.com/dbmdz/mirador-textoverlay/tree/mirador4
[mirador-textoverlay]: https://github.com/dbmdz/mirador-textoverlay
[mirador-annotation-editor]: https://github.com/SCA-IIIF/mirador-annotation-editor
[mirador-dl-plugin]: https://github.com/ProjectMirador/mirador-dl-plugin
[mirador-image-tools]: https://github.com/ProjectMirador/mirador-image-tools
[Image Cropper v4]: https://github.com/Daniel-KM/Omeka-S-module-Mirador
[@dbmdz/mirador-imagecropper]: https://github.com/dbmdz/mirador-plugins/tree/main/ImageCropper
[Physical Ruler]: https://github.com/nakamura196/mirador-physical-ruler
[mirador-physical-ruler]: https://github.com/nakamura196/mirador-physical-ruler
[Rotation]: https://github.com/nakamura196/mirador-rotation-plugin
[Rotation Plugin]: https://github.com/nakamura196/mirador-rotation-plugin
[mirador-rotation-plugin]: https://github.com/nakamura196/mirador-rotation-plugin
[Sync Windows]: https://github.com/nakamura196/mirador-sync-windows
[mirador-sync-windows]: https://github.com/nakamura196/mirador-sync-windows
[Zoom Percentage]: https://gitlab.com/Daniel-KM/Omeka-S-module-Mirador
[Johannes Baiter]: https://github.com/jbaiter
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
