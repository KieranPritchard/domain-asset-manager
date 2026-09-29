// Function to fetch data
async function getDomainsData(){
    // Stores the response
    const response = await fetch("/home/json");

    // Returns the response
    return response.body
}