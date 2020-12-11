<?php
/**
 * Resguardo de activo fijo.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /resguardo_detalles_modelo.php
 * Fecha Creación: 05.04.18
 * Se genera el presente programa para la visualización de la información
 * del detalle de los resguardos.
 */

$PageSecurity = 1;
$PathPrefix = '../';
$funcion=2308;

//

//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option =$_POST['option'];
$enc = new Encryption;

define('SUBIDAARCHIVOS', realpath(dirname(__FILE__)) . '/' . $PathPrefix . 'archivos/');

if($option == "obtenerResguardo"){
	$folio=$_POST["intFolio"];

	// ================= Obtener Informacion Encbezado ====================
	$SQL = "SELECT fr.idResguardo,
					fr.userid,
                    concat(te.ln_nombre,' ',te.sn_primer_apellido,' ',te.sn_segundo_apellido) AS empleado,
                    fr.folio, 
                    DATE_FORMAT(fr.fecha,'%d-%m-%Y') as fecha,
                    fr.ur,
                    concat(fr.ur,'-',tags.tagdescription) as ur_name,
                    fr.ue,
                    CONCAT(fr.ue, ' - ', tce.desc_ue) as ue_name,
                    DATE_FORMAT(fr.fechaultimoresguardo,'%d-%m-%Y') as fechaultimoresguardo,
                    fr.observaciones,
                    fr.estatus,
                    fr.ln_ubicacion,
                    tb_resguardo_status.description as nameEstatus
            FROM fixedasset_Resguardos fr
            left join tb_resguardo_status on fr.estatus = tb_resguardo_status.id 
            LEFT JOIN tb_empleados te on fr.userid = te.id_nu_empleado 
            LEFT JOIN tags ON fr.ur = tags.tagref
            LEFT JOIN tb_cat_unidades_ejecutoras tce ON tags.tagref = tce.ur 
            WHERE fr.folio = '". $folio ."'";
    //echo "sql:".$SQL;
    $ErrMsg = "No se obtuvieron las resguardos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    while ($myrow = DB_fetch_array($TransResult)) {
        $estatus="Actual";
        if($myrow ['estatus'] == '0'){
            $estatus="Historico";
        }

        $encabezado[] = array(
            'UR' => $myrow ['ur'],
            'URDescripcion' => $myrow ['ur_name'],
            'UE' => $myrow ['ue'],
            'UEDescripcion' => $myrow ['ue_name'],
            'Folio' => $myrow ['folio'],
            'idEmpleado' => $myrow ['userid'],
            'Empleado' => $myrow ['empleado'],
            'Fecha_Registro' => $myrow ['fecha'],
            'Fecha_Ultima' => $myrow ['fechaultimoresguardo'],
            'Observaciones' => $myrow ['observaciones'],
            'estatus' => $myrow['estatus'],
            'ln_ubicacion' => $myrow['ln_ubicacion'],
            'nameEstatus' => $myrow['nameEstatus']);
    }

    // ================================ / ====================================


    $SQL = "SELECT  fdr.id,
                    fdr.folio,
                    fdr.assetid,
                    fdr.estatus,
                    DATE_FORMAT(fdr.fecha,'%d-%m-%Y') as fecha,
                    coalesce(DATE_FORMAT(fdr.fecha_desincorporacion,'%d-%m-%Y'),'&nbsp') as fecha_desincorporacion,
                    fdr.observaciones,
                    fixedassets.barcode,
                    fixedassets.description,
                    fixedassets.`tipo_bien`,
                    fixedAssetCategoryBien.`description` as tipoBien,
                    fdr.kmInicial,
                    fdr.kmFinal
			FROM fixedasset_detalle_resguardos fdr 
			LEFT JOIN fixedassets ON fdr.assetid = fixedassets.assetid
            LEFT JOIN fixedAssetCategoryBien ON fixedassets.`tipo_bien` = fixedAssetCategoryBien.`id`
			WHERE folio = '".$folio."' 
			ORDER BY fdr.id;";
    //echo "sql:".$SQL;
    $ErrMsg = "No se obtuvieron las resguardos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

    	$statusPartida="Activo";
        if($myrow['estatus'] == '0'){
            $statusPartida="Baja";
        }

        $modificar = '<a onclick="fnModificar('.$myrow ['id'].')"><span class="glyphicon glyphicon-edit"></span></a>';
        $baja = '<a onclick="fnModificar('.$myrow ['id'].')"><span class="glyphicon glyphicon-trash"></span></a>';

        $info[] = array(
            'idActivo' => $myrow ['id'],
            'barcode' => $myrow ['barcode'],
            'description' => $myrow ['description'],
            'estatus' => $statusPartida,
            'fecha' => $myrow ['fecha'],
            'fecha_baja' => $myrow ['fecha_desincorporacion'],
            'observaciones' => $myrow ['observaciones'],
            'idtipoBien' => $myrow ['tipo_bien'],
            'tipoBien' => $myrow ['tipoBien'],
            'kmInicial' => $myrow ['kmInicial'],
            'kmFinal' => $myrow ['kmFinal'],
            'Modificar' => $modificar,
            'Baja' => $baja);
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idActivo', type: 'string' },";
    $columnasNombres .= "{ name: 'barcode', type: 'string' },";
    $columnasNombres .= "{ name: 'description', type: 'string' },";
    $columnasNombres .= "{ name: 'estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_baja', type: 'string' },";
    $columnasNombres .= "{ name: 'observaciones', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Baja', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'idActivo', datafield: 'idActivo', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Número de Inventario', datafield: 'barcode', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'description', width: '30%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'estatus', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Registro', datafield: 'fecha', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Baja', datafield: 'fecha_baja', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Observaciones', datafield: 'observaciones', width: '20%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Baja', datafield: 'Baja', width: '10%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('encabezado'=>$encabezado, 'datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if($option == "agregarResguardo"){
	$assetid= $_POST['selectPatrimonio_modal'];
    $empleado= $_POST['selectEmpleados_modal'];
    $ur= $_POST['selectUnidadNegocio_modal'];
    $ue= $_POST['selectUnidadEjecutora_modal'];
    $observaciones= $_POST['txtObservaciones_modal'];
    $folio= $_POST['folio'];

    $SQL = "INSERT INTO `fixedasset_detalle_resguardos` (`folio`, `assetid`, `estatus`, `fecha`, `ur`, `ue`, `observaciones`)
                VALUES ('".$folio."', ".$assetid.", '1', curdate(), ".$ur.", ".$ue.",'".$observaciones."');";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    if($TransResult){
    	//Modificamos el estatus del activo fijo como en resguardo
    	$SQL ="UPDATE fixedasset_Resguardos SET fechaultimoresguardo =curdate()  WHERE folio='".$folio."';";
    	$TransResult = DB_query($SQL, $db, $ErrMsg);

    	$SQL ="UPDATE fixedassets SET status ='9'  WHERE assetid=".$assetid.";";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if(!$TransResult){
            $Mensaje ="Problemas al modificar el estatus del activo";
        } 

        $Mensaje ="Se agrego al resguardo con folio: ".$folio."";
    }else{
        $Mensaje ="Problemas al agregar al resguardo con folio: ".$folio."";
    }
}

if($option == "nuevoResguardo"){
    $ur= $_POST['ur'];
    $ue= $_POST['ue'];
    $empleado= $_POST['empleado'];
    $observaciones= $_POST['observaciones'];
    $ln_ubicacion= $_POST['txtUbicacion'];

    // //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // //!!   Revisamos si ya tiene un resguardo.         !!
    // //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $SQL = "SELECT * FROM fixedasset_Resguardos WHERE userid = ".$empleado." and estatus='1' ORDER BY folio DESC LIMIT 1;";
    $ErrMsg = "No se obtuvo datos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $intRow=DB_num_rows($TransResult);

    if($intRow<=0){

    
        // $folioOld="";
        // //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // //!! Si tiene un resguardo modificamos fecha y status. !!
        // //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // if(DB_num_rows($TransResult)>0){
        //     $folioOld=$myRow['folio'];
        //     $SQL ="UPDATE fixedasset_Resguardos SET `estatus`='0', fechaultimoresguardo=curdate() WHERE folio=".$folioOld.";";
        //     $TransResult = DB_query($SQL, $db, $ErrMsg);
        // }

        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //!! Registramos el siguiente reguardo estatus activo. !!
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $folio = GetNextTransNo('1002',$db);
            $SQL = "INSERT INTO `fixedasset_Resguardos` (`userid`,`folio`, `fecha`, `estatus`, `fechaultimoresguardo`, `ur`, `ue`,`observaciones`, ln_ubicacion)
            VALUES (  ".$empleado.",".$folio.", curdate(), '1', curdate(), ".$ur.", ".$ue.",'".$observaciones."', '".$ln_ubicacion."');";
            //echo $SQL;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        // if($TransResult){
        //     if($folioOld !=""){
                
        //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //         //!!  Si existe modificaciones en las observaciones.   !!
        //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //         for ($i=0; $i < $_POST['numPartidasObs']; $i++) {
        //             $SQL="UPDATE `fixedasset_detalle_resguardos` SET `observaciones` ='".$_POST['observaciones'.$i]."' WHERE id='".$_POST['idPartida'.$i]."'";
        //             $TransResult = DB_query($SQL, $db, $ErrMsg);
        //         }

        //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //         //!! Jalamos historial de activos de ultimo resguardo. !!
        //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //         $SQL = "INSERT INTO `fixedasset_detalle_resguardos` (`folio`, `assetid`, `estatus`, `fecha`, `ur`, `ue`, `observaciones`) SELECT '".$folio."',`assetid`, `estatus`, `fecha`, `ur`, `ue`, `observaciones` FROM  `fixedasset_detalle_resguardos` WHERE folio = '".$folioOld."'";

        //         $TransResult = DB_query($SQL, $db, $ErrMsg);
        //     }
        // }

        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //!!    Insertamos los nuevos activos seleccionados.   !!
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        if($TransResult){
            $indexObs=($_POST['numPartidasObs']);
            for ($i=0; $i < $_POST['numActivos']; $i++) { 
                $SQL = "INSERT INTO `fixedasset_detalle_resguardos` (`folio`, `assetid`, `estatus`, `fecha`, `ur`, `ue`, `observaciones`,`kmInicial`, `kmFinal`)
                    VALUES ('".$folio."', ".$_POST['selectActivoFijo'.($i + 1)].", '1', curdate(), ".$ur.", ".$ue.",'".$_POST['observaciones'.$indexObs]."','".$_POST['txtKMInicial'.$i]."','".$_POST['txtKMFinal'.$i]."');";
                //echo $SQL;
                $indexObs++;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                if(!$TransResult){
                    $Mensaje ="Problemas al guardar activos fijos";
                    break;
                }

                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                //!!      Modificamos Activos en estatus resguardo.    !!
                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                $SQL ="UPDATE fixedassets SET status ='9'  WHERE assetid=".$_POST['selectActivoFijo'.($i + 1)].";";
                //echo $SQL;
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                if(!$TransResult){
                    $Mensaje ="Problemas al modificar el estatus del activo";
                    break;
                } 

                $cadenaDatosInsertar ="";
                if(isset($_FILES['archivos'])){
                    foreach ($_FILES['archivos']['tmp_name'] as $key => $tmp_name) {

                        $name_visible = $_FILES['archivos']['name'][$key];
                        $file_name = $key.date('YmdHis').$_FILES['archivos']['name'][$key];
                        $name_visible=str_replace(" ", "", $name_visible);
                        $file_name=str_replace(" ", "", $file_name);
                        $file_type=$_FILES['archivos']['type'][$key];

                        $cadenaDatosInsertar .= "('".$_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."',1002,'".$name_visible."','".$folio."','".$_POST['observacionFile'.$key]."','1'),";

                        moverArchivo($file_name,$_FILES['archivos']['tmp_name'][$key],SUBIDAARCHIVOS);
                    }

                    $cadenaDatosInsertar = substr($cadenaDatosInsertar, 0, -1);

                    if($cadenaDatosInsertar !=""){
                        $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`nu_tipo_sys`,`ln_nombre`, `nu_trasnno`, `txt_descripcion`,ind_active) VALUES ".$cadenaDatosInsertar;

                        $ErrMsg = "Problemas al guardar el archivo.";
                        $result = DB_query($SQL, $db, $ErrMsg);
                    }
                } 

            }
        }

        if($TransResult){
            $url = "&Folio=>" . $folio;
            $url = $enc->encode($url);
            $liga_folio= "URL=". $url;
            $contenido[] = array(
                'urlFolio' => 'resguardo_detalles.php?'.$liga_folio
            );
            $Mensaje ="Se agrego al resguardo con folio: ".$folio."";
        }else{
            $Mensaje ="Problemas al agregar al resguardo con folio: ".$folio."";
        }
    }else{
        $TransResult=true;
        $Mensaje='1';
    }

}


if($option == "modificarResguardo"){
    $ur= $_POST['ur'];
    $ue= $_POST['ue'];
    $empleado= $_POST['empleado'];
    $observaciones= $_POST['observaciones'];
    $ln_ubicacion= $_POST['txtUbicacion'];


    $SQL = "SELECT fixedasset_Resguardos.*, CONCAT(tb_empleados.ln_nombre,' ',tb_empleados.sn_primer_apellido,' ',tb_empleados.sn_segundo_apellido ) AS empleado
            FROM fixedasset_Resguardos 
            LEFT JOIN tb_empleados ON  fixedasset_Resguardos.userid =  tb_empleados.id_nu_empleado
            WHERE userid = ".$empleado." and estatus='1' ORDER BY folio DESC LIMIT 1;";
    $ErrMsg = "No se obtuvo datos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myRow=DB_fetch_array($TransResult);

    $folio=$myRow['folio'];
    $SQL ="UPDATE fixedasset_Resguardos 
            SET `observaciones`='".$observaciones."',fechaultimoresguardo=curdate(), ln_ubicacion='".$ln_ubicacion."'  
            WHERE folio=".$folio.";";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    if($TransResult){
        for ($i=0; $i < $_POST['numPartidasObs']; $i++) {
            $SQL="UPDATE `fixedasset_detalle_resguardos` 
                    SET `observaciones` ='".$_POST['observaciones'.$i]."' ,
                        `kmInicial` = '".$_POST['txtKMInicial'.$i]."', 
                        `kmFinal` = '".$_POST['txtKMFinal'.$i]."'
                    WHERE id='".$_POST['idPartida'.$i]."'";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        }

        $indexObs=($_POST['numPartidasObs']);
        for ($i=0; $i <= $_POST['numPartidasObs']; $i++) { 
            //echo "<br>".'parNuevo'.$i;
            //echo "nu : ".$_POST['parNuevo'.$i];
            if($_POST['parNuevo'.$i] == '1'){
                $SQL = "INSERT INTO `fixedasset_detalle_resguardos` (`folio`, `assetid`, `estatus`, `fecha`, `ur`, `ue`, `observaciones`,`kmInicial`, `kmFinal`)
                    VALUES ('".$folio."', ".$_POST['selectActivoFijo'.($i +1)].", '1', curdate(), ".$ur.", ".$ue.",'".$_POST['observaciones'.$i]."','".$_POST['txtKMInicial'.$i]."','".$_POST['txtKMFinal'.$i]."');";
                //echo $SQL;
                $indexObs++;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                if(!$TransResult){
                    $Mensaje ="Problemas al guardar activos fijos";
                    break;
                }

                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                //!!      Modificamos Activos en estatus resguardo.    !!
                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                $SQL ="UPDATE fixedassets SET status ='9'  WHERE assetid=".$_POST['selectActivoFijo'.($i + 1)].";";
                //echo $SQL;
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                if(!$TransResult){
                    $Mensaje ="Problemas al modificar el estatus del activo";
                    break;
                } 

            }

        }

        $cadenaDatosInsertar ="";
        if(isset($_FILES['archivos'])){
            foreach ($_FILES['archivos']['tmp_name'] as $key => $tmp_name) {

                $name_visible = $_FILES['archivos']['name'][$key];
                $file_name = $key.date('YmdHis').$_FILES['archivos']['name'][$key] ;
                $name_visible=str_replace(" ", "", $name_visible);
                $file_name=str_replace(" ", "", $file_name);
                $file_type=$_FILES['archivos']['type'][$key];

                $cadenaDatosInsertar .= "('".$_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."',1002,'".$name_visible."','".$folio."','".$_POST['observacionFile'.$key]."','1'),";

                moverArchivo($file_name,$_FILES['archivos']['tmp_name'][$key],SUBIDAARCHIVOS);
            }

            $cadenaDatosInsertar = substr($cadenaDatosInsertar, 0, -1);

            if($cadenaDatosInsertar !=""){
                $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`nu_tipo_sys`,`ln_nombre`, `nu_trasnno`, `txt_descripcion`,ind_active) VALUES ".$cadenaDatosInsertar;

                $ErrMsg = "Problemas al guardar el archivo.";
                $result = DB_query($SQL, $db, $ErrMsg);
            }
        } 

        for ($i=0; $i < $_POST['numObservacionesOldFile']; $i++) { 
            $SQL="UPDATE tb_archivos 
                    SET `txt_descripcion` = '".$_POST['observacionOldFile'.$i]."'
                    WHERE nu_id_documento = ".$_POST['OldIdFile'.$i];

            $ErrMsg = "Problemas al modificar observaciones del archivo.";
            $result = DB_query($SQL, $db, $ErrMsg);
        }

    }

    if($TransResult){
        $url = "&Folio=>" . $folio;
        $url = $enc->encode($url);
        $liga_folio= "URL=". $url;
        $contenido[] = array(
            'urlFolio' => 'resguardo_detalles.php?'.$liga_folio
        );
        $Mensaje ="Se modificó correctamente el resguardo con folio: ".$folio.", para el empleado : " . $myRow['empleado'];
    }else{
        $Mensaje ="Problemas al modificar el resguardo con folio: ".$folio."";
    }

}


if($option == "bajaActivoFijo"){

    $folioOld = $_POST['folio'];
    $observaciones= "";
    $TransResult=true;


    // Se manda a guardar antes de entrar aqui

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!! Registramos el siguiente reguardo estatus activo. !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // $folioNew = GetNextTransNo('1002',$db);
    //     $SQL = "INSERT INTO `fixedasset_Resguardos` (`userid`,`folio`, `fecha`, `estatus`, `fechaultimoresguardo`, `ur`, `ue`,`observaciones`)
    //             SELECT `userid`,'".$folioNew."',curdate(), `estatus`, curdate(), `ur`, `ue`,'".$observaciones."' FROM fixedasset_Resguardos WHERE folio =".$folioOld.";";
    // //echo $SQL."<br>";
    // $TransResult = DB_query($SQL, $db, $ErrMsg);

    // if($TransResult){
    //     $SQL ="UPDATE fixedasset_Resguardos SET `estatus`='0', fechaultimoresguardo=curdate() WHERE folio=".$folioOld.";";
    //     //echo $SQL."<br>";
    //     $TransResult = DB_query($SQL, $db, $ErrMsg);
    // }


    for ($i=0; $i < $_POST['numActivosBaja']; $i++) {

        $SQLActivo ="UPDATE fixedassets SET status ='1'  WHERE assetid in (select assetid from fixedasset_detalle_resguardos where id = ".$_POST['chkSelectActivo'.$i].");";

        $TransResultActivo = DB_query($SQLActivo, $db, $ErrMsg);
        $estatusBaja="0";
        //echo "sql:".$SQLActivo;
        if(!$TransResultActivo){
            $Mensaje ="Problemas al modificar el estatus del activo";
            break;
        } else{
            $SQLDetalle ="UPDATE fixedasset_detalle_resguardos 
                            SET estatus ='0',
                                fecha_desincorporacion  = curdate() ,
                                `observaciones` = '".$_POST['txtObservacionesBaja'.$i]."'
                            WHERE id=".$_POST['chkSelectActivo'.$i].";";   

            $TransResultActivo = DB_query($SQLDetalle, $db, $ErrMsg);
        }
    }

    if($TransResultActivo){

        $url = "&Folio=>" . $folioOld;
        $url = $enc->encode($url);
        $liga_folio= "URL=". $url;
        $contenido[] = array(
            'urlFolio' => 'resguardo_detalles.php?'.$liga_folio
        );
        $Mensaje ="Se modificó correctamente el resguardo con folio: ".$folioOld."";
    }else{
        $Mensaje ="Problemas al modificar el resguardo con folio: ".$folioOld."";
    }

}

if($option == 'obtenerArchivos'){

    $funcion=$_POST['funcion'];
    $type=$_POST['type_mov'];
    $transno=$_POST['transno_mov'];

    $SQL = "SELECT nu_id_documento,txt_url,ln_nombre,ind_permiso_active ,txt_descripcion
            FROM tb_archivos 
            WHERE  nu_tipo_sys = '".$type."' AND nu_trasnno = '".$transno."' and ind_active = 1;";

    $ErrMsg = "No se obtuvieron el detalle de los archivos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

        $infoDetalleArchivos[] = array(
            'idFile' => $myrow ['nu_id_documento'],
            'urlFile' => $myrow ['txt_url'],
            'nameFile' => $myrow ['ln_nombre'],
            'txt_descripcion' => $myrow ['txt_descripcion'],
            'idTipo' => $myrow ['ind_permiso_active']
        );
    }
    
    $contenido = array('datos' => $infoDetalleArchivos);
    $result = true;
}

if($option == 'verificarEmpleado'){

    $empleado=$_POST['empleado'];

    $SQL = "SELECT ind_activo FROM tb_empleados WHERE id_nu_empleado = '".$empleado."';";

    $ErrMsg = "No se obtuvieron validacion usuario";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

        $info[] = array(
            'blnActivo' => $myrow ['ind_activo']
        );
    }
    
    $contenido = array('datos' => $info);
    $result = true;
}


//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!                                               !!
//!!         Mover archivo al servidor.            !!
//!!                                               !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

function moverArchivo($docName, $docTemp, $ubicacion)
{
    # comprobación y creación de la carpeta de ser necesario
    if(!file_exists($ubicacion)){ crearCarpeta($ubicacion); }
    $name = $ubicacion . $docName;
    # comprobación de archivo subido
    if(is_uploaded_file($docTemp)){
        # cambio de ubicación del archivo
        $conf = move_uploaded_file($docTemp, $name);
        @chown($name, 'root');
        @chgrp($name, 'root');
        return $conf;
    }
    return false;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'ErrMsg' => $ErrMsg, 'DbgMsg' => $DbgMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>