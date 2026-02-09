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

for (const [viewerId, config] of Object.entries(window.miradors || {})) {
    window.miradors[viewerId] = Mirador.viewer(config, plugins);
}
