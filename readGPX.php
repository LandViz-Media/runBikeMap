<!DOCTYPE html>
<html>

  <head>
    <title>Cross Country Map 2019</title>
    <meta name="description" content="Mapping API's:  - Layer Control" />


    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin="" />

    <script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js" integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og==" crossorigin=""></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.4.0/gpx.min.js"></script>


    <script src="classes/leaflet.ajax.js"></script>





    <style>
      html,
      body,
      #map {
        height: 100%;
        margin: 0;
        padding: 0;
        border: 1px solid #000;
      }
    </style>





  </head>

  <body>

		     <div id="map"></div>



    <script>
      //Base Layers
      var OSM = L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18
      });

      var Esri_WorldImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
      });



      //create the map
      var map = L.map('map', {
        center: new L.LatLng(42.3, -94.2),
        zoom: 7,
        layers: [OSM]
      });






var url = 'sample.gpx'; // URL to your GPX file
new L.GPX(url, {
  async: true,
  marker_options: {
    startIconUrl: 'images/pin-icon-start.png',
    endIconUrl: 'images/pin-icon-end.png',
    shadowUrl: 'images/pin-shadow.png'
  }
}).on('loaded', function(e) {
  map.fitBounds(e.target.getBounds());

 dist = e.target.get_distance_imp()
 console.log(dist);
}).addTo(map);








      //layer control
      var baseMaps = {
        "OpenStreetMap": OSM,
        "ESRI Aerial": Esri_WorldImagery
      };

      var overlayMaps = {


      };

      L.control.layers(baseMaps, overlayMaps).addTo(map);



      //map.fitBounds(rankedSchools .getBounds(), {padding: [2, 2]});

    </script>

  </body>

</html>