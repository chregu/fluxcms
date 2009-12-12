bx_string = function() {
}

bx_string.stripHtmlTags = function(instr) {
    return instr.replace(/\<[^\>]+\>/ig, '');
}

