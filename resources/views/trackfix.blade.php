<!DOCTYPE html>
<html>
<head>
 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css"
   integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
   crossorigin=""/>
   <script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"
   integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew=="
   crossorigin=""></script>
  <style>
  #mapid { 
height:500px;
 
  }

  </style>
 </head>
 <body>
 начало
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset-20;?>&limit=<?php echo $limit+20;?>">-20</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset-10;?>&limit=<?php echo $limit+10;?>">-10</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset-1;?>&limit=<?php echo $limit+1;?>">-1</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo 1+$offset;?>&limit=<?php echo $limit-1;?>">+1</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo 10+$offset;?>&limit=<?php echo $limit-10;?>">+10</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo 20+$offset;?>&limit=<?php echo $limit-20;?>">+20</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo 100+$offset;?>&limit=<?php echo $limit-100;?>">+100</a>
 конец
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset;?>&limit=<?php echo $limit-floor($limit/2);?>">-<?php echo floor($limit/2);?></a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset;?>&limit=<?php echo $limit-100;?>">-100</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset;?>&limit=<?php echo $limit-20;?>">-20</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset;?>&limit=<?php echo $limit-10;?>">-10</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset;?>&limit=<?php echo $limit-1;?>">-1</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset;?>&limit=<?php echo $limit+1;?>">+1</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset;?>&limit=<?php echo $limit+10;?>">+10</a>
 <a href="/simplificator?track_id=<?php echo $track_id;?>&offset=<?php echo $offset;?>&limit=<?php echo $limit+20;?>">+20</a> 
 Следующий трек
 <a href="/simplificator?track_id=<?php echo $next_id;?>">next</a>
 <div id="mapid"></div>
 <script>
 var mymap = L.map('mapid',{
   <?php foreach($coords as $coord):?>
	center:[<?php echo $coord['lat'].','.$coord['lng'];?>],
	<?php endforeach;?>
    zoom: 18
});
//L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
L.tileLayer('https://tile.thunderforest.com/cycle/{z}/{x}/{y}@2x.png?apikey=cdeea879c575479fbf645def237f4afa', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
	
    maxZoom: 20,
	
    //id: 'mapbox/outdoors-v11',
    tileSize: 512,
    zoomOffset: -1,
    //accessToken: 'pk.eyJ1Ijoic2hpemlrc2FtYSIsImEiOiJja2I2bWNsbm0wMDJlMnFvYmRwanVma3ZnIn0.-2IBbm2m-ZnEv-EjvH7WAA'
}).addTo(mymap);
var greenIcon = new L.Icon({
  iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41]
});
<?php foreach($coords->last()['variants'] as $variant):?>
	L.marker([<?php echo $variant['location'][1];?>, <?php echo $variant['location'][0];?>],{title:"<?php echo $variant['location'][1];?>, <?php echo $variant['location'][0];?>"}).addTo(mymap);
<?php endforeach;?>
var latlngs = <?php echo $coords->values()->slice(0,$one_line_length-2)->map(function($item){
	return [$item['lat'],$item['lng']];
})->toJson();?>;
var polyline = L.polyline(latlngs, {color: 'green'}).addTo(mymap);
var bounds=polyline.getBounds();

var latlngs_l = <?php echo $coords->values()->slice($one_line_length-3)->values()->map(function($item){
	return [$item['lat'],$item['lng']];
})->toJson();?>;
var polyline_l = L.polyline(latlngs_l, {color: 'lightgreen'}).addTo(mymap);
bounds.extend(polyline_l.getBounds());

<?php foreach($lines as $k=>$line):?>
var latlngs<?php echo $k;?> = <?php echo $line['line']->map(function($item){
	return array_values(array_reverse(array_map('floatval',$item)));
})->toJson();?>;
<?php $marker=$line['line']->nth($line['line']->count()-2)->last();?>
L.marker([<?php echo $marker[1];?>, <?php echo $marker[0];?>],{icon:greenIcon,title:"<?php echo $marker[1];?>, <?php echo $marker[0];?>|<?php echo $line['length'];?>"}).addTo(mymap);
<?php $marker=$line['line']->last();?>
L.marker([<?php echo $marker[1];?>, <?php echo $marker[0];?>],{title:"<?php echo $marker[1];?>, <?php echo $marker[0];?>|<?php echo $line['length'];?>"}).addTo(mymap);
var polyline<?php echo $k;?> = L.polyline(latlngs<?php echo $k;?>, {color: 'red'}).addTo(mymap);
	bounds.extend(polyline<?php echo $k;?>.getBounds());
<?php endforeach;?>


var latlngs_last = <?php echo $one_line->map(function($item){
	return [$item[0],$item[1]];
})->toJson();?>;
var polyline_last = L.polyline(latlngs_last, {color: 'blue'}).addTo(mymap);
bounds.extend(polyline_last.getBounds());
// zoom the map to the polyline
var latlngs_extended =<?php echo json_encode($one_line_extended);?>;
var polyline_extended = L.polyline(latlngs_extended, {color: 'violet'}).addTo(mymap);
bounds.extend(polyline_extended.getBounds());
//console.log(bounds);

mymap.fitBounds(bounds);
 </script>
<?php 
//var_dump($one_line->map('count'));
//var_dump($one_line);
$for_replace=$for_replace->slice(0,$one_line_length-2);
//var_dump($for_replace);
?>
 
 
<form method=post>
@csrf
<input type="track" name="track_id" value="<?php echo $track_id;?>">
<input type="text" name="for_replace" value="<?php echo base64_encode($for_replace->toJson());?>">
<input type="text" name="replace" value="<?php echo base64_encode($one_line->toJson());?>">
<button name="do" value="replace">Сохранить</button>
</form>
 </body>
 </html>
