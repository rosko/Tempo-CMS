/********************************************************************
 * imgLib v0.1.1 03.02.2010
 * Contact me at dev@imglib.endofinternet.net
 * Site: http://www.imglib.endofinternet.net/
 * This copyright notice MUST stay intact for use.
 ********************************************************************/

var imgLib = (function () {
	var
		/*
			Browser check
		*/
		isIE = (navigator.appName == 'Microsoft Internet Explorer') ? true : false,
		isOpera = (window.opera) ? true : false,
		/*
			Property of element on context
			object {
				type:, // "1" if folder or "2" if file
				index: 1, // Index in array of dir_content.files or dir_content.dirs
				name: 'file name'// Name of file or folder
				time: time in miliseconds on show menu
			}
		*/
		context_element,
		elToPaste, //Information about element to past - object{type, src, cmd}
		timer, // Uses for prevent some timer events
		view_type, // Type of files or folders view, the posible options is list, thumbnail, table
		sort_type = 1, // Type of files or folders sort, the posible options is name (1), size (2), date(3)
		upload_form_show = 0, // Indicate if upload form show
		file_input_field_num, // Number of file input field
		ready = false, // Flag that indicate imgLib to ready to work (set to try alfer the all initialisation)
	/*
		AJAX section
	*/
		cur_path = '/', // Path to current dir
	/*
		Current dir content
		dir_content = {
			path: ['dir', 'subDir', 'subSubDir'], // Path to current dir
			inf: {
					is_writable: 1 // The current dir is writable (1) or not (0)
				},
			dirs: [
					{
						name: 'fauna', // Dir name
						empty: 0, // Directory is empty (1) or not (0)
						readable: 1, // Directoy is readable
						date: 1253824760 // Dir date as unix timestamp
					},
					{name: 'flora', empty: 1, readable: 1},
					{name: 'biiiiiig foldeeeeeeeer nameeeeeeeeeeeeee ', empty: 1, readable: 1}
				]
			},
			files: [
					{
						name: 'filename', // File name
						filesize: 100, // File size in bytes
						date: 1253824760, // File date as unix timestamp
						img_size: '1024x768', // If file is image contains the image size
						thumb: '/thmb/filename.jpg' // If file is image contains the path to image thumbnail
					}
			]
		}
	*/
		dir_content = {}, // Curent dir content object
	/*
		Configuration varitables
	*/
		id_name,
		req_url,
		bind_keys,
		enable_upload,
		upload_path,
		allowed_ext,
		max_upload_size,
		max_upload_file_size,
		save_file_cookie,
		save_inf_cookie,
		max_file_name_len,
		enable_browse_subdir,
		enable_file_operation,
		enable_create_dir,
		enable_rename,
		enable_delete,
		enable_create_thumbnail,
		thumbnail_dir,
		tooltips_delay,
		overlay_opacity,
		dbclick_delay,
		on_select,
		on_dblselect,
		on_deselect,
	/*
		Script messages
	*/
	msg = {
		rootPathName: 'Root',
		ajaxLoadingText: 'Loading...',
		moveToUpDirText: 'Up',
		delNonEmptyFolderPromt: 'Remove not empty folder %1?',
		delFileObjPromt: 'Remove %1?',
		enterNewDirNameStr: 'Enter name of new directory',
		defaultNewDirNameStr: 'New Folder',
		enterNewNameWoExtPromt: 'Enter new name of %1:\n(file extension is add automatic)',
		operationFailedStr: 'Operation failed. Error code is %1.',
		ajaxIsReguire: 'This script reguire browser that support the AJAX tehnology!',
		noChange: 'No change!',
		// Context Menu
		newDirTitle: 'Create folder',
		openTitle: 'Open',
		browseTitle: 'Browse',
		copyTitle: 'Copy',
		cutTitle: 'Cut',
		pastTitle: 'Paste',
		deleteTitle: 'Delete',
		renameTitle: 'Rename',
		reloadDirTitle: 'Reload curent directory',
		fileNameTitle: 'File name',
		fileSizeTitle: 'File size',
		fileDateTitle: 'File date',
		dateTitle: 'Date',
		sizeTitle: 'Size',
		imageSizeTitle: 'Image size',
		viewTitle: 'View',
		thumbnailTitle: 'Thumbnail',
		listTitle: 'List',
		tableTitle: 'Table',
		searchTitle: 'Search',
		enterSearchTitle: 'Enter part of file name:',
		sortTitle: 'Sort',
		sortByNameTitle: 'By name',
		sortBySizeTitle: 'By size',
		sortByDateTitle: 'By date',
		uploadTitle: 'Upload',
		cancelTitle: 'Cancel',
		writeProtectTitle: 'Directory is write protected!',
		addFieldTitle: 'Add more field',
		delFieldTitle: 'Delete field',
		selectFirstFileTitle: 'Select first file',
		allowExtTitle: 'Allowed extension',
		maxUploadSizeTitle: 'Max upload size (total/file)',
		pathTitle: 'Path'
	},
	context_menu_items, // Context menu items array
	/*
		Elements
	*/
	img_lib_element, // Main element who contains the imgLib elements
	ajax_load_element, // Element who contains the AJAX loading indicator
	context_menu_element, // Element who contains the context menu
	tulbar_element, // Element who contains the tulbar
	file_list_element, // Element who contains the file list
	tooltips_element // Element who contains the file tooltips
	;

	/*
		Aply the settings
		Waring! Some change like change idName require change also the css file to property display the file list
	*/
	function setSettings(settings) {
		/*
			settings = {
				idName: 'imgLibId', // id of elements to use as root element, if not set, create new
				reqURL: '/script.php', // URL to script for request the AJAX data and upload files
				bindKeys: true, // Bind keyboard shorcuts to some actions Default actions is: 1) Delete element on 'Del'; 2) Open file in new window on 'Shift+Enter'; 3) Rename file on 'F2'; 4) Create directory on 'F7'; 5) Show upload form on 'Insert'; 6) Cut file on 'Ctrl+X'; 7) Copy file on 'Ctrl+C'; 8) Paste file on 'Ctrl+V'.
				enableUpload: true, // Enable or disable the file upload
				uploadPath: '/upload/', // Path to upload folder from root
				startDir: '/some_folder', // Path to some folder for start browsing
				allowedExt: ['jpg', 'gif', 'png'] // Array with displayed allowed extension in upload dialog (only display, not check)
				maxUploadSize: 0, // Display max upload size (in bytes) in upload dialog (only display, not check)
				maxUploadFileSize: 0, // Display max file size (in bytes) to upload in upload dialog (only display, not check)
				saveFileCookie: 'file', // Name of cookie to save/restore elToPaste
				saveInfCookie: 'imglibinf', // Name of cookie to save/restore information like path and view type
				maxFileNameLen: 30, // Maximum file name length
				enableBrowseSubdir: true, // Enable browse sub folder
				enableFileOperation: true, // Enable copy or move file or folder
				enableCreateDir: true, // Enable create new folder
				enableRename: true, // Enable rename file or folder
				enableDelete: true, // Enable delete file or folder
				thumbnailDir: 'thmb', // Name of thumbnail directory, this directory no show
				tooltipsDelay: 400, // Delay to show tooltips
				overlayOpacity: 75, // Overlay opacity in ajax loading layer
				dbclickDelay: 400, // Delay betwen the click to consider as double click
				onSelect: function(file) {alert(file.name);}, // Function to execute when file is selected, file - the item of array dir_content.files[] with additional attributes: path - path to file; wwwPath - path to file for browser; wwwThumbPath - path to thumbnail file for browser
				onDblSelect: function(file) {alert(file.name);}, // Function to execute when file is selected by double click, file - the item of array dir_content.files[] with additional attributes: path - path to file; wwwPath - path to file for browser; wwwThumbPath - path to thumbnail file for browser
				onDeselect: function() {alert('deselect file');}, // Function to execute when selection is lost
				viewType: 'thumbnail', // Default view type
				messages: {}, // New messages string to replace original (for localisation)
				contextMenuItems = [ // Arrays with the users context menu items objects
					{
						text: 'Custom item', // The display item text
						handle: function(){alert('custom item click');}, // Handle of function to extcute when the item click
						cssClass: 'mi_item_icon', // css class of icons, empty or not set to disable
						showOn: 0, // Show on 0 - on files and folders, 1 - on folders, 2 - on files, 3 - on free spaces
						disableOnPaste: 0, // Disable item if can`t paste the file or folder
						disableOnFileOp: 1, // Disable item if can`t some file operation
						disabled: !enable_rename, // Dont show item
						defaultItem: 1 // Show item as default (bold text), dont use in users items
					},
					{....}
				]
			}
		*/
		var
			name,
			i,
			len
		;
		settings = settings || {};
		settings.messages = settings.messages || {};

		id_name = settings.idName || 'imgLibId';
		req_url = settings.reqURL || '/script.php';
		bind_keys = (typeof settings.bindKeys != 'undefined') ? settings.bindKeys : true;
		enable_upload = (typeof settings.enableUpload != 'undefined') ? settings.enableUpload : true;
		upload_path = settings.uploadPath || '/upload/';
		cur_path = settings.startDir || cur_path;
		allowed_ext = settings.allowedExt.sort() || ['jpg', 'gif', 'png'];
		max_upload_size = settings.maxUploadSize || 0;
		max_upload_file_size = settings.maxUploadFileSize || 0;
		save_file_cookie = settings.saveFileCookie || 'file';
		save_inf_cookie = settings.saveInfCookie || 'imglibinf';
		max_file_name_len = settings.maxFileNameLen || 30;
		enable_browse_subdir = (typeof settings.enableBrowseSubdir != 'undefined') ? settings.enableBrowseSubdir : true;
		enable_file_operation = (typeof settings.enableFileOperation != 'undefined') ? settings.enableFileOperation : true;
		enable_create_dir = (typeof settings.enableCreateDir != 'undefined') ? settings.enableCreateDir : true;
		enable_rename = (typeof settings.enableRename != 'undefined') ? settings.enableRename : true;
		enable_delete = (typeof settings.enableDelete != 'undefined') ? settings.enableDelete : true;
		thumbnail_dir = (typeof settings.thumbnailDir != 'undefined') ? settings.thumbnailDir : 'thmb';
		tooltips_delay = settings.tooltipsDelay || 400;
		overlay_opacity = settings.overlayOpacity || 75;
		dbclick_delay = settings.dbclickDelay || 400;
		on_select = settings.onSelect || on_select;
		on_dblselect = settings.onDblSelect || on_dblselect;
		on_deselect = settings.onDeselect || on_deselect;
		view_type = settings.viewType || 'thumbnail';

		// Normalize the upload_path (delete last slash)
		upload_path = (upload_path.lastIndexOf('/') == (upload_path.length - 1)) ? upload_path.substring(0, upload_path.length - 1) : upload_path;

		// Load messages
		for (i in settings.messages) {
			if (typeof settings.messages[i] == 'string') {
				msg[i] = HTMLDecode(settings.messages[i]);
			}
		}

		// Generate the default context elements items
		context_menu_items = [
			{text: msg.openTitle, handle: openFileInList, showOn: 2, defaultItem: 1},
			{text: msg.browseTitle, handle: browseDirectory, cssClass: 'folder', showOn: 1, defaultItem: 1, disabled: !enable_browse_subdir},
			{text: msg.newDirTitle, handle: createDirectory, cssClass: 'newFolder', showOn: 3, defaultItem: 1, disabled: !enable_create_dir},
			{text: msg.copyTitle, handle: function () {copyFileObjToBuf(false);}, cssClass: 'copy', showOn: 0, disableOnPaste: 0, disableOnFileOp: 1, disabled: !enable_file_operation},
			{text: msg.cutTitle, handle: function () {copyFileObjToBuf(true);}, cssClass: 'cut', showOn: 0, disableOnPaste: 0, disableOnFileOp: 1, disabled: !enable_file_operation},
			{text: msg.pastTitle, handle: pasteFileObjFromBuf, cssClass: 'paste', showOn: 0, disableOnPaste: 1, disableOnFileOp: 0, disabled: !enable_file_operation},
			{text: msg.deleteTitle, handle: removeFileObj, cssClass: 'delFolder', showOn: 1, disableOnPaste: 0, disableOnFileOp: 1, disabled: !enable_delete},
			{text: msg.deleteTitle, handle: removeFileObj, cssClass: 'del', showOn: 2, disableOnPaste: 0, disableOnFileOp: 1, disabled: !enable_delete},
			{text: msg.renameTitle, handle: renameFileObj, cssClass: 'rename', showOn: 0, disableOnPaste: 0, disableOnFileOp: 1, disabled: !enable_rename}
		];

		// Append the users items to context menu
		if ((typeof settings.contextMenuItems == 'object') && (settings.contextMenuItems.length > 0)) {
			// Add separator to begin users items
			context_menu_items[context_menu_items.length] = {};
			// Add items
			for (i = 0, len = settings.contextMenuItems.length; i < len; i++) {
				context_menu_items[context_menu_items.length] = settings.contextMenuItems[i];
			}
		}
	}
	/*
		Process add event and build the out on AJAX
	*/
	function onLoad() {
		//Restore the prev state
		restoreState();

		// Create the HTML core
		if (!$(id_name)) {
			img_lib_element = document.createElement('div');
			img_lib_element.className = 'imgLib';
			document.getElementsByTagName('body')[0].appendChild(img_lib_element);
		} else {
			img_lib_element = $(id_name);
			img_lib_element.className = 'imgLib';
		}
		img_lib_element.innerHTML = '';
/*
		var mDivHeight = getElPos(img_lib_element);
		mDivHeight = mDivHeight.height;
*/
		tulbar_element = document.createElement('div');
		tulbar_element.className = 'tulbar';
		file_list_element = document.createElement('div');
		file_list_element.className = 'fileList';
		img_lib_element.appendChild(tulbar_element);
		img_lib_element.appendChild(file_list_element);

		getDirContent(cur_path);

		if (img_lib_element) {
			addEvent(img_lib_element, 'click', hideContextMenu);

			// Context menu
			addEvent(file_list_element, 'click', function (evt) {
				evt = fixEvent(evt);
				if (evt.shiftKey) {
					showContextMenu(evt);
					cancelEvent(evt);
				} else {
					selectFile(evt);
				}
			});

			addEvent(file_list_element, 'contextmenu', showContextMenu);
			// Context menu in opera if enable capture mouse right click
			if (isOpera) {
				addEvent(file_list_element, 'mousedown', function (evt) {
					if (evt.which == 3) {
						showContextMenu(evt);
					}
				});
			}
			addEvent(window, 'resize', fixFileListHeight);

			// Bind keyboard shorcuts
			if (bind_keys) {
				addEvent(document, 'keydown', function (evt) {
					evt = fixEvent(evt);
					if (evt.keyCode == 46) {
						// Delete element on 'Del'
						removeFileObj();
					} else if ( (evt.keyCode == 13) && (evt.shiftKey) ) {
							// Open file in new window on 'Shift+Enter'
							openFileInList();
					} else if (evt.keyCode == 113) {
						// Rename file on 'F2'
						renameFileObj();
					} else if (evt.keyCode == 118) {
						// Create directory on 'F7'
						createDirectory();
					} else if (evt.keyCode == 45) {
						// Show upload form on 'Insert'
						showUploadForm();
					} else if (evt.ctrlKey) {
						if (evt.keyCode == 88) {
							// Cut file on 'Ctrl+X'
							copyFileObjToBuf(true);
						} else if (evt.keyCode == 67) {
							// Copy file on 'Ctrl+C'
							copyFileObjToBuf();
						} else if (evt.keyCode == 86) {
							// Paste file on 'Ctrl+V'
							pasteFileObjFromBuf();
						}
					}
				});
			}
/*
			addEvent(document, 'click', hideContextMenu);
			
*/
		}
		ready = true;
	}
	/*
		Initialise context menu - build a root element
	*/
	function initContextMenu() {
		context_menu_element = document.createElement('div');
		context_menu_element.className = 'contextMenu';
		img_lib_element.appendChild(context_menu_element);
	}
	/*
		Show context menu on event "evt"
	*/
	function showContextMenu(evt) {
		// If context menu element not exist - create it
		if (!context_menu_element) {
			initContextMenu();
		}

		hideTooltips();
		// Clear context menu
		context_menu_element.innerHTML = '';

		// Determine the type of element on context
		context_element = getElProp(evt);
		if (context_element.type == 2) {
			highlightFileItem(context_element.index);
		}
		bildContextMenu();
		// Show context menu with time out
		//setTimeout(function() {
		context_menu_element.style.display = 'block';
		fixMouseEventElementPosition(evt, context_menu_element, 0);
		//	}, 20);
		context_menu_element.style.display = 'block';
		cancelEvent(evt);
	}
	/*
		Hide context menu
	*/
	function hideContextMenu() {
		if (context_menu_element) {
			context_menu_element.style.display = 'none';
		}
	}
	/*
		Bild the out of context menu
	*/
	function bildContextMenu() {
		var
			cantPaste = (!!elToPaste && (context_element.type != 2)) ? false : true,
			cantFileOp = (context_element.type == 1 || context_element.type == 2) ? false : true,
			listElement = document.createElement('ul'), // Context menu list element
			i,
			len,
			show_first_separator
		;

		// Add elements to context menu
		for (i = 0, len = context_menu_items.length, show_first_separator = false; i < len; i++) {
			// Check the target type
			if ((context_menu_items[i].showOn == context_element.type) || (context_menu_items[i].showOn == 0) || (!context_menu_items[i].showOn) || (cantFileOp && context_menu_items[i].showOn == 3)) {
				// Check to disabled element
				if (context_menu_items[i].disabled) {
					continue;
				}
				listElement.appendChild(addToContextMenu(context_menu_items[i].text, context_menu_items[i].handle, context_menu_items[i].cssClass, (((context_menu_items[i].disableOnPaste == 1) && (cantPaste)) || ((context_menu_items[i].disableOnFileOp == 1) && (cantFileOp))), context_menu_items[i].defaultItem));
				if (context_menu_items[i].defaultItem && !show_first_separator) {
					listElement.appendChild(addToContextMenu());
					show_first_separator = true;
				}
			}
		}

		context_menu_element.appendChild(listElement);
	}
	/*
		Return element to add to context menu
			@itemText - displayed text
			@action - handle to the JS function executed on click
			@className - css class name on displayed item
			@disabled - disable this item
			@def - this item is default action
	*/
	function addToContextMenu(item_text, action, css_class, disabled, def) {
		var
			listItemElement = document.createElement('li'), // Folder tree ul item
			iconElement = document.createElement('span'), // Folder icon element
			nameElement = document.createElement('span') // Folder name span element
		;
		// If text not specified then insert separator
		if (!item_text) {
			listItemElement.className = 'separator';
			return listItemElement;
		}
		action = action || function () {};
		css_class = css_class || '';

		iconElement.className = 'icon ' + css_class;
		// Add events
		/*
		listItemElement.setAttribute('onmouseover', 'this.className = \'hover\'');
		listItemElement.setAttribute('onmouseout', 'this.className = \'\'');
		*/
		// Internet Explorer dont understant the ":hover" pseudo class
		if (isIE) {
			addEvent(listItemElement, 'mouseover', function () {listItemElement.className = 'hover';});
			addEvent(listItemElement, 'mouseout', function () {listItemElement.className = '';});
		}
		if (!disabled) {
			addEvent(listItemElement, 'click', function (evt) {hideContextMenu(); action(evt);});
		}

		nameElement.innerHTML = item_text;
		nameElement.className = 'name' + ((disabled) ? ' disabled' : '');
		if (def) {
			nameElement.style.fontWeight = 'bold';
		}

		// Build the menu item
		listItemElement.appendChild(iconElement);
		listItemElement.appendChild(nameElement);

		// Return the created element
		return listItemElement;
	}
	/*
		Get the position of event 'evt'
	*/
	function getEventPos(evt) {
		var x = 0, y = 0;
		x = isIE ? evt.clientX : (evt.pageX - document.body.scrollLeft);
		y = isIE ? evt.clientY : (evt.pageY - document.body.scrollTop);
		return {x: x, y: y};
	}
	/*
		Get the property of element on context event 'evt' and return the object
	*/
	function getElProp(evt) {
		evt = fixEvent(evt);
		var
			targetEl = evt.target,
			target_type,
			target_index,
			target_name
		;
		// For Safari
		if (targetEl.nodeType == 3) {
			targetEl = targetEl.parentNode;
		}

		// Find item index
		while (targetEl && targetEl.parentNode && (typeof targetEl.index == 'undefined')) {
			targetEl = targetEl.parentNode;
		}
		if (targetEl.itemType == 1) {
			target_index = targetEl.index;
			target_type = 1;
			target_name = (dir_content.dirs[targetEl.index]) ? dir_content.dirs[targetEl.index].name : '';
		} else if (targetEl.itemType == 2) {
			target_index = targetEl.index;
			target_type = 2;
			target_name = dir_content.files[targetEl.index].name;
		} else {
			target_type = 'unknow';
		}
		return {type: target_type, index: target_index, name: target_name, time: (new Date()).getTime()};
	}
	/*
		Rename file object
	*/
	function renameFileObj() {
		// Check if rename is posible
		if (dir_content.inf.is_writable != 1) return alert(msg.writeProtectTitle);

		var
			oldName = HTMLDecode(context_element.name),
			fileExt,
			newName
		;
		if (oldName && oldName != '') {
			if (context_element.type == 2) {
				// Remove file extension
				oldName = oldName.replace(/\.\w*$/g, '');
				// Get the file extension
				fileExt = context_element.name.replace(eval('/^' + oldName + '\./g'), '');
			}
			newName = prompt(msg.enterNewNameWoExtPromt.replace(/%1/g, HTMLDecode(context_element.name)), oldName);
			if (!newName) {
				return;
			}
			if (newName != oldName) {
				// Get the new file name with extension
				if (context_element.type == 2) {
					newName = newName + '.' + fileExt;
				}
				showLoading();
				sendXMLHttpReq(req_url, {
					mode: 'POST',
					parameters: 'cmd=rename&src=' + encodeURIComponent(cur_path + context_element.name) + '&new_name=' + encodeURIComponent(newName),
					onsuccess: function (req) {
						hideLoading();
						if (req.responseText != '') {
							alert(msg.operationFailedStr.replace(/%1/g, req.responseText));
						}
						getDirContent(cur_path);
					}
				});
			} else if (newName && newName == oldName) {
				alert(msg.noChange);
			}
		}
	}
	/*
		Remove file object
	*/
	function removeFileObj() {
		// Check if remove is posible
		if (dir_content.inf.is_writable != 1) return alert(msg.writeProtectTitle);

	var
			objName = HTMLDecode(context_element.name),
			confDlgText
		;
		if (objName && objName != '') {
			// Check if folder is not empty
			confDlgText = ((context_element.type == 1) && (dir_content.dirs[context_element.index].empty == '0')) ? msg.delNonEmptyFolderPromt : msg.delFileObjPromt;
			confDlgText = confDlgText.replace(/%1/g, objName);
			if (confirm(confDlgText)) {
				showLoading();
				sendXMLHttpReq(req_url, {
					mode: 'POST',
					parameters: 'cmd=rm&dst=' + encodeURIComponent(cur_path + objName) + '&rec=1',
					onsuccess: function (req) {
						hideLoading();
						if (req.responseText != '') {
							alert(msg.operationFailedStr.replace(/%1/g, req.responseText));
						}
						getDirContent(cur_path);
					}
				});
			}
		}
	}
	/*
		Create a new directory
	*/
	function createDirectory() {
		// Check if create dir is posible
		if (dir_content.inf.is_writable != 1) return alert(msg.writeProtectTitle);

		var newDir = prompt(msg.enterNewDirNameStr, msg.defaultNewDirNameStr);
		if (newDir) {
			showLoading();
			sendXMLHttpReq(req_url, {
				mode: 'POST',
				parameters: 'cmd=mkdir&dst=' + encodeURIComponent(cur_path) + '&name=' + encodeURIComponent(newDir),
				onsuccess: function (req) {
					hideLoading();
					if (req.responseText != '') {
						alert(msg.operationFailedStr.replace(/%1/g, req.responseText));
					}
					getDirContent(cur_path);
				}
			});
		}
	}
	/*
		Do sompfing in slected filelist element
		@evt - event object
	*/
	function selectFile(evt) {
		var last_file = {};
		if (context_element && (context_element.type == 2)) {
			last_file = context_element;
		}
		context_element = getElProp(evt);
		if (context_element.type == 1) {
			// If select directory then open the directory
			// if index >= 0 then select subfolde, if index < 0 - then select up folder
			if (context_element.index >= 0) {
				getDirContent(cur_path + context_element.name + '/');
			} else {
				var
					path = '',
					i
				;
				for (i = dir_content.path.length + context_element.index; i >= 0; i--) {
					path = dir_content.path[i] + '/' + path;
				}
				getDirContent('/' + path);
			}
		} else if (context_element.type == 2) {
			// Іf select the file then highlight and execute the external functions with the file properties
			highlightFileItem(context_element.index);
			// If set the external function - then execute it
			if (on_select || on_dblselect) {
				var file = dir_content.files[context_element.index];
				// Generate additional field
				file.path = cur_path + file.name;
				file.wwwPath = getURIEncPath(HTMLDecode(upload_path + cur_path + file.name));
				// If thumbnail exist, set the www path to him
				if (file.thumb && file.thumb !== '') {
					file.wwwThumbPath = getURIEncPath(HTMLDecode(upload_path + file.thumb));
				}
				if (on_select) {
					on_select(file);
				}
				// If double click on file then chose them by on_dblselect() function
				if ( on_dblselect &&(last_file.index == context_element.index) && ((context_element.time - last_file.time) < dbclick_delay) ) {
					on_dblselect(file);
				}
			}
		} else {
			// No selections
			// Clean the highlight
			highlightFileItem();
			// Hide the tooltips
			hideTooltips();
			// If set the external function - then execute it
			if (on_deselect) {
				on_deselect();
			}
		}
	}
	/*
		Browse slected directory
	*/
	function browseDirectory() {
		if (context_element.type == 1) {
			getDirContent(cur_path + context_element.name + '/');
		}
	}
	/*
		Open slected file
	*/
	function openFileInList() {
		if (context_element.type == 2) {
			window.open(getURIEncPath(HTMLDecode(upload_path + cur_path + context_element.name)), '_blank');
		}
	}
	/*
		Copy information about file or folder object to bufer elToPaste
		@remOriginal - (boolean) cut or copy object
	*/
	function copyFileObjToBuf(remOriginal) {
		clearFileObjBuf();
		var
			fileObjPath = cur_path,
			fileObjCmd = (remOriginal && (dir_content.inf.is_writable == 1)) ? 'move' : 'copy'
		;

		fileObjPath = (fileObjPath) ? fileObjPath : '/';
		elToPaste = {type: context_element.type, name: fileObjPath + context_element.name, cmd: fileObjCmd};
		saveState();
	}
	/*
		Clear information about file object in bufer
	*/
	function clearFileObjBuf() {
		elToPaste = null;
		setCookie(save_file_cookie, '', -1000);
	}
	/*
		Paste file or folder from bufer elToPaste
	*/
	function pasteFileObjFromBuf() {
		if (!!elToPaste) {
			// Check if paste is posible
			if (dir_content.inf.is_writable != 1) return alert(msg.writeProtectTitle);

			var
				objDst = cur_path,
				objSrc = elToPaste.name
			;

			objDst = objDst + (context_element.name || '');
			if (objSrc != '') {
				showLoading();
				sendXMLHttpReq(req_url, {
					mode: 'POST',
					parameters: 'cmd=' + elToPaste.cmd + '&src=' + encodeURIComponent(objSrc) + '&dst=' + encodeURIComponent(objDst),
					onsuccess: function (req) {
						hideLoading();
						if (req.responseText != '') {
							alert(msg.operationFailedStr.replace(/%1/g, req.responseText));
						}
						clearFileObjBuf();
						getDirContent(cur_path);
					}
				});
			}
		}
	}
	/*
		Save information about file object and curent dir to cookies
	*/
	function saveState() {
		var cookieData;
		if (!!elToPaste) {
			cookieData = [escape(elToPaste.type), escape(elToPaste.name), escape(elToPaste.cmd)];
			cookieData = escape(cookieData.join(','));
			setCookie(save_file_cookie, cookieData);
		}
		cookieData = [escape(cur_path), escape(view_type)];
		setCookie(save_inf_cookie, escape(cookieData));
	}
	/*
		Restore information about file object and curent dir from cookies
	*/
	function restoreState() {
		var cookieData;
		if (!elToPaste) {
			cookieData = getCookie(save_file_cookie);
			if (cookieData != null) {
				cookieData = cookieData.split(',');
				elToPaste = {type: unescape(unescape(cookieData[0])), name: unescape(unescape(cookieData[1])), cmd: unescape(unescape(cookieData[2]))};
			}
		}
		cookieData = getCookie(save_inf_cookie);
		if (cookieData != null) {
			cookieData = cookieData.split(',');
			if ((cookieData[0] != '') || (cookieData[0] != '/')) {
				cur_path = unescape(cookieData[0]);
			}
			view_type = cookieData[1];
		}
	}
	/*
		Show the folder tree
	*/
	function showFolderTree(evt) {
		hideContextMenu();
		hideTooltips();
		cancelEvent(evt);
		var treeElement = tulbar_element.getElementsByTagName('div')[0].getElementsByTagName('ul')[0];
		if (treeElement) {
			treeElement.style.display = (treeElement.style.display != 'block') ? 'block' : 'none';
		}
	}
	/*
		Hide the folder tree layer
	*/
	function hideFolderTree() {
		var treeElement = tulbar_element.getElementsByTagName('div')[0];
		if (treeElement) {
			treeElement.getElementsByTagName('ul')[0].style.display = 'none';
		}
	}
	/*
		Get content of dir
		@path - relative path to directory
	*/
	function getDirContent(path) {
		showLoading();
		context_element = {};
		path = HTMLDecode(path || cur_path);
		sendXMLHttpReq(req_url, {
			mode: 'POST',
			parameters: 'cmd=list&src=' + encodeURIComponent(path),
			onsuccess: function (req) {
				update(req.responseXML);
				hideLoading();
			}
		});
	}
	/*
		Reload curent dir
	*/
	function reloadDirContent() {
		getDirContent(cur_path);
	}
	/*
		Upate the dir content from the XML document
		@XMLDoc - document in XML format
	*/
	function update(XMLDoc) {
		hideFolderTree();

		parseXML(XMLDoc);
		updateHTML();
	}
	/*
		Parse the XML document and create object with curent dir content
		@XMLText - document in XML format
	*/
	function parseXML(XMLDoc) {
		// Clear the exist dir content
		/*
		dir_content = {
			path: ['dir', 'subDir', 'subSubDir'], // Path to current dir
			inf: {
					is_writable: 1 // The current dir is writable (1) or not (0)
				},
			dirs:[
					{
						name: 'fauna', // Dir name
						empty: 0, // Directory is empty (1) or not (0)
						readable: 1, // Directoy is readable
						date: 1253824760 // Dir date as unix timestamp
					},
					{name: 'flora', empty: 1, readable: 1},
					{name: 'biiiiiig foldeeeeeeeer nameeeeeeeeeeeeee ', empty: 1, readable: 1}
				]
			},
			files:[
					{
						name: 'filename', // File name
						filesize: 100, // File size in bytes
						date: 1253824760, // File date as unix timestamp
						img_size: '1024x768', // If file is image contains the image size
						thumb: '/thmb/filename.jpg' // If file is image contains the path to image thumbnail
					}
			]
		}
		*/

		var
			rootNodeName,
			xmlRoot,
			path,
			inf,
			dirs,
			files,
			i
		;
		// Create the template
		dir_content = {
			path: [],
			inf: {},
			dirs: [],
			files: []
		};

		// Parse the XML Document

		// catching potential errors with IE and Opera
		if (!XMLDoc || !XMLDoc.documentElement) {
			throw 'Invalid XML structure:\n' + XMLDoc.responseText;
		}
		// catching potential errors with Firefox
		rootNodeName = XMLDoc.documentElement.nodeName;
		if (rootNodeName == 'parsererror') {
			throw 'Invalid XML structure';
		}
		// obtain the XML's document element
		xmlRoot = XMLDoc.documentElement;

		// Get the XML elements
		path = xmlRoot.getElementsByTagName('path');
		inf = xmlRoot.getElementsByTagName('inf');
		dirs = xmlRoot.getElementsByTagName('dirs');
		files = xmlRoot.getElementsByTagName('files');

		/*------------------------------- Path ---------------------------------------------------*/
		if (path && path.length > 0) {
			path = path.item(0).getElementsByTagName('dir');
			for (i = 0; i < path.length; i++) {
				dir_content.path[dir_content.path.length] = path.item(i).getAttribute('name');
			}
		}

		/*------------------------------- Inf ---------------------------------------------------*/
		if (inf && inf.length > 0) {
			dir_content.inf = getAttributes(inf.item(0));
		}

		/*--------------------------------- Dirs -------------------------------------------------*/
		if (dirs && dirs.length > 0) {
			dirs = dirs.item(0).getElementsByTagName('dir');
			for (i = 0; i < dirs.length; i++) {
				dir_content.dirs[dir_content.dirs.length] = getAttributes(dirs.item(i));
			}
		}

		/*------------------------- Files --------------------------------------------------------*/
		if (files && files.length > 0) {
			files = files.item(0).getElementsByTagName('file');
			for (i = 0; i < files.length; i++) {
				dir_content.files[dir_content.files.length] = getAttributes(files.item(i));
			}
		}
	}
	/*
		Upate the HTML from the curent dir object
	*/
	function updateHTML() {
		var
			path = dir_content.path,
			treeMargin = 1,
			folder_tree_element = document.createElement('div'),
			dirs = dir_content.dirs,
			buttonsEl = document.createElement('div'), // Clean the tulbar buttons
			folderTreeLabel,
			folderTreeLabelIcon,
			folderTreeLabelText,
			folder_tree,
			updirEl,
			ch_view_el,
			listElement,
			sort_el,
			search_el,
			inputElement,
			i,
			len
		;

		// Initialise the varitables
		cur_path = '/';

		// Hide element to speed up
		tulbar_element.style.display = 'none';
		file_list_element.style.display = 'none';

		// Clear the tulbar element
		tulbar_element.innerHTML = '';

		folder_tree_element.className = 'folderTree';

		// Label to indicate the folder tree
		folderTreeLabel = document.createElement('div');
		folderTreeLabel.className = 'label curent';

		// Icon on folder tree label
		folderTreeLabelIcon = document.createElement('span');
		folderTreeLabelIcon.className = 'icon openFolder';

		folderTreeLabelText = document.createElement('span');

		// Add event to show and hide folder tree
		addEvent(folderTreeLabel, 'click', showFolderTree);
		addEvent(folder_tree_element, 'mouseover', function () {clearTimeout(timer); hideTooltips();});
		addEvent(folder_tree_element, 'mouseout', function () {timer = setTimeout(function () {hideFolderTree();}, tooltips_delay);});


		// If path.length = 0 -> the root dir, else - some subdir
		if (path.length == 0) {
			folderTreeLabelText.innerHTML = msg.rootPathName;
			folderTreeLabel.setAttribute('title', '/');
		} else {
			folderTreeLabelText.innerHTML = truncateName(path[path.length - 1]);
			folderTreeLabel.setAttribute('title', HTMLDecode(path[path.length - 1]));
		}
		folderTreeLabelText.innerHTML += '&nbsp;&gt;&gt;&gt;&gt;';
		// Build the folder tree label
		folderTreeLabel.appendChild(folderTreeLabelIcon);
		folderTreeLabel.appendChild(folderTreeLabelText);

		// Generate the folder tree
		folder_tree = document.createElement('ul'); // Build Folder tree elements
		folder_tree.className = 'tree';

		addEvent(folder_tree, 'click', function (evt) {selectFile(evt);});


		if (path.length > 0) {
			for (i = 0, len = path.length; i < len; i++) {
				// Generate the up dir button
				if (i == len - 1) {
					updirEl = createTulbarButton(msg.moveToUpDirText, (function (dir) {return function () {return getDirContent(dir);};})(cur_path), 'upFolder');
				}
				cur_path += path[i] + '/';
				// Create the root element
				if (i == 0) {
					folder_tree.appendChild(createFolderTreeElement(msg.rootPathName, '/', -(len + 1), 'openFolder', 0));
				}
				folder_tree.appendChild(createFolderTreeElement(truncateName(path[i]), path[i], i - len, 'openFolder' + ((i == len - 1) ? ' curent':''), treeMargin++, ((i == len - 1) ? ' curent':'')));
			}
		}
		cur_path = HTMLDecode(cur_path);

		// Add current dirs to folder tree
		for (i = 0; i < dirs.length; i++) {
			// Hide the thumbnail directory
			if (dirs[i].name != thumbnail_dir) {
				folder_tree.appendChild(createFolderTreeElement(truncateName(dirs[i].name), dirs[i].name, i, ((dirs[i].empty == 0) ? 'full':''), treeMargin));
			}
		}
		// Build the folder tree
		folder_tree_element.appendChild(folderTreeLabel);
		folder_tree_element.appendChild(folder_tree);
		/*
			End folder tree
		*/

		// Button controls

		buttonsEl.className = 'controls';
		// Append if exist the updir element who createw earlier
		if (updirEl && typeof updirEl == 'object') {
			buttonsEl.appendChild(updirEl);
		}
		// Reload Folder
		buttonsEl.appendChild(createTulbarButton(msg.reloadDirTitle, reloadDirContent, 'reloadFolder'));

		// Create element to change view of file list
		ch_view_el = document.createElement('div');
		listElement = document.createElement('ul');
		ch_view_el.className = 'contextMenu';
		listElement.appendChild(addToContextMenu(msg.thumbnailTitle, function () {view_type = 'thumbnail'; updateHTML();}, 'viewThumbnail', 0, (view_type == 'thumbnail')));
		listElement.appendChild(addToContextMenu(msg.listTitle, function () {view_type = 'list'; updateHTML();}, 'viewList', 0, (view_type == 'list')));
		listElement.appendChild(addToContextMenu(msg.tableTitle, function () {view_type = 'table'; updateHTML();}, 'viewTable', 0, (view_type == 'table')));
		ch_view_el.appendChild(listElement);
		buttonsEl.appendChild(createTulbarButton(msg.viewTitle, false, 'view', ch_view_el));

		// Sort button
		sort_el = document.createElement('div');
		listElement = document.createElement('ul');
		listElement.appendChild(addToContextMenu(msg.sortByNameTitle, function () {sort_type = 1; updateHTML();}, 'sort', 0, (sort_type == 1)));
		listElement.appendChild(addToContextMenu(msg.sortBySizeTitle, function () {sort_type = 2; updateHTML();}, 'sort', 0, (sort_type == 2)));
		listElement.appendChild(addToContextMenu(msg.sortByDateTitle, function () {sort_type = 3; updateHTML();}, 'sort', 0, (sort_type == 3)));
		sort_el.className = 'contextMenu';
		sort_el.appendChild(listElement);
		buttonsEl.appendChild(createTulbarButton(msg.sortTitle, false, 'sort', sort_el));

		// Search button
		// Create search element
		search_el = document.createElement('div');
		inputElement = document.createElement('input');

		inputElement.setAttribute('type', 'text');
		addEvent(inputElement, 'keyup', function () {searchFile(inputElement.value);});
		addEvent(inputElement, 'keypress', function () {searchFile(inputElement.value);});
		search_el.className = 'searchBox';
		search_el.innerHTML = msg.enterSearchTitle;
		search_el.appendChild(inputElement);
		buttonsEl.appendChild(createTulbarButton(msg.searchTitle, false, 'search', search_el));

		// Create Folder
		if (enable_create_dir) {
			buttonsEl.appendChild(createTulbarButton(msg.newDirTitle, createDirectory, 'newFolder'));
		}

		// Upload button
		if (enable_upload) {
			buttonsEl.appendChild(createTulbarButton(msg.uploadTitle, showUploadForm, 'upload'));
		}

		// Create the filelist
		createFileList();

		// Final build the tulbar
		tulbar_element.appendChild(folder_tree_element); // Add folder tree
		tulbar_element.appendChild(buttonsEl); // Add tulbar buttons

		// Visualise the changes
		tulbar_element.style.display = '';
		file_list_element.style.display = '';

		// Adjust the height of file list element
		fixFileListHeight();

		saveState();
	}
	/*
		Create the file or folder list from curent dir content or acepted dir_content
		@usr_dir_content - users dir_content
	*/
	function createFileList(usr_dir_content) {
		var
			dirs = dir_content.dirs,
			files = dir_content.files,
			elOrName,
			root_element = (view_type != 'table') ? document.createElement('ul') : document.createElement('table'), // Root element for filelist items
			i
		;

		// Sort items
		// Normalize the sort type
		sort_type = (sort_type > 3 || sort_type <1) ? 1 : sort_type;

		// Dirs sort only by date or name
		dirs = dirs.sort(function (a, b) {
			if (sort_type == 3) {
				return a.date - b.date;
			} else {
				// Sort by file name
				return ((a.name > b.name) ? 1 : ((a.name < b.name) ? -1 : 0));
			}
		});

		files = files.sort(function (a, b) {
			if (sort_type == 2) {
				return a.filesize - b.filesize;
			} else if (sort_type == 3) {
				return a.date - b.date;
			} else {
				// Sort by file name
				return ((a.name > b.name) ? 1 : ((a.name < b.name) ? -1 : 0));
			}
		});

		if (typeof usr_dir_content == 'object') {
			dirs = usr_dir_content.dirs;
			files = usr_dir_content.files;
		}
		file_list_element.innerHTML = '';
		root_element.className = view_type;

		// If set the external function - then execute it
		if (on_deselect) {
			on_deselect();
		}
		hideTooltips();

		// Cheate the table header
		if (view_type == 'table') {
			var
				itemElement = document.createElement('tr'),
				itemIconElement = document.createElement('th'),
				itemNameElement = document.createElement('th'),
				itemSizeElement = document.createElement('th'),
				itemFileDateElement = document.createElement('th')
			;
			itemElement.className = 'title';
			itemIconElement.className = 'icons';
			itemNameElement.innerHTML = msg.fileNameTitle;
			itemSizeElement.innerHTML = msg.sizeTitle;
			itemFileDateElement.innerHTML = msg.dateTitle;

			// Add sort functions
			addEvent(itemNameElement, 'click', function () {sort_type = 1; createFileList();});
			addEvent(itemSizeElement, 'click', function () {sort_type = 2; createFileList();});
			addEvent(itemFileDateElement, 'click', function () {sort_type = 3; createFileList();});
			// Add visual efect to sorted colum
			if (sort_type == 3) {
				itemFileDateElement.className = 'sort';
			} else if (sort_type == 2) {
				itemSizeElement.className = 'sort';
			} else {
				itemNameElement.className = 'sort';
			}
			var cols_el = document.createElement('colgroup');
			for (i = 0; i < 4; i++) {
				var col_el = document.createElement('col');
				if (i == sort_type) {
					col_el.className = 'sort';
				}
				cols_el.appendChild(col_el);
			}
			root_element.appendChild(cols_el);
			// Special for M$, IE dont display table without tbody element
			root_element.appendChild(document.createElement('tbody'));

			itemElement.appendChild(itemIconElement);
			itemElement.appendChild(itemNameElement);
			itemElement.appendChild(itemSizeElement);
			itemElement.appendChild(itemFileDateElement);


			root_element.lastChild.appendChild(itemElement);
		}

		// Create the filelist
		/*
			Folders
		*/
		for (i = 0; i < dirs.length; i++) {
			// Skip if invalid item (example: on search not valid items is skiped)
			if (!dirs[i]) {
				continue;
			}
			elOrName = dirs[i].name;
			// Hide the thumbnail directory
			if (elOrName != thumbnail_dir) {
				elName = truncateName(elOrName);
				var itemElement = (view_type == 'table') ? document.createElement('tr') : document.createElement('li');
				var iconElement = document.createElement('span');
				iconElement.className = 'icon folder' + ((dirs[i].empty == 0) ? ' full' : '');

				with(itemElement) {
					setAttribute('title', HTMLDecode(elOrName));
					if (view_type == 'thumbnail') {//margin: 0 auto;
						innerHTML = '<div class="image"></div><div class="name">' + elName + '</div>';
						itemElement.firstChild.appendChild(iconElement);
					} else if (view_type == 'table') {
					var
						itemIconElement = document.createElement('td'),
						itemNameElement = document.createElement('td'),
						itemSizeElement = document.createElement('td'),
						itemFileDateElement = document.createElement('td')
					;
					itemIconElement.appendChild(iconElement);
					itemNameElement.innerHTML = elName;
/*					Size for folders not calculated
					itemSizeElement.innerHTML = '';
*/
					itemFileDateElement.innerHTML = getDateF(dirs[i].date * 1000, 1);

					itemElement.appendChild(itemIconElement);
					itemElement.appendChild(itemNameElement);
					itemElement.appendChild(itemSizeElement);
					itemElement.appendChild(itemFileDateElement);
					} else {
						innerHTML += elName;
						itemElement.insertBefore(iconElement, itemElement.firstChild);
					}
				}
				// Append attribute to identificate the items
				itemElement.index = i;
				itemElement.itemType = 1;
				// Append to the file list
				if (view_type == 'table') {
					root_element.lastChild.appendChild(itemElement);
				} else {
					root_element.appendChild(itemElement);
				}
			}
		}

		/*
			Files
		*/
		if (files.length > 0) {
			for (i = 0; i < files.length; i++) {
				// Skip if invalid item (example: on search not valid items is skiped)
				if (!files[i]) continue;
				elOrName = files[i].name;
				var ext = elOrName.substring(elOrName.lastIndexOf('.') + 1).toLowerCase();
				if (elOrName.length > max_file_name_len) {
					elName = elOrName.substring(0, max_file_name_len - (ext.length + 3)) + '...' + ext;
				} else {
					elName = elOrName;
				}
				var itemElement = (view_type == 'table') ? document.createElement('tr') : document.createElement('li');
				var iconElement = document.createElement('span');
				iconElement.className = 'icon files ' + ext;

				// Add events
				addEvent(itemElement, 'mouseover', function (evt) {buildTooltips(evt); timer = setTimeout(showTooltips, tooltips_delay);});
				//addEvent(itemElement, 'mousemove', function (evt) {timer = setTimeout(function () {showTooltips(evt)}, tooltips_delay);});
				addEvent(itemElement, 'mouseout', function () {clearTimeout(timer); hideTooltips();});

				addEvent(iconElement, 'mouseover', function (evt) {buildTooltips(evt); timer = setTimeout(showTooltips, tooltips_delay);});
				//fixEvent(evt);
				//addEvent(iconElement, 'mousemove', function (evt) {timer = setTimeout(function () {showTooltips(evt)}, tooltips_delay);});
				addEvent(iconElement, 'mouseout', function () {clearTimeout(timer); hideTooltips();});

/*				Title no need - tooltips is show
				itemElement.setAttribute('title', HTMLDecode(elOrName));
*/
				if (view_type == 'thumbnail') {
					itemElement.innerHTML += '<div class="image">' + ((files[i].thumb && files[i].thumb != '') ? '<img class="loading" src="' + getURIEncPath(HTMLDecode(upload_path + files[i].thumb)) + '" alt="" />' : '') + '</div><div class="name">' + elName + '</div>';
					if (!files[i].thumb || files[i].thumb == '') {
						// No thumbnail -> show icon
						itemElement.firstChild.appendChild(iconElement);
					}
				} else if (view_type == 'table') {
					var
						itemIconElement = document.createElement('td'),
						itemNameElement = document.createElement('td'),
						itemSizeElement = document.createElement('td'),
						itemFileDateElement = document.createElement('td')
					;
					itemIconElement.appendChild(iconElement);
					itemNameElement.innerHTML = elName;
					itemSizeElement.innerHTML = getFloatSize(files[i].filesize) + ((files[i].img_size) ? (' (' + files[i].img_size + ')') : '');
					itemFileDateElement.innerHTML = getDateF(files[i].date * 1000, 1);

					itemElement.appendChild(itemIconElement);
					itemElement.appendChild(itemNameElement);
					itemElement.appendChild(itemSizeElement);
					itemElement.appendChild(itemFileDateElement);
				} else {
					itemElement.innerHTML += elName;
					itemElement.insertBefore(iconElement, itemElement.firstChild);
				}

				// Append attribute to identificate the items
				itemElement.index = i;
				itemElement.itemType = 2;

				// Append to the file list
				if (view_type == 'table') {
					root_element.lastChild.appendChild(itemElement);
				} else {
					root_element.appendChild(itemElement);
				}
			}
		}
		file_list_element.appendChild(root_element);
	}
	/*
		Create and return the tulbar button element
		@title - a title property
		@onclick - onclick handle
		@css_class - css class for button
		@add_element - additional element to insert alfer the icon
	*/
	function createTulbarButton(title, onclick, css_class, add_element) {
		var
			button = document.createElement('a'), // Tulbar A element
			buttonSpanEl = document.createElement('span') // Tulbar a span element
		;
		button.href = '#';
		if (typeof onclick == 'function') {
			addEvent(button, 'click', function (evt) {onclick(evt); cancelEvent(evt);});
		} else if (onclick == false) {
			addEvent(button, 'click', cancelEvent);
		}
		button.setAttribute('title', title);
		// Add event to animate the item
		addEvent(button, 'mouseover', function () {button.className = 'hover';});
		addEvent(button, 'mouseout', function () {button.className = '';});

		// Append additional element
		if (add_element) {
			button.appendChild(add_element);
		}

		buttonSpanEl.className = 'icon ' + css_class;
		button.appendChild(buttonSpanEl);

		return button;
	}
	/*
		Create and return the folder tree element
		@el_name - display name
		@el_title - a title property
		@index - a array index in dir_content.dirs array
		@css_class - css class for button
		@margin - style margin-left value in em
		@li_css_class - css class name for li element
	*/
	function createFolderTreeElement(el_name, el_title, index, css_class, margin, li_css_class) {
		var
			listItemElement = document.createElement('li'), // Folder tree ul item
			iconElement = document.createElement('span'), // Folder icon element
			nameElement = document.createElement('span') // Folder name span element
		;
		listItemElement.setAttribute('title', HTMLDecode(el_title));
		if (li_css_class) listItemElement.className = li_css_class;
		// Internet Explorer dont understant the ":hover" pseudo class
		if (isIE) {
			addEvent(listItemElement, 'mouseover', function () {listItemElement.className = li_css_class + ' hover';});
			addEvent(listItemElement, 'mouseout', function () {listItemElement.className = li_css_class;});
		}
		iconElement.className = className = 'icon folder ' + css_class;
		iconElement.style.marginLeft = margin + 'em';

		nameElement.innerHTML = el_name;
		// Append attribute to identificate the items
		listItemElement.index = index;
		listItemElement.itemType = 1;

		// Build all
		listItemElement.appendChild(iconElement);
		listItemElement.appendChild(nameElement);

		return listItemElement;
	};
	/*
		Truncate the file or folder name to max length
		@name - original name
		@length - owerwrite the default max lenght value
	*/
	function truncateName(name, length) {
		length = length || max_file_name_len;
		if (name.length > length) {
			name = name.substring(0, length - 3) + '...';
		}
		return name;
	}
	/*
		Show the loading indicator
	*/
	function showLoading() {
		// Create loading layer at first usage
		if (!ajax_load_element) {
			ajax_load_element = document.createElement('div');
			ajax_load_element.className = 'ajaxLoad';
			var
				overlay = document.createElement('div'),
				load_indicator = document.createElement('div')
			;
			overlay.className = 'overlay';
			ajax_load_element.appendChild(overlay);

			load_indicator.className = 'loadIcon';
			load_indicator.innerHTML = msg.ajaxLoadingText;
			ajax_load_element.appendChild(load_indicator);

			document.body.appendChild(ajax_load_element);
			setTransparency(overlay, overlay_opacity);
		}
		var el_pos = getElPos(img_lib_element);
		with (ajax_load_element.style) {
			display = '';
			top = el_pos.top + 'px';
			left = el_pos.left + 'px';
			width = el_pos.width + 'px';
			height = el_pos.height + 'px';
		}
	}
	/*
		Hide the loading indicator
	*/
	function hideLoading() {
		if (ajax_load_element) {
			ajax_load_element.style.display = 'none';
		}
	}
	/*
		Build file info box
			evt - mouse event
	*/
	function buildTooltips(evt) {
		if (!tooltips_element) {
			tooltips_element = document.createElement('div');
			tooltips_element.className = 'tooltips';
			//setTransparency(tooltips_element, 95);
			img_lib_element.appendChild(tooltips_element);
		}
		evt = fixEvent(evt);

		var file = evt.target;
		// Find item index
		while (file && file.parentNode && (typeof file.index == 'undefined')) {
			file = file.parentNode;
		}

		file = dir_content.files[file.index];
		if (!file) return;
		tooltips_element.style.display = '';
		tooltips_element.innerHTML = (((file.thumb && file.thumb !== '') && (view_type != 'thumbnail')) ? '<img class="loading" src="' + getURIEncPath(HTMLDecode(upload_path + file.thumb)) + '" alt="" />' : '') + '<span>' + msg.fileNameTitle + ': ' + file.name + '<br />' + msg.fileSizeTitle + ': ' + getFloatSize(file.filesize) + '<br />' + msg.fileDateTitle + ': ' + getDateF(file.date * 1000, 1) + ((file.img_size) ? ('<br />' + msg.imageSizeTitle + ': ' + file.img_size) : '')+ '</span>';
		fixMouseEventElementPosition(evt, tooltips_element);
		hideTooltips();
	}
	/*
		Show file info box
			evt - mouse event
	*/
	function showTooltips(evt) {
		if (tooltips_element) {
			tooltips_element.style.display = '';
		}
	}
	/*
		Hide file info box
	*/
	function hideTooltips() {
		if (tooltips_element) {
			tooltips_element.style.display = 'none';
		}
	}
	/*
		Fix element position from event to show in window without the scrool bars
		Element must be visible
			evt - mouse event
			element - element to show
			cursor_size - Size in pixels of cursor, default 20
		
	*/
	function fixMouseEventElementPosition(evt, element, cursor_size) {
		var
			event_position = getEventPos(evt),
			winGeom = getWindowGeometry(),
			top = 0,
			left = 0
		;

		if (typeof cursor_size == 'undefined') {
			cursor_size = 20;
		}
		if (event_position.x + element.offsetWidth + cursor_size - winGeom.xOffset > winGeom.width) {
			left = event_position.x - element.offsetWidth - cursor_size;
		} else {
			left = event_position.x;
		}

		if (event_position.y + element.offsetHeight + cursor_size - winGeom.yOffset > winGeom.height) {
			top = event_position.y - element.offsetHeight - cursor_size;
		} else {
			top = event_position.y;
		}
		
		element.style.top = top + cursor_size + 'px';
		element.style.left = left + cursor_size + 'px';
	}
	/*
		Fix the folder and files list height
	*/
	function fixFileListHeight() {
		var
			img_lib_height = getElPos(img_lib_element),
			tulbar_height
		;
		// 2 - top and bottom border height
		img_lib_height = img_lib_height.height - 2;
		// Apply height value
		if (img_lib_height > 0) {
			tulbar_height = getElPos(tulbar_element);
			tulbar_height = tulbar_height.height;
			// Litle trick for ie - its use crolbars -> the -1px for width fire the resize event in loop, and the end -> no scrol bars
			if (isIE) tulbar_height++;
			file_list_element.style.height = (img_lib_height - tulbar_height) + 'px';
		}
	}
	/*
		Highlight the file items or if not set - then clean
			index - a file index in dir_content.files array
	*/
	function highlightFileItem(index) {
		// Get all elements
		var
			fileItemsElements = (view_type != 'table') ? file_list_element.getElementsByTagName('li') : file_list_element.getElementsByTagName('tr'),
			i
		;

		// Clear exist selection
		for (i = fileItemsElements.length; i-- > 0;) {
			if (fileItemsElements[i].className == 'selected') {
				fileItemsElements[i].className = '';
			}
		}

		if (!index && index != 0) return;

		// Select the items
		for (i = fileItemsElements.length; i-- > 0;) {
			if ((fileItemsElements[i].itemType == 2) && (fileItemsElements[i].index == index)) {
				fileItemsElements[i].className = 'selected';
			}
		}
	}
	/*
		Search file in list by mask
			@mask - file name mask
	*/
	function searchFile(mask) {
		var
			usr_dir_content = {dirs: [], files: []}, // custum dir content who containt the searced files
			dirs = dir_content.dirs,
			files = dir_content.files,
			i
		;
		if (mask.length > 0) {
			for (i = 0; i < dirs.length; i++) {
				if (dirs[i].name.toLowerCase().indexOf(mask.toLowerCase()) != -1) {
					usr_dir_content.dirs[i] = dirs[i];
				}
			}
			for (i = 0; i < files.length; i++) {
				if (files[i].name.toLowerCase().indexOf(mask.toLowerCase()) != -1) {
					usr_dir_content.files[i] = files[i];
				}
			}
			createFileList(usr_dir_content);
		} else {
			createFileList();
		}
	}
	/*
		Show form to upload files to server
	*/
	function showUploadForm() {
		// Check if upload is posible
		if (dir_content.inf.is_writable != 1) return alert(msg.writeProtectTitle);

		// Check if upload form show and hide it
		if (upload_form_show == 1) {
			upload_form_show = 0;
			return createFileList();
		} else {
			upload_form_show = 1;
			file_input_field_num = 0;
			hideContextMenu();
		}

		var
			form = document.createElement('form'),
			add_control = document.createElement('span')
		;
		form.setAttribute('method', 'post');
		form.setAttribute('action', req_url);
		form.setAttribute('enctype', 'multipart/form-data');
		form.setAttribute('encoding', 'multipart/form-data');

		form.innerHTML = '<input type="hidden" name="MAX_FILE_SIZE" value="' + max_upload_size + '" /><input type="hidden" name="dir" value="' + cur_path + '" /><div class="info" style="">' + msg.allowExtTitle + ': ' + allowed_ext.join(', ') + '<br />' + msg.maxUploadSizeTitle + ': ' + getFloatSize(max_upload_size) + '/' + getFloatSize(max_upload_file_size) + '<br />' + msg.pathTitle + ': ' + cur_path + '</div><div class="inputs"></div><button type="submit">' + '<span class="icon upload" style="float:left;"></span><span class="text' + ((isIE && !isOpera) ? ' clear' : '') + '">' + msg.uploadTitle + '</span></button><button type="reset">' + '<span class="icon del" style="float:left;"></span><span class="text' + ((isIE && !isOpera) ? ' clear' : '') + '">' + msg.cancelTitle + '</span></button>';
		form.getElementsByTagName('div')[1].appendChild(getFileInput(0));

		addEvent(form, 'submit', sendFile);
		add_control.className = 'icon add';
		add_control.setAttribute('title', msg.addFieldTitle);
		addEvent(add_control, 'click', function () {file_list_element.getElementsByTagName('div')[1].appendChild(getFileInput(1));});
		addEvent(form.lastChild, 'click', showUploadForm);

		form.appendChild(add_control);

		file_list_element.innerHTML = '';
		file_list_element.appendChild(form);
	}
	/*
		Return the element with file select input to add to form
		@can_delete - this field can be deleted by user
	*/
	function getFileInput(can_delete) {
		var
			div = document.createElement('div'),
			delete_control = document.createElement('span'),
			clear = document.createElement('div')
		;
		// Apply properties to elements
		delete_control.className = 'icon del';
		delete_control.setAttribute('title', msg.delFieldTitle);
		addEvent(delete_control, 'click', function() {delete_control.parentNode.parentNode.removeChild(delete_control.parentNode);});

		div.className = 'input';
		clear.className = 'clear';

		// Build
		div.innerHTML = '<label>' + msg.fileNameTitle + ':</label><input type="file" name="file[' + (++file_input_field_num) + ']" />';
		if (can_delete) {
			div.appendChild(delete_control);
		}
		div.appendChild(clear);
		return div;
	}
	/*
		Send file(s) to server using pseudo ajax technique
	*/
	function sendFile(evt) {
		// Check if upload is enabled
		evt = fixEvent(evt);
		if (!enable_upload) return cancelEvent(evt);
		// Check the first field
		if (evt.target.getElementsByTagName('input')[2].value == '') {alert(msg.selectFirstFileTitle); return cancelEvent(evt);}
		var
			rand = Math.floor(Math.random() * 100000000),
			iframeContainer = document.createElement('span'),
			frameName = 'if' + rand,
			iframeElement
		;
		evt.target.setAttribute('target', frameName);
		iframeContainer.innerHTML = '<iframe style="display:none;" name="' + frameName + '" src="about:blank"></iframe>';
		iframeElement = iframeContainer.getElementsByTagName('iframe')[0];
		addEvent(iframeElement, 'load', function () {onSendFile(iframeElement);});

		file_list_element.appendChild(iframeContainer);
		showLoading();
	}
	/*
		Do something alfer file is uploaded
		@frame - frame to check
	*/
	function onSendFile(frame) {
		//Check if form loaded intro frame
		var frameDocument;
		if (frame.contentDocument) {
			frameDocument = frame.contentDocument;
		} else if (frame.contentWindow) {
			frameDocument = frame.contentWindow.document;
		}
		if (frameDocument.location.href != "about:blank") {
			// Do something only if frame load real document
			file_list_element.removeChild(frame.parentNode);
			hideLoading();
			reloadDirContent();
		}
	}

	return {
		init: function (settings) {
			/*
				Initialise first step
				@settings - object with configuration parametrs
			*/

			// Set the settings from curent call function init or from constructor call
			setSettings(settings);

			// Check the AJAX support
			if (!(createXMLHttpRequestObject())) {
				alert(msg.ajaxIsReguire);
				return false;
			}

			addEvent(window, 'load', onLoad);
		},

		getSelectedItem: function () {
			/*
				Return a information from selected items
			*/
			if (context_element) {
				return context_element;
			}
		},

		getItemInfo: function (type, index) {
			/*
				Return a information for file or folder
			*/
			if (type == 1) {
				return dir_content.dirs[index];
			} else if (type == 2) {
				return dir_content.files[index];
			}
		},

		getDirContent: function () {
			/*
				Return a curent dir content
			*/
			return dir_content;
		},

		gotoPath: function (path) {
			/*
				Go to dir if ready
				path - path to target folder, start from "/" (example - "/documents")
			*/
			if (ready) {
				getDirContent(path);
			}
		},

		setStartPath: function (path) {
			/*
				Set the start dir for browsing, must be called before the imgLib initialization
				path - path to target folder, start from "/" (example - "/documents")
			*/
			if (!ready) {
				cur_path = path;
			}
		}
	}
}());