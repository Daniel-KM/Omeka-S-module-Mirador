import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        target: 'es2022',
        outDir: resolve(__dirname, 'asset/vendor/mirador-esm'),
        emptyOutDir: true,
        rollupOptions: {
            preserveEntrySignatures: 'exports-only',
            treeshake: {
                // mirador-annotation-editor declares sideEffects: ["*.css"]
                // which causes Rollup to tree-shake all JS exports.
                // Force all modules to be treated as having side effects.
                moduleSideEffects: true,
            },
            input: {
                mirador: resolve(__dirname, 'asset/src/mirador.js'),
                'plugin-image-tools': resolve(__dirname, 'asset/src/plugin-image-tools.js'),
                'plugin-dl': resolve(__dirname, 'asset/src/plugin-dl.js'),
                'plugin-share': resolve(__dirname, 'asset/src/plugin-share.js'),
                'plugin-annotations': resolve(__dirname, 'asset/src/plugin-annotations.js'),
            },
            output: {
                format: 'es',
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                manualChunks(id) {
                    if (!id.includes('node_modules')) {
                        return undefined;
                    }
                    // Mirador core stays in the mirador entry chunk.
                    if (/node_modules\/mirador\//.test(id)) {
                        return undefined;
                    }
                    // Plugin packages stay in their respective entry chunks.
                    if (/node_modules\/(mirador-image-tools|mirador-dl-plugin|mirador-share-plugin|mirador-annotation-editor)\//.test(id)) {
                        return undefined;
                    }
                    // Annotation-specific heavy deps stay with the plugin.
                    if (/node_modules\/(react-konva|konva|react-quill|quill|use-image)\//.test(id)) {
                        return undefined;
                    }
                    // All other shared deps (react, mui, redux, etc.) go to vendor.
                    return 'vendor';
                },
            },
        },
    },
});
