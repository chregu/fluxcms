// looks like I doesn't support const, so we use var here.

var BX_SCROLLER_SCROLL_UP = 0;
var BX_SCROLLER_SCROLL_DOWN = 1;
var BX_SCROLLER_SCROLL_RIGHT = 2;
var BX_SCROLLER_SCROLL_LEFT = 3;

var BX_SCROLLER_STOPPED = 0;
var BX_SCROLLER_SCROLLING = 1;
var BX_SCROLLER_STARTING = 4;
var BX_SCROLLER_STOPPING = 8;

function bx_scroller() {
    
    // scroll interval in ms
    this.stepInterval = 20;
    
    // pixels to move per interval (horizontal and vertical)
    this.ppi = 8;

    // used for acceleration during start and stop
    this.startAcceleration = 1;
    this.stopAcceleration = 0.4;
    this.currentPpi = 0;
    
    this.scrolling = BX_SCROLLER_STOPPED;
    this.scrollDirection = BX_SCROLLER_SCROLL_DOWN;

    this.scrollNode = null;
    this.buttonUpNode = null;
    this.buttonDownNode = null;
    
    this.init = function(scrollNode) {
        this.scrollNode = scrollNode;
    }
    
    this.attachVerticalButtons = function(buttonUpNode, buttonDownNode) {
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

    this.attachHorizontalButtons = function(buttonLeftNode, buttonRightNode) {
        this.buttonLeftNode = buttonLeftNode;
        this.buttonRightNode = buttonRightNode;
        
        // button left, mouse down
        var wev_buttonLeftOnMouseDown = new bx_helpers_contextfixer(this.e_buttonLeftOnMouseDown, this);
        bx_helpers.addEventListener(this.buttonLeftNode, 'mousedown', wev_buttonLeftOnMouseDown.execute);

        // button left, mouse up
        var wev_buttonLeftOnMouseUp = new bx_helpers_contextfixer(this.e_buttonLeftOnMouseUp, this);
        bx_helpers.addEventListener(this.buttonLeftNode, 'mouseup', wev_buttonLeftOnMouseUp.execute);
        
        // button right, mouse down
        var wev_buttonRightOnMouseDown = new bx_helpers_contextfixer(this.e_buttonRightOnMouseDown, this);
        bx_helpers.addEventListener(this.buttonRightNode, 'mousedown', wev_buttonRightOnMouseDown.execute);

        // button right, mouse up
        var wev_buttonRightOnMouseUp = new bx_helpers_contextfixer(this.e_buttonRightOnMouseUp, this);
        bx_helpers.addEventListener(this.buttonRightNode, 'mouseup', wev_buttonRightOnMouseUp.execute);
    }
    
    this.scrollUp = function(ppi) {
        this.scrollNode.scrollTop = this.scrollNode.scrollTop - ppi;
    }
    
    this.scrollDown = function(ppi) {
        this.scrollNode.scrollTop = this.scrollNode.scrollTop + ppi;
    }
    
    this.scrollLeft = function(ppi) {
        this.scrollNode.scrollLeft = this.scrollNode.scrollLeft - ppi;
    }
    
    this.scrollRight = function(ppi) {
        this.scrollNode.scrollLeft = this.scrollNode.scrollLeft + ppi;
    }
    
    this.startScrolling = function(direction) {

        // stop any running interval
        this.clearInterval();
        
        this.direction = direction;
        this.scrolling = BX_SCROLLER_SCROLLING | BX_SCROLLER_STARTING;
        this.currentPpi = 0;

        // start the interval
        this.startInterval();
    }
    
    this.stopScrolling = function() {
        this.scrolling = BX_SCROLLER_SCROLLING | BX_SCROLLER_STOPPING;
    }
    
    this.startInterval = function() {
        var wrappedCallback = new bx_helpers_contextfixer(this._stepInterval, this);
        this.interval_stepInterval = window.setInterval(wrappedCallback.execute, this.stepInterval);
    }
    
    this.clearInterval = function() {
        if(this.interval_stepInterval)
            window.clearTimeout(this.interval_stepInterval);
    }

    this._stepInterval = function() {
        
        if(this.scrolling & BX_SCROLLER_SCROLLING) {
            
            if(this.scrolling & BX_SCROLLER_SCROLLING) {
                
                if(this.direction == BX_SCROLLER_SCROLL_UP) {
                    this.calcAcceleration();
                    this.scrollUp(this.currentPpi);
                } else if(this.direction == BX_SCROLLER_SCROLL_DOWN) {
                    this.calcAcceleration();
                    this.scrollDown(this.currentPpi);
                } else if(this.direction == BX_SCROLLER_SCROLL_LEFT) {
                    this.calcAcceleration();
                    this.scrollLeft(this.currentPpi);
                } else if(this.direction == BX_SCROLLER_SCROLL_RIGHT) {
                    this.calcAcceleration();
                    this.scrollRight(this.currentPpi);
                }
            }
        }
    }
    
    this.calcAcceleration = function() {
        
        if(this.scrolling & BX_SCROLLER_STARTING) {
            this.currentPpi = this.currentPpi + this.startAcceleration;
            if(this.currentPpi >= this.ppi) {
                // stop accelerating
                this.scrolling = BX_SCROLLER_SCROLLING;
                this.currentPpi = this.ppi;
            }
        } else if(this.scrolling & BX_SCROLLER_STOPPING) {
            this.currentPpi = this.currentPpi - this.stopAcceleration;
            if(this.currentPpi <= this.stopAcceleration) {
                // stop accelerating
                this.scrolling = BX_SCROLLER_STOPPED;
                this.clearInterval();
            }
        }
    }
    
    // button up
    this.e_buttonUpOnMouseDown = function() {
        this.startScrolling(BX_SCROLLER_SCROLL_UP);
    }
    this.e_buttonUpOnMouseUp = function() {
        this.stopScrolling();
    }
    
    // button down
    this.e_buttonDownOnMouseDown = function() {
        this.startScrolling(BX_SCROLLER_SCROLL_DOWN);
    }
    this.e_buttonDownOnMouseUp = function() {
        this.stopScrolling();
    }
    
    // button left
    this.e_buttonLeftOnMouseDown = function() {
        this.startScrolling(BX_SCROLLER_SCROLL_LEFT);
    }
    this.e_buttonLeftOnMouseUp = function() {
        this.stopScrolling();
    }
    
    // button right
    this.e_buttonRightOnMouseDown = function() {
        this.startScrolling(BX_SCROLLER_SCROLL_RIGHT);
    }
    this.e_buttonRightOnMouseUp = function() {
        this.stopScrolling();
    }
    
}

