function initLists() {
	
	
	
	initList(0);
	initList(1);
	initList(2);
	
}


function initList(nr) {
	Sortable.create('list'+nr, {ghsting: true,
		dropOnEmpty:true,containment:["list0","list1","list2"],constraint:false,
        onUpdate:function() {
			//new Effect.Highlight('list'+nr,{});
			
			
            new Ajax.Updater('list-info', 
			'./', 
			{ onComplete: function(request) { 
				//	new Effect.Highlight('list'+nr,{});
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
	alert(id);
	return false;
}

