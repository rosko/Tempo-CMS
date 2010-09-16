/********************************************************************
 * imgLib v0.1.1 03.02.2010
 * Contact me at dev@imglib.endofinternet.net
 * Site: http://www.imglib.endofinternet.net/
 * This copyright notice MUST stay intact for use.
 ********************************************************************/

/*
	Main function to suport
*/

if (!window.$) {
	// No jQuery, prototype ....
	function $(id) {
		if (typeof id == 'string') {
			return document.getElementById(id);
		}
		return id;
	}
}

/* ----------------------- Control events functions --------------------- */
var
	/*
		Attach function "fn" on event "evt" to Object "obj"
	*/
	addEvent = (function () {
		if (document.attachEvent) {
			return function (obj, evt, fn) {
				obj.attachEvent('on' + evt, fn);
			};
		} else if (document.addEventListener) {
			return function (obj, evt, fn) {
				obj.addEventListener(evt, fn, false);
			};
		} else {
			return function (obj, evt, fn) {
				obj['on' + evt] = fn;
			};
		}
	})(),
	/*
		Cancel event 'evt'.
	*/
	cancelEvent = (function () {
		if (document.attachEvent) {
			return function (evt) {
				if (!evt) {
					return false;
				}
				evt.returnValue = false;
				evt.cancelBubble = true;
			};
		} else if (document.addEventListener) {
			return function (evt) {
				if (!evt) {
					return false;
				}
				evt.preventDefault();
				evt.stopPropagation();
			};
		} else {
			return function () {
				return false;
			};
		}
	})()
;
/*
	Fix event properties
*/
function fixEvent(evt) {
	evt = evt || window.event;
	if (evt.isFixed) {
		return evt;
	}
	evt.isFixed = true;
	// Add pageX/pageY for IE
	if (evt.pageX == null && evt.clientX != null) {
		var
			html = document.documentElement,
			body = document.body
		;
		evt.pageX = evt.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0);
		evt.pageY = evt.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0);
	}
	if (!evt.target && evt.srcElement) {
		evt.target = evt.srcElement;
	}
	if (!evt.which && evt.button) {
		evt.which = evt.button & 1 ? 1 : (evt.button & 2 ? 3 : (evt.button & 4 ? 2 : 0));
	}
	return evt;
}


/* -------------------------- Text functions ---------------------------- */
/*
	Get URIComponent encoded path to file to safetly open in browser
	@path - the path to file
	return the encodeURIComponent encoded path
*/
function getURIEncPath(path) {
	var
		proto = '',
		host_port = '',
		i,
		len
	;
	if ((path.indexOf('http://') != -1) || (path.indexOf('https://') != -1)) {
		proto = path.substring(0, path.indexOf('://') + 3);
		path = path.substring(proto.length, path.length);
	}
	if ((proto.length > 0) && (path.indexOf(':') != -1) && (path.indexOf(':') < path.length)) {
		len = path.indexOf('/', path.indexOf(':') + 1);
		len = (len == -1) ? path.length : len;
		host_port = path.substring(0, len);
		path = path.substring(host_port.length, path.length);
	}
	path = path.split('/');
	for (i = 0, len = path.length; i < len; i++) {
		path[i] = encodeURIComponent(path[i]);
	}
	return proto + host_port + path.join('/');
}
/*
	Decode the html special char simple &amp; -> &
	@text - a HTML encoded string
*/
function HTMLDecode(text) {
	if (!text) {
		return '';
	}
	var b = document.createElement('b');
	b.innerHTML = text;
	return b.firstChild.data;
}
/*
	Get window arguments and hash as object
*/
function getURLArg() {
	var
		search_array = window.location.search.substring(1).split('&'),
		hash_array = window.location.hash.substring(1).split('&'),
		params = {search: {}, hash: {}},
		tmp,
		i
	;
	for (i = search_array.length; i-- > 0;) {
		tmp = search_array[i].split('=');
		params.search[tmp[0]] = params[tmp[0]] = tmp[1] || null;
	}
	for (i = hash_array.length; i-- > 0;) {
		tmp = hash_array[i].split('=');
		params.hash[tmp[0]] =params[tmp[0]] = tmp[1] || null;
	}
	return params;
}


/*----------------- Dimensions functions --------------------------------*/
/*
	Get information about window sizes and scrols
*/
function getWindowGeometry() {
	var
		width,
		height,
		xOffset,
		yOffset,
		win = window,
		doc = document;
	if (win.innerWidth) { // Not IE
		width = win.innerWidth;
		height = win.innerHeight;
		xOffset = win.pageXOffset;
		yOffset = win.pageYOffset;
	}
	else if (doc.documentElement && doc.documentElement.clientWidth) { // IE 6 and DOCTYPE
		width = doc.documentElement.clientWidth;
		height = doc.documentElement.clientHeight;
		xOffset = doc.documentElement.scrollLeft;
		yOffset = doc.documentElement.scrollTop;
	} else if (doc.body.clientWidth) { // IE4, IE5 и IE6 without DOCTYPE
		width = doc.body.clientWidth;
		height = doc.body.clientHeight;
		xOffset = doc.body.scrollLeft;
		yOffset = doc.body.scrollTop;
	}
	return {width: width, height: height, xOffset: xOffset, yOffset: yOffset};
}
/*
	Get window width and height
*/
function getWindowSize() {
	var wSize = getWindowGeometry();
	return {width: wSize.width, height: wSize.height};
}
/*
	Get information about the el sizes and position
*/
function getElPos(el) {
	if (typeof el == 'string') {
		el = $(el);
	}
	var
		top = 0,
		left = 0,
		width = el.offsetWidth,
		height = el.offsetHeight
	;
	while (el) {
		top += el.offsetTop;
		left += el.offsetLeft;
		el = el.offsetParent;
	}
	return {top: top, left: left, width: width, height: height};
}


/* -------------------- AJAX suport functions ------------------------- */
/*
	Return created XMLHttpRequest object
*/
function createXMLHttpRequestObject() {
	var
		xmlHttp,
		i,
		len
	;
	try {
		xmlHttp = new XMLHttpRequest();
		createXMLHttpRequestObject = function () {
			return new XMLHttpRequest();
		};
	} catch (e) {
		XmlHttpVersions = ['MSXML2.XMLHTTP.6.0', 'MSXML2.XMLHTTP.5.0', 'MSXML2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'Msxml2.XMLHTTP', 'Microsoft.XMLHTTP'];
		for (i = 0, len = XmlHttpVersions.length; i < len && !xmlHttp; i++) {
			try {
				xmlHttp = new ActiveXObject(XmlHttpVersions[i]);
				createXMLHttpRequestObject = function () {
					return new ActiveXObject(XmlHttpVersions[i]);
				};
			} catch (e) {
				//alert('0can't create object,' + e.toString());
			}
		}
	}
	if (!xmlHttp) {
		alert('can\'t create XMLHttpRequest object');
	} else {
		return xmlHttp;
	}
}
/*
	Send the XMLHttpRequest
	url: target url to request
	opt: object with extra options
		opt.mode: request mode (default "GET")
		opt.async: Determines whether XMLHttpRequest is asynchronously or no (default true)
		opt.contentType: The Content-Type header for your request. (default "application/x-www-form-urlencoded")
		opt.encoding: Encoding of request (default "UTF-8")
		opt.parameters: Content of POST body(default "")
		opt.onsuccess: handle to functions run on request successfuly complete (default null, example handleFunct(req), where req - XMLHttpRequest Object)
	return true if ok
*/
function sendXMLHttpReq(url, opt) {
	if (!url) {
		return;
	}
	// Set default parametrs
	var
		options = {
			mode: 'GET',
			async: true,
			contentType: 'application/x-www-form-urlencoded',
			encoding: 'UTF-8',
			parameters: '',
			onsuccess: null
		},
		req = createXMLHttpRequestObject(),
		name
	;
	if (typeof opt == 'object') {
		for (name in opt) {
			options[name] = opt[name];
		}
	}
	options.parameters = (options.parameters) ? options.parameters : ((options.mode == 'POST') ? '' : null);
	try {
		req.open(options.mode, url, options.async);
		if (options.mode == 'POST') {
			req.setRequestHeader('Content-Type', options.contentType + (options.encoding ? '; charset=' + options.encoding : ''));
			req.setRequestHeader('Content-length', options.parameters.length);
			req.setRequestHeader('Connection', 'close');
		}
		if (typeof options.onsuccess == 'function') {
			req.onreadystatechange = function () {
				if (req.readyState == 4) {
					if (req.status == 200) {
						try {
							options.onsuccess(req);
						} catch (e) {
							alert('2can\'t process ajax,' + e.toString());
						}
					} else {
						alert('3can\'t process ajax,' + req.statusText);
					}
				}
			};
		}
		req.send(options.parameters);
		return true;
	} catch (e) {
		alert('1can\'t process ajax,' + e.toString());
	}
}


/* -------------------- Cookie functions--------------------------------- */
function getCookie(name) {
	var
		dc = document.cookie,
		prefix = name + '=',
		begin = dc.indexOf('; ' + prefix),
		end
	;
	if (begin == -1) {
		begin = dc.indexOf(prefix);
		if (begin != 0) {
			return null;
		}
	} else {
		begin += 2;
	}
	end = document.cookie.indexOf(';', begin);
	if (end == -1) {
		end = dc.length;
	}
	return unescape(dc.substring(begin + prefix.length, end));
}

function setCookie(name, value, time, path) {
	var
		date,
		expires
	;
	if (time) {
		date = new Date();
		date.setTime(date.getTime() + (time * 1000));
		expires = '; expires=' + date.toGMTString();
	} else {
		expires = '';
	}
	if (path) {
		document.cookie = name + '=' + value + expires + '; path=' + path;
	} else {
		document.cookie = name + '=' + value + expires + '; path=/';
	}
}


/* ------------------ Style, class, Objects, DOM functions --------------- */
/*
	Function change transparency property of element "el" or element with id="el"
	val - transparency level, range 0-100
*/
function setTransparency(el, val) {
	if (typeof el == 'string') {
		el = $(el);
	}
	if (typeof el != 'object') {
		return;
	}
	val = parseInt(val);
	if (val < 1) {
		val = val * 100;
	}
	if (el.filters) {
		if (val < 100) {
			el.style.filter = 'alpha(opacity = ' + val + ')';
		} else {
			el.style.filter = '';
		}
	} else {
		el.style.opacity = (val / 100);
	}
	return true;
}
/*
	Get all attributes of node and return associative array
*/
function getAttributes(node) {
	var
		attrs = {},
		i,
		attr
	;
	if (node.attributes) {
		for (i = node.attributes.length; i-- > 0;) {
			attr = node.attributes[i];
			attrs[attr.nodeName] = attr.nodeValue;
		}
	}
	return attrs;
}
/*
	Extend obj from add object and return extended object
*/
function extend(obj, add) {
	for (var name in add) {
		if (obj[name] == add[name]) {
			continue;
		}
		obj[name] = add[name];
	}
	return obj;
}
/*
	Function change the style.display value of element to togle visibility
	el - element or element id
*/
function toggle(el) {
	el = $(el);
	el.style.display = (el.style.display != 'none') ? 'none' : '';
}

/* ----------------------- Formating values ----------------------------- */
var fileSizeName = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Eb'];
/*
	Get formated date miliseconds number in format
	f=0, or not set: yyyy/dd/mm H:M:S
	f=1: yyyy/mm/dd H:M:S
*/
function getDateF(msec, f) {
	msec = (msec) ? new Date(msec) : new Date();
	var
		dateEl = [
			msec.getFullYear(),
			msec.getDate(),
			msec.getMonth() + 1,
			msec.getHours(),
			msec.getMinutes(),
			msec.getSeconds()
		],
		i,
		len
	;
	if (f > 0) {
		f = dateEl[1];
		dateEl[1] = dateEl[2];
		dateEl[2] = f;
	}
	//Add first '0' to dates
	for (i = 0, len = dateEl.length; i < len; i++) {
		dateEl[i] = (dateEl[i].toString().length < 2) ? '0' + dateEl[i].toString() : dateEl[i];
	}
	return [dateEl[0], '/', dateEl[1], '/', dateEl[2], ' ', dateEl[3], ':', dateEl[4], ':', dateEl[5]].join('');
}
/*
	Get float size like 12.5Mb from size in bytes
	@size - the size in bytes
	return string containt size in float format
*/
function getFloatSize(size) {
	var nameIndex = 0;
	size = parseInt(size);
	while (size >= 1024) {
		size /= 1024;
		nameIndex++;
	}
	return size.toFixed(((nameIndex == 0) ? 0 : 2)) + ' ' + fileSizeName[nameIndex];
}


/* ----------------------- Translation ---------------------------------- */
/*
	Translate the labels on page
	@labels - labels to translate
		labels = [
			{
				id: 'elm_id', // Element id to change
				prop: { // Object vith properties to change or some text if need change only innerHTML
					innerHTML: 'Inner HTML text',
					value: 'New buttons value text',
					title: 'New element title'
				}
			}
		]
*/
function translateLabels(labels) {
	if (typeof labels == 'object') {
		var
			i,
			label_el,
			prop
		;
		for (i = labels.length; i-- > 0;) {
			if ($(labels[i].id)) {
				label_el = $(labels[i].id);
				if (typeof labels[i].prop == 'object') {
					for (prop in labels[i].prop) {
						label_el[prop] = HTMLDecode(labels[i].prop[prop]);
					}
				} else if (typeof labels[i].prop == 'string') {
					label_el.innerHTML = HTMLDecode(labels[i].prop);
				}
			}
		}
	}
}