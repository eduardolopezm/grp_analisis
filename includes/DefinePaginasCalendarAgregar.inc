<?php 
/*
 Creado por: Jesus Guadalupe Vargas Montes
 Fecha Creacion: 01/08/2013
 Descripcion: Clase para el alta de calendario
 */
Class PaginasAgregar{
	var $LineItems;
	var $Calendarid;
	var $Fechas;
	var $categoria;
	var $tipopagina;
	var $NumPaginas;
	var $NumLine;

	function PaginasAgregar($Calendarid,$Fechas,$categoria,$tipopagina,$NumPaginas,$CodUnico,$NumLine){
		$this->Calendarid = $Calendarid;
		$this->categoria = $categoria;
		$this->Fechas = $Fechas;
		$this->tipopagina =$tipopagina;
		$this->NumPaginas =$NumPaginas;
		$this->CodUnico =$CodUnico;
		$this->LineItems = array();	
		$this->NumLine = $NumLine;
	}
}


?>