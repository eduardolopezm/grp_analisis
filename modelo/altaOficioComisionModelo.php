<?php
/**
 * Viaticos modelo
 *
 * @category panel
 * @package  ap_grp
 * @author   Luis Aguilar Sandoval
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 29/12/2017
 * Fecha Modificacion: 29/12/2017
 * Archivo que contiene toda la logica del negocio
 * @file: altaOficioComisionModelo.php
 */

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = '../';
// $funcion = 2318;
$funcion = 2338;
session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');


// inclucion de modelos separados
include('./itinerarioModelo.php');


# tipo de movimiento ubicado en las tabas "systypesinvtrans" y "systypescat"
define('TYPEMOV', 501);

/* VARIABLE DE RESPUESTA */
define('DATA', [
    'selectUnidadNegocio'=>['col'=>"tagref", 'type'=>'string'],
    'selectUnidadEjecutora'=>['col'=>'id_nu_ue','type'=>'string'],
    'noOficio'=>['col'=>"sn_folio_solicitud", 'type'=>'string'],
    'idEmpleado'=>['col'=>"id_nu_empleado", 'type'=>'int'],
    'tipoSol'=>['col'=>"ind_tipo_solicitud", 'type'=>'int'],
    'now'=>['col'=>"dtm_fecha_elaboracion", 'type'=>'date'],
    'txtAreaObs'=>['col'=>'ln_objetivo_comicion', 'type'=>'string'],
    'fechaInicio'=>['col'=>'dtm_fecha_inicio', 'type'=>'string'],
    'fechaTermino'=>['col'=>'dtm_fecha_termino', 'type'=>'string'],
    'total'=>['col'=>'amt_importe_total', 'type'=>'int'],
    'trasnporte'=>['col'=>'ind_tipo_transporte', 'type'=>'int'],
    'idEstadoDestino'=>['col'=>'nu_destino_estado','type'=>'int'],
    'datosTransporte'=>['col'=>'amt_tansporte','type'=>'int'],
    'tipoGasto'=>['col'=>'ind_tipo_gasto','type'=>'int'],
    'cantPernocta'=>['col'=>'nu_porcentaje_pernocta','type'=>'int'],
    'clavePresupuestal'=>['col'=>'accountcode_general','type'=>'string'],
    'clavePresupuestalCombustible'=>['col'=>'accountcode_combustibles','type'=>'string'],
    'systypeno'=>['col'=>'systypeno','type'=>'string'],
    'homologar'=>['col'=>'homologar','type'=>'boolean'],
    'idEmpleadoHomologar'=>['col'=>'empleado_homologado','type'=>'string']
]);
    //'consecutivo'=>['col'=>'id_nu_oficio_ur','type'=>'int'],

/*switch ($_POST["method"]) {
    case 'getDataOficioComision':
        getDataOficioComision($db);
        break;
    case 'actualizarOficioActual':
        actualizarOficioActual($db);
        break;
    default:
        # code...
        break;
} */

function getSelects($db)
{
    $data = array('success'=>true, 'msg'=>'Funcion ejecutada correctamente');
    //obtenemos los n catalogos
    for ($i=0; $i<=3; $i++) {
        switch ($i) {
            case 0:
                $sql = "SELECT id_nu_empleado AS value, CONCAT(ln_nombre, ' ', sn_primer_apellido,' ',sn_segundo_apellido) AS texto
                        FROM tb_empleados
                        WHERE ind_activo=%d
                        ORDER BY ln_nombre ASC";
                $sql = sprintf($sql, 1);
                $empleados =  ejecutarsql($db, $sql);
                break;
            case 1:
                $sql = "SELECT id_nu_entidad_federativa as value,  ln_nombre_entidad_federativa as texto FROM tb_cat_entidad_federativa WHERE ind_activo=%d";
                $sql = sprintf($sql, 1);
                $entidad=ejecutarsql($db, $sql);
                break;
            case 2:
                $sql = "SELECT id_nu_tipo_gasto as value, sn_nombre as texto FROM tb_cat_tipo_gasto WHERE ind_activo=%d ORDER BY sn_nombre ASC";
                $sql = sprintf($sql, 1);
                $tipoGasto=ejecutarsql($db, $sql);
                break;
            case 3:
                $sql = "SELECT ln_nombre_descripcion as texto,id_nu_tipo_transporte as value FROM tb_cat_tipo_transporte";
                $transportes =ejecutarsql($db, $sql);   
        }
    }
    $data['content'] = array('empleados' => $empleados,'entidad' => $entidad,'tipoGasto' => $tipoGasto,'perfil'=>getPerfil($db),'transportes'=>$transportes);
    return $data;
}

function getEmployees($db){
    $data = array('success'=>true, 'msg'=>'Función ejecutada correctamente');

    if($_POST['homologado']){
        $Importes = array();

        $sql = "SELECT IF(`id_zona_economica` IS NULL, 'Internacional', `id_zona_economica`) AS `tipoCuota`, m.`amt_importe` AS `Importe`

                FROM `tb_empleados` AS e
                LEFT JOIN `tb_cat_puesto` AS p ON p.`id_nu_puesto` = e.`id_nu_puesto` AND p.`ind_activo` = 1
                LEFT JOIN `tb_cat_jerarquia` AS j ON j.`id_nu_jerarquia` = p.`id_nu_jerarquia` AND j.`ind_activo` = 1
                LEFT JOIN `tb_monto_jerarquia` AS m ON m.`id_nu_jerarquia` = j.`id_nu_jerarquia` AND m.`ind_activo` = 1

                WHERE e.`ind_activo` = '1'
                AND e.`ue` LIKE '$_POST[ue]'
                AND e.`id_nu_empleado` = '$_POST[idEmpleado]'

                ORDER BY IF(`id_zona_economica` IS NULL, 'Internacional', `id_zona_economica`) ASC";

        $result = DB_query($sql, $db);
        while($consultaImporte = DB_fetch_array($result)){
            $Importes[] = "(".( $consultaImporte['tipoCuota']=="Internacional" ? "m.`id_zona_economica` IS NULL" : "m.`id_zona_economica` = '$consultaImporte[tipoCuota]'" )." AND m.`amt_importe` > '$consultaImporte[Importe]')";
        }

        $Importes = ( COUNT($Importes) ? "AND ( ".implode(" OR ",$Importes)." )" : "" );

        $sql = "SELECT e.`id_nu_empleado` AS value, CONCAT(e.`ln_nombre`, ' ', e.`sn_primer_apellido`,' ',e.`sn_segundo_apellido`) AS texto

                FROM `tb_empleados` AS e
                LEFT JOIN `tb_cat_puesto` AS p ON p.`id_nu_puesto` = e.`id_nu_puesto` AND p.`ind_activo` = 1
                LEFT JOIN `tb_cat_jerarquia` AS j ON j.`id_nu_jerarquia` = p.`id_nu_jerarquia` AND j.`ind_activo` = 1
                LEFT JOIN `tb_monto_jerarquia` AS m ON m.`id_nu_jerarquia` = j.`id_nu_jerarquia` AND m.`ind_activo` = 1
                
                WHERE e.`ind_activo` = '1'
                AND e.`ue` LIKE '$_POST[ue]'
                $Importes

                GROUP BY e.`id_nu_empleado`
                ORDER BY `ln_nombre` ASC";
    }else{
        $sql = "SELECT `id_nu_empleado` AS value, CONCAT(`ln_nombre`, ' ', `sn_primer_apellido`,' ',`sn_segundo_apellido`) AS texto

                FROM `tb_empleados`

                WHERE `ind_activo` = '1'
                AND `ue` LIKE '$_POST[ue]'

                ORDER BY `ln_nombre` ASC";
    }

    $empleados =  ejecutarsql($db, $sql);
    $data['content'] = array('empleados' => $empleados);
    return $data;
}

function getDataEmploye($db)
{
    $data = array('success'=>true, 'msg'=>'Funcion ejecutada correctamente');
    // $sql="SELECT ln_nombre as nombre,  sn_primer_apellido AS primerApellido,  sn_segundo_apellido AS segundoApellido,  sn_rfc AS rfc,  tb_empleados.sn_codigo AS puesto, tb_cat_jerarquia.ln_descripcion AS jerarquia
    //     FROM tb_empleados
    //     INNER JOIN  tb_cat_puesto ON tb_cat_puesto.id_nu_puesto=tb_empleados.id_nu_puesto
    //     INNER JOIN tb_cat_jerarquia ON tb_cat_jerarquia.id_nu_jerarquia=tb_cat_puesto.id_nu_jerarquia
    //     WHERE tb_empleados.id_nu_empleado=%d";
    # creación de sql para consulta principal
    $sql = "SELECT
        CONCAT(ln_nombre,' ',sn_primer_apellido,' ',sn_segundo_apellido) as nombre,
        sn_rfc as rfc,
        sn_codigo as puesto,
        id_nu_puesto
    FROM tb_empleados
    WHERE id_nu_empleado = '%s'";

    # armado de los datos
    //var_export($sql);
    //var_export($_POST["employe"]);
    $sql = sprintf($sql, $_POST['employe']);
    //var_export($sql);
    //var_export($db);
    $resExec = DB_query($sql, $db, '');
    # envío de no registros
    if (DB_num_rows($resExec)==0) {
        return [
            'success' => true,
            'msg' => 'No se encontraron registros de empleados',
            'error'=>true
        ];
    }
    # procesamiento de los datos
    $rows = [];
    $registros = DB_fetch_array($resExec);
    # asignación de datos básicos del empleado
    $data['empleado'] = [
        'nombre' => escapaCaracteres($registros['nombre']),
        'rfc' => $registros['rfc'],
        'puesto' => $registros['puesto']
        //'monto' => obtenMontoDiario($registros['puesto'],$db)
    ];

    if($_POST['consultarComisiones']){
        # consulta comisiones activas
        if(!$_POST['idFolio']){
            $sql = "SELECT COUNT(`id_nu_empleado`) AS 'RegistrosEncontrados'
                    FROM `tb_viaticos`

                    WHERE `id_nu_empleado` = '$_POST[employe]'
                    AND `id_nu_estatus` <> '6'
                    AND `id_nu_estatus` <> '7'
                    AND `id_nu_estatus` <> '11'";
            $comisionesActivas = DB_fetch_array(DB_query($sql, $db))['RegistrosEncontrados'];

            # comprobación de comisiones activas
            if($comisionesActivas>0){
                return [
                    'success' => true,
                    'msg' => "El empleado <strong>".escapaCaracteres($registros['nombre'])."</strong> cuenta con <strong>$comisionesActivas</strong> oficio".( $comisionesActivas>1 ? "s" : "" )." de comisión activo".( $comisionesActivas>1 ? "s" : "" ).".",
                    'empleado'=>$data['empleado'],
                    'error'=>true
                ];
            }
        }
    }

    # consulta de los datos de puesto
    $sqlPuesto = "SELECT id_nu_jerarquia FROM tb_cat_puesto WHERE id_nu_puesto ='" . $registros['id_nu_puesto'] . "'";
    $resPuesto = DB_query($sqlPuesto, $db);
    # comprobación de datos del puesto
    if (DB_num_rows($resPuesto)==0) {
        return [
            'success' => true,
            'msg' => 'El empleado <strong>' . escapaCaracteres($registros['nombre']) . '</strong> no cuenta con un puesto.',
            'empleado'=>$data['empleado'],
            'error'=>true
        ];
    }

    # consulta de los datos de jerarquía
    $puestoReg = DB_fetch_array($resPuesto);
    $sqlJerarquia = "SELECT ln_descripcion,id_nu_jerarquia FROM tb_cat_jerarquia WHERE id_nu_jerarquia = '" . $puestoReg['id_nu_jerarquia'] . "'";
    $resJerarquia = DB_query($sqlJerarquia, $db);
    # comprobación de datos del puesto
    if (DB_num_rows($resJerarquia)==0) {
        return [
            'success' => true,
            'msg' => 'El empleado <strong>' . escapaCaracteres($registros['nombre']) . '</strong> no cuenta con una jerarquía.',
            'empleado'=>$data['empleado'],
            'error'=>true
        ];
    }

    # asignación de jerarquía
    $jerarquiaTemp = DB_fetch_array($resJerarquia);
    $data['empleado']['jerarquia']   = escapaCaracteres($jerarquiaTemp['ln_descripcion']);
    $data['empleado']['idJerarquia'] = $jerarquiaTemp['id_nu_jerarquia'];

    return $data;
}

function compruebaEmpleadoUsuario($db)
{
    $data = ['success'=>false,'msg'=>'El usuario no cuenta con un empleado relacionado.'];
    $sql = "SELECT `id_nu_empleado` as id FROM `tb_empleados` WHERE `id_nu_usuario` = '".$_SESSION['UserID']."'";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        return $data;
    }
    # procesamiento de la información
    while ($rs = DB_fetch_array($result)) {
        $data['id'] = $rs['id'];
    }
    $data['success'] = true;
    # retorno de la información obtenida
    return $data;
}

function obtenMontoDiario($puesto,$db,$idEmpleado="")
{
    $data = ['nacional' => 980, 'extrangero' => 450];
    $primerMonto = ['P','O','N','M','L','K'];
    $segundoMonto = ['J','I','H','G'];
    //var_export($puesto);
    $identificadorPuesto = substr($puesto, 0, 1);
    # se confirma el primer rango
    if (in_array($identificadorPuesto, $primerMonto)) {
        $data['nacional'] = 1700;
        return $data;
    }
    # se confirma el segundo rango
    if (in_array($identificadorPuesto, $segundoMonto)) {
        $data['nacional'] = 2850;
        return $data;
    }

    $sql = "SELECT tb_empleados.sn_codigo as puesto,
                   tb_empleados.id_nu_puesto,
                   tb_cat_puesto.id_nu_jerarquia AS jerarquia
             FROM  tb_empleados INNER JOIN tb_cat_puesto ON tb_empleados.id_nu_puesto = tb_cat_puesto.id_nu_puesto
             WHERE id_nu_empleado = ".$idEmpleado;//$_POST["employe"];


    //var_export($sql);       

    $result = DB_query($sql, $db);  

    $fetch = DB_fetch_array($result); 

    $jerarquia = $fetch["jerarquia"];  

    //$sql = "SELECT tb_cat_zonas_economicas.id_nu_zona_economica AS id_nu_zona_economica,ln_descripcion FROM tb_cat_zonas_economicas INNER JOIN tb_cat_entidad_federativa ON tb_cat_zonas_economicas.id_nu_zona_economica = tb_cat_entidad_federativa.id_nu_zona_economica WHERE tb_cat_entidad_federativa.id_nu_entidad_federativa=".$_POST["estado"];

    //$result = DB_query($sql, $db);

    //$fetch = DB_fetch_array($result);




    //$sql = "SELECT amt_importe FROM tb_monto_jerarquia WHERE id_nu_jerarquia=".$jerarquia." AND id_zona_economica=".$_POST["zona"];

    //var_export($sql);

    //$result = DB_query($sql, $db);

    //$fetch = DB_fetch_array($result);

    //$data["cuota"] = $fetch["amt_importe"];
    $data["jerarquia"] = $jerarquia;

    return $data;

}

function actualizarCuotaEnFuncionZona($db) {
    $sql = "SELECT `tb_empleados`.`sn_codigo` as puesto,
                   `tb_empleados`.`id_nu_puesto`,
                   `tb_cat_puesto`.`id_nu_jerarquia` AS jerarquia
             FROM  `tb_empleados`

             INNER JOIN `tb_cat_puesto` ON `tb_empleados`.`id_nu_puesto` = `tb_cat_puesto`.`id_nu_puesto`

             WHERE `id_nu_empleado` = '$_POST[idEmpleado]'";

    //var_export($sql);

    $result = DB_query($sql, $db);
    $fetch = DB_fetch_array($result); 
    $jerarquia = $fetch["jerarquia"];

    $sql = "SELECT `amt_importe` FROM `tb_monto_jerarquia` WHERE `id_nu_jerarquia` = '$jerarquia' AND `id_zona_economica` = '$_POST[zona]'";

    //var_export($sql);

    $result = DB_query($sql, $db);
    $fetch = DB_fetch_array($result);

    $data["cuota"] = $fetch["amt_importe"];
    $data["jerarquia"] = $jerarquia;

    return $data;
}

function obtenerCuotaInternacional($db) {
    $sql = "SELECT `amt_importe` FROM `tb_monto_jerarquia` WHERE `id_nu_jerarquia` = '$_POST[jerarquia]' AND `ind_tipo` = '$_POST[tipoComision]'";

    //var_export($sql);
    $result = DB_query($sql, $db);  
    $fetch = DB_fetch_array($result); 
    $data["cuota"] = $fetch["amt_importe"]; 

    return $data;
}

/**
 * no se encontro dependencia o relacion del empleado con la ur a la que pertenece mas que por la configuracion del usuario
 * @param  [type] $db [description]
 * @return [type]     [description]
 */
function getEmployeesByUr($db)
{
    // $sql = "SELECT
    //             id_nu_empleado AS value,
    //             CONCAT(ln_nombre, ' ', sn_primer_apellido,' ',sn_segundo_apellido) AS texto
    //         FROM tb_empleados
    //         WHERE ind_activo=%d
    //         ORDER BY ln_nombre ASC";
    // $sql = sprintf($sql, 1);
    // $empleados =  ejecutarsql($db, $sql);
}

function obtenClavePresupuestal($db)
{
    $data = ['success'=>false, 'msg'=>'No se encontraron claves presupuestales configuradas para viáticos',
        'clavesGeneral'=>[['label'=>'Seleccionar...', 'value'=>0]],
        'clavesCombustibles'=>[['label'=>'Seleccionar...', 'value'=>0]],
        'peajeTransporte'=>[['label'=>'Seleccionar...', 'value'=>0]],
    ];
    $info = $_POST;
    # consulta de datos de ambas claves
    $sqlCombustible = "SELECT `accountcode` FROM `chartdetailsbudgetbytag` WHERE `partida_esp` LIKE '26104' AND `anho` = '$_SESSION[ejercicioFiscal]'";
    $resultCombustible = DB_query($sqlCombustible, $db);

    # claves para peajes y transporte publico
    $sqlPeajeTransporte = "SELECT `accountcode` FROM `chartdetailsbudgetbytag` WHERE `partida_esp` IN('37201','37204') AND `anho` = '$_SESSION[ejercicioFiscal]' ORDER BY `partida_esp`";
    $resultPeajeTransporte = DB_query($sqlPeajeTransporte, $db);

    // $sqlGeneral = "SELECT `accountcode` FROM `chartdetailsbudgetbytag` WHERE `partida_esp` LIKE '37%'  AND `partida_esp` NOT IN('37104','37106') AND `anho` = '$_SESSION[ejercicioFiscal]'";
    
    $unionURUE = $_POST["UR"].$_POST["UE"];
    $sqlGeneral = "SELECT `accountcode` FROM `chartdetailsbudgetbytag` WHERE `partida_esp` IN('37501','37504')  AND `partida_esp` NOT IN('37104','37106') AND ln_aux1='$unionURUE' AND `anho` = '$_SESSION[ejercicioFiscal]' ORDER BY `accountcode`, `partida_esp`";

    //var_export($sqlGeneral);
    $resultGeneral = DB_query($sqlGeneral, $db);
    # retorno de datos en cero
    if (DB_num_rows($resultCombustible) == 0 && DB_num_rows($resultGeneral) == 0 && DB_num_rows($resultPeajeTransporte) == 0 ) {
        return $data;
    }
    # proce sado de datos encontrado
    while ($rsCombustible = DB_fetch_array($resultCombustible)) {
        // $data['clavesCombustibles'][] = $rsCombustible['accountcode'];
        $data['clavesCombustibles'][] = ['label'=>utf8_encode($rsCombustible['accountcode']), 'value'=>$rsCombustible['accountcode']];
    }
    while ($rsGeneral = DB_fetch_array($resultGeneral)) {
        // $data['clavesGeneral'][] = $rsGeneral['accountcode'];
        $data['clavesGeneral'][] = ['label'=>utf8_encode($rsGeneral['accountcode']), 'value'=>$rsGeneral['accountcode']];
    }
    while ($rsPeajeTransporte = DB_fetch_array($resultPeajeTransporte)) {
        // $data['peajeTransporte'][] = $rsPeajeTransporte['accountcode'];
        $data['peajeTransporte'][] = ['label'=>utf8_encode($rsPeajeTransporte['accountcode']), 'value'=>$rsPeajeTransporte['accountcode']];
    }
    $data['success'] = true;
    # retorno de los allasgos
    return $data;
}

function obtenPaises($db)
{
    $data = [
        'content'=>[]
        // 'content'=>[['label'=>'Seleccionar...', 'title'=>'Seleccionar...', 'value'=>0]]
    ];
    $sql = "SELECT `id_nu_pais` as value,`ln_descripcion` as texto,`sn_tipo_cambio` FROM `tb_cat_paises`  ORDER BY `ln_descripcion` ASC";
    $data['content'] = ejecutarsql($db, $sql);
    // $result = DB_query($sql, $db);
    // retorno de los datos
    // if(DB_num_rows($result) == 0){ return $data; }
    // while ($rs = DB_fetch_array($result)) {
    //     $data['content'][] = ['label'=>utf8_encode($rs['ln_descripcion']), 'title'=>utf8_encode($rs['ln_descripcion']), 'value'=>$rs['id_nu_pais']];
    // }
    return $data;
}


// vercion inicial 15.02.18
// function getMunicipios($db)
// {
    // $data = array('success'=>true, 'msg'=>'Funcion ejecutada correctamente');
    // $sql="SELECT id_nu_municipio as value, ln_nombre as texto FROM tb_cat_municipio WHERE id_nu_entidad_federativa=%d ORDER BY texto ASC";
    // $sql = sprintf($sql, $_POST['idEstadoDestino']);
    // $data['content'] = array('municipios' => ejecutarsql($db, $sql));
    // return $data;
// }
function getMunicipios($db)
{
    $data = ['success'=>true, 'content'=>''];
    # extraccion de la informacion del _POST
    extract($_POST);
    $sql = "SELECT `id_nu_municipio` as value, `ln_nombre` as texto FROM tb_cat_municipio WHERE `id_nu_entidad_federativa` = '$idEstadoDestino' ORDER BY texto ASC";
    $result = DB_query($sql, $db);
    $rows = array(['label'=>'Seleccione...', 'title'=>'Seleccione...', 'value'=>0]);
    while ($rs = DB_fetch_array($result)) {
        $rows[] = ['label'=>utf8_encode($rs['texto']), 'title'=>utf8_encode($rs['texto']), 'value'=>$rs['value']];
    }
    $data['content'] = $rows;
    return $data;
}

function obtenEntidades($db)
{
    $data = array('success'=>true, 'msg'=>'Funcion ejecutada correctamente');
    $sql="SELECT `id_nu_entidad_federativa` as value, `ln_nombre_entidad_federativa` as texto FROM tb_cat_entidad_federativa ORDER BY texto ASC";
    $data['content'] = ejecutarsql($db, $sql);
    return $data;
}


function store($db){
    $data = array('success'=>false, 'msg'=>'Ocurrió un error al momento de generar la información.');

    $info = (object)$_POST;
    $info->txtAreaObs = utf8_decode($info->txtAreaObs);
    // la funcion de fechas probiene del modelo itinerario
    //$info->fechaInicio = convierteFechas($info->fechaInicio);
    //$info->fechaTermino = convierteFechas($info->fechaTermino);
    $info->fechaInicio = date_format(date_create_from_format('d-m-Y', $info->fechaInicio),'Y-m-d');
    $info->fechaTermino = date_format(date_create_from_format('d-m-Y', $info->fechaTermino),'Y-m-d');
    $info->total = str_replace(",","",$info->total);
    /////$info->total = 0;
    // $info->selectUnidadNegocio = $info->selectUnidadNegocio[0];
    // $info->selectUnidadEjecutora = $info->selectUnidadEjecutora[0];
    $rows = $_POST['rows'];

    # generacion de la variable
    $datosTransporte = 0;
    # se llama la función de comprobacion generica de la información para poder continuar
    $comprobacionGeneral = comprobacionGeneral($db, $info);

    //var_export($comprobacionGeneral);

    /*if( isset($comprobacionGeneral['success']) ) {
        if (!$comprobacionGeneral['success']) {
            $data['msg'] = $comprobacionGeneral['msg'];
            return $data;
        }
    } */
    //var_export($comprobacionGeneral);
    if( isset($comprobacionGeneral['msg']) ) {
        if ($comprobacionGeneral['msg']) {
            //$entro = "entro";
            //var_export("$entro");
            $data['msg'] = $comprobacionGeneral['msg'];
            return $data;
        }
    }
    if(isset($comprobacionGeneral['datos']))  { 
      $datosTransporte = $comprobacionGeneral['datos'];
    }  
    //var_export($rows);
    # la siguiente funcion proviene del modelo de itinerario
    /////$info->total = (calculaTotalItinerario($rows)+$datosTransporte);

    # procesamiento de la comision
    DB_Txn_Begin($db);
    try {
        # se obtiene el consecutivo por numero de movimiento y transacción
        $info->systypeno = GetNextTransNo(TYPEMOV, $db);
        # se define el folio consecutivo por UR y por año
        $info->consecutivo = obtenFolioConsecutivo($db, $info);
        # se define el folio consecutivo real por UR y por año
        $info->noOficio = fnObtenFolio($db,$info);
        # se obtiene el siguiente registro
        $sql = getInsert($info, 'tb_viaticos');

        $result = DB_query($sql, $db);

        if ($result==true) {
            $flag = 0;
            # datos para la genracion de la información
            $datosSolicitud = ['id'=>$_SESSION['LastInsertId']];
            # procesamiento de la información de los datos
            $i=0;
            foreach($rows as $key => $linea){
                # esta funcion se encuentra en el modelo de itinerario
                $registroLinea = guardaItinerario($db, $linea, $datosSolicitud,$i);
                //return $registroLinea;
                # comprobacion de datos del itinerario

                if(isset($registroLinea['success'])) {
                    if (!$registroLinea['success']) {
                        $data['msg'] .= $registroLinea['msg'];
                        $flag++;
                        break;
                    }
                }
                $i++;
            }
        }

        if(isset($flag)){
            if($flag==0){
                DB_Txn_Commit($db);
                ////fnActualizaImporteTotal($db,$datosSolicitud['id']);
                $data['success'] = true;
                $data['msg'] = 'Se ha guardado la información para la solicitud de comisión <strong>'.$info->noOficio.'</strong>.';
            }else{
                DB_Txn_Rollback($db);
            }
        }else{
            DB_Txn_Rollback($db);
        }
    }catch(Exception $e){
        $data['msg'] = 'Ocurrió un incidente inesperado. '.$e->getMessage();
        DB_Txn_Rollback($db);
    }

    return $data;
}

function revisaFechasComisiones($db){
    $data = array('success'=>false, 'msg'=>'Ocurrió un error al momento de generar la información.');

    $info = $_POST;
    $info['fechaInicio'] = date_format(date_create_from_format('d-m-Y', $info['fechaInicio']),'Y-m-d');
    $info['fechaTermino'] = date_format(date_create_from_format('d-m-Y', $info['fechaTermino']),'Y-m-d');
    // Línea para que cuando se esté modificando un oficio de comisión, se omita al propio registro en la consulta
    $idAModificar = ( $info['identificador'] ? "AND `id_nu_viaticos` <> '$info[identificador]'" : "" );
    $empleados = array();
    if($info['idEmpleado']!=0){
        $empleados['Empleado'] = $info['idEmpleado'];
    }
    if($info['idEmpleadoHomologar']!=0){
        //$empleados['Homologado'] = $info['idEmpleadoHomologar'];
    }
    if(count($empleados)){
        foreach($empleados as $TipoEmpleado => $idEmpleado){
            // Revisa que los días capturados en el oficio no estorben con fechas de otros oficios
            $sql = "SELECT COUNT(`id_nu_viaticos`) AS 'RegistrosEncontrados'

                    FROM `tb_viaticos`

                    WHERE `id_nu_empleado` = '$idEmpleado'
                    AND ( (`dtm_fecha_inicio` <= '$info[fechaInicio]'
                    AND `dtm_fecha_termino` >= '$info[fechaInicio]')
                    OR (`dtm_fecha_inicio` <= '$info[fechaTermino]'
                    AND `dtm_fecha_termino` >= '$info[fechaTermino]') )
                    AND `id_nu_estatus` <> '7'
                    $idAModificar

                    ORDER BY `id_nu_viaticos` DESC";
            $data["fechasQueSeSobreponen$TipoEmpleado"] = DB_fetch_array(DB_query($sql, $db))['RegistrosEncontrados'];

            // Devuelve los días de comisión a los que se ha ido el empleado en el año a capturar
            $sql = "SELECT
                    SUM( IF(`ind_tipo_gasto`=1,DATEDIFF(`dtm_fecha_termino`,`dtm_fecha_inicio`)+1,0) ) AS 'EnComisionNacionalDiasNaturales',
                    SUM( IF(`ind_tipo_gasto`=1,DATEDIFF(`dtm_fecha_termino`,`dtm_fecha_inicio`)+1,0) ) AS 'EnComisionNacionalDiasHabiles',
                    SUM( IF(`ind_tipo_gasto`=2,DATEDIFF(`dtm_fecha_termino`,`dtm_fecha_inicio`)+1,0) ) AS 'EnComisionInternacionalDiasNaturales',
                    SUM( IF(`ind_tipo_gasto`=2,DATEDIFF(`dtm_fecha_termino`,`dtm_fecha_inicio`)+1,0) ) AS 'EnComisionInternacionalDiasHabiles'

                    FROM `tb_viaticos`

                    WHERE `id_nu_empleado` = '$idEmpleado'
                    AND DATEDIFF(`dtm_fecha_termino`,`dtm_fecha_inicio`) >= 0
                    AND ( YEAR(`dtm_fecha_inicio`) = YEAR('$info[fechaInicio]')
                    OR YEAR(`dtm_fecha_termino`) = YEAR('$info[fechaInicio]')
                    OR YEAR(`dtm_fecha_inicio`) = YEAR('$info[fechaTermino]')
                    OR YEAR(`dtm_fecha_termino`) = YEAR('$info[fechaTermino]') )
                    AND `id_nu_estatus` <> '7'
                    $idAModificar

                    ORDER BY `id_nu_viaticos` DESC";
            $diasDeComision = DB_fetch_array(DB_query($sql, $db));
            $data["diasDeComisionNacionalUsados$TipoEmpleado"] = $diasDeComision['EnComisionNacionalDiasNaturales'];
            //$data["diasDeComisionUsados$TipoEmpleado"] = $diasDeComision['EnComisionNacionalDiasHabiles'];
            $data["diasDeComisionInternacionalUsados$TipoEmpleado"] = $diasDeComision['EnComisionInternacionalDiasNaturales'];
            //$data["diasDeComisionUsados$TipoEmpleado"] = $diasDeComision['EnComisionInternacionalDiasHabiles'];
        }
    }

    return $data;
}


/* EJECUCION DE FUNCIONES */
try {
    $data = call_user_func_array($_POST['method'], [$db]);
} catch (Exception $e) {
    $data = array('success'=>false, 'msg'=>'Ocurrió un incidente inesperado al momento de consultar la información.');
}

/* ENVIO DE INFORMACIÓN */
header('Content-type: application/json; charset=utf-8');
//echo json_encode($data);
//echo json_encode($data, JSON_FORCE_OBJECT);
echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);


function getInsert($info, $tabla, $anexo = 0)
{
    $campos = '';
    $datos = '';
    $flag = 0;
    $extra = 'dtm_fecha_elaboracion,userid';// campos personalizados
    $datosExtra = sprintf("%s,'%s'", 'NOW()', $_SESSION['UserID']);// inclucion de datos uso por la constante
    $esepciones = ['selectUnidadEjecutora'];

    foreach ($info as $key => $value) {
        if (empty($value) && !in_array($key, $esepciones)) {
            continue;
        }
        if (array_key_exists($key, DATA)) {
            $data  = DATA[$key];
            if ($flag!=0) {
                $campos .= ', ';
                $datos .= ', ';
            }

            $campos .= " `$data[col]`";
            $datos .= " '$value'";//$data['type']=='string'? " '$value'" : " $value";

            $flag++;
        }
    }
    $sql = "INSERT INTO `$tabla` ($campos, %s) VALUES ($datos, %s)";// cadena temporal

    //var_export($sql);
    $sql = sprintf($sql, $extra, $datosExtra);// cadena formateada con los datos extra
    return $sql;
}

/**
 * Ejecutar un SQL
 * @param  [type] $db  [description]
 * @param  [type] $sql [description]
 * @return [type]      [description]
 */
function ejecutarsql($db, $sql)
{
    $arr = array();
    $TransResult = DB_query($sql, $db, '');
    while ($myrow = DB_fetch_array($TransResult)) {
        $texto = htmlspecialchars(utf8_encode($myrow ['texto']), ENT_QUOTES);
        $arr[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    return $arr;
}

function escapaCaracteres($str)
{
    return htmlspecialchars(utf8_encode($str));
}

function getLastID($db, $tbl, $id)
{
    $sql = "SELECT MAX($id) as id FROM $tbl";
    $result = DB_query($sql, $db);
    $fetch = DB_fetch_array($result);
    return $fetch['id'];
}

function validaExiteClavePresupuesto($db, $clave)
{
    $sql = "SELECT * FROM `chartdetailsbudgetbytag` WHERE `accountcode` LIKE '$clave'";
    $result = DB_query($sql, $db);
    $fetch = DB_num_rows($result);
    return ($fetch>0);
}

function obtenNombreMes($db, $periodo, $anio='')
{
    # comprobacion de año agregado
    $anio = empty($anio)? date('Y') : $anio;
    $msg = "Fallo en el query de " . __FUNCTION__;
    $nombreMes = '';
    # ejecucion de query
    $sql = "SELECT
            ctm.mes as mesName
        FROM periods as p
        LEFT JOIN cat_Months as ctm ON ctm.u_mes = DATE_FORMAT(p.lastdate_in_period, '%m')
        WHERE p.periodno = '$periodo'
        ORDER BY p.lastdate_in_period asc;"; //p.lastdate_in_period like '%$anio%' AND 
    $result = DB_query($sql, $db, $msg);
    # extraccion de datos
    while ($row = DB_fetch_array($result)) {
        $nombreMes = $row['mesName'];
    }
    # retorno de la informacion
    return $nombreMes;
}

function compruebaFolioSolicitud($db, $info)
{
    $sql = "SELECT * FROM `tb_viaticos` WHERE `id_nu_estatus` <>'7' AND `sn_folio_solicitud` = '".$info->noOficio."'";
    $sql = "SELECT * FROM `tb_viaticos` WHERE `sn_folio_solicitud` = '".$info->noOficio."'";
    $result = DB_query($sql, $db);
    $cant = DB_num_rows($result);
    //var_export($sql);
    return $cant>0;
}

function compruebaFechasPorTipo($datos)
{
    $data = ['success'=>true,'msg'=>'La fecha de inicio tiene que ser mayor a la fecha actual en el caso de las comisiones con <strong>Tipo Viático: Anticipado</strong>. Una vez realizado el cambio de fecha realice los cambios necesarios en el apartado de <strong>itinerario</strong> '];
    if ($datos->tipoGasto == 2) {
        # fecha del sistema
        $fechaSistema = new DateTime();
        # fechas a comprobar
        $fechaInicio = new DateTime(date_format(date_create_from_format('d-m-Y', $datos->fechaInicio),'Y-m-d'));
        // $fechaTermino = date_create($datos->fechaTermino);
        # comprobaciones de fechas
        $intervalInicio = $fechaSistema->diff($fechaInicio);
        if ($intervalInicio->format('%R%a') < 0) {
            $data['success'] = false;
        }
    }
    return $data;
}

function comprobacionGeneral($db, $info)
{
    # generacion de la suma de los montos de las claves presupuestales
    $datosTransporte = 0;/*($info->montoPeaje + $info->montoCombustible)*/

    # comprobación de existencia de folio
    $exist = compruebaFolioSolicitud($db, $info);
    if ($exist&&$info->idNuViaticos=="identificadorViaticos") {
        $data['msg'] = 'El folio <strong>'.$info->noOficio.'</strong> ya se encuentra registrado, es necesario colocar un folio diferente.';
        return $data;
    }

    # comprobación de fechas según el tipo de viático DEVENGADO y ANTISIPADO.
    $compruebaFechasPorTipo = compruebaFechasPorTipo($info);
    if (!$compruebaFechasPorTipo['success']&&$info->idNuViaticos=="identificadorViaticos") {
        return $compruebaFechasPorTipo;
    }

    $infoClaves = array();
    $infoClaves[] = array(
        'accountcode' => $info->clavePresupuestal
    );
    $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
    if (!$respuesta['result']) {
        $data['msg'] = $respuesta['mensaje'];
        return $data;
    }

    # obtencion del periodo actual
    //$periodo = GetPeriod(date('d/m/Y'), $db);
    $periodo = $respuesta['periodo'];
    if ($periodo < 0) {
        $data['msg'] = 'El periodo se encuentra cerrado.';
        return $data;
    }
    # obtencion del nombre del mes corriente
    $nombreMes = obtenNombreMes($db, $periodo);
    if (empty($nombreMes)) {
        $data['msg'] = 'Ocurrió un error al momento de obtener el nombre del mes.';
        return $data;
    }

    # comprobacion de si se valida o no la clave presupuestal
    # Las siguientes líneas se sacaron del if ($info->tipoGasto == 1)
        if ($info->clavePresupuestal == 0) {
            return ['success'=>false,'msg'=>'Es necesario indicar la clave presupuestal para viáticos.'];
        }
        $comprobacionClaveGeneral = compruebaPresupuestoPorClave($db, $info->clavePresupuestal, $nombreMes, $periodo, $info->total);
        if (!$comprobacionClaveGeneral['success']) {
            return $comprobacionClaveGeneral;
        }
    if ($info->tipoGasto == 1) {
    }

    # validacion de comprobacion de claves de ppeaje y combustible
    //// Se agregó 1==2 para saltarse el proceso, ya que esta parte no se está usando
    if (1==2&&$info->trasnporte != 0 && $info->trasnporte != 4) {
        if ($info->trasnporte != 3) {
            # ############################ #
            # validacion de datos de peaje #
            # ############################ #
            /*if ($info->clavePresupuestalPeaje == 0) {
                return ['success'=>false,'msg'=>'Es necesario indicar la clave presupuestal para peaje.'];
            } 
            $comprobacionClavePresupuestalPeaje = compruebaPresupuestoPorClave($db, $info->clavePresupuestalPeaje, $nombreMes, $periodo, $info->total);
            if (!$comprobacionClavePresupuestalPeaje['success']) {
                return $comprobacionClavePresupuestalPeaje;
            }*/
            if( isset($info->montoPeaje) ) {
               $datosTransporte += $info->montoPeaje;
            }

            # ################################## #
            # validacion de datos de Combustible #
            # ################################## #
            /*if ($info->clavePresupuestalCombustible == 0) {
                return ['success'=>false,'msg'=>'Es necesario indicar la clave presupuestal para combustible.'];
            }
            $comprobacionClavePresupuestalCombustible = compruebaPresupuestoPorClave($db, $info->clavePresupuestalCombustible, $nombreMes, $periodo, $info->total);
            if (!$comprobacionClavePresupuestalCombustible['success']) {
                return $comprobacionClavePresupuestalCombustible;
            } */
            if(isset($info->montoCombustible)) {
               $datosTransporte += $info->montoCombustible;
            }   
        } else {
            # ################################ #
            # validacion de datos de Terrestre #
            # ################################ #
            if ($info->clavePresupuestalTransportePublico == 0) {
                return ['success'=>false,'msg'=>'Es necesario indicar la clave presupuestal para transporte público.'];
            }
            $comprobacionClavePresupuestalTransportePublico = compruebaPresupuestoPorClave($db, $info->clavePresupuestalTransportePublico, $nombreMes, $periodo, $info->total);
            if (!$comprobacionClavePresupuestalTransportePublico['success']) {
                return $comprobacionClavePresupuestalTransportePublico;
            }
            $datosTransporte += $info->montoTrasportePublico;
        }
    }

    # retorno final
    return ['success'=>true, 'datos'=>$datosTransporte];
}

function compruebaPresupuestoPorClave($db, $clavePresupuestal, $nombreMes, $periodo, $total)
{
    $data = ['success'=>false,'msg'=>''];
    $nombreMes = $nombreMes."Acomulado";
    # validacion de existencia de clave presupuestal
    if (!validaExiteClavePresupuesto($db, $clavePresupuestal)) {
        $data['msg'] = "No se encontraron datos de la clave presupuestal <strong>{$clavePresupuestal}</strong>, favor de verificar la información.";
        return $data;
    }
    # consulta de clave presupuestal y se optiene el primer indice
    $respClave = fnInfoPresupuesto($db, $clavePresupuestal, $periodo)[0];
    if ($respClave[$nombreMes] <= 0) {
        $data['msg'] = "No cuenta con presupuesto para el periodo actual <strong>{$nombreMes}</strong>";
        return $data;
    }
    # comprobacion de monto de comicion contra el presupuesdo del mes
    if ($respClave[$nombreMes] < $total) {
        $data['msg'] = 'No cuenta con el presupuesto suficiente para la comisión.';
        return $data;
    }

    # cambio y confirmacion de la validacion
    $data['success'] = true;
    return $data;
}

function getPerfil($db)
{
    $userid = $_SESSION['UserID'];
    $sql = "SELECT profileid FROM sec_profilexuser WHERE userid = '$userid'";
    $resl = DB_query($sql, $db);
    $p = DB_fetch_assoc($resl);
    return $p['profileid'];
}

function obtenFolioConsecutivo($db, $info)
{
    $folio = 0;
    $currenDay = date('z')+1;// día 01.01.x == 0
    $sql = "SELECT (IFNULL(MAX(`id_nu_oficio_ur`),0)+1) as base FROM `tb_viaticos`
            WHERE `tagref` = '".$info->selectUnidadNegocio."'
            AND date_format(`dtm_fecha_elaboracion`,'%Y') = date_format(NOW(),'%Y')";// if empty geben 1
    $result = Db_query($sql, $db);
    # extracción de información. Datos esperados $base
    extract(DB_fetch_array($result));
    $folio = $currenDay.$base;
    # envio d econsecutivo por año y ur
    return $folio;
}

/**

Obtiene la informacion de un oficio de comision a partir de un 
folio que viene por POST

**/

function getDataOficioComision($db) {

  $data       = array(); 
  $oficioData = array(); 
  $itinerarioData = array();
  
  # Recupero la información general del oficio
  $sql = "SELECT id_nu_viaticos,tagref, id_nu_ue,  sn_folio_solicitud,  id_nu_empleado,  ind_tipo_gasto,  DATE_FORMAT(`dtm_fecha_inicio`,'%d-%m-%Y') AS `dtm_fecha_inicio`, DATE_FORMAT(`dtm_fecha_termino`,'%d-%m-%Y') AS `dtm_fecha_termino`,ln_objetivo_comicion, ind_tipo_transporte,  ind_tipo_solicitud, nu_porcentaje_pernocta, nu_destino_estado, nu_destino_municipio, amt_importe_total, systypeno,id_nu_oficio_ur, dtm_fecha_elaboracion,userid,accountcode_general,homologar,empleado_homologado,id_nu_estatus
           FROM  tb_viaticos
           WHERE  id_nu_viaticos = '$_POST[folio]'";

   //var_export($sql);                  

  $result = Db_query($sql, $db); 

  # procesamiento de la información
    while ($rs = DB_fetch_array($result)) {
        $oficioData["idNuViaticos"]         = $rs['id_nu_viaticos'];
        $oficioData['ur']                   = $rs['tagref'];
        $oficioData['ue']                   = $rs['id_nu_ue'];
        $oficioData['folio']                = $rs['sn_folio_solicitud'];
        $oficioData['empleado']             = $rs['id_nu_empleado'];
        $oficioData['tipoViatico']          = $rs['ind_tipo_gasto'];
        $oficioData['fechaInicio']          = $rs['dtm_fecha_inicio'];
        $oficioData['fechaFin']             = $rs['dtm_fecha_termino'];
        $oficioData['objetivoComision']     = utf8_encode($rs['ln_objetivo_comicion']);
        $oficioData['tipoSolicitud']        = $rs['ind_tipo_solicitud'];
        $oficioData['tipoTransporte']       = $rs['ind_tipo_transporte'];
        $oficioData['cantPernocta']         = $rs['nu_porcentaje_pernocta'];
        $oficioData['estadoDestino']        = $rs['nu_destino_estado'];
        $oficioData['municipioDestino']     = $rs['nu_destino_municipio'];
        $oficioData['importe']              = $rs['amt_importe_total'];
        $oficioData['clavePresupuestal']    = $rs['accountcode_general'];
        $oficioData['homologar']            = $rs['homologar'];
        $oficioData['empleado_homologado']  = $rs['empleado_homologado'];
        $oficioData['editable']             = fnDeterminarEditabilidad($db,$rs['id_nu_estatus']);
    }

  # Recupero las filas de itinerario para un oficio de comision
  $sql = "SELECT `id_nu_solicitud_itinerario`, `id_nu_solicitud_viaticos`, `nu_destino_pais`, `nu_destino_estado`, `nu_destino_municipio`, DATE_FORMAT(`dt_periodo_inicio`,'%d-%m-%Y') AS `dt_periodo_inicio`, DATE_FORMAT(`dt_periodo_termino`,'%d-%m-%Y') AS `dt_periodo_termino`, `amt_cuota_diaria`, `nu_dias`, `amt_importe`, `ind_pernocta`, `ch_zona_economica`
         FROM `tb_solicitud_itinerario`
         WHERE `id_nu_solicitud_viaticos` = '$oficioData[idNuViaticos]'"; 

  //return $sql;

   //var_export($sql);        

  $result = Db_query($sql, $db);   
  $i = 0;
  while ($rs = DB_fetch_array($result) ) {
            $itinerarioData[$i]["idItinerario"]     = $rs["id_nu_solicitud_itinerario"];
            $itinerarioData[$i]["pais"]             = $rs["nu_destino_pais"];
            $itinerarioData[$i]["estadoDestino"]    = $rs["nu_destino_estado"];
            $itinerarioData[$i]["municipioDestino"] = $rs["nu_destino_municipio"];
            $itinerarioData[$i]["fechaInicio"]      = $rs["dt_periodo_inicio"];
            $itinerarioData[$i]["fechaFin"]         = $rs["dt_periodo_termino"];
            $itinerarioData[$i]["cuota"]            = $rs["amt_cuota_diaria"];
            $itinerarioData[$i]["dias"]             = $rs["nu_dias"];
            $itinerarioData[$i]["amt_importe"]      = $rs["amt_importe"];
            $itinerarioData[$i]["ind_pernocta"]     = $rs["ind_pernocta"];
            $itinerarioData[$i]["zonaEconomica"]    = $rs["ch_zona_economica"];
            $i++;
    }


    /** 
         Obtengo puesto y monto por dia que le corresponde al trabajador esta informacion es utilizada por la 
         variable montoDiario en el javascript 
    **/

        $sql = "SELECT
        CONCAT(ln_nombre,' ',sn_primer_apellido,' ',sn_segundo_apellido) as nombre,
        sn_rfc as rfc,
        sn_codigo as puesto,
        id_nu_puesto
    FROM tb_empleados
    WHERE id_nu_empleado = '%s'";
    # armado de los datos
    $sql = sprintf($sql, $oficioData['empleado']);
    $resExec = DB_query($sql, $db, '');
    # envío de no registros
    if (DB_num_rows($resExec)==0) {
        return [
            'success' => true,
            'msg' => 'No se encontraron registros',
            'error'=>true
        ];
    }
    # procesamiento de los datos
    $rows = [];
    $registros = DB_fetch_array($resExec);


   $data[0]  =  $oficioData;
   $data[1]  =  $itinerarioData;

       # asignación de datos básicos del empleado
   //$data[2] = obtenMontoDiario($registros['puesto'],$db,$oficioData['empleado']);

   //var_export($data);

   
   return $data;

}

/**
Actuliza un oficio 
**/
function actualizarOficioActual($db) {
    $data = array('success'=>false, 'msg'=>'Ocurrió un error al momento de generar la información.');

    //print "actualizarOficioActual: ".microtime();
    $info = $_POST;
    $info['fechaInicio'] = date_format(date_create_from_format('d-m-Y', $info['fechaInicio']),'Y-m-d');
    $info['fechaTermino'] = date_format(date_create_from_format('d-m-Y', $info['fechaTermino']),'Y-m-d');
    $info["txtAreaObs"] = utf8_decode($info["txtAreaObs"]);
    $info["total"] = str_replace(",","",$info["total"]);
    //var_export($_POST);
    $rows = $_POST['rows'];
    //var_export($_POST['rows']);

    $comprobacionGeneral = comprobacionGeneral($db, (object)$info);
    if( isset($comprobacionGeneral['msg']) ) {
        if ($comprobacionGeneral['msg']) {
            //$entro = "entro";
            //var_export("$entro");
            $data['msg'] = $comprobacionGeneral['msg'];
            return $data;
        }
    }

    $campos = '';
    $datos = '';
    $flag = 0;
    $extra = 'dtm_fecha_elaboracion,userid';// campos personalizados
    $datosExtra = sprintf("%s,'%s'", 'NOW()', $_SESSION['UserID']);// inclucion de datos uso por la constante
    $esepciones = ['selectUnidadEjecutora','homologar','idEmpleadoHomologar'];

    foreach ($info as $key => $value) {
        if ( empty($value) && !in_array($key, $esepciones) ) {
            continue;
        }
        if( array_key_exists($key, DATA) ){
            $datoActual  = DATA[$key];
            if($flag!=0){
                $campos .= ', ';
                //$datos .= ', ';
            }

            $campos .= " `$datoActual[col]` = ";
            //$datos   = $datoActual['type']=='string'? " '$value'" : " $value";
            $campos .=  " '$value'";//$datos;
            $flag++;
        }
    }

    $i = 0;
   // print "sale for info> ".microtime();
    //var_export($rows);

    foreach ($rows as $ky => $linea) {
        if($linea["idItinerario".$i] != "-1"){
            $registroLinea = actualizaItinerario($db, $linea, $info["idNuViaticos"],$i);
        }else{
            aniadirNuevoRegistro($db,$linea,$info["idNuViaticos"],$i);
        }
        $i++;
    }

    //Elimino los itinerarios seleccionados para esta accion
    $recordsToDelete = ( array_key_exists("linesToDelete", $_POST) ? $_POST["linesToDelete"] : array() );
    if( count($recordsToDelete) > 0 ){
        for($i=0; $i < count($recordsToDelete); $i++){
            eliminarItinerario($db,$recordsToDelete[$i]);
        }
    }

    DB_Txn_Begin($db);

    $sql = "UPDATE `tb_viaticos` SET $campos WHERE `sn_folio_solicitud` = '$info[noOficio]'";

    $result = DB_query($sql, $db);

    if ($result==true){
        DB_Txn_Commit($db);
        ////fnActualizaImporteTotal($db,$info["idNuViaticos"]);
        $data['success'] = true;
        $data['msg'] = "Se ha guardado la información para la solicitud de comisión <strong>$info[noOficio]</strong>.";
    }else{
        DB_Txn_Rollback($db);
    }

    return $data;
}


/**
 Actualiza una linea de itinerario 
**/
function actualizaItinerario($db, $linea, $idNuViaticos,$numLinea){
    //var_export($_POST["linesToDelete"]);
    $campos = "`nu_destino_pais` = '".( $linea['pais'.$numLinea]!="" ? $linea['pais'.$numLinea] : 0 )."', `nu_destino_estado` = '".( $linea['idEstadoDestino'.$numLinea]!="" ? $linea['idEstadoDestino'.$numLinea] : 0 )."', `nu_destino_municipio` = '".( $linea["municipioItinerario".$numLinea]!="" ? $linea["municipioItinerario".$numLinea] : 0 )."', `dt_periodo_inicio` = '".date_format(date_create_from_format('d-m-Y', $linea["fechaInicio".$numLinea]),'Y-m-d')."', `dt_periodo_termino` = '".date_format(date_create_from_format('d-m-Y', $linea["fechaTermino".$numLinea]),'Y-m-d')."', `amt_cuota_diaria` = '".str_replace(",", "", $linea["cuota".$numLinea])."', `nu_dias` = '".$linea["dias".$numLinea]."', `amt_importe` = '".str_replace(',', '', $linea["importe".$numLinea])."', `ind_pernocta` = '".$linea["pernocta".$numLinea]."', `dtm_fecha_actualizacion` = '".date("Y-m-d H:i:s")."', `ch_zona_economica` = '".$linea["zonaEconomica".$numLinea]."'";

    $sql = "UPDATE `tb_solicitud_itinerario` SET $campos WHERE `id_nu_solicitud_viaticos`= '$idNuViaticos' AND `id_nu_solicitud_itinerario` = '".$linea["idItinerario".$numLinea]."'"; 

    //var_export($sql);
    DB_Txn_Begin($db);

    $result = DB_query($sql, $db);

    if($result==true){
        DB_Txn_Commit($db);
    }else{
        DB_Txn_Rollback($db);
    }
}


function eliminarItinerario($db,$idItinerario) {

     // Elimino linea de itinerario

       $sql = "DELETE FROM `tb_solicitud_itinerario` WHERE `id_nu_solicitud_itinerario` = '$idItinerario'";

       DB_Txn_Begin($db);

       $result = DB_query($sql, $db);

       if ($result==true) {
              DB_Txn_Commit($db);
         } else {
              DB_Txn_Rollback($db);
         }

}


function aniadirNuevoRegistro($db,$linea,$idNuViaticos,$numLinea) {
 
   $sql = "INSERT INTO `tb_solicitud_itinerario` (`id_nu_solicitud_viaticos`,`nu_destino_pais`,`nu_destino_estado`,`nu_destino_municipio`,`dt_periodo_inicio`,`dt_periodo_termino`,`amt_cuota_diaria`,`nu_dias`,`amt_importe`,`ind_pernocta`,`dtm_fecha_alta`,`ch_zona_economica`) VALUES ('$idNuViaticos', '".( $linea['pais'.$numLinea]!="" ? $linea['pais'.$numLinea] : 0 )."', '".( $linea['idEstadoDestino'.$numLinea]!="" ? $linea['idEstadoDestino'.$numLinea] : 0 )."', '".( $linea["municipioItinerario".$numLinea]!="" ? $linea["municipioItinerario".$numLinea] : 0 )."', '".date_format(date_create_from_format('d-m-Y', $linea["fechaInicio".$numLinea]),'Y-m-d')."', '".date_format(date_create_from_format('d-m-Y', $linea["fechaTermino".$numLinea]),'Y-m-d')."', '".str_replace(",","",$linea["cuota".$numLinea])."', '".$linea["dias".$numLinea]."', '".str_replace(',', '',$linea["importe".$numLinea])."', '".$linea["pernocta".$numLinea]."', '".date("Y-m-d H:i:s")."', '".$linea["zonaEconomica".$numLinea]."')";

     //var_export($sql);


     DB_Txn_Begin($db);

     $result = DB_query($sql, $db);

     if ($result==true) {

          DB_Txn_Commit($db);

     } else {
          DB_Txn_Rollback($db);
     }


}

function getZonaEconomica($db) {

    $data = array();

    $sql = "SELECT tb_cat_zonas_economicas.id_nu_zona_economica AS id_nu_zona_economica,ln_descripcion FROM tb_cat_zonas_economicas INNER JOIN tb_cat_entidad_federativa ON tb_cat_zonas_economicas.id_nu_zona_economica = tb_cat_entidad_federativa.id_nu_zona_economica WHERE tb_cat_entidad_federativa.id_nu_entidad_federativa=".$_POST["estado"];

     //var_export($sql);


     DB_Txn_Begin($db);

     $result = DB_query($sql, $db);

     if ($result==true) {

          DB_Txn_Commit($db);

          while ($rs = DB_fetch_array($result) ) {
            $data["zona"]   = $rs["ln_descripcion"];
            $data["idZona"] = $rs["id_nu_zona_economica"];
          }          

     } else {
          DB_Txn_Rollback($db);
     } 

     //var_export($data);

     return $data; 


}

/*
  Checa los fondos de una clave presupuestal
*/
function tieneFondosClavePresupuestal($db) {
    //$period = GetPeriod(date(date_format(date_create_from_format('d-m-Y', $_POST["fechaInicio"]),'Y-m-d')), $db);

    $infoClaves = array();
    $infoClaves[] = array(
        'accountcode' => $_POST["clave"]
    );
    $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
    return fnInfoPresupuesto($db, $_POST["clave"], $respuesta['periodo']);
}

/*function obtenerTransporte($db) {

    $sql = "SELECT ln_nombre_descripcion,id_nu_tipo_transporte FROM tb_cat_tipo_transporte";

    //var_export($sql);

    $result = DB_query($sql, $db);   

    $i = 0;
    while ($rs = DB_fetch_array($result) ) {

       $data[$i]["transporte"]   = $rs["ln_nombre_descripcion"]; 
       $data[$i]["idTransporte"] = $rs["id_nu_tipo_transporte"];
       $i++;

    }

    return $data;
  
} */

function fnActualizaImporteTotal($db,$idSolicitud){
    $sql = "UPDATE `tb_viaticos` AS v, (SELECT SUM( (`amt_importe`/2)*(`nu_dias`+`ind_pernocta`) ) AS `importeTotalNacional`, SUM(`amt_importe`*`nu_dias`) AS `importeTotalInternacional` FROM `tb_solicitud_itinerario` WHERE `id_nu_solicitud_viaticos` = '$idSolicitud') AS i

            SET v.`amt_importe_total` = IF( v.`ind_tipo_solicitud` = 1, IF( i.`importeTotalNacional` IS NOT NULL, i.`importeTotalNacional`, 0 ), IF( i.`importeTotalInternacional` IS NOT NULL, i.`importeTotalInternacional`, 0 ) )

            WHERE v.`id_nu_viaticos` = '$idSolicitud'";

    Db_query($sql, $db);
}

function fnDeterminarEditabilidad($db,$estatus){
    $perfil = getPerfil($db);
    $editable = true;

    if($estatus==2&&($perfil==9)){
        $editable = false;
    }

    if($estatus==3&&($perfil==9||$perfil==10)){
        $editable = false;
    }

    if($estatus>3){
        $editable = false;
    }

    return $editable;
}

function fnObtenFolio($db,$info){
    //// Actualmente usa la fecha de inicio de la comisión para determinar el año para el folio
    //// Si el año se determina con la feha de elaboración, basta con cambiar `dtm_fecha_inicio` por `dtm_fecha_elaboracion` y substr($info->fechaInicio,0,4) por date('Y')
    $anio = substr($info->fechaInicio,0,4);
    $ue = $info->selectUnidadEjecutora;
    $sql = "SELECT CONCAT('$anio','$ue',LPAD(COUNT(`id_nu_ue`)+1,6,0)) AS `Folio`, `tb_viaticos`.* 

            FROM `tb_viaticos`

            WHERE YEAR(`dtm_fecha_inicio`) = '$anio'
            AND `id_nu_ue` LIKE '$ue'";

    return DB_fetch_array(DB_query($sql, $db))['Folio'];
}
