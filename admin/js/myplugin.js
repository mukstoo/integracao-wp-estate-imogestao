document.addEventListener('DOMContentLoaded', async () => {
    const apiKeyElement = document.getElementById("apiKey");
    const nextRunTimeElement = document.getElementById("nextRunTime");
    const analysisResultsElement = document.getElementById("analysisResults");
    const resultsElement = document.getElementById("results");

    async function apiRequest(action, params = {}) {
        const url = new URL(myplugin_ajax.ajax_url);
        url.searchParams.set('action', action);
        Object.keys(params).forEach(key => url.searchParams.set(key, params[key]));
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(response.statusText);
        }
        return await response.json();
    }

    async function setApiKey() {
        try {
            const data = await apiRequest('set_api_key', { api_key: apiKeyElement.value });
            updateUI(data.data);
        } catch (error) {
            showError(error.message);
        }
    }

    async function analyze() {
        try {
            const data = await apiRequest('analyze_data');
            const postsToDeleteLength = Object.keys(data.data.postsToDelete).length;
            const postsToCreateLength = Object.keys(data.data.postsToCreate).length;
            const postsToUpdateLength = Object.keys(data.data.postsToUpdate).length;  // New line

            analysisResultsElement.innerHTML = `
                <p>Posts to Delete: ${postsToDeleteLength} <button type="button" id="executeDelete">Delete</button></p>
                <p>Posts to Create: ${postsToCreateLength} <button type="button" id="executeCreate">Create</button></p>
                <p>Number of posts to create at a time: <input type="number" id="numPostsToCreate" min="1" max="${postsToCreateLength}" value="1"></p>
                <p>Posts to Update: ${postsToUpdateLength} <button type="button" id="executeUpdate">Update</button></p> <!-- New line -->
            `;

            document.getElementById("executeDelete").addEventListener("click", async () => {
                try {
                    const data = await apiRequest('delete_posts');
                    updateUI(data.data);
                } catch (error) {
                    showError(error.message);
                }
            });

            document.getElementById("executeCreate").addEventListener("click", async (event) => {
                event.preventDefault();
                const numPostsToCreate = document.getElementById("numPostsToCreate").value; // Get the number from the input field
                createPosts(numPostsToCreate); // Pass this as an argument to your createPosts function
            });

            document.getElementById("executeUpdate").addEventListener("click", async () => {
                try {
                    const data = await apiRequest('update_posts'); // Assuming apiRequest can handle 'update_posts' action
                    updateUI(data.data);
                } catch (error) {
                    showError(error.message);
                }
            });

        } catch (error) {
            showError(error.message);
        }
    }

    async function createPosts(numPostsToCreate) {
        let processedPosts = 0;
        let totalPosts = 0;
        const createAllPosts = false; // Set this to false for just one post

        do {
            const data = await apiRequest('create_posts', { totalPosts, processedPosts, numPostsToCreate });

            console.log(data);
            if (data.success) {
                processedPosts = data.data.processedPosts;
                totalPosts = data.data.totalPosts;

                data.data.messages.forEach(msg => {
                    updateUI(msg);
                });

                resultsElement.innerHTML += `<p>Progress: ${processedPosts} of ${totalPosts} posts created</p>`;
            } else {
                showError("An error occurred");
                break;
            }

            if (!createAllPosts) {
                break;
            }

        } while (processedPosts < totalPosts);

        if (processedPosts >= totalPosts) {
            updateUI('Finished! All posts created successfully');
        }
    }

    async function fetchNextRunTime() {
        try {
            const data = await apiRequest('get_next_run_time');
            nextRunTimeElement.value = data.data.next_run_time;
        } catch (error) {
            showError(error.message);
        }
    }

    async function setNextRunTime() {
        try {
            let localDateTime = nextRunTimeElement.value;
            if (localDateTime) {
                // Replace 'T' with a space
                localDateTime = localDateTime.replace('T', ' ');
    
                // Convert to a Date object
                const localDate = new Date(localDateTime);
    
                // Convert to UTC
                const utcDate = new Date(localDate.getTime() - (localDate.getTimezoneOffset() * 60000)).toISOString().slice(0, 19).replace('T', ' ');
    
                const data = await apiRequest('set_next_run_time', { next_run_time: utcDate });
                console.log('Set Next Run Time Response:', data); // Debug line
                updateUI(`Next run time set to: ${data.data.next_run_time}`);
            } else {
                showError("Please enter a valid date and time.");
            }
        } catch (error) {
            console.log('Set Next Run Time Error:', error); // Debug line
            showError(error.message);
        }
    }
    

    async function viewLastRunLog() {
        try {
            const data = await apiRequest('get_last_run_log');
            console.log('View Last Run Log Response:', data); // Debug line
            if (data.success) {
                // Assuming the log is an array of strings
                data.data.log.forEach(entry => {
                    updateUI(entry);
                });
            }
        } catch (error) {
            console.log('View Last Run Log Error:', error); // Debug line
            showError(error.message);
        }
    }


    function updateUI(msg) {
        const textNode = document.createTextNode(msg);
        const lineBreak = document.createElement("br");
        resultsElement.appendChild(textNode);
        resultsElement.appendChild(lineBreak);
    }

    function showError(message) {
        const textNode = document.createTextNode(`Error: ${message}`);
        resultsElement.appendChild(textNode);
    }

    document.getElementById("setApiKey").addEventListener("click", setApiKey);
    document.getElementById("analyze").addEventListener("click", analyze);
    document.getElementById("setNextRunTime").addEventListener("click", setNextRunTime);  // New line
    document.getElementById("viewLog").addEventListener("click", viewLastRunLog);  // New line
    document.getElementById('syncData').addEventListener('click', async () => {
        try {
            const url = new URL(myplugin_ajax.ajax_url);
            url.searchParams.set('action', 'sync_data');
            const response = await fetch(url);
            const data = await response.json();
            if (data.success) {
                console.log(data.data);
            } else {
                console.error(data);
            }
        } catch (error) {
            console.error(error);
        }
    });

    fetchNextRunTime();
});