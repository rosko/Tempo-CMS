/********************************************************************
 * imgLib Image Tools v0.01 23.11.2009
 * Contact me at dev@imglib.endofinternet.net
 * Site: http://www.imglib.endofinternet.net/
 * This copyright notice MUST stay intact for use.
 ********************************************************************/

/************************ Image Tools ****************************/

var imageTools = (function () {
	var
		ImgToolsOptions = {
			src: null, // URL to processed image
			action: 'img_tools.php', // URL to file who process the image edit and preview
			srcArg: 'src', // Source image URL
			dstArg: 'dst', //Destination image URL
			previewArg: 'preview', // Argument to preview image rotation and flip
			resizeArg: 'resize', // Argument to resize image
			cropArg: 'crop', // Argument to crop image
			rotateArg: 'rotate', // Argument to rotate image
			flipArg: 'flip', // Argument to flip image
			loadingIndicator: 'loading.gif', // URL to loading indicator image file
			imgResizeOpt: { //Image resize options
				id: 'image_resize',
				loadingSrc: 'loading.gif',
				saveProportion: true,
				chSizeStep: 5,
				minBarScale: 1,
				maxBarScale: 200
				/*,
				onResize: function (size) {}
				*/
			},
			imgCropOpt: {//Image crop options
				overlay: {show: true},
				cropArea: {borderWidth: '1'},
				dragSquare: {show: true},
				cropAreaGrid: {show: true, opacity: 20},
				onCrop: showCrop
			},
			imgRotateFlipOpt: { //Image rotate-flip options
				onRotate: function (state) {hideLoadingStatus();},
				onFlip: function (state) {hideLoadingStatus();}
			}
		},
		msg = { // Messages strings
			errorLoadData: 'Error loading data!',
			cropImageWait: 'Crop image. Please wait.',
			resizeImageWait: 'Resize image. Please wait.',
			rotateFlipImageWait: 'Rotate/Flip image. Please wait.',
			loadingPreview: 'Loading preview....',
			errorPrefix: 'Error: ',
			loading: 'Loading....'
		},
		imgWidth,
		imgHeight,
		imgViewWidth,
		angle = 0, // Init the angle and flip type
		flipType = -1,
		// Elements id
		image_crop_id = 'image_crop',
		rotate_flip_img_id = 'rotate_flip_img',
		flip_h_id = 'flip_h',
		flip_v_id = 'flip_v',
		resize_edit_area_id = 'resize_edit_area',
		crop_edit_area_id = 'crop_edit_area',
		rotate_edit_area_id = 'rotate_edit_area',
		loading_id = 'LoadingStatus'
		;

	function initImageTools () {
	/*
		Init Image Tools
	*/
		//Init tabs
		minTabs.init('tabs');

		//Load all need data from parent window
		loadData();

		// Check the loading data
		if (!ImgToolsOptions.src) {
			alert(msg.errorLoadData);
			closeImageTools();
			return false;
		}

		//Preload image
		var preloadImg = new Image();
		preloadImg.onload = function() {
			//Set images view size
			imgWidth = preloadImg.width;
			imgHeight = preloadImg.height;

			//Init image resize
			ImgToolsOptions.imgResizeOpt.loadingSrc = ImgToolsOptions.loadingIndicator;
			imageResizer.init(ImgToolsOptions.src, ImgToolsOptions.imgResizeOpt);
			adjustImagesWidth();

			//Init image crop
			$(image_crop_id).src = ImgToolsOptions.src;
			imageCropper.init(image_crop_id, ImgToolsOptions.imgCropOpt);

			//Init image rotate and flip
			$(rotate_flip_img_id).src = ImgToolsOptions.src;

			// Translate the labels
			translateLabels(msg.labels);

			hideLoadingStatus();
			//Free resource
			preloadImg.onload = function() {};
		};

		// Add events
		addEvent($('resize_content_tab'), 'click', function () {imageCropper.hide(); setTimeout(function () {imageResizer.setWidth(imgViewWidth);}, 200);});
		addEvent($('crop_content_tab'), 'click', function () {activateCrop();});
		addEvent($('rotate_content_tab'), 'click', function () {initRotateFlip(); imageCropper.hide();});

		addEvent($('apply_resize'), 'click', function () {applyResize();});
		addEvent($('apply_crop'), 'click', function () {applyCrop();});
		addEvent($('apply_rotate_flip'), 'click', function () {applyRotateFlip();});
		
		addEvent($('reset_resize'), 'click', function () {imageResizer.reset();});
		addEvent($('rotate_ccw'), 'click', function () {previewRotateFlip(0, -90);});
		addEvent($('rotate_cw'), 'click', function () {previewRotateFlip(0, 90);});
		addEvent($(flip_h_id), 'click', function () {previewRotateFlip(1);});
		addEvent($(flip_v_id), 'click', function () {previewRotateFlip(1);});
		addEvent($('reset_rotate_flip'), 'click', function (evt) {previewRotateFlip(-1, 0); cancelEvent(evt);});
		
		addEvent($('close_button'), 'click', function (evt) {closeImageTools(); cancelEvent(evt);});
		addEvent(window, 'resize', adjustImagesWidth);

		showLoadingStatus();
		preloadImg.src = ImgToolsOptions.src;
	}

	function loadData () {
		var p = opener;
		if (!p) return;
		var data = p.getImgToolsData();
		ImgToolsOptions = extend(ImgToolsOptions, data || {});
		msg = extend(msg, data.msg || {});
	}

	function adjustImagesWidth () {
		var
			// Adjust image size if then bigger that window, and adjust the edit area size
			wSize = getWindowGeometry(),
			// Adjust the edit areas height, for example get the resize control height (crop and rotate is same)
			resize_area = $('resize_content'),
			crop_area = $('crop_content'),
			editHeight = (resize_area.style.display == '') ? resize_edit_area_id : ((crop_area.style.display == '') ? crop_edit_area_id : rotate_edit_area_id),
			editHeight = getElPos($(editHeight)),
			editWidth = editHeight.width
		;
		editHeight = (wSize.height - getElPos($('footer')).height - editHeight.top - 10);
		// 10px - for borders and other
		$(resize_edit_area_id).style.height = editHeight + 'px';
		$(crop_edit_area_id).style.height = editHeight + 'px';
		$(rotate_edit_area_id).style.height = editHeight + 'px';

		if ( (wSize.width > 0) && ( (imgWidth > (editWidth - 10)) ) || (imgHeight > (editHeight - 10) ) ) {
			if ( (imgWidth/editWidth) - 10 > (imgHeight/editHeight) - 10 ) {
				imgViewWidth = (editWidth - 10);
			} else {
				imgViewWidth = (imgWidth/(imgHeight/editHeight)) - 10;
			}
			$(image_crop_id).style.width = imgViewWidth + 'px';
			$(rotate_flip_img_id).style.width = imgViewWidth + 'px';
			setTimeout(function () {imageResizer.setWidth(imgViewWidth);}, 200);
		} else {
			imgViewWidth = imgWidth;
		}

	}

	function reloadImage () {
		if (!ImgToolsOptions.src) return false;
		// Reload image after change
		//Preload image
		var preloadImg = new Image();
		preloadImg.onload = function() {
			//Set images view size
			imgWidth = preloadImg.width;
			imgHeight = preloadImg.height;

			adjustImagesWidth();

			//Set the new image src
			$('image_resize').src = $(rotate_flip_img_id).src = $(image_crop_id).src = preloadImg.getAttribute('src');

			imageResizer.reloadImage();
			imageResizer.setWidth(imgViewWidth);

			imageCropper.resetPos();
			imageCropper.reloadImage();
			imageCropper.hide();

			previewRotateFlip(-1);

			//Free resource
			preloadImg.onload = function() {};
		};
		preloadImg.src = ImgToolsOptions.src +((ImgToolsOptions.src.indexOf('?') == -1) ? '?' : '&' ) + 'rnd='+Math.random();
	}

	function initRotateFlip() {
		if (!ImgToolsOptions.src) return false;

		if (!rotateFlipImage.getState()) {
			var imgRotateFlipOpt = {
				imageSrc: ImgToolsOptions.src,
				previewURL: ImgToolsOptions.action + ((ImgToolsOptions.action.indexOf('?') == -1) ? '?' : '&' ) + ImgToolsOptions.previewArg + '=1',
				previewImageURLArg: ImgToolsOptions.srcArg,
				previewRotateArg: ImgToolsOptions.rotateArg,
				previewFlipArg: ImgToolsOptions.flipArg
			};

			ImgToolsOptions.imgRotateFlipOpt = extend(ImgToolsOptions.imgRotateFlipOpt, (imgRotateFlipOpt || {}));
			rotateFlipImage.init(rotate_flip_img_id, ImgToolsOptions.imgRotateFlipOpt);

			$(flip_h_id).checked = $(flip_v_id).checked = false;
		}
	}

	function activateCrop() {
		if (!ImgToolsOptions.src) return;
		setTimeout(function () {imageCropper.resetPos();}, 100);
	}

	function showCrop (pos) {
		if (!ImgToolsOptions.src) return;
		$('crop_sel_start').innerHTML = pos.X1 + ',' + pos.Y1;
		$('crop_sel_end').innerHTML = pos.X2 + ',' + pos.Y2;
	}

	function applyCrop () {
		if (!ImgToolsOptions.src) return;
		var newCrop = imageCropper.getCrop();
		if ( ((newCrop.X2 - newCrop.X1) > 0) || ((newCrop.Y2 - newCrop.Y1) > 0) ) {
			showLoadingStatus(msg.cropImageWait);
			var params = 'cmd=edit&' + ImgToolsOptions.srcArg + '=' + encodeURIComponent(decodeURIComponent(ImgToolsOptions.src)) + '&' + ImgToolsOptions.dstArg + '=' + encodeURIComponent(decodeURIComponent(ImgToolsOptions.src)) + '&' + ImgToolsOptions.cropArg + '=' + newCrop.X1 + ',' + newCrop.Y1 + ',' + (newCrop.X2 - newCrop.X1) + ',' + (newCrop.Y2 - newCrop.Y1);

			sendXMLHttpReq(ImgToolsOptions.action, {
				mode: 'POST',
				parameters: params,
				onsuccess: function(req) {
					if (req.responseText != 'OK') {
						alert(msg.errorPrefix + req.responseText);
						hideLoadingStatus();
					} else {
						//alert('Success!');
						reloadImage();
						hideLoadingStatus();
					}
				}
			});
		} else {
			//alert('notfing to crop');
		}
	}

	function applyResize () {
		if (!ImgToolsOptions.src) return;
		var
			newWidth = imageResizer.getSize(),
			newHeight = newWidth.height,
			newWidth = newWidth.width
		;
		if ( (newWidth > 0 && newHeight > 0) && ( (newWidth != imgWidth) || (newHeight != imgHeight) ) ) {
			showLoadingStatus(msg.resizeImageWait);
			var params = 'cmd=edit&' + ImgToolsOptions.srcArg + '=' + encodeURIComponent(decodeURIComponent(ImgToolsOptions.src)) + '&' + ImgToolsOptions.dstArg + '=' + encodeURIComponent(decodeURIComponent(ImgToolsOptions.src)) + '&' + ImgToolsOptions.resizeArg + '=' + newWidth + ',' + newHeight;
			sendXMLHttpReq(ImgToolsOptions.action, {
				mode: 'POST',
				parameters: params,
				onsuccess: function(req) {
					if (req.responseText != 'OK') {
						alert(msg.errorPrefix + req.responseText);
						hideLoadingStatus();
					} else {
						//alert('Success!');
						reloadImage();
						hideLoadingStatus();
					}
				}
			});
		}else {
			//alert('notfing to resize');
		}
	}

	function applyRotateFlip () {
		if (!ImgToolsOptions.src) return;
		if ( !( ( (angle == 0) && (flipType == -1) ) || ( (angle == 180) && (flipType == 2) ) ) ) {
			showLoadingStatus(msg.rotateFlipImageWait);
			var params = 'cmd=edit&' + ImgToolsOptions.srcArg + '=' + encodeURIComponent(decodeURIComponent(ImgToolsOptions.src)) + '&' + ImgToolsOptions.dstArg + '=' + encodeURIComponent(decodeURIComponent(ImgToolsOptions.src)) + '&' + ImgToolsOptions.rotateArg + '=' + angle + '&' + ImgToolsOptions.flipArg + '=' + flipType;
			sendXMLHttpReq(ImgToolsOptions.action, {
				mode: 'POST',
				parameters: params,
				onsuccess: function(req) {
					if (req.responseText != 'OK') {
						alert(msg.errorPrefix + req.responseText);
						hideLoadingStatus();
					} else {
						//alert('Success!');
						reloadImage();
						hideLoadingStatus();
					}
				}
			});
		} else {
			//alert('notfing to rotate/flip');
		}
	}

	function previewRotateFlip (operationType, val) {
		// if object not ready
		if (!rotateFlipImage.getState()) return;
		if (!ImgToolsOptions.src) return false;

		if (operationType == 0) {
			//Rotate
			angle += parseInt(val);
			angle %= 360;
			angle = (angle < 0) ? angle + 360 : angle;

			showLoadingStatus(msg.loadingPreview);
			rotateFlipImage.rotate(angle);

		} else if (operationType == 1) {
			//Flip image
			var
				flip_v = $(flip_v_id),
				flip_h = $(flip_h_id)
			;

			//Check the flip type
			if ( (flip_v.checked == true) && (flip_h.checked == true) ) {
				flipType = 2;
			} else if (flip_h.checked == true) {
				flipType = 0;
			} else if (flip_v.checked == true) {
				flipType = 1;
			} else {
				flipType = -1;
			}

			showLoadingStatus(msg.loadingPreview);
			rotateFlipImage.flip(flipType);
		} else {
			//Reset image
			rotateFlipImage.reset();
			angle = 0;
			flipType = -1;
			$(flip_h_id).checked = $(flip_v_id).checked = false;
		}
		var imgEl = $(rotate_flip_img_id);
		if ( (angle == 90) || (angle == 270) ) {
			imgEl.style.height = imgViewWidth + 'px';
			imgEl.style.width = ''
		} else {
			imgEl.style.width = imgViewWidth + 'px';
			imgEl.style.height = ''
		}
		return true;
	}

	function showLoadingStatus (text, overlayColor, textColor) {
		text = text || msg.loading;
		var loading = $(loading_id);
		if (!loading) {
			overlayColor = overlayColor || '#fff';
			textColor = textColor || '#000';
			//Create loading status div
			var loading = document.createElement('div');
			loading.id = loading_id;
			with (loading.style) {
				position = 'absolute';
				width = '100%';
				height = '100%';
				top = '0';
				left = '0';
			}
			loading.innerHTML = '<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: ' + overlayColor + ';"></div><div style="position: relative; margin: 0 auto; width: 100%; top: 50%; text-align: center; color: ' + textColor + '; font-weight: bold; "><img src="' + ImgToolsOptions.loadingIndicator + '" aligh="center" /><span></span></div>';
			document.body.appendChild(loading);
			setTransparency(loading.getElementsByTagName('div')[0], 70);
		}
		loading.getElementsByTagName('span')[0].innerHTML = text;
		loading.style.display = '';
	}

	function hideLoadingStatus () {
		if ($(loading_id)) {
			$(loading_id).style.display = 'none';
		}
	}

	function closeImageTools() {
		window.close();
	}

	return {
		init: function () {
			/*
				Init the all Image Tools component
			*/
			addEvent(window, 'load', initImageTools);
		}
}
}());

imageTools.init();

/************************ Tabs support ***************************/

/*
	Minimal Tabs
	Ver. 0.1
	dev@imglib.endofinternet.net
*/
var minTabs = (function() {
	var activeClass='active';
	function getId(id) {
		return document.getElementById(id);
	}
	/*
		Get tags from element
	*/
	function getTags(element, tag) {
		return element.getElementsByTagName(tag);
	}
	/*
		Reset the state of tabs with id
	*/
	function reset(id) {
		var tabsEl = getTags(getTags(getId(id), 'ul')[0], 'li');
		for (var i=0, len=tabsEl.length; i<len; i++) {
			tabsEl[i].className = '';
			getId(getTags(tabsEl[i], 'a')[0].getAttribute('rel')).style.display = 'none';
		}
	}
	/*
		Activate the selected tab with the tabs element id
	*/
	function chTab(evt, id) {
		evt = evt || window.event;
		var targetEl = evt.target || evt.srcElement, selectedTab = getId(targetEl.getAttribute('rel'));
		if (selectedTab) {
			reset(id);
			selectedTab.style.display = '';
			targetEl.parentNode.className = activeClass;
		}
	}
	/*
		Initialise the tabs with ids, for example minTabs('myTab') or minTabs('myFirstTab', 'mySecondTab',...);
	*/
	return {
		init: function() {
			var argLen = arguments.length;
			if (argLen == 0) return;
			if (argLen > 1) {
				for (var i = argLen; --i >= 0;) {
					this.init(arguments[i]);
				}
			} else {
				var id = arguments[0], tabEl = getTags(getId(id), 'ul')[0];
				if (!tabEl) return;
				tabEl.onclick = function (evt) {chTab(evt, id); return false;};
				reset(id);
				/*
					Activate the first tab
				*/
				tabEl = getTags(tabEl, 'li')[0];
				tabEl.className = activeClass;
				getId(getTags(tabEl, 'a')[0].getAttribute('rel')).style.display = '';
			}
		}
	}
}());

/************************ Resize suport **************************/

/*
	Image resize script
	Ver 0.0141
	dev@imglib.endofinternet.net
*/
var imageResizer = (function () {
var
	// Elements ID
	imgId, //Target Image id
	ready, // The object is ready
	savePropId, //Save proportion check box
	widthInpId, //Width input id
	heightInpId, //Heigth input id
	widthScaleBarId, //Bar to scale the image to width
	widthScalePointerId, //Pointer to scale image to width
	widthScaleLabelId, //Label to display curent scale in % to width
	heightScaleBarId,
	heightScalePointerId,
	heightScaleLabelId,
	widthSizeDecId, //Width decrement "button"
	widthSizeIncId, //Width increment "button"
	heightSizeDecId,
	heightSizeIncId,
	imgPropId, //Element to display image proportion
	loadImgSrc, //Loading image path
	imgProp = {}, //Image properties - object {width, heigth}
	saveProp, //Save proportion of image
	chSizeStep, //Change size step in %
	minBarScale, //Minimum scale in % on bar
	maxBarScale, //Maximum scale in % on bar
	targetSide, // Who has change width (1) or height (2)
	lastChangeSide, // Who side last change - width (1) or height (2)
	newWidth,
	newHeight,
	onResize,
	// Short access for round function
	round = Math.round,
	isOpera = window.opera ? true : false;

	/*
		Read the image width and height
	*/
	function getImgDim () {
		newWidth = parseInt($(widthInpId).value);
		newHeight = parseInt($(heightInpId).value);
	}

	/*
		Write the image width and height
	*/
	function setImgDim () {
		$(widthInpId).value = newWidth;
		$(heightInpId).value = newHeight;
		if (onResize) {
			onResize(imageResizer.getSize());
		}
	}

	/*
		Apply to image new width and height
	*/
	function applyImgDim () {
		with($(imgId).style) {
			width = newWidth + 'px';
			height = newHeight + 'px';
		}
	}

	/*
		Resize image with the parametrs
			1 - resize with with input value
			2 - resize with height input value
	*/
	function resizeImage () {
		getImgDim();
		if (lastChangeSide == 1) {
			if (saveProp) {
				newHeight = parseInt(newWidth * ( imgProp.height / imgProp.width) );
			}
		} else if (lastChangeSide == 2) {
			if (saveProp) {
				newWidth = parseInt(newHeight * ( imgProp.width / imgProp.height) );
			}
		}

		//If new calculated width or height is not valid, use previsious width value
		newWidth = ( (newWidth < 1)|| isNaN(newWidth) || (typeof newWidth != 'number') ) ? 1 : newWidth;
		newHeight = ( (newHeight < 1)|| isNaN(newHeight) || (typeof newHeight != 'number') ) ? 1 : newHeight;

		setImgDim();
		//Aply the new width and heigth
		applyImgDim();
		adjustSizePointer();
	}

	/*
		Change size of image to +/_ step % to dst:
			type: 0 - decrease, 1 - increase
			dst = 1 - width
			dst = 2 - height
	*/
	function chSize (type, dst) {
		getImgDim();
		//if saving proportion then using width
		dst = saveProp ? 1 : dst;
		var
			scale,
			curChSizeStep = (parseInt(imgProp.width * (chSizeStep / 100) ) <= 0) ? Math.ceil(1/(imgProp.width/100)) : chSizeStep; // If image is small then correct the change size step
		if ( (dst == 1) || saveProp) {
			scale = newWidth / imgProp.width;
		} else if (dst == 2) {
			scale = newHeight / imgProp.height;
		}
		scale = scale * 100;
		scale = (type == 0) ? scale - curChSizeStep : scale + curChSizeStep;
		scale = round(scale);
		scale = (scale == (minBarScale + chSizeStep)) ? chSizeStep : scale; //If press + on start the bar then use the chSizeStep value correct the minBarScale of bar
		scale = (scale <= 0) ? minBarScale : scale;
		if ((saveProp) || (!saveProp && (dst == 1)) ) {
			newWidth = parseInt(imgProp.width * (scale / 100) );
		}
		if ((saveProp) || (!saveProp && (dst == 2)) ) {
			newHeight = parseInt(imgProp.height * (scale / 100) );
		}

		setImgDim();
		//Aply the new width and heigth
		applyImgDim();
		adjustSizePointer();
	}

	/*
		Adjust the size pointer position
	*/
	function adjustSizePointer () {
		var scaleW, scaleH;
		scaleW = newWidth / imgProp.width;
		scaleW = ( (scaleW <= 0) || isNaN(scaleW)) ? minBarScale/100 : scaleW;
		scaleW = round(parseInt((scaleW * 100)));
		scaleH = newHeight / imgProp.height;
		scaleH = ( (scaleH <= 0) || isNaN(scaleH)) ? minBarScale/100 : scaleH;
		scaleH = round(parseInt((scaleH * 100)));

		var sizeBarPosW = getElPos(widthScaleBarId);
		$(widthScaleLabelId).innerHTML = ( (scaleW <= 0) ? minBarScale : scaleW) + ' %';
		scaleW = (scaleW > maxBarScale) ? maxBarScale : scaleW;
		$(widthScalePointerId).style.left = getElPos(widthScaleBarId).left + scaleW - (getElPos(widthScalePointerId).width/2) + 'px';
		var sizeBarPosH = getElPos(heightScaleBarId);
		$(heightScaleLabelId).innerHTML = ( (scaleH <= 0) ? minBarScale : scaleH) + ' %';
		scaleH = (scaleH > maxBarScale) ? maxBarScale : scaleH;
		$(heightScalePointerId).style.left = getElPos(heightScaleBarId).left + scaleH - (getElPos(heightScalePointerId).width/2) + 'px';
	}

	/*
		Set the save proportion on change the save proportion property
	*/
	function setSaveProportion () {
		if ($(savePropId).checked) {
			saveProp = true;
			resetImage();
		} else {
			saveProp = false;
		}
	}

	/*
		Reset image to initial state
	*/
	function resetImage () {
		$(widthInpId).value = imgProp.width;
		$(heightInpId).value = imgProp.height;
		getImgDim();
		//Aply the new width and heigth
		applyImgDim();
		$(savePropId).checked = saveProp;
		adjustSizePointer();
	}

	/*
		Load image to resizer from src or image element
	*/
	function loadImage (src) {
		var preloadImg = new Image(), imgEl = $(imgId);
		preloadImg.onload = function() {
			//var imgEl = $(imgId);
			var width = preloadImg.width, height = preloadImg.height, imgDim;
			$(widthInpId).value = width;
			$(heightInpId).value = height;
			$(imgId).setAttribute('src', preloadImg.src);
			$(imgId).src = preloadImg.src;
			imgDim = (width > height) ? {width: (width / height), height: 1} : {width: 1, height: (height / width)};
			imgProp.width = width;
			imgProp.height = height;
			$(imgPropId).innerHTML = ( (width > height) ? (imgDim.width.toFixed(2)+'x'+imgDim.height) : (imgDim.width+'x'+imgDim.height.toFixed(2)));
			resetImage();
			preloadImg.onload = function() {};
		};
		preloadImg.src = src || imgEl.getAttribute('src');
		// Not work in IE
		//imgEl.setAttribute('src', loadImgSrc);
	}

	/*
		Adjust position of size pointer alfer window resize
	*/
	function onWindowResize() {
		adjustSizePointer();
	}

	/*
		Start drag the object
	*/
	function startDrag (evt) {
		if (targetSide) {
			//Fix event properties
			evt = fixEvent(evt);
			getImgDim();
			if (targetSide == 1) {
				var scale = evt.pageX - getElPos(widthScaleBarId).left;
				scale = (scale > maxBarScale) ? maxBarScale : ( (scale <= 0) ? minBarScale : scale);
				newWidth = parseInt(imgProp.width * (scale/100));
				if (saveProp) {
					newHeight = parseInt(imgProp.height * (scale/100));
				}
			} else if (targetSide == 2) {
				var scale = evt.pageX - getElPos(heightScaleBarId).left;
				scale = (scale > maxBarScale) ? maxBarScale : ( (scale <= 0) ? minBarScale : scale);
				if (saveProp) {
					newWidth = parseInt(imgProp.width * (scale/100));
				}
				newHeight = parseInt(imgProp.height * (scale/100))
			}
			setImgDim();
			//Aply the new width and heigth
			applyImgDim();
			adjustSizePointer();
			cancelEvent(evt);
		}
	}

	/*
		Set the settings
	*/
	function setSettings(settings) {
/*
	settings = {
		id: 'image_resize',
		loadingSrc: 'loading.gif',
		saveProportion: true,
		savePropId: 'saveProp',
		widthInpId: 'width',
		heightInpId: 'height',
		widthScaleBarId: 'sizeBarW',
		widthScalePointerId: 'sizePointerW',
		widthScaleLabelId: 'widthScaleLabel',
		heightScaleBarId: 'sizeBarH',
		heightScalePointerId: 'sizePointerH',
		heightScaleLabelId: 'heightScaleLabel',
		widthSizeDecId: 'sizeDecW',
		widthSizeIncId: 'sizeIncW',
		heightSizeDecId: 'sizeDecH',
		heightSizeIncId: 'sizeIncH',
		imgPropId: 'imgProp',
		chSizeStep: 5,
		minBarScale: 1,
		maxBarScale: 200,
		onResize: function (size) {}
	}
*/
		settings = settings || {};
		imgId = settings.id || 'image_resize'; //Target Image id
		loadImgSrc = settings.loadingSrc || 'loading.gif'; //Loading image path
		saveProp = (typeof settings.saveProportion != 'undefined') ? settings.saveProportion : true; //Save proportion of image
		savePropId = settings.savePropId || 'saveProp'; //Save proportion check box
		widthInpId = settings.widthInpId || 'width'; //Width input id
		heightInpId = settings.heightInpId || 'height'; //Heigth input id
		widthScaleBarId = settings.widthScaleBarId || 'sizeBarW'; //Bar to scale the image to width
		widthScalePointerId = settings.widthScalePointerId || 'sizePointerW'; //Pointer to scale image to width
		widthScaleLabelId = settings.widthScaleLabelId || 'widthScaleLabel'; //Label to display curent scale in % to width
		heightScaleBarId = settings.heightScaleBarId || 'sizeBarH';
		heightScalePointerId = settings.heightScalePointerId || 'sizePointerH';
		heightScaleLabelId = settings.heightScaleLabelId || 'heightScaleLabel';
		widthSizeDecId = settings.widthSizeDecId || 'sizeDecW'; //Width decrement "button"
		widthSizeIncId = settings.widthSizeIncId || 'sizeIncW'; //Width increment "button"
		heightSizeDecId = settings.heightSizeDecId || 'sizeDecH';
		heightSizeIncId = settings.heightSizeIncId || 'sizeIncH';
		imgPropId = settings.imgPropId || 'imgProp'; //Element to display image proportion
		chSizeStep = settings.chSizeStep || 5; //Change size step in %
		minBarScale = settings.minBarScale || 1; //Minimum scale in % on bar
		maxBarScale = settings.maxBarScale || 200; //Maximum scale in % on bar
		onResize = settings.onResize || onResize; //function get the new size object {width, height} on resize change
	}

	return {
	/*
		Initialise the object and set the parametrs
			src = '/img/sample_image.jpg' //Path to resized image
			settings - some settings
	*/
		init: function (scr, settings) {
			if (!scr) return;

			//Set the settings
			setSettings(settings);

			/*
				Attach Event
			*/
			addEvent($(widthInpId), 'keyup', function () {lastChangeSide = 1; resizeImage();} );
			addEvent($(heightInpId), 'keyup', function () {lastChangeSide = 2; resizeImage();});
			if (isOpera) {
				//addEvent($(widthInpId), 'keypress', function () {lastChangeSide = 1; resizeImage();});
				//addEvent($(heightInpId), 'keypress', function () {lastChangeSide = 2; resizeImage();});
			}
			addEvent($(savePropId), 'change', setSaveProportion);
			addEvent($(savePropId), 'click', setSaveProportion);
			addEvent($(widthSizeDecId), 'click', function() {chSize(0, 1);});
			addEvent($(widthSizeIncId), 'click', function() {chSize(1, 1);});
			addEvent($(heightSizeDecId), 'click', function() {chSize(0, 2);});
			addEvent($(heightSizeIncId), 'click', function() {chSize(1, 2);});

			addEvent($(widthScalePointerId), 'mousedown', function(evt) {targetSide = 1; cancelEvent(evt);});
			addEvent($(heightScalePointerId), 'mousedown', function(evt) {targetSide = 2; cancelEvent(evt);});
			addEvent(document, 'mouseup', function() {targetSide = null;});
			addEvent(document, 'mousemove', startDrag);
			addEvent(window, 'resize', onWindowResize);

			//Size of scale bar
			$(widthScaleBarId).style.width = maxBarScale + 'px';
			$(heightScaleBarId).style.width = maxBarScale + 'px';

			/*
				Preload Image
			*/
			loadImage(scr);

			// Set object to ready
			ready = true;
		},

		/*
			Return object {width, height} with image size
		*/
		getSize: function() {
			if (!ready) return false;
			return {width: newWidth, height: newHeight};
		},

		/*
			Reset the image
		*/
		reset: function() {
			if (!ready) return false;
			return resetImage();
		},

		/*
			Reload the image
		*/
		reloadImage: function() {
			if (!ready) return false;
			return loadImage();
		},

		/*
			Set the image width
		*/
		setWidth: function(width) {
			if (!ready) return false;
			newWidth = parseInt(width);
			setImgDim();
			lastChangeSide = 1;
			return resizeImage();
		},

		/*
			Set the image height
		*/
		setHeight: function(height) {
			if (!ready) return false;
			newHeight = parseInt(height);
			setImgDim();
			lastChangeSide = 2;
			return resizeImage();
		}

	}
}());

/************************ Crop suport ***************************/

/*
	Image crop script
	Ver 0.0271
	dev@imglib.endofinternet.net
	Opera cursor hack from imgAreaSelect jQuery plugin by Michal Wojciechowski, http://odyniec.net/
*/
var imageCropper = (function () {
var
	//Croper properties - startX, startY, X1, Y1, X2, Y2, xOffset, yOffset
		startX=0,
		startY=0,
		X1=0,
		Y1=0,
		X2=0,
		Y2=0,
		xOffset=0,
		yOffset=0,
	imgProp = {}, //Image properties - object {top, left, width, heigth, realWidth, realHeigth, src}
	dragObj = null, //Object with curent draging obgect
	imageId, //Target image id
	ready, // The object is ready
	croperSufix = '_croper', //Croper id sufix
	operaElSufix = '_opera_fix', //Opera fix element id sufix
	//CSS class names
		croperClassName = 'imgCroper',
		cropAreaClassName = 'cropArea',
		croperDrawSqClassName = 'drawSq',
		cropOverlayClassName = 'overlay',
		cropAreaBorderClassName = 'border',
		croperDrawGridClassName = 'drawGrid',
		croperDrawGridWClassName = 'drawGridW',
		croperDrawGridHClassName = 'drawGridH',
	//Crop overlay settings
		overlayShow,
		overlayBackgroundColor,
		overlayOpacity,
	//Crop area settings
		cropAreaBorderWidth,
		cropAreaBorderStyle,
		cropAreaBorderColor,
		cropAreaBorderBackground,
		cropAreaBorderOpacity,
	//Drag square settings
		dragSquareShow,
		dragSquareBackground,
		dragSquareBorderColor,
		dragSquareBorderWidth,
		dragSquareOpacity,
	minDragStart, //Minimal drag to start crop in pixels
	cornerSize, //Size of the active border to resize the crop area
	constProportions, //Constrain proportions of crop area
	//Croop area grid settings
		cropAreaGridShow,
		cropAreaGridWidth,
		cropAreaGridStyle,
		cropAreaGridColor,
		cropAreaGridOpacity,
	onCrop = null, //Function execute on crop
	onCropEnd = null, //Function execute on end crop
	holdSide = false,
	isOpera = (window.opera) ? true : false,
	isIE = (navigator.appName == 'Microsoft Internet Explorer') ? true : false,
	cropEl, //Main crop layer element
	cropAreaEl, //Crop area layer element
	cropOverlay = [], //Crop Overlay Elements
	cropVisible = false, //If crop is visible
	round = Math.round, //Short access for Math lib
	max = Math.max,
	min = Math.min
	;

	/*
		Common function
	*/

	/*
		Create the document element and return it
	*/
	function createEl(elName) {
		return document.createElement(elName);
	}

	/*
		Create new or reset the exist crop area
	*/
	function showCropArea (evt) {
		//Fix event properties
		evt = fixEvent(evt);
		//Save the position of cursor on draging start, work with the fixed event
		startX = evt.pageX;
		startY = evt.pageY;
		holdSide = false;
		Y1 = evt.pageY - imgProp.top;
		X1 = evt.pageX - imgProp.left;
		Y2 = Y1;
		X2 = X1;

		if (!cropEl) {
			cropAreaEl = createEl('div');
			cropEl = createEl('div');
			cropEl.className = croperClassName;
			cropAreaEl.className = cropAreaClassName;

			if (dragSquareShow) {
				var croperDrawSq = createEl('div');
				croperDrawSq.className = croperDrawSqClassName;
				croperDrawSq.innerHTML = '<div class="tl"></div><div class="tr"></div><div class="br"></div><div class="bl"></div><div class="tc"></div><div class="rc"></div><div class="bc"></div><div class="lc"></div>';
				var dragSqs = croperDrawSq.getElementsByTagName('div');
				for (var i = dragSqs.length; i-- > 0;) {
					dragSqs[i].style.border = dragSquareBorderWidth + 'px solid ' + dragSquareBorderColor;
					dragSqs[i].style.background = dragSquareBackground;
					setTransparency(dragSqs[i], dragSquareOpacity);
				}
			}

			if (overlayShow) {
				for (i = 4; i-- > 0;) {
					cropOverlay[i] = createEl('div');
					cropOverlay[i].style.background = overlayBackgroundColor;
					cropOverlay[i].className = cropOverlayClassName;
					cropEl.appendChild(cropOverlay[i]);
					setTransparency(cropOverlay[i], overlayOpacity);
				}
			}

			var cropAreaBorders = createEl('div'), cropAreaBordersBk = createEl('div');
			cropAreaBorders.className = cropAreaBorderClassName;
			cropAreaBorders.innerHTML = '<div class="t"></div><div class="r"></div><div class="b"></div><div class="l"></div>';
			var dragSqs = cropAreaBorders.getElementsByTagName('div');
			for (var len = i = dragSqs.length, borderWidthTemplate='0 0 0 ' + cropAreaBorderWidth + 'px 0 0 0'; i-- > 0;) {
				with (dragSqs[i].style) {
					border = cropAreaBorderWidth + 'px ' + cropAreaBorderStyle + ' ' + cropAreaBorderColor;
					borderWidth = borderWidthTemplate.substring(2*(len-1-i), borderWidthTemplate.length-2*i);
					/*
					//Is eqivalent to next strings
					if (i == 0) {
						borderWidth = cropAreaBorderWidth + 'px 0 0 0';
					}
					if ( i == 1 ) {
						borderWidth = '0 ' +cropAreaBorderWidth + 'px 0 0';
					}
					if (i == 2) {
						borderWidth = '0 0 ' +cropAreaBorderWidth + 'px 0';
					}
					if (i == 3) {
						borderWidth = '0 0 0 ' +cropAreaBorderWidth + 'px';
					}
					*/
				}
				setTransparency(dragSqs[i], cropAreaBorderOpacity);
			}

			if (cropAreaGridShow) {
				var croperDrawGrid = createEl('div'), croperDrawGridW = createEl('div'), croperDrawGridH = createEl('div'), croperDrawGridBorder = cropAreaGridWidth + 'px ' + cropAreaGridStyle + ' ' + cropAreaGridColor;
				croperDrawGrid.className = croperDrawGridClassName;
				croperDrawGridW.className = croperDrawGridWClassName;
				croperDrawGridW.style.border = croperDrawGridBorder;
				croperDrawGridW.style.borderWidth = '0 ' + cropAreaGridWidth + 'px';
				croperDrawGridH.className = croperDrawGridHClassName;
				croperDrawGridH.style.border = croperDrawGridBorder;
				croperDrawGridH.style.borderWidth = cropAreaGridWidth + 'px 0';
				croperDrawGrid.appendChild(croperDrawGridW);
				croperDrawGrid.appendChild(croperDrawGridH);
				setTransparency(croperDrawGridW, cropAreaGridOpacity);
				setTransparency(croperDrawGridH, cropAreaGridOpacity);
				cropAreaEl.appendChild(croperDrawGrid);
			}

			cropAreaEl.appendChild(cropAreaBorders);

			if (dragSquareShow) {
				cropAreaEl.appendChild(croperDrawSq);
			}

			if (isOpera) {
				var operaDiv = createEl('div');
				operaDiv.id = imageId + croperSufix + operaElSufix;
				with (operaDiv.style) {
					height = '100%';
					position = 'absolute';
					width = '100%';
				}
				cropAreaEl.appendChild(operaDiv);
			}

			with (cropEl.style) {
				top = imgProp.top + 'px';
				left = imgProp.left + 'px';
				width = imgProp.width + 'px';
				height = imgProp.height + 'px';
			}

			holdSide = false;

			addEvent(cropEl, 'mousedown', startDrag);
			addEvent(cropAreaEl, 'mousedown', startDrag);
			addEvent(document, 'mousemove', onDrag);

			cropEl.appendChild(cropAreaEl);
			$(imageId).parentNode.appendChild(cropEl);
		}
		resetCrop();
		cropEl.style.display = 'none';
		$(imageId).style.cursor = 'crosshair';
		cropVisible = false;
	}

	/*
		Adjust the croper size
	*/
	function adjCroper (evt) {
		var
			cX1 = startX - imgProp.left, //Current X1 coordinate
			cX2, //Current X2 coordinate
			cY1 = startY - imgProp.top, //Current Y1 coordinate
			cY2; //Current Y1 coordinate

		//Get the coordinates
		if (startY > evt.pageY) {
			cY2 = cY1;
			cY1 = evt.pageY - imgProp.top;
		} else {
			cY2 = evt.pageY - imgProp.top;
		}
		if (startX > evt.pageX) {
			cX2 = cX1;
			cX1 = evt.pageX - imgProp.left;
		} else {
			cX2 = evt.pageX - imgProp.left;
		}

		if (holdSide == 'w') {
			cX1 = X1;
			cX2 = X2;
		} else if (holdSide == 'h') {
			cY1 = Y1;
			cY2 = Y2;
		}

		//Chek to max top position
		if (cY1 < 0) {
			cY1 = 0;
		}
		//Chek to max left position
		if (cX1 < 0) {
			cX1 = 0;
		}
		//Check to max right position
		if (cX2 > imgProp.width) {
			cX2 = imgProp.width;
		}
		//Chek to max bottom position
		if (cY2 > imgProp.height) {
			cY2 = imgProp.height;
		}

		//Chek to min drag
		if ( (cX2-cX1 < minDragStart) || (cY2-cY1 < minDragStart) ) return;

		X1 = cX1;
		X2 = cX2;
		Y1 = cY1;
		Y2 = cY2;

		//Redraw the crop area
		updateCrop();
	}

	/*
		Move the croper layer
	*/
	function moveCroper (evt) {
		var
			cX1 = evt.pageX - imgProp.left - xOffset, //Current X1 coordinate
			cY1 = evt.pageY - imgProp.top - yOffset, //Current Y1 coordinate
			cHeight = Y2-Y1, //Current crop area height
			cWidth = X2-X1; //Current crop area width

		//Chek to max top position
		if (cY1 < 0) {
			cY1 = 0;
		}
		//Chek to max bottom position
		if (cY1 + cHeight > imgProp.height) {
			cY1 = imgProp.height - cHeight;
		}
		//Chek to max left position
		if (cX1 < 0) {
			cX1 = 0;
		}
		//Check to max right position
		if (cX1 + cWidth > imgProp.width) {
			cX1 = imgProp.width - cWidth;
		}

		//Update the curent position
		Y1 = cY1;
		Y2 = cY1 + cHeight;
		X1 = cX1;
		X2 = cX1 + cWidth;

		//Redraw the crop area
		updateCrop();
	}

	/*
		Draw the croper layer
	*/
	function updateCrop() {//	0.458ms	0.484ms	0.47ms
		/*
			Adjust the crop area properties
		*/
			
		if (!cropVisible) {
			cropEl.style.display = '';
			cropVisible = true;
		}

		with (cropAreaEl.style) {
			top = Y1 + 'px';
			left = X1 + 'px';
			width = (X2 - X1) + 'px';
			height = (Y2 - Y1) + 'px';
		}

		if (overlayShow) {
			//Set the overlay position of crop area
			cropOverlay[0].style.height = Y1 + 'px';
			with (cropOverlay[1].style) {
				top = Y1 + 'px';
				left = X2 + 'px';
				width = (imgProp.width - X2) + 'px';
				height = (Y2 - Y1) + 'px';
			}
			with (cropOverlay[2].style) {
				top = Y2 + 'px';
				height = (imgProp.height - Y2) + 'px';
			}
			with (cropOverlay[3].style) {
				top = Y1 + 'px';
				width = X1 + 'px';
				height = (Y2 - Y1) + 'px';
			}
		}

		if (isIE) {
			toggle(cropAreaEl);
			toggle(cropAreaEl);
		}

		if (onCrop) {
			onCrop(imageCropper.getCrop());
		}
	}

	/*
		Start the mouse drag when the mousedown
	*/
	function startDrag (evt) {
		if (cropVisible) {
			//Fix event properties
			evt = fixEvent(evt);
			var cX1 = imgProp.left + X1, cY1 = imgProp.top + Y1, cX2 = imgProp.left + X2, cY2 = imgProp.top + Y2;
			holdSide = false;
			if ( (evt.pageY < cY1 - cornerSize) || (evt.pageX < cX1 - cornerSize) || (evt.pageX > cX2 + cornerSize) || (evt.pageY > cY2 + cornerSize) ) {
			//Mouse out of crop area -> new crop area
				dragObj = $(imageId);
				showCropArea(evt);
				cancelEvent(evt);
			} else if ( (evt.pageY < cY1 + cornerSize) || (evt.pageX < cX1 + cornerSize) || (evt.pageX > cX2 - cornerSize) || (evt.pageY > cY2 - cornerSize) ) {
				//Mouse on borders -> resize the crop area
				//Top or bottom border -> reverse the startY
				startX = min(cX1, cX2);
				startY = min(cY1, cY2);
				startX = (evt.pageX - cornerSize > startX) ? min(cX1, cX2) : max(cX1, cX2);
				startY = (evt.pageY - cornerSize > startY) ? min(cY1, cY2) : max(cY1, cY2);
				//Center of borders
				if ( ( (evt.pageY < cY1 + cornerSize) || (evt.pageY > cY2 - cornerSize) ) && ( (evt.pageX > cX1 + cornerSize) && (evt.pageX < cX2 - cornerSize) ) ) {
					holdSide = 'w';
				} else if ( ( (evt.pageX < cX1 + cornerSize) || (evt.pageX > cX2 - cornerSize) ) && ( (evt.pageY > cY1 + cornerSize) && (evt.pageY < cY2 - cornerSize) ) ) {
					holdSide = 'h';
				}
				dragObj = $(imageId);
			} else {
				//Mouse on crop area -> move the crop area
				dragObj = cropAreaEl;
				yOffset = evt.pageY - (imgProp.top + Y1);
				xOffset = evt.pageX - (imgProp.left + X1);
			}
			cancelEvent(evt);
		} else {
			dragObj = $(imageId);
			showCropArea(evt);
			cancelEvent(evt);
		}
	}

	/*
		Start the mouse drag when the mousedown
	*/
	function endDrag (evt) {
		if (dragObj && onCropEnd) {
			onCropEnd(imageCropper.getCrop());
		}
		dragObj = null;
	}

	/*
		Process the mouse drag
	*/
	function onDrag (evt) {
		//Fix event properties
		evt = fixEvent(evt);

		var cropX1 = imgProp.left + X1, cropY1 = imgProp.top + Y1, cropX2 = imgProp.left + X2, cropY2 = imgProp.top + Y2;

		if (!dragObj && cropVisible){
			//Change the cursor in corners and borders
			//Set size of corners area
			var targetCursor;
			if (evt.pageY < cropY1+cornerSize) {
				//Top
				targetCursor = (evt.pageX < cropX1+cornerSize) ? 'nw' : ( (evt.pageX > cropX2-cornerSize) ? 'ne' : 'n');
			} else if (evt.pageY > cropY2-cornerSize) {
				//Bottom
				targetCursor = (evt.pageX < cropX1+cornerSize) ? 'sw' : ( (evt.pageX > cropX2-cornerSize) ? 'se' : 's');
			} else if (evt.pageX < cropX1+cornerSize) {
				//Left border
				targetCursor = 'w';
			} else if (evt.pageX > cropX2-cornerSize) {
				//Right border
				targetCursor = 'e';
			}

			$(imageId).style.cursor = cropEl.style.cursor = (targetCursor) ? targetCursor + '-resize' : 'move';

			//Opera cursor bugfix
			if (isOpera) {
				toggle(imageId + croperSufix + operaElSufix);
			}
		}

		if (dragObj) {
			if (dragObj.id == imageId) {
				adjCroper(evt);
				cancelEvent(evt);
			} else if (dragObj.id == cropAreaEl.id) {
				moveCroper(evt);
				cancelEvent(evt);
			}
		}
	}

	/*
		Adjust position of size pointer alfer window resize
	*/
	function onWindowResize(evt) {
		var imgPos = getElPos(imageId);
		imgProp.top = imgPos.top;
		imgProp.left = imgPos.left;
		imgProp.width = imgPos.width;
		imgProp.height = imgPos.height;
		if (!cropEl) return;
		with (cropEl.style) {
			top = imgProp.top + 'px';
			left = imgProp.left + 'px';
			width = imgProp.width + 'px';
			height = imgProp.height + 'px';
		}
		imageCropper.hide();
	}

	/*
		Set the settings
	*/
	function setSettings(settings) {
/*
	settings = {
		overlay: {show: true, backgroundColor: '#000', opacity: 50}, //Image overlay settings
		cropArea: {borderWidth: 1, borderStyle: 'dashed', borderColor: '#fff', backgroundColor: '#fff', borderOpacity: 100}, //Image crop area settings, set the borderWidth to 0 to disable it
		dragSquare: {show: true, background: '#81bee7', borderColor: 'blue', borderWidth: 1, opacity: 75}, //Drag square settings
		cropAreaGrid: {show: true, width: 1, style: 'solid', color: '#ddd', opacity: 30}, //Grid in crop area settings
		minDragStart: 0, //Minimal drag to start crop in pixels
		cornerSize: 10, //Size of the active border to resize the crop area
		constProportions: false, //Constrain proportions of crop area
		onCrop: function(){} //function get the crop position object {X1, Y1, X2, Y2} on the changing of crop
		onCropEnd: function(){} //function get the crop position object {X1, Y1, X2, Y2} alfer the crop end
	}
*/
		settings = settings || {};
		settings.overlay = settings.overlay || {};
		settings.cropArea = settings.cropArea || {};
		settings.dragSquare = settings.dragSquare || {};
		settings.cropAreaGrid = settings.cropAreaGrid || {};
		with (settings) {
		//Crop overlay settings
			overlayShow = (typeof overlay.show != 'undefined') ? overlay.show : true;
			overlayBackgroundColor = overlay.backgroundColor || '#000';
			overlayOpacity = overlay.opacity || 50;
		//Crop area settings
			cropAreaBorderWidth = cropArea.borderWidth || 1;
			cropAreaBorderStyle = cropArea.borderStyle || 'dashed';
			cropAreaBorderColor = cropArea.borderColor || '#fff';
			cropAreaBorderBackground = cropArea.borderBackground || '#000';
			cropAreaBorderOpacity = cropArea.borderOpacity || 100;
		//Drag square settings
			dragSquareShow = (typeof dragSquare.show != 'undefined') ? dragSquare.show : true;
			dragSquareBackground = dragSquare.background || '#81bee7';
			dragSquareBorderColor = dragSquare.borderColor || 'blue';
			dragSquareBorderWidth = dragSquare.borderWidth || 1;
			dragSquareOpacity = dragSquare.opacity || 75;
		//Croop area grid settings
			cropAreaGridShow = (typeof cropAreaGrid.show != 'undefined') ? cropAreaGrid.show : true;
			cropAreaGridWidth = cropAreaGrid.width || 1;
			cropAreaGridStyle = cropAreaGrid.style || 'solid';
			cropAreaGridColor = cropAreaGrid.color || '#ddd';
			cropAreaGridOpacity = cropAreaGrid.opacity || 30;
		}
		minDragStart = settings.minDragStart || 0; //Minimal drag to start crop in pixels
		cornerSize = settings.cornerSize || 10; //Size of the active border to resize the crop area
		constProportions = settings.constProportions || false; //Constrain proportions of crop area
		onCrop = settings.onCrop || onCrop;
		onCropEnd = settings.onCropEnd || onCropEnd;
	}

	/*
		Load image to crop from image element
	*/
	function loadImage () {
		var preloadImg = new Image();
		preloadImg.onload = function() {
			imgProp.realWidth = preloadImg.width;
			imgProp.realHeight = preloadImg.height;
			preloadImg.onload = function() {};
		};
		preloadImg.src = $(imageId).src;
	}

	/*
		Set crop to zero
	*/
	function resetCrop () {
			X1 = Y1 = X2 = Y2 = 0;
			if (onCrop) {
				onCrop(imageCropper.getCrop());
			}
			if (onCropEnd) {
				onCropEnd(imageCropper.getCrop());
			}
	}

	return {
	/*
		Initialise the object and set the parametrs
	*/
		init: function (id, settings) {
			/*
				Cheking
			*/
			if ( (!id) || !$(id) ) return;

			/*
				Apply settings
			*/
			imageId = id;
			setSettings(settings);
			/*
				Attach Event
			*/
			addEvent($(imageId), 'mousedown', startDrag);
			addEvent(document, 'mouseup', endDrag);
			addEvent(window, 'resize', onWindowResize);
			var imgPos = getElPos(imageId);
			imgProp = {top: imgPos.top, left: imgPos.left, width: imgPos.width, height: imgPos.height, src: $(imageId).src};

			//Get real image width and height
			loadImage();

			//Disable image toolbar for Internet Explorer
			if (isIE) {
				$(imageId).setAttribute('galleryimg', 'no');
			}
			//Set the cursor to graphic selection
			$(imageId).style.cursor = 'crosshair';

			// Set object to ready
			ready = true
		},

		/*
			Return object {X1, Y1, X2, Y2} with image crop area size
		*/
		getCrop: function() {
			if (!ready) return false;
			if (cropVisible) {
				var scaleX = imgProp.realWidth/imgProp.width, scaleY = imgProp.realHeight/imgProp.height;
				return {X1: round(X1*scaleX), Y1: round(Y1*scaleY), X2: round(X2*scaleX), Y2: round(Y2*scaleY)};
			}
			return {X1: 0, Y1: 0, X2: 0, Y2: 0};
		},

		/*
			Reset the position of crop layer
		*/
		resetPos: function() {
			if (!ready) return false;
			return onWindowResize();
		},

		/*
			Reload the image
		*/
		reloadImage: function() {
			if (!ready) return false;
			return loadImage();
		},

		/*
			Reset the position of crop layer
		*/
		hide: function() {
			if (!ready) return false;
			cropVisible = false;
			resetCrop();
			$(imageId).style.cursor = 'crosshair';
			return (cropEl) ? cropEl.style.display = 'none' : true;
		}

	}
}());

/************************ Rotate/Flip suport **********************/

/*
	Image rotate & flip script
	Ver 0.011
	dev@imglib.endofinternet.net
	For Microsoft Internet Explorer the <xml:namespace ns="urn:schemas-microsoft-com:vml" prefix="v" /> must be placed alfer <body> tag
	and add this to style table "v\:image { behavior:url(#default#VML); display:inline-block;}"
	Tested IE 5
*/
var rotateFlipImage = (function () {
	var
		canvas_support = (function () {
			//Check the canvas suport for Mozila/Opera/Safari
			var result = false;
			try {
				result = !!(document.createElement('canvas').getContexts('2d'));
			} catch(e) {
				try {
					result = !!(document.createElement('canvas').getContext);
				}catch(e) {}
			}
			return result;
		}()),
		isIE = (navigator.userAgent.toLowerCase().indexOf('msie') != -1) ? true : false, //Chek for Internet Explorer
		ready = false, // If the object is ready
		imageEl, // Image element
		imageSrc, // Image src property
		imageRes, // Target image resourse
		imageWidth,
		imageHeight,
		imageScaleX,
		imageScaleY,
		canvas = null, //Canvas element
		canvasContext, // canvas.getContext
		canvasFlipType = -1, // Curent canvas flip type
		canvasAngle = 0, // Curent canvas angle, sets on external call
		//Server side config
		previewURL,
		previewImageURLArg, // Preview image URL argument name
		previewRotateArg, // Preview rotate argument name
		previewFlipArg, // Preview rotate argument name
		// Bind function
		onEnd = null,
		onRotate = null,
		onFlip = null,
		// Short access for Math fuction
		sin = Math.sin,
		cos = Math.cos
		;
		canvas_support = (isIE) ? true : canvas_support; // IE > 5 support VML, so can be used

		//canvas_support = false;

	/*
		Preload the image
			src - image src property
			onLoad - onload event function
	*/
	function preloadImage(src, onLoad) {
		if (!src) return;
		preloadImg = new Image();
		if (typeof onLoad == 'function') {
			preloadImg.onload = function() {onLoad(preloadImg);};
		}
		preloadImg.src = src;
	}

	/*
		Create canvas element
	*/
	function createCanvas() {
		if (isIE) {
			canvas = document.createElement('v:image');
			//canvas.id = 'canvas';
			canvas.src = imageSrc;
			with (canvas.style) {
				//position = 'relative';
				//top = '100px';
				//left = '100px';
				//width = '300px';
				//height = '225px';
				//border = '1px solid red';
			}
		} else {
			canvas = document.createElement('canvas');
			//canvas.id = 'canvas';
			//canvas.style.border = '1px solid red';
			canvasContext = canvas.getContext('2d');
		}
		//Replace the image element
		imageEl.parentNode.insertBefore(canvas, imageEl.nextSibling);
		imageEl.style.display = 'none';
		return true;
	}

	/*
		Flip canvas element
	*/
	function flipCanvas(type) {
		canvasFlipType = type; // Save flip type
		if (isIE) {
			canvas.style.flip = ((type == 0) ? 'x' : ((type == 1) ? 'y' : ((type == 2) ? 'x y' : '')));
			// If "canvas" is rotated then need recalculate the angle
			rotateImage(canvasAngle);
		} else {
			if (type == -1) return true; // Do notfing if no need flip
			var canvasTranslateWidth = 0, canvasTranslateHeight = 0, canvasScaleX = 1, canvasScaleY = 1;
			if (type != 0) { // Flip vertical
				canvasTranslateHeight = imageHeight - 1;
				canvasScaleY = -1;
			}
			if (type != 1) { // Flip horisontal
				canvasTranslateWidth = imageWidth - 1;
				canvasScaleX = -1;
			}
			canvasContext.translate(canvasTranslateWidth, canvasTranslateHeight);
			canvasContext.scale(canvasScaleX, canvasScaleY);
		}
		return true;
	}

	/*
		Set canvas width & height
	*/
	function setCanvasSize(width, height) {
		canvas.style.width = width+'px';
		canvas.style.height = height+'px';
		if (!isIE) {
			canvas.setAttribute('width', width);
			canvas.setAttribute('height', height)
		}
		return true;
	}

	/*
	Rotate the image to angle
*/
	rotateImage = (function () {
		if (canvas_support && !isIE) {// Firefox 2, Safari 3, Opera 9.5+
			return function (angle) {
				if (!canvas) {
					createCanvas();
				}
				// Normalize the anle
				angle = parseInt(angle);
				angle = isNaN(angle) ? 0 : angle;
				angle %= 360;
				angle = (angle < 0) ? angle + 360 : angle;
				//Convert angle to radians
				angle = angle * Math.PI / 180;

				//Calculate the canvas width and height
				var
					iWidthSin = imageWidth * imageScaleX * sin(angle),
					iWidthCos = imageWidth * imageScaleX * cos(angle),
					iHeightSin = imageHeight * imageScaleY * sin(angle),
					iHeightCos = imageHeight * imageScaleY * cos(angle),
					
					cWidth = ((iWidthCos < 0) ? -iWidthCos : iWidthCos) + ((iHeightSin < 0) ? -iHeightSin : iHeightSin),
					cHeight = ((iWidthSin < 0) ? -iWidthSin : iWidthSin) + ((iHeightCos < 0) ? -iHeightCos : iHeightCos)
					;

				//Set canvas width and height
				setCanvasSize(cWidth, cHeight);

				// The steps is reverse so real way - drawImage->flipCanvas->translate->scale->rotate->translate
				canvasContext.save();
				canvasContext.translate(cWidth/2, cHeight/2); // move to center image on screen
				canvasContext.rotate(angle); // rotate image
				canvasContext.scale(imageScaleX, imageScaleY); //Scale image to destination size
				canvasContext.translate(-imageWidth/2, -imageHeight/2); // move image to its center
				flipCanvas(canvasFlipType); //Flip canvas if need
				canvasContext.drawImage(imageRes, 0, 0);
				canvasContext.restore();

				//Calback function
				if (onRotate) {onRotate(state());}
			}
		} else if (isIE) {// MSIE 6 & 7
			return function (angle) {
				if (!canvas) {
					createCanvas();
				}
				// Normalize the anle
				angle = parseInt(angle);
				angle = isNaN(angle) ? 0 : angle;
				angle %= 360;
				angle = (angle < 0) ? angle + 360 : angle;

				//Correct angle if flip
				if ( (canvasFlipType == 0) || (canvasFlipType == 1) ) angle = -angle;

				//Set canvas width and height
				setCanvasSize(imageWidth*imageScaleX, imageHeight*imageScaleY);

				//Apply the rotation
				canvas.style.rotation = angle;

				//Calback function
				if (onRotate) {onRotate(state());}
			}
		} else {
			return function (angle) {// Server side rotation
				// Normalize the anle
				angle = parseInt(angle);
				angle = isNaN(angle) ? 0 : angle;
				angle %= 360;
				angle = (angle < 0) ? angle + 360 : angle;
				var newSrc;
				if ( !( ( (angle == 0) && (canvasFlipType == -1) ) || ( (angle == 180) && (canvasFlipType == 2) ) ) ) {
					newSrc = previewURL + ((previewURL.indexOf('?') == -1) ? '?' : '&' ) + previewImageURLArg + '=' + encodeURIComponent(imageSrc) +'&' + previewRotateArg + '=' + angle +'&' + previewFlipArg + '=' + canvasFlipType;
				} else {
					newSrc = imageSrc;
				}
				preloadImage(newSrc, function () {
					imageEl.src = newSrc;
					//Calback function
					if (onRotate) {onRotate(state());}
				});
			}
		}
	}());

	/*
		Flip the image to type
			type - type of flip
			-1	-	reset flip
			0	-	horisontal
			1	-	vertical
			2	-	both
	*/
	flipImage = (function () {
		if (canvas_support && !isIE) {// Firefox 2, Safari 3, Opera 9.5+
			return function (type) {
				if (!canvas) {
					createCanvas();
				}
				// Normalize the type
				type = ( (type != -1) && (type != 1) && (type != 2) ) ? 0 : type;

				if (canvasAngle != 0) {
					// Canvas is rotaded
					canvasFlipType = type; // Save flip type
					rotateImage(canvasAngle);
				} else {
					// Canvas not rotaded, just flip

					//Set canvas width and height
					setCanvasSize(imageWidth*imageScaleX, imageHeight*imageScaleY);

					// The steps is reverse so real way - drawImage->flipCanvas->scale
					canvasContext.save();
					canvasContext.scale(imageScaleX, imageScaleY);
					flipCanvas(type);
					canvasContext.drawImage(imageRes, 0, 0);
					canvasContext.restore();
				}

				//Calback function
				if (onFlip) {onFlip(state());}
			}
		} else if (isIE) {// MSIE 6 & 7
			return function (type) {
				if (!canvas) {
					createCanvas();
				}
				// Normalize the type
				type = ( (type != -1) && (type != 1) && (type != 2) ) ? 0 : type;
				//Set canvas width and height
				setCanvasSize(imageWidth*imageScaleX, imageHeight*imageScaleY);
				// Apply flip
				flipCanvas(type);
				//Calback function
				if (onFlip) {onFlip(state());}
			}
		} else {// Server side flip
			return function (type) {
				// Normalize the type
				type = ( (type != -1) && (type != 1) && (type != 2) ) ? 0 : type;
				// Save flip type
				canvasFlipType = type;
				var newSrc;
				if ( !( ( (canvasAngle == 0) && (canvasFlipType == -1) ) || ( (canvasAngle == 180) && (canvasFlipType == 2) ) ) ) {
					newSrc = previewURL + ((previewURL.indexOf('?') == -1) ? '?' : '&' ) + previewImageURLArg + '=' + encodeURIComponent(imageSrc) +'&' + previewRotateArg + '=' + canvasAngle +'&' + previewFlipArg + '=' + canvasFlipType;
				} else {
					newSrc = imageSrc;
				}
				preloadImage(newSrc, function () {
					imageEl.src = newSrc;
					//Calback function
					if (onFlip) {onFlip(state());}
				});
			}
		}
	}());

	/*
		Set the settings
	*/
	function setSettings(settings) {
/*
	settings = {
		imageSrc: '', // Target image src if not equal to "src" property
		previewURL: 'index.php', // URL to preview image rotation, flip if canvas not suport
		previewImageURLArg: 'img', // Image argument from URL to preview image rotation, flip if canvas not suport
		previewRotateArg: 'r', // Image argument from URL to preview image rotation if canvas not suport
		previewFlipArg: 'f', // Image argument from URL to preview image flip if canvas not suport
		onRotate: function () {}, //function execute alfer end of rotate operation, the first function argument - the curent state of rotation and flip
		onFlip: function () {} //function execute alfer end of flip operation, the first function argument - the curent state of rotation and flip
	}
*/
		settings = settings || {};
		//Server side config
		imageSrc = settings.imageSrc || imageSrc;
		previewURL = settings.previewURL || 'index.php';
		previewImageURLArg = settings.previewImageURLArg || 'src';
		previewRotateArg = settings.previewRotateArg || 'rotate';
		previewFlipArg = settings.previewFlipArg || 'flip';
		// Bind function
		onRotate = settings.onRotate || onRotate;
		onFlip = settings.onFlip || onFlip;
	}

	/*
		Get curent state of rotation and fliping, return object {angle: deg, flip: type}, where
				angle - curent rotation angle
				flip - curent flip type
					type of flip
						-1	-	reset flip
						0	-	horisontal
						1	-	vertical
						2	-	both
	*/
	function state () {
		return {angle: canvasAngle, flip: canvasFlipType};
	}

	return {
		/*
			Initialise the object and set the parametrs
		*/
		init: function(id, settings) {
		/*
			Checks
		*/
			if ( (!id) || !$(id) ) return;

			/*
				Apply settings
			*/
			imageEl = $(id);
			setSettings(settings);

			// Save the original image src or use from settings
			imageSrc = imageSrc || imageEl.getAttribute('src');
			if (canvas_support) {
				// Preload imge
				preloadImage(imageSrc, function () {
					if (imageRes.width > 0) {
						ready = true;
					}
					imageWidth = imageRes.width;
					imageHeight = imageRes.height;
					if (imageEl.offsetWidth > 0) {
						imageScaleX = imageEl.offsetWidth/imageWidth;
						imageScaleY = imageEl.offsetHeight/imageHeight;
					} else {
						imageScaleX = imageScaleY = 1;
					}
					imageRes.onload = function () {};
				});

				imageRes = new Image();
				imageRes.onload = function () {
					if (imageRes.width > 0) {
						ready = true;
					}
					imageWidth = imageRes.width;
					imageHeight = imageRes.height;
					//var prevDisplay = imageEl.style.display;
					//imageEl.style.display = 'block';
					if (imageEl.offsetWidth > 0) {
						imageScaleX = imageEl.offsetWidth/imageWidth;
						imageScaleY = imageEl.offsetHeight/imageHeight;
					} else {
						imageScaleX = imageScaleY = 1;
					}
					//imageEl.style.display = prevDisplay;
					imageRes.onload = function () {};
				};
				imageRes.src = imageSrc;
			} else {
				ready = true;
			}

		},

		/*
			Rotate the image
		*/
		rotate: function(angle) {
			if (ready) {
				//Save the angle
				canvasAngle = angle;
				return rotateImage(angle);
			}
			return false;
		},

		/*
			Flip the image
		*/
		flip: function(type) {
			if (ready) {
				return flipImage(type);
			}
			return false;
		},

		/*
			Get state of rotation and fliping, return object {angle: deg, flip: type}, where
				angle - curent rotation angle
				flip - curent flip type
					type of flip
						-1	-	reset flip
						0	-	horisontal
						1	-	vertical
						2	-	both
		*/
		getState: function() {
			if (ready) {
				return state();
			}
			return false;
		},

		/*
			Reset the angle and flip type
		*/
		reset: function() {
			if (ready) {
				canvasFlipType = -1;
				canvasAngle = 0;
				rotateImage(0);
				return true;
			}
			return false;
		}
	}
}());