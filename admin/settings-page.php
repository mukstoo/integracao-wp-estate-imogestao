<div class="wrap">
    <p>Here is where the form would go if I actually had options.</p>
    <div class="input-group">
        <label for="apiKey">API Key: </label>
        <input type="text" id="apiKey" name="apiKey" value="<?php echo get_option('my_api_key'); ?>">
        <button id="setApiKey">Set API Key</button>
    </div>
    <div class="input-group">
        <label for="maxItems">Max Items: </label>
        <input type="number" id="maxItems" name="maxItems" min="1" value="1">
        <button id="fetchData">Fetch Data</button>
    </div>
    <div class="input-group">
        <label for="storedItems">Stored Items: </label>
        <input type="number" id="storedItems" name="storedItems" min="0" value="0">
        <button id="setStoredItems">Set Stored Items</button>
        <button id="clearData">Clear Data</button>
    </div>
    <div class="input-group">
        <button id="syncData">Sync Data</button>
    </div>

    <div id="results"></div>
</div>