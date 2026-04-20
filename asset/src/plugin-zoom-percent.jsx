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
 */
function ZoomPercentOverlay({ windowId }) {
    const [value, setValue] = useState(null);
    const [visible, setVisible] = useState(false);
    const [host, setHost] = useState(null);
    const prevRef = useRef(null);
    const fadeTimerRef = useRef(null);

    // Inject a host span immediately before the "Zoom in" button so
    // the portal renders in the toolbar flow rather than overlaying
    // the canvas.
    useEffect(() => {
        let cancelled = false;
        let hostSpan = null;

        const inject = () => {
            if (cancelled) {
                return;
            }
            const ref = OSDReferences.get(windowId);
            const osdEl = ref && ref.current && ref.current.element;
            if (!osdEl) {
                setTimeout(inject, 150);
                return;
            }
            // Search within the window root for the zoom-in control.
            const winRoot = osdEl.closest('[class*="window-container"]')
                || osdEl.closest('[class*="Window-root"]')
                || osdEl.ownerDocument;
            const zoomInIcon = winRoot.querySelector(
                '[data-testid="ZoomInIcon"], [aria-label*="Zoom in" i], [aria-label*="Zoom avant" i]'
            );
            const zoomInBtn = zoomInIcon && (zoomInIcon.closest('button') || zoomInIcon);
            if (!zoomInBtn || !zoomInBtn.parentNode) {
                setTimeout(inject, 200);
                return;
            }
            hostSpan = document.createElement('span');
            hostSpan.className = 'mirador-zoom-percent-host';
            hostSpan.style.display = 'inline-flex';
            hostSpan.style.alignItems = 'center';
            hostSpan.style.verticalAlign = 'middle';
            zoomInBtn.parentNode.insertBefore(hostSpan, zoomInBtn);
            setHost(hostSpan);
        };
        inject();

        return () => {
            cancelled = true;
            if (hostSpan && hostSpan.parentNode) {
                hostSpan.parentNode.removeChild(hostSpan);
            }
        };
    }, [windowId]);

    useEffect(() => {
        let detached = false;
        let currentOsd = null;

        const compute = () => {
            // Always re-fetch the osd ref: Mirador may swap the
            // viewer (e.g. after opening a new canvas) without the
            // windowId changing, so the captured instance can be
            // stale.
            const ref = OSDReferences.get(windowId);
            const osd = ref && ref.current;
            if (!osd || !osd.viewport) {
                return;
            }
            const imageZoom = osd.viewport.viewportToImageZoom(
                osd.viewport.getZoom(true)
            );
            const pct = Math.round(imageZoom * 100);
            if (!Number.isFinite(pct) || prevRef.current === pct) {
                return;
            }
            prevRef.current = pct;
            setValue(pct);
            setVisible(true);
            if (fadeTimerRef.current) {
                clearTimeout(fadeTimerRef.current);
            }
            fadeTimerRef.current = setTimeout(
                () => setVisible(false),
                5000
            );
        };

        const events = ['zoom', 'pan', 'animation', 'animation-finish', 'resize', 'open'];

        const attach = () => {
            if (detached) {
                return;
            }
            const ref = OSDReferences.get(windowId);
            const osd = ref && ref.current;
            if (!osd) {
                setTimeout(attach, 150);
                return;
            }
            currentOsd = osd;
            events.forEach((e) => osd.addHandler(e, compute));
            compute();
        };
        attach();

        return () => {
            detached = true;
            if (currentOsd) {
                events.forEach((e) => currentOsd.removeHandler(e, compute));
            }
            if (fadeTimerRef.current) {
                clearTimeout(fadeTimerRef.current);
            }
        };
    }, [windowId]);

    if (!host || value == null) {
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
        host
    );
}

const zoomPercentPlugin = {
    target: 'OpenSeadragonViewer',
    mode: 'add',
    component: ZoomPercentOverlay,
};

export default zoomPercentPlugin;
