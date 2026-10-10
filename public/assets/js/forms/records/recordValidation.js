// Shared helpers for the add and edit record forms.
// Load this before addRecord.js and editRecord.js.

// Placeholder hints for each record type
const RECORD_PLACEHOLDERS = {
    A: "e.g. 203.0.113.10",
    AAAA: "e.g. 2001:db8::1",
    CNAME: "e.g. target.example.com",
    MX: "e.g. mail.example.com",
    TXT: "e.g. v=spf1 include:_spf.example.com ~all",
    NS: "e.g. ns1.example.com",
    SOA: "e.g. ns1.example.com admin.example.com ...",
    SRV: "weight port target, e.g. 5 5060 sip.example.com"
}

// Types that use a priority, same as PRIORITY_TYPES in the controller
const PRIORITY_TYPES = ["MX", "SRV"]

// Same rule as valid_hostname() in the controller
const HOSTNAME_REGEX = /^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/i

// Checks a string is a valid IPv4 address, e.g. 203.0.113.10
function isIPv4(value) {
    const parts = value.split(".")

    return parts.length === 4 && parts.every(part =>
        /^\d{1,3}$/.test(part)
        && Number(part) <= 255
        && (part === "0" || !part.startsWith("0"))
    )
}

// Checks a string is a valid IPv6 address, e.g. 2001:db8::1
function isIPv6(value) {
    // Rejects anything that isn't a bare address before handing it to the URL parser
    if (!value.includes(":") || /[\[\]\/\s%]/.test(value)) {
        return false
    }

    try {
        new URL(`http://[${value}]`)
        return true
    } catch {
        return false
    }
}

// Checks a string is a valid hostname, e.g. mail.example.com
function isHostname(value) {
    // A trailing dot is valid in DNS
    const host = value.replace(/\.+$/, "")

    return host.length <= 253 && HOSTNAME_REGEX.test(host)
}

// Returns an error message for the value, or an empty string if it suits the type
function getValueError(type, value) {
    if (value.length === 0) return "Please enter a value"
    if (value.length > 512) return "Value must be 512 characters or fewer"

    switch (type) {
        case "A":
            return isIPv4(value) ? "" : "A records need a valid IPv4 address"

        case "AAAA":
            return isIPv6(value) ? "" : "AAAA records need a valid IPv6 address"

        case "CNAME":
        case "NS":
        case "MX":
            return isHostname(value) ? "" : `${type} records need a valid hostname`

        case "SRV": {
            // Format: "weight port target"
            const parts = value.split(/\s+/)

            if (parts.length !== 3
                || !/^\d+$/.test(parts[0])
                || !/^\d+$/.test(parts[1])
                || Number(parts[1]) > 65535
                || !isHostname(parts[2])) {
                return "SRV value must look like: weight port target"
            }

            return ""
        }

        // TXT and SOA are free-form
        default:
            return ""
    }
}

// Returns an error message for the TTL, or an empty string (TTL is optional)
function getTtlError(raw) {
    const ttl = raw.trim()

    if (ttl === "") return ""

    if (!/^\d+$/.test(ttl) || Number(ttl) > 2147483647) {
        return "TTL must be a whole number of seconds"
    }

    return ""
}

// Returns an error message for the priority, or an empty string
function getPriorityError(type, raw) {
    // Only MX and SRV use a priority
    if (!PRIORITY_TYPES.includes(type)) return ""

    const priority = raw.trim()

    if (priority === "") return `${type} records need a priority`

    if (!/^\d+$/.test(priority) || Number(priority) > 65535) {
        return "Priority must be between 0 and 65535"
    }

    return ""
}

// Shows or hides an error box, returns true when there is no error
function showFieldError(errorId, message) {
    const errorBox = document.getElementById(errorId)

    if (message) {
        errorBox.innerText = message
        errorBox.classList.remove("hidden")
        return false
    }

    errorBox.classList.add("hidden")
    return true
}

// Enables or disables a submit button and swaps the cursor to match
function setSubmitState(buttonId, enabled) {
    const submitButton = document.getElementById(buttonId)

    // Adds or removes the disabled attribute on the button
    submitButton.toggleAttribute("disabled", !enabled)

    // Swaps between the cursor pointer and the disabled one
    submitButton.classList.toggle("cursor-pointer", enabled)
    submitButton.classList.toggle("cursor-not-allowed", !enabled)
}

// Shows or hides the priority field and updates the value placeholder
// prefix is "add" or "edit"
function toggleRecordFields(prefix) {
    const type = document.getElementById(`${prefix}RecordType`).value
    const wrapper = document.getElementById(`${prefix}RecordPriorityWrapper`)
    const priority = document.getElementById(`${prefix}RecordPriority`)
    const value = document.getElementById(`${prefix}RecordValue`)

    const needsPriority = PRIORITY_TYPES.includes(type)

    // A disabled input isn't submitted, and the controller ignores priority for other types
    wrapper.classList.toggle("hidden", !needsPriority)
    priority.disabled = !needsPriority
    priority.required = needsPriority

    value.placeholder = RECORD_PLACEHOLDERS[type] ?? ""
}

// Builds a key for spotting exact duplicates (same subdomain, type and value)
function recordKey(subdomainId, type, value) {
    return `${subdomainId}|${type.toUpperCase()}|${value.trim().toLowerCase()}`
}

// Loads the records from the PHP endpoint, returns [{ id, key }]
async function fetchExistingRecords() {
    try {
        const response = await fetch("/dns-records/json")
        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        const data = await response.json()

        return data.map(record => ({
            id: Number(record.id),
            key: recordKey(record.subdomain_id, record.record_type, record.value)
        }))
    } catch (error) {
        console.error("Could not load records:", error)
        return []
    }
}