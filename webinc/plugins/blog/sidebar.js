function initLists() {
	
	
	
	initList(0);
	initList(1);
	initList(2);
	
}


function initList(nr) {
	Sortable.create('list'+nr, {
		dropOnEmpty:true,containment:["list0","list1","list2"],constraint:false,
        onUpdate:function() {
			//new Effect.Highlight('list'+nr,{});
			
			
            new Ajax.Request( 
			'./listupdate/', 
			{ onComplete: function(request) { 
					new Effect.Opacity('list-info',
					{ 	duration: 2, 
						
					from: 1.0, to: 0 });
			}, 
			parameters: serializeList(nr), 
			evalScripts:false, 
			asynchronous:true
			}
			)
			
		}
	}
	)
}

function serializeList(nr) {
	
	var params =  Sortable.serialize('list'+nr).replace(/list([0-9]+)\[\]=/g,'bx[plugins][admin_edit][sidebar][$1][]=');
	
	if (params.length == 0) {
		return 	'bx[plugins][admin_edit][sidebar]['+nr+']=';
	}
	return params;
	
	
}


function editItem(id) {
	
	var myAjaxObj = new Ajax.Request(
	'./edit/',
	{
		
		parameters: 'id='+ id,
		onComplete: showEdit,
		
	}
	);
	
	return false;
}

function showEdit(request) {
	var data = eval("(" + request.responseText.replace(/<\?xml.*?>/,"") + ")");
	
	$('sidebar_edit').style.display="block";
	
	$('name').value = data.name;
	$('content').value = data.content;
	$('id').value = data.id;
}


function sendEdit() {
	var myAjaxObj = new Ajax.Request(
	
	'./sendedit/',
	{
		
		parameters: Form.serialize( $('editform')),
		onComplete: editCompleted
	}
	);
	
	return false;
}

function createNew() {
	$('sidebar_edit').style.display="block";
	$('name').value = "noname";
	$('content').value = "";
	$('id').value = "";
}


function editCompleted(request) {
	
	var data = eval("(" + request.responseText.replace(/<\?xml.*?>/,"") + ")");
	
	var listId = "list0"; 
	if ($('item_'+data.id)) {
		var li = $('item_'+data.id);
		var listId = li.parentNode.id;
		li.innerHTML = data.content;
	} else {
		$('id').value = data.id;
		var li = $(listId).appendChild(document.createElement("li"));
		li.id = 'item_'+data.id;
		li.innerHTML = data.content;
		initList(0);
	}
	
}

function deleteEntry() {
	
	var myAjaxObj = new Ajax.Request(
	
	'./delete/',
	{
		
		parameters: "bx[plugins][admin_edit][delete]="+ $('id').value,
		onComplete: deleteCompleted
	}
	);
	
}

function deleteCompleted(request) {
	var data = eval("(" + request.responseText.replace(/<\?xml.*?>/,"") + ")");
	
	var li = $('item_'+data.id);
	if (li) {
		li.parentNode.removeChild(li);
	}
}

