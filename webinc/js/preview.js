function preview(request){
    //
    var previewNode = $('previewComment');
    
    if (!previewNode) {
        var lastComment = $('commentForm');
        var previewNode = document.createElement("div");
        previewNode.id = 'previewComment';
        previewNode.className = 'post_content';
        previewNode = lastComment.parentNode.insertBefore(previewNode,lastComment.nextSibling);
        
    }
    previewNode.innerHTML = request.responseText;
    location.hash = 'previewComment';
    //document.getElementById('preview').value =request.responseText;
}


function previewSubmit(test) {
    var name = document.getElementById('name').value;
    var mail = document.getElementById('email').value;
    var uri = document.getElementById('openid_url').value;
    var text = document.getElementById('comment_text').value;
    
    var f = document.forms['commentForm'];
    
    body = "mail=" + encodeURIComponent(mail) + "&uri="+ encodeURIComponent(uri) + "&text="+ encodeURIComponent(text) + "&name="+ encodeURIComponent(name); 
    new ajax (liveSearchRoot + 'inc/bx/php/preview.php', {
        postBody: body,
		method: 'post', 
		onComplete: preview
    });
    
    
    return false;
}
