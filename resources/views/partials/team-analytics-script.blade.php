const initializeTeamAnalyticsCharts = () => {
    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.color = '#a1a1aa';

    const analytics = @json($analytics ?? []);
    const monthlyCanvas = document.getElementById('monthlyChart');
    const weeklyCanvas = document.getElementById('weeklyChart');

    if (monthlyCanvas) {
        const existingMonthlyChart = Chart.getChart(monthlyCanvas);
        if (existingMonthlyChart) {
            existingMonthlyChart.destroy();
        }

        new Chart(monthlyCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: analytics.monthlyLabels ?? [],
                datasets: [{
                    label: 'Uploads',
                    data: analytics.monthlyUploads ?? [],
                    backgroundColor: '#0c4d05',
                    borderRadius: 6,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y ?? 0;
                                return `${value} upload${value === 1 ? '' : 's'}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: '#f4f4f5'
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    if (weeklyCanvas) {
        const existingWeeklyChart = Chart.getChart(weeklyCanvas);
        if (existingWeeklyChart) {
            existingWeeklyChart.destroy();
        }

        new Chart(weeklyCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: analytics.weeklyLabels ?? [],
                datasets: [{
                    label: 'Weekly uploads',
                    data: analytics.weeklyUploads ?? [],
                    borderColor: '#0c4d05',
                    backgroundColor: 'rgba(12, 77, 5, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y ?? 0;
                                return `${value} upload${value === 1 ? '' : 's'}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: '#f4f4f5'
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });
    }

};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeTeamAnalyticsCharts, { once: true });
} else {
    initializeTeamAnalyticsCharts();
}
