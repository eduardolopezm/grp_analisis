<?php 
/*
 Creado por: Jesus Guadalupe Vargas Montes
 Fecha Creacion: 05/07/2013
 Descripcion: Clase para el alta de productos
 */
Class Transfer{
	var $LineItems;
	var $StockidItem;
	var $DescriptionTem;
	var $QuantityItem;
	var $NumLine;

function Transfer($StockidItem,$DescriptionTem,$QuantityItem,$NumLine){
	$this->StockidItem = $StockidItem;
	$this->DescriptionTem = $DescriptionTem;
	$this->QuantityItem =$QuantityItem ;
	$this->LineItems = array();	
	$this->NumLine = $NumLine;
}
}


?>