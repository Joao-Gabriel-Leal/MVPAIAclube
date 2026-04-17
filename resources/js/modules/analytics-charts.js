import Chart from 'chart.js/auto';

const initializedCharts = new WeakMap();
let defaultsConfigured = false;

export function initializeAnalyticsCharts(root = document) {
    configureChartDefaults();

    root.querySelectorAll('canvas[data-chart-source]').forEach((canvas) => {
        if (initializedCharts.has(canvas)) {
            return;
        }

        const sourceId = canvas.dataset.chartSource;

        if (!sourceId) {
            return;
        }

        const source = root.getElementById(sourceId) ?? document.getElementById(sourceId);

        if (!source?.textContent) {
            return;
        }

        const config = JSON.parse(source.textContent);
        const mergedOptions = mergeOptions(baseOptions(config.type), config.options ?? {});

        initializedCharts.set(canvas, new Chart(canvas, {
            ...config,
            options: mergedOptions,
        }));
    });
}

function configureChartDefaults() {
    if (defaultsConfigured) {
        return;
    }

    Chart.defaults.font.family = 'system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';
    Chart.defaults.color = '#64748b';
    Chart.defaults.animation.duration = 220;
    Chart.defaults.datasets.bar.maxBarThickness = 28;
    Chart.defaults.elements.bar.borderSkipped = false;
    Chart.defaults.elements.line.borderWidth = 2.2;
    Chart.defaults.elements.line.tension = 0.32;
    Chart.defaults.elements.point.radius = 2;
    Chart.defaults.elements.point.hoverRadius = 3.2;

    defaultsConfigured = true;
}

function baseOptions(chartType) {
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: {
                top: 2,
                right: 4,
                bottom: 0,
                left: 2,
            },
        },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#475569',
                    boxWidth: 10,
                    boxHeight: 10,
                    padding: 10,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: {
                        size: 10,
                        weight: 700,
                    },
                },
            },
            tooltip: {
                backgroundColor: '#173554',
                titleColor: '#ffffff',
                bodyColor: '#f8fafc',
                padding: 8,
                cornerRadius: 12,
                displayColors: true,
                boxPadding: 3,
                titleFont: {
                    size: 11,
                    weight: 800,
                },
                bodyFont: {
                    size: 10,
                    weight: 600,
                },
            },
        },
    };

    if (isCircularChart(chartType)) {
        return {
            ...commonOptions,
            interaction: {
                mode: 'nearest',
                intersect: true,
            },
            plugins: {
                ...commonOptions.plugins,
                legend: {
                    ...commonOptions.plugins.legend,
                    labels: {
                        ...commonOptions.plugins.legend.labels,
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 8,
                    },
                },
            },
        };
    }

    return {
        ...commonOptions,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            x: {
                border: {
                    display: false,
                },
                grid: {
                    display: false,
                    drawBorder: false,
                },
                ticks: {
                    color: '#64748b',
                    font: {
                        size: 10,
                        weight: 600,
                    },
                },
            },
            y: {
                beginAtZero: true,
                border: {
                    display: false,
                },
                grid: {
                    color: 'rgba(203, 213, 225, 0.55)',
                    drawBorder: false,
                },
                ticks: {
                    precision: 0,
                    color: '#64748b',
                    font: {
                        size: 10,
                        weight: 600,
                    },
                },
            },
        },
    };
}

function mergeOptions(base, override) {
    if (!isPlainObject(override)) {
        return base;
    }

    const result = { ...base };

    Object.entries(override).forEach(([key, value]) => {
        if (isPlainObject(value) && isPlainObject(result[key])) {
            result[key] = mergeOptions(result[key], value);
            return;
        }

        result[key] = value;
    });

    return result;
}

function isPlainObject(value) {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}

function isCircularChart(chartType) {
    return ['doughnut', 'pie', 'polarArea'].includes(chartType);
}
