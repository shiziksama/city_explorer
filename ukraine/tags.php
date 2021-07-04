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
while($z->read()) {
	if($z->name=='node'&&$z->nodeType==\XmlReader::ELEMENT){
		$z->endElement();
		continue;
	}elseif($z->name=='way'&&$z->nodeType==\XmlReader::ELEMENT){
		
		$node=simplexml_load_string($z->readOuterXml());
		$tags=[];
		foreach($node as $key=>$child){
			if($key=='tag'){
				$tags[''.$child->attributes()->k]=''.$child->attributes()->v;
			}
		}
		if(empty($tags['highway'])){
			$z->endElement();
			continue;
		}
		$el=$lbroads->modify_tags(['tags'=>$tags]);
		$elements=$lbroads->filter_overpass([$el]);
		
		if(empty($elements)){
			$z->endElement();
			continue;
		}
		$elements[0]['type']='way';
		$element=$lbroads->add_lbroads_tags($elements[0]);
		$tags=$element['tags'];
		
		if(empty($tags)){
			$z->endElement();
			continue;
		}
		echo json_encode($tags).PHP_EOL;
		/*
		foreach($tags as $k=>$v){
			echo $k.'|'.$v.PHP_EOL;
		}*/
		$z->endElement();
	}
}
