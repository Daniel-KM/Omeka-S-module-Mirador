import canvasLinkPlugin from 'mirador-canvaslink';

// Default link: the current page with the position of the canvas in the param
// "page", like the links of dbmdz, read by mirador-4.mjs to open the viewer.
const getCanvasLink = ({ canvases, visibleCanvases }) => {
    const url = new URL(window.location.href);
    url.hash = '';
    url.searchParams.delete('canvas');
    const canvasId = visibleCanvases?.[0]?.id;
    const index = canvasId ? (canvases || []).findIndex((canvas) => canvas.id === canvasId) : -1;
    if (index >= 0) {
        url.searchParams.set('page', String(index + 1));
    } else {
        url.searchParams.delete('page');
    }
    return url.toString();
};

// Keep the same config object for the same window config to avoid renders.
const configs = new WeakMap();
const withDefaultLink = (config) => {
    if (config.getCanvasLink) {
        return config;
    }
    if (!configs.has(config)) {
        configs.set(config, { ...config, getCanvasLink });
    }
    return configs.get(config);
};

export default canvasLinkPlugin.map((plugin) => ({
    ...plugin,
    mapStateToProps: (state, props) => {
        const stateProps = plugin.mapStateToProps(state, props);
        return { ...stateProps, config: withDefaultLink(stateProps.config) };
    },
}));
