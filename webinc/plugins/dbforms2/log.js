dbforms2_log = function() {
}

dbforms2_log.init = function () {
    this.months = new Array(
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    )
}

dbforms2_log.log = function(msg) {
    if(_BX_HELPERS_IS_MOZ) {
        var msg = this.getDateStr()+" "+msg+"\n";
        dump(msg);
        if(typeof console != 'undefined') {
            console.log(msg);
        }
    }
}

dbforms2_log.getDateStr = function() {
    now = new Date();
    
    day = now.getDay();
    hours = now.getHours();
    minutes = now.getMinutes();
    seconds = now.getSeconds();
    
    day = day < 10 ? '0'+day : day;
    hours = hours < 10 ? '0'+hours : hours;
    minutes = minutes < 10 ? '0'+minutes : minutes;
    seconds = seconds < 10 ? '0'+seconds : seconds;
    
    return this.months[now.getMonth()]+' '+hours+':'+minutes+':'+seconds;
}
