@extends('index')

@section('head')
<title>Треки по киеву</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css"
   integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
   crossorigin=""/>
   <script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"
   integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew=="
   crossorigin=""></script>
  <style>
  #mapid { 
  position:fixed;
  left:0;
  right:0;
  bottom:0;
  top:0;
 
  }
  </style>
@endsection

@section('content')
<div id="mapid"></div>
<script>
var mymap = L.map('mapid');
L.tileLayer('https://tile.thunderforest.com/cycle/{z}/{x}/{y}@2x.png?apikey=cdeea879c575479fbf645def237f4afa', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
    maxZoom: 18,
    //id: 'mapbox/outdoors-v11',
    tileSize: 512,
    zoomOffset: -1,
    //accessToken: 'pk.eyJ1Ijoic2hpemlrc2FtYSIsImEiOiJja2I2bWNsbm0wMDJlMnFvYmRwanVma3ZnIn0.-2IBbm2m-ZnEv-EjvH7WAA'
}).addTo(mymap);

@foreach($tracks as $tr)
	var corner1 = L.latLng({!!$tr->getpoint()!!}),
	corner2 = L.latLng({!!$tr->getpoint()!!}),
	bounds = L.latLngBounds(corner1, corner2);
	console.log(bounds);
	@break;
@endforeach
@foreach($tracks as $tr)

var latlngs = {!!$tr->get_jsarr()!!};
var polyline = L.polyline(latlngs, {color: 'red',opacity:1,weight:1}).addTo(mymap);

bounds.extend(polyline.getBounds());
@endforeach

mymap.fitBounds(bounds);

</script>
@endsection
