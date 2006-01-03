function dbforms2_common() {
}

dbforms2_common.serializeToString = function(dom) {
    var serializer = new XMLSerializer();
    return serializer.serializeToString(dom);
}
