<?php
/* $Revision: 4.0 $ 
ARCHIVO MODIFICADO POR: ALEJANDRA ROSAS

COMENTARIOS: Impresion de encabezado de fichas deposito por pagare contenido en la factura
	     La impresion de este formato es preimpreso y configurable
             (El formato de impresion tambien llamada caida, es configurado por un usuario con conocimientos tecnicos).            
             
FECHA DE MODIFICACION: 11-MARZO-2010 
CAMBIOS:
   1.- Imprime el encabezado de la ficha de deposito por cada pagare que contiene la factura
   2.- Se comento el codigo
FIN DE CAMBIOS
*/

// Consulta que extrae la informacion del encabezado de acuerdo al tipo de formato que se desea imprimir
$sql=" SELECT *
       FROM conf_printdocsdetails
       WHERE tipo='encabezado'
	AND conf_printdocsdetails.u_conf=".$u_conf ."
	AND conf_printdocsdetails.activo=1";
$result_h=DB_query($sql,$db, $ErrMsg);
// Mientras exista informacion para este formato envia datos de encabezado a pdf
while ($myrow_h=DB_fetch_array($result_h))
{ 	
	/*
	Extracion de datos tales como:
		- Posicion X es la ubicacion en el ancho de la hoja
		- Posicion Y es la ubicacion en lo alto de la hoja
		- tamaño de letra
		- Longitud espacio delimitado para la impresion del dato
		- Decimales se utiliza cuando se necita el formato de number_format
	*/
	if ($myrow_h['valor']=='total') {
		$posxtot=$myrow_h['posx'];
		$posytot=$myrow_h['posy'];
		$fontsizetot=$myrow_h['font_size'];
		$lontot=$myrow_h['longitud'];
		$dectot=$myrow_h['decimales'];
	}
	
	if ($myrow_h['valor']=='canletra') {
		$posxcanletra=$myrow_h['posx'];
		$posycanletra=$myrow_h['posy']-$mas;
		$fontsizecanletra=$myrow_h['font_size'];
		$loncanletra=$myrow_h['longitud'];
	}
	
	//Si valor es igual a Label se imprime el dato escrito por el usuario
	if ($myrow_h[3]=='Label') {
		
		
		if($myrow_h['valor']!='idpagare'){
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy']-$mas,$myrow_h['longitud'],$myrow_h['font_size'],$myrow_h['valor']);
			
		}else{
			
		}
	} else {
		//Variable local que aloja los datos que contiene valor
		$valor=$myrowdatos[$myrow_h['posvalor']];
		//Si valor en formato es igual a numerico valor se guarda en la variable con el formato number_format
		if (trim($myrow_h['formato'])=='numerico') {
			$valor=number_format($valor,$myrow_h['decimales']);
		}
		//Si valor en formato es igual a fecha valor se guarda en la variable con el formato ConvertSQLDate
		if (trim($myrow_h['formato'])=='fecha') {
			$valor=ConvertSQLDate($valor);
		}
		//Si valor es igual a iva se imprime alineado a la derecha
		if ($myrow_h['valor']=='iva') {
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy']-$mas,$myrow_h['longitud'],$myrow_h['font_size'],$valor,'right');
		}
		//Si valor es diferente de iva y formato es diferente de numerico se imprime valor sin formato especial
		if ($myrow_h['valor']!='iva' and $myrow_h['formato']!='numerico') {
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy']-$mas,$myrow_h['longitud'],$myrow_h['font_size'],$valor);
		}
		
		if ($myrow_h['valor']=='lugar1') {
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy']-$mas,$myrow_h['longitud'],$myrow_h['font_size'],$_SESSION['CompanyRecord']['regoffice1']);
		}
		
		if ($myrow_h['valor']=='lugar2') {
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy']-$mas,$myrow_h['longitud'],$myrow_h['font_size'],$_SESSION['CompanyRecord']['regoffice2']);
		}
		
		if ($myrow_h['valor']=='lugar3') {
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy']-$mas,$myrow_h['longitud'],$myrow_h['font_size'],$_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
		}
	}//Fin impresion datos de encabezado a pdf
}// Fin mientras exista informacion para este formato envia datos de encabezado a pdf

?>
