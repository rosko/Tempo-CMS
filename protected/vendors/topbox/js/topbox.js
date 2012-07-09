/*
 * Topbox v1.01
 * published under the MIT - License
 * author: Artur Heinze
 */

(function($){

    var $this = null;
    
    $.topbox = $this = {
        
        instances: [],
        //box: null,
        options: {},
        persist: false,
        
        show: function(content, options) {
            
            if (this.instances.length>0) {
                //box = this.instances[this.instances.length-1];
            }
            //if(box) {this.clear();}
            var box;
            
            this.options = $.extend({
                'title'     : false,
                'closeOnEsc': true,
                'theme'     : 'default',
                'height'    : 'auto',
                'width'     : 'auto',
                'speed'     : 500,
                'easing'    : 'swing',
                'buttons'   : false,

                //events
                'beforeShow'  : function(){},
                'onShow'      : function(){},
                'beforeClose' : function(){},
				'onClose'     : function(){}
            },options);
			
            var tplDlg = '<div class="topbox-window '+$this.options.theme+'">';
                tplDlg+=  '<div class="topbox-closebutton"></div>';
                tplDlg+=  '<div class="topbox-title" style="display:none;"></div>';
                tplDlg+=  '<div class="topbox-content"><div class="topbox-innercontent"></div></div>';
                tplDlg+=  '<div class="topbox-buttonsbar"><div class="topbox-buttons"></div></div>';
                tplDlg+= '</div>';
            
            box = $(tplDlg);
            this.instances.push(box);
      
            box.find(".topbox-closebutton").bind("click",function(){
                $this.close();
            });
            
            if(this.options.buttons){
                
                var btns = box.find(".topbox-buttons");
                
                $.each(this.options.buttons, function(caption, fn){
                    
					$('<button type="button" class="topbox-button">'+caption+'</button>').bind("click", function(e){
						e.preventDefault();
						fn.apply(this);
                    }).appendTo(btns);
                });
            }else{
               box.find(".topbox-buttonsbar").hide(); 
            }
            
            if($this.options.height != 'auto'){
                box.find(".topbox-innercontent").css({
                  'height'    : $this.options.height,
                  'overflow-y': 'auto'
                });
            }
            
            if($this.options.width != 'auto'){
                box.find(".topbox-innercontent").css({
                  'width'     : $this.options.width,
                  'overflow-x': 'auto'
                });
            }
      
            this.setContent(content).setTitle(this.options.title);
			
			this.options.beforeShow.apply(this);
			
            box.css({
                'opacity'   : 0,
                'visibility': 'hidden'
            })
            .appendTo("#cms-modal .cms-modal-inner")
            .css({
                'left' : ($(window).width()/2-box.width()/2),
                'top'  : ((-1.5) * box.height())
            }).css({
                'visibility': 'visible'
            }).animate({
                top: 0,
                opacity: 1
            }, this.options.speed, this.options.easing, function(){
            
                //focus
                if(box.find(":input:first").length) {
                    box.find(":input:first").focus();
                }                
                $this.options.onShow.apply(this);
            
            });
            
            $(window).bind('resize.topbox', function(){
                box.css({
                    'left': ($(window).width()/2-box.width()/2)
                });
            });
            
            // bind esc
            if(this.options.closeOnEsc){
                $(document).bind('keydown.topbox', function (e) {
                    if (e.keyCode === 27) { // ESC
                        e.preventDefault();
                        $this.close();
                    }
                });
            }
            this.instances[this.instances.length-1] = box;
            
            this.showOverlay();
			
            return this;
        },
        
        close: function(){
            
            if (this.instances.length>0) {
                var box = this.instances[this.instances.length-1];
            }
            if(!box) {return;}
            
            if(this.options.beforeClose.apply(this)===false){
                return this;
            }
            if (this.instances.length<2) {
                this.overlay.fadeOut();
            }
            
            box.animate({ 
                'top'  : ((-1.5) * box.height()),
                'opacity': 0
            }, this.options.speed, this.options.easing, function(){
                $this.clear();
            });
			
			this.options.onClose.apply(this);

            return this;
        },
		
		'confirm': function(content, fn, options){
			
			var options = $.extend({
				title : "Please confirm",
				buttons: {
					Ok: function(){
						fn.apply($this);
					},
					
					Cancel: function(){
						this.close();
					}
				}
			}, options);
			
			this.show(content, options);
		
		},
		
		'alert': function(content, options){
			
			var options = $.extend({
				title : "Alert",
				buttons: {
					Ok: function(){
						this.close();
					}
				}
			}, options);
			
			this.show(content, options);
		},
        
        clear: function(){
            
            if (this.instances.length>0) {
                var box = this.instances[this.instances.length-1];
            }
            if(!box) {return;}
            
            if (this.persist) {
//                this.persist.appendTo(this.persist.data("tb-persist-parent"));
                this.persist = false;
            }
            
            this.instances[this.instances.length-1].remove();
            this.instances.pop();
            //box.remove();
            //box = null;
            
            if (this.instances.length==0) {
                if(this.overlay){
                    this.overlay.hide();
                }
            
                $(window).unbind('resize.topbox');
                $(document).unbind('keydown.topbox');
            }
            
            return this;
        },
        
        setTitle: function(title){ 
          
            if (this.instances.length>0) {
                var box = this.instances[this.instances.length-1];
            }
          if(!box) {return;}
          
          if(title){
            box.find(".topbox-title").html(title).show();
          }else{
            box.find(".topbox-title").html(title).hide();
          }
          
          return this;
        },

        setContent: function(content){ 
            
            if (this.instances.length>0) {
                var box = this.instances[this.instances.length-1];
            }
            if(!box) {return;}
            
            if (typeof content === 'object') {
				// convert DOM object to a jQuery object
				content = content instanceof jQuery ? content : $(content);
                
                if(content.parent().length) {
                    this.persist = content;
                    this.persist.data("tb-persist-parent", content.parent());
                }
			}
			else if (typeof content === 'string' || typeof content === 'number') {
				// just insert the data as innerHTML
				content = $('<div></div>').html(content);
			}
			else {
				// unsupported data type!
				content = $('<div></div>').html('SimpleModal Error: Unsupported data type: ' + typeof content);
			}
          
            content.appendTo(box.find(".topbox-innercontent").html(''));

            return this;
        },
        
        showOverlay: function(){
            
            if (this.instances.length>0) {
                var box = this.instances[this.instances.length-1];
            }
            if(!box) {return;}
            
            if(!this.overlay){
                if(!$("#topbox-overlay").length) {
                    $("<div>").attr('id','topbox-overlay').css({
                        top: 0,
                        left: 0,
                        position: 'absolute'
                    }).prependTo('body');
                                        
                }
                
                this.overlay = $("#topbox-overlay");
            }
            
            this.overlay.css({
                width: $(document).width(),
                height: $(document).height()
            }).show();
        }
    };

    $.fn.topbox = function() {

        var args    = arguments;
        var options = args[0] ? args[0] : {};

        return this.each(function() {
            $.topbox.show(this, options);
        });
    };
})(jQuery);