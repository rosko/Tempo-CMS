/*
 * FancyBox - jQuery Plugin
 * simple and fancy lightbox alternative
 *
 * Copyright (c) 2009 Janis Skarnelis
 * Examples and documentation at: http://fancybox.net
 * 
 * Version: 1.2.6 (16/11/2009)
 * Requires: jQuery v1.3+
 * 
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

;(function($) {
	$.fn.fixPNG = function() {
		return this.each(function () {
			var image = $(this).css('backgroundImage');

			if (image.match(/^url\(["']?(.*\.png)["']?\)$/i)) {
				image = RegExp.$1;
				$(this).css({
					'backgroundImage': 'none',
					'filter': "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=" + ($(this).css('backgroundRepeat') == 'no-repeat' ? 'crop' : 'scale') + ", src='" + image + "')"
				}).each(function () {
					var position = $(this).css('position');
					if (position != 'absolute' && position != 'relative')
						$(this).css('position', 'relative');
				});
			}
		});
	};

	var elem, opts, busy = false, imagePreloader = new Image, loadingTimer, loadingFrame = 1, imageRegExp = /\.(jpg|gif|png|bmp|jpeg)(.*)?$/i;
	var ieQuirks = null, IE6 = $.browser.msie && $.browser.version.substr(0,1) == 6 && !window.XMLHttpRequest, oldIE = IE6 || ($.browser.msie && $.browser.version.substr(0,1) == 7);

	$.fn.fancybox = function(o, i) {
		var settings		= $.extend({}, $.fn.fancybox.defaults, o);
		var matchedGroup	= this;
		
		if (!i) {
			var i = $('body');
		}
		var contener		= $(i);

		function _initialize() {
			elem = this;
			opts = $.extend({}, settings);

			_build();
			_start();

			return false;
		};
		
		function _build() {
			var html = '', zindex = 99;
			
			var fglobals = $('div.fancy_global');
			for (var i=0; i < fglobals.length; i++) {
				if ($(fglobals[i]).css('z-index') > zindex) {
					zindex = $(fglobals[i]).css('z-index');
				}
			}
			zindex++;
			
			html += '<div class="fancy_global" style="z-index: '+zindex+'">';

			html += '<div class="fancy_overlay" style="z-index: '+zindex+'"></div>';
			html += '<div class="fancy_loading" style="z-index: '+zindex+'"><div></div></div>';

			html += '<div class="fancy_outer" style="z-index: '+zindex+'">';
			html += '<div class="fancy_inner" style="z-index: '+zindex+'">';

			html += '<div class="fancy_close"></div>';

			html += '<div class="fancy_bg" style="z-index: '+zindex+'"><div class="fancy_bg fancy_bg_n"></div><div class="fancy_bg fancy_bg_ne"></div><div class="fancy_bg fancy_bg_e"></div><div class="fancy_bg fancy_bg_se"></div><div class="fancy_bg fancy_bg_s"></div><div class="fancy_bg fancy_bg_sw"></div><div class="fancy_bg fancy_bg_w"></div><div class="fancy_bg fancy_bg_nw"></div></div>';

			html += '<a href="javascript:;" class="fancy_left"><span class="fancy_ico fancy_left_ico"></span></a><a href="javascript:;" class="fancy_right"><span class="fancy_ico fancy_right_ico"></span></a>';

			html += '<div class="fancy_content" style="z-index: '+zindex+'"></div>';

			html += '</div>';
			html += '</div>';
			
			html += '<div class="fancy_title" style="z-index: '+zindex+'"></div>';
			
			html += '</div>';
			
			$(html).appendTo(contener);

			$('.fancy_title', contener).append('<table cellspacing="0" cellpadding="0" border="0"><tr><td class="fancy_title fancy_title_left"></td><td class="fancy_title fancy_title_main"><div></div></td><td class="fancy_title fancy_title_right"></td></tr></table>');

			if ($.browser.msie) {
				$(".fancy_bg").fixPNG();
			}

			if (IE6) {
				$("div.fancy_overlay", contener).css("position", "absolute");
				$(".fancy_loading div, .fancy_close, .fancy_title, .fancy_ico", contener).fixPNG();

				$(".fancy_inner", contener).prepend('<iframe class="fancy_bigIframe" src="javascript:false;" scrolling="no" frameborder="0"></iframe>');

				// Get rid of the 'false' text introduced by the URL of the iframe
				var frameDoc = $('.fancy_bigIframe', contener)[0].contentWindow.document;
				frameDoc.open();
				frameDoc.close();
				
			}
		};

		function _start() {
			if (busy) return;

			if ($.isFunction(opts.callbackOnStart)) {
				opts.callbackOnStart();
			}

			opts.itemArray		= [];
			opts.itemCurrent	= 0;

			if (settings.itemArray.length > 0) {
				opts.itemArray = settings.itemArray;

			} else {
				var item = {};

				if (!elem.rel || elem.rel == '') {
					var item = {href: elem.href, title: elem.title};

					if ($(elem).children("img:first").length) {
						item.orig = $(elem).children("img:first");
					} else {
						item.orig = $(elem);
					}

					if (item.title == '' || typeof item.title == 'undefined') {
						item.title = item.orig.attr('alt');
					}
					
					opts.itemArray.push( item );

				} else {
					var subGroup = $(matchedGroup).filter("a[rel=" + elem.rel + "]");
					var item = {};

					for (var i = 0; i < subGroup.length; i++) {
						item = {href: subGroup[i].href, title: subGroup[i].title};

						if ($(subGroup[i]).children("img:first").length) {
							item.orig = $(subGroup[i]).children("img:first");
						} else {
							item.orig = $(subGroup[i]);
						}

						if (item.title == '' || typeof item.title == 'undefined') {
							item.title = item.orig.attr('alt');
						}

						opts.itemArray.push( item );
					}
				}
			}

			while ( opts.itemArray[ opts.itemCurrent ].href != elem.href ) {
				opts.itemCurrent++;
			}

			if (opts.overlayShow) {
				if (IE6) {
					$('embed, object, select', contener).css('visibility', 'hidden');
					$(".fancy_overlay", contener).css('height', $(document).height());
				}

				$(".fancy_overlay", contener).css({
					'background-color'	: opts.overlayColor,
					'opacity'			: opts.overlayOpacity
				}).show();
			}
			
			$(window).bind("resize.fb scroll.fb", _scrollBox);

			_change_item();
		};
		
		function _scrollBox ()
		{
			var w = _getViewport();
			
			if (opts.centerOnScroll && $(".fancy_outer", contener).is(':visible')) {
				var ow	= $(".fancy_outer", contener).outerWidth();
				var oh	= $(".fancy_outer", contener).outerHeight();

				var pos	= {
					'top'	: (oh > w[1] ? w[3] : w[3] + Math.round((w[1] - oh) * 0.5)),
					'left'	: (ow > w[0] ? w[2] : w[2] + Math.round((w[0] - ow) * 0.5))
				};

				$(".fancy_outer", contener).css(pos);

				$('.fancy_title', contener).css({
					'top'	: pos.top	+ oh - 32,
					'left'	: pos.left	+ ((ow * 0.5) - ($('.fancy_title', contener).width() * 0.5))
				});
			}
			
			if (IE6 && $(".fancy_overlay", contener).is(':visible')) {
				$(".fancy_overlay", contener).css({
					'height' : $(document).height()
				});
			}
			
			if ($(".fancy_loading", contener).is(':visible')) {
				$(".fancy_loading", contener).css({'left': ((w[0] - 40) * 0.5 + w[2]), 'top': ((w[1] - 40) * 0.5 + w[3])});
			}			
		}

		function _change_item() {
			$(".fancy_right, .fancy_left, .fancy_close, .fancy_title", contener).hide();

			var href = opts.itemArray[ opts.itemCurrent ].href;

			if (href.match("iframe") || elem.className.indexOf("iframe") >= 0) {
				_showLoading();
				_set_content('<iframe class="fancy_frame" onload="jQuery.fn.fancybox.showIframe()" name="fancy_iframe' + Math.round(Math.random()*1000) + '" frameborder="0" hspace="0" src="' + href + '"></iframe>', opts.frameWidth, opts.frameHeight);

			} else if (href.match(/#/)) {
				var target = window.location.href.split('#')[0]; target = href.replace(target, ''); target = target.substr(target.indexOf('#'));

				_set_content('<div class="fancy_div">' + $(target).html() + '</div>', opts.frameWidth, opts.frameHeight);

			} else if (href.match(imageRegExp)) {
				imagePreloader = new Image; imagePreloader.src = href;

				if (imagePreloader.complete) {
					_proceed_image();

				} else {
					_showLoading();
					$(imagePreloader).unbind().bind('load', function() {
						$(".fancy_loading", contener).hide();

						_proceed_image();
					});
				}
			} else {
				_showLoading();
				$.get(href, function(data) {
					$(".fancy_loading", contener).hide();
					_set_content( '<div class="fancy_ajax">' + data + '</div>', opts.frameWidth, opts.frameHeight );
				});
			}
		};

		function _proceed_image() {
			var width	= imagePreloader.width;
			var height	= imagePreloader.height;

			var horizontal_space	= (opts.padding * 2) + 40;
			var vertical_space		= (opts.padding * 2) + 60;

			var w = _getViewport();
			
			if (opts.imageScale && (width > (w[0] - horizontal_space) || height > (w[1] - vertical_space))) {
				var ratio = Math.min(Math.min(w[0] - horizontal_space, width) / width, Math.min(w[1] - vertical_space, height) / height);

				width	= Math.round(ratio * width);
				height	= Math.round(ratio * height);
			}

			_set_content('<img alt="" class="fancy_img" src="' + imagePreloader.src + '" />', width, height);
		};

		function _preload_neighbor_images() {
			if ((opts.itemArray.length -1) > opts.itemCurrent) {
				var href = opts.itemArray[opts.itemCurrent + 1].href || false;

				if (href && href.match(imageRegExp)) {
					objNext = new Image();
					objNext.src = href;
				}
			}

			if (opts.itemCurrent > 0) {
				var href = opts.itemArray[opts.itemCurrent -1].href || false;

				if (href && href.match(imageRegExp)) {
					objNext = new Image();
					objNext.src = href;
				}
			}
		};

		function _set_content(value, width, height) {
			busy = true;

			var pad = opts.padding;

			if (oldIE || ieQuirks) {
				$(".fancy_content", contener)[0].style.removeExpression("height");
				$(".fancy_content", contener)[0].style.removeExpression("width");
			}

			if (pad > 0) {
				width	+= pad * 2;
				height	+= pad * 2;

				$(".fancy_content", contener).css({
					'top'		: pad + 'px',
					'right'		: pad + 'px',
					'bottom'	: pad + 'px',
					'left'		: pad + 'px',
					'width'		: 'auto',
					'height'	: 'auto'
				});

				if (oldIE || ieQuirks) {
					$(".fancy_content", contener)[0].style.setExpression('height',	'(this.parentNode.clientHeight - '	+ pad * 2 + ')');
					$(".fancy_content", contener)[0].style.setExpression('width',		'(this.parentNode.clientWidth - '	+ pad * 2 + ')');
				}
			} else {
				$(".fancy_content", contener).css({
					'top'		: 0,
					'right'		: 0,
					'bottom'	: 0,
					'left'		: 0,
					'width'		: '100%',
					'height'	: '100%'
				});
			}

			if ($(".fancy_outer", contener).is(":visible") && width == $(".fancy_outer", contener).width() && height == $(".fancy_outer", contener).height()) {
				$(".fancy_content", contener).fadeOut('fast', function() {
					$(".fancy_content", contener).empty().append($(value)).fadeIn("normal", function() {
						_finish();
					});
				});

				return;
			}

			var w = _getViewport();

			var itemTop		= (height	+ 60) > w[1] ? w[3] : (w[3] + Math.round((w[1] - height	- 60) * 0.5));
			var itemLeft	= (width	+ 40) > w[0] ? w[2] : (w[2] + Math.round((w[0] - width	- 40) * 0.5));

			var itemOpts = {
				'left':		itemLeft,
				'top':		itemTop,
				'width':	width + 'px',
				'height':	height + 'px'
			};

			if ($(".fancy_outer", contener).is(":visible")) {
				$(".fancy_content", contener).fadeOut("normal", function() {
					$(".fancy_content", contener).empty();
					$(".fancy_outer", contener).animate(itemOpts, opts.zoomSpeedChange, opts.easingChange, function() {
						$(".fancy_content", contener).append($(value)).fadeIn("normal", function() {
							_finish();
						});
					});
				});

			} else {

				if (opts.zoomSpeedIn > 0 && opts.itemArray[opts.itemCurrent].orig !== undefined) {
					$(".fancy_content", contener).empty().append($(value));

					var orig_item	= opts.itemArray[opts.itemCurrent].orig;
					var orig_pos	= $.fn.fancybox.getPosition(orig_item);

					$(".fancy_outer", contener).css({
						'left':		(orig_pos.left	- 20 - opts.padding) + 'px',
						'top':		(orig_pos.top	- 20 - opts.padding) + 'px',
						'width':	$(orig_item).width() + (opts.padding * 2),
						'height':	$(orig_item).height() + (opts.padding * 2)
					});

					if (opts.zoomOpacity) {
						itemOpts.opacity = 'show';
					}

					$(".fancy_outer", contener).animate(itemOpts, opts.zoomSpeedIn, opts.easingIn, function() {
						_finish();
					});

				} else {

					$(".fancy_content", contener).hide().empty().append($(value)).show();
					$(".fancy_outer", contener).css(itemOpts).fadeIn("normal", function() {
						_finish();
					});
				}
			}
		};

		function _set_navigation() {
			if (opts.itemCurrent !== 0) {
				$(".fancy_left, .fancy_left_ico", contener).unbind().bind("click", function(e) {
					e.stopPropagation();

					opts.itemCurrent--;
					_change_item();

					return false;
				});

				$(".fancy_left", contener).show();
			}

			if (opts.itemCurrent != ( opts.itemArray.length -1)) {
				$(".fancy_right, .fancy_right_ico", contener).unbind().bind("click", function(e) {
					e.stopPropagation();

					opts.itemCurrent++;
					_change_item();

					return false;
				});

				$(".fancy_right", contener).show();
			}
		};

		function _finish() {
			if ($.browser.msie) {
				$(".fancy_content", contener)[0].style.removeAttribute('filter');
				$(".fancy_outer", contener)[0].style.removeAttribute('filter');
			}

			_set_navigation();

			_preload_neighbor_images();

			$(document).bind("keydown.fb", function(e) {
				if (e.keyCode == 27 && opts.enableEscapeButton) {
					_close();

				} else if(e.keyCode == 37 && opts.itemCurrent !== 0) {
					$(document).unbind("keydown.fb");
					opts.itemCurrent--;
					_change_item();
					

				} else if(e.keyCode == 39 && opts.itemCurrent != (opts.itemArray.length - 1)) {
					$(document).unbind("keydown.fb");
					opts.itemCurrent++;
					_change_item();
				}
			});

			if (opts.hideOnContentClick) {
				$(".fancy_content", contener).click(_close);
			}

			if (opts.overlayShow && opts.hideOnOverlayClick) {
				$(".fancy_overlay", contener).bind("click", _close);
			}

			if (opts.showCloseButton) {
				$(".fancy_close", contener).bind("click", _close).show();
			}

			if (typeof opts.itemArray[ opts.itemCurrent ].title !== 'undefined' && opts.itemArray[ opts.itemCurrent ].title.length > 0) {
				var pos = $(".fancy_outer", contener).position();

				$('.fancy_title div', contener).text( opts.itemArray[ opts.itemCurrent ].title).html();

				$('.fancy_title', contener).css({
					'top'	: pos.top + $(".fancy_outer", contener).outerHeight() - 32,
					'left'	: pos.left + (($(".fancy_outer", contener).outerWidth() * 0.5) - ($('.fancy_title', contener).width() * 0.5))
				}).show();
			}

			if (opts.overlayShow && IE6) {
				$('embed, object, select', $('.fancy_content', contener)).css('visibility', 'visible');
			}

			if ($.isFunction(opts.callbackOnShow)) {
				opts.callbackOnShow( opts.itemArray[ opts.itemCurrent ] );
			}

			if ($.browser.msie) {
				$(".fancy_outer", contener)[0].style.removeAttribute('filter'); 
				$(".fancy_content", contener)[0].style.removeAttribute('filter'); 
			}
			
			busy = false;
		};
		
		function _close () {
			$('div.fancy_global', contener).remove();

			return false;
		};
		
		function _showLoading () {
			clearInterval(loadingTimer);

			var w = _getViewport();

			$(".fancy_loading", contener).css({'left': ((w[0] - 40) * 0.5 + w[2]), 'top': ((w[1] - 40) * 0.5 + w[3])}).show();
			$(".fancy_loading", contener).bind('click', _close);

			loadingTimer = setInterval(_animateLoading, 66);			
		};
		
		function _animateLoading () {
			if (!$(".fancy_loading", contener).is(':visible')){
				clearInterval(loadingTimer);
				return;
			}

			$(".fancy_loading > div", contener).css('top', (loadingFrame * -40) + 'px');
			loadingFrame = (loadingFrame + 1) % 12;
		};
		
		function _getViewport () {
			return [$(contener).width(), $(contener).height(), $(document).scrollLeft(), $(document).scrollTop() ];
		};

		return this.unbind('click.fb').bind('click.fb', _initialize);
	};

	$.fn.fancybox.getNumeric = function(el, prop) {
		return parseInt($.curCSS(el.jquery?el[0]:el,prop,true))||0;
	};

	$.fn.fancybox.getPosition = function(el) {
		var pos = el.offset();

		pos.top	+= $.fn.fancybox.getNumeric(el, 'paddingTop');
		pos.top	+= $.fn.fancybox.getNumeric(el, 'borderTopWidth');

		pos.left += $.fn.fancybox.getNumeric(el, 'paddingLeft');
		pos.left += $.fn.fancybox.getNumeric(el, 'borderLeftWidth');

		return pos;
	};

	$.fn.fancybox.showIframe = function() {
		$(".fancy_loading").hide();
		$(".fancy_frame").show();
	};

	$.fn.fancybox.defaults = {
		padding				:	10,
		imageScale			:	true,
		zoomOpacity			:	true,
		zoomSpeedIn			:	0,
		zoomSpeedOut		:	0,
		zoomSpeedChange		:	300,
		easingIn			:	'swing',
		easingOut			:	'swing',
		easingChange		:	'swing',
		frameWidth			:	560,
		frameHeight			:	340,
		overlayShow			:	true,
		overlayOpacity		:	0.3,
		overlayColor		:	'#666',
		enableEscapeButton	:	true,
		showCloseButton		:	true,
		hideOnOverlayClick	:	true,
		hideOnContentClick	:	true,
		centerOnScroll		:	true,
		itemArray			:	[],
		callbackOnStart		:	null,
		callbackOnShow		:	null,
		callbackOnClose		:	null
	};
})(jQuery);