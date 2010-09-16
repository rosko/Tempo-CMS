/********************************************************************
 * imgLib v0.1.1 03.02.2010
 * Contact me at dev@imglib.endofinternet.net
 * Site: http://www.imglib.endofinternet.net/
 * This copyright notice MUST stay intact for use.
 ********************************************************************/
var imgLibManager={init:function(a){a=a||{};this.width=a.width||600;this.height=a.height||500;this.returnTo=a.returnTo||'';this.url=a.url||'/scripts/imglib/index.html'},open:function(a,e,c,d){var b=window.location.search;if(b.length<1){b="?"}this.url=this.url+b+"&type="+c;tinyMCE.activeEditor.windowManager.open({file:this.url,title:'ImgLib v.0.1.1',width:this.width,height:this.height,resizable:"yes",inline:"no",close_previous:"no",popup_css:false},{window:d,input:a});return false}};