function openId(request){
  loginWindow = window.open(request.responseText);
}

function openIdSubmit() {
    var uri = document.getElementById('openid_url').value;
    new ajax (liveSearchRoot + 'inc/bx/php/openid/start_auth.php', {
	postBody: uri,
    method: 'post', 
    onComplete: openId
    });
}

function openIdOk(name, email) {
  
  if ($('name').value == '') {
      $('name').value = name;
  }
  if ($('email').value == '') {
      $('email').value = email;
  }
  document.getElementById('verify').setAttribute("value","ok");
}

function immediate() {

}
