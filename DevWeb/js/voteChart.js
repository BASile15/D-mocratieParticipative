function initializeChart(data) {
    const ctx = document.getElementById('voteChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pour','Abstention' ,'Contre'],
            datasets: [{
                data: [data.Pour, data.Abstention , data.Contre],
                backgroundColor: ['#4CAF50','#3371ff' ,'#F44336'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw || 0;
                            const percentage = ((value / data.totalVotes) * 100).toFixed(2);
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}
