<?php
/**
 * @date: 13.02.18
 * @author desarrollo.grp
 * En este archivo se encuentra toda la cinfiguracion para la genercion delos datos del itinerario asicomo el manejo de la misma información
 */

function guardaItinerario($db,$datos,$solicitud,$line)
{
	$data = ['success'=>false,'msg'=>'Ocurrió un error al momento de generar la información'];
	DB_Txn_Begin($db);
	try {
		# comprobación de la existencia de los estados
		if(!empty($datos['pais'])){
			$datos['idEstadoDestino'] = $datos['pais'];
			$datos['municipioItinerario'] = $datos['pais'];
		}
		# comprobación de la cantidad de pernocta que se asigna
		$pernocta = empty($datos['pernocta'.$line])?0:$datos['pernocta'.$line];
		# generación del query
		$sql = "INSERT INTO `tb_solicitud_itinerario` (`id_nu_solicitud_viaticos`,`nu_destino_pais`,`nu_destino_estado`,`nu_destino_municipio`,`dt_periodo_inicio`,`dt_periodo_termino`, `amt_cuota_diaria`,`nu_dias`,`amt_importe`,`ind_pernocta`,`ch_zona_economica`) VALUES ('".$solicitud['id']."','".( $datos['pais'.$line]!="" ? $datos['pais'.$line] : 0 )."','".( $datos['idEstadoDestino'.$line]!="" ? $datos['idEstadoDestino'.$line] : 0 )."','".( $datos['municipioItinerario'.$line]!="" ? $datos['municipioItinerario'.$line] : 0 )."','".date_format(date_create_from_format('d-m-Y', $datos['fechaInicio'.$line]),'Y-m-d')."','".date_format(date_create_from_format('d-m-Y', $datos['fechaTermino'.$line]),'Y-m-d')."','".str_replace(",","",$datos['cuota'.$line])."','".$datos['dias'.$line]."','".str_replace(",","",$datos['importe'.$line])."','$pernocta','".$datos["zonaEconomica".$line]."')";
       

		$result = DB_query($sql, $db);
		
		if($result == true) {
			$data['success'] = true;
			DB_Txn_Commit($db);
		}
	} catch (Exception $e) {
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
	}
	return $data;
}

function convierteFechas($fecha)
{
	$fTemp = explode('-', $fecha);
	return $fTemp[2].'-'.$fTemp[1].'-'.$fTemp[0];
}

function calculaTotalItinerario($datos)
{
	$total = 0;
	$i=0;
	foreach ($datos as $key => $linea) { 
		$total += (float) $linea['importe'.$i]; 
		$i++;
	}
	return $total;
}
