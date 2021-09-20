jQuery(document).ready(async function(){
    let plugins = [];
    if (window.miradorPlugins && window.miradorPlugins.length) {
        for (let {plugin, name} of window.miradorPlugins) {
            if (window.globalMiradorPlugins.includes(name)) {
                plugins = [...plugins, ...plugin];
            }
        }
    }
    await Object.keys(miradors).forEach(viewerId => {
        miradors[viewerId] = Mirador.viewer(miradors[viewerId], plugins);
    });
});
