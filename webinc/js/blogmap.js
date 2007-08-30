function createMarker(point, number, title, link, image, content, author, date) {
  var marker = new GMarker(point);
  GEvent.addListener(marker, "click", function() {
    marker.openInfoWindowHtml(
        "<div style=\"height: 200px; width:400px; padding-bottom:20px;\"><b><a href='"+ link +"'>" + title + "</a></b>"+
        "<br/>"+ author +" @ "+ date +
        "<br/><img src='"+ image +"' alt='"+ image +"' width='100'/><br/>"+ content +"</div>"
     );
  });
  return marker;
}

function load() {
    if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        var bounds = new GLatLngBounds();
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        

        for (var i = 0; i < locations.length; ++i) {
            if(locations[i] != undefined) {
                if(i == 1) {
                    map.setCenter(new GLatLng(locations[i]['lat'], locations[i]['lon']));
                }
                var point = new GLatLng(locations[i]['lat'], locations[i]['lon']);
                
                map.addOverlay(createMarker(point, i + 1, locations[i]['title'], locations[i]['link'], locations[i]['image'], locations[i]['content'], locations[i]['author'], locations[i]['date']));
                bounds.extend(point);
            }
        }
        map.setZoom(map.getBoundsZoomLevel(bounds));
        var clat = (bounds.getNorthEast().lat() + bounds.getSouthWest().lat()) /2;
        var clng = (bounds.getNorthEast().lng() + bounds.getSouthWest().lng()) /2;
        map.setCenter(new GLatLng(clat,clng));

        //map.setCenter(new GLatLng(37.4419, -122.1419), 13);
    }
}

