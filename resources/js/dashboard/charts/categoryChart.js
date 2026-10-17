// Helper function to bring in and return the scans
async function loadScans() {
    try {
        const res = await fetch('/json');
        if (!res.ok) {
            return [];
        }
        const scans = await res.json();
        return Array.isArray(scans) ? scans : [];
    } catch {
        return [];
    }
}

// Helper function to build the doughnut charts data
function buildDoughnutData(scans) {
    // Stores the count of each category
    let clean = 0;
    let malicious = 0;
    let suspicious = 0;

    // Loops over each of the counts
    scans.forEach(s => {
        if (s.malicious > 0) {
            malicious++;
        } else if (s.suspicious > 0) {
            suspicious++;
        } else {
            clean++;
        }
    });

    return { clean, malicious, suspicious };
}

export async function renderDoughnut() {
    const canvas = document.getElementById('categoryChart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    const scans = await loadScans();
    const { clean, malicious, suspicious } = buildDoughnutData(scans);

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Clean', 'Malicious', 'Suspicious'],
            datasets: [{
                data: [clean, malicious, suspicious],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',   // green
                    'rgba(220, 53, 69, 0.7)',   // red
                    'rgba(255, 193, 7, 0.7)'    // amber
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderDoughnut);
} else {
    renderDoughnut();
}