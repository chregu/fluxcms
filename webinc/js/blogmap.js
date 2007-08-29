function createMarker(point, number, title, link, image) {
  var marker = new GMarker(point);
  GEvent.addListener(marker, "click", function() {
    marker.openInfoWindowHtml(
        "<b><a href='"+ link +"'>" + title + "</a></b><br/><img src='"+ image +"' alt='"+ image +"' width='100'/>"
    );
  });
  return marker;
}

function load() {
    if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        

        for (var i = 0; i < locations.length; ++i) {
            if(locations[i] != undefined) {
                if(i == 1) {
                    map.setCenter(new GLatLng(locations[i]['lat'], locations[i]['lon']), 6);
                }
                var point = new GLatLng(locations[i]['lat'], locations[i]['lon']);
                
                map.addOverlay(createMarker(point, i + 1, locations[i]['title'], locations[i]['link'], locations[i]['image']));

            }
        }
        //map.setCenter(new GLatLng(37.4419, -122.1419), 13);
    }
}

