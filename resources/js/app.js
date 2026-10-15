import {
    Activity,
    Archive,
    CircleAlert,
    CircleCheck,
    CircleHelp,
    ChartNoAxesCombined,
    Clock3,
    Copy,
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
    Save,
    ServerCog,
    ScanSearch,
    ShieldCheck,
    TableProperties,
    Users,
    UserRoundCheck,
} from 'lucide';

const lucideIcons = {
    Activity,
    Archive,
    CircleAlert,
    CircleCheck,
    CircleHelp,
    ChartNoAxesCombined,
    Clock3,
    Copy,
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
    Save,
    ServerCog,
    ScanSearch,
    ShieldCheck,
    TableProperties,
    Users,
    UserRoundCheck,
};

const renderIcons = (root = document) => createIcons({ icons: lucideIcons, root });

renderIcons();

const backupTabList = document.querySelector('[data-backup-tabs]');

if (backupTabList instanceof HTMLElement) {
    const tabs = [...backupTabList.querySelectorAll('[role="tab"]')];

    const activateTab = (tab, updateUrl = true) => {
        tabs.forEach((candidate) => {
            const selected = candidate === tab;
            const panel = document.getElementById(candidate.getAttribute('aria-controls') ?? '');

            candidate.setAttribute('aria-selected', String(selected));
            candidate.setAttribute('tabindex', selected ? '0' : '-1');
            candidate.classList.toggle('tab-active', selected);

            if (panel instanceof HTMLElement) {
                panel.hidden = !selected;
            }
        });

        if (updateUrl) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab.dataset.backupTab ?? 'manual');

            if (url.href !== window.location.href) {
                window.history.pushState(null, '', url);
            }
        }
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activateTab(tab));
        tab.addEventListener('keydown', (event) => {
            const nextIndex = event.key === 'ArrowRight'
                ? (index + 1) % tabs.length
                : event.key === 'ArrowLeft'
                    ? (index - 1 + tabs.length) % tabs.length
                    : event.key === 'Home'
                        ? 0
                        : event.key === 'End'
                            ? tabs.length - 1
                            : null;

            if (nextIndex !== null) {
                event.preventDefault();
                tabs[nextIndex]?.focus();
                activateTab(tabs[nextIndex]);
            }
        });
    });

    window.addEventListener('popstate', () => {
        const requestedTab = new URL(window.location.href).searchParams.get('tab');
        const tab = tabs.find((candidate) => candidate.dataset.backupTab === requestedTab) ?? tabs[0];

        if (tab instanceof HTMLElement) {
            activateTab(tab, false);
        }
    });

    document.querySelectorAll('[data-open-backup-tab]').forEach((button) => {
        button.addEventListener('click', () => {
            const requestedTab = button.dataset.openBackupTab;
            const tab = tabs.find((candidate) => candidate.dataset.backupTab === requestedTab);

            if (tab instanceof HTMLElement) {
                activateTab(tab);
                tab.focus();
            }
        });
    });
}

const backupPreflightForm = document.querySelector('[data-backup-preflight-form]');

if (backupPreflightForm instanceof HTMLFormElement) {
    const diagnosticButton = backupPreflightForm.querySelector('[data-run-backup-diagnostic]');
    const submitButton = backupPreflightForm.querySelector('[data-backup-submit]');
    const results = backupPreflightForm.querySelector('[data-backup-diagnostic-results]');
    const summary = backupPreflightForm.querySelector('[data-backup-diagnostic-summary]');
    const checksList = backupPreflightForm.querySelector('[data-backup-diagnostic-checks]');
    const fields = ['connection_name', 'disk_name', 'destination_path'];
    let verifiedSignature = null;

    const currentSignature = () => fields
        .map((name) => backupPreflightForm.elements.namedItem(name)?.value ?? '')
        .join('\u001f');

    const invalidateDiagnostic = () => {
        verifiedSignature = null;

        if (submitButton instanceof HTMLButtonElement) {
            submitButton.disabled = true;
        }

        if (results instanceof HTMLElement) {
            results.hidden = true;
        }
    };

    const showSummary = (message, state, reference = null) => {
        if (!(summary instanceof HTMLElement)) {
            return;
        }

        const summaryClasses = {
            success: 'alert alert-success items-start',
            info: 'alert alert-info items-start',
            error: 'alert alert-error items-start',
        };
        summary.className = summaryClasses[state] ?? summaryClasses.error;
        summary.replaceChildren();
        const icon = document.createElement('i');
        icon.dataset.lucide = state === 'success' ? 'circle-check' : state === 'info' ? 'activity' : 'circle-alert';
        icon.className = 'mt-0.5 size-5 shrink-0';
        icon.setAttribute('aria-hidden', 'true');
        const text = document.createElement('span');
        text.className = 'text-sm leading-5';
        text.textContent = reference ? `${message} Referência: ${reference}.` : message;
        summary.append(icon, text);
        renderIcons(summary);
    };

    const renderChecks = (checks) => {
        if (!(checksList instanceof HTMLUListElement)) {
            return;
        }

        checksList.replaceChildren();

        checks.forEach((check) => {
            const passed = check.status === 'passed';
            const item = document.createElement('li');
            item.className = 'flex min-w-0 items-start gap-3 rounded-field border border-base-300 bg-base-100 p-3';
            const icon = document.createElement('i');
            icon.dataset.lucide = passed ? 'circle-check' : 'circle-alert';
            icon.className = passed ? 'mt-0.5 size-5 shrink-0 text-success' : 'mt-0.5 size-5 shrink-0 text-error';
            icon.setAttribute('aria-hidden', 'true');
            const content = document.createElement('div');
            content.className = 'min-w-0';
            const label = document.createElement('p');
            label.className = 'font-medium';
            label.textContent = check.label;
            const message = document.createElement('p');
            message.className = 'mt-1 break-words text-sm leading-5 text-base-content/65';
            message.textContent = check.message;
            content.append(label, message);
            item.append(icon, content);
            checksList.append(item);
        });

        renderIcons(checksList);
    };

    backupPreflightForm.addEventListener('input', invalidateDiagnostic);
    backupPreflightForm.addEventListener('change', invalidateDiagnostic);

    diagnosticButton?.addEventListener('click', async () => {
        if (!(diagnosticButton instanceof HTMLButtonElement) || !(results instanceof HTMLElement)) {
            return;
        }

        if (!backupPreflightForm.reportValidity()) {
            return;
        }

        const signature = currentSignature();
        const token = backupPreflightForm.querySelector('input[name="_token"]')?.value;
        const endpoint = new URL(backupPreflightForm.dataset.diagnosticUrl ?? '', window.location.href);

        if (endpoint.origin !== window.location.origin) {
            showSummary('O endereço do diagnóstico é inválido. Atualize a página e tente novamente.', 'error');
            results.hidden = false;
            return;
        }

        diagnosticButton.disabled = true;
        diagnosticButton.setAttribute('aria-busy', 'true');
        diagnosticButton.textContent = 'Verificando…';
        results.hidden = false;
        if (checksList instanceof HTMLElement) {
            checksList.replaceChildren();
        }
        showSummary('Verificando a configuração selecionada. Nenhum dump será executado.', 'info');

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: new FormData(backupPreflightForm),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error('diagnostic_request_failed');
            }

            const diagnostic = await response.json();

            if (signature !== currentSignature()) {
                results.hidden = false;
                showSummary('As opções mudaram durante a verificação. Execute o diagnóstico novamente.', 'error');
                return;
            }

            if (!Array.isArray(diagnostic.checks) || typeof diagnostic.ready !== 'boolean') {
                throw new Error('diagnostic_response_invalid');
            }

            renderChecks(diagnostic.checks);
            showSummary(
                diagnostic.ready
                    ? 'Tudo pronto para iniciar o backup.'
                    : 'Há itens que precisam ser corrigidos antes de iniciar o backup.',
                diagnostic.ready ? 'success' : 'error',
                diagnostic.diagnostic_id,
            );
            verifiedSignature = diagnostic.ready ? signature : null;

            if (submitButton instanceof HTMLButtonElement) {
                submitButton.disabled = !diagnostic.ready;
            }
        } catch {
            verifiedSignature = null;
            results.hidden = false;
            showSummary('Não foi possível concluir o diagnóstico. Confira sua conexão, atualize a página e tente novamente.', 'error');
        } finally {
            diagnosticButton.disabled = false;
            diagnosticButton.removeAttribute('aria-busy');
            diagnosticButton.replaceChildren();
            const icon = document.createElement('i');
            icon.dataset.lucide = 'activity';
            icon.className = 'size-4';
            icon.setAttribute('aria-hidden', 'true');
            diagnosticButton.append(icon, document.createTextNode('Diagnosticar novamente'));
            renderIcons(diagnosticButton);
        }
    });

    backupPreflightForm.addEventListener('submit', (event) => {
        if (verifiedSignature !== currentSignature()) {
            event.preventDefault();
            invalidateDiagnostic();
            results.hidden = false;
            showSummary('Execute o diagnóstico novamente antes de iniciar o backup.', 'error');
        }
    });
}

document.querySelectorAll('[data-copy-target]').forEach((button) => {
    button.addEventListener('click', async () => {
        const target = document.getElementById(button.dataset.copyTarget ?? '');
        const status = document.querySelector('[data-copy-status]');

        if (!(target instanceof HTMLElement) || !navigator.clipboard) {
            if (status instanceof HTMLElement) {
                status.textContent = 'A cópia automática não está disponível. Selecione e copie a chave manualmente.';
            }

            return;
        }

        try {
            await navigator.clipboard.writeText(target.textContent?.trim() ?? '');

            if (status instanceof HTMLElement) {
                status.textContent = 'Chave copiada para a área de transferência.';
            }

            button.textContent = 'Copiada';
        } catch {
            if (status instanceof HTMLElement) {
                status.textContent = 'Não foi possível copiar. Selecione e copie a chave manualmente.';
            }
        }
    });
});

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
            const statusClasses = {
                info: 'status status-info status-sm',
                success: 'status status-success status-sm',
                warning: 'status status-warning status-sm',
                error: 'status status-error status-sm',
            };
            statusDot.className = statusClasses[state] ?? 'status status-neutral status-sm';
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
