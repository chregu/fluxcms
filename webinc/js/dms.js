function cat(){
}

function copy(path){
     var copy = prompt('Please enter your new Path here', path);
     if (copy) {
         var copylog = prompt('Please enter your Comment here', 'Copied from '+path+' to '+copy);
         if(copylog){
             window.location.href=path + '?copyto=' + copy+'&copylog='+copylog;
         }
     }
}

function del(path){
    var del = confirm("Are you sure?");
    if(del){
        var dellog = prompt('Please enter your Comment here', 'Deleted');
        if(dellog){
            window.location.href=path + '?delete=1&dellog='+dellog;
        }
    }else{
        return 0;
    }
}

function move(path){
     var move = prompt('Please enter your new Path here', path);
     if (move) {
         var movelog = prompt('Please enter your Comment here', 'Moved from '+path+' to '+move);
         if(movelog){
             window.location.href=path + "?moveto=" + move+'&movelog='+movelog;
         }
     }
}

function mkdir(path){
     var move = prompt('Please enter yoour folder name here', path);
     if (move) {
         window.location.href=path + "?mkdir=" + move;
     }
}

function openclose(name){
    var tr = document.getElementById(name);
    if (tr.style.display == "none") {
        show(name);
    } else {
        close(name);
    }
}

function show(name){
    document.getElementById(name).style.display = "";
}

function close(name){
    document.getElementById(name).style.display = "none";
}
