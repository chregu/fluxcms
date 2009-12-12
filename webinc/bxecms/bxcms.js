/* here we have bxcms specific functions */
function bxe_onSaveFileCreated(url) {
	window.parent.navi.Navitree.reload(url);
}