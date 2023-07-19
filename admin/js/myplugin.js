/* document.addEventListener('DOMContentLoaded', async (event) => {
    let itemsProcessed = parseInt(localStorage.getItem("itemsProcessed")) || 0;
    document.getElementById("storedItems").value = itemsProcessed;

    async function fetchData() {
        itemsProcessed = parseInt(document.getElementById("storedItems").value);
        const maxItems = parseInt(document.getElementById("maxItems").value);
        const itemsToProcess = itemsProcessed + maxItems;
        const page = Math.floor(itemsProcessed / 24);

        try {
            const response = await fetch(`${myplugin_ajax.ajax_url}?action=fetch_data&page=${page}&items_processed=${itemsProcessed}&max_items=${itemsToProcess}`);
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            const data = await response.json();
            console.log(data);
            updateUI(data.data);
            updateLocalStorage(data.data);
        } catch (error) {
            showError(error.message);
        }
    }

    function updateUI(data) {
        const results = document.getElementById("results");
        const textNode = document.createTextNode(data.message);
        const lineBreak = document.createElement("br");
        results.appendChild(textNode);
        results.appendChild(lineBreak); // Add a line break after each message
    }

    function updateLocalStorage(data) {
        console.log(data);
        console.log(data.numItemsFetched);
        if (!isNaN(data.numItemsFetched)) {
            console.log(data.numItemsFetched);
            itemsProcessed += data.numItemsFetched;
            localStorage.setItem("itemsProcessed", itemsProcessed);
            document.getElementById("storedItems").value = itemsProcessed;
        }
    }

    function showError(message) {
        const results = document.getElementById("results");
        const textNode = document.createTextNode(`Error: ${message}`);
        results.appendChild(textNode);
    }

    function clearData() {
        localStorage.removeItem("itemsProcessed");
        itemsProcessed = 0;
        document.getElementById("storedItems").value = itemsProcessed;
        document.getElementById("results").textContent = "";
    }

    function updateStoredItems() {
        const storedItems = parseInt(document.getElementById("storedItems").value);
        localStorage.setItem("itemsProcessed", storedItems);
        itemsProcessed = storedItems;
        document.getElementById("storedItems").value = itemsProcessed;
    }

    document.getElementById("fetchData").addEventListener("click", fetchData);
    document.getElementById("clearData").addEventListener("click", clearData);
    document.getElementById("setStoredItems").addEventListener("click", updateStoredItems);
});
 */
document.addEventListener('DOMContentLoaded', async () => {
    const storedItemsElement = document.getElementById("storedItems");
    const resultsElement = document.getElementById("results");
    let itemsProcessed = parseInt(localStorage.getItem("itemsProcessed")) || 0;
    storedItemsElement.value = itemsProcessed;

    async function fetchData() {
        itemsProcessed = parseInt(storedItemsElement.value);
        const maxItems = parseInt(document.getElementById("maxItems").value);
        const itemsToProcess = itemsProcessed + maxItems;
        const page = Math.floor(itemsProcessed / 24);

        try {
            const response = await fetch(`${myplugin_ajax.ajax_url}?action=fetch_data&page=${page}&items_processed=${itemsProcessed}&max_items=${itemsToProcess}`);
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            const data = await response.json();
            updateUI(data.data);
            updateLocalStorage(data.data);
        } catch (error) {
            showError(error.message);
        }
    }

    function updateUI(data) {
        const textNode = document.createTextNode(data.message);
        const lineBreak = document.createElement("br");
        resultsElement.appendChild(textNode);
        resultsElement.appendChild(lineBreak); // Add a line break after each message
    }

    function updateLocalStorage(data) {
        if (!isNaN(data.numItemsFetched)) {
            itemsProcessed += data.numItemsFetched;
            localStorage.setItem("itemsProcessed", itemsProcessed);
            storedItemsElement.value = itemsProcessed;
        }
    }

    function showError(message) {
        const textNode = document.createTextNode(`Error: ${message}`);
        resultsElement.appendChild(textNode);
    }

    function clearData() {
        localStorage.removeItem("itemsProcessed");
        itemsProcessed = 0;
        storedItemsElement.value = itemsProcessed;
        resultsElement.textContent = "";
    }

    function updateStoredItems() {
        const storedItems = parseInt(storedItemsElement.value);
        localStorage.setItem("itemsProcessed", storedItems);
        itemsProcessed = storedItems;
        storedItemsElement.value = itemsProcessed;
    }

    document.getElementById("fetchData").addEventListener("click", fetchData);
    document.getElementById("clearData").addEventListener("click", clearData);
    document.getElementById("setStoredItems").addEventListener("click", updateStoredItems);
});
