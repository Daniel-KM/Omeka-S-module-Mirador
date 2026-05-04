import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    esbuild: {
        loader: 'jsx',
        include: /\.[jt]sx?$/,
    },
    optimizeDeps: {
        esbuildOptions: {
            loader: { '.js': 'jsx' },
        },
    },
    resolve: {
        alias: [
            // Rewrite deep imports from the @nakamura196/mirador alpha fork
            // (used by mirador-sync-windows) to the official mirador source
            // tree. The fork's "dist/es/src/..." paths mirror "src/..." in
            // the official package, so the substitution is direct. Use an
            // absolute filesystem path to bypass mirador's exports field
            // (which only exposes the package main).
            { find: /^@nakamura196\/mirador\/dist\/es\/src(\/.*)?$/, replacement: resolve(__dirname, 'node_modules/mirador/src') + '$1' },
            { find: '@nakamura196/mirador', replacement: 'mirador' },
            // Force every import of "mirador" (including from external
            // plugins) to resolve to the source tree. Mirador 4.0.0 ships
            // dist/mirador.es.js with OpenSeadragon 5.0.1 inline- vendored;
            // importing the source forces Vite to rebundle Mirador with our
            // overridden OpenSeadragon ^6.0.2.
            { find: /^mirador$/, replacement: 'mirador/src' },
        ],
    },
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
                'plugin-annotations': resolve(__dirname, 'asset/src/plugin-annotations.js'),
                'plugin-dl': resolve(__dirname, 'asset/src/plugin-dl.js'),
                'plugin-image-tools': resolve(__dirname, 'asset/src/plugin-image-tools.js'),
                'plugin-imagecropper': resolve(__dirname, 'asset/src/plugin-imagecropper.js'),
                'plugin-ocr-helper': resolve(__dirname, 'asset/src/plugin-ocr-helper.js'),
                'plugin-ruler': resolve(__dirname, 'asset/src/plugin-ruler.js'),
                'plugin-share': resolve(__dirname, 'asset/src/plugin-share.js'),
                'plugin-sync-windows': resolve(__dirname, 'asset/src/plugin-sync-windows.js'),
                'plugin-textoverlay': resolve(__dirname, 'asset/src/plugin-textoverlay.js'),
                'plugin-zoom-percent': resolve(__dirname, 'asset/src/plugin-zoom-percent.jsx'),
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
                    if (/node_modules\/(mirador-annotation-editor|mirador-dl-plugin|mirador-imagecropper|mirador-image-tools|mirador-ocr-helper|mirador-physical-ruler|mirador-share-plugin|mirador-sync-windows|mirador-textoverlay)\//.test(id)) {
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
