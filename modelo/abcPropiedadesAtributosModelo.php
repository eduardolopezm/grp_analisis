<?php
/**
 * Modelo para el ABC de Fuente del Recurso
 * 
 * @category ABC
 * @package ap_grp
 * @author Jesùs Reyes Santos <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
/*
stockid = clave
description = descripcion
Units = unidad de medida
mbflag B fijo
decimalplace fijo2
discontinued = activo
sat_stock_code = id_producto

borrar eliminar

SELECT DISTINCT id_parcial , desc_parcial, tb_cat_objeto_parcial.estatus as estatus, tb_cat_objeto_parcial.disminuye_ingreso as ingreso, locations.loccode as idFinalidad, locations.locationname as finalidad
            FROM tb_cat_objeto_parcial
            JOIN locations on (tb_cat_objeto_parcial.loccode = locations.loccode)
            ".$sqlUR."
            ORDER BY locations.loccode, id_parcial ASC";

*/
session_start();

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
//header('Content-type: text/html; charset=ISO-8859-1');
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2510;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];
if ($option == 'obtenerDatos') {
    $folio = $_POST['folio'];
    //$sqlUR = " WHERE  stockmaster.tipo_dato = 2";

    // if (!empty($id_parcial)) {
       
    // }
    $info = array();

    $SQL = "SELECT 
            id_atributos as atributos, 
            ln_etiqueta as etiqueta
            FROM tb_cat_atributos_contrato
            WHERE id_contratos = '".$folio."' and ind_activo = 1 
            ORDER BY id_atributos ASC";
    $ErrMsg = "No se obtuvieron los contratos del contrato";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Atributos' => $myrow ['atributos'],
            'Etiqueta' => $myrow ['etiqueta'],
            'Valor' => ''
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if($option == 'obtenerDatosactualizar'){
    $folioContrato = $_POST['folioContrato'];
    $folio = $_POST['folio'];


    
    $SQL = "SELECT 
    id_atributos as atributos, 
    ln_etiqueta as etiqueta,
    tb_propiedades_atributos.ln_valor as valor,
    tb_propiedades_atributos.id_folio_contrato as contrato
    FROM tb_cat_atributos_contrato
    LEFT JOIN tb_propiedades_atributos ON (tb_propiedades_atributos.id_etiqueta_atributo =	tb_cat_atributos_contrato.id_atributos AND tb_propiedades_atributos.id_folio_contrato = '".$folioContrato."') 
    WHERE id_contratos = '".$folio."'  AND  ind_activo = 1 
    ORDER BY id_atributos ASC";

   

    $ErrMsg = "No se obtuvieron los contratos del contrato";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Atributos' => $myrow ['atributos'],
            'Etiqueta' => $myrow ['etiqueta'],
            'Valor' => ($myrow['valor'] == NULL ? '': $myrow['valor'])
        );
    }
    $contenido = array('datos' => $info);
    $result = true;
}


if ($option == 'guardarSuficiencia') {
    $datos = $_POST['datos'];
    $id_folio_contrato = $_POST['id_folio_contrato'];
    $id_folio_configuracion = $_POST['id_folio_configuracion'];
    $id_etiqueta_atributo="";
    $ln_valor="";

    foreach ($datos as $datosChidos) {
        $id_etiqueta_atributo = $datosChidos['Atributos'];
        $ln_valor = $datosChidos['Valor'];
        
        $SQL="INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
        VALUES('".$id_folio_contrato."', '".$id_folio_configuracion."', '".$id_etiqueta_atributo."', '".$ln_valor."', '1')";

        $ErrMsg = "No se agrego la informacion del contrato  ".$id_folio_contrato;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se agregó el registro ".$id_folio_contrato." de los atributos éxito";
        $result = true;

    }
    if($id_folio_configuracion == 7){

    //GENERATE AUTOMATICO ADEUDO
        $sql = "SELECT contratos.id_contrato as clave,
        CONCAT(tags.tagref , ' - ' , tags.tagdescription ) AS unidadNegocio,
        tags.tagref AS unidadNegocioID,
        CONCAT(ues.ue , ' - ' , ues.desc_ue ) AS unidadEjecutora,
        ues.ue AS unidadEjecutoraID,
        CONCAT(configContrato.id_contratos, ' - ', configContrato.id_loccode,' - ', locations.locationname) AS configContrato,
        configContrato.id_contratos AS idconfContrato,
        debtorsmaster.debtorno as contribuyenteID,
        tb_cat_objetos_contrato.id_stock as objetoParcial,
        tb_cat_objetos_contrato.id_loccode as objetoPrincipal,
        tb_cat_objetos_contrato.amt_valor as importe,
        tb_contratos_objetos_parciales.nu_cantidad as cantidad,
        tb_contratos_objetos_parciales.amt_total as total,
        contratos.dtm_fecha_inicio as fechaInicio,
        contratos.dtm_fecha_vigencia as fechaVigencia,
        contratos.nu_periodicidad as periodicidad,
        contratos.enum_periodo as periodo,
        contratos.enum_status as status
        FROM tb_contratos AS contratos 
        JOIN tags on (tags.tagref = contratos.tagref)    
        JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
        JOIN tb_contratos_contribuyentes as configContrato on (configContrato.id_contratos = contratos.id_confcontratos) 
        JOIN locations on (configContrato.id_loccode = locations.loccode)  
        JOIN debtorsmaster on (debtorsmaster.debtorno = contratos.id_debtorno)	
        JOIN tb_contratos_objetos_parciales on tb_contratos_objetos_parciales.id_contrato = '".$id_folio_contrato."'
        JOIN tb_cat_objetos_contrato on tb_cat_objetos_contrato.id_objetos = tb_contratos_objetos_parciales.id_objetos
        WHERE contratos.id_contrato = '".$id_folio_contrato."' AND contratos.ind_activo = '1'";

        $result = DB_query($sql, $db);

        // comprobación de existencia de la información
        if(DB_num_rows($result) == 0){
            $data['msg'] = 'No se encontraron los datos solicitados. Por favor verifique si tiene objetos detalle configurados';
            return $data;
        }
        // procesamiento de la información obtenida
        $cantidadPeriodo = 0;
        $total = 0;
        $mes = 0;

        while ($rs = DB_fetch_array($result)) {
            // $mes = 0;
            $me = 0;
            $mes = (int)date("m",strtotime($rs['fechaInicio']));

            if($rs['periodo'] == 'Mes'){
                // $me = round(12 - $mes + 1 );
                // $cantidadPeriodo = $me / (int)$rs['periodicidad'];
                $me = 12;
                $cantidadPeriodo = $me / (int)$rs['periodicidad'];
            }else{
                $cantidadPeriodo = 1;
            }
            $data['content'][] = [
                'contrato'=>utf8_encode($rs['clave']),// 0
                'cantidadPeriodo'=> round($cantidadPeriodo),// 0
                'contribuyenteID'=>utf8_encode($rs['contribuyenteID']),// 0
                'periodicidad'=>utf8_encode($rs['periodicidad']),// 0
                'periodo'=>utf8_encode($rs['periodo']),// 0
                'unidadNegocioID'=>utf8_encode($rs['unidadNegocioID']),// 0
                'unidadEjecutoraID'=>utf8_encode($rs['unidadEjecutoraID']),// 0
                'periodoCal'=> date("Y",strtotime($rs['fechaInicio']))."01",// 0
                'objetoPrincipal'=>utf8_encode($rs['objetoPrincipal']),// 0
                'objetoParcial'=>utf8_encode($rs['objetoParcial']),// 1
                'cantidad'=>utf8_encode($rs['cantidad']),// 1
                'importe'=>utf8_encode($rs['importe']),// 2
                'total'=>utf8_encode($rs['total']),// 2
                'fechaInicial'=> utf8_encode($rs['fechaInicio']),
                'fechaFinal'=> utf8_encode($rs['fechaVigencia']),
                'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 7
                'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',// 7
                'identificador'=>utf8_encode($rs['clave'])// 9
            ];
        }


        $anio = 0;
        $contt = 0;
        // $mes = date("n",$data['content']['fechaInicial']);
        $perioNum = 0;
        for ($i=0; $i < $cantidadPeriodo; $i++) {
            $perioNum = ($mes+$contt);
            if($perioNum > 12 ){
                $mes = 1;
                $contt = 0;
                $anio++;
                $perioNum = 1;
            }
            

            foreach ($data['content'] as $key => $value) {
                # code...
                // if($i > 0){
                    // $final = date("Y-m-d", strtotime("+1 month", $time));

                    // $final = date("Y-m-d",strtotime($value['fechaInicial']."+".$i." month"));
                    $final = date("Y-m-t", strtotime($rs['fechaInicial']."+".$i." month"));


                // }else{
                // 	$final = date("Y-m-t", strtotime($rs['fechaInicial']));

                // }
            
            $sql = "INSERT INTO `tb_administracion_contratos` 
                (
                    id_contrato, 
                    id_contribuyente, 
                    id_periodo,
                    id_objeto_principal,
                    id_objeto_parcial,
                    nu_cantidad,
                    mtn_importe,
                    mtn_total,
                    dt_vencimineto, 
                    pase_cobro, 
                    folio_recibo,
                    cajero, 
                    dt_fechadepago, 
                    estatus, 
                    dtm_fecha_efectiva
                )
            VALUES
                (
                    ".$value['contrato'].",
                    ".$value['contribuyenteID'].",
                    '".(date("Y",strtotime($value['fechaInicial'])) + $anio).str_pad(($perioNum), 2, '0', STR_PAD_LEFT)."',
                    '".$value['objetoPrincipal']."',
                    '".$value['objetoParcial']."',
                    '".$value['cantidad']."',
                    '".$value['importe']."',
                    '".$value['total']."',
                    '".$final."',
                    '',
                    '',
                    '',
                    '',
                    'En Proceso',
                    now()
                )";
                try {
                    $data['sql'] = $info['contrato'];
                    $result = DB_query($sql, $db);
                    if($result == true){
                        $data['success'] = true;
                        $data['msg'] = "<p>Se ha generado el adeudo exitosamente.</p>";
                        DB_Txn_Commit($db);
                    }else{
                        DB_Txn_Rollback($db);
                    }
                } catch (Exception $e) {
                    // captura del error
                    $data['msg'] .= '<br>'.$e->getMessage();
                    DB_Txn_Rollback($db);
                }
            }
            $contt++;
        }
    }
}

if ($option == 'actualizarAtributos') {
    $datos2 = $_POST['datos2'];
    $id_folio_contrato = $_POST['id_folio_contrato'];
    $id_folio_configuracion = $_POST['id_folio_configuracion'];
    
   
    foreach ($datos2 as $datosChidos) {
        $id_etiqueta_atributo = $datosChidos['Atributos'];
        $ln_valor = $datosChidos['Valor'];

        if(fnValidarExiste($id_etiqueta_atributo, $id_folio_contrato, $db)){
            $SQL="UPDATE tb_propiedades_atributos 
            SET tb_propiedades_atributos.ln_valor = '$ln_valor' WHERE tb_propiedades_atributos.id_etiqueta_atributo = '$id_etiqueta_atributo' and tb_propiedades_atributos.id_folio_contrato = '$id_folio_contrato' ";


            $ErrMsg = "No se agrego la informacion del contrato  ".$id_folio_contrato;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro ".$id_folio_contrato." de los atributos éxito";
            $result = true;
        }else{

            $SQL="INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
            VALUES('".$id_folio_contrato."', '".$id_folio_configuracion."', '".$id_etiqueta_atributo."', '".$ln_valor."', '1')";

            $ErrMsg = "No se agrego la informacion del contrato  ".$id_folio_contrato;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro ".$id_folio_contrato." de los atributos éxito";
            $result = true;
            
        }

    }
}

function fnValidarExiste($id_etiqueta_atributo, $id_folio_contrato, $db){
    $SQL = "SELECT * FROM tb_propiedades_atributos WHERE nu_activo = 1 and id_etiqueta_atributo = '".$id_etiqueta_atributo."' and id_folio_contrato = '".$id_folio_contrato."' ORDER BY id_etiqueta_atributo ASC";
    
    
    $ErrMsg = "No se encontro la informacion de ".$id_etiqueta_atributo;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $existeFin = true;
    }else{
        $existeFin = false;
    }
    return $existeFin;
}

if($option == 'obtenerDatosAvanzar'){
    $folioContrato = $_POST['folioContrato'];
    $folio = $_POST['folio'];


    
    $SQL = "SELECT 
    id_atributos as atributos, 
    ln_etiqueta as etiqueta,
    tb_propiedades_atributos.ln_valor as valor,
    tb_propiedades_atributos.id_folio_contrato as contrato
    FROM tb_cat_atributos_contrato
    LEFT JOIN tb_propiedades_atributos ON (tb_propiedades_atributos.id_etiqueta_atributo =	tb_cat_atributos_contrato.id_atributos AND tb_propiedades_atributos.id_folio_contrato = '".$folioContrato."') 
    WHERE id_contratos = '".$folio."'  AND  ind_activo = 1 
    ORDER BY id_atributos ASC";

   

    $ErrMsg = "No se obtuvieron los contratos del contrato";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Atributos' => $myrow ['atributos'],
            'Etiqueta' => $myrow ['etiqueta'],
            'Valor' => ($myrow['valor'] == NULL ? '': $myrow['valor'])
        );
    }
    $contenido = array('datos' => $info);
    $result = true;
}


$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);


