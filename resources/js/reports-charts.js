import Chart from 'chart.js/auto';

const COLORS = {
    primary: '#2563eb',
    secondary: '#7c3aed',
    success: '#16a34a',
    warning: '#ca8a04',
    danger: '#dc2626',
    info: '#0891b2',
    muted: '#94a3b8',
    mean: '#0f172a',
};

function readChartsPayload() {
    const el = document.getElementById('reports-charts-data');

    if (! el) {
        return null;
    }

    try {
        return JSON.parse(el.textContent);
    } catch {
        return null;
    }
}

function canvas(id) {
    return document.getElementById(id);
}

function emptyState(ctx, message = 'Sin datos para el período') {
    const { width, height } = ctx.canvas;
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#94a3b8';
    ctx.font = '14px system-ui, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(message, width / 2, height / 2);
}

function money(value) {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        maximumFractionDigits: 0,
    }).format(value ?? 0);
}

function renderInventoryBars(data) {
    const el = canvas('chart-inventory-category');
    if (! el) return;

    if (! data?.labels?.length) {
        emptyState(el.getContext('2d'));
        return;
    }

    new Chart(el, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Valor inventario actual',
                    data: data.current_value,
                    backgroundColor: COLORS.primary,
                    borderRadius: 6,
                },
                {
                    label: 'Valor stock mínimo requerido',
                    data: data.min_value,
                    backgroundColor: COLORS.warning,
                    borderRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${money(ctx.raw)}`,
                    },
                },
            },
            scales: {
                x: { stacked: false },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => money(value),
                    },
                },
            },
        },
    });
}

function renderExpiryGauge(data) {
    const el = canvas('chart-expiry-gauge');
    if (! el) return;

    const percent = Number(data?.percent ?? 0);
    const levelColor = {
        green: COLORS.success,
        yellow: COLORS.warning,
        red: COLORS.danger,
    }[data?.level ?? 'green'] ?? COLORS.success;

    const remaining = Math.max(0, 100 - percent);

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: ['En riesgo (≤90 días)', 'Fuera de riesgo'],
            datasets: [{
                data: [percent, remaining],
                backgroundColor: [levelColor, '#e2e8f0'],
                borderWidth: 0,
                circumference: 180,
                rotation: 270,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${ctx.raw}%`,
                    },
                },
            },
        },
        plugins: [{
            id: 'gaugeCenterText',
            afterDraw(chart) {
                const { ctx, chartArea } = chart;
                if (! chartArea) return;
                const x = (chartArea.left + chartArea.right) / 2;
                const y = chartArea.bottom - 8;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.fillStyle = levelColor;
                ctx.font = 'bold 28px system-ui, sans-serif';
                ctx.fillText(`${percent}%`, x, y - 18);
                ctx.fillStyle = '#64748b';
                ctx.font = '12px system-ui, sans-serif';
                ctx.fillText('unidades ≤ 90 días', x, y);
                ctx.restore();
            },
        }],
    });

    const meta = document.getElementById('expiry-gauge-meta');
    if (meta) {
        meta.innerHTML = `
            <span class="badge badge-outline">${data.within_90 ?? 0} u. ≤ 90 días</span>
            <span class="badge badge-outline">${data.within_180 ?? 0} u. ≤ 180 días</span>
            <span class="badge badge-outline">${data.total_units ?? 0} u. totales</span>
        `;
    }
}

function renderConsumptionTrend(data) {
    const el = canvas('chart-consumption-trend');
    if (! el) return;

    if (! data?.labels?.length) {
        emptyState(el.getContext('2d'));
        return;
    }

    new Chart(el, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Controlados / psicotrópicos (RX)',
                    data: data.controlled,
                    borderColor: COLORS.danger,
                    backgroundColor: 'rgba(220, 38, 38, 0.12)',
                    tension: 0.3,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'No controlados (Non-RX)',
                    data: data.non_controlled,
                    borderColor: COLORS.primary,
                    backgroundColor: 'rgba(37, 99, 235, 0.12)',
                    tension: 0.3,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${money(ctx.raw)}`,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (value) => money(value) },
                },
            },
        },
    });
}

function renderRotationBubble(data) {
    const el = canvas('chart-rotation-bubble');
    if (! el) return;

    if (! data?.length) {
        emptyState(el.getContext('2d'));
        return;
    }

    new Chart(el, {
        type: 'bubble',
        data: {
            datasets: [{
                label: 'Fármacos',
                data: data.map((item) => ({
                    x: item.x,
                    y: item.y,
                    r: item.r,
                    label: item.label,
                    unit_cost: item.unit_cost,
                    stock_value: item.stock_value,
                    consumed_value: item.consumed_value,
                })),
                backgroundColor: 'rgba(124, 58, 237, 0.45)',
                borderColor: COLORS.secondary,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(ctx) {
                            const raw = ctx.raw;
                            return [
                                raw.label,
                                `Rotación: ${raw.x} u.`,
                                `Eficiencia: ${raw.y}%`,
                                `Costo unit.: ${money(raw.unit_cost)}`,
                                `Stock: ${money(raw.stock_value)}`,
                            ];
                        },
                    },
                },
            },
            scales: {
                x: {
                    title: { display: true, text: 'Rotación (unidades administradas)' },
                    beginAtZero: true,
                },
                y: {
                    title: { display: true, text: 'Eficiencia operativa (consumo / stock %)' },
                    beginAtZero: true,
                },
            },
        },
    });
}

function renderSupplierScatter(data) {
    const el = canvas('chart-supplier-scatter');
    if (! el) return;

    if (! data?.length) {
        emptyState(el.getContext('2d'));
        return;
    }

    new Chart(el, {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Proveedores',
                data: data.map((item) => ({
                    x: item.x,
                    y: item.y,
                    label: item.label,
                })),
                backgroundColor: COLORS.info,
                borderColor: COLORS.info,
                pointRadius: 8,
                pointHoverRadius: 10,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(ctx) {
                            const raw = ctx.raw;
                            return [
                                raw.label,
                                `Ciclo reposición: ${raw.x} días`,
                                `Cumplimiento: ${raw.y}%`,
                            ];
                        },
                    },
                },
            },
            scales: {
                x: {
                    title: { display: true, text: 'Tiempo de reposición (días)' },
                    beginAtZero: true,
                },
                y: {
                    title: { display: true, text: 'Tasa de cumplimiento (%)' },
                    min: 0,
                    max: 100,
                },
            },
        },
    });
}

function renderPurchasesDonut(data) {
    const el = canvas('chart-purchases-donut');
    if (! el) return;

    if (! data?.labels?.length) {
        emptyState(el.getContext('2d'));
        return;
    }

    const palette = [
        COLORS.primary,
        COLORS.secondary,
        COLORS.info,
        COLORS.success,
        COLORS.warning,
        COLORS.danger,
        COLORS.muted,
    ];

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: data.labels.map((_, i) => palette[i % palette.length]),
                borderWidth: 2,
                borderColor: '#fff',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${money(ctx.raw)}`,
                    },
                },
            },
        },
    });
}

function renderLossControl(data) {
    const el = canvas('chart-loss-control');
    if (! el) return;

    if (! data?.labels?.length) {
        emptyState(el.getContext('2d'));
        return;
    }

    const alertIndexes = new Set((data.alerts ?? []).map((a) => a.index));
    const pointColors = data.values.map((_, i) => (alertIndexes.has(i) ? COLORS.danger : COLORS.primary));

    new Chart(el, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Pérdidas (merma + vencimiento)',
                    data: data.values,
                    borderColor: COLORS.primary,
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    fill: false,
                    tension: 0.2,
                    pointBackgroundColor: pointColors,
                    pointBorderColor: pointColors,
                    pointRadius: 4,
                },
                {
                    label: 'Media',
                    data: data.labels.map(() => data.mean),
                    borderColor: COLORS.mean,
                    borderDash: [6, 4],
                    pointRadius: 0,
                    borderWidth: 2,
                },
                {
                    label: 'Límite superior (UCL)',
                    data: data.labels.map(() => data.ucl),
                    borderColor: COLORS.danger,
                    borderDash: [4, 4],
                    pointRadius: 0,
                    borderWidth: 1.5,
                },
                {
                    label: 'Límite inferior (LCL)',
                    data: data.labels.map(() => data.lcl),
                    borderColor: COLORS.success,
                    borderDash: [4, 4],
                    pointRadius: 0,
                    borderWidth: 1.5,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${money(ctx.raw)}`,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (value) => money(value) },
                },
            },
        },
    });

    const alertsEl = document.getElementById('loss-control-alerts');
    if (alertsEl) {
        if (! data.alerts?.length) {
            alertsEl.innerHTML = '<span class="text-sm text-success">Sin puntos fuera de control en el período.</span>';
        } else {
            alertsEl.innerHTML = data.alerts
                .map((a) => `<span class="badge badge-error badge-outline">${a.label}: ${money(a.value)}</span>`)
                .join(' ');
        }
    }
}

function renderMovementFunnel(data) {
    const el = canvas('chart-movement-funnel');
    if (! el) return;

    if (! data?.labels?.length || data.values.every((v) => Number(v) === 0)) {
        emptyState(el.getContext('2d'));
        return;
    }

    const palette = [COLORS.success, COLORS.info, COLORS.primary, COLORS.danger];

    new Chart(el, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Valor CLP',
                data: data.values,
                backgroundColor: palette,
                borderRadius: 8,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => money(ctx.raw),
                    },
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { callback: (value) => money(value) },
                },
            },
        },
    });
}

const payload = readChartsPayload();

if (payload) {
    renderInventoryBars(payload.inventory_by_category);
    renderExpiryGauge(payload.expiry_gauge);
    renderConsumptionTrend(payload.consumption_trend);
    renderRotationBubble(payload.rotation_bubble);
    renderSupplierScatter(payload.supplier_scatter);
    renderPurchasesDonut(payload.purchases_by_supplier);
    renderLossControl(payload.loss_control);
    renderMovementFunnel(payload.movement_funnel);
}
