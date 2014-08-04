/*
* Copyright (c) Vinicius Gerevini, distributed
* as-is and without warranty under the MIT License.
* See http://opensource.org/licenses/MIT for more information.
* This information must remain intact.
*/

(function(global, $) {

	// Define core
	var codiad = global.codiad,
		scripts = document.getElementsByTagName('script'),
		path = scripts[scripts.length - 1].src.split('?')[0],
		curpath = path.split('/').slice(0, -1).join('/') + '/';

	// Instantiates plugin
	$(function() {
		codiad.Compass.init();
	});

	codiad.Compass = {
		path: curpath,
		controller: curpath + "controller.php",
		dialog: curpath + "settings-dialog.php",
		logDialog: curpath + "console-dialog.php",

		isWatching: false,
		isCompiling: false,
		isCompassInstalled: false,

		settings: {},

		logListenerSchedule: 0,
		watchListenerSchedule: 0,


		init: function() {
			var _self = this;
			
			this.loadSettings();

			$("#editor-bottom-bar").append('<div id="compassWatch" title="Compass"><div class="divider"></div><a class="ico-wrapper" onclick="codiad.Compass.showLogModal();"><span>compass:&nbsp;&nbsp;</span><span class="status">stopped</span></a></div>');

			_self.isInstalled(_self.initContextMenu);
			
			_self.isRunning();
		},


		/*
		* Starts compass watch
		
		* @param {String} path Path of folder with configuration file
		*/
		watch: function(path) {
			var _self = this;

			if (this.isWatching)
				return;

			$.getJSON(_self.controller + '?action=watch&path=' + path, function(result) {
				if (result.status == 'success') {
					$("#compassWatch .status").html("running");
					_self.isWatching = true;
					codiad.message.success("Compass is running");
					
					if (_self.settings.showWatchStatusInBottomBar) {
						_self.watchStatusListener(_self);
					}
					codiad.filemanager.rescan(path);
				} else {
					$("#compassWatch .status").html("failed");
					codiad.message.error("Compass error");
				}
				codiad.filemanager.contextMenuHide();
			});
		},

		/*
		 * Stop compass watch
		 */
		stopWatch: function() {
			var _self = this;
			if (!this.isWatching)
				return;

			$.getJSON(_self.controller + '?action=stop-watch', function(result) {
				if (result.status == 'success') {

					_self.stopWatchStatusListener();

					$("#compassWatch .status").html("stopped");
					_self.isWatching = false;
					codiad.message.success("Compass stopped");
				} else {
					codiad.message.error("Couldn't stop compass");
				}
				codiad.filemanager.contextMenuHide();
			});
		},

		/*
		 * Clean generated and chache files
		 *
		 * @param {String} path Path of folder with configuration file
		 */
		clean: function(path) {
			var _self = this;
			$.getJSON(_self.controller + '?action=clean&path=' + path, function(result) {
				if (result.status == 'success') {
					codiad.message.success("Compass: project cleanned");
					codiad.filemanager.contextMenuHide();
				}
			});
		},

		/*
		* Create compass project
		
		* @param {String} path Path to new project
		*/
		create: function(path) {
			var _self = this;
			$.getJSON(_self.controller + '?action=create&path=' + path, function(result) {
				if (result.status == 'success') {
					codiad.message.success("Compass: project created");
					codiad.filemanager.rescan(path);
					codiad.filemanager.contextMenuHide();
				}
			});
		},

		/*
		 * Compile scss files to css
		 *
		 * @param {String} path Path of folder with configuration file
		 */
		compile: function(path) {
			var _self = this;
			if (this.isCompiling) {
				codiad.message.error("Compass is busy");
				codiad.filemanager.contextMenuHide();
			}
			_self.isCompiling = true;
			$.getJSON(_self.controller + '?action=compile&path=' + path, function(result) {
				if (result.status == 'success') {
					codiad.message.success("Compass: files compiled");
					codiad.filemanager.rescan(path);
					_self.isCompiling = false;
					codiad.filemanager.contextMenuHide();
				}
			});
		},

		/*
		 * Show configuration modal
		 */
		showConfigurationModal: function() {
			codiad.modal.load(300, this.dialog);
		},

		/*
		 * Show log console modal
		 */
		showLogModal: function() {
			codiad.modal.load(600, this.logDialog);
		},

		/*
		* Verifies if compass is installed
		*
		* @param {Function} callback Function to be executed after verification (if true)
		*/
		isInstalled: function(callback) {
			var _self = this;
			$.getJSON(_self.controller + '?action=is-installed', function(result) {
				if (result.status == 'success') {
					_self.isCompassInstalled = true;
					callback(_self);
				} else {
					$("#compassWatch .status").html("not installed");
				}
			});
		},
		
		/*
		* Verifies if compass watch is running
		*/
		isRunning: function() {
			var _self = this;
			$.getJSON(_self.controller + '?action=is-running', function(result) {
				if (result.status == 'success' && result.message) {
					$("#compassWatch .status").html("running");
					_self.isWatching = true;
					codiad.message.success("Compass is running");
				}
			});
		},

		/*
		* Init context menu with compass entries
		*
		* @param {Object} _self Context
		*/
		initContextMenu: function(_self) {

			amplify.subscribe('context-menu.onShow', function(obj) {
				var ext = _self.getFileExtension(obj.path);

				$('#context-menu').append('<hr class="codiad-compass">');
				_self.createContextMenuEntry('Compass create', 'icon-plus-circled', 'codiad.Compass.create($(\'#context-menu\').attr(\'data-path\'));', 'directory-only');

				if (obj.type == 'directory' || obj.type == 'root') {
					// Verify if there is configuration file
					$.getJSON(_self.controller + "?action=has-config&path=" + obj.path, function(data) {
						if (data.status == 'success' && data.hasConfig) {
							_self.createContextMenuEntry('Compass clean', 'icon-water', 'codiad.Compass.clean($(\'#context-menu\').attr(\'data-path\'));', 'directory-only');
							if (_self.isWatching)
								_self.createContextMenuEntry('Compass stop', 'icon-stop', 'codiad.Compass.stopWatch();', '');
							else
								_self.createContextMenuEntry('Compass watch', 'icon-eye', 'codiad.Compass.watch($(\'#context-menu\').attr(\'data-path\'));', '');

							_self.createContextMenuEntry('Compass compile', 'icon-code', 'codiad.Compass.compile($(\'#context-menu\').attr(\'data-path\'));', '');
						}
					});
				}
			});

			amplify.subscribe('context-menu.onHide', function() {
				$('.codiad-compass').remove();
			});
		},

		/*
		* Create element in context menu
		*
		* @param {String} text Text of entry
		* @param {String} icon Class of icon for entry
		* @param {String} onClickActionString String of onClick action
		* @param {String} context Elements int wich menu will appear [ directory-only, file-only, both]
		*/
		createContextMenuEntry: function(text, icon, onClickActionString, context) {
			$('#context-menu').append('<a class="' + context + ' codiad-compass" onclick="' + onClickActionString + '"><span class="' + icon + '"></span>' + text + '</a>');
		},

		/*
		 * Get extension of file
		 *
		 * @param {String} path File path
		 */
		getFileExtension: function(path) {
			return path.substring(path.lastIndexOf(".") + 1);
		},

		/*
		 * Load settings
		 *
		 */
		loadSettings: function() {
			var _self = this;
			$.getJSON(this.controller + "?action=load-settings", function(data) {
				_self.settings = data;
			});
		},

		/*
		 * Set settings into settings form modal elements
		 */
		loadSettingsToDialog: function() {

			$("#compassPluginSettingsForm [name = 'logBufferMaxLines']").val(this.settings.logBufferMaxLines);

			if (this.settings.showWatchStatusInBottomBar) {
				$("#compassPluginSettingsForm [name = 'showWatchStatusInBottomBar']").attr("checked", "checked");
			}

			if (!this.isCompassInstalled) {
				$(".compass-not-installed-alert").show();
			}
		},

		/*
		 * Get values of settings modal form and save in settings file
		 */
		saveSettings: function() {
			var _self = this;

			this.settings.logBufferMaxLines = $("#compassPluginSettingsForm [name = 'logBufferMaxLines']").val();
			this.settings.showWatchStatusInBottomBar = $("#compassPluginSettingsForm [name = 'showWatchStatusInBottomBar']").is(":checked");

			$.post(this.controller + "?action=save-settings", {
				settings: this.settings
			}, function(data) {
				var json = JSON.parse(data);
				if (json.status == "error") {
					codiad.message.error("Compass: couldn't save settings");
				} else {
					codiad.message.success("Compass: settings saved");
				}
				_self.loadSettings();
			});
		},

		/*
		 * Get data from watch log file and set it in log view
		 */
		getLog: function() {
			var _self = this;
			$.getJSON(_self.controller + "?action=get-log&lines=" + _self.settings.logBufferMaxLines, function(data) {
				if (data.status == "error") {
					codiad.message.error("Compass: couldn't load log");
				} else {
					$("#compassLogView div").html(data.message);
					$("#compassLogView").scrollTop($("#compassLogView div").height());
				}
			});
		},
		
		/*
		* Erase data of log file
		*/
		clearLog: function() {
			$.getJSON(this.controller + "?action=clear-log", function(data) {
				if(data.status == 'success') {
					codiad.message.success('Log file was clean');
				}
			});
		},

		/*
		 * Get data from log file while log view is visible
		 */
		logListener: function() {
			var _self = this;
			_self.getLog();
			if ($("#compassLogView").is(":visible")) {
				_self.logListenerSchedule = setTimeout(function() {
					_self.logListener();
				}, 4000);
			}
		},

		/*
		 * Stop log listener
		 */
		stopLogListener: function() {
			clearTimeout(this.logListenerSchedule);
		},
		
		/*
		*  Start/Stop log view update
		*/
		toggleLogListener: function(button) {
			if(button.getAttribute('data-action') == 'stop') {
				this.stopLogListener();
				button.setAttribute('data-action', 'start');
				button.innerHTML = "Start log update";
			} else {
				this.logListener();
				button.setAttribute('data-action', 'stop');
				button.innerHTML = "Stop log update";
			}
		},

		/*
		 * Get latest execution time
		 */
		getWatchLastCompileTime: function() {
			var _self = this;
			$.getJSON(_self.controller + "?action=get-last-execution-time", function(data) {
				if (!(data.status == "error" || !data.message)) {
					$("#compassWatch .status").html("running | Last compile: "+data.message);
				}
			});
		},

		/*
		 * Start watch status listener
		 */
		watchStatusListener: function(_self) {
			_self.getWatchLastCompileTime();
			_self.watchListenerSchedule = setTimeout(function() {
				_self.watchStatusListener(_self);
			}, 4000);
		},

		/*
		 * Stop watch status listener
		 */
		stopWatchStatusListener: function() {
			clearTimeout(this.watchListenerSchedule);
		}

	};

})(this, jQuery);