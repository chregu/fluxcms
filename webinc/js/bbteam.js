function makeDiv(request){
    document.write(request.responseText);
}

function nameSubmit(id) {
    alert(id);
    new ajax ('http://bb-vorsorge/inc/bx/php/makeTeamDiv.php', {
	postBody: name,
    method: 'post', 
    onComplete: makeDiv
    });
    return false;
}
