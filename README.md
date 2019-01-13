Based on [Universal Viewer] module for Omeka S

Installation
------------
The module uses an external library [Mirador], so use the release zip to
install it, or use and init the source.

* From the zip

Download the last release [`Mirador.zip`] from the list of releases (the
master does not contain the dependency), and uncompress it in the `modules`
directory.

* From the source and for development:

If the module was installed from the source, rename the name of the folder of
the module to `Mirador`, and go to the root of the module, and run:

```
    composer install
```

The next times:

```
    composer update
```

Usage
-----

If the [IIIF Server] is installed, all resources of Omeka S are automatically
available by the Mirador Viewer.

The viewer is always available at `http://www.example.com/item-set/{item-set id}/play-mirador`
and `http://www.example.com/item/{item id}/play-mirador`. Furthermore, it is
automatically embedded in "item-set/{id}" and "item/{id}" show and/or browse
pages. This can be disabled in the settings of the site. Finally, a block layout
is available to add the viewer in any standard page.

To embed the Mirador Viewer somewhere else, just use the helper:

```php
    // Display the viewer with the specified item set.
    echo $this->miradorViewer($itemSet);

    // Display the viewer with the specified item and specified options.
    echo $this->miradorViewer($item, array(
        'class' => 'my-class',
        'style' => 'display: block; width: 90%; height: 600px; margin: 1em 5%; position: relative;',
        'config' => 'https://example.com/my/specific/config.json',
    ));

    // Display multiple resources (items and/or item sets).
    echo $this->miradorViewer($resources);
```

[Universal Viewer]: https://github.com/Daniel-KM/Omeka-S-module-UniversalViewer
[Mirador]: https://github.com/ProjectMirador/mirador
[Omeka S]: https://omeka.org/s
[Omeka]: https://omeka.org
[IIIF Server]: https://github.com/Daniel-KM/Omeka-S-module-IiifServer
[IIIF]: http://iiif.io
