import * as Mirador from 'mirador';

const selectedPlugins = window.__miradorPlugins || [];
const pluginPackages = window.__miradorPluginPackages || {};
const plugins = [];

for (const name of selectedPlugins) {
    const pkg = pluginPackages[name];
    if (!pkg) continue;
    try {
        const mod = await import(pkg);
        const exported = mod.default || mod;
        if (Array.isArray(exported)) plugins.push(...exported);
        else plugins.push(exported);
    } catch (e) {
        console.warn('[Mirador] Plugin "' + name + '" failed:', e);
    }
}

// Delay viewer instantiation until the page has fully loaded so CSS and fonts
// are applied. On a cold cache the module script can otherwise execute while
// the stylesheet for the Mirador container is still being parsed; OSD then
// measures its container at 0×0 and never paints.
const instantiateViewers = () => {
    for (const [viewerId, config] of Object.entries(window.miradors || {})) {
        // Force the canvas drawer: WebGL fails with "Error creating texture
        // in WebGL. undefined" on cross-origin IIIF tiles served without
        // proper CORS headers (typical of cookbook.iiif.io). Site admins can
        // override via mirador_config_item.osdConfig.drawer.
        config.osdConfig = Object.assign({ drawer: 'canvas' }, config.osdConfig || {});
        window.miradors[viewerId] = Mirador.viewer(config, plugins);
    }
};
if (document.readyState === 'complete') {
    instantiateViewers();
} else {
    window.addEventListener('load', instantiateViewers, { once: true });
}
