<?php
include __DIR__.'/../app/LBRoads.php';
$lbroads=new \App\LBroads;
class WeblamasXMLReader extends \XMLReader{
	public function endElement(){
		$q=$this->name;
		if($this->isEmptyElement)return;
		while($this->read()){
			if($this->name==$q&&$this->nodeType==\XmlReader::END_ELEMENT){
				return;
			}
		}
	}
	
}


//$z=new WeblamasXMLReader;
$z = new WeblamasXMLReader();
$z->open("php://stdin");
echo "<?xml version='1.0' encoding='UTF-8'?>".PHP_EOL;
while($z->read()) {
	if($z->name=='node'&&$z->nodeType==\XmlReader::ELEMENT){
		echo '<node id="'.$z->getAttribute('id').'" lat="'.$z->getAttribute('lat').'" lon="'.$z->getAttribute('lon').'"/>'.PHP_EOL;
		$z->endElement();
		continue;
		//break;
	}elseif($z->name=='way'&&$z->nodeType==\XmlReader::ELEMENT){
//		var_dump($z->expand());
//		die();
//		$doc = new \DOMDocument;
//		$node = simplexml_import_dom($doc->importNode($z->expand(), true));
		//foreach($node->)
//		var_dump($node->asXML());
		
		$node=simplexml_load_string($z->readOuterXml());
		$nodes=[];
		$tags=[];
		foreach($node as $key=>$child){
			if($key=='nd'){
				$nodes[]=''.$child->attributes()->ref;
			}elseif($key=='tag'){
				$tags[''.$child->attributes()->k]=''.$child->attributes()->v;
			}
		}
		if(empty($tags['highway'])){
			$z->endElement();
			continue;
		};//Только дороги
		//var_dump($tags);
		$el=$lbroads->modify_tags(['tags'=>$tags]);
		$elements=$lbroads->filter_overpass([$el]);
		
		if(empty($elements)){
			$z->endElement();
			continue;
		}
		$tags=$elements[0]['tags'];
		
		if(empty($tags)){
			$z->endElement();
			continue;
		}
		//var_dump($z->readOuterXml());
		echo '<way id="'.$node->attributes()->id.'">'.PHP_EOL;
		foreach($nodes as $nd){
			echo '	<nd ref="'.$nd.'"/>'.PHP_EOL;
		}
		foreach($tags as $k=>$v){
         echo '	<tag k="'.$k.'" v="'.$v.'"/>'.PHP_EOL;
		}
        echo '</way>'.PHP_EOL;
		//die();
		//var_dump($tags);
		//var_dump(''.$node->attributes()->id);
		$z->endElement();
		//var_dump('way');
		//break;
	}else{
		if($z->nodeType==\XmlReader::ELEMENT){
			$attributes='';
			$name=$z->name;
			if($z->hasAttributes)  {
				while($z->moveToNextAttribute()) { 
					$attributes.=' '.$z->name. '="'. $z->value. '"'; 
				}
			}
			echo '<'.$name.$attributes.'>'.PHP_EOL;
		}elseif($z->nodeType==\XmlReader::END_ELEMENT){
			echo '</'.$z->name.'>'.PHP_EOL;
		}
		/*
		<osm version="0.6" generator="osmconvert 0.8.10" timestamp="2021-02-01T21:42:02Z">
        <bounds minlat="44.008624" minlon="22.132644" maxlat="52.386497" maxlon="40.238113"/>
		*/
	}
	//var_dump($z->nodeType);
	//die();
	//echo '';//TODO echo readed item
	//if(@!$z->read())break;//one more very bad solution
}
/*
for($i=0;$i<10;$i++){
$line = fgets(STDIN);
var_dump($line);
}
*/
echo '</xml>';