<?php 
/*
 Creado por: Jesus Guadalupe Vargas Montes
 Fecha Creacion: 30/07/2013
 Descripcion: Clase para el alta de calendario
 */
Class Paginas{
	var $LineItems;
	var $categoria;
	var $tipopagina;
	var $NumPaginas;
	var $CodUnico;
	var $NumLine;

function Paginas($categoria,$tipopagina,$NumPaginas,$CodUnico,$NumLine){
	$this->categoria = $categoria;
	$this->tipopagina =$tipopagina;
	$this->NumPaginas =$NumPaginas;
	$this->CodUnico =$CodUnico;
	$this->LineItems = array();	
	$this->NumLine = $NumLine;
}
}


?>