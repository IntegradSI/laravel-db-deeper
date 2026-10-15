import {
    Activity,
    Archive,
    CircleAlert,
    CircleCheck,
    CircleHelp,
    ChartNoAxesCombined,
    Clock3,
    createIcons,
    Database,
    Eye,
    Gauge,
    HardDrive,
    Info,
    Layers3,
    LayoutDashboard,
    ListOrdered,
    Menu,
    Pause,
    Play,
    RefreshCw,
    ServerCog,
    ScanSearch,
    ShieldCheck,
    TableProperties,
    Users,
    UserRoundCheck,
} from 'lucide';

const icons = {
    Activity,
    Archive,
    CircleAlert,
    CircleCheck,
    CircleHelp,
    ChartNoAxesCombined,
    Clock3,
    Database,
    Eye,
    Gauge,
    HardDrive,
    Info,
    Layers3,
    LayoutDashboard,
    ListOrdered,
    Menu,
    Pause,
    Play,
    RefreshCw,
    ServerCog,
    ScanSearch,
    ShieldCheck,
    TableProperties,
    Users,
    UserRoundCheck,
};

const renderIcons = (root = document) => createIcons({ icons, root });

renderIcons();

const drawer = document.querySelector('#db-deeper-drawer');

document.querySelectorAll('.drawer-side a').forEach((link) => {
    link.addEventListener('click', () => {
        if (drawer instanceof HTMLInputElement) {
            drawer.checked = false;
        }
    });
});

const livePanel = document.querySelector('[data-live-panel]');

if (livePanel instanceof HTMLElement) {
    const liveContent = document.querySelector('[data-live-content]');
    const status = livePanel.querySelector('[data-refresh-status]');
    const statusDot = livePanel.querySelector('[data-refresh-dot]');
    const countdown = livePanel.querySelector('[data-refresh-countdown]');
    const lastUpdated = livePanel.querySelector('[data-refresh-last-updated]');
    const errorMessage = document.querySelector('[data-refresh-error]');
    const toggleButton = livePanel.querySelector('[data-refresh-toggle]');
    const toggleLabel = livePanel.querySelector('[data-refresh-toggle-label]');
    const refreshButton = livePanel.querySelector('[data-refresh-now]');
    const intervalSeconds = Math.max(15, Number.parseInt(livePanel.dataset.refreshInterval ?? '30', 10) || 30);
    const refreshUrl = new URL(livePanel.dataset.refreshUrl ?? '', window.location.href);
    let paused = false;
    let loading = false;
    let nextRefreshAt = Date.now() + intervalSeconds * 1000;

    const updateStatus = (message) => {
        if (status instanceof HTMLElement) {
            status.textContent = message;
        }
    };

    const updateStatusDot = (state) => {
        if (statusDot instanceof HTMLElement) {
            statusDot.className = `status status-${state} status-sm`;
        }
    };

    const refresh = async (manual = false) => {
        if (!(liveContent instanceof HTMLElement) || loading || (!manual && (paused || document.hidden))) {
            return;
        }

        if (refreshUrl.origin !== window.location.origin) {
            updateStatus('Endereço de atualização inválido');

            return;
        }

        loading = true;
        updateStatusDot('info');
        refreshButton?.setAttribute('aria-busy', 'true');

        if (refreshButton instanceof HTMLButtonElement) {
            refreshButton.disabled = true;
        }

        updateStatus('Coletando dados do banco…');

        try {
            const response = await fetch(refreshUrl, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (!response.ok || response.redirected || !response.headers.get('content-type')?.includes('text/html')) {
                throw new Error(`Atualização recusada (${response.status})`);
            }

            const markup = await response.text();
            const template = document.createElement('template');
            template.innerHTML = markup;
            liveContent.replaceChildren(template.content);
            renderIcons(liveContent);

            const now = new Date();

            if (lastUpdated instanceof HTMLTimeElement) {
                lastUpdated.dateTime = now.toISOString();
                lastUpdated.textContent = new Intl.DateTimeFormat('pt-BR', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                }).format(now);
            }

            if (errorMessage instanceof HTMLElement) {
                errorMessage.textContent = '';
                errorMessage.hidden = true;
            }

            nextRefreshAt = Date.now() + intervalSeconds * 1000;
            updateStatus(paused ? 'Atualização pausada' : 'Dados atualizados agora');
            updateStatusDot(paused ? 'neutral' : 'success');
        } catch {
            if (errorMessage instanceof HTMLElement) {
                errorMessage.textContent = 'Não foi possível atualizar os dados. A tela mantém a última leitura válida; tente novamente.';
                errorMessage.hidden = false;
            }

            updateStatus('Falha ao atualizar · leitura anterior mantida');
            updateStatusDot('error');
            nextRefreshAt = Date.now() + intervalSeconds * 1000;
        } finally {
            loading = false;
            refreshButton?.removeAttribute('aria-busy');

            if (refreshButton instanceof HTMLButtonElement) {
                refreshButton.disabled = false;
            }
        }
    };

    toggleButton?.addEventListener('click', () => {
        paused = !paused;
        toggleButton.setAttribute('aria-pressed', String(paused));

        if (toggleLabel instanceof HTMLElement) {
            toggleLabel.textContent = paused ? 'Retomar' : 'Pausar';
        }

        const toggleIcon = toggleButton.querySelector('[data-refresh-icon]');

        if (toggleIcon instanceof SVGElement) {
            const nextIcon = document.createElement('i');
            nextIcon.setAttribute('data-lucide', paused ? 'play' : 'pause');
            nextIcon.setAttribute('data-refresh-icon', '');
            nextIcon.setAttribute('class', 'size-4');
            nextIcon.setAttribute('aria-hidden', 'true');
            toggleIcon.replaceWith(nextIcon);
            renderIcons(toggleButton);
        }

        toggleButton.setAttribute('aria-label', paused ? 'Retomar atualização automática' : 'Pausar atualização automática');
        updateStatus(paused ? 'Atualização pausada' : 'Atualização automática ativa');
        updateStatusDot(paused ? 'neutral' : 'success');
        nextRefreshAt = Date.now() + intervalSeconds * 1000;

        if (!paused) {
            refresh(true);
        }
    });

    refreshButton?.addEventListener('click', () => refresh(true));

    window.setInterval(() => {
        if (!paused && !document.hidden && Date.now() >= nextRefreshAt) {
            refresh();
        }

        if (countdown instanceof HTMLElement) {
            const secondsLeft = Math.max(0, Math.ceil((nextRefreshAt - Date.now()) / 1000));
            countdown.textContent = paused ? 'pausada' : `${secondsLeft}s`;
        }
    }, 1000);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !paused && Date.now() >= nextRefreshAt) {
            refresh();
        }
    });
}
