const dashboardData = window.dashboardData

if (!dashboardData || !Array.isArray(dashboardData.domains) ||
    !Array.isArray(dashboardData.subdomains) || !Array.isArray(dashboardData.records)) {
    throw new Error("Dashboard data is missing or invalid")
}

const { domains, subdomains, records } = dashboardData

function setMetric(id, value) {
    const element = document.getElementById(id)
    if (element) element.textContent = value.toLocaleString()
}

function setEmptyState(id, isEmpty) {
    document.getElementById(id)?.classList.toggle("hidden", !isEmpty)
}

function countBy(rows, key, values) {
    const counts = Object.fromEntries(values.map(value => [value, 0]))
    rows.forEach(row => {
        const value = String(row[key] ?? "").toLowerCase()
        if (Object.prototype.hasOwnProperty.call(counts, value)) counts[value] += 1
    })
    return counts
}

function createChart(canvasId, config) {
    const canvas = document.getElementById(canvasId)
    if (!canvas) throw new Error(`Missing dashboard chart canvas: ${canvasId}`)
    return new Chart(canvas, config)
}

function renderDashboard() {
    setMetric("totalDomains", domains.length)
    setMetric("totalSubdomains", subdomains.length)
    setMetric("totalRecords", records.length)

    const statusCounts = countBy(subdomains, "status", ["active", "inactive", "unknown"])
    setMetric("activeSubdomains", statusCounts.active)
    setMetric("inactiveSubdomains", statusCounts.inactive)

    if (typeof Chart === "undefined") {
        console.error("Chart.js did not load; dashboard charts cannot be rendered")
        return
    }

    setEmptyState("subdomainStatusEmpty", subdomains.length === 0)
    createChart("subdomainStatusChart", {
        type: "doughnut",
        data: {
            labels: ["Active", "Inactive", "Unknown"],
            datasets: [{
                data: [statusCounts.active, statusCounts.inactive, statusCounts.unknown],
                backgroundColor: ["#059669", "#dc2626", "#94a3b8"],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { position: "bottom" } }
        }
    })

    const recordTypes = ["A", "AAAA", "CNAME", "MX", "TXT", "NS", "SOA", "SRV"]
    const recordCounts = countBy(records, "record_type", recordTypes.map(type => type.toLowerCase()))
    setEmptyState("recordTypeEmpty", records.length === 0)
    createChart("recordTypeChart", {
        type: "doughnut",
        data: {
            labels: recordTypes,
            datasets: [{
                data: recordTypes.map(type => recordCounts[type.toLowerCase()]),
                backgroundColor: ["#0284c7", "#4f46e5", "#7c3aed", "#d97706", "#0d9488", "#2563eb", "#9333ea", "#ea580c"],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { position: "bottom" } }
        }
    })

    const subdomainsPerDomain = new Map(domains.map(domain => [String(domain.id), 0]))
    subdomains.forEach(subdomain => {
        const domainId = String(subdomain.domain_id)
        if (subdomainsPerDomain.has(domainId)) {
            subdomainsPerDomain.set(domainId, subdomainsPerDomain.get(domainId) + 1)
        }
    })

    setEmptyState("subdomainsByDomainEmpty", domains.length === 0)
    createChart("subdomainsByDomainChart", {
        type: "bar",
        data: {
            labels: domains.map(domain => domain.name),
            datasets: [{
                label: "Subdomains",
                data: domains.map(domain => subdomainsPerDomain.get(String(domain.id)) ?? 0),
                backgroundColor: "#0284c7",
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: "y",
            maintainAspectRatio: false,
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { display: false } }
        }
    })
}

renderDashboard()
