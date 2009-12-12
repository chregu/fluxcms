var _BX_HELPERS_IS_IE = (navigator.userAgent.toLowerCase().indexOf("msie") > -1)?true:false;
var _BX_HELPERS_IS_MOZ = (document.implementation && document.implementation.createDocument && (navigator.userAgent.toLowerCase().indexOf('opera') == -1)) ? true : false;

bx_helpers = function() {
}


bx_helpers.addEventListener = function(node, event, callback) {
    if(_BX_HELPERS_IS_IE) {
        node.attachEvent('on'+event, callback);
    } else if(_BX_HELPERS_IS_MOZ){
        node.addEventListener(event, callback, false);
    }
}


/* ContextFixer, fixes a problem with the prototype based model

    When a method is called in certain particular ways, for instance
    when it is used as an event handler, the context for the method
    is changed, so 'this' inside the method doesn't refer to the object
    on which the method is defined (or to which it is attached), but for
    instance to the element on which the method was bound to as an event
    handler. This class can be used to wrap such a method, the wrapper 
    has one method that can be used as the event handler instead. The
    constructor expects at least 2 arguments, first is a reference to the
    method, second the context (a reference to the object) and optionally
    it can cope with extra arguments, they will be passed to the method
    as arguments when it is called (which is a nice bonus of using 
    this wrapper).
*/

// use this one for new code, ContextFixer should be deprecated one day...
var bx_helpers_contextfixer = ContextFixer;

function ContextFixer(func, context) {
    /* Make sure 'this' inside a method points to its class */
    this.func = func;
    this.context = context;
    this.args = arguments;
    var self = this;
    
    this.execute = function() {
        /* execute the method */
        var args = new Array();
        // the first arguments will be the extra ones of the class
        for (var i=0; i < self.args.length - 2; i++) {
            args.push(self.args[i + 2]);
        };
        // the last are the ones passed on to the execute method
        for (var i=0; i < arguments.length; i++) {
            args.push(arguments[i]);
        };
        self.func.apply(self.context, args);
    };
};

