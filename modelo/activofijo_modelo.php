<?php

/**
 activo fijo modelo
 *
 * @category ABC
 * @package ap_grp
 * @author Jorge Cesar Garcia Baltazar <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 21/08/2017
 * Fecha Modificación: 21/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */



// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');


//error_reporting(E_ALL ^ E_NOTICE);

session_start();
$PageSecurity = 1;
$PathPrefix = '../';
$InputError = 0;
$exito = 1;
//include($PathPrefix.'includes/session.inc');
header('Content-type: text/html; charset=ISO-8859-1');

include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
//
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2308;
include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix.'includes/DateFunctions.inc');


$ErrMsg = _('');
$contenido = array();
$Mensaje = "";
$columnasNombres="";
$columnasNombresGrid = "";
$_POST['DepnType']  = 0;


header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";

$TransResult = DB_query($SQL, $db);


if (isset($_POST['cargarinicio'])) {
    $infotipoactivos = array();
    $infoprocesoscontabilizar = array();
    $SQL = "SELECT idtype, description FROM fixedAssetTypes ";
    $ErrMsg = "No se obtuvieron tipos de activos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infotipoactivos[] = array( 'idtype' => $myrow ['idtype'],
                'description' => $myrow ['description']);
    }

    $infocategoriaactivos = array();
    $infocatalogoactivos = array();

    $infocatalogoactivos[] = array( 'clave' => '',
                'descripcion' => '');
    $SQL = "select concat(b.cabm, '-', b.familia) clave, concat(b.cabm, '-', b.familia,'-', b.descripcion) descripcion  from fixedassetcategories  a join fixedassetmaster b on a.categoryid = b.categoryid";
    $ErrMsg = "No se obtuvieron tipos de activos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infocatalogoactivos[] = array( 'clave' => $myrow ['clave'],
                'descripcion' => $myrow ['descripcion']);
    }


    $SQL = "SELECT DISTINCT matrizid, proceso from fixedassetmatrizconversion";
    $ErrMsg = "No se obtuvieron tipos de activos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $idrow = 0;
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoprocesoscontabilizar[] = array('value' => $myrow['matrizid'],
            'texto' => $myrow['proceso']);
    }


    

    $infoactivoslocations = array();
    $SQL = "SELECT locationid, locationdescription FROM fixedassetlocations";
    $ErrMsg = "No se obtuvieron las localidades de activos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoactivoslocations[] = array( 'locationid' => $myrow ['locationid'],
                'locationdescription' => $myrow ['locationdescription']);
    }

    $sql = "";


    $selectOrigen = array();
    $selectOrigen[] = array( 'origenid' => 1, 'origentype' => 'Propio');
    $selectOrigen[] = array( 'origenid' => 2, 'origentype' => 'Arrendado');
    $selectOrigen[] = array( 'origenid' => 3, 'origentype' => 'Remesa');
    $selectOrigen[] = array( 'origenid' => 4, 'origentype' => 'Donacion');
    $selectOrigen[] = array( 'origenid' => 4, 'origentype' => 'Convenio');

    $selectTipoDepreciacion = array();
    $selectTipoDepreciacion[] = array( 'tipoid' => 1, 'depreciacion' => 'Valor Decreciente');
    $selectTipoDepreciacion[] = array( 'tipoid' => 0, 'depreciacion' => 'Linea Recta');

    $usuario = $_SESSION['UserID'];
    
    
    
    $selectUnidaddenegocio = array();
    $SQL = "SELECT tagref, tagdescription FROM tags WHERE tagref in (select tagref from sec_unegsxuser where userid='$usuario')";
    $ErrMsg = "No se obtuvieron las unidades de negocio";

    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $selectUnidaddenegocio[] = array( 'tagref' => $myrow ['tagref'],
                'tagdescription' => $myrow ['tagdescription']);
    }
    
    


    $contenido = array('tipoActivos' => $infotipoactivos, 'infocatalogoactivos' => $infocatalogoactivos,
        'activoslocations' => $infoactivoslocations, 'selectOrigen' => $selectOrigen,
        'selectTipoDepreciacion' => $selectTipoDepreciacion,
        'selectUnidaddenegocio' => $selectUnidaddenegocio,
        'infoprocesoscontabilizar' => $infoprocesoscontabilizar
    );
}

function fnValidarContabilizar($db, $proceso, $categoriaActivo) {
    // $SQL = "SELECT b.cargo, b.abono 
    //         FROM fixedassetcategories a 
    //         JOIN fixedassetmatrizconversion b on (a.costact = b.abono or a.costact = b.cargo) and proceso = '$proceso' 
    //         WHERE categoryid = '$categoriaActivo'";
    $SQL = "SELECT b.cargo, b.abono 
            FROM fixedassetmatrizconversion b  
            WHERE matrizid = '$proceso'";

    $ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
    $DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

    while ($myrow = DB_fetch_array($result)) {
        $cuentacargo = $myrow ['cargo'];
        $cuentaabono = $myrow ['abono'];
    }

    return $cuentaabono;
}

function fnNuevoBarCode($db, $assetid){
	$SQL = "SELECT * FROM fixedassets WHERE assetid = '$assetid'";

	$ErrMsg = _('No se localizó el código buscado porque');
	$DbgMsg = _('El SQL falló en devolver valores');
	$result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

	$myrow = DB_fetch_array($result);

	if(is_array($myrow)){
		return $myrow['cabm'].str_replace("-", "", $myrow['datepurchased']).str_pad($myrow['assetid'], 9, '0', STR_PAD_LEFT);
	}

	return false;
}

function fnContabilizar($db, $tagrefowner, $costo, $proceso, $categoriaActivo, $numeroGenerado,$ue="") {

    $SQL = "SELECT b.cargo, b.abono 
            FROM fixedassetmatrizconversion b  
            WHERE matrizid = '$proceso'";

    $ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
    $DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

    while ($myrow = DB_fetch_array($result)) {
        $cuentacargo = $myrow ['cargo'];
        $cuentaabono = $myrow ['abono'];
    }

    $PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db, $tagrefowner);
    $TransNo = GetNextTransNo(42, $db); /* transaction type is asset category change */
    $folioUEPoliza = fnObtenerFolioUeGeneral($db, $ur,$ue, 291);

    //credit cost for the old category
    $SQL = "INSERT INTO gltrans (type,
                                typeno,
                                trandate,
                                periodno,
                                account,
                                narrative,
                                amount,
                                userid,
                                rate,
                                stockid,
                                qty,
                                lastdatemod,
                                tag,
                                posted,
                                ln_ue,
                                nu_folio_ue)
                VALUES ('42',
                    '" . $TransNo . "',
                    '" . Date('Y-m-d') . "',
                    '" . $PeriodNo . "',
                    '" . $cuentaabono . "',
                    '" . $numeroGenerado . ' ' . _('Ingreso manual al activo') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'] . "',
                    '" . ($costo * -1). "',
                    '" . $_SESSION ['UserID']. "',
                    1,
                    '" . $numeroGenerado. "',
                    1,
                    '" . Date('Y-m-d') . "',
                    '" . $tagrefowner . "',
                    1,
                    '".$ue."',
                    '".$folioUEPoliza."'
                )";
    $ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
    $DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

    //echo $SQL;

    //debit cost for the new category
    $SQL = "INSERT INTO gltrans (type,
                                typeno,
                                trandate,
                                periodno,
                                account,
                                narrative,
                                amount,
                                userid,
                                rate,
                                stockid,
                                qty,
                                lastdatemod,
                                tag,
                                posted,
                                ln_ue,
                                nu_folio_ue)
                VALUES ('42',
                    '" . $TransNo . "',
                    '" . Date('Y-m-d') . "',
                    '" . $PeriodNo . "',
                    '" . $cuentacargo . "',
                    '" . $numeroGenerado . ' ' . _('ingreso manual al activo') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'] . "',
                    '" . $costo. "',
                    '" . $_SESSION ['UserID']. "',
                    1,
                    '" . $numeroGenerado. "',
                    1,
                    '" . Date('Y-m-d') . "',
                    '" . $tagrefowner . "',".
                    "1,
                    '".$ue."',
                    '".$folioUEPoliza."'
                )";
    $ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
    $DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

    //echo $SQL;
}

if (isset($_POST['aAssetId'])) {
    $sql = "SELECT eco, 
                assetid,
                description,
                longdescription,
                assetcategoryid,
                serialno,
                assetlocation,
                DATE_FORMAT(datepurchased, '%d-%m-%Y') as datepurchased,
                DATE_FORMAT(fechaIncorporacionPatrimonial, '%d-%m-%Y') as fechaIncorporacionPatrimonial,
                depntype,
                depnrate,
                cost,
                accumdepn,
                barcode,
                disposalproceeds,
                disposaldate,
                endcalibrationdate,
                status,
                calibrationdate,
                lastmaintenancedate,
                ownertype,
                tagrefowner,
                ue,
                loccode,
                active,
                model,
                size,
                coalesce(certificate,'fixedAssets/certificates/') as certificate,
                cabm, 
                legalid, 
                factura, 
                marca,
                contabilizado,
                clavebien,
                proveedor, 
                tipo_bien, 
                placas, 
                color,
                observaciones,
                asegurado,
                anio
            FROM fixedassets
            WHERE assetid ='" . $_POST['aAssetId'] . "'";
            
    $result = DB_query($sql, $db);
    $AssetRow = DB_fetch_array($result);

    $contenido = array(
        'eco' => $AssetRow['eco'], 
        'LongDescription' => $AssetRow['longdescription'],
        'Description' => $AssetRow['description'], 
        'AssetCategoryID' => $AssetRow['assetcategoryid'],
        'SerialNo' => $AssetRow['serialno'], 
        'AssetLocation' => $AssetRow['assetlocation'],
        'DepnType' => $AssetRow['depntype'], 
        'BarCode' => $AssetRow['barcode'],
        'DepnRate' => $AssetRow['depnrate'], 
        'endcalibrationdate' => $AssetRow['endcalibrationdate'],
        'FixedAssetStatus' => $AssetRow['status'], 
        'calibrationdate' => $AssetRow['calibrationdate'],
        'lastmaintenancedate' => $AssetRow['lastmaintenancedate'], 
        'FixedAssetOwnerType' => $AssetRow['ownertype'], 
        'tagrefowner' => $AssetRow['tagrefowner'],
        'loccode' => $AssetRow['loccode'],
        'ue' => $AssetRow['ue'],
        'model' => $AssetRow['model'], 
        'active' => $AssetRow['active'], 
        'size' => $AssetRow['size'], 
        'cabm' => $AssetRow['cabm'],
        'legalid' => $AssetRow['legalid'], 
        'depnrate' => $AssetRow['depnrate'],
        'costo' => $AssetRow['cost'], 
        'datepurchased' => $AssetRow['datepurchased'],
        'fechaIncorporacionPatrimonial' => $AssetRow['fechaIncorporacionPatrimonial'],
        'NumeroFactura' => $AssetRow['factura'], 
        'Marca' => $AssetRow['marca'],
        'contabilizado' => $AssetRow['contabilizado'],
        'clavebien' => $AssetRow['clavebien'],
        'proveedor' => $AssetRow['proveedor'],
        'tipo_bien' => $AssetRow['tipo_bien'],
        'placas' => $AssetRow['placas'],
        'color_bien' => $AssetRow['color'],
        'observaciones' => $AssetRow['observaciones'],
        'asegurado' => $AssetRow['asegurado'],
        'anio' => $AssetRow['anio']
    );
}

function traerConsecutivoActivo($db, $cabm, $anio)
{
    return "08"."".$cabm."".$anio."".str_pad(GetNextTransNo(280, $db), 8, '0', STR_PAD_LEFT); //traer folio del activo fijo;
}

if (isset($_POST['new_edit'])) {
    //initialise no input errors assumed initially before we test

    /* actions to take once the user has clicked the submit button
    ie the page has called itself with some user input */

    //first off validate inputs sensible
    $i=1;


    if (!isset($_POST['Description']) or strlen($_POST['Description']) > 50 or strlen($_POST['Description'])==0) {
        $InputError = 1;
        prnMsg(_('The asset description must be entered and be fifty characters or less long. It cannot be a zero length string either, a description is required'), 'error');
        $Errors[$i] = 'Description';
        $i++;
    }
    if (strlen($_POST['LongDescription'])==0) {
        $InputError = 1;
        prnMsg(_('The asset long description cannot be a zero length string, a long description is required'), 'error');
        $Errors[$i] = 'LongDescription';
        $i++;
    }

    $InputError = 0;

    if (!is_numeric($_POST['DepnRate'])
        /*or filter_number_format($_POST['DepnRate'])>100
        or filter_number_format($_POST['DepnRate'])<0*/) {
        $InputError = 1;
        prnMsg(_('The depreciation rate is expected to be a number between 0 and 100'), 'error');
        $Errors[$i] = 'DepnRate';
        $i++;
    }
    if (($_POST['DepnRate']>0) and ($_POST['DepnRate']<1)) {
        prnMsg(_('Numbers less than 1 are interpreted as less than 1%. The depreciation rate should be entered as a number between 0 and 100'), 'warn');
    }


    if ($InputError !=1 && $_POST['proceso'] != 'borrar') {
        if ($_POST['proceso'] == _('Actualizar')) { /*so its an existing one */
           
            $AssetID = $_POST['ActivoFijoID'];

            /*Start a transaction to do the whole lot inside */
            $result = DB_Txn_Begin($db);

            /*Need to check if changing the balance sheet codes - as will need to do journals for the cost and accum depn of the asset to the new category */

            $result = DB_query("SELECT *
                                FROM fixedassets INNER JOIN fixedassetcategories
                                ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
                                WHERE assetid='" . $AssetID . "'", $db);

            $OldDetails = DB_fetch_array($result);
            
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            //!!        Si cambia la categoria genera otra poliza          !!
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

            if ($OldDetails['assetcategoryid'] !=$_POST['AssetCategoryID']  and $OldDetails['cost']!=0) {

                $PeriodNo = GetPeriod(date('d/m/Y'), $db);//GetPeriod(Date($_SESSION['DefaultDateFormat']));
                /* Get the new account codes for the new asset category */

                $result = DB_query("SELECT costact,
                                            accumdepnact
                                    FROM fixedassetcategories
                                    WHERE categoryid='" . $_POST['AssetCategoryID'] . "'", $db);
                $NewAccounts = DB_fetch_array($result);

                $TransNo = GetNextTransNo(42, $db); /* transaction type is asset category change */
            

                //credit cost for the old category
                $SQL = "INSERT INTO gltrans (type,
                                            typeno,
                                            trandate,
                                            periodno,
                                            account,
                                            narrative,
                                            amount)
                            VALUES ('42',
                                '" . $TransNo . "',
                                '" . Date('Y-m-d') . "',
                                '" . $PeriodNo . "',
                                '" . $OldDetails['costact'] . "',
                                '" . $AssetID . ' ' . _('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'] . "',
                                '" . ($OldDetails['cost'] * -1). "'
                                )";
                $ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
                $DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);

                //debit cost for the new category
                $SQL = "INSERT INTO gltrans (type,
                                            typeno,
                                            trandate,
                                            periodno,
                                            account,
                                            narrative,
                                            amount)
                            VALUES ('42',
                                '" . $TransNo . "',
                                '" . Date('Y-m-d') . "',
                                '" . $PeriodNo . "',
                                '" . $NewAccounts['costact'] . "',
                                '" . $AssetID . ' ' . _('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'] . "',
                                '" . $OldDetails['cost']. "'
                                )";
                $ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
                $DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);

                if ($OldDetails['accumdepn']!=0) {
                    //debit accumdepn for the old category
                    $SQL = "INSERT INTO gltrans (type,
                                                typeno,
                                                trandate,
                                                periodno,
                                                account,
                                                narrative,
                                                amount)
                                VALUES ('42',
                                    '" . $TransNo . "',
                                    '" . Date('Y-m-d') . "',
                                    '" . $PeriodNo . "',
                                    '" . $OldDetails['accumdepnact'] . "',
                                    '" . $AssetID . ' ' . _('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'] . "',
                                    '" . $OldDetails['accumdepn']. "'
                                    )";
                    $ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
                    $DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);

                } /*end if there was accumulated depreciation for the asset */
            } /* end if there is a change in asset category */

            if (isset($_POST['select_tagrefowner'])) {
                $tagrefowner=$_POST['select_tagrefowner'];

                // $SQL="SELECT loccode FROM locations WHERE tagref ='".$tagrefowner."'";
                // $result = DB_query($SQL, $db);
                // $loccode = DB_fetch_array($result);
                // $almacen=$loccode['loccode'];
                
            }

            $almacen=$_POST['almacen'];
                
            $sql = "UPDATE fixedassets
                    SET 
                        longdescription ='" . $_POST['LongDescription'] . "',
                        description ='" . $_POST['Description'] . "',
                        assetcategoryid ='" . $_POST['AssetCategoryID'] . "',
                        assetlocation ='" .  $_POST['AssetLocation'] . "',
                        depntype ='" . $_POST['DepnType'] . "',
                        depnrate ='" . $_POST['DepnRate'] . "',
                        serialno ='" . $_POST['SerialNo'] . "',
                        fixedassettype ='" . $_POST['FixedAssetType'] . "',
                        status ='" . $_POST['FixedAssetStatus'] . "',
                        ownertype = '" . $_POST['FixedAssetOwnerType'] . "',
                        legalid = '" . $_POST['select_legalbusiness'] . "',
                        tagrefowner = '" . $tagrefowner . "',
                        ue = '" . $_POST['ue'] . "',
                        loccode = '" . $almacen . "',
                        datepurchased = '" . ConvertSQLDate($_POST['datepurchased']) . "',
                        fechaIncorporacionPatrimonial = '" . ConvertSQLDate($_POST['FechaIncorporacionPatrimonial']) . "',
                        active = '" . $_POST['activo'] . "',
                        model = '" . $_POST['model'] . "',
                        marca = '" . $_POST['marca'] . "',
                        factura = '" . $_POST['factura'] . "',
                        cabm = '" . $_POST['FixedCABM'] . "',
                        cost = '" . $_POST['costo'] . "',
                        contabilizado = '".$_POST['procesoscontabilizar']."',
                        clavebien = '".$_POST['txtClaveBien']."',
                        BarCode = '".$_POST['BarCode']."',
                        proveedor = '".$_POST['proveedor']."',
                        tipo_bien = '".$_POST['tipoBien']."',
                        placas = '".$_POST['placas']."',
                        color = '".$_POST['color']."',
                        observaciones = '".$_POST['observacion']."',
                        asegurado = '".$_POST['asegurado']."',
                        anio = '".$_POST['anio']."'
                    WHERE assetid='" . $AssetID . "'";

            //echo "<pre>".$sql;
            //barcode ='" . $_POST['eco'] . "', // Se eliminó barcode porque no puede cambiarse una vez capturado

            $ErrMsg = _('The asset could not be updated because');
            $DbgMsg = _('The SQL that was used to update the asset and failed was');
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

            //echo $sql;

            //si seleccionaron archivo
            if (isset($_FILES["certificate"]["name"])) {
                $origen=$_FILES["certificate"]["tmp_name"];
                $destino=$carpetaDestino.$_FILES["certificate"]["name"];

                # movemos el archivo
                if (@move_uploaded_file($origen, $destino)) {
                    //
                    //
                    //insertar certificado
                    $sql = "INSERT INTO fixedAssetLog (
                                                idfixedactive,
                                                userid,
                                                value,
                                                fecha,
                                                logtype)
                            VALUES (
                                '" . $AssetID . "',
                                '" . $_SESSION ['UserID'] . "',
                                '" . $carpetaDestino.$_FILES["certificate"]["name"] . "',
                                DATE_ADD('" . $_POST['calibrationdate'] . "', INTERVAL 365 DAY),
                                '1'
                                )";
                    $ErrMsg =  _('The asset could not be added because');
                    $DbgMsg = _('The SQL that was used to add the asset failed was log');
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                                            
                                        debug_sql($sql, __LINE__, $debug_sql, __FILE__);
                                        
                    $sql = "UPDATE fixedassets
                            SET certificate ='" . $carpetaDestino.$_FILES["certificate"]["name"] . "'
                            WHERE assetid='" . $AssetID . "'";

                    $ErrMsg = _('The asset could not be updated because');
                    $DbgMsg = _('The SQL that was used to update the assetLog and failed was');
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

                    prnMsg(_('El certificado se ha subido correctamente:') . ' ' . $_FILES["certificate"]["name"], 'success');
                } else {
                    prnMsg(_('No se ha podido subir el certificado:') . ' ' . $_FILES["certificate"]["name"], 'warn');
                }
            }
            //
            if (isset($_FILES["factura"]["name"])) {
                $nombre_archivo = $_FILES['factura']['name'];
                $path_parts = pathinfo($nombre_archivo);
                $nombre = $path_parts ['filename'];
                $extension = $path_parts ['extension'];
                $Hora = date('H');
                $Minuto = date('i');
                $Segundo = date('s');
                $nombre=str_replace(' ', '_', $nombre);
                
                if ($path_parts ['extension'] != "") {
                    $extension = ".".$extension;
                }
                $namefile = $nombre . $Hora . $Minuto . $Segundo . $extension;

                $origen=$_FILES["factura"]["tmp_name"];
                $destino="fixedAssets/".$namefile;


                # movemos el archivo
                if (@move_uploaded_file($_FILES["factura"]["tmp_name"], $destino)) {
                    //insertar certificado
                    $sql = "INSERT INTO fixedassetsfile(assetid,namefile,directoryfile,datetimeup,userup) 
                                        VALUES (".$AssetID.",'".$namefile."','".$destino."',sysdate(),'".$_SESSION['UserID']."');";

                    $ErrMsg =  _('The asset could not be added because');
                    $DbgMsg = _('The SQL that was used to add the asset failed was file');
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    //move_uploaded_file($_FILES["factura"]["tmp_name"], $destino);
                                            
                                        debug_sql($sql, __LINE__, $debug_sql, __FILE__);
                    prnMsg(_('La Factura se ha subido correctamente:') . ' ' . $_FILES["factura"]["name"], 'success');
                } else {
                    prnMsg(_('No se ha podido subir la factura:') . ' ' . $namefile, 'warn');
                }
            }
                
            //si existe fecha de
            if ((isset($_POST['endcalibrationdate'])) && (trim($_POST['endcalibrationdate']) != '')) {
                //insertar fecha de vigencia
                $sql = "INSERT INTO fixedAssetLog (
                                            idfixedactive,
                                            userid,
                                            fecha,
                                            logtype)
                        VALUES (
                            '" . $AssetID . "',
                            '" . $_SESSION ['UserID'] . "',
                            '" . $_POST['endcalibrationdate'] . "',
                            '2'
                            )";
                $ErrMsg =  _('The asset could not be added because');
                $DbgMsg = _('The SQL that was used to add the asset failed was log');
                // $result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
                                //debug_sql($sql,__LINE__,$debug_sql,__FILE__);
            }

            $Mensaje =_('Se modifico correctamente el patrimonio con el Número de Inventairo:  <b>'.$_POST['BarCode'].'.</b>');
        } else { //it is a NEW part
            

            //Se le envia directamente el almacen
            // if (isset($_POST['select_tagrefowner'])) {
            //     $tagrefowner=$_POST['select_tagrefowner'];
            //     $SQL="SELECT loccode FROM locations WHERE tagref ='".$tagrefowner."'";
            //     $result = DB_query($SQL, $db);
            //     $loccode = DB_fetch_array($result);
            //     $almacen=$loccode['loccode'];
            // }
            $tagrefowner=$_POST['select_tagrefowner'];
            $almacen=$_POST['almacen'];

            $leyenda="";
            if ($_POST['FixedAssetOwnerType']==1) {
                $sql= "SELECT COUNT(*) FROM fixedassets WHERE  serialno ='".trim($_POST['SerialNo'])."' and ownertype='1'";
                $leyenda=" como propio.";
            } else {
                $sql= "SELECT COUNT(*) FROM fixedassets WHERE  serialno ='".trim($_POST['SerialNo'])."' and tagrefowner='".$tagrefowner."'";
                $leyenda=" como arrendado.";
            }

            $result = DB_query($sql, $db);
            $myrow = DB_fetch_row($result);

            if ($myrow[0]>0 && trim($_POST['SerialNo']) != '') {
                 $Mensaje = _('El activo fijo con numero de serie: '.$_POST['SerialNo'].' ya ha sido registrado'.$leyenda);
                 $info[] = array('numInventario' => '');
                 $contenido = array('datos' => $info);
            } else {
                if (empty($_POST['calibrationdate'])) {
                    $calibrationdate = 'NULL';
                } else {
                    $calibrationdate = "'".$_POST['calibrationdate']."'";
                }

                if (empty($_POST['lastmaintenancedate'])) {
                    $lastmaintenancedate = 'NULL';
                } else {
                    $lastmaintenancedate = $_POST['lastmaintenancedate'];
                }

                /* antes

                if (empty($_POST["FixedAssetStatus"])) {
                    $_POST["FixedAssetStatus"]= 8;
                }*/
                if (!isset($_GET['CallByPO']) && !isset($_POST['CallByPO'])) {
                    $_POST["FixedAssetStatus"]= 1;
                } else {
                    $_POST["FixedAssetStatus"]= 8;
                }

                $result = DB_query("SELECT costact, accumdepnact
                                    FROM fixedassetcategories
                                    WHERE categoryid= '" . $_POST['AssetCategoryID'] . "'", $db);

                $NewAccounts = DB_fetch_array($result);

                if ($_POST['FixedAssetOwnerType'] =="2") {
                    $_POST['DepnRate']=0;
                    $_POST['DepnType']=0;
                }

                $explodeCABM = explode('-', $_POST["FixedCABM"]);
                $explodedatepurchased = explode('-', $_POST["datepurchased"]);




                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                //!!                                               !!
                //!! Revisar para descomentar cuentas contables.   !!
                //!!                                               !!
                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


                $existeEnMatriz = fnValidarContabilizar($db, $_POST["procesoscontabilizar"], $_POST["AssetCategoryID"]);

                if ($existeEnMatriz == "") {
                    $Mensaje = " No existe en matriz de conversión el proceso '".$_POST["procesoscontabilizar"]."' para la clave del bien seleccionado";
                    $exito = 0; //cambia la bandera para mostrar datos que faltan antes de hacer la inserción
                    goto finaldelcodigo;
                }
                
                //$BarCode = traerConsecutivoActivo($db, $explodeCABM[0], $explodedatepurchased[2][2].$explodedatepurchased[2][3]);
                //
                $_POST['size'] = "";

                $sql = "INSERT INTO fixedassets (eco,
                                                description,
                                                longdescription,
                                                assetcategoryid,
                                                assetlocation,
                                                depntype,
                                                depnrate,
                                                barcode,
                                                serialno,
                                                fixedassettype, 
                                                endcalibrationdate,
                                                calibrationdate,
                                                lastmaintenancedate,
                                                ownertype,
                                                active,
                                                model, 
                                                marca, 
                                                factura,
                                                size,
                                                legalid,
                                                tagrefowner,
                                                ue,
                                                currentlocation, 
                                                loccode,
                                                cost, 
                                                status,
                                                datepurchased, 
                                                disposaldate,
                                                fechaIncorporacionPatrimonial,
                                                cabm,
                                                contabilizado,
                                                clavebien,
                                                proveedor, 
                                                tipo_bien, 
                                                placas, 
                                                color,
                                                observaciones,
                                                asegurado,anio
                                                )
                            VALUES (
                                '" . $_POST['eco'] . "',
                                '" . $_POST['Description'] . "',
                                '" . $_POST['LongDescription'] . "',
                                '" . $_POST['AssetCategoryID'] . "',
                                '" . $_POST['AssetLocation'] . "',
                                '" . $_POST['DepnType'] . "',
                                '" . $_POST['DepnRate']. "',
                                '" . $_POST['BarCode'] . "',
                                '" . $_POST['SerialNo'] . "',
                                '" . $_POST['FixedAssetType'] . "',
                                DATE_ADD('" . $_POST['calibrationdate'] . "', INTERVAL 365 DAY),
                                " . $calibrationdate . ",
                                '" . $lastmaintenancedate . "',
                                '" . $_POST['FixedAssetOwnerType'] . "',
                                '" . $_POST['activo'] . "',
                                '" . $_POST['model'] . "',
                                '" . $_POST['marca'] . "',
                                '" . $_POST['factura'] . "',
                                '" . $_POST['size'] . "',
                                '" . $_POST['select_legalbusiness'] . "',
                                '" . $tagrefowner . "',
                                '" . $_POST['ue'] . "',
                                '" . $tagrefowner . "',
                                '" . $almacen . "', 
                                '".$_POST["costo"]."', 
                                '".$_POST["FixedAssetStatus"]."',
                                '". ConvertSQLDate($_POST["datepurchased"])."', 
                                '".ConvertSQLDate($_POST['FechaIncorporacionPatrimonial']) . "', 
                                '".ConvertSQLDate($_POST['FechaIncorporacionPatrimonial']) . "', 
                                '".$_POST["FixedCABM"]."',
                                '".$_POST['procesoscontabilizar']."',
                                '".$_POST['txtClaveBien']."',
                                '".$_POST['proveedor']."',
                                '".$_POST['tipoBien']."',
                                '".$_POST['placas']."',
                                '".$_POST['color']."',
                                '".$_POST['observacion']."',
                                '".$_POST['asegurado']."',
                                '".$_POST['anio']."'
                                )";//

                                  

                $ErrMsg = _('The asset could not be added because');
                $DbgMsg = _('The SQL that was used to add the asset failed was fixedAssets ');
                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

                $NewAssetID = DB_Last_Insert_ID($db, 'fixedassets', 'assetid');
                
            	$NuevoBarCode = fnNuevoBarCode($db,$NewAssetID);
                $_POST["BarCode"]=$NuevoBarCode;
                $BarCode=$NuevoBarCode;

            	if(isset($NuevoBarCode)){
            		$NSQL = "UPDATE fixedassets SET barcode = '".$NuevoBarCode."' WHERE assetid =  '" . $NewAssetID . "'";

					$ErrMsg = _('No se actualió el Nuevo Código porque');
					$DbgMsg = _('El SQL tuvo algún fallo');
					$resultNSQL = DB_query($NSQL, $db, $ErrMsg, $DbgMsg);
				}
                

                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

                //Generar Poliza

                fnContabilizar($db, $tagrefowner, $_POST["costo"], $_POST["procesoscontabilizar"], $_POST["AssetCategoryID"], $BarCode,$_POST['ue']);


                //echo "ver sesion :".$_SESSION['CompanyRecord']['gllink_stock'];
                if ($_SESSION['CompanyRecord']['gllink_stock']==1) {
                    if (isset($_POST["txtManufactura"]) and $_POST["txtManufactura"]=="1") {
                        //$NewAssetID = DB_Last_Insert_ID($db, 'fixedassets', 'assetid');
                        $sqlLogFixedAsset = "UPDATE fixedAssetLogregistre SET assetid= $NewAssetID, userid = '".$_SESSION['UserID']."', fecha=curdate() WHERE wo=". $_POST['txtOrdenTrabajo'] ." and stockid = '".$_POST["BarCode"]."' and serialno='". $_POST['SerialNo'] ."'";
                        //echo "ok <pre> ".$sqlLogFixedAsset;
                        $result = DB_query($sqlLogFixedAsset, $db, $ErrMsg, $DbgMsg);

                        $PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db, $tagrefowner);
                        $tipodocto = 26;
                        $SQLInvoiceDate = Date('Y-m-d');
                        $Narrative = $_POST['txtOrdenTrabajo'] . " " . $_POST['BarCode'] . " - " . $_POST['Description'] . ' x 1 @ ' . number_format($_POST["costo"], 2);

                        $rate=1;

                        $ISQL = Insert_Gltrans(
                            $tipodocto,
                            $_POST["txtTransno"],
                            $SQLInvoiceDate,
                            $PeriodNo,
                            $NewAccounts['costact'],
                            $Narrative,
                            $tagrefowner,
                            $_SESSION['UserID'],
                            $rate,
                            '',
                            '',
                            $_POST['BarCode'],
                            1,
                            0,
                            $almacen,
                            $_POST["costo"],
                            "",
                            0,
                            $_POST["costo"],
                            $db,
                            '',
                            'ACTIVO FIJO',
                            $_POST['txtOrdenTrabajo']
                        );

                        $ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
                        $DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
                        $result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
                    }
                }

                if ($NewAssetID != '' and $NewAssetID != '0') {
                  //  echo 'adentro';
                    
                     $sql = "INSERT INTO fixedassettrans (
                                                            assetid,
                                                            transdate,
                                                            amount, 
                                                            transtype, 
                                                            transno, 
                                                            periodno, 
                                                            inputdate,
                                                            fixedassettranstype
                                                )
                            VALUES (
                                    '" . $NewAssetID . "',
                                    curdate(),
                                    '".$_POST["costo"]."', 
                                    0, 
                                    0, 
                                    0, 
                                    curdate(), 
                                    0 )";//

                                  
                    //echo $sql;
                    $ErrMsg = _('The asset could not be added because');
                    $DbgMsg = _('The SQL that was used to add the asset failed was trans');
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                

                    //si seleccionaron archivo
                    /*if ($_FILES["certificate"]["name"]) {
                        $origen=$_FILES["certificate"]["tmp_name"];
                        $destino=$carpetaDestino.$_FILES["certificate"]["name"];
     
                        # movemos el archivo
                        if (@move_uploaded_file($origen, $destino)) {
                            //insertar certificado
                            $sql = "INSERT INTO fixedAssetLog (
                                                        idfixedactive,
                                                        userid,
                                                        value,
                                                        fecha,
                                                        logtype)
                                    VALUES (
                                        '" . $NewAssetID . "',
                                        '" . $_SESSION ['UserID'] . "',
                                        '" . $carpetaDestino.$_FILES["certificate"]["name"] . "',
                                        DATE_ADD('" . $_POST['calibrationdate'] . "', INTERVAL 365 DAY),
                                        '1'
                                        )";
                            $ErrMsg =  _('The asset could not be added because');
                            $DbgMsg = _('The SQL that was used to add the asset failed was');
                                                        debug_sql($sql, __LINE__, $debug_sql, __FILE__);
                            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                            prnMsg(_('El certificado se ha subido correctamente:') . ' ' . $_FILES["certificate"]["name"], 'success');

                            $sql = "UPDATE fixedassets
                            SET certificate ='" . $carpetaDestino.$_FILES["certificate"]["name"] . "'
                            WHERE assetid='" . $NewAssetID . "'";

                            $ErrMsg = _('The asset could not be updated because');
                            $DbgMsg = _('The SQL that was used to update the asset and failed was');
                            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                        } else {
                            prnMsg(_('No se ha podido subir el certificado:') . ' ' . $_FILES["certificate"]["name"], 'warn');
                        }
                    }*/
                        
                    /*//si existe fecha de vigencia
                    if (isset($_POST['endcalibrationdate']) || trim($_POST['endcalibrationdate']) != '') {
                        //insertar fecha de vigencia
                        $sql = "INSERT INTO fixedAssetLog (
                                                    idfixedactive,
                                                    userid,
                                                    fecha,
                                                    logtype)
                                VALUES (
                                    '" . $NewAssetID . "',
                                    '" . $_SESSION ['UserID'] . "',
                                    '" . $_POST['endcalibrationdate'] . "',
                                    '2'
                                    )";
                        $ErrMsg =  _('The asset could not be added because');
                        $DbgMsg = _('The SQL that was used to add the asset failed was');
                                                debug_sql($sql, __LINE__, $debug_sql, __FILE__);
                        //$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
                    }*/
                    
                    $Mensaje = _('Se agrego correctamente el nuevo patrimonio con Número de Inventario: <b>'. $BarCode .'</b>');
                    $info[] = array('numInventario' => $BarCode);
                    $contenido = array('datos' => $info);

                    //echo $sql;
                    unset($_POST['Description']);
                    unset($_POST['LongDescription']);
                    unset($_POST['BarCode']);
                    unset($_POST['AssetCategoryID']);
                    unset($_POST['DepnType']);
                    unset($_POST['DepnRate']);
                    unset($_POST['BarCode']);
                    unset($_POST['SerialNo']);
                    unset($_POST['FixedAssetType']);
                    unset($_POST['model']);
                    unset($_POST['size']);
                }
            }//ALL WORKED SO RESET THE FORM VARIABLES
            $result = DB_Txn_Commit($db);
            
            if (isset($_POST['CallByPO'])) {
                echo "<script>";

                if (isset($_POST["txtManufactura"]) && !empty($_POST["txtManufactura"])) {
                    echo "window.opener.document.getElementById('NewFixedAsset').style.display= 'none';";
                }
                
                echo "alert('Se dio de alta el activo de manera correcta !!!');";
                echo "window.close();";
                echo "</script>";
            }
        }
    } else {
        $ErrMsg = _('Problemas de validación, no se puede actualizar o eliminar activo');
    }

    $Result = DB_Txn_Commit($db);
}



if (isset($_POST['proceso']) and $_POST['proceso'] == "borrar") {
    $AssetID = $_POST['ActivoFijoID'];
    //the button to delete a selected record was clicked instead of the submit button

    $CancelDelete = 0;
    //what validation is required before allowing deletion of assets ....  maybe there should be no deletion option?
    $result = DB_query("SELECT cost,
                                accumdepn,
                                accumdepnact,
                                costact
                        FROM fixedassets INNER JOIN fixedassetcategories
                        ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
                        WHERE assetid='" . $AssetID . "'", $db);

    $AssetRow = DB_fetch_array($result);

    
    $NBV = $AssetRow['cost'] -$AssetRow['accumdepn'];

    
    if ($NBV!=0) {
        $CancelDelete =1; //cannot delete assets where NBV is not 0
        $ErrMsg = _('The asset still has a net book value - only assets with a zero net book value can be deleted');
    }
    $result = DB_query("SELECT * FROM fixedassettrans WHERE assetid='" . $AssetID . "'", $db);
    if (DB_num_rows($result) > 0) {
        $CancelDelete =1; /*cannot delete assets with transactions */
        $ErrMsg =_('El activo tiene transacciones asociadas a él. El activo solo puede ser eliminado cuando las transacciones del activo han sido purgadas, de otra forma la integridad de los reportes del activo fijo pudieran estar comprometidas');
    }
    /*$result = DB_query("SELECT * FROM purchorderdetails WHERE assetid='" . $AssetID . "'", $db);
    if (DB_num_rows($result) > 0) {
        $CancelDelete =1; //cannot delete assets where there is a purchase order set up for it 
        prnMsg(_('There is a purchase order set up for this asset. The purchase order line must be deleted first'), 'error');
    }*/
    
    if ($CancelDelete==0) {
        $result = DB_Txn_Begin($db);

        /*Need to remove cost and accumulate depreciation from cost and accumdepn accounts */
        $PeriodNo = GetPeriod(date('d/m/Y'), $db);
        $TransNo = GetNextTransNo(43, $db); /* transaction type is asset deletion - (and remove cost/acc5umdepn from GL) */
        if ($AssetRow['cost'] > 0) {
            //credit cost for the asset deleted
            $SQL = "INSERT INTO gltrans (type,
                                        typeno,
                                        trandate,
                                        periodno,
                                        account,
                                        narrative,
                                        amount)
                        VALUES ('43',
                            '" . $TransNo . "',
                            '" . Date('Y-m-d') . "',
                            '" . $PeriodNo . "',
                            '" . $AssetRow['costact'] . "',
                            '" . _('Delete asset') . ' ' . $AssetID . "',
                            '" . -$AssetRow['cost']. "'
                            )";
            $ErrMsg = _('Cannot insert a GL entry for the deletion of the asset because');
            $DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');

            //echo $SQL;
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);

            //debit accumdepn for the depreciation removed on deletion of this asset
            $SQL = "INSERT INTO gltrans (type,
                                        typeno,
                                        trandate,
                                        periodno,
                                        account,
                                        narrative,
                                        amount)
                        VALUES ('43',
                            '" . $TransNo . "',
                            '" . Date('Y-m-d') . "',
                            '" . $PeriodNo . "',
                            '" . $AssetRow['accumdepnact'] . "',
                            '" . _('Delete asset') . ' ' . $AssetID . "',
                            '" . $Asset['accumdepn']. "'
                            )";
            $ErrMsg = _('Cannot insert a GL entry for the reversal of accumulated depreciation on deletion of the asset because');
            $DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
        } //end if cost > 0

        $sql="UPDATE fixedassets set active = 0 WHERE assetid ='" . $AssetID . "'";
        $result=DB_query($sql, $db, _('No se puede eliminar el registro'), '', true);

        $result = DB_Txn_Commit($db);

    
        /*$sql="SELECT barcode FROM fixedassets WHERE assetid ='" . $AssetID . "'";
        $result = DB_query($sql,$db);
        $myrow = DB_fetch_row($result);
        $stockID = $myrow[0];

        $sql="DELETE FROM stockmaster WHERE stockid ='" . $stockID . "'";
        $result=DB_query($sql,$db, _('No se puede eliminar el registro'),'',true);

        $sql="DELETE FROM stockserialitems WHERE stockid ='" . $stockID . "'";
        $result=DB_query($sql,$db, _('No se puede eliminar el registro'),'',true);*/


        $Mensaje = _('El activo fue eliminado exitosamente');
        
        unset($_POST['LongDescription']);
        unset($_POST['Description']);
        unset($_POST['AssetCategoryID']);
        unset($_POST['AssetLocation']);
        unset($_POST['DepnType']);
        unset($_POST['DepnRate']);
        unset($_POST['BarCode']);
        unset($_POST['SerialNo']);
        unset($AssetID);
        unset($_SESSION['SelectedAsset']);
    } //end if OK Delete Asset
    $result = DB_Txn_Commit($db);
} /* end if delete asset */


if (isset($_POST['cargarDepreciacionDefault'])) {
    
    $depreciaciondefault = array();

    $SQL = "SELECT truncate(coalesce(defaultdepnrate,0) * 100,2) AS tasadepreciacion, a.categoryid, a.aniosdepreciacion, costact 
            FROM fixedassetcategories a 
            WHERE a.categoryid IN (SELECT concat(ccap,ccon,cparg) 
                                    FROM  tb_cat_partidaspresupuestales_partidaespecifica p_especificas 
                                    WHERE p_especificas.partidacalculada = '{$_POST['aCategoriaSeleccionada']}');";

    //echo $SQL;
    $ErrMsg = "No se obtuvo datos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $depreciaciondefault[] = array( 'texto' => $myrow ['tasadepreciacion'], 'categoryid' => $myrow ['categoryid'], 'aniosdepreciacion' => $myrow['aniosdepreciacion'], 'cuentacontable' =>  $myrow['costact']);
    }
    
    $contenido = $depreciaciondefault;
}

//============== Cargar de procesos en ABC Activo fijo ==============================//
//========================= activofijo.js ==============================//

if (isset($_POST['option']) and $_POST['option'] == 'procesos') {

    $SQL = "SELECT DISTINCT matrizid, proceso from fixedassetmatrizconversion";
    $ErrMsg = "No se obtuvieron tipos de activos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $idrow = 0;
    while ($myrow = DB_fetch_array($TransResult)) {
        $contenido[] = array('value' => $myrow['matrizid'],
            'texto' => $myrow['proceso']);
    }

}



//============== Cargar Datos Panel Patrimonio ==============================//
//=================== activofijo_panel.js ==============================//

if (isset($_POST['option']) and $_POST['option'] == 'cargarinicio_panel') {
    $infolistadeactivos = array();

    // separar la seleccion multiple de las unidades responsables
    $unidadres= $_POST["selectUnidadNegocio"];
    $datosUR = "";
    foreach ($unidadres as $key) {
        if($key != "-1"){
            if (empty($datosUR)) {
                $datosUR .= "'".$key."'";
            } else {
                $datosUR .= ", '".$key."'";
            }
        }
    }

    // separar la seleccion multiple de las unidades ejecutora
    $unidadue= $_POST["selectUnidadEjecutora"];
    $datosUE = "";
    foreach ($unidadue as $key) {
        if($key != "-1"){
            if (empty($datosUE)) {
                $datosUE .= "'".$key."'";
            } else {
                $datosUE .= ", '".$key."'";
            }
        }
        
    }

    // separar la seleccion multiple de los Patrimonio
    $patrimonios= $_POST["selectPatrimonio"];
    $datosPatrimonios = "";
    foreach ($patrimonios as $key) {
        if($key != "-1"){
            if (empty($datosPatrimonios)) {
                $datosPatrimonios .= "'".$key."'";
            } else {
                $datosPatrimonios .= ", '".$key."'";
            }
        }
    }

    // separar la seleccion multiple de los Patrimonio
    $categoriasPatrimonio= $_POST["selectCategoriaActivo"];
    $datosCategorias = "";
    foreach ($categoriasPatrimonio as $key) {
        if($key != "-1"){
            if (empty($datosCategorias)) {
                $datosCategorias .= "'".$key."'";
            } else {
                $datosCategorias .= ", '".$key."'";
            }
        }
    }

    // separar la seleccion multiple de los Patrimonio
    $estatusActivo= $_POST["selectEstatusActivo"];
    $datosActive = "";
    foreach ($estatusActivo as $key) {
        if($key != "-1"){
            if (empty($datosActive)) {
                $datosActive .= "'".$key."'";
            } else {
                $datosActive .= ", '".$key."'";
            }
        }
    }

    // separar la seleccion multiple del tipo de bien 
    $selecttipoBien= $_POST["selectTipoBien"];
    $datosTipoBien = "";
    foreach ($selecttipoBien as $key) {
        if($key != "-1"){
            if (empty($datosTipoBien)) {
                $datosTipoBien .= "'".$key."'";
            } else {
                $datosTipoBien .= ", '".$key."'";
            }
        }
    }

    // separar la seleccion multiple del tipo de bien 
    $selectAlmacen= $_POST["selectAlmacen"];
    $datosAlmacen = "";
    foreach ($selectAlmacen as $key) {
        if($key != "-1"){
            if (empty($datosAlmacen)) {
                $datosAlmacen .= "'".$key."'";
            } else {
                $datosAlmacen .= ", '".$key."'";
            }
        }
    }
    

    
    $SQL = "SELECT tagrefowner as ur,
                    a.ue,
                    assetid,
                    barcode,
                    assetcategoryid,
                    a.description,
                    a.contabilizado,
                    a.assetcategoryid as partida_especifica,
                    a.assetlocation,
                    a.loccode,
                    a.active,
                    a.tipo_bien,
                    a.placas,
                    a.color,
                    a.observaciones,
                    a.asegurado,
                    a.clavebien,
                    a.proveedor,
                    fstatus.fixedassetstatus,
                    DATE_FORMAT(a.datepurchased,'%d/%m/%Y') as fecha_adquisicion,
                    fatb.description as desc_tipobien,
                    locations.locationname
            FROM fixedassets a

            -- JOIN tb_cat_partidaspresupuestales_partidaespecifica p_especificas on a.assetcategoryid = p_especificas.partidacalculada 
            LEFT JOIN fixedassetstatus fstatus ON a.status = fstatus.fixedassetstatusid
            LEFT JOIN fixedAssetCategoryBien fatb ON a.tipo_bien = fatb.id
            LEFT JOIN locations on a.loccode = locations.loccode
            JOIN sec_unegsxuser ON sec_unegsxuser.tagref = a.tagrefowner AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
            JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` = '".$_SESSION['UserID']."' AND a.tagrefowner = `tb_sec_users_ue`.`tagref` AND  a.ue = `tb_sec_users_ue`.`ue`
            WHERE  a.description LIKE '%".$_POST['txtDescripcion']."%'";

    if(!empty($datosUR)){
            $SQL .= " AND a.tagrefowner IN (".$datosUR.")";
    }else{
        $SQL .= " AND a.tagrefowner IN (SELECT tagref FROM sec_unegsxuser WHERE userid= '".$_SESSION["UserID"]."') ";
    }

    if(!empty($datosUE)){
        $SQL .= " AND a.ue IN (".$datosUE.")";
    }

    if(!empty($datosPatrimonios)){
        $SQL .= " AND a.assetid IN (".$datosPatrimonios.")";
    }

    if(!empty($datosCategorias)){
        $SQL .= " AND a.assetcategoryid IN (".$datosCategorias.")";
    }

    if(!empty($datosEstatus)){
        $SQL .= " AND a.status IN (".$datosEstatus.")";
    }

    if(!empty($datosActive)){
        $SQL .= " AND a.active IN (".$datosActive.")";
    }

    if(!empty($datosTipoBien)){
        $SQL .= " AND a.tipo_bien IN (".$datosTipoBien.")";
    }

    if(!empty($datosAlmacen)){
        $SQL .= " AND a.loccode IN (".$datosAlmacen.")";
    }

    $SQL .= " ORDER BY assetid DESC";

    $ErrMsg = "No se obtuvieron tipos de activos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $idrow = 0;

    $enc = new Encryption;

    while ($myrow = DB_fetch_array($TransResult)) {

        // if ($myrow['contabilizado'] == 0) {
        //     $puedeModificar =  '<a onclick="fnGenerarContabilidad('.$myrow ['assetid'].','.$idrow.')">Generar</a>';
        // } else {
        //     $puedeModificar =  'Contabilizado';
        // }


        $url = "&AssetId=>" . $myrow["assetid"];
        $url .= "&new=>false";
        $url .= "&aEr=>false";
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        $liga_folio ="<a target='_self' href='./activofijo.php?$liga' ><span class='glyphicon glyphicon-edit'></span></a>";
        $idAdjuntos = $myrow["assetid"];
        $url_ver = "&AssetId=>" . $myrow["assetid"];
        $url_ver .= "&new=>false";
        $url_ver .= "&aEr=>false";
        $url_ver .= "&ver=>1";
        $url_ver = $enc->encode($url_ver);
        $liga_ver= "URL=" . $url_ver;
        $liga_folio_ver ="<a target='_self' href='./activofijo.php?$liga_ver' ><span class='glyphicon glyphicon-eye-open'></span></a>";

        $estatus="Activo";
        if($myrow['active']==0){
            $estatus="Inactivo";
        }
        
        $infolistadeactivos[] = array(
            'ur' => $myrow ['ur'],
            'ue' => $myrow ['ue'],
            // 'assetid' => $myrow ['assetid'],
            'categorydescription' => $myrow ['partida_especifica'],
            'loccode' => $myrow ['locationname'],
            'desc_tipobien' => $myrow ['desc_tipobien'],
            'barcode' => $myrow ['barcode'],
            'description' => $myrow ['description'],
            'GenerarContabilidad' => $puedeModificar,
            'fecha_adquisicion' => $myrow ['fecha_adquisicion'],
            'Etapa' => $myrow ['fixedassetstatus'],
            'Estatus' => $estatus,
            'Ver' => $liga_folio_ver,
            'Modificar' => $liga_folio, 
            'Adjuntos' => '<a onclick="fnAdjuntos('.$idAdjuntos.')"><span class="glyphicon glyphicon-th-list"></span></a>',
        );

        $idrow++;
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'ue', type: 'string' },";
    $columnasNombres .= "{ name: 'categorydescription', type: 'string' },";
    $columnasNombres .= "{ name: 'loccode', type: 'string' },";
    $columnasNombres .= "{ name: 'desc_tipobien', type: 'string' },";
    $columnasNombres .= "{ name: 'barcode', type: 'string' },";
    $columnasNombres .= "{ name: 'description', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_adquisicion', type: 'string' },";
    $columnasNombres .= "{ name: 'Etapa', type: 'string' },";
    $columnasNombres .= "{ name: 'Estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'Ver', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string'},";
    $columnasNombres .= "{ name: 'Adjuntos', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '4%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '4%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Partida Específica', datafield: 'categorydescription', width: '9%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Almacén', datafield: 'loccode', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo Bien', datafield: 'desc_tipobien', width: '6%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Número de Inventario', datafield: 'barcode', width: '18%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'description', width: '25%', cellsalign: 'left', align: 'center', hidden: false },";
    
    $columnasNombresGrid .= " { text: 'Condición', datafield: 'Etapa', width: '8%', cellsalign: 'center', align: 'center', hidden: false},";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'Estatus', width: '6%', cellsalign: 'center', align: 'center', hidden: false},";
    $columnasNombresGrid .= " { text: 'Fecha Adquisición', datafield: 'fecha_adquisicion', width: '10%', cellsalign: 'center', align: 'center', hidden: false},";
    $columnasNombresGrid .= " { text: 'Ver', datafield: 'Ver', width: '5%', cellsalign: 'center', align: 'center', hidden: false},";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '5%', cellsalign: 'center', align: 'center', hidden: false},";
    $columnasNombresGrid .= " { text: 'Adjuntos', datafield: 'Adjuntos', width: '5%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(" ", "_", traeNombreFuncionGeneral($funcion, $db, $ponerNombre = '0'))."_".date('dmY');

    $contenido = array('infolistadeactivos' => $infolistadeactivos, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
}
//============== / Cargar Datos Panel Patrimonio ==============================//



//==============  Generar un resguardo de patrimonio ==============================//
//==================  activofijo_resguardos.js ==============================//
if(isset($_POST['option']) and $_POST['option'] == 'generarResguardo'){

    $assetid= $_POST['selectPatrimonio_modal'];
    $empleado= $_POST['selectEmpleados_modal'];
    $ur= $_POST['selectUnidadNegocio_modal'];
    $ue= $_POST['selectUnidadEjecutora_modal'];
    $observaciones= $_POST['txtObservaciones'];
    $folio=0;

    $SQL = "SELECT * FROM fixedasset_Resguardos WHERE userid = ".$empleado." ORDER BY folio DESC LIMIT 1;";
    $ErrMsg = "No se obtuvo datos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);


    //Validaciones para saber si el usuario ya tienen un resguardo
    // y saber y agregar al resguardo que tiene o generar uno nuevo.

    if(DB_num_rows($TransResult)>0){
        $myRow = DB_fetch_array($TransResult);
        $folio=$myRow['folio'];

        $SQL = "INSERT INTO `fixedasset_detalle_resguardos` (`folio`, `assetid`, `estatus`, `fecha`, `ur`, `ue`)
                VALUES ('".$folio."', ".$assetid.", '1', curdate(), ".$ur.", ".$ue.");";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        if($TransResult){
            $Mensaje ="Se agrego al resguardo con folio: ".$folio."";
        }else{
            $Mensaje ="Problemas al agregar al resguardo con folio ".$folio."";
        }

    }else{

        //Generamos el folio siguiente para el nuevo resguardo
        $folio = GetNextTransNo('1002',$db);

        $SQL = "INSERT INTO `fixedasset_Resguardos` (`userid`, `folio`, `fecha`, `estatus`, `fechaultimoresguardo`, `ur`, `ue`,`observaciones`)
        VALUES (  ".$empleado.", '".$folio."', curdate(), 'actual', curdate(), ".$ur.", ".$ue.",'".$observaciones."');";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $SQL = "INSERT INTO `fixedasset_detalle_resguardos` (`folio`, `assetid`, `estatus`, `fecha`, `ur`, `ue`)
                VALUES ('".$folio."', ".$assetid.", '1', curdate(), ".$ur.", ".$ue.");";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        if($TransResult){
            $Mensaje ="Se agrego el resguardo con folio: ".$folio."";
        }else{
            $Mensaje ="Problemas al generar el resguardo ";
        } 
    }

    //Modificamos el estatus del activo fijo como en resguardo
    if($TransResult){
        $SQL ="UPDATE fixedassets SET status ='9'  WHERE assetid=".$assetid.";";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if(!$TransResult){
            $Mensaje ="Problemas al modificar el estatus del activo";
        } 

    }
}
//==============  / Generar un resguardo de patrimonio ==============================//



//==============  Cargar Datos de panel de resguardo ==============================//
//==================  activofijo_resguardos.js ==============================//
if(isset($_POST['option']) and $_POST['option'] == 'mostrarRegistros'){


    $info = array();

    // separar la seleccion multiple de las unidades responsables
    $unidadres="";
    $datosUR = "";
    if(isset($_POST["selectUnidadNegocio"]) and $_POST["selectUnidadNegocio"]!=""){
        $unidadres=explode( ',',$_POST["selectUnidadNegocio"]);

        foreach ($unidadres as $key) {
            if($key != "-1"){
                if (empty($datosUR)) {
                    $datosUR .= "".$key."";
                } else {
                    $datosUR .= ", ".$key."";
                }
            }
        }
    }

    // separar la seleccion multiple de las unidades ejecutora
    $unidadue="";
    $datosUE = "";
    if(isset($_POST["selectUnidadEjecutora"]) and $_POST["selectUnidadEjecutora"]!=""){
        $unidadue=explode( ',',$_POST["selectUnidadEjecutora"]);
    
        foreach ($unidadue as $key) {
            if($key != "-1"){
                if (empty($datosUE)) {
                    $datosUE .= "".$key."";
                } else {
                    $datosUE .= ", ".$key."";
                }
            }
            
        }
    }

    // separar la seleccion multiple de los empleados
    $empleados= "";
    $datosEmpleados = "";
    if(isset($_POST["selectEmpleadotab2"]) and $_POST["selectEmpleadotab2"]!=""){
        $empleados=explode( ',',$_POST["selectEmpleadotab2"]);
    
        foreach ($empleados as $key) {
            if($key != "-1"){
                if (empty($datosEmpleados)) {
                    $datosEmpleados .= "".$key."";
                } else {
                    $datosEmpleados .= ", ".$key."";
                }
            }
        }
    }

    // separar la seleccion multiple de los empleados
    $patrimonios= "";
    $datosPatrimonios = "";
    if(isset($_POST["selectPatrimonio"]) and $_POST["selectPatrimonio"]!=""){
        $patrimonios=explode( ',',$_POST["selectPatrimonio"]);
        foreach ($patrimonios as $key) {
            if($key != "-1"){
                if (empty($datosPatrimonios)) {
                    $datosPatrimonios .= "".$key;
                } else {
                    $datosPatrimonios .= ", ".$key;
                }
            }
        }
    }


    $folio=$_POST["txtFolio"];

    $fecha_ini = $_POST['txtFechaInicial'];
    $fecha_fin = $_POST['txtFechaFinal'];

    $SQL = "SELECT fr.idResguardo,
                    concat(te.ln_nombre,' ',te.sn_primer_apellido,' ',te.sn_segundo_apellido) AS empleado,
                    fr.folio, 
                    DATE_FORMAT(fr.fecha,'%d-%m-%Y') as fecha,
                    fr.ur,
                    fr.ue,
                    DATE_FORMAT(fr.fechaultimoresguardo,'%d-%m-%Y') as fechaultimoresguardo,
                    fr.observaciones,
                    fr.estatus,
                    tb_resguardo_status.description as nameEstatus,
                    fr.ln_ubicacion
            FROM fixedasset_Resguardos fr
            left join tb_resguardo_status on fr.estatus = tb_resguardo_status.id 
            LEFT JOIN tb_empleados te on fr.userid = te.id_nu_empleado
            JOIN sec_unegsxuser ON sec_unegsxuser.tagref = fr.ur AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
            JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` = '".$_SESSION['UserID']."' AND fr.ur = `tb_sec_users_ue`.`tagref` AND  fr.ue = `tb_sec_users_ue`.`ue` 
            WHERE fr.folio LIKE '%". $folio ."%' ";
        
        //unidad Ejecutora (tagref)
        if(!empty($datosUR)){
            $SQL .= " AND fr.ur IN (".$datosUR.")";
        }else{
            $SQL .= " AND fr.ur IN (SELECT tagref FROM sec_unegsxuser WHERE userid= '".$_SESSION["UserID"]."') ";
        }
        if(!empty($datosUE)){
            $SQL .= " AND fr.ue IN (".$datosUE.")";
        }
        if(!empty($datosEmpleados)){
            $SQL .= " AND fr.userid IN (".$datosEmpleados.")";
        }
        if(!empty($datosPatrimonios)){
            $SQL .= " AND fr.folio IN (SELECT folio FROM fixedasset_detalle_resguardos WHERE assetid IN (".$datosPatrimonios.") GROUP BY folio)";
        }

    $SQL .= " ORDER BY fr.folio desc ;";
    //echo "sql:".$SQL;
    $ErrMsg = "No se obtuvieron las resguardos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

        //Encriptar url del detalle de resguardo, folio.
        $enc = new Encryption;
        $url = "&Folio=>" . $myrow["folio"];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        $liga_folio ="<a target='_self' href='./resguardo_detalles.php?$liga' style='color: blue;'><u>".$myrow ['folio']."</u></a>";

        $estatusRes="Actual";
        if($myrow ['estatus'] == '0'){
            $estatusRes="Historico";
        }

        $info[] = array(
            'UR' => $myrow ['ur'],
            'UE' => $myrow ['ue'],
            'Folio' => $myrow ['folio'],
            'Folio_link' => $liga_folio,
            'Empleado' => $myrow ['empleado'],
            'Fecha_Registro' => $myrow ['fecha'],
            'Fecha_Ultima' => $myrow ['fechaultimoresguardo'],
            'estatus' => $myrow['nameEstatus'],
            'Observaciones' => $myrow ['observaciones'],
            'ln_ubicacion' => $myrow ['ln_ubicacion']);
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'UR', type: 'string' },";
    $columnasNombres .= "{ name: 'UE', type: 'string' },";
    $columnasNombres .= "{ name: 'Folio', type: 'string' },";
    $columnasNombres .= "{ name: 'Folio_link', type: 'string' },";
    $columnasNombres .= "{ name: 'Empleado', type: 'string' },";
    $columnasNombres .= "{ name: 'Fecha_Registro', type: 'string' },";
    $columnasNombres .= "{ name: 'Fecha_Ultima', type: 'string' },";
    $columnasNombres .= "{ name: 'estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'Observaciones', type: 'string' },";
    $columnasNombres .= "{ name: 'ln_ubicacion', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'UR', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'UE', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'Folio', width: '10%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'Folio_link', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Empleado', datafield: 'Empleado', width: '30%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Registro', datafield: 'Fecha_Registro', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Modificación', datafield: 'Fecha_Ultima', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'estatus', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Observaciones', datafield: 'Observaciones', width: '20%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ubicacion', datafield: 'ln_ubicacion', width: '20%', cellsalign: 'left', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}
//==============  / Cargar Datos de panel de resguardo ==============================//



finaldelcodigo:

$dataObj = array('sql' => "", 'contenido' => $contenido, 'exito' => $exito, 'result' => $TransResult, 'ErrMsg' => $ErrMsg, 'DbgMsg' => $DbgMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
