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
 */

$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = '../';
// $funcion = 2318;
$funcion = 2338;
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

/* VARIABLE DE CONSTANTES */
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('FUNCTIONID', $funcion);
# tipo de movimiento ubicado en las tabas "systypesinvtrans" y "systypescat"
define('TYPEMOV', 501);
# definicion de estatus para poder comprobar
define('STATUSCOMP', 5);


function cronSolicitudes($db)
{
    $data = ['success'=>true,'msg'=>'Todo se generó de forma correcta.'];
    // $sql = "SELECT DISTINCT `id_nu_empleado` as id FROM `tb_empleados` WHERE `id_nu_usuario` = '".$_SESSION['UserID']."'";
    // $result = DB_query($sql, $db);
    // # si no encuentra resultados se manda nulo
    // if(DB_num_rows($result) == 0){
    //     $data['msg'] = 'El usuario no cuenta con un empleado asignado.';
    //     $data['success'] = false;
    //     return $data;
    // }
    // // procesamiento de la información de los id de los empleados o empleado que se encuentre
    // $idEmpleados = '';
    // $flag = 0;
    // while ($rs = DB_fetch_array($result)) {
    //     $idEmpleados .= ($flag!=0?',':'')." '".$rs['id']."'";
    //     $flag++;
    // }

    // $sqlSolicitudes = "SELECT id_nu_viaticos as id, `sn_folio_solicitud` as folio ,id_nu_empleado as empleado
    //     FROM `tb_viaticos` WHERE `id_nu_empleado` in($idEmpleados)
    //     AND `dtm_fecha_inicio` <= '".date('Y-m-d')." 23:59:59' AND `id_nu_estatus` = '8'";
    $fechaActual = date('Y-m-d');
    $sqlSolicitudes = "SELECT `id_nu_viaticos` AS `id`, `sn_folio_solicitud` AS `folio`, `id_nu_empleado` AS `empleado`, IF(`ind_tipo_gasto`='2' AND `ind_momento_presupuestal`<3,`id_nu_estatus`,IF(`dtm_fecha_termino` <= '$fechaActual 23:59:59',5,IF(`dtm_fecha_inicio` <= '$fechaActual 23:59:59',4,8))) AS `nuevoEstatus`

        FROM `tb_viaticos`

        WHERE `id_nu_estatus` = '8'
        AND ( `dtm_fecha_inicio` <= '$fechaActual 23:59:59' OR `dtm_fecha_termino` <= '$fechaActual 23:59:59' )";
    $resultSolicitud = DB_query($sqlSolicitudes, $db);
    # comprobación de solicitudes a cambiar
    if(DB_num_rows($resultSolicitud) == 0){
        $data['msg'] = 'No se encontraron solicitudes.';
        return $data;
    }
    # procesamiento de las solicitudes encontradas
    DB_Txn_Begin($db);
    try {
        $flagUpdate = 0;
        $msgErr = '';
        while ($rs = DB_fetch_array($resultSolicitud)) {
            $sqlUpdate = "UPDATE `tb_viaticos` SET `id_nu_estatus` = '$rs[nuevoEstatus]' WHERE `id_nu_viaticos` = '$rs[id]' ";
            $resultUpdate = DB_query($sqlUpdate, $db);
            if($resultUpdate != true){
                $flagUpdate ++;
                $msgErr .= "Error en la solicitud $rs[folio]";
            }else{
                $data['msg'] = "Solicitud afectada $rs[folio]";
            }
        }
        # si ocurre algún erro se revierten los cambios y se manda el error
        if($flagUpdate != 0){
            $data['success'] = false;
            $data['msg'] = $msgErr;
            DB_Txn_Rollback($db);
            return $data;
        }
        # si todo salio bien se consolida la información
        DB_Txn_Commit($db);
    } catch (Exception $e) {
        $data['msg'] .= '<br>'.$e->getMessage();
        DB_Txn_Rollback($db);
    }

    # retorno de la información
    return $data;
}

function getSolicitudes($db)
{
    $data = array('success'=>false, 'msg'=>'Ocurrió un incidente inesperado al momento de consultar la información.', 'content'=>[]);
    $info = $_POST;
    //$noPermitidos = getNoPermitidos($db);// obtención de los permisos según el perfil
    $where = obtenCondiciones($info);
    $getNoPermitidos = getNoPermitidos($db);
    $sql="SELECT DISTINCT
            `sn_folio_solicitud` AS solicitud,
            DATE_FORMAT(`dtm_fecha_elaboracion`,'%d/%m/%Y') AS fechaElaboracion,
            `tags`.`tagdescription` AS UR,
            `tb_cat_unidades_ejecutoras`.`desc_ue` AS UE,
            DATE_FORMAT(`dtm_fecha_inicio`,'%d/%m/%Y') AS fechaIncio,
            DATE_FORMAT(`dtm_fecha_termino`,'%d/%m/%Y') AS fechaFin,
            `ln_objetivo_comicion` AS objetivo,
            `amt_importe_total` AS monto,
            `tags`.`tagref` AS idUR,
            `tb_viaticos`.`id_nu_ue` AS nUE,
            `tb_viaticos`.`id_nu_viaticos` AS idFolioSol,
            `tb_viaticos`.`id_nu_estatus` AS idEstatus,
            `tb_viaticos`.`id_nu_estatus` AS idStatus,
            `tb_viaticos`.`dtm_fecha_elaboracion`,
            IF(`ind_tipo_gasto`=2,'Anticipado',IF(`ind_tipo_gasto`=1,'Devengado','')) AS `tipoGasto`,
            IF(`ind_tipo_solicitud`=1,'Nacional',IF(`ind_tipo_solicitud`=2,'Internacional','')) AS `tipoSolicitud`,
            `est`.`namebutton` AS `textoEstatus`

        FROM `tb_viaticos`
        LEFT JOIN `tb_solicitud_itinerario` ON `tb_solicitud_itinerario`.`id_nu_solicitud_viaticos` = `tb_viaticos`.`id_nu_viaticos`
        INNER JOIN `tb_empleados` ON `tb_empleados`.`id_nu_empleado` = `tb_viaticos`.`id_nu_empleado`
        INNER JOIN `tags` ON `tags`.`tagref` = `tb_viaticos`.`tagref`
        INNER JOIN `tb_cat_unidades_ejecutoras` ON `tb_cat_unidades_ejecutoras`.`ur` = `tags`.`tagref` AND `tb_viaticos`.`id_nu_ue` = `tb_cat_unidades_ejecutoras`.`ue`
        LEFT JOIN `tb_botones_status` AS est ON `est`.`statusid` = `tb_viaticos`.`id_nu_estatus` AND `est`.`sn_funcion_id` = '2338' AND `est`.`functionid` = 0
        JOIN sec_unegsxuser ON sec_unegsxuser.tagref = `tb_viaticos`.`tagref` AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
        JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` = '".$_SESSION['UserID']."' AND `tb_viaticos`.`tagref` = `tb_sec_users_ue`.`tagref` AND  `tb_viaticos`.`id_nu_ue`= `tb_sec_users_ue`.`ue`

        $where

        ORDER BY `tb_viaticos`.`dtm_fecha_elaboracion` DESC, `solicitud` DESC";

        // " LIMIT 50";
    // $data['sql'] = $sql;

        //var_export($sql);
       
    $resExec = DB_query($sql, $db, '');
    if (DB_num_rows($resExec)!=0) {
        $rows = array();
        $enc = new Encryption;
        while ($row = mysqli_fetch_object($resExec)) {
            $sqlEst = "SELECT `namebutton` FROM `tb_botones_status` WHERE `sn_funcion_id` = 2338 AND `functionid` = 0 AND `statusid` = {$row->idEstatus}";
            $respEst = DB_query($sqlEst, $db);
            $fetchEst = DB_fetch_array($respEst);
            $urlGeneral = "&idFolio=".$row->idFolioSol;
            $enc = new Encryption;
            $url = $enc->encode($urlGeneral);
            $liga= "URL=" . $url;
            $link = $row->solicitud;
            if($row->idEstatus>=1&&$row->idEstatus<=3||$row->idEstatus==7){//if($row->idEstatus == STATUSCOMP){
                $link = '<a type="button" id="btnAbrirOficio_'.$row->idFolioSol.'" name="btnAbrirOficio_'.$row->idFolioSol.'" href="altaOficioComision.php?'.$urlGeneral.'" title="Capturar Comisi&oacute;n" style="color: blue;">'.$row->solicitud.'</a>';
            }
             $urlImp = "&solicitud=>".$row->solicitud;
             $urlImpEncri = $enc->encode($urlImp);
             $ligaEncriptada="URL=".$urlImpEncri;

               
            $ligaImprimir='<a  id="imprimir'.$row->solicitud.'" href="impresionViaticos.php?'.$ligaEncriptada .'"   href="#" target="_blank">'."<span class='glyphicon glyphicon glyphicon-print'></span>".'</a>';

            $rows[] = [
                'solicitud'=> $link,
                'solString' => $row->solicitud,
                'fechaElaboracion' => $row->fechaElaboracion,
                'ur' => utf8_encode($row->UR),
                'ue' => utf8_encode($row->UE),
                'fechaInicio' => $row->fechaIncio,
                'fechaFin' => $row->fechaFin,
                'objetivo' => utf8_encode($row->objetivo),
                'monto' => $row->monto,
                'status' => utf8_encode($fetchEst['namebutton']),
                'idfolio'=> $row->idFolioSol,
                'idStatus'=>$row->idEstatus,
                'nUR'=>$row->idUR,
                'nUE'=>$row->nUE,
                'imprimir'=>$ligaImprimir,
                'tipoSol'=>utf8_encode($row->tipoSolicitud),
                'tipoGasto'=>utf8_encode($row->tipoGasto),
                'textoEstatus' => utf8_encode($row->textoEstatus)
            ];
        }

        $data['success']=true;
        $data['msg']='Solicitud ejecutada con exito';
        $data['content'] = $rows;
    }
    // obtencion del perfil del usuario
    $data['profile'] = getPerfil($db);
    $data['noPermitidos'] = $getNoPermitidos;
    // var_dump($data);
    return $data;
}

function obtenSolicitudesTerminadas($db)
{
    $info = $_POST;
    $data = ['content' => []];
    $where = obtenCondiciones($info, 1);
    $sql = "SELECT DISTINCT
                `sn_folio_solicitud` AS solicitud,
                DATE_FORMAT(`dtm_fecha_elaboracion`,'%d/%m/%Y') AS fechaElaboracion,
                `tags`.`tagdescription` AS UR,
                `tb_cat_unidades_ejecutoras`.`desc_ue` AS UE,
                DATE_FORMAT(`dtm_fecha_inicio`,'%d/%m/%Y') AS fechaIncio,
                DATE_FORMAT(`dtm_fecha_termino`,'%d/%m/%Y') AS fechaFin,
                `ln_objetivo_comicion` AS objetivo,
                `amt_importe_total` AS monto,
                IF(`amt_importe_comprobado`<>''&&`amt_importe_comprobado` IS NOT NULL,`amt_importe_comprobado`,0) AS montoComprobado,
                `tags`.`tagref` AS idUR,
                `tb_viaticos`.`id_nu_ue` AS nUE,
                `tb_viaticos`.`id_nu_viaticos` AS idFolioSol,
                `tb_viaticos`.`id_nu_estatus` AS idEstatus,
                `tb_viaticos`.`id_nu_estatus` AS idStatus,
                IF(`ind_tipo_gasto`=2,'Anticipado',IF(`ind_tipo_gasto`=1,'Devengado','')) AS `tipoGasto`,
                IF(`ind_tipo_solicitud`=1,'Nacional',IF(`ind_tipo_solicitud`=2,'Internacional','')) AS `tipoSolicitud`,
                `est`.`namebutton` AS `textoEstatus`

            FROM `tb_viaticos`
            INNER JOIN `tb_solicitud_itinerario` ON `tb_solicitud_itinerario`.`id_nu_solicitud_viaticos` = `tb_viaticos`.`id_nu_viaticos`
            INNER JOIN `tb_empleados` ON `tb_empleados`.`id_nu_empleado` = `tb_viaticos`.`id_nu_empleado`
            INNER JOIN `tags` ON `tags`.`tagref` = `tb_empleados`.`tagref`
            INNER JOIN `tb_cat_unidades_ejecutoras` ON `tb_cat_unidades_ejecutoras`.`ur` = `tags`.`tagref` AND `tb_viaticos`.`id_nu_ue` = `tb_cat_unidades_ejecutoras`.`ue`
            LEFT JOIN `tb_botones_status` AS est ON `est`.`statusid` = `tb_viaticos`.`id_nu_estatus` AND `est`.`sn_funcion_id` = '2338' AND `est`.`functionid` = 0

            $where

            ORDER BY `fechaElaboracion` DESC, `solicitud` DESC";
        // " LIMIT 50";
    $resExec = DB_query($sql, $db, '');
    //$data['sql'] = $sql;
    if (DB_num_rows($resExec)!=0) {
        $rows = array();
        $comprobarSol = [5,9,10];
        $enc = new Encryption;
        while ($row = mysqli_fetch_object($resExec)) {
            $sqlEst = "SELECT `namebutton` FROM `tb_botones_status` WHERE `sn_funcion_id` = 2338 AND `functionid` = 0 AND `statusid` = {$row->idEstatus}";
            $respEst = DB_query($sqlEst, $db);
            $fetchEst = DB_fetch_array($respEst);
            $urlGeneral = "&solicitud=>".$row->solicitud."&solComision=>" . $row->idFolioSol . "&estatus=>" . $row->idEstatus;
            $enc = new Encryption;
            $url = $enc->encode($urlGeneral);
            $liga= "URL=" . $url;
            $link = $row->solicitud;
            if(in_array($row->idEstatus, $comprobarSol)){
                $link = '<a type="button" id="btnAbrirComprobacion_'.$row->idFolioSol.'" name="btnAbrirComprobacion_'.$row->idFolioSol.'" href="comprobacionOficioComision.php?'.$liga.'" title="Comprobar Comisi&oacute;n" style="color: blue;">'.$row->solicitud.'</a>';
            }
             $urlImp = "&solicitud=>".$row->solicitud;
             $urlImpEncri = $enc->encode($urlImp);
             $ligaEncriptada="URL=".$urlImpEncri;

               
            $ligaImprimir='<a  id="imprimir'.$row->solicitud.'" href="impresionViaticos.php?'.$ligaEncriptada .'"   href="#" target="_blank">'."<span class='glyphicon glyphicon glyphicon-print'></span>".'</a>';

            $rows[] = [
                'solicitud'=> $link,
                'solString' => $row->solicitud,
                'fechaElaboracion' => $row->fechaElaboracion,
                'ur' => utf8_encode($row->UR),
                'ue' => utf8_encode($row->UE),
                'fechaInicio' => $row->fechaIncio,
                'fechaFin' => $row->fechaFin,
                'objetivo' => utf8_encode($row->objetivo),
                'monto' => $row->monto,
                'montoComprobado' => $row->montoComprobado,
                'status' => utf8_encode($fetchEst['namebutton']),
                'idfolio'=> $row->idFolioSol,
                'idStatus'=>$row->idEstatus,
                'nUR'=>$row->idUR,
                'nUE'=>$row->nUE,
                'imprimir'=>$ligaImprimir,
                'tipoSol'=>utf8_encode($row->tipoSolicitud),
                'tipoGasto'=>utf8_encode($row->tipoGasto),
                'textoEstatus' => utf8_encode($row->textoEstatus)
            ];
        }

        $data['success']=true;
        $data['msg']='Solicitud ejecutada con exito';
        $data['content'] = $rows;
    }
    // var_dump($data);
    return $data;
}

function llenarEstatus($db)
{
    $data = array('success'=>false, 'msg'=>'No se encontraron datos');
    $functionid = !empty($_POST['functionid'])?$_POST['functionid']:FUNCTIONID;
    # procesamiento de datos no terminados
    $condicion = " AND statusid in(1,2,3,7,8) ";//,11
    $sql = "SELECT DISTINCT statusname, namebutton, sn_nombre_secundario, sn_orden FROM tb_botones_status WHERE sn_funcion_id = '$functionid' AND functionid = 0 $condicion ORDER BY sn_orden";
    $result = DB_query($sql, $db);
    $rows = array(['label'=>'Seleccione una opción', 'title'=> 'Seleccione una opción', 'value'=>'']);
    while ($rs = DB_fetch_assoc($result)) {
        $rows[$rs['statusname']] = ['label'=>utf8_encode($rs['namebutton']), 'title'=>utf8_encode($rs['sn_nombre_secundario']), 'value'=>$rs['statusname']];
    }

    # procesamiento de datos terminados
    $condicionTerminadas = " AND statusid in(4,5,6,7,9,10) ";
    $sqlTerminadas = "SELECT DISTINCT statusname, namebutton, sn_nombre_secundario, sn_orden FROM tb_botones_status WHERE sn_funcion_id = '$functionid' AND functionid = 0 $condicionTerminadas ORDER BY sn_orden";
    $resultTerminadas = DB_query($sqlTerminadas, $db);
    $rowsTerminadas = array(['label'=>'Seleccione una opción', 'title'=> 'Seleccione una opción', 'value'=>'']);
    while ($rs = DB_fetch_assoc($resultTerminadas)) {
        $rowsTerminadas[$rs['statusname']] = ['label'=>utf8_encode($rs['namebutton']), 'title'=>utf8_encode($rs['sn_nombre_secundario']), 'value'=>$rs['statusname']];
    }

    # envio de la informacion a cliente
    $data['content'] = $rows;
    $data['contentTerminadas'] = $rowsTerminadas;
    $data['success'] = true;
    // if(DB_num_rows($result)){
    // }
    return $data;
}

function obtenCondiciones($datos, $tipo=0)
{
    $where = ' WHERE ';
    $flag = 0;
    # extraccion de los datos enviados
    $datos['fechaIni'] = date_format(date_create_from_format('d-m-Y', $datos['fechaIni']),'Y-m-d');
    $datos['fechaFin'] = date_format(date_create_from_format('d-m-Y', $datos['fechaFin']),'Y-m-d');
    extract($datos);
    # comprobacion de UR
    if(!empty($selectUnidadNegocio)){
        $where .= " `tags`.`tagref` = '$selectUnidadNegocio' ";
        $flag++;
    }
    # comprobacion de UE
    if(!empty($selectUnidadEjecutora)){
        // Para que muestre todas las solicitudes de todas las UE en caso de que no se haya especificado ninguna
        if ($selectUnidadEjecutora != "-1") {
            $where .= ($flag!=0?' AND ':'')." `tb_viaticos`.`id_nu_ue` = '$selectUnidadEjecutora' ";
            $flag++;
        }    
    }
    # comprobacion de folio de solicitud
    if(!empty($numeroSolicitud)){
        $where .= ($flag!=0?' AND ':'')." `sn_folio_solicitud` LIKE '%$numeroSolicitud%' ";
        $flag++;
    }
    # comprobacion de fechas
    if(!empty($fechaIni) && !empty($fechaFin)){
        //$fechaIni = conbercionFecha($fechaIni);
        //$fechaFin = conbercionFecha($fechaFin);
        if($flag!=0){ $where .= ' AND '; }
        // $where .= " dtm_fecha_inicio >= '$fechaIni 00:00:00' AND dtm_fecha_termino <= '$fechaFin 23:59:59' "; // CAmbio por fecha de elaboración 05.03.18
        $where .= " `dtm_fecha_elaboracion` >= '$fechaIni 00:00:00' AND `dtm_fecha_elaboracion` <= '$fechaFin 23:59:59' ";
        $flag++;
    }
    if(!empty($fechaIni) && empty($fechaFin)){
        $fechaIni = conbercionFecha($fechaIni);
        // $where .= ($flag!=0?' AND ':'')." dtm_fecha_inicio >= '$fechaIni 00:00:00' "; // CAmbio por fecha de elaboración 05.03.18
        $where .= ($flag!=0?' AND ':'')." `dtm_fecha_elaboracion` >= '$fechaIni 00:00:00' ";
        $flag++;
    }
    if(empty($fechaIni) && !empty($fechaFin)){
        $fechaFin = conbercionFecha($fechaFin);
        // $where .= ($flag!=0?' AND ':'')." dtm_fecha_termino <= '$fechaFin 23:59:59' "; // CAmbio por fecha de elaboración 05.03.18
        $where .= ($flag!=0?' AND ':'')." `dtm_fecha_elaboracion` <= '$fechaFin 23:59:59' ";
        $flag++;
    }
    # comprobaion de estatus
    if(!empty($selectEstatus)){
        $where .= ($flag!=0?' AND ':'')." `tb_viaticos`.`id_nu_estatus` = '$selectEstatus' ";
        $flag++;
    }else if(!empty($selectEstatusTerminadas)){
        $where .= ($flag!=0?' AND ':'')." `tb_viaticos`.`id_nu_estatus` = '$selectEstatusTerminadas' ";
        $flag++;
    }else{
        $subCond = $tipo==0? "'1','2','3','7','8'" : "'4','5','6','7','9','10'";//,'11'
        $where .= ($flag!=0?' AND ':'')." `tb_viaticos`.`id_nu_estatus` in($subCond) ";
        $flag++;
    }
    # comprobacion de datos vacios
    if($flag==0){ $where = ''; }
    return $where;
}

/**
 * Obtener estatus de viaticos
 * @param  [type] $db [description]
 * @return [type]     [description]
 */
function obtenerEstatusViaticos($db){

    //definicion de variables
    $data = array('success'=>true, 'msg'=>'Funcion ejecutada correctamente');
    $info = array();
    $noFuncion= $_POST["numeroFuncion"];
    $funciones= "";

    $criterios=" AND sn_funcion_id= '%d'";


    // Consultar estatus
    $sql= "SELECT * FROM tb_botones_status WHERE sn_flag_disponible=%d ".$criterios." ORDER BY statusid";
    $sql = sprintf($sql, 1,$noFuncion);
    $resExec = DB_query($sql, $db, '');
    while ($registro = DB_fetch_array($resExec)) {
        $info[] = array(
            'id' => $registro ['id'],
            'descripcion' => $registro ['sn_nombre_secundario'],
            'estatus' => $registro ['statusname'],
            'boton' => $registro ['namebutton']
        );
    }
    $data['content']=array('boton' => $info);
    return $data;
}

function fnObtenerIdentificadorPresupuesto($db, $clave)
{
    // Obtener informacion para identificador Inicio
    $cppt = "";
    $SQL = "SELECT 
    chartdetailsbudgetbytag.tagref,
    tb_cat_unidades_ejecutoras.ue as ue,
    chartdetailsbudgetbytag.cppt
    FROM chartdetailsbudgetbytag 
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    WHERE chartdetailsbudgetbytag.accountcode = '".$clave."'
    ";
    $ErrMsg = "No se encontro el Identificar de la Clave ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $cppt = $myrow['tagref']."-".$myrow['ue']."-".$myrow['cppt'];
    }
    return $cppt;
    // Obtener informacion para identificador Fin
}

function updateStatus($db)
{
    $data = array('success'=>false, 'msg'=>'Ocurrió un incidente al momento de actualizar la información.');
    $info = $_POST;
    $rows = $info['rows'];
    $leng = count($rows);
    $flag = 0;
    $info['comprometido'] = empty($info['comprometido'])?0:$info['comprometido'];
    if($leng){
            $enc = new Encryption;
            $newLinks = [];
            $newStatus = [];
            $noPermitidos = getNoPermitidos($db);
            foreach ($rows as $k => $rw) {
                $folio = $rw['idfolio'];
                $nuOficio = $rw['solicitud'];
                $tipoStatus = $info['type'];
                $where = "WHERE v.`id_nu_viaticos` = '$folio' AND v.`tagref` LIKE '$rw[nUR]' AND v.`id_nu_ue` LIKE '$rw[nUE]'";
                $sql = sprintf("SELECT SUM(vc.`amt_comprobado`) AS 'totalComprobado', v.* FROM `tb_viaticos` AS v LEFT JOIN `tb_cat_documentos_comprobacion` AS vc ON vc.`id_nu_solicitud` = v.`id_nu_viaticos` %s", $where);
                $result = DB_query($sql, $db);
                $fetchTempSol = DB_fetch_array($result);
                $numdat = DB_num_rows($result);

                $momentoPresupuestalAnteriorAComprobacion = ( $fetchTempSol['ind_tipo_gasto']==1 ? "2" : "5" );

                # si cuenta con alguna de las claves se comprueba el avanzar
                $claveComp = false;

                if(!empty($fetchTempSol['accountcode_general'])){ $claveComp = true; }
                if(!empty($fetchTempSol['accountcode_combustibles'])){ $claveComp = true; }

                # Validación adicional que evita que más de una persona mande el mismo oficio a validar.
                if($fetchTempSol['id_nu_estatus']==$tipoStatus){
                        $data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>, debido a que ya fue ".( $tipoStatus==8||$tipoStatus==6 ? "autorizado" : "validado" )." previamente. Actualice su navegador.";
                        $flag++;
                        continue;
                }

                # Revisión de documentos al autorizar comprobaciones
                if($tipoStatus==6&&!fnExistenDocumentosParaComprobacion($db,$folio)){
                        $data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>, debido a que no ha agregado documentos para comprobar.";
                        $flag++;
                        continue;
                }

                # Revisión de que el momento presupuestal anterior a comprobar sea el correcto
                // Considerar si se reemplaza fnConsultaMomentoPresupuestal($db,$folio) por fetchTempSol['ind_momento_presupuestal']
                if($tipoStatus==6&&fnConsultaMomentoPresupuestal($db,$folio)!=$momentoPresupuestalAnteriorAComprobacion){
                    $data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>, debido a que no se ha realizado ".( $fetchTempSol['ind_tipo_gasto']==1 ? "la autorización" : "el pago" )." del Oficio de Comisión.";
                    $flag++;
                    continue;
                }

                $infoClaves = array();
                $infoClaves[] = array(
                    'accountcode' => $fetchTempSol['accountcode_general']
                );
                $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                if (!$respuesta['result']) {
                    $data['msg'] .= $respuesta['mensaje'];
                        $flag++;
                        continue;
                }
                //$periodo = GetPeriod(date('d/m/Y'), $db);
                $periodo = $respuesta['periodo'];

                # ejecución de comprobación de suficiencia en el presupuesto
                if(  $claveComp && ( ($fetchTempSol['ind_tipo_gasto']==2&&$info['tipoCambio']==1)||($fetchTempSol['ind_tipo_gasto']==1&&$tipoStatus==6) )  ){
                    if(!compruebaPresupuestoClave($db, $folio, $fetchTempSol, $periodo)){
                        $data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>, debido a falta de presupuesto.";
                        $flag++;
                        continue;
                    }
                }

                $SQL = "SELECT IF(SUM(IF(a.`accountcode`<>'',1,0)) IS NOT NULL,SUM(IF(a.`accountcode`<>'',1,0)),0) AS 'RegistrosEncontrados'
                        FROM `accountxsupplier` AS a
                        INNER JOIN `tb_empleados` AS e ON e.`sn_clave_empleado` = a.`supplierid` AND a.`supplierid` <> '' AND e.`id_nu_empleado` = '$fetchTempSol[id_nu_empleado]'";

                //// Revisar si el proceso necesita hacerse en los Oficios de Comisión Anticipados
                if($tipoStatus==6&&DB_fetch_array(DB_query($SQL, $db))['RegistrosEncontrados']==0){
                    $data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>, debido a que el empleado no tiene cuenta asignada.";
                        $flag++;
                        continue;
                }

                $PP = ( strlen($fetchTempSol['accountcode_general'])==78 ? substr($fetchTempSol['accountcode_general'],26,4) : "" );
                $PGen = ( strlen($fetchTempSol['accountcode_general'])==78 ? substr($fetchTempSol['accountcode_general'],31,3) : "" );
                $cuenta5137 = "";
                $cuenta2119 = "";
                $cuenta1123 = "";
                $cuenta1112 = "";

                $ln_clave = fnObtenerIdentificadorPresupuesto($db, $fetchTempSol['accountcode_general']);

                // Los queries originales incluían estas líneas después de WHERE `categoryid` LIKE '$PGen'
                // AND `stockact` LIKE '1.1.2.3%'
                // AND `stockact` LIKE '5.1.3.7%'
                $SQL = "SELECT `stockact`, `accountegreso`, `adjglact`, `ln_abono_salida`

                        FROM `stockcategory` 

                        WHERE `ln_clave` LIKE '$ln_clave'
                        AND `accountegreso` LIKE (
                            SELECT a.`accountcode`
                            FROM `accountxsupplier` AS a
                            INNER JOIN `tb_empleados` AS e ON e.`sn_clave_empleado` = a.`supplierid` AND a.`supplierid` <> '' AND e.`id_nu_empleado` = '$fetchTempSol[id_nu_empleado]'
                            LIMIT 1
                        )";
                $resultCuenta = DB_query($SQL,$db);
                if(DB_num_rows($resultCuenta)){
                    while($myrowCuenta=db_fetch_array($resultCuenta)){
                        $cuenta5137 = $myrowCuenta['stockact'];
                        $cuenta2119 = $myrowCuenta['accountegreso'];
                        $cuenta1123 = $myrowCuenta['adjglact'];
                        $cuenta1112 = $myrowCuenta['ln_abono_salida'];
                    }
                }

                $cuentaAbonoPorTipoParaComprobacion = ( $fetchTempSol['ind_tipo_gasto']==1 ? $cuenta2119 : $cuenta1123 );

                //// Revisar si el proceso necesita hacerse en los Oficios de Comisión Anticipados
                if(  $tipoStatus==6&&( $cuenta2119==""||( $fetchTempSol['ind_tipo_gasto']==1&&$cuenta5137==""||($fetchTempSol['ind_tipo_gasto']==2&&$cuenta1123=="") ) )  ){
                    $data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>, debido a que el empleado no tiene registro en la matriz del devengado.";
                        $flag++;
                        continue;
                }

                $SQL = "SELECT *

                        FROM `suppliers` AS `s`
                        INNER JOIN `tb_empleados` AS `e` ON `e`.`sn_clave_empleado` = `s`.`supplierid` AND `e`.`id_nu_empleado` = '$fetchTempSol[id_nu_empleado]';";

                $resproveedor = DB_query($SQL, $db);
                $datosEmpleadoProveedor = ( DB_num_rows($resproveedor) ? db_fetch_array($resproveedor) : array() );

                if($tipoStatus==6&&$fetchTempSol['ind_tipo_gasto']==1&&!count($datosEmpleadoProveedor)){
                    $data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>, debido a que el empleado no tiene registro en la matriz de proveedores.";
                        $flag++;
                        continue;
                }

                $SQL = "SELECT *

                        FROM `tb_bancos_proveedores`

                        WHERE `ln_supplierid` = '$datosEmpleadoProveedor[supplierid]'
                        AND `ln_activo` = '1';";

                $resCuentas = DB_query($SQL, $db);
                $cuentaBancaria = ( DB_num_rows($resCuentas) ? db_fetch_array($resCuentas)['nu_id'] : "" );

                if($tipoStatus==6&&$fetchTempSol['ind_tipo_gasto']==1&&$cuentaBancaria==""){
                    $data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>, debido a que el empleado no tiene registrada una cuenta bancaria.";
                        $flag++;
                        continue;
                }
                $datosEmpleadoProveedor['nu_id'] = $cuentaBancaria;

                # cambio de estatus y comprometido del presupuesto
                if ($numdat) {
                    $upSql = sprintf("UPDATE `tb_viaticos` AS v SET v.`id_nu_estatus` = %d %s", $tipoStatus, $where);
                    $rs = DB_query($upSql, $db);
                    if($rs!=true){
                        $flag++;
                        $data['msg'] = "No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>";
                        continue;
                    }else{
                        $url = '&folio=>'.$folio.(in_array($tipoStatus, $noPermitidos)? '&status=>'.$tipoStatus : '');
                        //$newLinks[$folio] = '<a href="anexoTecnicoDetalle.php?URL='.($enc->encode($url)).'" style="color: blue;"><u>'.$nuOficio/*$folio*/.'</u></a>';
                         $newLinks[$folio] = '<a id="'.$folio.'" href="altaOficioComision.php?idFolio='.$folio.'"><u>'.$nuOficio.'</u></a>';
                        $newStatus[$folio] = getEstatusById($tipoStatus, $db);
                        // var_dump($newStatus);

                        # Se generan variables que se usan para movimientos presupuestables y contables
                        $totalGeneral = ($fetchTempSol['amt_importe_total'] - $fetchTempSol['amt_tansporte']);
                        $totalComprobado = ( $fetchTempSol['totalComprobado']>=$fetchTempSol['amt_importe_total']*0.9&&$fetchTempSol['totalComprobado']<=$fetchTempSol['amt_importe_total'] ? $fetchTempSol['amt_importe_total'] : $fetchTempSol['totalComprobado'] );
                        $totalNegativoGeneral = ($fetchTempSol['amt_importe_total'] - $fetchTempSol['amt_tansporte']) * -1;
                        $totalNegativoCombustible = $fetchTempSol['amt_tansporte'] * -1;

                        # ejecución del comprometido del presupuesto
                        # Por instrucciones del contador en el archivo Registros Contables Viáticos.xlsx el movimiento presupuestal de comprometido se realiza al autorizar el Oficio de Comisión, independientemente del tipo de viático
                        if( ($tipoStatus==8) ){
                        ////if( ($fetchTempSol['ind_tipo_gasto']==2&&$tipoStatus==8)||($fetchTempSol['ind_tipo_gasto']==1&&$tipoStatus==6) ){
                        ////(if($info['comprometido'] == 1 && $tipoStatus == 8){VALIDAR autorizar OC y autorizar COC
                            $descripcion = "Se realiza el comprometido de la solicitud $fetchTempSol[sn_folio_solicitud]. Por el usuario $_SESSION[UserID]";
                            # se comprometido el presupuesto de la clave principal
                            if(!empty($fetchTempSol['accountcode_general'])){
                                //$compromentido = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalNegativoGeneral, 259, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']);
                                if($folioAnterior!=$folio){
                                    if(fnConsultaMomentoPresupuestal($db,$folio)=="0"){
                                        $folioPolizaUe = fnObtenerFolioUeGeneral($db, $fetchTempSol['tagref'], $fetchTempSol['id_nu_ue'], TYPEMOV);
                                        $resultado= GeneraMovimientoContablePresupuesto(
                                            TYPEMOV,
                                            "POREJERCER",
                                            "COMPROMETIDO",
                                            $fetchTempSol['systypeno'],
                                            $periodo,
                                            $totalGeneral,
                                            $fetchTempSol['tagref'],
                                            date('Y-m-d'),
                                            $fetchTempSol['accountcode_general'],
                                            '',
                                            $db,
                                            false,
                                            '',
                                            '',
                                            $fetchTempSol['ln_objetivo_comicion'],
                                            $fetchTempSol['id_nu_ue'],
                                            1,
                                            0,
                                            $folioPolizaUe
                                        );
                                        //$comprometido = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral, 258, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']); // Abono
                                        //// Se cambió fnInsertPresupuestoLog por fnInsertPresupuestoLogAcomulado
                                        //// en lugar de $fetchTempSol['id_nu_ue']); se termina con $fetchTempSol['id_nu_ue'], 'DESC', 'disponible', '', '');
                                        $comprometido = fnInsertPresupuestoLogAcomulado($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral * -1, 259, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue'], 'DESC', 'disponible', '', ''); // Cargo

                                        fnActualizaMomentoPresupuesta($db,$folio,1);
                                    }
                                    /* Esto se volvió basura
                                    if(fnPresupuestoSinRegistrar($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral, 258, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue'])){
                                    }
                                    if(fnPresupuestoSinRegistrar($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral * -1, 259, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue'])){
                                    }
                                    */
                                }
                            }
                            # comprometido del presupuesto de la clave de combustible
                            if(!empty($fetchTempSol['accountcode_combustibles'])){
                                //$compromentido = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_combustibles'], $periodo, $totalNegativoCombustible, 259, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']);
                            }
                        }
                        // Se retira esta línea porque el cliente quiere usar el panel de devengado
                        // ($fetchTempSol['ind_tipo_gasto']==2&&$tipoStatus==8)||
                        # Por instrucciones del contador en el archivo Registros Contables Viáticos.xlsx el movimiento presupuestal de devengado se realiza al autorizar el Oficio de Comisión, únicamente con Oficios del tipo de viático
                        if( ($fetchTempSol['ind_tipo_gasto']==1&&$tipoStatus==8) ){
                        //if( ($fetchTempSol['ind_tipo_gasto']==1&&$tipoStatus==6) ){
                        ////if($fetchTempSol['ind_tipo_gasto']==1&&$tipoStatus == 6){ VALIDAR autorizar COC
                            $descripcion = "Se realiza el devengado de la solicitud $fetchTempSol[sn_folio_solicitud]. Por el usuario $_SESSION[UserID]";
                            # se comprometido el presupuesto de la clave principal
                            if(!empty($fetchTempSol['accountcode_general'])){
                                if($folioAnterior!=$folio){
                                    if(fnConsultaMomentoPresupuestal($db,$folio)=="1"){
                                        $folioPolizaUe = fnObtenerFolioUeGeneral($db, $fetchTempSol['tagref'], $fetchTempSol['id_nu_ue'], TYPEMOV);
                                        $resultado= GeneraMovimientoContablePresupuesto(
                                            TYPEMOV,
                                            "COMPROMETIDO",
                                            "DEVENGADO",
                                            $fetchTempSol['systypeno'],
                                            $periodo,
                                            $totalGeneral,
                                            $fetchTempSol['tagref'],
                                            date('Y-m-d'),
                                            $fetchTempSol['accountcode_general'],
                                            '',
                                            $db,
                                            false,
                                            '',
                                            '',
                                            $fetchTempSol['ln_objetivo_comicion'],
                                            $fetchTempSol['id_nu_ue'],
                                            1,
                                            0,
                                            $folioPolizaUe
                                        );
                                        //// Se reemplaza $periodo, $totalGeneral por $registroExistente['periodo'], $registroExistente['totalGeneral'] y se agrega consulta y bucle
                                        $sqlMovimientos = "SELECT `period` AS `periodo`, (`qty`*-1) AS `totalGeneral` FROM `chartdetailsbudgetlog` WHERE `type` = '".TYPEMOV."' AND `transno` = '$fetchTempSol[systypeno]' AND `nu_tipo_movimiento` = '259'";
                                        $resultMovimiento = DB_query($sqlMovimientos,$db);
                                        if(DB_num_rows($resultMovimiento)){
                                            while($registroExistente=db_fetch_array($resultMovimiento)){
                                                $devengado = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $registroExistente['periodo'], $registroExistente['totalGeneral'], 259, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']); // Abono
                                            }
                                        }
                                        //// Se cambió fnInsertPresupuestoLog por fnInsertPresupuestoLogAcomulado
                                        //// en lugar de $fetchTempSol['id_nu_ue']); se termina con $fetchTempSol['id_nu_ue'], 'DESC', 'disponible', '', '');
                                        $devengado = fnInsertPresupuestoLogAcomulado($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral * -1, 260, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue'], 'DESC', 'disponible', '', ''); // Cargo

                                        fnActualizaMomentoPresupuesta($db,$folio,2);
                                    }
                                }
                            }
                            # comprometido del presupuesto de la clave de combustible
                            if(!empty($fetchTempSol['accountcode_combustibles'])){
                                //$compromentido = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_combustibles'], $periodo, $totalNegativoCombustible, 259, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']);
                            }
                        }
                        if($tipoStatus==6){
                            if(!empty($fetchTempSol['accountcode_general'])){
                                    if(fnConsultaMomentoPresupuestal($db,$folio)==$momentoPresupuestalAnteriorAComprobacion&&$cuenta5137!=""&&$cuentaAbonoPorTipoParaComprobacion!=""){
                                        $folioPolizaUe = fnObtenerFolioUeGeneral($db, $fetchTempSol['tagref'], $fetchTempSol['id_nu_ue'], TYPEMOV);
                                        $SQL = "INSERT INTO gltrans (type,
                                                typeno,
                                                trandate,
                                                periodno,
                                                account,
                                                narrative,
                                                amount,
                                                tag,
                                                dateadded,
                                                userid,
                                                posted,
                                                ln_ue,
                                                purchno,
                                                stockid,
                                                grns,
                                                nu_folio_ue)
                                                VALUES ('".TYPEMOV."',
                                                '" . $fetchTempSol['systypeno'] . "',
                                                NOW(),
                                                '" . $periodo . "',
                                                '" . $cuenta5137 . "',
                                                '" . $fetchTempSol['ln_objetivo_comicion'] . "',
                                                '" . $totalGeneral . "',
                                                '" . $fetchTempSol['tagref'] . "',
                                                NOW(),
                                                '".$_SESSION['UserID']."',
                                                '1',
                                                '".$fetchTempSol['id_nu_ue']."',
                                                '0',
                                                '',
                                                '0',
                                                '".$folioPolizaUe."'
                                            )";
                                        //echo "<br>6.-" . $SQL;
                                        $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de las cuentas contables para la orden de compra no se realizo');
                                        $DbgMsg = _('El SQL utilizado es');
                                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                                        $cuenta_proveedor= traeCuentaProveedor($_SESSION['PO']->SupplierID, $db);

                                        $SQL = "INSERT INTO gltrans (type,
                                                typeno,
                                                trandate,
                                                periodno,
                                                account,
                                                narrative,
                                                amount,
                                                tag,
                                                dateadded,
                                                userid,
                                                posted,
                                                ln_ue,
                                                purchno,
                                                stockid,
                                                grns,
                                                nu_folio_ue)
                                                VALUES ('".TYPEMOV."',
                                                '" . $fetchTempSol['systypeno'] . "',
                                                NOW(),
                                                '" . $periodo . "',
                                                '" . $cuentaAbonoPorTipoParaComprobacion . "', 
                                                '" . $fetchTempSol['ln_objetivo_comicion'] . "',
                                                '" . $totalGeneral * -1 . "',
                                                '" . $fetchTempSol['tagref'] . "',
                                                NOW(),
                                                '".$_SESSION['UserID']."',
                                                '1',
                                                '".$fetchTempSol['id_nu_ue']."',
                                                '0',
                                                '',
                                                '0',
                                                '".$folioPolizaUe."'
                                            )";
                                        //echo "<br>7.-" . $SQL;
                                        $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de reverso no se realizo');
                                        $DbgMsg = _('El SQL utilizado es');
                                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                        /* Momento Devengado
                                        $folioPolizaUe = fnObtenerFolioUeGeneral($db, $fetchTempSol['tagref'], $fetchTempSol['id_nu_ue'], TYPEMOV);
                                        $resultado= GeneraMovimientoContablePresupuesto(
                                            TYPEMOV,
                                            "COMPROMETIDO",
                                            "DEVENGADO",
                                            $fetchTempSol['systypeno'],
                                            $periodo,
                                            $totalGeneral,
                                            $fetchTempSol['tagref'],
                                            date('Y-m-d'),
                                            $fetchTempSol['accountcode_general'],
                                            '',
                                            $db,
                                            false,
                                            '',
                                            '',
                                            $fetchTempSol['ln_objetivo_comicion'],
                                            $fetchTempSol['id_nu_ue'],
                                            1,
                                            0,
                                            $folioPolizaUe
                                        );
                                        $devengado = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral, 259, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']); // Abono
                                        $devengado = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral * -1, 260, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']); // Cargo

                                        $SQL = "INSERT INTO gltrans (type,
                                                typeno,
                                                trandate,
                                                periodno,
                                                account,
                                                narrative,
                                                amount,
                                                tag,
                                                dateadded,
                                                userid,
                                                posted,
                                                ln_ue,
                                                purchno,
                                                stockid,
                                                grns,
                                                nu_folio_ue)
                                                VALUES ('".TYPEMOV."',
                                                '" . $fetchTempSol['systypeno'] . "',
                                                NOW(),
                                                '" . $periodo . "',
                                                '" . $cuenta1123 . "',
                                                '" . $fetchTempSol['ln_objetivo_comicion'] . "',
                                                '" . $totalGeneral . "',
                                                '" . $fetchTempSol['tagref'] . "',
                                                NOW(),
                                                '".$_SESSION['UserID']."',
                                                '1',
                                                '".$fetchTempSol['id_nu_ue']."',
                                                '0',
                                                '',
                                                '0',
                                                '".$folioPolizaUe."'
                                            )";
                                        //echo "<br>6.-" . $SQL;
                                        $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de las cuentas contables para la orden de compra no se realizo');
                                        $DbgMsg = _('El SQL utilizado es');
                                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                                        $cuenta_proveedor= traeCuentaProveedor($_SESSION['PO']->SupplierID, $db);

                                        $SQL = "INSERT INTO gltrans (type,
                                                typeno,
                                                trandate,
                                                periodno,
                                                account,
                                                narrative,
                                                amount,
                                                tag,
                                                dateadded,
                                                userid,
                                                posted,
                                                ln_ue,
                                                purchno,
                                                stockid,
                                                grns,
                                                nu_folio_ue)
                                                VALUES ('".TYPEMOV."',
                                                '" . $fetchTempSol['systypeno'] . "',
                                                NOW(),
                                                '" . $periodo . "',
                                                '" . $cuenta2119 . "', 
                                                '" . $fetchTempSol['ln_objetivo_comicion'] . "',
                                                '" . $totalGeneral * -1 . "',
                                                '" . $fetchTempSol['tagref'] . "',
                                                NOW(),
                                                '".$_SESSION['UserID']."',
                                                '1',
                                                '".$fetchTempSol['id_nu_ue']."',
                                                '0',
                                                '',
                                                '0',
                                                '".$folioPolizaUe."'
                                            )";
                                        //echo "<br>7.-" . $SQL;
                                        $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de reverso no se realizo');
                                        $DbgMsg = _('El SQL utilizado es');
                                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                        */

                                        $SQL = "INSERT INTO supptrans (transno,
                                                tagref,
                                                type,
                                                supplierno,
                                                suppreference,
                                                reffiscal,
                                                origtrandate,
                                                trandate,
                                                duedate,
                                                ovamount,
                                                ovgst,
                                                rate,
                                                transtext,
                                                currcode,
                                                alt_tagref,
                                                ln_ue,
                                                id_clabe,
                                                nu_anio_fiscal)
                                                VALUES (".$fetchTempSol['systypeno'].",
                                                '" . $fetchTempSol['tagref'] . "',
                                                ".TYPEMOV.",
                                                '".$datosEmpleadoProveedor['supplierid']."',
                                                '',
                                                '',
                                                NOW(),
                                                NOW(),
                                                NOW(),
                                                ".$totalGeneral.",
                                                0,
                                                1,
                                                '".$fetchTempSol['ln_objetivo_comicion']."',
                                                '".$datosEmpleadoProveedor['currcode']."',
                                                '0',
                                                '".$fetchTempSol['id_nu_ue']."',
                                                '".$datosEmpleadoProveedor['nu_id']."',
                                                '$_SESSION[ejercicioFiscal]')";
                                        $ErrMsg = "No se agregó información al encabezado del pago";
                                        $TransResult = DB_query($SQL, $db, $ErrMsg);
                                        $SuppTransID = DB_Last_Insert_ID($db, 'supptrans', 'id');

                                        $sqlMovimientos = "SELECT * FROM `chartdetailsbudgetlog` WHERE `type` = '".TYPEMOV."' AND `transno` = '$fetchTempSol[systypeno]' AND `nu_tipo_movimiento` = '260'";
                                        $resultMovimiento = DB_query($sqlMovimientos,$db);
                                        if(DB_num_rows($resultMovimiento)){
                                            while($registroExistente=db_fetch_array($resultMovimiento)){
                                                $SQL="INSERT INTO supptransdetails(supptransid,
                                                        stockid,
                                                        description,
                                                        price,
                                                        qty,
                                                        orderno,
                                                        grns,
                                                        tagref_det, 
                                                        clavepresupuestal,
                                                        ln_clave_iden,
                                                        requisitionno,
                                                        comments,
                                                        period,
                                                        nu_id_compromiso,
                                                        nu_id_devengado,
                                                        nu_idret)
                                                        VALUES(".$SuppTransID.",
                                                        '1',
                                                        '".$registroExistente['cvefrom']."',
                                                        '1',
                                                        '".abs($registroExistente['qty'])."',
                                                        '0',
                                                        '0',
                                                        '".$registroExistente['tagref']."',
                                                        '".$registroExistente['cvefrom']."',
                                                        '$fetchTempSol[tagref]-$fetchTempSol[id_nu_ue]-$PP',
                                                        '0',
                                                        '".$fetchTempSol['ln_objetivo_comicion']."',
                                                        '".$registroExistente['period']."',
                                                        '',
                                                        '',
                                                        '0')";
                                                $ErrMsg = "No se guardó la información para el detalle del documento de pago";
                                                $TransResult2 = DB_query($SQL, $db, $ErrMsg);
                                            }
                                        }

                                        fnActualizaMomentoPresupuesta($db,$folio,3);
                                    }
                            }
                        }
                        if($tipoStatus==7){
                            # Código para regresar el movimiento contable del devengado, no se debe ejecutar en este modelo
                            if(1==2){
                                $SQL = "INSERT INTO gltrans (type,
                                        typeno,
                                        trandate,
                                        periodno,
                                        account,
                                        narrative,
                                        amount,
                                        tag,
                                        dateadded,
                                        userid,
                                        posted,
                                        ln_ue,
                                        purchno,
                                        stockid,
                                        grns,
                                        nu_folio_ue)
                                        VALUES ('".TYPEMOV."',
                                        '" . $fetchTempSol['systypeno'] . "',
                                        NOW(),
                                        '" . $periodo . "',
                                        '" . $cuenta5137 . "',
                                        '" . $fetchTempSol['ln_objetivo_comicion'] . "',
                                        '" . $totalGeneral * -1 . "',
                                        '" . $fetchTempSol['tagref'] . "',
                                        NOW(),
                                        '".$_SESSION['UserID']."',
                                        '1',
                                        '".$fetchTempSol['id_nu_ue']."',
                                        '0',
                                        '',
                                        '0',
                                        '".$folioPolizaUe."'
                                    )";
                                //echo "<br>6.-" . $SQL;
                                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de las cuentas contables para la orden de compra no se realizo');
                                $DbgMsg = _('El SQL utilizado es');
                                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                                $cuenta_proveedor= traeCuentaProveedor($_SESSION['PO']->SupplierID, $db);

                                $SQL = "INSERT INTO gltrans (type,
                                        typeno,
                                        trandate,
                                        periodno,
                                        account,
                                        narrative,
                                        amount,
                                        tag,
                                        dateadded,
                                        userid,
                                        posted,
                                        ln_ue,
                                        purchno,
                                        stockid,
                                        grns,
                                        nu_folio_ue)
                                        VALUES ('".TYPEMOV."',
                                        '" . $fetchTempSol['systypeno'] . "',
                                        NOW(),
                                        '" . $periodo . "',
                                        '" . $cuentaAbonoPorTipoParaComprobacion . "', 
                                        '" . $fetchTempSol['ln_objetivo_comicion'] . "',
                                        '" . $totalGeneral . "',
                                        '" . $fetchTempSol['tagref'] . "',
                                        NOW(),
                                        '".$_SESSION['UserID']."',
                                        '1',
                                        '".$fetchTempSol['id_nu_ue']."',
                                        '0',
                                        '',
                                        '0',
                                        '".$folioPolizaUe."'
                                    )";
                                //echo "<br>7.-" . $SQL;
                                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de reverso no se realizo');
                                $DbgMsg = _('El SQL utilizado es');
                                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                /* Reversa Momento Devengado
                                $resultado= GeneraMovimientoContablePresupuesto(
                                    TYPEMOV,
                                    "DEVENGADO",
                                    "COMPROMETIDO",
                                    $fetchTempSol['systypeno'],
                                    $periodo,
                                    $totalGeneral,
                                    $fetchTempSol['tagref'],
                                    date('Y-m-d'),
                                    $fetchTempSol['accountcode_general'],
                                    '',
                                    $db,
                                    false,
                                    '',
                                    '',
                                    $fetchTempSol['ln_objetivo_comicion'],
                                    $fetchTempSol['id_nu_ue'],
                                    1,
                                    0,
                                    $folioPolizaUe
                                );
                                $devengado = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral, 260, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']); // Abono
                                $devengado = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral * -1, 259, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']); // Cargo

                                $SQL = "INSERT INTO gltrans (type,
                                        typeno,
                                        trandate,
                                        periodno,
                                        account,
                                        narrative,
                                        amount,
                                        tag,
                                        dateadded,
                                        userid,
                                        posted,
                                        ln_ue,
                                        purchno,
                                        stockid,
                                        grns,
                                        nu_folio_ue)
                                        VALUES ('".TYPEMOV."',
                                        '" . $fetchTempSol['systypeno'] . "',
                                        NOW(),
                                        '" . $periodo . "',
                                        '" . $cuenta1123 . "',
                                        '" . $fetchTempSol['ln_objetivo_comicion'] . "',
                                        '" . $totalGeneral * -1 . "',
                                        '" . $fetchTempSol['tagref'] . "',
                                        NOW(),
                                        '".$_SESSION['UserID']."',
                                        '1',
                                        '".$fetchTempSol['id_nu_ue']."',
                                        '0',
                                        '',
                                        '0',
                                        '".$folioPolizaUe."'
                                    )";
                                //echo "<br>6.-" . $SQL;
                                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de las cuentas contables para la orden de compra no se realizo');
                                $DbgMsg = _('El SQL utilizado es');
                                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                                $cuenta_proveedor= traeCuentaProveedor($_SESSION['PO']->SupplierID, $db);

                                $SQL = "INSERT INTO gltrans (type,
                                        typeno,
                                        trandate,
                                        periodno,
                                        account,
                                        narrative,
                                        amount,
                                        tag,
                                        dateadded,
                                        userid,
                                        posted,
                                        ln_ue,
                                        purchno,
                                        stockid,
                                        grns,
                                        nu_folio_ue)
                                        VALUES ('".TYPEMOV."',
                                        '" . $fetchTempSol['systypeno'] . "',
                                        NOW(),
                                        '" . $periodo . "',
                                        '" . $cuenta2119 . "', 
                                        '" . $fetchTempSol['ln_objetivo_comicion'] . "',
                                        '" . $totalGeneral . "',
                                        '" . $fetchTempSol['tagref'] . "',
                                        NOW(),
                                        '".$_SESSION['UserID']."',
                                        '1',
                                        '".$fetchTempSol['id_nu_ue']."',
                                        '0',
                                        '',
                                        '0',
                                        '".$folioPolizaUe."'
                                    )";
                                //echo "<br>7.-" . $SQL;
                                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de reverso no se realizo');
                                $DbgMsg = _('El SQL utilizado es');
                                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                */
                            }
                            # se comprometido el presupuesto de la clave principal
                            if(fnConsultaMomentoPresupuestal($db,$folio)==2){
                                $descripcion = 'Se regresa el devengado a comprometido de la solicitud '.
                                    $fetchTempSol['sn_folio_solicitud'].'. Por el usuario '.$_SESSION['UserID'];
                                $folioPolizaUe = fnObtenerFolioUeGeneral($db, $fetchTempSol['tagref'], $fetchTempSol['id_nu_ue'], TYPEMOV);
                                $resultado= GeneraMovimientoContablePresupuesto(
                                    TYPEMOV,
                                    "DEVENGADO",
                                    "COMPROMETIDO",
                                    $fetchTempSol['systypeno'],
                                    $periodo,
                                    $totalGeneral,
                                    $fetchTempSol['tagref'],
                                    date('Y-m-d'),
                                    $fetchTempSol['accountcode_general'],
                                    '',
                                    $db,
                                    false,
                                    '',
                                    '',
                                    $fetchTempSol['ln_objetivo_comicion'],
                                    $fetchTempSol['id_nu_ue'],
                                    1,
                                    0,
                                    $folioPolizaUe
                                );
                                // Se documentan las dos líneas siguientes, se reemplaza por fnInsertPresupuestoLogMovContrarios terminando en 0,0); antes de fnActualizaMomentoPresupuesta($db,$folio,0);
                                // $devengado = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral, 260, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']); // Abono
                                // $devengado = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral * -1, 259, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']); // Cargo

                                fnActualizaMomentoPresupuesta($db,$folio,1);
                            }
                            if(fnConsultaMomentoPresupuestal($db,$folio)==1){
                                $descripcion = 'Se regresa el comprometido de la solicitud '.
                                    $fetchTempSol['sn_folio_solicitud'].'. Por el usuario '.$_SESSION['UserID'];
                                $folioPolizaUe = fnObtenerFolioUeGeneral($db, $fetchTempSol['tagref'], $fetchTempSol['id_nu_ue'], TYPEMOV);
                                $resultado= GeneraMovimientoContablePresupuesto(
                                    TYPEMOV,
                                    "COMPROMETIDO",
                                    "POREJERCER",
                                    $fetchTempSol['systypeno'],
                                    $periodo,
                                    $totalGeneral,
                                    $fetchTempSol['tagref'],
                                    date('Y-m-d'),
                                    $fetchTempSol['accountcode_general'],
                                    '',
                                    $db,
                                    false,
                                    '',
                                    '',
                                    $fetchTempSol['ln_objetivo_comicion'],
                                    $fetchTempSol['id_nu_ue'],
                                    1,
                                    0,
                                    $folioPolizaUe
                                );
                                //$comprometido = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral, 258, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']); // Abono
                                // Se documenta la siguiente línea, se reemplaza por fnInsertPresupuestoLogMovContrarios terminando en 0,0);
                                // $comprometido = fnInsertPresupuestoLog($db, TYPEMOV, $fetchTempSol['systypeno'], $fetchTempSol['tagref'], $fetchTempSol['accountcode_general'], $periodo, $totalGeneral, 259, "", $descripcion, 1, '', 0, $fetchTempSol['id_nu_ue']); // Cargo
                                fnInsertPresupuestoLogMovContrarios($db, TYPEMOV, $fetchTempSol['systypeno'], 0, 0);

                                fnActualizaMomentoPresupuesta($db,$folio,0);
                            }
                        }
                        $data['msg'] .= "<br>Se realizará la modificación solicitada para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>.";
                    }
                }else{
                    $flag++;
                    $data['msg'] .= "No se encontró la información solicitada para el folio <strong>$fetchTempSol[sn_folio_solicitud]</strong>";
                    continue;
                }
            }
            if(!$flag){
                $data['success'] = true;
                ////$data['msg']='Se realizaron las modificaciones solicitadas';
                $data['links']=$newLinks;
                $data['nuevoEstatus'] = $newStatus;
            }
    }else{
        $data['msg'] = 'La información proporcionada no es sufuciente para realizar el cambio';
    }
    // $data['info'] = $info;

    return $data;
}

function fnConsultaMomentoPresupuestal($db,$id_nu_viaticos){
    $sql = "SELECT `ind_momento_presupuestal`

            FROM `tb_viaticos`

            WHERE `id_nu_viaticos` = '$id_nu_viaticos'";

    return DB_fetch_array(DB_query($sql, $db))['ind_momento_presupuestal'];
}

function fnActualizaMomentoPresupuesta($db,$id_nu_viaticos,$tipoMovimiento){
    $sql = "UPDATE `tb_viaticos`

            SET `ind_momento_presupuestal` = '$tipoMovimiento'

            WHERE `id_nu_viaticos` = '$id_nu_viaticos'";

    DB_query($sql, $db);
}

function fnExistenDocumentosParaComprobacion($db,$id_nu_viaticos){
    /*$sql = "SELECT v.`id_nu_viaticos`, `amt_importe_total` AS 'ImporteTotal', si.`amt_importe` AS 'ImporteTotalAcumulado', SUM(dc.`amt_total`) AS 'TotalAComprobar', SUM(dc.`amt_comprobado`) AS 'TotalComprobado'

            FROM `tb_cat_documentos_comprobacion` AS dc
            INNER JOIN `tb_solicitud_itinerario` AS si ON si.`id_nu_solicitud_viaticos` = dc.`id_nu_solicitud`
            INNER JOIN `tb_viaticos` AS v ON v.`id_nu_viaticos` = dc.`id_nu_solicitud`

            WHERE dc.`id_nu_solicitud` = '$id_nu_viaticos'

            GROUP BY dc.`id_nu_solicitud`";*/

    $sql = "SELECT IF(v.`amt_importe_total` IS NOT NULL,v.`amt_importe_total`,'No') AS 'ImporteTotal', IF(SUM(dc.`amt_comprobado`) IS NOT NULL,SUM(dc.`amt_comprobado`),'No') AS 'TotalComprobado'

            FROM `tb_cat_documentos_comprobacion` AS dc
            INNER JOIN `tb_viaticos` AS v ON v.`id_nu_viaticos` = dc.`id_nu_solicitud`

            WHERE dc.`id_nu_solicitud` = '$id_nu_viaticos'";

    $montoComprobacion =  DB_fetch_array(DB_query($sql, $db));

    return ( count($montoComprobacion) ? ( $montoComprobacion['TotalComprobado'] != 'No' && $montoComprobacion['ImporteTotal']!='No' ? true : false ) : false );

    //return ( count($montoComprobacion) ? ( $montoComprobacion['TotalComprobado'] >= $montoComprobacion['ImporteTotal']*0.9 && $montoComprobacion['TotalComprobado'] <= $montoComprobacion['ImporteTotal'] ? true : false ) : false );
}

function montosAComprobar($db){
    $info = $_POST['ids'];
    $mensajes = array();

    if(count($info)){
        foreach($info AS $idFolio){
            $sql = "SELECT v.`sn_folio_solicitud` AS 'Folio', IF(v.`amt_importe_total` IS NOT NULL,v.`amt_importe_total`,'No') AS 'ImporteTotal', IF(SUM(dc.`amt_comprobado`) IS NOT NULL,SUM(dc.`amt_comprobado`),'No') AS 'TotalComprobado'

            FROM `tb_cat_documentos_comprobacion` AS dc
            INNER JOIN `tb_viaticos` AS v ON v.`id_nu_viaticos` = dc.`id_nu_solicitud`

            WHERE dc.`id_nu_solicitud` = '$idFolio'";

            $montoComprobacion =  DB_fetch_array(DB_query($sql, $db));

            if($montoComprobacion['TotalComprobado'] != 'No' && $montoComprobacion['ImporteTotal']!='No'){
                if($montoComprobacion['TotalComprobado'] > $montoComprobacion['ImporteTotal']){
                    $mensajes[] = "El monto de comprobación del folio <strong>$montoComprobacion[Folio]</strong> excede el monto a comprobar.";
                }
                if($montoComprobacion['TotalComprobado'] < $montoComprobacion['ImporteTotal']*0.9){
                    $mensajes[] = "El monto de comprobación del folio <strong>$montoComprobacion[Folio]</strong> es inferior al mínimo a comprobar.";
                }
            }
        }
    }

    $data['msg'] = ( count($mensajes) ? implode("<br>", $mensajes) : "" );
    $data['success'] = ( count($mensajes) ? true : false );

    return $data;
}

function fnPresupuestoSinRegistrar($db, $type, $transno, $tagref, $clave, $periodo, $cantidad=0, $tipoMovimiento=0, $partida_esp="", $description="", $sn_disponible = 1, $statusid = "", $sn_funcion_id = 0, $ue=""){
    if(empty(trim($partida_esp))){
        $SQL = "SELECT `partida_esp` FROM `chartdetailsbudgetbytag` WHERE `accountcode` LIKE '$clave' AND `anho` = '$_SESSION[ejercicioFiscal]'";
        $resultClave = DB_query($SQL, $db, $ErrMsg);
        while( $rowClave = DB_fetch_array($resultClave)){
            $partida_esp = $rowClave ['partida_esp'];
        }
    }
    if(empty(trim($partida_esp))){
        $partida_esp = 0;
    }

    $SQL = "SELECT COUNT(`idmov`) AS 'RegistrosEncontrados'

            FROM `chartdetailsbudgetlog`

            WHERE `userid` LIKE '$_SESSION[UserID]'
            AND `qty` = '$cantidad'
            AND `description` LIKE '$description'
            AND `cvefrom` LIKE '$clave'
            AND `type` = '$type'
            AND `transno` = '$transno'
            AND `tagref` LIKE '$tagref'
            AND `period` = '$periodo'
            AND `partida_esp` = '$partida_esp'
            AND `nu_tipo_movimiento` = '$tipoMovimiento'
            AND `sn_disponible` = '$sn_disponible'
            AND `estatus` = '$statusid'
            AND `sn_funcion_id` = '$sn_funcion_id'
            AND `ln_ue` LIKE '$ue'";
    $ErrMsg = "No se pudo consultar la información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    return DB_fetch_array($TransResult)['RegistrosEncontrados'] ? false : true;
}

/* EJECUCIÓN DE FUNCIONES */
try{
    $data = call_user_func_array($_POST['method'],[$db]);
}
catch(Exception $e){
    $data = array('success'=>false, 'msg'=>'Ocurrió un incidente inesperado al momento de consultar la información.');
}
/* MODIFICACIÓN DE HEADER */
header('Content-type:application/json;charset=utf-8');
/* ENVÍO DE INFORMACIÓN */
echo json_encode($data);

function conbercionFecha($fecha)
{
    $exp = explode('-', $fecha);
    return "$exp[2]-$exp[1]-$exp[0]";
}

function getNoPermitidos($db){
    // $data = [4,5,6,7,8];
    $data = [7,11];
    switch (getPerfil($db)) {
        case 9:
            // $data = array_merge($data,[2,3]);
            $data = array_merge($data,[2,8]);
            break;
        case 10:
            // $data = array_merge($data,[3]);
            $data = array_merge($data,[8]);
            break;
    }
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

function getEstatusById($id, $db)
{
    $sql = "SELECT `statusname` as id, `namebutton` as name FROM `tb_botones_status` WHERE `sn_funcion_id` = ".FUNCTIONID." AND `statusname` = '$id' LIMIT 1;";
    $result = DB_query($sql, $db);
    $resTemp = DB_fetch_assoc($result);
    return [utf8_encode($resTemp['name']), $resTemp['id']];
}

function compruebaPresupuestoClave($db, $identificador, $solicitud, $periodo)
{
    # comprobación del periodo abierto
    //$periodo = GetPeriod(date('d/m/Y'), $db);
    if($periodo < 0){ return false; }
    # obtención del nombre del mes corriente
    $nombreMes = obtenNombreMes($db, $periodo);
    if(empty($nombreMes)){ return false; }
    $nombreMes = $nombreMes."Acomulado";
    # se valida la existencia de la clave presupuestal
    $accountcode_general = '';
    if(!empty($solicitud['accountcode_general'])){
        $accountcode_general = $solicitud['accountcode_general'];
        if(validaExiteClavePresupuesto($db, $accountcode_general)){
            # consulta de clave presupuestal y se obtiene el primer indice
            $respClave = fnInfoPresupuesto($db, $accountcode_general, $periodo)[0];
            if($respClave[$nombreMes] <= 0){ return false; }
            # comprobación de monto de comisión contra el presupuesto del mes
            if($respClave[$nombreMes] < $solicitud['amt_importe_total']){ return false; }
            # se retorna un verdadero debido a que si cuenta con presupuesto en la clave
            return true;
        }
    }
    /*$accountcode_combustibles = '';
    if(!empty($solicitud['accountcode_combustibles'])){
        $accountcode_combustibles = $solicitud['accountcode_combustibles'];
        if(validaExiteClavePresupuesto($db, $accountcode_combustibles)){
            # consulta de clave presupuestal y se obtiene el primer indice
            $respClave = fnInfoPresupuesto($db, $accountcode_combustibles, $periodo)[0];
            if($respClave[$nombreMes] <= 0){ return false; }
            # comprobación de monto de comisión contra el presupuesto del mes
            if($respClave[$nombreMes] < $solicitud['amt_importe_total']){ return false; }
            # se retorna un verdadero debido a que si cuenta con presupuesto en la clave
            return true;
        }
    }*/
}

function validaExiteClavePresupuesto($db, $clave)
{
    $sql = "SELECT * FROM `chartdetailsbudgetbytag` WHERE `accountcode` LIKE '$clave' AND `anho` = '$_SESSION[ejercicioFiscal]'";
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
    while ($row = DB_fetch_array($result)) { $nombreMes = $row['mesName']; }
    # retorno de la informacion
    return $nombreMes;
}
