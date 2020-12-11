<?php

$PageSecurity = 8;
$funcion=2387;

include('includes/session.inc');
$title = _('Ministración');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
//include('includes/AccountSectionsDef.inc');
include("includes/SecurityUrl.php"); 
setlocale(LC_ALL, 'es_ES');

$nombreArchivo = traeNombreFuncionGeneral($funcion, $db) . '_' . date('dmYHis');

$nombreArchivo = str_replace(" ", "_", $nombreArchivo);

//Inicio de exportación en Excel
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=$nombreArchivo.xls");
header("Pragma: no-cache");
header("Expires: 0");

$htmlCabecera="";
$htmlCuerpo="";
$htmlFooter="";

$estatus="";

setlocale(LC_MONETARY, 'en_US');


if(!empty($_GET["Folio"]) and $_GET["Folio"] !=""){

    // $SQLCabecera="  SELECT tb_m.ln_ur as ur, 
    //             concat(tb_m.ln_ur,' - ',tags.tagdescription) as entidadPublica,
    //             tags.tagdescription,
    //             lbusiness.taxid as rfcEntidad,
    //             tb_m.folio,
    //             truncate(coalesce(SUM(tb_m_d.solicitado),0),2) as solicitado,
    //             truncate(coalesce(SUM(tb_m_d.autorizado),0),2) as autorizado,
    //             -- concat(tb_m.ln_ur ,' - ', tb_m.folio,'/', year(fecha_elab)) as numControl,
    //             concat(tb_m.ln_pp,' - ',tb_pp.descripcion) as programaPresupuestal,
    //             tb_m.estatus,
    //             year(tb_m.fecha_elab) as anioElabo,
    //             tb_m.ln_mes,
    //             tb_m.fecha_pago,
    //             tb_m.fecha_elab,
    //             tb_beneficiario.nombre as nombrebeneficiario,
    //             tb_beneficiario.rfc as rfcbeneficiaro,
    //             tb_beneficiario.cuenta as cuentabeneficiaro,
    //             tb_beneficiario.plaza,
    //             tb_beneficiario.denominaciontesofe,
    //             tb_beneficiario.nombre_cuenta
    //         FROM tb_ministracion tb_m
    //         LEFT JOIN tags on tb_m.ln_ur = tags.tagref
    //         LEFT JOIN legalbusinessunit lbusiness on tags.legalid  = lbusiness.legalid
    //         LEFT JOIN tb_ministracion_detalle tb_m_d on tb_m.id = tb_m_d.idMinistracion
    //         LEFT JOIN tb_cat_programa_presupuestario tb_pp on tb_m.ln_pp = tb_pp.cppt
    //         LEFT JOIN tb_beneficiario_concentradora tb_beneficiario ON tb_m.idBeneficiario = tb_beneficiario.id
    //         WHERE tb_m.id= '".$_GET["idMinistracion"]."'
    //         GROUP BY tb_m.ln_ur;";

    $SQLCabecera="	SELECT  tb_m.estatus
    				FROM tb_ministracion tb_m
    				WHERE tb_m.id= '".$_GET["idMinistracion"]."'";
    $resultCabecera = DB_query($SQLCabecera, $db);

    if($resultCabecera){
        while ($myrowCabecera = DB_fetch_array($resultCabecera)) {
        	$estatus = $myrowCabecera['estatus'];
        }
    }

    $SQLCuerpo="SELECT
					concat(cdbt.anho,'-',cdbt.cve_ramo,'-',cdbt.tagref,'-',cdbt.id_finalidad,'-',cdbt.id_funcion,'-',cdbt.id_subfuncion,'-',cdbt.cprg,'-',cdbt.cain,'-',cdbt.cppt,'-',cdbt.partida_esp,'-',cdbt.ctga,'-',cdbt.cfin,'-',cdbt.cgeo,'-',cdbt.pyin,'-',cdbt.ln_aux1,'-',cdbt.ln_aux2, '-',cdbt.ln_aux3) as desc_capitulo,
					truncate(coalesce(SUM(tb_md.solicitado),0),2) as solicitado,
					truncate(coalesce(SUM(tb_md.autorizado),0),2)  as autorizado,
					dtMinistracion.estatus
				FROM tb_ministracion_detalle tb_md
				LEFT JOIN chartdetailsbudgetbytag cdbt ON tb_md.presupuesto = cdbt.accountcode
				LEFT JOIN (SELECT id,estatus FROM tb_ministracion WHERE id = '".$_GET["idMinistracion"]."') dtMinistracion ON tb_md.`idMinistracion` = dtMinistracion.id
				WHERE idMinistracion = '".$_GET["idMinistracion"]."'
				GROUP BY cdbt.anho,
					cdbt.cve_ramo,
					cdbt.tagref,
					cdbt.id_finalidad,
					cdbt.id_funcion,
					cdbt.tagref,
					cdbt.id_subfuncion,
					cdbt.cprg,
					cdbt.cain,
					cdbt.cppt,
					cdbt.partida_esp,
					cdbt.ctga,
					cdbt.cfin,
					cdbt.cgeo,
					cdbt.pyin,
					cdbt.ln_aux1,
					cdbt.ln_aux2,
					cdbt.ln_aux3;";

	$resultCuerpo = DB_query($SQLCuerpo, $db);

    if($resultCuerpo){

    	$htmlCuerpo='<table border="1" class="table table-bordered" name="tablaReducciones" id="tablaReducciones">
			         	<tbody>
			          		<tr class="header-verde">
			          			<td>#</td>
			          			<td>Clave Presupuestarias</td>
			          			<td>Importe</td>
			          		</tr>
			          
			        ';
		$importe=0;
		$contador = 1;
        while ($myrowCuerpo = DB_fetch_array($resultCuerpo)) {

        	$importe=$myrowCuerpo['solicitado'];

        	if($estatus==5){
        		$importe=$myrowCuerpo['autorizado'];
        	}

        	$htmlCuerpo.='<tr>
        				  	<td>'.$contador.'</td>
        				  	<td>'.$myrowCuerpo['desc_capitulo'].'</td>
        				  	<td>'.money_format('%n ', $importe) .'</td>
        				  </tr>';

        	$contador++;
        }
        $htmlCuerpo.='	</tbody>
        			  </table>';
    }

    echo $htmlCuerpo;

}

?>