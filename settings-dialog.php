<form id="compassPluginSettingsForm">
    <label>Compass Plugin Settings</label>
    <br/>
    Log buffer size (lines)
    <input type="text" name="logBufferMaxLines"/> <br/>
    
    <input type="checkbox" name="showWatchStatusInBottomBar"/> Show last execution in bottom bar
    <br/>
    
    <div class="compass-not-installed-alert">
        Compass is not installed.
    </div>
    
    
    <button onclick="codiad.Compass.saveSettings(); return false;" class="btn-left">Save</button>
    <button onclick="codiad.modal.unload();" class="btn-right">Close</button>
    
    <script>
        codiad.Compass.loadSettingsToDialog();
    </script>
</form>