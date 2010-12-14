FCKConfig.ToolbarSets["CMS"] = [
['Bold','Italic','StrikeThrough'],
['FontFormat'],
['OrderedList','UnorderedList','-','Subscript','Superscript','-','Outdent','Indent','Blockquote'],
['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
['Source','Templates'],
'/',
['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
['Link','Unlink'],
['Image','Flash','Table','Rule','SpecialChar'],
['Cut','Copy','Paste','PasteText','PasteWord','-','Print'],
['TextColor','BGColor'],
['ShowBlocks'] 
] ;

// 'Style',

/*
FCKConfig.CustomStyles = 
{

'Red Title' : { Element : 'h3', Styles : { 'color' : 'Red' } },
'MY STYLE 1' : {Element :'h2', Styles : {'color' : 'Blue' , 'background-color' : 'Red' } },

	// Basic Inline Styles.
	'Bold'			: { Element : 'b', Overrides : 'strong' },
	'Italic'		: { Element : 'i', Overrides : 'em' },
	'Underline'		: { Element : 'u' },
	'StrikeThrough'	: { Element : 'strike' },
	'Subscript'		: { Element : 'sub' },
	'Superscript'	: { Element : 'sup' },
	
	// Basic Block Styles (Font Format Combo).
	'p'				: { Element : 'p' },
	'div'			: { Element : 'div' },
	'pre'			: { Element : 'pre' },
	'address'		: { Element : 'address' },
	'h1'			: { Element : 'h1' },
	'h2'			: { Element : 'h2' },
	'h3'			: { Element : 'h3' },
	'h4'			: { Element : 'h4' },
	'h5'			: { Element : 'h5' },
	'h6'			: { Element : 'h6' },
	
	// Other formatting features.
	'FontFace' : 
	{ 
		Element		: 'span', 
		Styles		: { 'font-family' : '#("Font")' }, 
		Overrides	: [ { Element : 'font', Attributes : { 'face' : null } } ]
	},
	
	'Size' :
	{ 
		Element		: 'span', 
		Styles		: { 'font-size' : '#("Size","fontSize")' }, 
		Overrides	: [ { Element : 'font', Attributes : { 'size' : null } } ]
	},
	
	'Color' :
	{ 
		Element		: 'span', 
		Styles		: { 'color' : '#("Color","color")' }, 
		Overrides	: [ { Element : 'font', Attributes : { 'color' : null } } ]
	},
	
	'BackColor'		: { Element : 'span', Styles : { 'background-color' : '#("Color","color")' } }
};

*/

FCKConfig.Plugins.Add('imglib');