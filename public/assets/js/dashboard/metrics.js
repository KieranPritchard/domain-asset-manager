// Functions to fetch data
async function getDomainsData(){
    // Stores the response
    const response = await fetch("/home/domains");

    // Stores the data
    const data = JSON.parse(response.body)

    return data
}

async function getSubdomainsData(){
    // Stores the response
    const response = await fetch("/home/subdomains");

    // Stores the data
    const data = JSON.parse(response.body)

    return data
}

async function getRecordsData(){
    // Stores the response
    const response = await fetch("/home/subdomains");

    // Stores the data
    const data = JSON.parse(response.body)

    return data
}

// Function to display the total domains
async function totalDomains(){
    // Stores the field for the domains
    const totalDomainsField = document.getElementById("totalDomains")

    // Gets the domains data
    const domains = await getDomainsData()

    // Stores the number of domains
    const numOfDomains = domains.length

    // Sets the number of total domains
    totalDomainsField.innerHTML = numOfDomains
}

// Function to display the total domains
async function totalSubdomains(){
    // Stores the field for the domains
    const totalSubdomainsField = document.getElementById("totalSubdomains")

    // Gets the domains data
    const domains = await getSubdomainsData()

    // Filters the subdomains

    // Stores the number of domains
    const numOfDomains = domains.length

    // Sets the number of total domains
    totalSubdomainsField.innerHTML = numOfDomains
}