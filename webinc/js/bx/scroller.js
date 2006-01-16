
const BX_SCROLL_UP = 0;
const BX_SCROLL_DOWN = 1;

function bx_scroller() {
    
    this.stepInterval = 20;
    this.stepPixels = 4;
    this.scrolling = false;
    this.scrollDirection = null; // 0 = up, 1 = down
    this.scrollNode = null;
    this.buttonUpNode = null;
    this.buttonDownNode = null;
    
    this.init = function(scrollNode, buttonUpNode, buttonDownNode) {

        this.scrollNode = scrollNode;
        this.buttonUpNode = buttonUpNode;
        this.buttonDownNode = buttonDownNode;

        // button up, mouse down
        var wev_buttonUpOnMouseDown = new bx_helpers_contextfixer(this.e_buttonUpOnMouseDown, this);
        bx_helpers.addEventListener(this.buttonUpNode, 'mousedown', wev_buttonUpOnMouseDown.execute);
        
        // button up, mouse up
        var wev_buttonUpOnMouseUp = new bx_helpers_contextfixer(this.e_buttonUpOnMouseUp, this);
        bx_helpers.addEventListener(this.buttonUpNode, 'mouseup', wev_buttonUpOnMouseUp.execute);
        
        // button down, mouse down
        var wev_buttonDownOnMouseDown = new bx_helpers_contextfixer(this.e_buttonDownOnMouseDown, this);
        bx_helpers.addEventListener(this.buttonDownNode, 'mousedown', wev_buttonDownOnMouseDown.execute);

        // button down, mouse up
        var wev_buttonDownOnMouseUp = new bx_helpers_contextfixer(this.e_buttonDownOnMouseUp, this);
        bx_helpers.addEventListener(this.buttonDownNode, 'mouseup', wev_buttonDownOnMouseUp.execute);
        
    }
    
    this.scrollUp = function() {
        this.scrollNode.scrollTop = this.scrollNode.scrollTop - this.stepPixels;
    }
    
    this.scrollDown = function() {
        this.scrollNode.scrollTop = this.scrollNode.scrollTop + this.stepPixels;
    }
    
    this.startScrolling = function(direction) {
        if(!this.scrolling) {
            this.direction = direction;
            this.scrolling = true;
    
            // start the interval
            var wrappedCallback = new bx_helpers_contextfixer(this._stepInterval, this);
            this.interval_stepInterval = window.setInterval(wrappedCallback.execute, this.stepInterval);
        }
    }
    
    this.stopScrolling = function() {
        this.scrolling = false;
        window.clearTimeout(this.interval_stepInterval);
    }
    
    this._stepInterval = function() {
        if(this.scrolling ) {
            if(this.direction == BX_SCROLL_UP) {
                this.scrollUp();
            } else {
                this.scrollDown();
            }
        }
    }
    
    
    // button up
    this.e_buttonUpOnMouseDown = function() {
        this.startScrolling(BX_SCROLL_UP);
    }
    
    this.e_buttonUpOnMouseUp = function() {
        this.stopScrolling();
    }
    
    // button down
    this.e_buttonDownOnMouseDown = function() {
        //alert('down/down');
        this.startScrolling(BX_SCROLL_DOWN);
    }
    
    this.e_buttonDownOnMouseUp = function() {
        this.stopScrolling();
    }
    
}

