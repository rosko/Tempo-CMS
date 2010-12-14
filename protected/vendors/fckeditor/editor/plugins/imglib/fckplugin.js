
var a,b=['Link','Image','Flash'];
for(a in b) {
    FCKConfig[b[a]+'BrowserURL']=FCKConfig.BasePath+'plugins/imglib/index.html#returnto=txtUrl&caller_type='+b[a];
    FCKConfig[b[a]+'UploadURL']=FCKConfig.BasePath+'plugins/imglib/imglib.php?fckeditor=1';
}
