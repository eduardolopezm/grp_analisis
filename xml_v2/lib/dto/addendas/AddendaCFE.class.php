<?php
class AddendaCFE {
	
	private $type = 'CFE';
	private $impdap;
	private $sdoant;
	
	public function getType() {
		return $this->type;
	}
	
	public function getImpdap() {
		return $this->impdap;
	}
	
	public function setImpdap($impdap) {
		$this->impdap = $impdap;
	}
	
	public function getSdoant() {
		return $this->sdoant;
	}
	
	public function setSdoant($sdoant) {
		$this->sdoant = $sdoant;
	}
}