import React, { useEffect, useRef, useState } from 'react';
import { OSDReferences } from 'mirador';

/**
 * Mirador 4 plugin: discreet zoom percentage overlay.
 *
 * Shows the current image zoom (native resolution ratio, rounded) in
 * the bottom-right corner of the canvas while the user zooms, then
 * fades out after a 5 s delay. Styling stays readable on any
 * background via a semi-transparent dark pill with white text and a
 * tight text-shadow.
 *
 * The OSD `zoom` event is listened to directly so the value reflects
 * the animation frame-by-frame; Mirador's redux `zoom` only updates
 * at animation settle, which feels laggy during a wheel scroll.
 */
function ZoomPercentOverlay({ windowId }) {
    const [value, setValue] = useState(null);
    const [visible, setVisible] = useState(false);
    const prevRef = useRef(null);
    const fadeTimerRef = useRef(null);

    useEffect(() => {
        let osd = null;
        let detached = false;

        const update = () => {
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

        const attach = () => {
            if (detached) {
                return;
            }
            const ref = OSDReferences.get(windowId);
            osd = ref && ref.current;
            if (!osd) {
                setTimeout(attach, 150);
                return;
            }
            osd.addHandler('zoom', update);
            osd.addHandler('open', update);
            update();
        };
        attach();

        return () => {
            detached = true;
            if (osd) {
                osd.removeHandler('zoom', update);
                osd.removeHandler('open', update);
            }
            if (fadeTimerRef.current) {
                clearTimeout(fadeTimerRef.current);
            }
        };
    }, [windowId]);

    if (value == null) {
        return null;
    }

    const style = {
        position: 'absolute',
        right: 12,
        bottom: 12,
        padding: '3px 9px',
        minWidth: 36,
        textAlign: 'center',
        fontSize: 12,
        fontFamily: 'system-ui, -apple-system, sans-serif',
        lineHeight: 1.2,
        color: '#fff',
        background: 'rgba(0, 0, 0, 0.55)',
        border: '1px solid rgba(255, 255, 255, 0.18)',
        borderRadius: 10,
        pointerEvents: 'none',
        userSelect: 'none',
        textShadow: '0 0 3px rgba(0, 0, 0, 0.9), 0 0 1px rgba(0, 0, 0, 0.9)',
        backdropFilter: 'blur(2px)',
        WebkitBackdropFilter: 'blur(2px)',
        zIndex: 1000,
        opacity: visible ? 1 : 0,
        transition: visible
            ? 'opacity 120ms ease-out'
            : 'opacity 900ms ease-out 4100ms',
    };

    return (
        <div style={style} aria-live="polite">
            {value}%
        </div>
    );
}

const zoomPercentPlugin = {
    target: 'OpenSeadragonViewer',
    mode: 'add',
    component: ZoomPercentOverlay,
};

export default zoomPercentPlugin;
