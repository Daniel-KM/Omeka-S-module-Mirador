jQuery(document).ready(async function(){
    let plugins = [];
    for (let {plugin, name} of window.miradorPlugins) {
        if (window.globalMiradorPlugins.includes(name)) {
            plugins = [...plugins, ...plugin]
        }
    }
    await Mirador.viewer(miradorConfig, plugins);
});
