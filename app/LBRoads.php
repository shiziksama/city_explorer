<?php 
namespace App;
class LBRoads{
	public $roads='[out:json][timeout:600];
		(
		  way["highway"]({{bbox}});
		  -(
			way["highway"="construction"]({{bbox}});
			way["highway"="steps"]({{bbox}});
			way["highway"="proposed"]({{bbox}});
			way["highway"="rest_area"]({{bbox}});
			way["highway"="bridleway"]({{bbox}});
			way["amenity"="parking"]({{bbox}});
			way["smoothness"="bad"]({{bbox}});
			way["surface"="dirt"]({{bbox}});
			way["surface"="unpaved"]({{bbox}});
			way["surface"="gravel"]({{bbox}});
			way["surface"="grass"]({{bbox}});
			way["surface"="ground"]({{bbox}});
			way["surface"="sand"]({{bbox}});
			way["surface"="pebblestone"]({{bbox}});
			way["surface"="fine_gravel"]({{bbox}});
			way["area"="yes"]({{bbox}});
			way["tracktype"="grade2"]({{bbox}});
			way["tracktype"="grade3"]({{bbox}});
			way["tracktype"="grade4"]({{bbox}});
			way["tracktype"="grade5"]({{bbox}});
			);
		);
		out body;
		>;
		out skel qt;';
	public function filter_tags($tagvalue,$tag){
		if(is_numeric($tag))return false;
		if($tag=='hazard'&&$tagvalue=="animal_crossing")return false;
		if($tag=='type'&&$tagvalue=="route")return false;
		if($tag=='surface'&&$tagvalue=="paved")return false;
		if($tag=='leisure'&&$tagvalue=="track")return false;
		if($tag=='bicycle'&&$tagvalue=="dismount")return false;
		if($tag=='railway'&&in_array($tagvalue,["abandoned",'tram']))return false;
		//if($tag=='access'&&in_array($tagvalue,["yes",'agricultural','permissive','designated']))return false;
		//if($tag=='access'&&$tagvalue=="permissive")return false;
		//var_dump($tag);
		$starttags=['name','old_name','agricultur','ambulance','augsburgbarrierefrei:','BLfD:','arcade','ascent','payment','capacity','traffic_sign','railway:','surface:de','surface:note','TMC:','FIXME','after_construction','building','heritage','wikimedia_commons','handrail','snowplowing','ramp','step:','wheelchair','est_width','postal_code','placement','piste','ine:','junction','psv:','historic','proposed','pictogram','minspeed','int_name','alt_name','source','destination','toll','turn','maxspeed','overtaking','osmarender','abandoned','lanes','cs_dir','tunnel','note','ref','operator','shoulder','traffic_island','bus:','it:','is_in:','merano:','brand','comment','symbol','zone','parking','bridge','hazmat','temporary','usability','change:lanes','maxweight','maxheight','access','fixme','Cod_via','date','trolley_wire','fuel','direction','colonnade','tactile_paving','gritting','maintenance','DfT_AADF','yh:','IBGE:','4C:','old_ref','CoCT:','geobase:','LINZ:','LINZ2OSM:','FayettevilleGIS:','GPT:','bburg:','architect','USFS:','RLIS:','NJDOT_SRI','nvdb:','massgis:','canvec:','garmin:','salting','nat_name','fdot:','RDV63:','statscan:','teryt:','surrey:','lit:','light:','milepost','HU:','change:','project','chile:','gdot:','hov','gomel_PT:','zip_','garmin-','full_name','from_address','to_address_','wb_pb','way_fbid','url:','transit','traffic','tol:','year_','was:','tmc:','subject:','structure','street','start','ssr:','sk:','sensor_','priority','ourfootprints','omkum:','nfs:','nat_ref','mvdgis:','logainm:','lit:','lit_','linz:','lcn:','lcn2:','lamp:','inscription','info','inegi:','import','nps:','image','hour_','opening_hours','minsk_PT','kerb:','gnis:','geobase_','garmin','ele:','construction:','recycling','removed','barrier','reg_name','colour','lamp_','dtag-','horse','wrong_name'];
		foreach($starttags as $sttag){
			if(substr($tag,0,mb_strlen($sttag))==$sttag)return false;
		}	
		if(substr($tag,0,4)=='mtb:')return false;
		if(substr($tag,0,13)=='class:bicycle')return false;
		if(substr($tag,0,13)=='motor_vehicle')return false;
		if(substr($tag,0,3)=='hgv')return false;
		if(substr($tag,0,8)=='motorcar')return false;
		if(substr($tag,0,5)=='width')return false;
		if(substr($tag,0,7)=='vehicle')return false;
		if(substr($tag,0,6)=='hazard')return false;
		if(substr($tag,0,14)=='embedded_rails')return false;
		if(substr($tag,0,8)=='disused:')return false;
		if(substr($tag,0,7)=='survey:')return false;
		if(substr($tag,0,5)=='addr:')return false;
		if(substr($tag,0,11)=='tourist_bus')return false;
		if(substr($tag,0,10)=='lamp_model')return false;
		if(substr($tag,0,6)=='busway')return false;
		if(substr($tag,0,12)=='sorting_name')return false;
		if(substr($tag,0,10)=='short_name')return false;
		if(substr($tag,0,13)=='official_name')return false;
		if(substr($tag,0,9)=='wikipedia')return false;
		if(substr($tag,0,8)=='loc_name')return false;
		if(substr($tag,0,11)=='description')return false;
		if(substr($tag,0,9)=='mapillary')return false;
		if(substr($tag,0,4)=='VRS:')return false;
		if(substr($tag,0,4)=='uic_')return false;
		if(substr($tag,0,10)=='check_date')return false;
		if(substr($tag,0,7)=='contact')return false;
		$endtags=['conditional','source','_name','_date','_ref',':name',':date','_id',':ref',':note',':mofa',':moped','weight','-ref','wikidata','height','length','traffic_sign',':bus','colour',':psv',':railway','historic','wheelchair'];
		foreach($endtags as $etag){
			if(substr($tag,-mb_strlen($etag))==$etag)return false;
		}	
		
		if(in_array($tag,['layer','embankment','oneway','tunnel','level','fixme','int_ref','is_in','lit','sac_scale','placement','FIXME','lcn','narrow','avz','incline','ramp','traffic_calming','height','service','segregated','covered','','hgv','sidewalk','horse','noexit','network','forestry','route_ref','motorroad','man_made','priority_road','','noname','abutters','mofa','loc_ref','long_name','maxaxleload','history','motorcycle','mtb:scale','start_date','conveying','trail_visibility','goods','unsigned_ref','psv','local_name','created_by','bus','moped','lane_markings','construction','passing_places','winter_service','last_renovation','carriage','flood_prone','rcn','','living_street','nat_ref','bdouble','reg_name','admin_level','owner','historic','maxlength','route','indoor','lit_by_led','lamp_mount','taxi','reg_ref','wikidata','maxwidth','smoothness:date','','url','old_reg_ref','nat_name','distance','catmp-RoadID','speed','garmin_type','website','recommended_speed','surface_survey','related_law','old_loc_ref','electrified','days','','emergency','lines','seasonal','bicycle:conditional','level:ref','TODO','adfc_hl_net','amenity','area','XTRID','de:strassenschluessel','alley','strassen-nrw:abs','alt_ref','tmc','image','StrVz','Speed','associatedStreet','barrier','cycleway:width','footway:width','alias','arcade','Ref','augsburg_stadt:ref','4wd_only','animal_crossing','Bing','GM_TYPE','ELEVATION','author','atv','public_transport','shelter','traffic','abbreviation','','end_date','','cutting','step_count','left_of','snowmobile','rcn_ref','dog','gauge','fee','place_numbers','local_code','place','old_ref1','reg-ref','new_ref','noref','stroller','opening_hours','phone','length','','length','mtb','kerb','boundary','ski','','from','pistamota','pictogram','brp_access','visible_name','','osmc:symbol','t_node','ele','DUG','Etichetta','ISTAT','Lunghezza','Toponimo','Tratto','cycling_width','disabled','stop','frequency','voltage','trailblazed','obstacle','official_ref','highway_authority_ref','bus_name','prow_ref','import','attribution','HFCS','Bergen_County_database_ref','AUTO_ID','ET_ID','Shape_Le_1','Shape_Leng','id','Cat2','CATEGORY','OBJECTID','RUTA_ID','RV_CATEGOR','CAT_CALLE','ESTADO','FECHA','CL_Source','GlobalID','LHI','LLO','MCL','MCR','COL','COR','RHI','RLO','Shape_STLe','COUNTYFP','FULLNAME','LINEARID','MTFCC','RTTYP','STATEFP','CONNECT','F_NODE','LINK_ID','ROAD_USE','MULTI_LINK','T_NODE','ROAD_NO','CREATED_DA','PARK_NAME','CREATED_US','LAST_EDITE','LAST_EDI_1','LENGTH_MIL','CityName','CountryName','EndLevel','MARINE','Nod1','Nod2','RegionName','RoadID','RouteParam','ZipIdx','Carrera','Comments','NHS','highway:category:pl','highway:class:pl','EdgeID','FCC','FIPS','EID','FNODE_','LENGTH','LPOLY_','RPOLY_','STREET_','STREET_ID','TNODE_','Shape_len','FID','GM_LAYER','MALARIA','admin_leve','version','HE_ref','DfT_traffic_counter_ref','ID','bearing','addr_cnt','fetype','fraddl','fraddr','toaddl','toaddr','zip_right','_voi_id_','_voi_nom_fr_','_voi_tyv_id_','_BEARING_','SHAPE_Leng','osm_id','ncat','review','CDOT_route','city','FUENTE','DPA_PARROQ','adot_name','opening_date','lcn_ref','rcrc_ref','highways_england:area','old_railway_operator','AND:importance_level','AND_a_nosr_r','mdt_base_route','condition','ownership','osm_type','expressway','','traces','odbl','operational_status','hwpi','restriction','centre_turn_lane','pihatie','local_class','cmt','','avgspeed','highway:category','contributor','wydot_ml_number','via','uploaded_by','undefined','towards','sym_ul','survey','weight','todo','ssvz','src','split_from','shop','roadid','postcode','post_code','myid','maxgcweight','maxbogieweight','marine','mp_type','nod1','nod2','roadid','routeparam','label','jns_kons_1','jns_knstrk','knds_jln','pjg_jln','tipe_jln_1','jenis_knst','video','streets_pk','objectid','f_node','end','email','elevation','delivery','_ALTTRLNAME_','de:strassenschluessel_exists','scenic','tree_lined','dogs','leisure','bus_bay','rural','bench','converted_by','railway','trailer','groundcheck','fire_path','tram','tracks','passage_type','flickr','remark','rcl','ptv','fixed','status','OBS','tourism','area:highway','natural']))return false;
		return true;
	}
	public function modify_tags($element){
		if(empty($element['tags']))return $element;
		$tags=array_filter($element['tags'],array($this,'filter_tags'),ARRAY_FILTER_USE_BOTH);
		if(!empty($tags['highway'])&&in_array($tags['highway'],['tertiary_link','motorway_link','track','motorway','residential','tertiary','secondary','unclassified','service','trunk','trunk_link','living_street','primary','path','primary_link','secondary_link'])){
			$tags['highway']='road';
		}
		if(!empty($tags['highway'])&&in_array($tags['highway'],['pedestrian'])){
			$tags['highway']='footway';
		}
		if(!empty($tags['tracktype:bing'])&&empty($tags['tracktype'])){
			$tags['tracktype']=$tags['tracktype:bing'];
			unset($tags['tracktype:bing']);
		}
		
		if(!empty($tags['tracktype:esri'])&&empty($tags['tracktype'])){
			$tags['tracktype']=$tags['tracktype:esri'];
			unset($tags['tracktype:esri']);
		}
		
		if(!empty($tags['tracktype:mapbox'])&&empty($tags['tracktype'])){
			$tags['tracktype']=$tags['tracktype:mapbox'];
			unset($tags['tracktype:mapbox']);
		}
		$canlb_tags=['foot','bicycle','cycleway','oneway:bicycle','cycleway:right','cycleway:left','cycleway:both','cycleway:right:oneway','cycleway:left:oneway','cycleway:right:segregated','cycleway:left:segregated'];
		foreach($canlb_tags as $tag){
			if(!empty($tags[$tag])&&in_array($tags[$tag],['no','none'])){
				unset($tags[$tag]);
			}
			if(!empty($tags[$tag])&&in_array($tags[$tag],['official'])){
				$tags[$tag]='designated';
			}
			
		}
		if(!empty($tags['foot'])&&$tags['foot']=='use_sidepath'){
			unset($tags['foot']);
		}
		if(!empty($tags['highway'])&&!empty($tags['bicycle'])&&$tags['highway']=='pedestrian'){
			$tags['highway']='footway';
			unset($tags['bicycle']);
		}
		if(!empty($tags['sidewalk:right:width'])||!empty($tags['sidewalk:left:width'])||!empty($tags['sidewalk:both:width'])){
			unset($tags['sidewalk:right:width']);
			unset($tags['sidewalk:left:width']);
			unset($tags['sidewalk:both:width']);
			$tags['footway']='sidewalk';	
		}
		
		if(!empty($tags['footway'])&&!empty($tags['highway'])&&$tags['highway']=='footway'&&$tags['footway']=='sidewalk'){
			unset($tags['footway']);
		}
		
		if(!empty($tags['bicycle'])&&!empty($tags['highway'])&&$tags['highway']=='footway'){
			unset($tags['bicycle']);
		}
		if(!empty($tags['foot'])&&!empty($tags['highway'])&&$tags['highway']=='footway'){
			unset($tags['foot']);
		}
		if(!empty($tags['tracktype'])&&$tags['tracktype']=='grade1'){
			unset($tags['tracktype']);
			$tags['surface']='asphalt';
		}
		if(!empty($tags['surface'])&&$tags['surface']=='compacted'){
			unset($tags['surface']);
		}
		if(!empty($tags['smoothness'])&&$tags['smoothness']=='intermediate'){
			unset($tags['smoothness']);
		}
		if(!empty($tags['surface'])&&in_array($tags['surface'],['concrete','paving_stones'])){
			$tags['surface']='asphalt';
		}
		if(isset($tags['surface:grade'])&&!empty($tags['surface'])){
			if($tags['surface']=='asphalt'){
				if(!in_array($tags['surface:grade'],[3,2])){
					$tags['surface']='dirt';
				}
				unset($tags['surface:grade']);
			}
		}
		if(!empty($tags['cycleway'])&&in_array($tags['cycleway'],['opposite','shared_line','shared_lane','share_busway'])){
			$tags['cycleway']='line';
		}
		
		
		if(!empty($tags['smoothness'])&&in_array($tags['smoothness'],['excellent','good','very_good'])){
			unset($tags['smoothness']);
			$tags['surface']='asphalt';
		}
		if(!empty($tags['cycleway:both'])&&empty($tags['cycleway'])){
			$tags['cycleway']=$tags['cycleway:both'];
			unset($tags['cycleway:both']);
		}
		if(!empty($tags['cycleway:left'])&&empty($tags['cycleway'])){
			$tags['cycleway']=$tags['cycleway:left'];
			unset($tags['cycleway:left']);
		}
		if(!empty($tags['cycleway:right'])&&empty($tags['cycleway'])){
			$tags['cycleway']=$tags['cycleway:right'];
			unset($tags['cycleway:right']);
		}
		if(!empty($tags['cycleway:left'])&&in_array($tags['cycleway:left'],['lane','track'])){
			$tags['cycleway']=$tags['cycleway:left'];
			unset($tags['cycleway:left']);
		}
		if(!empty($tags['cycleway:right'])&&in_array($tags['cycleway:right'],['lane','track'])){
			$tags['cycleway']=$tags['cycleway:right'];
			unset($tags['cycleway:right']);
		}
		if((!empty($tags['bicycle'])&&!empty($tags['foot'])&&$tags['bicycle']==$tags['foot'])){
			unset($tags['foot']);
		}
		
		//var_dump($tags);
		$element['tags']=$tags;
		return $element;
	}
	public function filter_overpass($elements){
		$extags=[];
		$extags[1]=[];
		$extags[2]=[
		'{"highway":"service","service":"parking_aisle"}',
		'{"highway":"service","service":"driveway"}',
		'{"highway":"service","service":"alley"}',
		'{"highway":"service","service":"drive-through"}',
		'{"highway":"road","surface":"paving_stones"}',
		'{"bicycle":"no","highway":"road"}',
		'{"bicycle":"yes","highway":"road"}',
		'{"foot":"yes","highway":"road"}',
		'{"cycleway":"no","highway":"road"}',
		];
		$extags[3]=[
		'{"bicycle":"no","highway":"road","surface":"asphalt"}',
		'{"bicycle":"no","highway":"road","surface":"asphalt"}',
		'{"bicycle":"yes","cycleway":"no","highway":"road"}',
		'{"bicycle":"yes","highway":"road","surface":"asphalt"}',
		'{"foot":"yes","highway":"road","surface":"asphalt"}',
		'{"bicycle":"yes","highway":"road","surface":"paving_stones"}',
		'{"cycleway":"no","highway":"road","surface":"asphalt"}',
		];
		$extags[4]=[
		/*
		{bicycle=yes
		
foot=yes
highway=road
surface=asphalt
*/
		];
		$filters=[
			['highway'=>'construction'],
			['highway'=>'steps'],
			['highway'=>'proposed'],
			['highway'=>'platform'],
			['highway'=>'bus_stop'],
			['highway'=>'rest_area'],
			['highway'=>'bridleway'],
			['highway'=>'via_ferrata'],
			['highway'=>'planned'],
			['highway'=>'corridor'],
			['highway'=>'raceway'],
			['highway'=>'elevator'],
			['highway'=>'emergency_bay'],
			['highway'=>'services'],
			['amenity'=>'parking'],
			['amenity'=>'services'],//заправка
			['smoothness'=>'bad'],
			['designation'=>'public_bridleway'],
			//['smoothness'=>'bed'],//
			['smoothness'=>'very_bad'],
			['smoothness'=>'very_horrible'],
			['smoothness'=>'horrible'],
			['smoothness'=>'impassable'],
			['smoothness'=>'medium'],
			['footway'=>'crossing'],
			['surface'=>'dirt'],
			['surface'=>'unpaved'],
			['surface'=>'gravel'],
			['surface'=>'grass'],
			['surface'=>'ground'],
			['surface'=>'sand'],
			['surface'=>'earth'],
			['surface'=>'pebblestone'],
			['surface'=>'fine_gravel'],
			['surface'=>'cobblestone'],
			['surface'=>'concrete:plates'],
			['surface'=>'concrete:lanes'],
			['surface'=>'wood'],
			['surface'=>'stone'],
			['surface'=>'grass_paver'],
			['area'=>'yes'],
			['ice_road'=>'yes'],
			['winter_road'=>'yes'],
			['tracktype'=>'grade2'],
			['tracktype'=>'grade3'],
			['tracktype'=>'grade4'],
			['tracktype'=>'grade5'],
			['tracktype'=>'indeterminate'],//не совсем правильно, но пусть будет
			
		];
		$elements=array_map(function($item)use($extags,$filters){
			if(!empty($item['tags'])){
				if(isset($item['tags']['aeroway'])){ return null;}//аэропорт
				if(isset($item['tags']['crossing'])){ return null;}//переходы дороги
				if(isset($item['tags']['informal'])){ return null;}//не существует на самом деле вообще
				if(isset($item['tags']['ford'])){ return null;}//Переправа(возможно брод)
				if(isset($item['tags']['waterway'])){ return null;}//поток воды
				if(isset($item['tags']['driveway'])){ return null;}//подъезды к дому
				if(isset($item['tags']['cutline'])){ return null;}//вырубка
				foreach($filters as $filter){
					foreach($filter as $k=>$v){
						if(!empty($item['tags'][$k])&&$item['tags'][$k]==$v)return null;//дополнительный фильтр
					}
				}
				$item['tags']=array_filter($item['tags'],array($this,'filter_tags'),ARRAY_FILTER_USE_BOTH);
				ksort($item['tags']);
				if(count($item['tags'])==1){
					if(!empty($item['tags']['highway'])&&in_array($item['tags']['highway'],['road'])){
						return null;
					}
					//var_dump(json_encode($item['tags']));
				}elseif(count($item['tags'])==2){
					if(in_array(json_encode($item['tags']),$extags[2])){
						return null;
					}
					if(!empty($item['tags']['surface'])&&$item['tags']['highway']=='road'){ //дорога с указанным качеством покрытия
						return null;
					}
					if(!empty($item['tags']['highway'])&&in_array($item['tags']['highway'],['road'])&&!empty($item['tags']['smoothness'])){ //пофигу на качество дороги
						return null;
					}
					//var_dump(json_encode($item['tags']));
				}elseif(count($item['tags'])==3){
					if(in_array(json_encode($item['tags']),$extags[3])){
						return null;
					}
					//var_dump(json_encode($item['tags']));
				}
			}

			return $item;
		},$elements);
		$elements=array_values(array_filter($elements));
		return $elements;
	}
	public function drop_nodes($elements){
		$nodes=[];
		foreach($elements as $element){
			if(!empty($element['nodes'])){
				$nodes=array_merge($nodes,$element['nodes']);
			}
		}

		$nodes=array_unique($nodes);
		$elements=array_filter($elements,function($value)use($nodes){
			if($value['type']=='node'&&!in_array($value['id'],$nodes))return false;
				return true;
		});
		$elements=array_values($elements);
		return $elements;
	}
	public function overpass($data,$bbox='',$zoom=0,$x=0,$y=0){
		$url = 'http://overpass-api.de/api/interpreter';
		$data=str_replace('{{bbox}}',$bbox,$data);
		//var_dump($data);
		$data=['data'=>$data];
	
		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		if(empty($result)){
			if(php_sapi_name()=='cli'){
				var_dump('overpass_download');
				
			}
			$result = json_decode(file_get_contents($url, false, $context),true);
			if(php_sapi_name()=='cli'){
				sleep(5);
			}
		}
		return $result;
	}
	public function create_o5m($zoom,$x,$y){
		while($zoom>3){
			$zoom-=1;
			$x=floor($x/2);
			$y=floor($y/2);
		}
		$filename='planet-highways.'.$zoom.'.'.$x.'.'.$y.'.o5m';
		if(file_exists(base_path('ukraine/'.$filename))){
			return $filename;
		}
		$items_count=pow(2,$zoom);
		$lng_deg_per_item=360/$items_count;
		$lng_from=-180+$x*$lng_deg_per_item;
		$lng_to=-180+($x+1)*$lng_deg_per_item;
		
		$lat_deg_per_item=(85.0511*2)/$items_count;
		$lat_to=rad2deg(atan(sinh(pi() * (1 - 2 * $y / $items_count))));
		$lat_from=rad2deg(atan(sinh(pi() * (1 - 2 * ($y+1) / $items_count))));
		
		$shell='osmconvert '.base_path('ukraine/planet-highways.o5m').' --complete-ways --drop-author -b='.$lng_from.','.$lat_from.','.$lng_to.','.$lat_to.' -o='.base_path('ukraine/'.$filename);
		var_dump($shell);
		var_dump(time());
		$s=shell_exec($shell);
		var_dump(time());
		return $filename;
	}
	public function osmconvert($data,$bbox='',$zoom='',$x='',$y=''){
		$filename='planet-highways.o5m';
		foreach(explode(";",$data) as $line){
			preg_match_all('/\([\d.,-]+\)/',$line,$out);
			if(empty(array_filter($out)))continue;
			$bbox=$out[0][0];
			$bbox=substr($bbox,1,-1);
			$filename='planet-black.o5m';
		}
		
		
		if(!empty($zoom)){
			$filename=$this->create_o5m($zoom,$x,$y);
		}
		
		$bbox=explode(',',$bbox);
		$bbox=$bbox[1].','.$bbox[0].','.$bbox[3].','.$bbox[2];
		$shell='osmconvert '.base_path('ukraine/'.$filename).' --complete-ways --drop-author -b='.$bbox;
		//var_dump($shell);
		if(php_sapi_name()=='cli'){var_dump('start_convert|time:'.time());}
		$s=shell_exec($shell);
		if(php_sapi_name()=='cli'){var_dump('get_xml|time:'.time());}
		$z = new WeblamasXMLReader();
		$z->xml($s);
		$elements=[];
		while($z->read()) {
			if($z->name=='node'&&$z->nodeType==\XmlReader::ELEMENT){
				//echo '<node id="'.$z->getAttribute('id').'" lat="'.$z->getAttribute('lat').'" lon="'.$z->getAttribute('lon').'"/>'.PHP_EOL;
				$elements[]=[
					'type'=>'node',
					'id'=>''.$z->getAttribute('id'),
					'lat'=>''.$z->getAttribute('lat'),
					'lon'=>''.$z->getAttribute('lon'),
				];
				$z->endElement();
				continue;
			}elseif($z->name=='way'&&$z->nodeType==\XmlReader::ELEMENT){
				$osm=simplexml_load_string($z->readOuterXml());
				$tags=[];
				$nodes=[];
				foreach($osm as $key=>$child){
					if($key=='nd'){
						$nodes[]=''.$child->attributes()->ref;
					}elseif($key=='tag'){
						$tags[''.$child->attributes()->k]=''.$child->attributes()->v;
					}
				}
				$elements[]=[
					'type'=>'way',
					'id'=>''.$osm->attributes()->id,
					'tags'=>$tags,
					'nodes'=>$nodes,
					
				];
				$z->endElement();
			}
		}
		if(php_sapi_name()=='cli'){var_dump('after_xml|time:'.time());}
		return ['elements'=>$elements, 
				"version"=> 0.6,
				"generator"=>"Overpass API 0.7.56.8 7d656e78",
				"osm3s"=>[
					"timestamp_osm_base"=>"2021-02-05T12:58:03Z",
					"copyright"=>"The data included in this document is from www.openstreetmap.org. The data is made available under ODbL."
				  ]];
	}
	public function computeOutCode($point,$lat_from,$lat_to,$lng_from,$lng_to){
		$result=0;
		if($point['lat']<$lat_from){
			$result = $result |1;
		}
		if($point['lat']>$lat_to){
			$result = $result |2;
		}
		if($point['lng']<$lng_from){
			$result = $result |4;
		}
		if($point['lng']>$lng_to){
			$result = $result |8;
		}
		return $result;
	}
	public function parse_parent_lines($zoom,$x,$y){
		$parent_lines=$this->get_lines($zoom-1,floor($x/2),floor($y/2));
		
		$items_count=pow(2,$zoom);
		$lng_deg_per_item=360/$items_count;
		$lng_from=-180+$x*$lng_deg_per_item;
		$lng_to=-180+($x+1)*$lng_deg_per_item;
		
		$lat_deg_per_item=(85.0511*2)/$items_count;
		$lat_to=rad2deg(atan(sinh(pi() * (1 - 2 * $y / $items_count))));
		$lat_from=rad2deg(atan(sinh(pi() * (1 - 2 * ($y+1) / $items_count))));
		$lines=[];
		foreach($parent_lines as $track){
			$points_numbers=[];
			foreach($track['points'] as $k=>$point){
				$points_numbers[$k]=$this->computeOutCode($point,$lat_from,$lat_to,$lng_from,$lng_to);
			}
			foreach($points_numbers as $k=>$number){
				if($k==0)continue;
				if(($number & $points_numbers[$k-1])==0||$number==0||$points_numbers[$k-1]==0){ //Значит эта линия пересекает.
					$lines[]=$track;
					break;
				}
			}
		}
		$path=base_path('lb_json/l_'.$zoom.'.'.$x.'.'.$y.'.json');
		file_put_contents($path,json_encode($lines));
		return $lines;
		var_dump(count($lines));
		var_dump(count($parent_lines));
		die();
	}
	public function get_lines($zoom,$x,$y){
		if(php_sapi_name()=='cli'){
			var_dump('get_lines|zoom:'.$zoom.' '.$x.' '.$y);
			if(php_sapi_name()=='cli'){var_dump('get_lines|time:'.time());}
		}
		$path=base_path('lb_json/l_'.$zoom.'.'.$x.'.'.$y.'.json');
		if(file_exists($path)){
			$result=json_decode(file_get_contents($path),true);
			return $result;
		}
		if($zoom>6){
			return $this->parse_parent_lines($zoom,$x,$y);
		}
		$items_count=pow(2,$zoom);
		$lng_deg_per_item=360/$items_count;
		$lng_from=-180+$x*$lng_deg_per_item;
		$lng_to=-180+($x+1)*$lng_deg_per_item;
		
		$lat_deg_per_item=(85.0511*2)/$items_count;
		$lat_to=rad2deg(atan(sinh(pi() * (1 - 2 * $y / $items_count))));
		$lat_from=rad2deg(atan(sinh(pi() * (1 - 2 * ($y+1) / $items_count))));
		//$lbroads=new \App\Lbroads();
		if(php_sapi_name()=='cli'){var_dump('before_overpass|time:'.time());}
		$result=$this->get_overpass($this->roads,$lat_from.','.$lng_from.','.$lat_to.','.$lng_to,$zoom,$x,$y);
		if(php_sapi_name()=='cli'){var_dump('after_overpass|time:'.time());}
		$pre_lines=[];
		foreach($result['elements'] as $el){
			//if($el['type']=='way'&&!empty($el['tags']['lbroad'])){
			if($el['type']=='way'){
				$pre_lines[]=$el;
			}elseif($el['type']=='node'){
				$points[$el['id']]=['lat'=>$el['lat'],'lng'=>$el['lon']];
			}
		}
		$lines=[];
		foreach($pre_lines as $pre_line){
			//$line=['id'=>$pre_line['id'],'points'=>[]];
			//var_dump($pre_line);
			foreach($pre_line['nodes'] as $pt){
				$pre_line['points'][]=$points[$pt];
			}
			unset($pre_line['nodes']);
			//unset($pre_line['type']);
			$lines[]=$pre_line;
		}
		if(php_sapi_name()=='cli'){var_dump('filtered_lines|time:'.time());}
		file_put_contents($path,json_encode($lines));
		return $lines;	
	}
		
	private function get_overpass_result($zoom,$x,$y){
		if(php_sapi_name()=='cli'){
			var_dump('zoom:'.$zoom.' '.$x.' '.$y);
			
		}
		
		
		
		if(php_sapi_name()=='cli'){var_dump('time:'.time());}
		if($zoom>13){
			//$lbroads=new \App\Lbroads();
			return $this->get_overpass_result($zoom-1,intdiv($x,2),intdiv($y,2));
			//return $lbroads->get_overpass($this->roads,$lat_from.','.$lng_from.','.$lat_to.','.$lng_to,$zoom,$x,$y);
		}
		if($zoom>8){
			$items_count=pow(2,$zoom);
			$lng_deg_per_item=360/$items_count;
			$lng_from=-180+$x*$lng_deg_per_item;
			$lng_to=-180+($x+1)*$lng_deg_per_item;
			
			$lat_deg_per_item=(85.0511*2)/$items_count;
			$lat_to=rad2deg(atan(sinh(pi() * (1 - 2 * $y / $items_count))));
			$lat_from=rad2deg(atan(sinh(pi() * (1 - 2 * ($y+1) / $items_count))));
			//$lbroads=new \App\Lbroads();
			
			return $this->get_overpass($this->roads,$lat_from.','.$lng_from.','.$lat_to.','.$lng_to,$zoom,$x,$y);
		}
		$params=[
			['x'=>2*$x,'y'=>2*$y],
			['x'=>2*$x,'y'=>2*$y+1],
			['x'=>2*$x+1,'y'=>2*$y],
			['x'=>2*$x+1,'y'=>2*$y+1],
		];
		$elements=[];
		foreach($params as $param){
			$result=$this->get_overpass_result($zoom+1,$param['x'],$param['y']);
			$elements=array_merge($elements,$result['elements']);
		}
		$result['elements']=$elements;
		return $result;
	}
	public function get_overpass($data,$bbox='',$zoom=0,$x=0,$y=0){
		if(!empty($zoom)){
			$path=base_path('lb_json/d_'.$zoom.'.'.$x.'.'.$y.'.json');
			if(file_exists($path)){
				$result=json_decode(file_get_contents($path),true);
			}
		}
		//var_dump('qq');
		if(empty($result)){
			if(php_sapi_name()=='cli'){var_dump('before osmconvert|time:'.time());}
			//$result=$this->overpass($data,$bbox,$zoom,$x,$y);
			$result=$this->osmconvert($data,$bbox,$zoom,$x,$y);
			if(php_sapi_name()=='cli'){var_dump('after osmconvert|time:'.time());}
		}

		$elements=array_map([$this,'modify_tags'],$result['elements']);
		if(php_sapi_name()=='cli'){var_dump('modify_tags|time:'.time());}
		$elements=$this->filter_overpass($elements);
		if(php_sapi_name()=='cli'){var_dump('filter_overpass|time:'.time());}
		//$elements=$this->drop_nodes($elements);
		//if(php_sapi_name()=='cli'){var_dump('drop_nodes|time:'.time());}
		$result['elements']=$elements;
		
		if(!empty($zoom)){
			file_put_contents($path,json_encode($result));
		}
		return $result;
	}
	public function test_great($tags){
		if(!empty($tags['bicycle'])&&!empty($tags['surface'])&&$tags['bicycle']=='designated'&&$tags['surface']=='asphalt'){
			return true;
		}
		if(!empty($tags['highway'])&&!empty($tags['surface'])&&$tags['highway']=='cycleway'&&$tags['surface']=='asphalt'){
			return true;
		}
		if(!empty($tags['cycleway'])&&!empty($tags['surface'])&&$tags['cycleway']=='track'&&$tags['surface']=='asphalt'){
			return true;
		}
		return false;
	}
	public function test_bicyundefined($tags){
		//add cycleway=track
		unset($tags['foot']);
		unset($tags['footway']);
		if(count($tags)==2&&!empty($tags['bicycle'])&&$tags['bicycle']=='designated'){
			return true;
		}
		unset($tags['bicycle']);//удаляем bicycle, чтобы cycleway мог быть один
		if(json_encode($tags)=='{"cycleway":"track","highway":"road"}') return true;
		if(count($tags)==1&&$tags['highway']=='cycleway'){
			return true;
		}
		return false;
	}
	public function test_bikeline($tags){
		unset($tags['sidewalk']);
		unset($tags['surface']);
		unset($tags['highway']);
		unset($tags['bicycle']);
		unset($tags['foot']);
		
		if(!empty($tags['cycleway'])&&in_array($tags['cycleway'],['lane','line','opposite_lane'])){
			if(count($tags)==1){
				return true;
			}
			//var_dump($tags);
		}
		
		return false;
	}
	public function test_greatfoot($tags){
		if(json_encode($tags)=='{"highway":"footway","surface":"asphalt"}')return true;
		return false;
	}
	public function test_foot($tags){
		if(json_encode($tags)=='{"highway":"footway"}')return true;
		if(json_encode($tags)=='{"highway":"footway","surface":"sett"}')return true;
		if(json_encode($tags)=='{"foot":"designated","highway":"road"}')return true;
		return false;
	}
	public function add_lbroads_tags($element){
		if($element['type']=='node')return $element;
		if($this->test_great($element['tags'])){
			$element['tags']=['lbroad'=>'great'];
		}elseif($this->test_bicyundefined($element['tags'])){
			$element['tags']=['lbroad'=>'bicycle_undefined'];
		}elseif($this->test_bikeline($element['tags'])){
			$element['tags']=['lbroad'=>'bikelane'];
		}elseif($this->test_greatfoot($element['tags'])){
			$element['tags']=['lbroad'=>'great_foot'];
		}elseif($this->test_foot($element['tags'])){
			$element['tags']=['lbroad'=>'foot'];
		}else{
			//var_dump($element['tags']);
		}
		return $element;
	
	}
	
	
}