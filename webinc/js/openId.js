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
  
  if ($('bx_fw[name]').value == '') {
      $('bx_fw[name]').value = name;
  }
  if ($('bx_fw[email]').value == '') {
      $('bx_fw[email]').value = email;
  }
  document.getElementById('verify').setAttribute("value","verified");
}

function immediate() {

}
