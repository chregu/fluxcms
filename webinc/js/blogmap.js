var marker = [];

// Spreadsheets API timing needs more study
var teksti = " ";
function sideBar(line, j){
teksti += "<br/><span class='sidebar'";
teksti += "onclick='GEvent.trigger(marker["+j+"],\"click\")' ";
teksti += "onmouseover='GEvent.trigger(marker["+j+"],\"mouseover\")' ";
teksti += "onmouseout='GEvent.trigger(marker["+j+"],\"mouseout\")' ";
teksti += ">";
teksti += line;
teksti += "</span>";
document.getElementById("sidebar").innerHTML = teksti;
}

/*function createMarker(point, number, title, link, image, content, author, date) {
  //var inArray = false;
  var marker = new GMarker(point);
        
  GEvent.addListener(marker, "click", function() {
    
    marker.openInfoWindowHtml(
    "<div style=\"height: 200px; width:400px; padding-bottom:20px;\"><b><a href='"+ link +"'>" + title + "</a></b>"+
    "<br/>"+ author +" @ "+ date +
    "<br/><img src='"+ image +"' alt='"+ image +"' width='100'/><br/>"+ content +"</div>"
    );
    
  });
  
  return marker;
}*/

function load() {
    
    if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));
        bounds = new GLatLngBounds();
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        new GKeyboardHandler(map);
        map.enableContinuousZoom();
        map.enableDoubleClickZoom();
        
        //blueIcon = new GIcon(G_DEFAULT_ICON);
        markerImage = [
        "/themes/standard/admin/images/marker/marker.png",
        "/themes/standard/admin/images/marker/dd-start.png",
        "/themes/standard/admin/images/marker/dd-end.png",
        "/themes/standard/admin/images/marker/markeryellow.png",
        "/themes/standard/admin/images/marker/temp_marker.png"
        ];
        
        for (var i = 0; i < locations.length; ++i) {
            if(locations[i] != undefined) {
                if(i == 1) {
                    map.setCenter(new GLatLng(locations[i]['lat'], locations[i]['lon']));
                }
                var html = "<div style=\"text-align:left;height: 150px; width:400px; padding-bottom:20px;\"><b><a href='"+ locations[i]['link'] +"'>" + locations[i]['title'] + "</a></b>"+
                "<br/>"+ locations[i]['author'] +" @ "+ locations[i]['date'] +
                "<br/><img src='"+ locations[i]['image'] +"' alt='"+ locations[i]['image'] +"' width='100'/><br/>"+ locations[i]['content'] +"</div>";
                
                var label = locations[i]['title'];
                
                var point = new GLatLng(locations[i]['lat'], locations[i]['lon']);
                if(selectedPost == locations[i]['id']) {
                    sideBar(label,i);
                    ZMarker(point,html,1,0,i,null,1);
                } else {
                    sideBar(label,i);
                    ZMarker(point,html,1,0,i,null,null);
                }
                //marker = createMarker(point, i + 1, locations[i]['title'], locations[i]['link'], locations[i]['image'], locations[i]['content'], locations[i]['author'], locations[i]['date']);
                
                bounds.extend(point);
                
            }
        }
        map.setZoom(map.getBoundsZoomLevel(bounds));
        var clat = (bounds.getNorthEast().lat() + bounds.getSouthWest().lat()) /2;
        var clng = (bounds.getNorthEast().lng() + bounds.getSouthWest().lng()) /2;
        map.setCenter(new GLatLng(clat,clng));
        
        map.setCenter(bounds.getCenter(),map.getBoundsZoomLevel(bounds));
        map.zoomOut();
    }
}



/*

Z MARKER 

*/


// A special createZMarker function
// 'infowindowclose' listener is attached to marker
// It deletes the marker and creates a copy with lower z-index
// Feel free to use but please include:
// Originally created by Esa 2007

var n=1;
function count(){
n++;
return n;
}
function ZMarker(point,label,n,imInd,i,visited,open) {
function sendBack(marker,b) {
return GOverlay.getZIndex(marker.getPoint().lat())-n*10000;
}
marker[i] = new GMarker(point,{title:label, zIndexProcess:sendBack});
map.addOverlay(marker[i]);
marker[i].setImage(markerImage[imInd]);
marker[i].visited = visited;

if(open == 1) {
    marker[i].openInfoWindowHtml(label);
    marker[i].visited = true;
    GEvent.trigger(marker[i],"mouseout");
} else {
    GEvent.addListener(marker[i], "click", function() {
    marker[i].openInfoWindowHtml(label);
    marker[i].visited = true;
    GEvent.trigger(marker[i],"mouseout");
    });
}
GEvent.addListener(marker[i],'mouseover',function(){
marker[i].setImage(markerImage[3]);
document.getElementById("sidebar").getElementsByTagName("span")[i-1].style.background ="yellow";
});
GEvent.addListener(marker[i],'mouseout',function(){
if(marker[i].visited){
marker[i].setImage(markerImage[4]);
document.getElementById("sidebar").getElementsByTagName("span")[i-1].style.color ="gray";

}else{
    //marker[i].setImage(markerImage[2]);
    //marker[i].setImage(new GIcon(G_DEFAULT_ICON));
    marker[i].setImage(markerImage[0]);
    document.getElementById("sidebar").getElementsByTagName("span")[i-1].style.color ="black";
}
document.getElementById("sidebar").getElementsByTagName("span")[i-1].style.background ="white";
});
GEvent.addListener(marker[i], "infowindowclose", function() {
map.removeOverlay(marker[i]);

bounds.extend(point);

map.setZoom(map.getBoundsZoomLevel(bounds));
var clat = (bounds.getNorthEast().lat() + bounds.getSouthWest().lat()) /2;
var clng = (bounds.getNorthEast().lng() + bounds.getSouthWest().lng()) /2;
map.setCenter(new GLatLng(clat,clng));

map.setCenter(bounds.getCenter(),map.getBoundsZoomLevel(bounds));
map.zoomOut();

ZMarker(point,label,count(), 4,i,marker[i].visited);
})}


//Spreadsheets API callback

function handleJS(root) {
feed = root.feed;
}
