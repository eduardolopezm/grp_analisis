<?php

class CfdiRelacionados {

	private $uuid;
	private $TipoRelacion;

	public function getTipoRelacion() {
		return $this->TipoRelacion;
	}

	public function setTipoRelacion($TipoRelacion) {
		$this->TipoRelacion = $TipoRelacion;
	}

	public function getUuid() {
		return $this->uuid;
	}

	public function setUuid($uuid) {
		$this->uuid = $uuid;
	}
}
?>
