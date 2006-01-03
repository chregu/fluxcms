function onChangeSelect(select,subname) {
	var options = document.forms[0][subname].options;
	var selected = false;
	var firstNotSelected = null
	for (var i = 0;i <  options.length; i++) {
       
		if (options[i].getAttribute("parentName") != select.value) {
				options[i].style.display = "none";
				options[i].text = " (from another theme)";
				options[i].style.color = "#dddddd";
		} else {
        
			options[i].style.display = "block";
			options[i].text = options[i].value;
			options[i].style.color = "black";
            changePicture(select.value, document.forms[0][subname].value);
			if (options[i].getAttribute("selected")) {
				selected = true;
				options[i].selected = true;
			} else {
				options[i].selected = false;
				if (!firstNotSelected) {
					firstNotSelected = options[i];
				}
			}
		}
	}
	if (!selected) {
		firstNotSelected.selected = true;
    }
   
    
    changePicture(select.value, document.forms[0][subname].value);
}


function onChangeSubSelect(select, parentname) {
    
    changePicture(document.forms[0][parentname].value, select.value);
    
    
    
}

function changePicture(theme, css) {
    // very site-options theme centric 
    var img = document.getElementById("themePreview");
    img.src = "/themes/"+ theme + "/preview/"+css.replace(/\.css$/,".jpg");
    
    img = document.getElementById("themePreview_gross");
    img.src = "/themes/"+ theme + "/preview/"+css.replace(/\.css$/,".jpg");
    
}
