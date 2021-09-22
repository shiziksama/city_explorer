@extends('index')

@section('head')
<title>Треки</title>
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
if(true){
L.tileLayer('https://tracks.lamastravels.in.ua/map_overlay/{{$user->id}}/{z}/{x}/{y}.png', {
	maxZoom: 18,
    tileSize: 512,
    zoomOffset: -1,
}).addTo(mymap);
}

@if($tracks->count()==0)
var corner1 = L.latLng(50.4898,30.5394),corner2 = L.latLng(50.4366,30.4322),	bounds = L.latLngBounds(corner1, corner2);
@else
	@foreach($tracks as $tr)
		var corner1 = L.latLng({!!$tr->getpoint()!!}),
		corner2 = L.latLng({!!$tr->getpoint()!!}),
		bounds = L.latLngBounds(corner1, corner2);
		console.log(bounds);
		@break;
	@endforeach
@endif
@foreach($tracks as $tr)
@foreach($tr->get_original_tracks() as $k=>$line)
var latlngs_o{{$tr->id}}_{{$k}} = {!!json_encode($line)!!};
var polyline_o{{$tr->id}}_{{$k}} = L.polyline(latlngs_o{{$tr->id}}_{{$k}}, {color: 'lightgreen',opacity:1,weight:11}).addTo(mymap);

@endforeach
@foreach($tr->get_tracks() as $k=>$line)

var latlngs{{$tr->id}}_{{$k}} = {!!json_encode($line)!!};
var polyline{{$tr->id}}_{{$k}} = L.polyline(latlngs{{$tr->id}}_{{$k}}, {color: 'blue',opacity:0.7,weight:8}).addTo(mymap);

bounds.extend(polyline{{$tr->id}}_{{$k}}.getBounds());
@endforeach

@endforeach


	//console.log(bounds);
mymap.fitBounds(bounds);

</script>
@endsection
