<?php
namespace App;
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