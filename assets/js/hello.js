window.addEventListener('DOMContentLoaded', () => {
    const helloLanding = document.body.querySelector('.theme-hello');
    if (!helloLanding) {
        return;
    }

    const navbarShrink = () => {
        const navbarCollapsible = helloLanding.querySelector('.main-nav');
        if (!navbarCollapsible) {
            return;
        }
        if (window.scrollY === 0) {
            navbarCollapsible.classList.remove('navbar-shrink');
        } else {
            navbarCollapsible.classList.add('navbar-shrink');
        }
    };

    navbarShrink();
    document.addEventListener('scroll', navbarShrink);

    const sections = {};
    helloLanding.querySelectorAll('.section').forEach((section) => {
        if (section.id) {
            sections[section.id] = section.offsetTop;
        }
    });

    window.addEventListener('scroll', () => {
        const scrollPosition = document.documentElement.scrollTop || document.body.scrollTop;

        Object.keys(sections).forEach((sectionId) => {
            if (sections[sectionId] - 200 <= scrollPosition) {
                const active = helloLanding.querySelector('.navbar a.nav-link.active');
                if (active) {
                    active.setAttribute('class', 'nav-link');
                }

                const nextActive = helloLanding.querySelector(`.navbar a.nav-link[href*="${sectionId}"]`);
                if (nextActive) {
                    nextActive.setAttribute('class', 'nav-link active');
                }
            }
        });
    });

    initHelloShowreel(helloLanding);
});

function initHelloShowreel(scopeRoot) {
    const showreel = scopeRoot.querySelector('[data-showreel]');
    if (!showreel || typeof window.gsap === 'undefined') {
        return;
    }

    const gsap = window.gsap;
    const soundtrack = showreel.querySelector('[data-soundtrack]');
    const startOverlay = showreel.querySelector('[data-start-overlay]');
    const startButton = showreel.querySelector('[data-start-button]');
    const playToggle = showreel.querySelector('[data-play-toggle]');
    const restartButton = showreel.querySelector('[data-restart]');
    const progressFill = showreel.querySelector('[data-progress-fill]');
    const sceneLabel = showreel.querySelector('[data-scene-label]');
    const timecode = showreel.querySelector('[data-timecode]');
    const audioLabel = showreel.querySelector('[data-audio-label]');
    const stage = showreel.querySelector('[data-stage]');
    const assemblyStage = showreel.querySelector('.hello-showreel__assembly-stage');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const audioDuration = Number(showreel.dataset.audioDuration || '497.533979');
    const configuredAudioSrc = showreel.dataset.audioSrc || '/hello/showreel-track.mp3';

    const PHASES = {
        opening: { start: 0, end: 80 },
        statement: { start: 80, end: 105 },
        calibration: { start: 105, end: 255 },
        rupture: { start: 255, end: 265 },
        assembly: { start: 265, end: 360 },
        continuity: { start: 360, end: 390 },
        closing: { start: 390, end: audioDuration },
    };

    const sceneNames = {
        opening: 'Opening frame',
        statement: 'Enterprise / precision',
        calibration: 'Calibration / instrumentation',
        assembly: 'Assembly / orchestration',
        continuity: 'Continuity / automation',
        closing: 'Closing signature',
    };

    const playback = {
        master: null,
        usingAudioSync: false,
        syncAttached: false,
        sync: null,
    };

    if (soundtrack && !soundtrack.getAttribute('src')) {
        soundtrack.setAttribute('src', configuredAudioSrc);
    }

    if (prefersReducedMotion) {
        if (startOverlay) {
            startOverlay.hidden = true;
        }
        if (playToggle) {
            playToggle.disabled = true;
        }
        updateTimecode(0);
        return;
    }

    ensureStageHelpers();
    const master = buildTimeline();
    playback.master = master;

    updateScene(getSceneForTime(0));
    updatePlaybackUI(false);
    updateProgressUI(0);
    updateTimecode(0);

    startButton?.addEventListener('click', () => startReel(master));
    playToggle?.addEventListener('click', () => togglePlayback(master));
    restartButton?.addEventListener('click', () => restartReel(master));

    soundtrack?.addEventListener('loadedmetadata', () => {
        if (audioLabel) {
            audioLabel.textContent = `Soundtrack ${formatDuration(audioDuration)}`;
        }
    });

    soundtrack?.addEventListener('ended', () => stopPlaybackAtEnd(master));
    soundtrack?.addEventListener('pause', () => {
        if (playback.usingAudioSync && soundtrack.currentTime < audioDuration) {
            updatePlaybackUI(false);
        }
    });
    soundtrack?.addEventListener('error', () => {
        if (audioLabel) {
            audioLabel.textContent = 'Soundtrack unavailable';
        }
    });

    function ensureStageHelpers() {
        if (stage && !stage.querySelector('.hello-showreel__rupture-overlay')) {
            const ruptureOverlay = document.createElement('div');
            ruptureOverlay.className = 'hello-showreel__rupture-overlay';
            stage.appendChild(ruptureOverlay);
        }

        if (assemblyStage && !assemblyStage.querySelector('.hello-showreel__assembly-cursor')) {
            const cursor = document.createElement('div');
            cursor.className = 'hello-showreel__assembly-cursor';
            assemblyStage.appendChild(cursor);
        }
    }

    function buildTimeline() {
        const scenes = showreel.querySelectorAll('.hello-showreel__scene');
        const ruptureOverlay = showreel.querySelector('.hello-showreel__rupture-overlay');
        const assemblyCursor = showreel.querySelector('.hello-showreel__assembly-cursor');

        gsap.set(scenes, { autoAlpha: 0 });
        gsap.set(showreel.querySelector('.hello-showreel__scene--opening'), { autoAlpha: 1, visibility: 'visible' });
        gsap.set(q('.hello-showreel__hero-line, .hello-showreel__scene-line, .hello-showreel__closing-title span'), { yPercent: 110 });
        gsap.set(q('.hello-showreel__hero-statement, .hello-showreel__statement-panel, .hello-showreel__marker-card, .hello-showreel__closing-body, .hello-showreel__closing-caption'), { autoAlpha: 0, y: 30 });
        gsap.set(q('.hello-showreel__scene--calibration .hello-showreel__eyebrow'), { autoAlpha: 0, y: 20 });
        gsap.set(q('.hello-showreel__instrument-panel, .hello-showreel__media-plate--portrait'), { autoAlpha: 0, y: 36 });
        gsap.set(q('.hello-showreel__meter span'), { scaleY: 0.12 });
        gsap.set(q('.hello-showreel__signal'), { scaleX: 0.08, autoAlpha: 0, transformOrigin: 'left center' });
        gsap.set(q('.hello-showreel__board-readout'), { autoAlpha: 0, y: 22 });
        gsap.set(q('.hello-showreel__scene--calibration .hello-showreel__calibration-board'), { autoAlpha: 0, x: 24, scale: 0.985 });
        gsap.set(q('.hello-showreel__assembly-module'), { autoAlpha: 0, y: 92, scale: 0.9 });
        gsap.set(q('.hello-showreel__chain-node'), { autoAlpha: 0, y: 28 });
        gsap.set(q('.hello-showreel__chain-link'), { autoAlpha: 0, y: 28, scaleX: 0.08 });
        gsap.set(q('.hello-showreel__closing-panel'), { autoAlpha: 0, scale: 0.975, y: 18 });
        gsap.set(q('.hello-showreel__vignette'), { opacity: 1 });
        gsap.set(q('.hello-showreel__grid'), { opacity: 0.24 });
        gsap.set(ruptureOverlay, { autoAlpha: 0, scale: 1.18 });
        gsap.set(assemblyCursor, { autoAlpha: 0, scale: 0.35, xPercent: -50, yPercent: -50 });

        const master = gsap.timeline({
            paused: true,
            defaults: { ease: 'power3.inOut' },
            onUpdate: () => {
                updateProgressUI(master.time());
                updateTimecode(master.time());
                updateScene(getSceneForTime(master.time()));
            },
            onComplete: () => {
                updatePlaybackUI(false);
            },
        });

        const p = (phaseName, ratio) => {
            const phase = PHASES[phaseName];
            return phase.start + (phase.end - phase.start) * ratio;
        };

        const showScene = (sceneName, atTime) => {
            master.set(showreel.querySelector(`.hello-showreel__scene--${sceneName}`), { autoAlpha: 1, visibility: 'visible' }, atTime);
        };

        const hideScene = (sceneName, atTime, duration = 1.4) => {
            master.to(showreel.querySelector(`.hello-showreel__scene--${sceneName}`), { autoAlpha: 0, duration }, atTime);
        };

        showScene('opening', PHASES.opening.start);
        master
            .from(q('.hello-showreel__scene--opening .hello-showreel__scene-header'), { autoAlpha: 0, y: -18, duration: 2.8 }, p('opening', 0.015))
            .to(q('.hello-showreel__scene--opening .hello-showreel__hero-line'), { yPercent: 0, duration: 9, stagger: 0.8, ease: 'power4.out' }, p('opening', 0.035))
            .to(q('.hello-showreel__scene--opening .hello-showreel__instrument-panel'), { autoAlpha: 1, y: 0, duration: 5.5 }, p('opening', 0.1))
            .to(q('.hello-showreel__scene--opening .hello-showreel__media-plate--portrait'), { autoAlpha: 1, y: 0, duration: 6.2 }, p('opening', 0.14))
            .to(q('.hello-showreel__scene--opening .hello-showreel__hero-statement'), { autoAlpha: 1, y: 0, duration: 5.5 }, p('opening', 0.18))
            .to(q('.hello-showreel__scene--opening .hello-showreel__meter span'), { scaleY: 1, duration: 4.4, stagger: 0.35, ease: 'back.out(1.3)' }, p('opening', 0.16))
            .to(q('.hello-showreel__scene--opening .hello-showreel__title-cluster'), { y: -16, duration: 14, ease: 'sine.inOut' }, p('opening', 0.28))
            .to(q('.hello-showreel__scene--opening .hello-showreel__title-cluster'), { y: 0, duration: 14, ease: 'sine.inOut' }, p('opening', 0.46))
            .to(q('.hello-showreel__scene--opening .hello-showreel__hero-right'), { y: -14, duration: 10, ease: 'sine.inOut' }, p('opening', 0.38))
            .to(q('.hello-showreel__scene--opening .hello-showreel__hero-right'), { y: 0, duration: 12, ease: 'sine.inOut' }, p('opening', 0.56))
            .to(q('.hello-showreel__scene--opening .hello-showreel__instrument-panel'), { borderColor: 'rgba(201, 168, 106, 0.34)', duration: 3.2 }, p('opening', 0.6))
            .to(q('.hello-showreel__scene--opening .hello-showreel__instrument-panel'), { borderColor: 'rgba(242, 238, 231, 0.12)', duration: 4 }, p('opening', 0.68))
            .to(q('.hello-showreel__scene--opening .hello-showreel__title-cluster'), { y: -8, duration: 7, ease: 'power2.out' }, p('opening', 0.82))
            .to(q('.hello-showreel__scene--opening .hello-showreel__title-cluster'), { y: 0, duration: 7, ease: 'power2.inOut' }, p('opening', 0.9));

        hideScene('opening', 77.8, 1.9);

        showScene('statement', PHASES.statement.start);
        master
            .from(q('.hello-showreel__scene--statement .hello-showreel__scene-header'), { autoAlpha: 0, y: -18, duration: 1.5 }, p('statement', 0.04))
            .fromTo(q('.hello-showreel__scene--statement .hello-showreel__statement-panel'), { autoAlpha: 0, y: 40, scale: 0.98 }, { autoAlpha: 1, y: 0, scale: 1, duration: 4.5, ease: 'power3.out' }, p('statement', 0.08))
            .fromTo(q('.hello-showreel__scene--statement .hello-showreel__marker-card'), { autoAlpha: 0, y: 42, scale: 0.97 }, { autoAlpha: 1, y: 0, scale: 1, duration: 3.2, stagger: 1.2, ease: 'back.out(1.08)' }, p('statement', 0.26))
            .to(q('.hello-showreel__scene--statement .hello-showreel__statement-panel'), { borderColor: 'rgba(201, 168, 106, 0.36)', duration: 1.8 }, p('statement', 0.46))
            .to(q('.hello-showreel__scene--statement .hello-showreel__statement-panel'), { borderColor: 'rgba(242, 238, 231, 0.12)', duration: 2.2 }, p('statement', 0.54))
            .to(q('.hello-showreel__scene--statement .hello-showreel__marker-card:nth-child(1)'), { y: -8, duration: 0.8 }, p('statement', 0.6))
            .to(q('.hello-showreel__scene--statement .hello-showreel__marker-card:nth-child(1)'), { y: 0, duration: 1.2 }, p('statement', 0.632))
            .to(q('.hello-showreel__scene--statement .hello-showreel__marker-card:nth-child(2)'), { y: -8, duration: 0.8 }, p('statement', 0.7))
            .to(q('.hello-showreel__scene--statement .hello-showreel__marker-card:nth-child(2)'), { y: 0, duration: 1.2 }, p('statement', 0.732))
            .to(q('.hello-showreel__scene--statement .hello-showreel__marker-card:nth-child(3)'), { y: -8, duration: 0.8 }, p('statement', 0.8))
            .to(q('.hello-showreel__scene--statement .hello-showreel__marker-card:nth-child(3)'), { y: 0, duration: 1.2 }, p('statement', 0.832));

        hideScene('statement', 103.2, 1.4);

        showScene('calibration', PHASES.calibration.start);
        master
            .from(q('.hello-showreel__scene--calibration .hello-showreel__scene-header'), { autoAlpha: 0, y: -18, duration: 1.8 }, p('calibration', 0.015))
            .to(q('.hello-showreel__scene--calibration .hello-showreel__scene-line'), { yPercent: 0, duration: 7.5, stagger: 0.55, ease: 'power4.out' }, p('calibration', 0.035))
            .to(q('.hello-showreel__scene--calibration .hello-showreel__eyebrow'), { autoAlpha: 1, y: 0, duration: 3.8 }, p('calibration', 0.07))
            .to(q('.hello-showreel__scene--calibration .hello-showreel__calibration-board'), { autoAlpha: 1, x: 0, scale: 1, duration: 4.8 }, p('calibration', 0.09))
            .to(q('.hello-showreel__scene--calibration .hello-showreel__signal--wide'), { autoAlpha: 1, scaleX: 1, duration: 3.2 }, p('calibration', 0.12))
            .to(q('.hello-showreel__scene--calibration .hello-showreel__signal--mid'), { autoAlpha: 1, scaleX: 1, duration: 2.8 }, p('calibration', 0.14))
            .to(q('.hello-showreel__scene--calibration .hello-showreel__signal--narrow'), { autoAlpha: 1, scaleX: 1, duration: 2.4 }, p('calibration', 0.16))
            .to(q('.hello-showreel__scene--calibration .hello-showreel__board-readout'), { autoAlpha: 1, y: 0, duration: 2.6, stagger: 0.6 }, p('calibration', 0.17));

        [0.24, 0.31, 0.39, 0.48, 0.57, 0.66, 0.74, 0.81].forEach((ratio, index) => {
            master
                .to(q('.hello-showreel__scene--calibration .hello-showreel__signal--mid'), { scaleX: 0.62, duration: 0.7 }, p('calibration', ratio))
                .to(q('.hello-showreel__scene--calibration .hello-showreel__signal--mid'), { scaleX: 1, duration: 1.2 }, p('calibration', ratio) + 0.7)
                .to(q('.hello-showreel__scene--calibration .hello-showreel__signal--narrow'), { scaleX: 0.4 + (index % 3) * 0.08, duration: 0.55 }, p('calibration', ratio) + 1.25)
                .to(q('.hello-showreel__scene--calibration .hello-showreel__signal--narrow'), { scaleX: 1, duration: 1 }, p('calibration', ratio) + 1.8)
                .to(q('.hello-showreel__scene--calibration .hello-showreel__calibration-board'), { y: -8, duration: 0.8 }, p('calibration', ratio) + 2.2)
                .to(q('.hello-showreel__scene--calibration .hello-showreel__calibration-board'), { y: 0, duration: 1.4 }, p('calibration', ratio) + 3.0);
        });

        master
            .to(q('.hello-showreel__scene--calibration .hello-showreel__signal'), { opacity: 0.84, duration: 1.1, stagger: 0.15 }, p('calibration', 0.72))
            .to(q('.hello-showreel__scene--calibration .hello-showreel__signal'), { opacity: 1, duration: 1.4, stagger: 0.15 }, p('calibration', 0.75))
            .to(q('.hello-showreel__grid'), { opacity: 0.38, duration: 10 }, p('calibration', 0.72))
            .to(q('.hello-showreel__vignette'), { opacity: 1.08, duration: 13 }, p('calibration', 0.76))
            .to(q('.hello-showreel__scene--calibration .hello-showreel__scene-content > *'), { scale: 0.88, y: 16, autoAlpha: 0.35, duration: 2.1, ease: 'power2.in' }, 255)
            .to(q('.hello-showreel__grid'), { opacity: 0.72, scale: 1.05, duration: 1.4 }, 256)
            .to(q('.hello-showreel__vignette'), { opacity: 1.35, duration: 1.2 }, 256)
            .to(ruptureOverlay, { autoAlpha: 1, scale: 1, duration: 1.5, ease: 'power2.out' }, 257.2)
            .to(q('.hello-showreel__stage'), { scale: 0.982, filter: 'brightness(0.68)', duration: 1.3, ease: 'power2.in' }, 257.6)
            .to(q('.hello-showreel__stage'), { scale: 1, filter: 'brightness(1)', duration: 1.9, ease: 'power2.out' }, 261.6)
            .to(ruptureOverlay, { autoAlpha: 0, duration: 1.2, ease: 'power2.inOut' }, 262.8)
            .to(q('.hello-showreel__grid'), { opacity: 0.3, scale: 1, duration: 1.6 }, 262.8)
            .to(q('.hello-showreel__vignette'), { opacity: 1, duration: 1.8 }, 263.2);

        hideScene('calibration', 263.8, 0.8);

        showScene('assembly', PHASES.assembly.start);
        master
            .from(q('.hello-showreel__scene--assembly .hello-showreel__scene-header'), { autoAlpha: 0, y: -20, duration: 1.7 }, 266.2)
            .fromTo(q('.hello-showreel__scene--assembly .hello-showreel__assembly-stage'), { clipPath: 'inset(20% 16% 22% 16% round 30px)', scale: 0.94 }, { clipPath: 'inset(0% 0% 0% 0% round 30px)', scale: 1, duration: 4.4, ease: 'power3.out' }, 266.8)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-grid'), { opacity: 0.5, duration: 4.6 }, 267.6)
            .set(assemblyCursor, { x: '22%', y: '26%' }, 270.2)
            .to(assemblyCursor, { autoAlpha: 1, scale: 1, duration: 0.75 }, 270.4)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--a'), { autoAlpha: 1, y: 0, scale: 1, duration: 2.2, ease: 'back.out(1.35)' }, 271.2)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--a'), { borderColor: 'rgba(201, 168, 106, 0.46)', boxShadow: '0 0 34px rgba(201, 168, 106, 0.16)', duration: 1.1 }, 273.6)
            .to(assemblyCursor, { x: '76%', y: '31%', duration: 1.5, ease: 'power3.inOut' }, 275.8)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--b'), { autoAlpha: 1, y: 0, scale: 1, duration: 2.2, ease: 'back.out(1.35)' }, 277.6)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--b'), { borderColor: 'rgba(201, 168, 106, 0.46)', boxShadow: '0 0 34px rgba(201, 168, 106, 0.16)', duration: 1.1 }, 279.9)
            .to(assemblyCursor, { x: '37%', y: '76%', duration: 1.6, ease: 'power3.inOut' }, 282.2)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--c'), { autoAlpha: 1, y: 0, scale: 1, duration: 2.2, ease: 'back.out(1.35)' }, 284.1)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--c'), { borderColor: 'rgba(201, 168, 106, 0.46)', boxShadow: '0 0 34px rgba(201, 168, 106, 0.16)', duration: 1.1 }, 286.4)
            .to(assemblyCursor, { x: '82%', y: '79%', duration: 1.8, ease: 'power3.inOut' }, 288.8)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--d'), { autoAlpha: 1, y: 0, scale: 1, duration: 2.6, ease: 'back.out(1.55)' }, 291.1)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--d'), { borderColor: 'rgba(201, 168, 106, 0.52)', boxShadow: '0 0 40px rgba(201, 168, 106, 0.18)', duration: 1.2 }, 293.8)
            .to(assemblyCursor, { scale: 1.35, duration: 0.8 }, 294.6)
            .to(assemblyCursor, { scale: 0.7, autoAlpha: 0, duration: 1 }, 295.5)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--a'), { xPercent: 10, yPercent: 9, duration: 5.5, ease: 'power2.out' }, 299)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--b'), { xPercent: -12, yPercent: 12, duration: 5.5, ease: 'power2.out' }, 301)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--c'), { xPercent: 13, yPercent: -10, duration: 5.5, ease: 'power2.out' }, 303)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module--d'), { xPercent: -13, yPercent: -14, duration: 5.9, ease: 'power2.out' }, 305)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-module'), { boxShadow: '0 18px 60px rgba(0, 0, 0, 0.28)', duration: 4.4, stagger: 0.45 }, 311)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-grid'), { opacity: 0.58, duration: 8 }, 318)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-stage'), { filter: 'brightness(1.05)', duration: 5.5 }, 327)
            .to(q('.hello-showreel__scene--assembly .hello-showreel__assembly-stage'), { filter: 'brightness(0.98)', duration: 9 }, 332);

        hideScene('assembly', 357.5, 2);

        showScene('continuity', PHASES.continuity.start);
        master
            .from(q('.hello-showreel__scene--continuity .hello-showreel__scene-header'), { autoAlpha: 0, y: -18, duration: 1.4 }, 361)
            .to(q('.hello-showreel__scene--continuity .hello-showreel__scene-line'), { yPercent: 0, duration: 4.2, stagger: 0.35, ease: 'power4.out' }, 362)
            .fromTo(q('.hello-showreel__scene--continuity .hello-showreel__eyebrow'), { autoAlpha: 0, y: 18 }, { autoAlpha: 1, y: 0, duration: 2.4 }, 363.4)
            .to(q('.hello-showreel__scene--continuity .hello-showreel__chain-node:nth-of-type(1)'), { autoAlpha: 1, y: 0, duration: 2.1, ease: 'back.out(1.12)' }, 365.2)
            .to(q('.hello-showreel__scene--continuity .hello-showreel__chain-link:nth-of-type(2)'), { autoAlpha: 1, y: 0, scaleX: 1, duration: 1.2 }, 367.4)
            .to(q('.hello-showreel__scene--continuity .hello-showreel__chain-node:nth-of-type(3)'), { autoAlpha: 1, y: 0, duration: 2.1, ease: 'back.out(1.12)' }, 368.8)
            .to(q('.hello-showreel__scene--continuity .hello-showreel__chain-link:nth-of-type(4)'), { autoAlpha: 1, y: 0, scaleX: 1, duration: 1.2 }, 371.4)
            .to(q('.hello-showreel__scene--continuity .hello-showreel__chain-node:nth-of-type(5)'), { autoAlpha: 1, y: 0, duration: 2.3, ease: 'back.out(1.12)' }, 372.8)
            .to(q('.hello-showreel__scene--continuity .hello-showreel__chain-track'), { xPercent: -3, duration: 5 }, 377)
            .to(q('.hello-showreel__scene--continuity .hello-showreel__chain-track'), { xPercent: 0, duration: 7 }, 382)
            .to(q('.hello-showreel__scene--continuity .hello-showreel__chain-node:last-child'), { borderColor: 'rgba(201, 168, 106, 0.46)', duration: 1.2 }, 384.5)
            .to(q('.hello-showreel__scene--continuity .hello-showreel__chain-node:last-child'), { borderColor: 'rgba(242, 238, 231, 0.12)', duration: 2.2 }, 386);

        hideScene('continuity', 388.6, 1);

        showScene('closing', PHASES.closing.start);
        master
            .from(q('.hello-showreel__scene--closing .hello-showreel__scene-header'), { autoAlpha: 0, y: -18, duration: 1.6 }, 391.2)
            .to(q('.hello-showreel__scene--closing .hello-showreel__closing-panel'), { autoAlpha: 1, scale: 1, y: 0, duration: 5.2 }, 392.4)
            .to(q('.hello-showreel__scene--closing .hello-showreel__closing-title span'), { yPercent: 0, duration: 7.2, stagger: 0.65, ease: 'power4.out' }, 394.8)
            .to(q('.hello-showreel__scene--closing .hello-showreel__closing-body'), { autoAlpha: 1, y: 0, duration: 4.5 }, 401)
            .to(q('.hello-showreel__scene--closing .hello-showreel__closing-caption'), { autoAlpha: 1, y: 0, duration: 4 }, 404.2)
            .to(q('.hello-showreel__scene--closing'), { filter: 'brightness(1.08)', duration: 8.5 }, 414)
            .to(q('.hello-showreel__scene--closing'), { filter: 'brightness(0.95)', duration: 12 }, 423.5)
            .to(q('.hello-showreel__vignette'), { opacity: 1.18, duration: 22 }, 430)
            .to(q('.hello-showreel__scene--closing .hello-showreel__closing-panel'), { y: -8, duration: 8.5, ease: 'sine.inOut' }, 438)
            .to(q('.hello-showreel__scene--closing .hello-showreel__closing-panel'), { y: 0, duration: 9.5, ease: 'sine.inOut' }, 447.5)
            .to(q('.hello-showreel__scene--closing .hello-showreel__closing-panel'), { scale: 1.015, duration: 12, ease: 'sine.inOut' }, 458)
            .to(q('.hello-showreel__scene--closing .hello-showreel__closing-panel'), { scale: 1, duration: 14, ease: 'sine.inOut' }, 470)
            .to(q('.hello-showreel__stage'), { filter: 'brightness(0.9)', duration: 8 }, 486)
            .to(q('.hello-showreel__stage'), { filter: 'brightness(1)', duration: 6.3 }, 491)
            .call(() => {}, null, audioDuration);

        return master;
    }

    async function startReel(master) {
        startOverlay?.setAttribute('hidden', 'hidden');

        if (soundtrack) {
            soundtrack.currentTime = 0;
            try {
                await soundtrack.play();
                playback.usingAudioSync = true;
                attachAudioSync(master);
                updatePlaybackUI(true);
                if (audioLabel) {
                    audioLabel.textContent = `Soundtrack synced ${formatDuration(audioDuration)}`;
                }
                return;
            } catch (error) {
                playback.usingAudioSync = false;
                detachAudioSync();
                if (audioLabel) {
                    audioLabel.textContent = 'Soundtrack blocked';
                }
            }
        }

        master.restart(true);
        updatePlaybackUI(true);
    }

    function togglePlayback(master) {
        if (playback.usingAudioSync && soundtrack) {
            if (soundtrack.paused) {
                soundtrack.play().catch(() => {
                    if (audioLabel) {
                        audioLabel.textContent = 'Soundtrack blocked';
                    }
                });
                updatePlaybackUI(true);
            } else {
                soundtrack.pause();
                updatePlaybackUI(false);
            }
            return;
        }

        if (!master.isActive() && master.progress() === 0) {
            startReel(master);
            return;
        }

        if (master.paused()) {
            master.play();
            updatePlaybackUI(true);
        } else {
            master.pause();
            updatePlaybackUI(false);
        }
    }

    function restartReel(master) {
        soundtrack?.pause();
        if (soundtrack) {
            soundtrack.currentTime = 0;
        }

        detachAudioSync();
        playback.usingAudioSync = false;
        master.pause(0);
        master.time(0, false);
        updateProgressUI(0);
        updateTimecode(0);
        updateScene(getSceneForTime(0));
        updatePlaybackUI(false);
        startReel(master);
    }

    function attachAudioSync(master) {
        if (playback.syncAttached) {
            return;
        }

        const sync = () => {
            if (!playback.usingAudioSync || !soundtrack) {
                return;
            }

            const clamped = Math.max(0, Math.min(soundtrack.currentTime, audioDuration));
            master.pause();
            master.time(clamped, false);
            updateProgressUI(clamped);
            updateTimecode(clamped);
            updateScene(getSceneForTime(clamped));

            if (clamped >= audioDuration) {
                stopPlaybackAtEnd(master);
            }
        };

        playback.sync = sync;
        playback.syncAttached = true;
        gsap.ticker.add(sync);
    }

    function detachAudioSync() {
        if (!playback.syncAttached || !playback.sync) {
            return;
        }

        gsap.ticker.remove(playback.sync);
        playback.syncAttached = false;
    }

    function stopPlaybackAtEnd(master) {
        soundtrack?.pause();
        if (soundtrack) {
            soundtrack.currentTime = audioDuration;
        }
        playback.usingAudioSync = false;
        detachAudioSync();
        master.pause(audioDuration);
        master.time(audioDuration, false);
        updateProgressUI(audioDuration);
        updateTimecode(audioDuration);
        updateScene(getSceneForTime(audioDuration));
        updatePlaybackUI(false);
    }

    function getSceneForTime(time) {
        const clamped = Math.max(0, Math.min(time, audioDuration));

        if (clamped < PHASES.statement.start) {
            return 'opening';
        }
        if (clamped < PHASES.calibration.start) {
            return 'statement';
        }
        if (clamped < PHASES.assembly.start) {
            return 'calibration';
        }
        if (clamped < PHASES.continuity.start) {
            return 'assembly';
        }
        if (clamped < PHASES.closing.start) {
            return 'continuity';
        }
        return 'closing';
    }

    function updatePlaybackUI(isPlaying) {
        if (playToggle) {
            playToggle.textContent = isPlaying ? 'Pause' : 'Play';
        }
    }

    function updateScene(sceneName) {
        if (sceneLabel) {
            sceneLabel.textContent = sceneNames[sceneName] || sceneName;
        }
    }

    function updateProgressUI(currentSeconds) {
        if (!progressFill) {
            return;
        }

        const progress = (Math.max(0, Math.min(currentSeconds, audioDuration)) / audioDuration) * 100;
        progressFill.style.width = `${progress}%`;
    }

    function updateTimecode(currentSeconds) {
        if (!timecode) {
            return;
        }

        timecode.textContent = formatDuration(Math.max(0, Math.min(currentSeconds, audioDuration)));
    }

    function formatDuration(value) {
        const totalMilliseconds = Math.round(value * 1000);
        const minutes = String(Math.floor(totalMilliseconds / 60000)).padStart(2, '0');
        const seconds = String(Math.floor((totalMilliseconds % 60000) / 1000)).padStart(2, '0');
        const milliseconds = String(totalMilliseconds % 1000).padStart(3, '0');
        return `${minutes}:${seconds}.${milliseconds}`;
    }

    function q(selector) {
        return showreel.querySelectorAll(selector);
    }
}
