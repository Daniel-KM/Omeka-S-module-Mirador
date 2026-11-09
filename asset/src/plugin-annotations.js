import 'mirador-annotation-editor/dist/index.css';
import plugins, { annotationAdapters } from 'mirador-annotation-editor';

// Bridge to store annotations, used by the config set by the module. Without
// endpoint, annotations are stored in the local storage of the browser. With an
// endpoint, the server should follow the protocol of Aiiinotate.
// @see \Mirador\View\Helper\Mirador::render()
// @see view/common/mirador.phtml
if (typeof window.miradorAnnotationServerAdapter !== 'function') {
    window.miradorAnnotationServerAdapter = (canvasId) => (window.annotationEndpoint
        ? new annotationAdapters.AiiinotateAdapter(window.annotationEndpoint, 3, canvasId)
        : new annotationAdapters.LocalStorageAdapter(`localStorage://?canvasId=${canvasId}`));
}

export default plugins;
