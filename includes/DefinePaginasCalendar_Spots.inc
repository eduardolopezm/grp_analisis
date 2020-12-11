<?php 
/*
 Creado por: Jesus Guadalupe Vargas Montes
 Fecha Creacion: 24/03/2014
 Descripcion: Clase para el alta de calendario//
 */
Class Paginas{
	var $LineItems;
	var $categoria;
	var $tipopagina;
	var $NumPaginas;
	var $CodUnico;
	var $leyenda;
	var $NumLine;

function Paginas($categoria,$tipopagina,$NumPaginas,$CodUnico,$leyenda,$NumLine){
	$this->categoria = $categoria;
	$this->tipopagina =$tipopagina;
	$this->NumPaginas =$NumPaginas;
	$this->CodUnico =$CodUnico;
	$this->leyenda =$leyenda;
	$this->LineItems = array();	
	$this->NumLine = $NumLine;
}
}


?>