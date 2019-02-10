# Metadata tab for the current image

Display the metadata of the currently shown image (canvas) in the left panel.

## Usage

* enable the plugin by including the CSS and the JavaScript (**after** loading Mirador):

```html
<link rel="stylesheet" type="text/css" href="<url to the plugin>/metadataTab.css" />
...
<script src="<url to the plugin>/metadataTab.js"></script>
```

* configure the tab containing the canvas metadata with the `metadataTab` configuration attribute in your Mirador configuration:

```js
var mirador = Mirador({
  ...
  metadataTab: {
  }
  ...
});
```

## Copyright

This plugin is based on the core code and on [CanvasLink](https://github.com/dbmdz/mirador-plugins/blob/master/CanvasLink) of the Digital Library/Munich Digitization Centre at the Bavarian State Library. It is licensed under MIT.
