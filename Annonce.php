<?php
class Annonce{
	public $titre = '';
	public $url = '';
	public $properties = array();
	public $description = '';
	
	public function __construct($url){
		$this->url = $url;
	}
	
	public function addProperty($name, $value){
		$this->properties[$name] = $value;
	}
	
	public function __toString(){
		$str = $this->titre.chr(10);
		for($i = 0; $i < min( 30, strlen($this->titre)); $i++){
			$str .= '-';
		}
		$str .= chr(10).$this->url.chr(10).chr(10);
		
		foreach ($this->properties as $propName => $propVal){
			$str .= '* '.$propName.' : '.$propVal.chr(10);
		}
		
		$str .= chr(10).strip_tags($this->description).chr(10).chr(10).chr(10);
		
		return $str;
	}
}