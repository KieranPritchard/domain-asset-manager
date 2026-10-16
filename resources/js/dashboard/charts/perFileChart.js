// Helper function to bring in and return the scans
async function loadScans() {
    const res = await fetch('/json'); // whatever you name the file above
    const scans = await res.json();
    return scans;
}

// Helper function to build the stacked bar chart data
function buildStackedData(scans) {
    // Maps the filename to each label
    const labels = scans.map(s => s.original_filename);

    // Stores each of the different scan types
    const malicious = scans.map(s => s.malicious);
    const suspicious = scans.map(s => s.suspicious);
    const undetected = scans.map(s => s.undetected);
    const harmless = scans.map(s => s.harmless);

    // Returns the data
    return { labels, malicious, suspicious, undetected, harmless };
}

// Function to render a stacked chart
async function renderStacked() {
    // Loads the scans and passes them into the help function to build the data
    const scans = await loadScans();
    const { labels, malicious, suspicious, undetected, harmless } = buildStackedData(scans);

    // Builds the chart up
    new Chart(document.getElementById('scanStacked'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
            {
                label: 'Malicious',
                data: malicious,
                backgroundColor: 'rgba(220, 53, 69, 0.7)'
            },
            {
                label: 'Suspicious',
                data: suspicious,
                backgroundColor: 'rgba(255, 193, 7, 0.7)'
            },
            {
                label: 'Undetected',
                data: undetected,
                backgroundColor: 'rgba(108, 117, 125, 0.5)'
            },
            {
                label: 'Harmless',
                data: harmless,
                backgroundColor: 'rgba(40, 167, 69, 0.7)'
            }
            ]
        },
        options: {
            responsive: true,
            plugins: {
            legend: { position: 'bottom' }
            },
            scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true }
            }
        }
    });
}

// Runs the function
renderStacked();