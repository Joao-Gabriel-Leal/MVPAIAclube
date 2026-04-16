import Chart from 'chart.js/auto';

const initializedCharts = new WeakMap();

export function initializeAnalyticsCharts(root = document) {
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

        initializedCharts.set(canvas, new Chart(canvas, {
            responsive: true,
            maintainAspectRatio: false,
            ...config,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        backgroundColor: '#1e1b4b',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                    },
                    ...config.options?.plugins,
                },
                ...config.options,
            },
        }));
    });
}
