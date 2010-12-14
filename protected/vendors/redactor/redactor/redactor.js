/*
	Redactor v5.1.1
	http://imperavi.com/
 
	Copyright 2010, Imperavi Ltd.
	Dual licensed under the MIT or GPL Version 2 licenses.
	
	EXAMPLE
	$('#content').redactor();
*/

var LOOP_LEVEL = 0;
var LOOP_SIZE=100;
var RedactorColorMode;
var RedactorActive;
var RedactorModalActive;
var isCtrl = false;
		    
(function($){
	
	// Initialization	
	$.fn.redactor = function(options)
	{				
		var obj = new Construct(this, options);
		
		obj.init();
		return obj;
	};
	
	// Options and variables	
	function Construct(el, options) {

		this.opts = $.extend({	
			textarea: true,
			path: '/',
			pageview: false,
			fullscreen: false,
			autosave: false,
			saveInterval: 60, // seconds
			resize: true,
			visual: true,
			focus: false,
			toolbar: 'original',
			upload: 'upload.php',
			uploadParams: '',
			uploadFunction: false,
			width: false,
			height: false,
			autoformat: true,
			colors: Array(
				'#ffffff', '#000000', '#eeece1', '#1f497d', '#4f81bd', '#c0504d', '#9bbb59', '#8064a2', '#4bacc6', '#f79646',
				'#f2f2f2', '#7f7f7f', '#ddd9c3', '#c6d9f0', '#dbe5f1', '#f2dcdb', '#ebf1dd', '#e5e0ec', '#dbeef3', '#fdeada',
				'#d8d8d8', '#595959', '#c4bd97', '#8db3e2', '#b8cce4', '#e5b9b7', '#d7e3bc', '#ccc1d9', '#b7dde8', '#fbd5b5',
				'#bfbfbf', '#3f3f3f', '#938953', '#548dd4', '#95b3d7', '#d99694', '#c3d69b', '#b2a2c7', '#b7dde8', '#fac08f',
				'#a5a5a5', '#262626', '#494429', '#17365d', '#366092', '#953734', '#76923c', '#5f497a', '#92cddc', '#e36c09',
				'#7f7f7f', '#0c0c0c', '#1d1b10', '#0f243e', '#244061', '#632423', '#4f6128', '#3f3151', '#31859b', '#974806')			
		}, options);
		
		this.$el = $(el);
	};

	// Functionality
	Construct.prototype = {
		
		// Toolbar
		buttons: {
			original:
			{
				
				html: 	{name: 'html', title: 'Код', func: 'toggle'},
				//undo:   {exec: 'Undo', name: 'undo', title: 'Отмена' },
				//redo: 	{exec: 'Redo', name: 'redo', title: 'Повтор'},				
				//bold: 	{exec: 'Bold', name: 'bold', title: 'Полужирный'},				
				//italic: 	{exec: 'italic', name: 'italic', title: 'Наклонный'},				
				//superscript: 	{exec: 'superscript', name: 'superscript', title: 'Надстрочный'},				
				styles: {name: 'styles', title: 'Стили', func: 'show', 
					dropdown: 
					{
						p: 			{exec: 'formatblock', name: 'p', title: 'Обычный текст'},
						blockquote: {exec: 'formatblock', name: 'blockquote', title: 'Цитата'},
						code: 		{exec: 'formatblock', name: 'code', title: 'Код'},
						h2: 		{exec: 'formatblock', name: 'h2', title: 'Заголовок 1', style: 'font-size: 18px;'},
						h3: 		{exec: 'formatblock', name: 'h3', title: 'Заголовок 2', style: 'font-size: 14px; font-weight: bold;'}																	
					}
				},
				format: {name: 'format', title: 'Формат', func: 'show',
					dropdown: 
					{
						bold: 		  {exec: 'bold', name: 'bold', title: 'Полужирный', style: 'font-weight: bold;'},
						italic: 	  {exec: 'italic', name: 'italic', title: 'Наклонный', style: 'font-style: italic;'},
						superscript:  {exec: 'superscript', name: 'superscript', title: 'Надстрочный'},
						strikethrough:  {exec: 'StrikeThrough', name: 'StrikeThrough', title: 'Зачеркнутый', style: 'text-decoration: line-through !important;'},
						fgcolor: 	  {name: 'fgcolor', title: 'Цвет текста', func: 'showFgcolor'},
						hilite: 	  {name: 'hilite', title: 'Заливка текста', func: 'showHilite'},
						removeformat: {exec: 'removeformat', name: 'removeformat', title: 'Удалить формат'},
						clearWord: {func: 'clearWord', name: 'clearWord', title: 'Удалить стили MS Word'}
					}						
				},
				lists: 	{name: 'lists', title: 'Списки', func: 'show',
					dropdown: 
					{
						ul: 	 {exec: 'insertunorderedlist', name: 'insertunorderedlist', title: '&bull; Обычный список'},
						ol: 	 {exec: 'insertorderedlist', name: 'insertorderedlist', title: '1. Нумерованный список'},
						outdent: {exec: 'outdent', name: 'outdent', title: '< Уменьшить отступ'},
						indent:  {exec: 'indent', name: 'indent', title: '> Увеличить отступ'}
					}			
				},
				justify: 	{name: 'justify', title: 'Выравнивание', func: 'show',
					dropdown: 
					{
						JustifyLeft: 	 {exec: 'JustifyLeft', name: 'JustifyLeft', title: 'По левому краю'},					
						JustifyCenter: 	 {exec: 'JustifyCenter', name: 'JustifyCenter', title: 'По центру'},
						JustifyRight: {exec: 'JustifyRight', name: 'JustifyRight', title: 'По правому краю'}
					}		
				},				
				image: 	{name: 'image', title: 'Картинка', func: 'showImage'},
				table: 	{name: 'table', title: 'Таблица', func: 'showTable'},
				video: 	{name: 'video', title: 'Видео', func: 'showVideo'},
				link: 	{name: 'link', title: 'Ссылка', func: 'show',
					dropdown: 
					{
						link: 	{name: 'link', title: 'Вставить ссылку ...', func: 'showLink'},
						unlink: {exec: 'unlink', name: 'unlink', title: 'Удалить ссылку'}
					}			
				}
				//fullscreen: {name: 'fullscreen', title: 'Во весь экран', func: 'fullscreen'}
			},
			mini:
			{
				html: 	{name: 'html', title: 'Код', func: 'toggle'},
				styles: {name: 'styles', title: 'Стили', func: 'show', 
					dropdown: 
					{
						p: 			{exec: 'formatblock', name: 'p', title: 'Обычный текст'},
						blockquote: {exec: 'formatblock', name: 'blockquote', title: 'Цитата'},
						code: 		{exec: 'formatblock', name: 'code', title: 'Код'}
					}
				},
				format: {name: 'format', title: 'Формат', func: 'show',
					dropdown: 
					{
						bold: 		  {exec: 'bold', name: 'bold', title: 'Полужирный', style: 'font-weight: bold;'},
						italic: 	  {exec: 'italic', name: 'italic', title: 'Наклонный', style: 'font-style: italic;'},
						superscript:  {exec: 'superscript', name: 'superscript', title: 'Надстрочный'},
						fgcolor: 	  {name: 'fgcolor', title: 'Цвет текста', func: 'showFgcolor'},
						hilite: 	  {name: 'hilite', title: 'Заливка текста', func: 'showHilite'},
						removeformat: {exec: 'removeformat', name: 'removeformat', title: 'Удалить формат'}																		
					}						
				},
				lists: 	{name: 'lists', title: 'Списки', func: 'show',
					dropdown: 
					{
						ul: 	 {exec: 'insertunorderedlist', name: 'insertunorderedlist', title: '&bull; Обычный список'},
						ol: 	 {exec: 'insertorderedlist', name: 'insertorderedlist', title: '1. Нумерованный список'},
						outdent: {exec: 'outdent', name: 'outdent', title: '< Уменьшить отступ'},
						indent:  {exec: 'indent', name: 'indent', title: '> Увеличить отступ'}
					}			
				},
				//image: 	{name: 'image', title: 'Картинка', func: 'showImage'},
				table: 	{name: 'table', title: 'Таблица', func: 'showTable'},
				link: 	{name: 'link', title: 'Ссылка', func: 'show',
					dropdown: 
					{
						link: 	{name: 'link', title: 'Вставить ссылку ...', func: 'showLink'},
						unlink: {exec: 'unlink', name: 'unlink', title: 'Удалить ссылку'}
					}			
				}
			}		
		},	
	
		init: function()
		{		
			
			var link = {};
			link.href = '';
	        $("link").each(function(i,s)
			{
				if (s.href && s.href.match(/redactor\.css/)) link.href = s.href;
			});
		
			this.opts.path = link.href.replace(/css\/redactor\.css/, '');
	   		this.cssUrl = this.opts.path + 'css/blank.css';
	   		
  		   		
	   		this.textarea = this.$el;
	   		this.frameID = this.$el.attr('id');
	   		this.width = this.textarea.css('width');
	   		this.height = this.textarea.css('height');    		
	   		
	   		// create container
			this.box = $('<div id="redactor_box_' + this.frameID + '" style="width: ' + this.width + ';" class="redactor_box"></div>');
	
	 		 // create iframe
			this.frame = $('<iframe frameborder="0" marginheight="0" marginwidth="0" scrolling="auto" vspace="0" hspace="0" id="redactor_frame_' + this.frameID + '" style="height: ' + this.height + ';" class="redactor_frame"></iframe>');
	   	
			this.textarea.hide();	   	
	   	
			// append
			$(this.box).insertAfter(this.textarea);
			$(this.box).append(this.frame).append(this.textarea);
   			
   			// toolbar
	   		this.toolbar = $('<ul id="redactor_toolbar_' + this.frameID + '" class="redactor_toolbar"></ul>');
			this.buildToolbar();
			$(this.box).prepend(this.toolbar);
	
			// resizer
			if (this.opts.resize)
			{
				this.resizer = $('<div id="redactor_resize' + this.frameID + '" class="redactor_bottom"><div></div></div>');
				$(this.box).append(this.resizer);
	
	           $(this.resizer).mousedown(function(e) { this.initResize(e) }.bind(this));
			}
	
	
			// enable	
	   		this.doc = this.contentDocumentFrame(this.frame);
	   		
			this.doc.open();
			this.doc.write(this.getEditorDoc(this.textarea.val()));
			this.doc.close();
					
			this.designMode();
			
			this.formSets();
			
			$(this.doc).click(function() { this.hideAllDropDown() }.bind(this));
			
			// observe keyup and keydown

		    $(this.doc).keydown(function(e)
		    {
		        if(e.ctrlKey || e.metaKey) isCtrl = true;
		        if(e.keyCode == 66 && isCtrl) { this.execCommand('bold', 'bold'); return false; }
		        if(e.keyCode == 73 && isCtrl) { this.execCommand('italic', 'italic'); return false; }
		    }.bind(this)).keyup(function(e)
		    {
		        isCtrl = false;			        
		        this.syncCode();
		        	        
		    }.bind(this));
				
			// autosave	
			if (this.opts.autosave)	
			{	
				setInterval(function()
				{
					var html = this.getHtml();
					$.post(this.opts.autosave, { data: html });
				}.bind(this), this.opts.saveInterval*1000);
				
			}
				
			if (this.opts.focus) this.frame.get(0).contentWindow.focus();	
		},
		buildToolbar: function()
		{
			$.each($(this.buttons[this.opts.toolbar]), 
	   			function (i, s)
	   			{
	   				$.each(s,
	   					function (z, f)
	   					{
	   						var a = $('<a href="javascript:void(null);" class="redactor_ico redactor_ico_' + f.name + '" title="' + f.title + '">&nbsp;</a>');
	   						
	   						if (typeof(f.func) == 'undefined') a.click(function() { this.execCommand(f.exec, f.name); }.bind(this));
	   						else if (f.func != 'show') a.click(function() { this[f.func](); }.bind(this));
	   						
							var li = $('<li></li>');
	   						$(li).append(a);   						   						
	
	 						if (typeof(f.dropdown) != 'undefined')
	 						{
	 							var ul = $('<ul class="redactor_dropdown_' + this.frameID + '" id="redactor_dropdown_' + this.frameID + '_' + f.name + '" style="display: none;"></ul>');
		   						$.each(f.dropdown,
				   					function (x, d)
	   								{
	   									if (typeof(d.style) == 'undefined') d.style = '';
	   									var ul_li = $('<li></li>');
	   									var ul_li_a = $('<a href="javascript:void(null);" style="' + d.style + '">' + d.title + '</a>');
	   									$(ul_li).append(ul_li_a); 
	   									$(ul).append(ul_li);
	   									
	   									if (typeof(d.func) == 'undefined') $(ul_li_a).click(function() { this.execCommand(d.exec, d.name); }.bind(this));
	   									else $(ul_li_a).click(function(e) { this[d.func](e); }.bind(this));
	   									  									
	   								}.bind(this)
	   							);
	   							$(li).append(ul);
	
	   							this.hdlHideDropDown = function(e) { this.hideDropDown(e, ul, f.name) }.bind(this);
	   							this.hdlShowDropDown = function(e) { this.showDropDown(e, ul, f.name) }.bind(this);
	   							this.hdlShowerDropDown = function(e) { this.showerDropDown(e, ul, f.name) }.bind(this);   							
	   						
								a.click(this.hdlShowDropDown);  
								a.mouseover(this.hdlShowerDropDown);  							
		
								$(document).click(this.hdlHideDropDown);
								
	   				
	   						}
	   						else a.mouseover(function(e) { this.hideAllDropDown() }.bind(this));			
	   							   						
	   						$(this.toolbar).append(li);
	   						
	   					}.bind(this)
	   				);
	   			}.bind(this)
	   		);
		},
		focus: function()
		{
			this.frame.get(0).contentWindow.focus();	
		},	
		getEditorDoc: function(html)
		{
	    	var frameHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n';
			frameHtml += '<html><head><link media="all" type="text/css" href="' + this.cssUrl + '" rel="stylesheet"></head><body>';
			frameHtml += html;
			frameHtml += '</body></html>';
			return frameHtml;
		},	
		contentDocumentFrame: function(frame)
		{	
			frame = frame.get(0);
	
			if (frame.contentDocument) return frame.contentDocument;
			else if (frame.contentWindow && frame.contentWindow.document) return frame.contentWindow.document;
			else if (frame.document) return frame.document;
			else return null;
		},
		designMode: function()
		{
			if (this.doc)
			{
				this.doc.designMode = 'on';
				this.frame.load(function() { this.doc.designMode = 'on'; }.bind(this));
			}
		},
		formSets: function()
		{
			var oldOnsubmit = null;		
	
			var theForm = $(this.container).parents('form');
			if (theForm.length == 0) return false;
	
			oldOnsubmit = theForm.get(0).onsubmit;
	
			if (typeof theForm.get(0).onsubmit != "function")
			{
				theForm.get(0).onsubmit = function()
				{
	          		if (this.opts.visual)
					{
						this.paragraphise();
						return this.syncCode();
					}
				}.bind(this)
			}
			else
			{
				theForm.get(0).onsubmit = function()
				{
	            	if (this.opts.visual)
					{
						this.paragraphise();
						this.syncCode();
	
						return oldOnsubmit();
					}
				}.bind(this)
			}
	
			return true;
		},			
		execCommand: function(cmd, param)
		{		
			if (this.opts.visual)
			{
				if (this.doc)
				{
	    			try
		    		{
	    				this.frame.get(0).contentWindow.focus();
		    		
		    			if (cmd == 'inserthtml' && jQuery.browser.msie) this.doc.selection.createRange().pasteHTML(param);
		    			else   			
						{
							this.doc.execCommand(cmd, false, param);
							if (param == "blockquote")
							{
	    			    		this.doc.body.appendChild(this.doc.createElement("BR"));
						    }					
						}
					}
					catch (e) { }
					
					this.syncCode();		
				}
			}
		},
		showFgcolor: function(e)
		{
			if (this.opts.visual)
			{
				$(e.target).addClass('redactor_colortooltip');			
			
				RedactorColorMode = 'ForeColor';
				RedactorActive = this;
				this.colorPicker(e);
			}
		},
		showHilite: function(e)
		{
			if (this.opts.visual)
			{
				$(e.target).addClass('redactor_colortooltip');
			
				if ($.browser.msie) var mode = 'BackColor';
				else var mode = 'hilitecolor';
				
				RedactorColorMode = mode;
				RedactorActive = this;
				this.colorPicker(e);
			}
		},		
		clearWord: function()
		{
			var html = this.getHtml();
			html = CleanWHtml(html);			
			this.setHtml(html);
		},
		showVideo: function()
		{
			RedactorActive = this;
			this.modalInit({ title: 'Видео', width: 600, height: 330, url: this.opts.path + 'plugins/insert_video.html', triggerClose: 'redactorCloseModal'});
		},	
		insertVideo: function()
		{
			var data = $('#redactor_insert_video_area').val();
			if (RedactorActive.opts.visual) data = '<div class="redactor_video_box">' + data + '</div>';
	
			RedactorActive.execCommand('inserthtml', data);
			this.modalHide();
			
		},		
		showTable: function()
		{
			RedactorActive = this;
			this.modalInit({ title: 'Таблица', width: 400, height: 240, url: this.opts.path + 'plugins/insert_table.html', triggerClose: 'redactorCloseModal'});
		},	
		insertTable: function()
		{
			var units = $('#redactor_insert_table_units').val();
			var width = $('#redactor_insert_table_width').val();
			var rows = $('#redactor_insert_table_rows').val();
			var cell = $('#redactor_insert_table_cell').val();
	
			if (units == '%' && width == 100) width = 99;
			var table = '<table style="width: '+ width + units + ';">';
	
			for(x=0;x<rows;x++)
			{
				table += '<tr>';
				for(y=0;y<cell;y++)
				{
				   table += '<td></td>';
				}
				table += '</tr>';
			}
			table += '</table><br /> ';
	
			RedactorActive.frame.get(0).contentWindow.focus();
			RedactorActive.execCommand('inserthtml', table);
			this.modalHide();		
			
		},	
		showLink: function()
		{
			RedactorActive = this;

			this.modalInit({ 
				title: 'Ссылка', 
				height: 290, 
				url: this.opts.path + 'plugins/insert_link.html', 
				triggerClose: 'redactorCloseModal', 
				end: function()
				{
						var sel = this.get_selection();
						if ($.browser.msie)
						{
								var temp = sel.htmlText.match(/href="(.*?)"/gi);
								if (temp != null)
								{
									temp = new String(temp);
									temp = temp.replace(/href="(.*?)"/gi, '$1');
								}
		
		  					 	var text = sel.text;
								if (temp != null) var url = temp;
								else  var url = '';
								var title = '';
						}
						else
						{
							if (sel.anchorNode.parentNode.tagName == 'A')
							{
								var url = sel.anchorNode.parentNode.href;
								var text = sel.anchorNode.parentNode.text;
								var title = sel.anchorNode.parentNode.title;
								if (sel.toString() == '') this.insert_link_node = sel.anchorNode.parentNode
		
							}
							else
							{
							 	var text = sel.toString();
								var url = '';
								var title = '';
							}
						}
		
						$('#redactor_link_url').val(url);
						$('#redactor_link_text').val(text);
						$('#redactor_link_title').val(title);		
						
				}.bind(this)
			});
	
		},	
		insertLink: function()
		{
			var value = $('#redactor_link_text').val();
			if (value == '') return true;
			
			if ($('#redactor_link_id_url').get(0).checked) 
			{
				var a = '<a href="' + $('#redactor_link_url').val() + '" title="' + $('#redactor_link_title').val() + '">' + value + '</a> ';
			}
			else
			{
				var a = '<a href="mailto:' + $('#redactor_link_url').val() + '" title="' + $('#redactor_link_title').val() + '">' + value + '</a> '
			}
	
			if (a)
			{
				if (this.insert_link_node)
				{
					$(this.insert_link_node).text(value);
					$(this.insert_link_node).attr('href', $('#redactor_link_url').val());
					$(this.insert_link_node).attr('title', $('#redactor_link_title').val());
	
					return true;
				}
				else
				{
					RedactorActive.frame.get(0).contentWindow.focus();
					RedactorActive.execCommand('inserthtml', a);
				}
			}
			this.modalHide();
		},
		showImage: function()
		{
			this.spanid = Math.floor(Math.random() * 99999);
			if (jQuery.browser.msie)
			{
				this.execCommand('inserthtml', '<span id="span' + this.spanid + '"></span>');
			}
			
		
			RedactorActive = this;
			this.modalInit({ title: 'Изображение', height: 330, url: this.opts.path + 'plugins/insert_image.html', triggerClose: 'redactorCloseModal', end: function()
			{
				var params = '';
				if (this.opts.uploadFunction) var params = this.opts.uploadFunction();
				this.uploadInit('redactorInsertImageForm', { url: this.opts.upload + params, trigger: 'redactorUploadBtn', success: function() {
				this.imageUploadCallback();
				}.bind(this)  });
			}.bind(this) });
		},
		imageUploadCallback: function(data)
		{
			if ($('#redactor_file_link').val() != '') data = $('#redactor_file_link').val();
			var alt = $('#redactor_file_alt').val();
	
			var style = '';
			if ($('#redactor_form_image_align') != 0)
			{
				var float = $('#redactor_form_image_align').val();
				
				if (float == 'left') style = 'style="float: right; margin-right: 10px; margin-bottom: 10px;"';
				else if (float == 'right') style = 'style="float: right; margin-left: 10px; margin-bottom: 10px;"';
				
				var html = '<img alt="' + alt + '" src="' + data + '" ' + style + ' />';
			}
			else
			{
				var html = '<p><img alt="' + alt + '" src="' + data + '" /></p>'; 
			}
		
			RedactorActive.frame.get(0).contentWindow.focus();
			
			if ($.browser.msie)
			{		
				$(RedactorActive.doc.getElementById('span' + RedactorActive.spanid)).after(html);
				$(RedactorActive.doc.getElementById('span' + RedactorActive.spanid)).remove();
			}	
			else
			{
				RedactorActive.execCommand('inserthtml', html);
			}
	
			this.modalHide();
	
		},		
		
		
		/*
			Fullscreen
		*/		
		fullscreen: function()
		{	
			
		},
		
		/*
			API
		*/
		setHtml: function(html)
		{
			this.doc.open();
			this.doc.write(this.getEditorDoc(html));
			this.doc.close();
		},
		getHtml: function()
		{
			return this.doc.body.innerHTML;
		},
		getCode: function()
		{
			if (this.opts.visual)
			{
				this.formatHtml();
				var html = this.getHtml();
				html = this.tidyUp(html);
				
				return html;
			}
			else
			{
				return this.textarea.val();
			}
		},

	
		/*
			DropDown
		*/
		showedDropDown: false,
		showDropDown: function(e, ul, name)
		{
		
			if (this.showedDropDown) this.hideAllDropDown();
			else
			{
				this.showedDropDown = true;
				this.showingDropDown(e, ul, name);
			}		
				
		},
		showingDropDown: function(e, ul, name)
		{
			this.hideAllDropDown();
			 		
	   		this.addSelButton(name);
	   		$(ul).show();	
	
		},
		showerDropDown: function(e, ul, name)
		{
			if (this.showedDropDown) this.showingDropDown(e, ul, name);
		},
		hideAllDropDown: function()
		{
			$('#redactor_toolbar_' + this.frameID + ' a.redactor_ico').removeClass('redactor_ico_select');
	   		$('ul.redactor_dropdown_' + this.frameID).hide();
		},
		hideDropDown: function(e, ul, name)
		{
			if (!$(e.target).hasClass('redactor_ico_select'))
			{
				this.showedDropDown = false;
				this.hideAllDropDown();
			
			}	
		
			$(document).unbind('click', this.hdlHideDropDown);
			$(this.doc).unbind('click', this.hdlHideDropDown);
			
		},
		addSelButton: function(name)
		{
			var element = $('#redactor_toolbar_' + this.frameID + ' a.redactor_ico_' + name);
			element.addClass('redactor_ico_select');
		},
		removeSelButton: function(name)
		{
			var element = $('#redactor_toolbar_' + this.frameID + ' a.redactor_ico_' + name);
			element.removeClass('redactor_ico_select');
		},	
		toggleSelButton: function(name)
		{
			$('#redactor_toolbar_' + this.frameID + ' a.redactor_ico_' + name).toggleClass('redactor_ico_select');
		},
		
		/*
			Toggle
		*/
		toggle: function()
		{
			this.toggleSelButton('html');
	
			if (this.opts.visual)
			{
				this.frame.hide();
				this.textarea.show();
	
				this.paragraphise();
	
				
				var html = this.getHtml();
								
				html = this.tidyUp(html);
	
				html = html.replace(/\%7B/gi, '{');
				html = html.replace(/\%7D/gi, '}');
	
	
	
				// flash replace
				html = html.replace(/<div(.*?)class="redactor_video_box"(.*?)>([\w\W]*?)\<\/div>/gi, "$3");
				
				this.formatHtml(html);
	
				html = html.replace(/<hr class="redactor_cut">/gi, '<!--more-->');
				html = html.replace(/<hr class=redactor_cut>/gi, '<!--more-->');
	
				this.textarea.val(html).focus();
	
				this.opts.visual = false;
			}
			else
			{
				this.textarea.hide();
	
				var html = this.textarea.val();
				
				html = html.replace(/<!--more-->/gi, '<hr class="redactor_cut">');
	
				html = html.replace(/\<object([\w\W]*?)\<\/object\>/gi, '<div class="redactor_video_box"><object$1</object></div>');
	
				this.doc.body.innerHTML = html;
				
				this.frame.show();
				this.focus();
				
				this.opts.visual = true;
			}
		},		


		get_selection: function ()
		{
			if (this.frame.get(0).contentWindow.getSelection) return this.frame.get(0).contentWindow.getSelection();
			else if (this.frame.get(0).contentWindow.document.selection) return this.frame.contentWindow.get(0).document.selection.createRange();
		},
	
		/*
			Paragraphise
		*/
		paragraphise: function()
		{
			if (this.opts.autoformat === false) return true;
			if (this.opts.visual)
			{
	
				var theBody = this.doc.body;
	
				/* Remove all text nodes containing just whitespace */
				for (var i = 0; i < theBody.childNodes.length; i++)
				{
					if (theBody.childNodes[i].nodeName.toLowerCase() == "#text" &&
						theBody.childNodes[i].data.search(/^\s*$/) != -1)
					{
						theBody.removeChild(theBody.childNodes[i]);
	
						i--;
					}
				}
	
				var removedElements = new Array();
	
				for (var i = 0; i < theBody.childNodes.length; i++)
				{
					if (theBody.childNodes[i].nodeName.isInlineName())
					{
						removedElements.push(theBody.childNodes[i].cloneNode(true));
	
						theBody.removeChild(theBody.childNodes[i]);
	
						i--;
					}
					else if (theBody.childNodes[i].nodeName.toLowerCase() == "br")
					{
						if (i + 1 < theBody.childNodes.length)
						{
							/* If the current break tag is followed by another break tag */
							if (theBody.childNodes[i + 1].nodeName.toLowerCase() == "br")
							{
								/* Remove consecutive break tags */
								while (i < theBody.childNodes.length && theBody.childNodes[i].nodeName.toLowerCase() == "br")
								{
									theBody.removeChild(theBody.childNodes[i]);
								}
	
								if (removedElements.length > 0)
								{
									this.insertNewParagraph(removedElements, theBody.childNodes[i]);
	
									removedElements = new Array();
								}
							}
							/* If the break tag appears before a block element */
							else if (!theBody.childNodes[i + 1].nodeName.isInlineName())
							{
								theBody.removeChild(theBody.childNodes[i]);
							}
							else if (removedElements.length > 0)
							{
								removedElements.push(theBody.childNodes[i].cloneNode(true));
	
								theBody.removeChild(theBody.childNodes[i]);
							}
							else
							{
								theBody.removeChild(theBody.childNodes[i]);
							}
	
							i--;
						}
						else
						{
							theBody.removeChild(theBody.childNodes[i]);
						}
					}
					else if (removedElements.length > 0)
					{
						this.insertNewParagraph(removedElements, theBody.childNodes[i]);
	
						removedElements = new Array();
					}
				}
	
				if (removedElements.length > 0)
				{
					this.insertNewParagraph(removedElements);
				}
			}
	
			return true;
		},
		insertNewParagraph: function(elementArray, succeedingElement)
		{
			var theBody = this.doc.getElementsByTagName("body")[0];
			var theParagraph = this.doc.createElement("p");
	
			for (var i = 0; i < elementArray.length; i++)
			{
				theParagraph.appendChild(elementArray[i]);
			}
	
			if (typeof(succeedingElement) != "undefined")
			{
				theBody.insertBefore(theParagraph, succeedingElement);
			}
			else
			{
				theBody.appendChild(theParagraph);
			}
	
			return true;
		},
		syncCode: function()
		{
			var html = this.getHtml();
			html = this.tidyUp(html);
	
			html = html.replace(/\%7B/gi, '{');
			html = html.replace(/\%7D/gi, '}');
	
			html = html.replace(/<hr class="redactor_cut">/gi, '<!--more-->');
			html = html.replace(/<hr class=redactor_cut>/gi, '<!--more-->');
	
			this.textarea.val(html);
		},		
		tidyUp: function(html)
		{
			if (typeof($) == 'undefined') return html;
			
			if ($.browser.msie)
			{
		      	var match = html.match(/<(.*?)>/gi);
		      	
				$.each(match, function(i,s)
				{
					html = html.replace(s, s.toLowerCase());
				}) 
				
				
			}
	
			if ($.browser.mozilla) html = this.convertSpan(html);
			
			return html;
		},
		convertSpan: function(html)
		{
			html = html.replace(/\<span(.*?)style="font-weight: bold;"\>([\w\W]*?)\<\/span\>/gi, "<b>$2</b>");
			html = html.replace(/\<span(.*?)style="font-style: italic;"\>([\w\W]*?)\<\/span\>/gi, "<i>$2</i>");
			html = html.replace(/\<span(.*?)style="font-weight: bold; font-style: italic;"\>([\w\W]*?)\<\/span\>/gi, "<i><b>$2</b></i>");
			html = html.replace(/\<span(.*?)style="font-style: italic; font-weight: bold;"\>([\w\W]*?)\<\/span\>/gi, "<b><i>$2</i></b>");
	
			return html;
	  	},	
		formatHtml: function(html)
		{
			if (typeof(html) == 'undefined') var html = this.getHtml();
			this.cleanHTML(html);
		},
		finishTabifier: function(code)
		{
			code=code.replace(/\n\s*\n/g, '\n');  //blank lines
			code=code.replace(/^[\s\n]*/, ''); //leading space
			code=code.replace(/[\s\n]*$/, ''); //trailing space
		
			this.textarea.val(code);
			LOOP_LEVEL=0;
		},
		cleanHTML: function(code)
		{
			
			var i=0;
			var point=0, start=null, end=null, tag='', out='', cont='';
			this.cleanAsync(code, i, point, start, end, tag, out, cont);
			
		},		
		cleanAsync: function(code, i, point, start, end, tag, out, cont)
		{
			var iStart=i;
			for (; i<code.length && i<iStart+LOOP_SIZE; i++)
			{
				point = i;
		
			//if no more tags, copy and exit
			if (-1 == code.substr(i).indexOf('<'))
			{
				out+=code.substr(i);
				this.finishTabifier(out);
				return;
			}

			//copy verbatim until a tag
			while ('<'!=code.charAt(point)) point++;
			if (i!=point) {
				cont=code.substr(i, point-i);
				if (!cont.match(/^\s+$/)) {
					if ('\n'==out.charAt(out.length-1)) {
						out+=tabs();
					} else if ('\n'==cont.charAt(0)) {
						out+='\n'+tabs();
						cont=cont.replace(/^\s+/, '');
					}
					cont=cont.replace(/\s+/g, ' ');
					out+=cont;
				} if (cont.match(/\n/)) {
					out+='\n'+tabs();
				}
			}
			start=point;

			//find the end of the tag
			while ('>'!=code.charAt(point)) point++;
			tag=code.substr(start, point-start);
			i=point;

			//if this is a special tag, deal with it!
			if ('!--'==tag.substr(1,3)) {
				if (!tag.match(/--$/)) {
					while ('-->'!=code.substr(point, 3)) point++;
					point+=2;
					tag=code.substr(start, point-start);
					i=point;
				}
				if ('\n'!=out.charAt(out.length-1)) out+='\n';
				out+=tabs();
				out+=tag+'>\n';
			} else if ('!'==tag[1]) {
				out=placeTag(tag+'>', out);
			} else if ('?'==tag[1]) {
				out+=tag+'>\n';
			} else if (t=tag.match(/^<(script|style)/i)) {
				t[1]=t[1].toLowerCase();
				tag=cleanTag(tag);
				out=placeTag(tag, out);
				end=String(code.substr(i+1)).toLowerCase().indexOf('</'+t[1]);
				if (end) {
					cont=code.substr(i+1, end);
					i+=end;
					out+=cont;
				}
			} else {
				tag=cleanTag(tag);
				out=placeTag(tag, out);
			}
		}


			if (i<code.length) setTimeout(function() { this.cleanAsync(code, i, point, start, end, tag, out, cont) }.bind(this), 0);
			else this.finishTabifier(out);

		},
		
		
		/*
			Resizer
		*/
		initResize: function(e)
		{	
			e.preventDefault();
			this.splitter = e.target;
	
			if (this.opts.visual)
			{
				this.element_resize = this.frame;
				this.element_resize.get(0).style.visibility = 'hidden';
				this.element_resize_parent = this.textarea;
			}
			else
			{
				this.element_resize = this.textarea;
				this.element_resize_parent = this.frame;
			}
	
			this.stopResizeHdl = function (e) { this.stopResize(e) }.bind(this);
			this.startResizeHdl = function (e) { this.startResize(e) }.bind(this);
			this.resizeHdl =  function (e) { this.resize(e) }.bind(this);
	
			$(document).mousedown(this.startResizeHdl);
			$(document).mouseup(this.stopResizeHdl);
			$(this.splitter).mouseup(this.stopResizeHdl);
	
			this.null_point = false;
			this.h_new = false;
			this.h = this.element_resize.height();
		},
		startResize: function()
		{
			$(document).mousemove(this.resizeHdl);
		},
		resize: function(e)
		{
			e.preventDefault();
			var y = e.pageY;
			if (this.null_point == false) this.null_point = y;
			if (this.h_new == false) this.h_new = this.element_resize.height();
	
			var s_new = (this.h_new + y - this.null_point) - 10;
	
			if (s_new <= 30) return true;
	
			if (s_new >= 0)
			{
				this.element_resize.get(0).style.height = s_new + 'px';
				this.element_resize_parent.get(0).style.height = s_new + 'px';
			}
	
		},
		stopResize: function(e)
		{
			$(document).unbind('mousemove', this.resizeHdl);
			$(document).unbind('mousedown', this.startResizeHdl);
			$(document).unbind('mouseup', this.stopResizeHdl);
			$(this.splitter).unbind('mouseup', this.stopResizeHdl);
			
			this.element_resize.get(0).style.visibility = 'visible';
		},
		
		
		/*
			Colorpicker
		*/		
		colorPicker: function(e)
		{			
			this.dialogOpen = false;
			
	
			if ($('#cmts_colorpicker_redactor').length) this.colorPickertoggle(e);
			else this.colorPickerbuild(e);			
		
		},
		colorPickerbuild: function(e)
		{
			this.dialog = $('<div>').attr('id', 'cmts_colorpicker_redactor').css({ display: 'none', position: 'absolute', 'border': '1px solid #ddd', padding: '4px', background: '#fff', zIndex: 10000 });
			var swatchTable = $('<div>').css({'overflow': 'hidden',  'width': '190px'});
	
			var len = this.opts.colors.length;
			for (var i = 0; i < len; ++i)
			{
				var color = this.opts.colors[i];
				
				var swatch = $('<div title="' + color + '"></div>').css({'width': '15px', 'float': 'left', cursor: 'pointer', 'height': '15px', 'fontSize': '1px', 'border': '2px solid #fff', 'backgroundColor': color, 'padding': '0'});		
				$(swatch).appendTo(swatchTable).click(function(e) { this.colorPickerset(e); }.bind(this));
			}
	
			$(swatchTable).appendTo(this.dialog);	
			$(document.body).append(this.dialog);

			this.colorPickershow(e);
		},
		colorPickerset: function(e)
		{	
			var color = $(e.target).attr('title');		

			RedactorActive.execCommand(RedactorColorMode, color);
	
			this.colorPickerhide(e);
		},
		colorPickertoggle: function(e)
		{			
			if (!this.dialogOpen) this.colorPickershow(e);
			else this.colorPickerhide(e);
		},
		colorPickershow: function(e)
		{	
			var el = $(e.target).parent().parent().parent();
			
			var height = $(el).height();
			var top = $(el).offset().top + height;
			var left = $(el).offset().left;						
			
			$('#cmts_colorpicker_redactor').css({ top: top + 'px', left: left + 'px',  display: '' }).fadeIn();	
			$(document).click( function(e) { this.colorPickerhide(e); }.bind(this));			
			$(this.doc).click( function(e) { this.colorPickerhide(e); }.bind(this));			
			this.dialogOpen = true;
		},
		colorPickerhide: function(e)
		{	
			if ($(e.target).hasClass('redactor_colortooltip')) return false;
		
			$(this.dialog).fadeOut();
			$(document).unbind('click', this.colorPickerhide);	
			$(this.doc).unbind('click', this.colorPickerhide);			
			this.dialogOpen = false;
		},
		
		/*
			Modal
		*/			
		modalInit: function(options)
		{
			this.modalOptions = {
				url: false,
				callback: false,
				end: false,
				loader: true,
				triggerClose: false,
				title: 'Modal Window',
				drag: false,
				width: 450,
				height: 450,
				overlay: true,
				overlayClose: true,
				fixed: true
			};
			
			$.extend(this.modalOptions, options);
			
			this.closeHandler = function() { this.modalHide(); }.bind(this);
			this.keypressHandler = function(e) { if( e.keyCode == 27) this.modalHide(); }.bind(this);	
	
	  		this.modalBuild();
	
			if ($.browser.msie) this.fixIE("100%", "hidden");
			
		},
		modalCreate: function()
		{
			this.modal = $('<div id="redactor_cmts_modal" style="display: none;"><div id="redactor_cmts_modal_header"><div id="redactor_cmts_modal_title"></div><span id="redactor_cmts_modal_close"></span></div><div id="redactor_cmts_modal_content"></div></div>');
			$(this.modal).appendTo('body');
			
			if (this.modalOptions.fixed) $('#redactor_cmts_modal').css('position', 'fixed');
			else $('#redactor_cmts_modal').css('position', 'absolute');		
	
			$('#redactor_cmts_modal').css({'margin-top': '-' + (this.modalOptions.height/2) + 'px', 'margin-left': '-' + (this.modalOptions.width/2) + 'px'});		
			$('#redactor_cmts_modal_close').click(this.closeHandler);	
		},
		modalOverlayCreate: function()
		{
			this.overlay = $('<div id="redactor_cmts_modal_overlay" style="display: none;"></div>');
			$(this.overlay).appendTo('body');	
		},	
		modalLoad: function()
		{
			this.modal.show();
			
			$('#redactor_cmts_modal_title').text(this.modalOptions.title);
			
			$('#redactor_cmts_modal').css({'height': this.modalOptions.height + 'px', 'width': this.modalOptions.width + 'px'});
	
			var pbottom = this.normalize($('#redactor_cmts_modal_content').css('padding-bottom'));
			var ptop = this.normalize($('#redactor_cmts_modal_content').css('padding-top'));
	
			var content_height = this.modalOptions.height - ptop - pbottom - $('#redactor_cmts_modal_header').get(0).offsetHeight;
	
			if (this.modalOptions.loader) $('#redactor_cmts_modal_content').css('height', content_height + 'px').html('<div id="credactor_mts_modal_loader"></div>');
			        
			$.ajax({ url: this.modalOptions.url, cache: false, success: function(data)
			{
				$('#redactor_cmts_modal_content').html(data);
	        	if (this.modalOptions.triggerClose) $('#' + this.modalOptions.triggerClose).mousedown(this.closeHandler);
	        	
	        	if (this.modalOptions.end) this.modalOptions.end();
	        	
			}.bind(this)});	
		    
			      	
			$(document).keypress(this.keypressHandler);
		},
		modalBuild: function()
		{
			if ($('#redactor_cmts_modal').get(0))
			{
				this.modal = $('#redactor_cmts_modal');
				if (this.modalOptions.overlay) this.overlay = $('#redactor_cmts_modal_overlay');
				this.modalShow();
			}
			else
			{
				this.modalCreate();
				if (this.modalOptions.overlay) this.modalOverlayCreate();
				this.modalShow();
			}
		},
		modalShow: function()
		{
			if (this.modalOptions.overlay && this.modalOptions.overlayClose) $(this.overlay).click(this.closeHandler);					  	
		  	
		  	if (this.modalOptions.overlay) 
		  	{
				this.overlay.show();
				this.modalLoad();
			}
			else this.modalLoad();
		},
		modalHide: function()
		{
			if (this.modalOptions.overlay) 
			{
				this.modal.hide();
				this.overlay.hide();
			}
			else this.modal.hide();
			
			if (jQuery.browser.msie) this.fixIE("", "");	
			if (this.modalOptions.overlayClose) $(this.overlay).unbind('click', this.closeHandler);
	
			$(document).unbind('keypress', this.keypressHandler);
	
			if (this.modalOptions.callback) this.modalOptions.callback();
			
		},
		fixIE: function(height, overflow)
		{
			$('html, body').css({'width': height, 'height': height, 'overflow': overflow});
			$("select").css('visibility', overflow);
		},	
		normalize: function(str)
		{
			return new Number((str.replace('px','')));
		},



		/*
			Upload
		*/	
		uploadInit: function(element, options)
		{
			/*
				Options
			*/
			this.uploadOptions = {
				url: false,
				success: false,
				start: false,
				trigger: false,
				auto: false,
				input: false
			};
	  
			$.extend(this.uploadOptions, options);
	
	
			/*
				Test input or form
			*/		
			if ($('#' + element).get(0).tagName == 'INPUT')
			{
				this.uploadOptions.input = $('#' + element);
				this.element = $($('#' + element).get(0).form);
			}
			else
			{
				this.element = $('#' + element);
			}
			
	
			this.element_action = this.element.attr('action');
	
			/*
				Auto or trigger
			*/
			if (this.uploadOptions.auto)
			{
				this.element.submit(function(e) { return false; });
				this.uploadSubmit();
			}
			else if (this.uploadOptions.trigger)
			{
				$('#' + this.uploadOptions.trigger).click(function() { this.uploadSubmit(); }.bind(this)); 
			}
		},
		uploadSubmit : function()
		{
			this.uploadForm(this.element, this.uploadFrame());
		},	
		uploadFrame : function()
		{
			this.id = 'f' + Math.floor(Math.random() * 99999);
		
			var d = document.createElement('div');
			var iframe = '<iframe style="display:none" src="about:blank" id="'+this.id+'" name="'+this.id+'"></iframe>';
			d.innerHTML = iframe;
			document.body.appendChild(d);
	
			/*
				Start
			*/
			if (this.uploadOptions.start) this.uploadOptions.start();
	
			$('#' + this.id).load(function () { this.uploadLoaded() }.bind(this));
	
			return this.id;
		},
		uploadForm : function(f, name)
		{
			if (this.uploadOptions.input)
			{
				var formId = 'redactorUploadForm' + this.id;
				var fileId = 'redactorUploadFile' + this.id;
				this.form = $('<form  action="' + this.uploadOptions.url + '" method="POST" target="' + name + '" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');	
	
				var oldElement = this.uploadOptions.input;
				var newElement = $(oldElement).clone();
				$(oldElement).attr('id', fileId);
				$(oldElement).before(newElement);
				$(oldElement).appendTo(this.form);
				$(this.form).css('position', 'absolute');
				$(this.form).css('top', '-1200px');
				$(this.form).css('left', '-1200px');
				$(this.form).appendTo('body');	
				
				this.form.submit();
			}
			else
			{
				f.attr('target', name);
				f.attr('method', 'POST');
				f.attr('enctype', 'multipart/form-data');		
				f.attr('action', this.uploadOptions.url);
	
				this.element.submit();
			}
	
		},
		uploadLoaded : function()
		{
			var i = $('#' + this.id);
			
			
			
			if (i.contentDocument) var d = i.contentDocument;
			else if (i.contentWindow) var d = i.contentWindow.document;
			else var d = window.frames[this.id].document;
			
			if (d.location.href == "about:blank") return true;
	
	
			/*
				Success
			*/
			this.imageUploadCallback(d.body.innerHTML);
	
			this.element.attr('action', this.element_action);
			this.element.attr('target', '');
			//this.element.unbind('submit');
			
			//if (this.uploadOptions.input) $(this.form).remove();
		}
						
	};
	

	String.prototype.isInlineName = function()
	{
		var inlineList = new Array("#text", "a", "em", "font", "span", "strong", "u");
		var theName = this.toLowerCase();
		
		for (var i = 0; i < inlineList.length; i++)
		{
			if (theName == inlineList[i])
			{
				return true;
			}
		}
		
		return false;
	};
	
	
	// bind
	Function.prototype.bind = function(object)
	{
	    var method = this; var oldArguments = $.makeArray(arguments).slice(1);
	    return function (argument)
	    {
	        if (argument == new Object) { method = null; oldArguments = null; }
	        else if (method == null) throw "Attempt to invoke destructed method reference.";
	        else { var newArguments = $.makeArray(arguments); return method.apply(object, oldArguments.concat(newArguments)); }
	    };
	};	
	

	
})(jQuery);






function tabs() {
	var s='';
	for (var j=0; j<LOOP_LEVEL; j++) s+='\t';
	return s;
}

function cleanTag(tag) {
	var tagout='';
	tag=tag.replace(/\n/g, ' ');       //remove newlines
	tag=tag.replace(/[\s]{2,}/g, ' '); //collapse whitespace
	tag=tag.split(' ');
	for (var j=0; j<tag.length; j++) {
		if (-1==tag[j].indexOf('=')) {
			//if this part doesn't have an equal sign, just lowercase it and copy it
			tagout+=tag[j].toLowerCase()+' ';
		} else {
			//otherwise lowercase the left part and...
			var k=tag[j].indexOf('=');
			var tmp=[tag[j].substr(0, k), tag[j].substr(k+1)];

			tagout+=tmp[0].toLowerCase()+'=';
			var x=tmp[1].charAt(0);
			if ("'"==x || '"'==x) {
				//if the right part starts with a quote, find the rest of its parts
				tagout+=tmp[1];
				while(x!=String(tag[j]).charAt(String(tag[j]).length-1)) {
					tagout+=' '+tag[++j];
				}
				tagout+=' ';
			} else {
				//otherwise put quotes around it
				tagout+="'"+tmp[1]+"' ";
			}
		}
	}
	tag=tagout.replace(/\s*$/, '>');
	return tag;
}

/////////////// The below variables are only used in the placeTag() function
/////////////// but are declared global so that they are read only once
//opening and closing tag on it's own line but no new indentation level
var ownLine=['area', 'body', 'head', 'hr', 'i?frame', 'link', 'meta', 'noscript', 'style', 'table', 'tbody', 'thead', 'tfoot'];

//opening tag, contents, and closing tag get their own line
//(i.e. line before opening, after closing)
var contOwnLine=['li', 'dt', 'dt', 'h[1-6]', 'option', 'script'];

//line will go before these tags
var lineBefore=new RegExp('^<(/?'+ownLine.join('|/?')+'|'+contOwnLine.join('|')+')[ >]');

//line will go after these tags
lineAfter=new RegExp('^<(br|/?'+ownLine.join('|/?')+'|/'+contOwnLine.join('|/')+')[ >]');

//inside these tags (close tag expected) a new indentation level is created
var newLevel=['blockquote', 'div', 'dl', 'fieldset', 'form', 'frameset', 'map', 'ol', 'p', 'pre', 'select', 'td', 'th', 'tr', 'ul'];
newLevel=new RegExp('^</?('+newLevel.join('|')+')[ >]');
function placeTag(tag, out) {
	var nl=tag.match(newLevel);
	if (tag.match(lineBefore) || nl) {
		out=out.replace(/\s*$/, '');
		out+="\n";
	}

	if (nl && '/'==tag.charAt(1)) LOOP_LEVEL--;
	if ('\n'==out.charAt(out.length-1)) out+=tabs();
	if (nl && '/'!=tag.charAt(1)) LOOP_LEVEL++;

	out+=tag;
	if (tag.match(lineAfter) || tag.match(newLevel)) {
		out=out.replace(/ *$/, '');
		out+="\n";
	}
	return out;
}

function CleanWHtml(html)
{ 

	var s = html.replace(/\r/g, '\n').replace(/\n/g, ' ');
	
	var rs = [];
	rs.push(/<!--.+?-->/g); // Comments
	rs.push(/<title>.+?<\/title>/g); // Title
	rs.push(/<(meta|link|.?o:|.?style|.?div|.?head|.?html|body|.?body|.?span|!\[)[^>]*?>/g); // Unnecessary tags
	rs.push(/ v:.*?=".*?"/g); // Weird nonsense attributes
	rs.push(/ style=".*?"/g); // Styles
	rs.push(/ class=".*?"/g); // Classes
	rs.push(/(&nbsp;){2,}/g); // Redundant &nbsp;s
	rs.push(/<p>(\s|&nbsp;)*?<\/p>/g); // Empty paragraphs
	$.each(rs, function() {
	    s = s.replace(this, '');
	});
	
	s = s.replace(/\s+/g, ' ');
	
	return s;


}






