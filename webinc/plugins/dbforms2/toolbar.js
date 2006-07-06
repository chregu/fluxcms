dbforms2_toolbar = function() {

    this.buttons = new Array();
    
    this.addButtonEventHandler = function(bname, handler) {
        bx_helpers.addEventListener(this.buttons[bname], 'click', handler);
    }
    
    this.setButton = function(bname, bobject) {
        this.buttons[bname] = bobject;
    }
    
    this.lockButton = function(bname) {
        /*
        this.buttons[bname].disabled = true;
        this.buttons[bname].className = 'disabled';
        */
    }
    
    this.unlockButton = function(bname) {
        this.buttons[bname].disabled = false;
        this.buttons[bname].className = '';
    }
    
    this.lockAllButtons = function() {
        for(bname in this.buttons) {
            this.lockButton(bname);
        }
    }
    
    this.unlockAllButtons = function() {
        for(bname in this.buttons) {
            this.unlockButton(bname);
        }
    }
    
    this.lockButtons = function(bnames) {
        for(bname in bnames) {
            this.lockButton(bnames[bname]);
        }
    }

    this.unlockButtons = function(bnames) {
        for(bname in bnames) {
            this.unlockButton(bnames[bname]);
        }
    }
        
        
}
