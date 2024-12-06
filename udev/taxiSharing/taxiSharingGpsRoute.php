<?
	include "../common/inc/inc_header.php";  //헤더 
	include "../../sharing/taxiSharingGpsList.php"; 
?>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <script>

      // This example creates a 2-pixel-wide red polyline showing the path of
      // the first trans-Pacific flight between Oakland, CA, and Brisbane,
      // Australia which was made by Charles Kingsford Smith.

      function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 17,
          center: <?=$f_result?>,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        var flightPlanCoordinates = [<?=$result?>
        ];
        var flightPath = new google.maps.Polyline({
          path: flightPlanCoordinates,
          geodesic: true,
          strokeColor: '#FF0000',
          strokeOpacity: 1.0,
          strokeWeight: 5
        });
        <?=$marker?>
        flightPath.setMap(map);
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCPtNcIYMQ7e7zp-hFYDfKwW9ZB2w7jkow&callback=initMap">
    </script>
<?
	dbClose($DB_con);
	$stmt = null;
	$infoStmt = null;
	$mapStmt = null;
	$orderStmt = null;
	$pointStmt = null;
?>
	 