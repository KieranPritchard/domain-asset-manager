// Function to fetch data
async function getDomainsData(){
    // Stores the response
    const response = await fetch("/home/json");

    // Returns the response
    return response.body
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