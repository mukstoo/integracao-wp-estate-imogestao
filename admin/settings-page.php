<div class="wrap">
    <p>Here is where the form would go if I actually had options.</p>
    <form id="adminSettingsForm" method="post" action="" onsubmit="return false;">
        <div class="input-group">
            <label for="apiKey">API Key: </label>
            <input type="text" id="apiKey" name="apiKey" value="<?php echo esc_attr(get_option('my_api_key')); ?>">
            <button type="button" id="setApiKey">Set API Key</button>
        </div>
        <div class="input-group">
            <button type="button" id="analyze">Analyze</button>
            <div id="analysisResults"></div>
        </div>
        <div class="input-group">
            <label for="nextRunTime">Next Scheduled Run: </label>
            <input type="datetime-local" id="nextRunTime" name="nextRunTime">
            <button type="button" id="setNextRunTime">Set Next Run Time</button>
        </div>
        <div class="input-group">
            <button type="button" id="viewLog">View Last Run Log</button>  <!-- New button to view log -->
        </div>
        <div class="input-group">
            <button type="button" id="syncData">Sync Data</button>
        </div>
    </form>
    <div id="results"></div>
</div>
