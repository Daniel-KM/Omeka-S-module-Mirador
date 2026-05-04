import React, { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { OSDReferences } from 'mirador';

/**
 * Mirador 4 plugin: discreet zoom percentage indicator.
 *
 * Shows the current image zoom (native resolution ratio, rounded) as
 * a sibling of the Mirador zoom controls, immediately to the left of
 * the "Zoom in" button, styled like a disabled MUI IconButton so it
 * blends with the surrounding toolbar. Fades out after a 5 s delay.
 *
 * The OSD `zoom` event is listened to directly so the value reflects
 * the animation frame-by-frame; Mirador's redux `zoom` only updates
 * at animation settle, which feels laggy during a wheel scroll.
 *
 * Multi-window robustness: Mirador may re-render a window's toolbar
 * (canvas swap, focus change, fullscreen toggle) and detach our host
 * span. The component watches the per-window DOM and the OSD ref via
 * a MutationObserver and a polling fallback, re-injecting the host
 * and re-binding OSD handlers whenever needed. Lookup is scoped to
 * `document.getElementById(windowId)` so the right toolbar wins in a
 * multi-window workspace.
 */
const OSD_EVENTS = ['zoom', 'pan', 'animation', 'animation-finish', 'resize'];

// Sanity ceiling: any rounded percentage above this is treated as a
// placeholder artefact (typical bug seen on first frames: 73300%).
const MAX_REASONABLE_PCT = 5000;

function ZoomPercentOverlay({ windowId }) {
    const [value, setValue] = useState(null);
    const [visible, setVisible] = useState(false);
    const [, setHostTick] = useState(0);
    const hostRef = useRef(null);
    const prevPctRef = useRef(null);
    const fadeTimerRef = useRef(null);
    const attachedOsdRef = useRef(null);
    // Becomes true after the first OSD `tile-loaded` event, which is the
    // earliest reliable signal that `getContentSize()` reflects the
    // actual image resolution and not a 1x1 / low-res placeholder.
    const firstTileLoadedRef = useRef(false);

    useEffect(() => {
        let cancelled = false;
        let observer = null;
        let pollTimer = null;

        const compute = () => {
            const ref = OSDReferences.get(windowId);
            const osd = ref && ref.current;
            if (!osd || !osd.viewport) {
                return;
            }
            // OSD emits zoom/pan/resize events before the tiled image
            // actually opens; the placeholder viewport then yields huge
            // ratios (e.g. 73300%). Wait until the first tile has loaded
            // before measuring, so getContentSize() reflects the real
            // image resolution.
            if (!firstTileLoadedRef.current) {
                return;
            }
            if (!osd.world || osd.world.getItemCount() === 0) {
                return;
            }
            const tiledImage = osd.world.getItemAt(0);
            if (!tiledImage || typeof tiledImage.getContentSize !== 'function') {
                return;
            }
            const contentSize = tiledImage.getContentSize();
            if (!contentSize || contentSize.x <= 0 || contentSize.y <= 0) {
                return;
            }
            const imageZoom = osd.viewport.viewportToImageZoom(
                osd.viewport.getZoom(true)
            );
            const pct = Math.round(imageZoom * 100);
            if (!Number.isFinite(pct) || pct <= 0 || pct > MAX_REASONABLE_PCT) {
                return;
            }
            // Even if the percentage is unchanged, re-show: the user may
            // have switched windows / canvases and expects the overlay
            // to confirm the active viewer.
            const sameValue = prevPctRef.current === pct;
            prevPctRef.current = pct;
            if (!sameValue) {
                setValue(pct);
            }
            setVisible(true);
            if (fadeTimerRef.current) {
                clearTimeout(fadeTimerRef.current);
            }
            fadeTimerRef.current = setTimeout(
                () => setVisible(false),
                5000
            );
        };

        const ensureHost = () => {
            if (hostRef.current && hostRef.current.isConnected) {
                return true;
            }
            const ref = OSDReferences.get(windowId);
            const osdEl = ref && ref.current && ref.current.element;
            if (!osdEl) {
                return false;
            }
            const winRoot = document.getElementById(windowId)
                || osdEl.closest('.mirador-window')
                || osdEl.closest('[class*="window-container"]')
                || osdEl.closest('[class*="Window-root"]')
                || osdEl.ownerDocument;
            const zoomInIcon = winRoot.querySelector(
                '[data-testid="ZoomInIcon"], [aria-label*="Zoom in" i], [aria-label*="Zoom avant" i]'
            );
            const zoomInBtn = zoomInIcon
                && (zoomInIcon.closest('button') || zoomInIcon);
            if (!zoomInBtn || !zoomInBtn.parentNode) {
                return false;
            }
            // Drop any stale orphan host before creating a new one.
            if (hostRef.current && hostRef.current.parentNode) {
                hostRef.current.parentNode.removeChild(hostRef.current);
            }
            const span = document.createElement('span');
            span.className = 'mirador-zoom-percent-host';
            span.dataset.windowId = windowId;
            span.style.display = 'inline-flex';
            span.style.alignItems = 'center';
            span.style.verticalAlign = 'middle';
            zoomInBtn.parentNode.insertBefore(span, zoomInBtn);
            hostRef.current = span;
            // useRef does not trigger a re-render: bump a tick so the
            // portal gets attached now that hostRef has a valid target.
            setHostTick((n) => n + 1);
            return true;
        };

        const onTileLoaded = () => {
            firstTileLoadedRef.current = true;
            compute();
        };

        const onOpen = () => {
            // Canvas changed → reset until the new image has at least one
            // tile loaded. Hide the previous percentage in the meantime.
            firstTileLoadedRef.current = false;
            setVisible(false);
        };

        const ensureOsdHandlers = () => {
            const ref = OSDReferences.get(windowId);
            const osd = ref && ref.current;
            if (!osd) {
                return false;
            }
            if (attachedOsdRef.current === osd) {
                return true;
            }
            if (attachedOsdRef.current) {
                OSD_EVENTS.forEach((e) =>
                    attachedOsdRef.current.removeHandler(e, compute)
                );
                attachedOsdRef.current.removeHandler('tile-loaded', onTileLoaded);
                attachedOsdRef.current.removeHandler('open', onOpen);
            }
            OSD_EVENTS.forEach((e) => osd.addHandler(e, compute));
            osd.addHandler('tile-loaded', onTileLoaded);
            osd.addHandler('open', onOpen);
            attachedOsdRef.current = osd;
            compute();
            return true;
        };

        const sync = () => {
            if (cancelled) {
                return;
            }
            ensureHost();
            ensureOsdHandlers();
        };

        // First attempt immediately; the toolbar may not exist yet on
        // initial mount.
        sync();

        // Watch the per-window DOM tree: any toolbar mutation rebinds
        // the host and refreshes the OSD ref.
        const winRoot = document.getElementById(windowId);
        if (winRoot) {
            observer = new MutationObserver(sync);
            observer.observe(winRoot, { childList: true, subtree: true });
        }

        // Polling fallback for the brief windows during which neither
        // the host nor the OSD ref are ready (Mirador is still building
        // the window). Stops once both are attached.
        const poll = () => {
            if (cancelled) {
                return;
            }
            const ready = (hostRef.current && hostRef.current.isConnected)
                && attachedOsdRef.current;
            if (!ready) {
                sync();
                pollTimer = setTimeout(poll, 200);
            }
        };
        pollTimer = setTimeout(poll, 200);

        return () => {
            cancelled = true;
            if (observer) {
                observer.disconnect();
            }
            if (pollTimer) {
                clearTimeout(pollTimer);
            }
            if (attachedOsdRef.current) {
                OSD_EVENTS.forEach((e) =>
                    attachedOsdRef.current.removeHandler(e, compute)
                );
                attachedOsdRef.current.removeHandler('tile-loaded', onTileLoaded);
                attachedOsdRef.current.removeHandler('open', onOpen);
                attachedOsdRef.current = null;
            }
            if (hostRef.current && hostRef.current.parentNode) {
                hostRef.current.parentNode.removeChild(hostRef.current);
            }
            hostRef.current = null;
            if (fadeTimerRef.current) {
                clearTimeout(fadeTimerRef.current);
            }
        };
    }, [windowId]);

    if (!hostRef.current || value == null) {
        return null;
    }

    const style = {
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        verticalAlign: 'middle',
        minWidth: 40,
        height: 40,
        padding: '0 6px',
        marginRight: 4,
        fontSize: 14,
        lineHeight: 1,
        fontFamily: 'inherit',
        fontWeight: 500,
        color: 'inherit',
        borderRadius: '50%',
        userSelect: 'none',
        pointerEvents: 'none',
        opacity: visible ? 1 : 0,
        transition: visible
            ? 'opacity 120ms ease-out'
            : 'opacity 900ms ease-out 4100ms',
    };

    return createPortal(
        <span style={style} aria-live="polite">
            {value}%
        </span>,
        hostRef.current
    );
}

const zoomPercentPlugin = {
    target: 'OpenSeadragonViewer',
    mode: 'add',
    component: ZoomPercentOverlay,
};

export default zoomPercentPlugin;
