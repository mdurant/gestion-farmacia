/**
 * Control de inactividad y sesión única por dispositivo.
 */
export function registerSessionGuard(Alpine) {
    Alpine.data('sessionGuard', (config) => ({
        idleMs: config.idleMs,
        warningMs: config.warningMs,
        pollMs: config.pollMs ?? 10000,
        statusUrl: config.statusUrl,
        renewUrl: config.renewUrl,
        logoutUrl: config.logoutUrl,
        loginUrl: config.loginUrl ?? '/login?sesion=reemplazada',
        csrfToken: config.csrfToken,

        showWarning: false,
        showSuperseded: false,
        countdownSeconds: 0,
        countdownPercent: 100,
        lastActivityAt: Date.now(),
        warningStartedAt: null,
        countdownTimer: null,
        idleTimer: null,
        pollTimer: null,

        init() {
            const events = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'];

            events.forEach((eventName) => {
                window.addEventListener(eventName, () => this.registerActivity(), { passive: true });
            });

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    this.syncStatus();
                }
            });

            window.addEventListener('focus', () => this.syncStatus());

            this.resetIdleTimer();
            this.syncStatus();
            this.pollTimer = window.setInterval(() => this.syncStatus(), this.pollMs);
        },

        registerActivity() {
            if (this.showWarning || this.showSuperseded) {
                return;
            }

            this.lastActivityAt = Date.now();
            this.resetIdleTimer();
        },

        resetIdleTimer() {
            if (this.idleTimer) {
                window.clearTimeout(this.idleTimer);
            }

            this.idleTimer = window.setTimeout(() => this.openWarning(), this.idleMs);
        },

        openWarning() {
            if (this.showWarning || this.showSuperseded) {
                return;
            }

            this.showWarning = true;
            this.warningStartedAt = Date.now();
            this.countdownSeconds = Math.ceil(this.warningMs / 1000);
            this.countdownPercent = 100;
            this.startCountdown();
        },

        openSuperseded() {
            this.showSuperseded = true;
            this.showWarning = false;

            if (this.idleTimer) {
                window.clearTimeout(this.idleTimer);
                this.idleTimer = null;
            }

            if (this.countdownTimer) {
                window.clearInterval(this.countdownTimer);
                this.countdownTimer = null;
            }
        },

        goToLoginAfterSuperseded() {
            window.location.href = this.loginUrl;
        },

        startCountdown() {
            if (this.countdownTimer) {
                window.clearInterval(this.countdownTimer);
            }

            this.countdownTimer = window.setInterval(() => {
                const elapsed = Date.now() - this.warningStartedAt;
                const remaining = Math.max(0, this.warningMs - elapsed);

                this.countdownSeconds = Math.ceil(remaining / 1000);
                this.countdownPercent = Math.max(0, (remaining / this.warningMs) * 100);

                if (remaining <= 0) {
                    window.clearInterval(this.countdownTimer);
                    this.logoutNow();
                }
            }, 250);
        },

        async renewSession() {
            try {
                const response = await fetch(this.renewUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    const data = await response.json().catch(() => ({}));
                    if (data.reason === 'session_superseded') {
                        this.openSuperseded();
                    } else {
                        this.logoutNow();
                    }

                    return;
                }

                this.closeWarning();
                this.lastActivityAt = Date.now();
                this.resetIdleTimer();
            } catch {
                this.logoutNow();
            }
        },

        closeWarning() {
            this.showWarning = false;
            this.warningStartedAt = null;

            if (this.countdownTimer) {
                window.clearInterval(this.countdownTimer);
                this.countdownTimer = null;
            }
        },

        async syncStatus() {
            if (this.showSuperseded) {
                return;
            }

            try {
                const response = await fetch(this.statusUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json().catch(() => ({}));

                if (response.status === 401 || data.expired) {
                    if (data.reason === 'session_superseded') {
                        this.openSuperseded();
                    } else {
                        window.location.href = '/login';
                    }

                    return;
                }

                if (data.show_warning && ! this.showWarning) {
                    this.openWarning();
                }
            } catch {
                // Sin conexión: el temporizador local sigue activo.
            }
        },

        logoutNow() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = this.logoutUrl;

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = this.csrfToken;
            form.appendChild(token);

            document.body.appendChild(form);
            form.submit();
        },
    }));
}
