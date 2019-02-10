/*
 * Display the metadata of the current canvas (image) in the left sidebar.
 *
 * Contains code adapted from
 * - metadataView.js (to display the list of metadata from the manifest)
 * - canvasLink.js (for the event handlers).
 */
var MetadataTab = {
    /* options of the plugin */
    options: {
        metadataListingCls: 'metadata-listing',
    },

    /* all of the needed locales */
    locales: {
        'en': {
            'image-metadata': 'Meta',
            'no-image-metadata': 'No metadata for this image',
        },
        'fr': {
            'image-metadata': 'Méta',
            'no-image-metadata': 'Pas de métadonnées pour cette image',
        },
    },

    metadataTabTemplate: Mirador.Handlebars.compile([
        '<li class="tab metadataTab" data-tabid="metadataTab">{{t "image-metadata"}}</li>'
    ].join('')),

    tocTabTemplate: Mirador.Handlebars.compile([
        '<li class="tab tocTab" data-tabid="tocTab">{{t "tabTitleIndex"}}</li>'
    ].join('')),

    emptyTemplate: Mirador.Handlebars.compile([
        '<li class="leaf-item open">',
        '<h2><span>{{t "no-image-metadata"}}</span></h2>',
        '</li>',
    ].join('')),

    // Same template than metadataView.
    template: Mirador.Handlebars.compile([
        '<div class="sub-title">{{t "details"}}:</div>',
        '<div class="{{metadataListingCls}}">',
        '{{#each details}}',
        '<div class="metadata-item"><div class="metadata-label">{{label}}:</div><div class="metadata-value">{{{value}}}</div></div>',
        '{{/each}}',
        '</div>',
        '<div class="sub-title">{{t "rights"}}:</div>',
        '{{#if rights}}',
        '<div class="{{metadataListingCls}}">',
        '{{#each rights}}',
        '<div class="metadata-item {{identifier}}"><div class="metadata-label">{{label}}:</div><div class="metadata-value">{{{value}}}</div></div>',
        '{{/each}}',
        '{{#if logo}}',
        '<div class="metadata-item"><div class="metadata-label">{{t "logo"}}:</div><img class="metadata-logo" src="{{logo}}"/></div>',
        '{{/if}}',
        '</div>',
        '{{else}}',
        '<div class="{{metadataListingCls}}">',
        '<div class="metadata-item"><div class="metadata-label">{{t "rightsStatus"}}:</div><div class="metadata-value">{{t "unspecified"}}</div></div>',
        '</div>',
        '{{/if}}',
        '{{#if links}}',
        '<div class="sub-title">{{t "links"}}:</div>',
        '<div class="{{metadataListingCls}}">',
        '{{#each links}}',
        '<div class="metadata-item {{identifier}}"><div class="metadata-label">{{label}}:</div><div class="metadata-value">{{{value}}}</div></div>',
        '{{/each}}',
        // '{{#if relatedLinks}}',
        //   '<dt>{{label}}:</dt><dd>{{{value}}}</dd>',
        // '{{/if}}',
        '</dl>',
        '{{/if}}',
    ].join(''), {
        noEscape: true
    }),

    /* Copy of all helpers from metadataView to build the list of metadata.*/
    // TODO Avoid to copy the helpers of metadataView in imageMetadata.

    // Base code from https://github.com/padolsey/prettyprint.js. Modified to fit Mirador needs
    stringifyObject: function(obj, nestingMargin) {
        var type = typeof obj,
            _this = this,
            str,
            first = true,
            increment = 15,
            delimiter = '<br/>';

        if (obj instanceof RegExp) {
            return '/' + obj.source + '/';
        }

        if (typeof nestingMargin === 'undefined') {
            nestingMargin = 0;
        }

        if (obj instanceof Array) {
            str = '[ ';
            jQuery.each(obj, function(i, item) {
                str += (i === 0 ? '' : ', ') + _this.stringifyObject(item, nestingMargin + increment);
            });
            return str + ' ]';
        }

        if (typeof obj === 'object' && obj['@type'] === 'sc:Collection') {
            var collectionUrl = obj['@id'];
            var collectionLabel = obj.label || collectionUrl;
            return '<a href="' + collectionUrl + '" target="_blank">' + collectionLabel + '</a>';
        } else if (typeof obj === 'object') {
            str = '<div style="margin-left:' + nestingMargin + 'px">';
            for (var i in obj) {
                if (obj.hasOwnProperty(i)) {
                    str += (first ? '' : delimiter) + i + ': ' + _this.stringifyObject(obj[i], nestingMargin + increment);
                    first = false;
                }
            }

            return str + '</div>';
        }
        return obj.toString();
    },

    stringifyRelated: function(obj) {
        var _this = this,
            str,
            next,
            label,
            format;
        if (obj instanceof Array) {
            str = '';
            jQuery.each(obj, function(i, item) {
                next = _this.stringifyRelated(item);
                if (next !== '') str += (i === 0 ? '' : '<br/>') + next;
            });
            return str;
        }

        if (typeof obj === 'object' && '@id' in obj) {
            label = ('label' in obj) ? obj.label : obj['@id'];
            format = ('format' in obj && obj.format !== 'text/html') ? '(' + obj.format + ')' : '';
            return '<a href="' + obj['@id'] + '"  target="_blank">' + label + '</a> ' + format;
        }

        return _this.addLinksToUris(obj.toString());
    },

    getMetadataDetails: function(jsonLd) {
        var mdList = [{
                label: i18next.t('label'),
                value: '<b>' + (Mirador.JsonLd.getTextValue(jsonLd.label) || '') + '</b>'
            },
            {
                label: i18next.t('description'),
                value: Mirador.JsonLd.getTextValue(jsonLd.description) || ''
            }
        ];

        if (jsonLd.metadata) {
            value = "";
            label = "";
            jQuery.each(jsonLd.metadata, function(index, item) {
                label = Mirador.JsonLd.getTextValue(item.label);
                value = Mirador.JsonLd.getTextValue(item.value);
                mdList.push({
                    label: label,
                    value: value
                });
            });
        }

        return mdList;
    },

    getMetadataRights: function(jsonLd) {
        return [{
            identifier: 'license',
            label: i18next.t('license'),
            value: jsonLd.license || ''
        }, {
            identifier: 'attribution',
            label: i18next.t('attribution'),
            value: Mirador.JsonLd.getTextValue(jsonLd.attribution) || ''
        }];
    },

    getMetadataLinks: function(jsonLd) {
        // #414
        return [{
            identifier: 'related',
            label: i18next.t('related'),
            value: this.stringifyRelated(jsonLd.related || '')
        }, {
            identifier: 'seeAlso',
            label: i18next.t('seeAlso'),
            value: this.stringifyRelated(jsonLd.seeAlso || '')
        }, {
            // TODO Add the links of the current image (unlike main manifest, they may be uri, not url).
            //  identifier: 'manifest',
            //  label: i18next.t('manifest'),
            //  value: this.stringifyRelated(jsonLd['@id'] || '')
            //}, {
            identifier: 'within',
            label: i18next.t('within'),
            value: this.getWithin(jsonLd.within || '')
        }];
    },

    getWithin: function(within) {
        if (typeof within === 'object' && within['@type'] === 'sc:Collection') {
            var collectionUrl = within['@id'];
            var collectionLabel = within.label || collectionUrl;
            return '<a href="' + collectionUrl + '" target="_blank">' + collectionLabel + '</a>';
        } else if (within instanceof Array) {
            return within.map(this.getWithin, this).join("<br/>");
        } else {
            return this.stringifyObject(within);
        }
    },

    extractLabelFromAttribute: function(attr) {
        var label = attr;

        label = label.replace(/^@/, '');
        label = label.replace(/\s{2,}/g, ' ');

        return label;
    },

    bindEvents: function() {},

    toggle: function(stateValue) {
        if (stateValue) {
            this.show();
        } else {
            this.hide();
        }
    },

    show: function() {
        var element = jQuery(this.element);
        if (this.panel) {
            element = element.parent();
        }
        element.show({
            effect: "slide",
            direction: "right",
            duration: 300,
            easing: "swing"
        });
    },

    hide: function() {
        var element = jQuery(this.element);
        if (this.panel) {
            element = element.parent();
        }
        element.hide({
            effect: "slide",
            direction: "right",
            duration: 300,
            easing: "swing"
        });
    },

    addLinksToUris: function(text) {
        // http://stackoverflow.com/questions/8188645/javascript-regex-to-match-a-url-in-a-field-of-text
        var regexUrl = /(http|ftp|https):\/\/[\w\-]+(\.[\w\-]+)+([\w.,@?\^=%&amp;:\/~+#\-]*[\w@?\^=%&amp;\/~+#\-])?/gi,
            textWithLinks = text,
            matches,
            parsedTextWithLinks;

        if (typeof text === 'string') {
            if (textWithLinks.indexOf('<a ') === -1) {
                matches = text.match(regexUrl);

                if (matches) {
                    jQuery.each(matches, function(index, match) {
                        textWithLinks = textWithLinks.replace(match, '<a href="' + match + '" target="_blank">' + match + '</a>');
                    });
                }
            } else {
                parsedTextWithLinks = jQuery('<div />').append(textWithLinks);
                jQuery(parsedTextWithLinks[0]).find('a').attr('target', '_blank');
                textWithLinks = parsedTextWithLinks[0].innerHTML;
            }
        }

        return textWithLinks;
    },

    /* Extracts metadata of the current image or canvasId */
    extractInformationFromWindow: function(viewerWindow, canvasId) {
        var _this = this;
        var currentImage;
        if (canvasId) {
            viewerWindow.imagesList.some(function(el) {
                if (el['@id'] === canvasId) {
                    currentImage = el;
                    return true;
                }
            });
        } else {
            currentImage = viewerWindow.imagesList[viewerWindow.focusModules[viewerWindow.viewType].currentImgIndex];
        }
        if (!currentImage) {
            return;
        }

        var tplData = {
            metadataListingCls: this.options.metadataListingCls || 'metadata-listing',
        };

        _this.manifest = currentImage;
        this.imageMetadataTypes = {};

        this.imageMetadataTypes.details = _this.getMetadataDetails(_this.manifest);
        this.imageMetadataTypes.rights = _this.getMetadataRights(_this.manifest);
        this.imageMetadataTypes.links = _this.getMetadataLinks(_this.manifest);

        //vvvvv This is *not* how this should be done.
        jQuery.each(this.imageMetadataTypes, function(metadataKey, metadataValues) {
            tplData[metadataKey] = [];

            jQuery.each(metadataValues, function(idx, itm) {
                if (typeof itm.value === 'object') {
                    itm.value = _this.stringifyObject(itm.value);
                }

                if (typeof itm.value === 'string' && itm.value !== '') {
                    tplData[metadataKey].push({
                        identifier: itm.identifier || '',
                        label: _this.extractLabelFromAttribute(itm.label),
                        value: (metadataKey === 'links') ? itm.value : _this.addLinksToUris(itm.value)
                    });
                }
            });
        });

        if (_this.manifest.logo) {
            var logo = '';
            if (typeof _this.manifest.logo === "string") {
                logo = _this.manifest.logo;
            } else if (typeof _this.manifest.logo['@id'] !== 'undefined') {
                logo = _this.manifest.logo['@id'];
            }
            tplData.logo = logo;
        }

        var noMetadata = jQuery.isEmptyObject(this.imageMetadataTypes.details) &&
            jQuery.isEmptyObject(this.imageMetadataTypes.rights) &&
            jQuery.isEmptyObject(this.imageMetadataTypes.links);
        return noMetadata ? null : tplData;
    },

    /* End of copy and adaptation of all helpers from metadataView. */

    /* Adaptation of helpers from canvasLink.js to inject tab */

    /* adds the locales to the internationalization module of the œviewer */
    addLocalesToViewer: function() {
        var currentLocales = {};
        for (var language in this.locales) {
            currentLocales = this.locales[language];
            i18next.addResources(
                language, 'translation',
                currentLocales
            );
        }
    },

    /* injects the panel to the dom */
    injectPanelToViewerWindow: function(viewerWindow, canvasId) {
        var tplData = this.extractInformationFromWindow(viewerWindow, canvasId);
        var metadataPanel = tplData ? this.template(tplData) : this.emptyTemplate();

        // TODO Don't use mirador-container, but this.element (there may be multiple viewer and multiple item in a viewer).
        // Append the first time, else replace with the metadata of the current image.
        if (jQuery('.mirador-container .sidePanel .tabContentArea > .metadataPanel').length == 0) {
            // The toc is set in all cases, but there may be no tab on init, and in
            // that case, the tabs are hidden. See tabs.js.
            var hasTabs = jQuery('.mirador-container .sidePanel > .tabGroup li').length > 0;
            if (hasTabs) {
                jQuery('.mirador-container .sidePanel > .tabGroup').append(this.metadataTabTemplate);
            } else {
                // jQuery('.mirador-container .sidePanel > .tabGroup').hide();
                jQuery('.mirador-container .sidePanel > .tabGroup').append(this.tocTabTemplate);
                jQuery('.mirador-container .sidePanel > .tabGroup .tocTab').addClass('selected');
            }
            metadataPanel = '<div class="metadataPanel" style="display: none;">' + metadataPanel + '</div>';
            jQuery('.mirador-container .sidePanel > .tabGroup').append(this.metadataTabTemplate);
            jQuery('.mirador-container .sidePanel .tabContentArea').append(metadataPanel);
        } else {
            jQuery('.mirador-container .sidePanel .tabContentArea > .metadataPanel').html(metadataPanel);
        }
    },

    /* injects the needed viewer event handler */
    injectViewerEventHandler: function() {
        var this_ = this;
        var origFunc = Mirador.Viewer.prototype.setupViewer;
        Mirador.Viewer.prototype.setupViewer = function() {
            origFunc.apply(this);
            var options = this.state.getStateProperty('metadataTab');
            if (jQuery.isPlainObject(options)) {
                this_.options = options;
            }
        };
    },

    /* Injects the content of the initial image (canvasId is the current ui.*/
    injectWindowEventHandler: function() {
        var _this = this;
        var origFunc = Mirador.Window.prototype.init;
        Mirador.Window.prototype.init = function() {
            var _thisViewerWindow = this;
            // Update the metadata for the current image.
            this.eventEmitter.subscribe("SET_CURRENT_CANVAS_ID." + this.id, function(evt, canvasId) {
                _this.injectPanelToViewerWindow(_thisViewerWindow, canvasId);
            });
            // TODO Subscribe and/or publish state of tabs (selected). Simple jQuery works for now.
            jQuery(document).on('click', '.mirador-container .sidePanel > .tabGroup .tab', function(e) {
                // In Mirador, div panes are not the same than tabs, so a list is needed.
                var tab = jQuery(this);
                var tabs = {
                    'tocTab': '.toc',
                    'searchTab': '.search-result',
                    'layersTab': '.layersPanel',
                    'metadataTab': '.metadataPanel',
                };
                var tabId = tab.data('tabid');
                tab.parent().find('.tab').removeClass('selected');
                tab.addClass('selected');
                tab.closest('.sidePanel').find('.tabContentArea > ul, .tabContentArea > div').hide();
                tab.closest('.sidePanel').find('.tabContentArea > ' + tabs[tabId]).show();
            });
            // Initial callback: Mirador is loaded and the canvas is set.
            jQuery(document).ready(function() {
                _this.injectPanelToViewerWindow(_thisViewerWindow);
            });
            origFunc.apply(this);
        }
    },

    /* End of adaptation of helpers from canvasLink.js */

    /* initializes the plugin */
    init: function() {
        var _this = this;

        i18next.on('initialized', function() {
            this.addLocalesToViewer();
        }.bind(this));

        this.injectViewerEventHandler();
        this.injectWindowEventHandler();
    },

}

$(document).ready(function() {
    MetadataTab.init();
});
