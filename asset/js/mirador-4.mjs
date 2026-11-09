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

// Open the canvas set in the url (see plugin canvaslink) once the manifests are
// loaded: the param "canvas" (canvas id) applies to each window whose manifest
// contains it; the param "page" (position, like dbmdz) applies only when there
// is a single window in the page, since a position is ambiguous otherwise.
const openUrlCanvas = (viewer, { canvasId, page }) => {
    let pending = null;
    const unsubscribe = viewer.store.subscribe(() => {
        const state = viewer.store.getState();
        if (!pending) {
            const windowIds = Object.keys(state.windows);
            if (!windowIds.length) {
                return;
            }
            pending = new Set(windowIds);
        }
        for (const windowId of [...pending]) {
            const win = state.windows[windowId];
            const manifest = win ? state.manifests[win.manifestId] : null;
            if (win && (!manifest || manifest.isFetching)) {
                continue;
            }
            pending.delete(windowId);
            if (!win || manifest.error) {
                continue;
            }
            const canvases = Mirador.getCanvases(state, { windowId });
            const target = canvases.some((canvas) => canvas.id === canvasId)
                ? canvasId
                : canvases[page - 1]?.id;
            if (target) {
                // Wait for the sagas that set the starting canvas of the window.
                setTimeout(() => viewer.store.dispatch(Mirador.setCanvas(windowId, target)));
            }
        }
        if (!pending.size) {
            unsubscribe();
        }
    });
};

const instantiateViewers = () => {
    const params = new URLSearchParams(window.location.search);
    const configs = Object.values(window.miradors || {});
    const isSingleWindow = configs.length === 1 && (configs[0].windows?.length || 0) <= 1;
    const urlCanvas = {
        canvasId: params.get('canvas'),
        // Like dbmdz, the param may list the pages of the book view ("3,4").
        page: isSingleWindow ? parseInt((params.get('page') || '').split(',')[0], 10) || 0 : 0,
    };
    for (const [viewerId, config] of Object.entries(window.miradors || {})) {
        // Force the canvas drawer: WebGL fails with "Error creating texture
        // in WebGL. undefined" on cross-origin IIIF tiles served without
        // proper CORS headers (typical of cookbook.iiif.io). Site admins can
        // override via mirador_config_item.osdConfig.drawer.
        config.osdConfig = Object.assign({ drawer: 'canvas' }, config.osdConfig || {});
        window.miradors[viewerId] = Mirador.viewer(config, plugins);
        if (urlCanvas.canvasId || urlCanvas.page > 0) {
            openUrlCanvas(window.miradors[viewerId], urlCanvas);
        }
    }
};
// Delay viewer instantiation until the page has fully loaded so CSS and fonts
// are applied. On a cold cache the module script can otherwise execute while
// the stylesheet for the Mirador container is still being parsed; OSD then
// measures its container at 0×0 and never paints.
if (document.readyState === 'complete') {
    instantiateViewers();
} else {
    window.addEventListener('load', instantiateViewers, { once: true });
}
