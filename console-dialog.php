<label>Compass Plugin Log</label>
<br/>
<pre id="compassLogView" class="compass-console-log">
	<div>
	</div>
</pre>
<div class="buttons">
	<button onclick="codiad.Compass.toggleLogListener(this); return false;" class="btn-left" data-action="stop" >Stop log update</button>
	<button onclick="codiad.Compass.clearLog(); return false;" class="btn-middle" >Clear console</button>
	<button onclick="codiad.Compass.stopLogListener(); codiad.modal.unload();" class="btn-right" >Close</button>
</div>

<script>
    codiad.Compass.logListener();
</script>