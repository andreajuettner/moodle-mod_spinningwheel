// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Standalone wheel spinner for the Moodle mobile app.
 *
 * Renders the wheel on an offscreen canvas and exposes it as a data-URL
 * via Angular property binding (self.wheelImageUrl). This survives
 * Angular re-renders that would otherwise wipe a live <canvas> element.
 *
 * Spin animation rotates the <img> element via CSS transform.
 *
 * Loaded by classes/output/mobile.php via file_get_contents().
 *
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var self = this;

// State variables — populated by parseOtherData().
var entries = [];
var colors = [];
var config = {};
var labels = {};
var cmid = 0;
var dataParsed = false;

// Animation state.
var spinning = false;
var cssRotation = 0;
var TAU = Math.PI * 2;

// Offscreen canvas (never inserted into the DOM).
// Use 800x800 for crisp text on retina/HiDPI displays.
var offscreenCanvas = document.createElement('canvas');
offscreenCanvas.width = 800;
offscreenCanvas.height = 800;

/**
 * Parse data from CONTENT_OTHERDATA.
 * Called with retries because the mobile app may populate
 * CONTENT_OTHERDATA after the JS has already started executing.
 */
function parseOtherData() {
    if (dataParsed) {
        return;
    }
    var otherdata = self.CONTENT_OTHERDATA || {};
    if (!otherdata.entries) {
        return;
    }
    try {
        entries = typeof otherdata.entries === 'string'
            ? JSON.parse(otherdata.entries) : otherdata.entries;
        colors = typeof otherdata.colors === 'string'
            ? JSON.parse(otherdata.colors) : (otherdata.colors || []);
        config = typeof otherdata.config === 'string'
            ? JSON.parse(otherdata.config) : (otherdata.config || {});
        labels = typeof otherdata.labels === 'string'
            ? JSON.parse(otherdata.labels) : (otherdata.labels || {});
        cmid = parseInt(otherdata.cmid || '0', 10);
    } catch (e) {
        entries = [];
        return;
    }
    if (!Array.isArray(entries)) {
        entries = [];
    }
    if (!Array.isArray(colors) || colors.length === 0) {
        colors = ['#E8174A', '#3A86FF', '#F9C80E', '#1B998B', '#7B2FF7', '#FF6B35'];
    }
    dataParsed = true;
}

// ========================================================================
// WHEEL RENDERING (extracted from amd/src/wheel.js — zero Moodle deps)
// ========================================================================

var TEXT_RADIUS = 0.72;
var PIC_RADIUS_SMALL = 0.42;
var PIC_RADIUS_LARGE = 0.55;
var PIC_SIZE_SMALL = 56;
var PIC_SIZE_LARGE = 96;
var imageCache = new Map();

/**
 * Fix a URL for authenticated access in the Moodle mobile app.
 * For pluginfile URLs: converts to webservice/pluginfile with token.
 * For other URLs: appends the token as a query parameter.
 */
function getAuthenticatedUrl(url) {
    if (!url) {
        return url;
    }
    try {
        var site = self.CoreSitesProvider && self.CoreSitesProvider.getCurrentSite();
        if (!site) {
            return url;
        }
        // Use the app's built-in method for pluginfile URLs if available.
        if (url.indexOf('/pluginfile.php/') !== -1 && typeof site.fixPluginfileURL === 'function') {
            return site.fixPluginfileURL(url);
        }
        // Manual fallback: get token and append it.
        var token = typeof site.getToken === 'function' ? site.getToken() : (site.token || '');
        if (token) {
            if (url.indexOf('/pluginfile.php/') !== -1) {
                url = url.replace('/pluginfile.php/', '/webservice/pluginfile.php/');
            }
            url += (url.indexOf('?') !== -1 ? '&' : '?') + 'token=' + token;
        }
    } catch (e) {
        // Silently fall back to the original URL.
    }
    return url;
}

/**
 * Preload profile picture images.
 * Supports both base64 data URLs (mobile) and regular URLs (with auth fallback).
 */
function preloadImages(entryList) {
    var promises = entryList
        .filter(function(e) { return e.picture; })
        .map(function(e) {
            if (imageCache.has(e.picture)) {
                return Promise.resolve();
            }
            return new Promise(function(resolve) {
                var img = new Image();
                img.onload = function() {
                    imageCache.set(e.picture, img);
                    resolve();
                };
                img.onerror = function() { resolve(); };
                // Data URLs (base64) work directly; other URLs get auth token.
                img.src = e.picture.indexOf('data:') === 0
                    ? e.picture : getAuthenticatedUrl(e.picture);
            });
        });
    return Promise.all(promises);
}

/**
 * Draw the wheel on a canvas.
 */
function drawWheel(canvas, entryList, colorList, rotation, cfg) {
    var ctx = canvas.getContext('2d');
    if (!ctx) {
        return;
    }
    var size = Math.min(canvas.width, canvas.height);
    var cx = size / 2;
    var cy = size / 2;
    var radius = size / 2 - 6;
    var count = entryList.length;
    var displaymode = cfg.displaymode || 0;

    if (count === 0) {
        return;
    }

    var sliceAngle = TAU / count;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Drop shadow beneath the wheel (skip if ctx.filter is unsupported).
    if (cfg.showshadow !== false && typeof ctx.filter === 'string') {
        ctx.save();
        ctx.beginPath();
        ctx.arc(cx, cy + 3, radius, 0, TAU);
        ctx.fillStyle = 'rgba(0, 0, 0, 0.12)';
        ctx.filter = 'blur(6px)';
        ctx.fill();
        ctx.filter = 'none';
        ctx.restore();
    }

    ctx.save();
    ctx.translate(cx, cy);
    ctx.rotate(rotation);

    for (var i = 0; i < count; i++) {
        var startAngle = i * sliceAngle;
        var endAngle = startAngle + sliceAngle;
        var color = colorList[i % colorList.length];

        // Segment.
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.arc(0, 0, radius, startAngle, endAngle);
        ctx.closePath();
        ctx.fillStyle = color;
        ctx.fill();
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Content.
        ctx.save();
        ctx.rotate(startAngle + sliceAngle / 2);

        var textColor = getContrastColor(color);

        var hasImage = entryList[i].picture && imageCache.has(entryList[i].picture);

        if (displaymode === 2 && hasImage) {
            drawCircularImage(ctx, entryList[i].picture, radius * PIC_RADIUS_LARGE, 0, PIC_SIZE_LARGE);
        } else if (displaymode === 1 && hasImage) {
            drawCircularImage(ctx, entryList[i].picture, radius * PIC_RADIUS_SMALL, 0, PIC_SIZE_SMALL);
            drawRadialText(ctx, entryList[i].text, radius, count, textColor);
        } else {
            // Always draw text as fallback (also when images fail to load in mobile).
            drawRadialText(ctx, entryList[i].text, radius, count, textColor);
        }

        ctx.restore();
    }

    ctx.restore();

    // Outer ring.
    ctx.beginPath();
    ctx.arc(cx, cy, radius, 0, TAU);
    ctx.strokeStyle = '#dee2e6';
    ctx.lineWidth = 3;
    ctx.stroke();

    // Center circle.
    ctx.beginPath();
    ctx.arc(cx, cy, radius * 0.08, 0, TAU);
    ctx.fillStyle = '#ffffff';
    ctx.fill();
    ctx.strokeStyle = '#dee2e6';
    ctx.lineWidth = 2;
    ctx.stroke();
}

/**
 * Draw radial text along the segment bisector.
 */
function drawRadialText(ctx, text, radius, count, color) {
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = color;
    var fontSize = Math.max(16, Math.min(32, Math.floor(radius / count * 1.5)));
    ctx.font = 'bold ' + fontSize + 'px Arial, Helvetica, sans-serif';

    var maxWidth = radius * 0.35;
    if (ctx.measureText(text).width > maxWidth) {
        while (ctx.measureText(text + '\u2026').width > maxWidth && text.length > 0) {
            text = text.slice(0, -1);
        }
        text += '\u2026';
    }

    ctx.fillText(text, radius * TEXT_RADIUS, 0);
}

/**
 * Draw a circular clipped profile picture.
 */
function drawCircularImage(ctx, pictureUrl, x, y, diameter) {
    var img = imageCache.get(pictureUrl);
    if (!img) {
        return;
    }
    var r = diameter / 2;
    ctx.save();
    ctx.beginPath();
    ctx.arc(x, y, r, 0, TAU);
    ctx.clip();
    ctx.drawImage(img, x - r, y - r, diameter, diameter);
    ctx.restore();

    ctx.beginPath();
    ctx.arc(x, y, r, 0, TAU);
    ctx.strokeStyle = 'rgba(255,255,255,0.5)';
    ctx.lineWidth = 1.5;
    ctx.stroke();
}

/**
 * Get a contrasting text color for the given background.
 */
function getContrastColor(hexColor) {
    var hex = String(hexColor).replace('#', '');
    if (hex.length === 3) {
        hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    }
    var r = parseInt(hex.substring(0, 2), 16) || 0;
    var g = parseInt(hex.substring(2, 4), 16) || 0;
    var b = parseInt(hex.substring(4, 6), 16) || 0;
    var luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.5 ? '#1d2125' : '#ffffff';
}

/**
 * Calculate which segment the pointer (top center) points at.
 */
function getSelectedIndex(rotation, count) {
    if (count === 0) {
        return -1;
    }
    var sliceAngle = TAU / count;
    var pointerAngle = -Math.PI / 2;
    var effective = (pointerAngle - rotation) % TAU;
    if (effective < 0) {
        effective += TAU;
    }
    return Math.floor(effective / sliceAngle) % count;
}

// ========================================================================
// CONFETTI (extracted from amd/src/local/confetti.js — zero Moodle deps)
// ========================================================================

var CONFETTI_COUNT = 150;
var CONFETTI_DURATION = 3000;
var CONFETTI_GRAVITY = 0.12;
var CONFETTI_COLORS = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#7BC67E', '#FF6B6B'];

function launchConfetti() {
    var canvas = document.createElement('canvas');
    canvas.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;' +
        'pointer-events:none;z-index:10000;';
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    document.body.appendChild(canvas);

    var ctx = canvas.getContext('2d');

    var particles = [];
    for (var i = 0; i < CONFETTI_COUNT; i++) {
        particles.push({
            x: canvas.width * Math.random(),
            y: canvas.height * Math.random() * -0.5 - 20,
            vx: (Math.random() - 0.5) * 6,
            vy: Math.random() * 3 + 2,
            w: Math.random() * 8 + 4,
            h: Math.random() * 6 + 2,
            color: CONFETTI_COLORS[Math.floor(Math.random() * CONFETTI_COLORS.length)],
            rotation: Math.random() * Math.PI * 2,
            rotationSpeed: (Math.random() - 0.5) * 0.2,
        });
    }

    var startTime = performance.now();

    var animate = function(now) {
        var elapsed = now - startTime;
        var progress = elapsed / CONFETTI_DURATION;

        if (progress >= 1) {
            canvas.remove();
            return;
        }

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        var fadeStart = 0.7;
        var globalAlpha = progress > fadeStart ? 1 - ((progress - fadeStart) / (1 - fadeStart)) : 1;

        particles.forEach(function(p) {
            p.vy += CONFETTI_GRAVITY;
            p.x += p.vx;
            p.y += p.vy;
            p.rotation += p.rotationSpeed;

            ctx.save();
            ctx.globalAlpha = globalAlpha;
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rotation);
            ctx.fillStyle = p.color;
            ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
            ctx.restore();
        });

        requestAnimationFrame(animate);
    };

    requestAnimationFrame(animate);
}

// ========================================================================
// SOUNDS (extracted from amd/src/local/sounds.js — zero Moodle deps)
// ========================================================================

var audioCtx = null;
var celebrateBuffer = null;

function getAudioContext() {
    if (audioCtx) {
        return audioCtx;
    }
    try {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        return audioCtx;
    } catch (e) {
        return null;
    }
}

/**
 * Initialize audio on user gesture. Resumes suspended AudioContext.
 * Must be called inside a user-triggered event handler.
 */
function initSound() {
    var ctx = getAudioContext();
    if (ctx && ctx.state !== 'running') {
        ctx.resume().catch(function() {});
    }
}

/**
 * Preload celebration sound.
 * For base64 data URLs: decodes directly to AudioBuffer via Web Audio API
 * (same mechanism as the tick sound — reliable in mobile WebViews).
 * For regular URLs: fetches with auth token.
 */
function preloadCelebrateSound(url) {
    if (!url) {
        return;
    }
    var ctx = getAudioContext();
    if (!ctx) {
        return;
    }
    try {
        if (url.indexOf('data:') === 0) {
            // Base64 data URL — decode directly without network request.
            var base64 = url.split(',')[1];
            var binaryStr = atob(base64);
            var len = binaryStr.length;
            var bytes = new Uint8Array(len);
            for (var i = 0; i < len; i++) {
                bytes[i] = binaryStr.charCodeAt(i);
            }
            ctx.decodeAudioData(bytes.buffer, function(decoded) {
                celebrateBuffer = decoded;
            }, function() {
                celebrateBuffer = null;
            });
        } else {
            // Regular URL — fetch with auth.
            fetch(getAuthenticatedUrl(url))
                .then(function(r) { return r.arrayBuffer(); })
                .then(function(buf) { return ctx.decodeAudioData(buf); })
                .then(function(decoded) { celebrateBuffer = decoded; })
                .catch(function() { celebrateBuffer = null; });
        }
    } catch (e) {
        celebrateBuffer = null;
    }
}

function playTick() {
    var ctx = getAudioContext();
    if (!ctx) {
        return;
    }
    try {
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.frequency.value = 800;
        osc.type = 'sine';
        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.05);

        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.05);
    } catch (e) {
        // Silently ignore audio errors.
    }
}

function playCelebrate() {
    var ctx = getAudioContext();
    if (!ctx || !celebrateBuffer) {
        return;
    }
    try {
        var source = ctx.createBufferSource();
        source.buffer = celebrateBuffer;
        var gain = ctx.createGain();
        gain.gain.value = 0.7;
        source.connect(gain);
        gain.connect(ctx.destination);
        source.start(0);
    } catch (e) {
        // Silently ignore playback errors.
    }
}

// ========================================================================
// MOBILE SPINNER CONTROLLER
// ========================================================================

/**
 * Render the wheel to the offscreen canvas and expose it as a data-URL
 * via the Angular-bound property self.wheelImageUrl.
 */
function renderWheel() {
    if (entries.length === 0) {
        return;
    }
    drawWheel(offscreenCanvas, entries, colors, 0, config);
    // setTimeout ensures Angular's zone.js detects the property change.
    setTimeout(function() {
        self.wheelImageUrl = offscreenCanvas.toDataURL('image/png');
    }, 0);
}

/**
 * Initialize the wheel: parse data and render.
 * Returns true on success, false if data is not ready yet.
 */
function initWheel() {
    parseOtherData();
    if (entries.length === 0) {
        return false;
    }

    // Preload celebration sound early so it's ready when the spin ends.
    if (config.celebratesoundurl && !celebrateBuffer) {
        preloadCelebrateSound(config.celebratesoundurl);
    }

    if (config.displaymode > 0) {
        preloadImages(entries).then(function() {
            renderWheel();
        });
    } else {
        renderWheel();
    }
    return true;
}

/**
 * Called by the template's core-site-plugins-call-ws (onSuccess) event
 * after the spin_wheel WS call returns successfully.
 */
self.onSpinResult = function(result) {
    parseOtherData();
    if (spinning || entries.length === 0) {
        return;
    }

    spinning = true;

    // Clear previous result overlay.
    setTimeout(function() {
        self.spinResultText = '';
        self.spinWinnerMessage = '';
    }, 0);

    // Initialize audio on first user gesture (must be inside a user-triggered event).
    initSound();
    if (config.celebratesoundurl && !celebrateBuffer) {
        preloadCelebrateSound(config.celebratesoundurl);
    }

    // Disable spin button during animation.
    var btn = document.querySelector('[data-action="mobile-spin"]');
    if (btn) {
        btn.setAttribute('disabled', 'true');
    }

    // Find the target segment by matching selected text.
    var targetIndex = -1;
    for (var i = 0; i < entries.length; i++) {
        if (entries[i].text === result.selectedtext) {
            targetIndex = i;
            break;
        }
    }
    if (targetIndex === -1) {
        targetIndex = result.selectedindex || 0;
    }

    // Calculate target CSS rotation.
    var count = entries.length;
    var sliceAngle = TAU / count;
    var targetSegmentCenter = targetIndex * sliceAngle + sliceAngle / 2;
    var targetAngle = (-Math.PI / 2 - targetSegmentCenter) % TAU;
    if (targetAngle < 0) {
        targetAngle += TAU;
    }

    var normalizedCurrent = cssRotation % TAU;
    if (normalizedCurrent < 0) {
        normalizedCurrent += TAU;
    }
    var delta = targetAngle - normalizedCurrent;
    if (delta < 0) {
        delta += TAU;
    }

    var fullSpins = 5 + Math.floor(Math.random() * 3);
    var totalRotation = fullSpins * TAU + delta;

    var startRotation = cssRotation;
    var duration = config.spintime || 5000;
    var startTime = performance.now();
    var lastSegment = -1;

    // Find the img element for direct CSS transform animation.
    var img = document.querySelector('[data-region="mobile-wheel-image"]');

    var animate = function(now) {
        var elapsed = now - startTime;
        var progress = Math.min(elapsed / duration, 1);

        // Cubic ease-out for realistic deceleration.
        var eased = 1 - Math.pow(1 - progress, 3);

        cssRotation = startRotation + totalRotation * eased;

        // Rotate the img via direct DOM style (bypasses Angular for 60fps performance).
        if (img) {
            img.style.transform = 'rotate(' + cssRotation + 'rad)';
        }

        // Ticking sound on segment boundary crossing.
        if (config.tickingsound) {
            var currentSegment = getSelectedIndex(cssRotation, count);
            if (currentSegment !== lastSegment && lastSegment !== -1) {
                playTick();
            }
            lastSegment = currentSegment;
        }

        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            onSpinComplete(result, btn);
        }
    };

    requestAnimationFrame(animate);
};

/**
 * Handle post-spin effects: celebration, result display, entry removal.
 */
function onSpinComplete(result, btn) {
    // Celebration effects.
    if (config.showconfetti) {
        launchConfetti();
    }
    if (config.celebratesoundurl) {
        playCelebrate();
    }

    // Show result text via Angular binding (overlay on wheel).
    setTimeout(function() {
        self.spinResultText = result.selectedtext;
        self.spinWinnerMessage = config.winnermessage || '';
    }, 0);

    // Remove entry from wheel if removeafter is enabled.
    if (config.removeafter) {
        for (var j = 0; j < entries.length; j++) {
            if (entries[j].text === result.selectedtext) {
                entries.splice(j, 1);
                break;
            }
        }
        // Redraw the wheel at the current rotation (so the visual stays consistent),
        // then reset the CSS rotation since the image now includes the rotation.
        var normalizedRotation = cssRotation % TAU;
        drawWheel(offscreenCanvas, entries, colors, normalizedRotation, config);
        cssRotation = 0;
        setTimeout(function() {
            self.wheelImageUrl = offscreenCanvas.toDataURL('image/png');
        }, 0);
        var img = document.querySelector('[data-region="mobile-wheel-image"]');
        if (img) {
            img.style.transform = '';
        }
    }

    spinning = false;
    if (btn) {
        if (entries.length === 0) {
            btn.setAttribute('disabled', 'true');
        } else {
            btn.removeAttribute('disabled');
        }
    }
}

// ========================================================================
// INITIALIZATION with retries
// ========================================================================

var initAttempts = 0;
var MAX_INIT_ATTEMPTS = 20;

function tryInit() {
    if (initWheel()) {
        return;
    }
    initAttempts++;
    if (initAttempts < MAX_INIT_ATTEMPTS) {
        setTimeout(tryInit, 500);
    }
}

setTimeout(tryInit, 300);
