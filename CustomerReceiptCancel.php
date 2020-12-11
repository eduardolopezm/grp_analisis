<?php
//ini_set('display_errors', 1);
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
***
*/
/*
 * ARCHIVO MODIFICADO POR: CARMEN GARCIA FECHA DE MODIFICACION: 25-FEB-2011 CAMBIOS: 1. SE AGREGO FUNCION DE REENVIO DE DATOS PARA XML FIN DE CAMBIOS //
 */
//
$funcion = 205;
$PageSecurity = 3;
include ('includes/session.inc');
$title = traeNombreFuncion($funcion, $db);
$debug_sql = false;

// LA VARIABLE $ambiente, nos sirvira para para definir en que ambiente se esta ejecutando la pagina
// y asi saber que paginas mandar llamar y que parametros para la facturacion electronica,
// ya sea demo o pagina de facturacion.
// Se hace correción de los permisos para cancelación de recibos.
/*
 se agregó coluna de usuario tarea: 74592
 */
// DGJ 14/SEP/2018 se agrega funcion FuncionActualizaFechaEmision()
// a produccion
// Se envia a produccion cambios de anticipos 03/12/2018
/*
 DGJ: 04/07/2019 TAREA 77965 a produccion 
 */
/*
 DGJ: 25/07/2019 TAREA 78945 a produccion 
 */

include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/SecurityFunctions.inc');
include ('includes/SendInvoicingV6_0.php');
include ('javascripts/libreriasGrid.inc');
include ('Numbers/Words.php');
include ('XSAInvoicing2.inc');

$datos=array();


$msg = '';

/*
 * INICIO *RECUPERO LOS VALORES QUE VIENEN EN EL URL, ESTO SE APLICA CUANDO SE LLAMA * LA OPCION DE ELIMINAR ALGUN RECIBO
 */
{
	if (isset ( $_GET ['txtfechadesde'] )) {
		$_POST ['txtfechadesde'] = $_GET ['txtfechadesde'];
	}
	if (isset ( $_GET ['txtfechahasta'] )) {
		$_POST ['txtfechahasta'] = $_GET ['txtfechahasta'];
	}
	if (isset ( $_GET ['txtrecibono'] )) {
		$_POST ['txtrecibono'] = $_GET ['txtrecibono'];
	}
	if (isset ( $_GET ['cbounidadnegocio'] )) {
		$_POST ['cbounidadnegocio'] = $_GET ['cbounidadnegocio'];
    }
    if (isset ( $_GET ['selectUnidadEjecutoraFiltro'] )) {
		$_POST ['selectUnidadEjecutoraFiltro'] = $_GET ['selectUnidadEjecutoraFiltro'];
	}
}
/* FIN */
if (isset ( $_POST ['txtfechadesde'] )) {
    $fechadesde = $_POST ['txtfechadesde'];
    $fechadesde2 = date('d/m/Y',strtotime($_POST ['txtfechadesde']));
} else {
    $fechadesde2 = date ( 'Y' ) . "/" . date ( 'm' ) . "/" . date ( 'd' );
    $fechadesde = date ( 'd' ) . "-" . date ( 'm' ) . "-" . date ( 'Y' );
}
if (isset ( $_POST ['txtfechahasta'] )) {
    $fechahasta = $_POST ['txtfechahasta'];
    $fechahasta2 = date('d/m/Y',strtotime($_POST ['txtfechahasta']));
} else {
    $fechahasta2 = date ( 'Y' ) . "/" . date ( 'm' ) . "/" . date ( 'd' );
    $fechahasta = date ( 'd' ) . "-" . date ( 'm' ) . "-" . date ( 'Y' );
}
if(isset($_POST['clientefijo'])){
    $checked = 'checked';
}
$arrfrom = explode("-",$fechadesde);
$from = $arrfrom[2]. "-" . $arrfrom[1] . "-" . $arrfrom[0] . " 00:00:00.000";
$arrto = explode("-",$fechahasta);
$to = $arrto[2]. "-" . $arrto[1] . "-" . $arrto[0] . " 23:59:59.000";

if (isset ( $_POST ['contribu'] )) {
	$contribuyente = $_POST ['contribu'];
} else {
	$contribuyente = '';
}

if (isset ( $_POST ['txtrecibono'] )) {
	$recibono = $_POST ['txtrecibono'];
} else {
	$recibono = '';
}

$paseDeCobro = '';
if (isset ( $_POST ['txtPaseDeCobro'] )) {
    $paseDeCobro = $_POST ['txtPaseDeCobro'];
}

if (isset ( $_POST ['cliente'] )) {
	$cliente = $_POST ['cliente'];
} else {
	$cliente = '';
}
if (isset ( $_POST ['cbounidadnegocio'] )) {
	$unidadnegocio = $_POST ['cbounidadnegocio'];
} else {
	$unidadnegocio = 0;
}
if (isset ( $_POST ['selectUnidadEjecutoraFiltro'] )) {
	$unidadResponsable = $_POST ['selectUnidadEjecutoraFiltro'];
} else {
	$unidadResponsable = 0;
}
if (isset ( $_POST ['cbTipodocumento'] )) {
	$tipodocumento = $_POST ['cbTipodocumento'];
} else {
	$tipodocumento = '';
}
if (isset ( $_GET ['iddocto'] )) {
	$iddocto = $_GET ['iddocto'];
}
if (isset ( $_GET ['folio'] )) {
	$folio = $_GET ['folio'];
}
if (isset ( $_GET ['Regenera'] )) {
	$Regenera = $_GET ['Regenera'];
}
$serie = $_GET ['serie'];
$folio = $_GET ['folio'];
$rfc = $_GET ['rfc'];
$keyfact = $_GET ['keyfact'];
$permisoSustitucion = Havepermission($_SESSION ['UserID'], 2126, $db);

echo  '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css"/>
		<link rel="stylesheet" type="text/css" href="javascripts/DataTables/jquery.dataTables.min.css"/>
        <link rel="stylesheet" type="text/css" href="Angular1/bootstrap-multiselect.css"/>';
        
echo '<style type="text/css">
		.clTitleSec{ font-size: 12pt; color: #2980b9; background: #FFF; padding: 1px; width:180px; border:0;}
		.clfieldset{ min-width: 900px; max-width: 1000px; padding-top: 5px; }
	 </style>';

if(isset($_GET['Exito']) AND $_GET['Exito'] == 1 ){
	prnMsg("Se genero con éxito el recibo con el folio: ".$_GET['folio'],'info');

}elseif (isset($_GET['Exito']) AND $_GET['Exito'] == 0) {
	prnMsg("Ocurrio un error al generar el recibo: ".$_GET['error'],'info');
}

if (isset($_POST['confirmdelete'])) {
	//echo "<meta http-equiv='Refresh' content='0; url=".$_POST['liga']." >";
	echo "<meta http-equiv='Refresh' content='0; url=" . $_POST['liga']. "'>";
}
if (isset($_GET['confirmdelete'])) {
	$come=$_SESSION['comentario'];
	//$come_po=$_POST['comentario'];
	unset($_SESSION['comentario']);
	unset($_SESSION['liga']);
	$transno = $_GET ['transno'];
	echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="POST" name="form_del" id="form_del">';
    // Validaciones para facturación Anticipos
    $vrFlagAnt = false;
    $sqlSaldoAnt = "SELECT custallocns.transid_allocto, saldoA.id
        FROM debtortrans AS recibo
        INNER JOIN custallocns ON transid_allocfrom=recibo.id
        INNER JOIN debtortrans AS saldoA ON saldoA.type = 130 AND saldoA.ref1 = custallocns.transid_allocto AND saldoA.reference = '" . $transno . "'
        WHERE recibo.type=12 AND recibo.transno= '".$transno."'";
    debug_sql($sqlSaldoAnt, __LINE__,$debug_sql,__file__);
    $resaldoA = DB_query($sqlSaldoAnt, $db);
    if(DB_num_rows($resaldoA)>0){
        while ($rowsA = DB_fetch_array($resaldoA)) {  
            $sqlRel = "SELECT *
                FROM custallocns
                WHERE transid_allocfrom=".$rowsA['id'];
            debug_sql($sqlRel, __LINE__,$debug_sql,__file__);
            $resRel = DB_query($sqlRel, $db);
            if(DB_num_rows($resRel)>0){
                $vrFlagAnt = true;
                break;
            }
        }
    }
    if($vrFlagAnt==true){
        prnMsg ( _ ( 'El recibo tiene documentos relacionados, favor de verificarlo' ), 'error' );
    }else{
        $sqlSaldoAnt = "SELECT custallocns.transid_allocto, saldoA.id
                        FROM debtortrans AS recibo
                        INNER JOIN custallocns ON transid_allocfrom=recibo.id
                        INNER JOIN debtortrans AS saldoA ON saldoA.type = 130 AND saldoA.ref1 = custallocns.transid_allocto AND saldoA.reference = '" . $transno . "'
                        WHERE recibo.type=12 AND recibo.transno = '" . $transno . "'";
                    
        debug_sql($sqlSaldoAnt, __LINE__,$debug_sql,__file__);
        $resaldoA = DB_query($sqlSaldoAnt, $db);
        if(DB_num_rows($resaldoA)>0){
            $rowsA = DB_fetch_array($resaldoA);
            $sqlRel = "UPDATE debtortrans
                    SET ovamountcancel=ovamount,
                    ovgstcancel=ovgst,
                    ovamount=0, ovgst=0, alloc=0, invtext='CANCELADO'
                    WHERE id = " . $rowsA['id'];
            debug_sql($sqlRel, __LINE__,$debug_sql,__file__);

            $ErrMsg = _ ( 'El Sql que fallo fue' );
            $DbgMsg = _ ( 'No se pudo fecha de emision' );
            $Result = DB_query ( $sqlRel, $db, $ErrMsg, $DbgMsg, true );
        }
        $SQLValCan = "SELECT *
                    FROM debtortrans
                    WHERE  abs(ovamount+ovgst)=0
                    AND debtortrans.transno = '" . $transno . "'
                    AND type = '" . $tipodefacturacion . "'";
        $resultValCan = DB_query ( $SQLValCan, $db );
        $SQL_recibo="SELECT * FROM debtortrans WHERE transno='".$transno."'";
        $resultVerRecibo=DB_query($SQL_recibo, $db);
        $myrow_=DB_fetch_array($resultVerRecibo);
        if (DB_num_rows ( $resultValCan ) > 0) {
            prnMsg ( _ ( 'El recibo ya se encuentra cancelado, favor de verificarlo' ), 'error' );
        } else {
            $Result = DB_Txn_Begin ( $db );
            if (isset($come) and  strnatcasecmp($come,"Escribe un comentario...")!=0) {
                $comen="CANCELADO ".$come;
            }
            else {
                $comen='CANCELADO';
            }
            $DSQL = "UPDATE debtortrans
                   SET ovamountcancel=ovamount,
                ovgstcancel=ovgst,
                ovamount=0, ovgst=0, alloc=0, invtext='".$comen."'
                WHERE transno = " . $transno . "
                and type in (12,21,80,90)";
            $DbgMsg = _ ( 'El SQL fallo al actualizar los registros:' );
            $ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
            $Result = DB_query ( $DSQL, $db, $ErrMsg, $DbgMsg, true );
            $SQL = "SELECT *
                FROM debtortransmovs
                WHERE transno = " . $transno . "
                and type = 12";
            $ErrMsg = _ ( 'No transactions were returned by the SQL because' );
            $TransResult = DB_query ( $SQL, $db, $ErrMsg );
            while ( $myrow = DB_fetch_array ( $TransResult ) ) {
                $xtype = substr ( $myrow ['reference'], 0, strpos ( $myrow ['reference'], " - " ) );
                $xtransno = substr ( $myrow ['reference'], strpos ( $myrow ['reference'], " - " ) + 3 );

                if (($xtype != "") and ($xtransno != "")) {
                    $USQL = "UPDATE debtortrans
                    SET alloc = alloc - " . (abs ( $myrow ['ovamount'] ) + abs ( $myrow ['ovgst'] )) . ",
                    settled='0'
                    WHERE type = " . $xtype . "
                    and transno = " . $xtransno;
                    $DbgMsg = _ ( 'El SQL fallo al eliminar los registros:' );
                    $ErrMsg = _ ( 'No se pudo actualizar la Transaccion Contable' );
                    $Result = DB_query ( $USQL, $db, $ErrMsg, $DbgMsg, true );
                    // echo "<br>" . $USQL;
                    // exit();
                }
                $cliente = $myrow ['debtorno'];
            }

            $DSQL = "DELETE FROM debtortransmovs
                WHERE transno = " . $transno . "
                and type in (12,80,21,90)";
            $DbgMsg = _ ( 'El SQL fallo al eliminar los registros:' );
            $ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
            $Result = DB_query ( $DSQL, $db, $ErrMsg, $DbgMsg, true );

            $CSQL = "SELECT id
                FROM debtortrans
                WHERE transno = " . $transno . "
                and type = 12";
            $ErrMsg = _ ( 'Error!! LA CONSULTA NO ARROJO RESULTADO' );
            $cResult = DB_query ( $CSQL, $db, $ErrMsg );
            if ($cmyrow = DB_fetch_array ( $cResult )) {
                $idrecibo = $cmyrow ['id'];
                $DCSQL = "DELETE FROM custallocns
                    WHERE transid_allocfrom = " . $idrecibo;
                $DbgMsg = _ ( 'Registro Eliminados:' );
                $ErrMsg = _ ( 'El SQL fallo al eliminar los registros del custallocns' );
                $DCResult = DB_query ( $DCSQL, $db, $ErrMsg, $DbgMsg, true );
            }
            $CSQL = "SELECT id
                FROM debtortrans
                WHERE transno = " . $transno . "
                and type = 80";
            $ErrMsg = _ ( 'Error!! LA CONSULTA NO ARROJO RESULTADO' );
            $cResult = DB_query ( $CSQL, $db, $ErrMsg );
            if ($cmyrow = DB_fetch_array ( $cResult )) {
                $idanticipo = $cmyrow ['id'];
                $DCSQL = "DELETE FROM custallocns
                    WHERE transid_allocfrom = " . $idanticipo;
                $DbgMsg = _ ( 'Registro Eliminados:' );
                $ErrMsg = _ ( 'El SQL fallo al eliminar los registros del custallocns' );
                $DCResult = DB_query ( $DCSQL, $db, $ErrMsg, $DbgMsg, true );
            }

            if ($_SESSION ['SameDateInReceiptCancel'] == 1) {

                $isql = "INSERT INTO gltrans(type,typeno,chequeno,trandate,periodno,account,narrative,amount,posted,jobref,tag)
                        SELECT type, typeno, '0', trandate, periodno, account, CONCAT(narrative,'@RECIBO CANCELADO'), (amount*-1), '0', jobref, tag
                        FROM gltrans
                        WHERE type=12 AND typeno = '" . $transno . "'";
            } else {
                $isql = "INSERT INTO gltrans(type,typeno,chequeno,trandate,periodno,account,narrative,amount,posted,jobref,tag)
                        SELECT type, typeno, '0', Now(), periodno, account, CONCAT(narrative,'@RECIBO CANCELADO'), (amount*-1), '0', jobref, tag
                        FROM gltrans
                        WHERE type=12 AND typeno = '" . $transno . "'";
            }
            $DbgMsg = _ ( 'El SQL fallo al insertar en gltrans:' );
            $ErrMsg = _ ( 'Fallo la insercion en glrans' );
            $Result = DB_query ( $isql, $db, $ErrMsg, $DbgMsg, true );
            $Result = DB_Txn_Commit ( $db );
            $systype_doc = 12;
            $SQL = "SELECT * FROM  debtortrans WHERE type=" . $systype_doc . " AND transno=" . $transno; // ." and order_=".$_SESSION['ProcessingOrder'];
            $ErrMsg = _ ( 'ERROR CRITICO' ) . '! ' . _ ( 'ANOTE ESTE ERROR Y BUSQUE AYUDA' ) . ': ' . _ ( 'El registro de transacciones deudor no pueden cancelarse, porque' );
            $DbgMsg = _ ( 'El siguiente SQL se utiliz� para modificar el registro de transacciones del deudor' );
            $Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
            if (DB_num_rows ( $Result ) == 1) {
                $myrowtags = DB_fetch_array ( $Result );
                $foliox = trim ( $myrowtags ['folio'] );
                $separa = explode ( '|', $foliox );
                $serie = $separa [1];
                $folio = $separa [0];
                $UIID = $myrowtags ['uuid'];
                $idfactura = $myrowtags ['id'];
                $fechaorigen = $myrowtags ['origtrandate'];
                $tagref = $myrowtags ['tagref'];
            }
            $SQL = " SELECT l.taxid,l.address5,t.tagname,t.typeinvoice
                     FROM legalbusinessunit l, tags t
                     WHERE l.legalid=t.legalid AND tagref='" . $tagref . "'";
            $Result = DB_query ( $SQL, $db );
            if (DB_num_rows ( $Result ) == 1) {
                $myrowtags = DB_fetch_array ( $Result );
                $rfc = trim ( $myrowtags ['taxid'] );
                $keyfact = $myrowtags ['address5'];
                $nombre = $myrowtags ['tagname'];
                $tipofacturacionxtag = $myrowtags ['typeinvoice'];
            }
            if ($tipofacturacionxtag == 1) {
                $param = array (
                        'in0' => $serie,
                        'in1' => $folio,
                        'in2' => $rfc,
                        'in3' => $keyfact
                );
                try {
                    $client = new SoapClient ( $_SESSION ['XSA'] . "xsamanager/services/CancelCFDService?wsdl" );
                    $codigo = $client->cancelaCFD ( $param );
                } catch ( SoapFault $exception ) {
                    $errorMessage = $exception->getMessage ();
                }
                if ($codigo == true) {
                    prnMsg ( _ ( 'Numero de Recibo' ) . ' ' . $_SESSION ['TransNo'] . ' ' . _ ( 'Se Cancelo con Exito' ), 'success' );
                    $liga = "http://" . $_SESSION ['XSA'] . "xsamanager/cancelCfdWebView?serie=" . $serie . "&folio=" . $folio . "&tipo=PDF&rfc=" . $rfc . "&key=" . $keyfact;
                } else {
                    prnMsg ( _ ( 'No se pudo realizar la cancelacion en SAT favor de informar al administrador' ), 'error' );
                }
            } else if ($tipofacturacionxtag == 4) {
                $success = false;
                $config = $_SESSION;
                include_once 'timbradores/TimbradorFactory.php';
                $timbrador = TimbradorFactory::getTimbrador ( $config );
                if ($timbrador != null) {
                    $timbrador->setRfcEmisor ( $rfc );
                    $timbrador->setDb ( $db );
                    $success = $timbrador->cancelarDocumento ( $UIID );
                    $success = $timbrador->cancelarDocumento ( $UIID );
                    $success = $timbrador->cancelarDocumento ( $UIID );
                    foreach ( $timbrador->getErrores () as $error ) {
                        prnMsg ( $error, 'error' );
                    }
                } else {
                    prnMsg ( _ ( 'No hay un timbrador configurado en el sistema' ), 'error' );
                }
                if ($success) {
                    $XMLElectronico = generaXMLCancelCFDI ( $UIID, 'ingreso', $tagref, $serie, $folio, $idfactura, 'Recibo', $fechaorigen, $db );
                    prnMsg ( _ ( 'Numero de Factura' ) . ' ' . $TransNo . ' ' . _ ( 'Se Cancelo con Exito' ), 'success' );
                } else {
                    prnMsg ( _ ( 'Numero de Factura' ) . ' ' . $TransNo . ' ' . _ ( 'Se Cancelo con Exito' ), 'success' );
                }
            } 
            echo "<meta http-equiv='Refresh' content='0; url=CustomerReceiptCancel.php'>";
        }
    }
	echo "</form>";
}


/**
 * ******************************************
 */
// Criterios de cancelacion
/**
 * ******************************************
 */
?>
<script type="text/javascript" src="javascripts/CustomerReceiptCancel.js"></script>
<!--Modal Agregar/Modificar -->

<div class="modal fade" id="ModalUR" name="ModalUR" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
        <!--Contenido Encabezado-->
        <div class="col-md-12 menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalUR_Titulo" name="ModalUR_Titulo"></div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div id="divMensajeOperacion" name="divMensajeOperacion" class="m10"></div>
      <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">
        <!--Mensaje o contenido-->
        <div id="msjValidacion" name="msjValidacion"></div>
            
            
            <div class="col-md-12">
                <component-text-label label="Numero de recibo de pago: " max="9" maxlength="255" id="transno" name="transno" placeholder="Numero de recibo de pago" readonly></component-text-label>
            </div>
            <div class="row"></div>
            <br>
            <br>
            
            <div class="col-md-12"> 
			<component-textarea-label label="Comentario de cancelación: " max="9" maxlength="255" id="txtComentario" name="txtComentario" placeholder="Comentario de cancelación"></component-textarea-label>
		    </div>
			</br>
            </br>
            </br>
			</br>
            </br>
            </br>
      </div>
      <div class="modal-footer">
        <component-button type="button" id="btn" name="btn" onclick="fnAgregar()" value="Guardar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>
<!-- TERMINA MODAL DE EDICION -->

<?php
/**
 * ******************************************
 */
// Criterios de consulta 
/**
 * ******************************************
 */

?>



<link rel="stylesheet" href="css/listabusqueda.css" />

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<!-- target="_blank" -->
<a href="suficiencia_manual.php" name="Link_NuevoGeneral" id="Link_NuevoGeneral" class="btn btn-primary" style="width: 200px; display: none;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Link Nuevo</a>

<div align="left">
  <!--Panel Busqueda-->
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title row">
                <div class="col-md-3 col-xs-3">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
                        <b style="color: #333;">Criterios de filtrado</b>
                    </a>
                </div>
            </h4>
        </div>
        <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
                <form action="<?php $_SERVER ['PHP_SELF'] ?>" method="post" name="form1" id="form1">  
                    <div class="col-md-4">
                        <div class="form-inline row" style="display: none;">
                            <div class="col-md-3">
                                <span><label>UR: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select name="cbounidadnegocio[]" id="cbounidadnegocio" class="form-control selectGeneral" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutoraFiltro')" multiple="true"> 
                                    <?php
                                        $sql = "SELECT t.tagref, t.tagdescription
                                        FROM tags t, sec_unegsxuser uxu
                                        WHERE t.tagref=uxu.tagref
                                        AND uxu.userid='" . $_SESSION ['UserID'] . "'";
                                        if (isset ( $_POST ['legalbusiness'] ) and $_POST ['legalbusiness'] > 0) {
                                            $sql = $sql . " and t.legalid = '" . $_POST ['legalbusiness'] . "'";
                                        }
                                        $sql = $sql . " ORDER BY tagref";
                                        $resultTags = DB_query ( $sql, $db, '', '' );
                                        $cbmPostUE = $_POST['cbounidadnegocio'];
                                        while ( $myrow = DB_fetch_array ( $resultTags ) ) {
                                            $selected="";
                                            if (!empty($cbmPostUE)) {
                                                foreach ($cbmPostUE as $key => $value) {
                                                    if ($value != -1) {
                                                        if ($myrow['tagref'] == $value) {
                                                            $selected="selected";
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                            echo "<option ".$selected." value='" . $myrow['tagref'] . "'>" .$myrow['tagref']." - ".$myrow['tagdescription'] . "</option>";
                                        }
                                    ?>
                                </select> 
                            </div>
                        </div>
                        
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>UE: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="selectUnidadEjecutoraFiltro" name="selectUnidadEjecutoraFiltro[]" class="form-control selectGeneral" multiple="true">
                                    <?php
                                        $SQLx = "SELECT DISTINCT tce.ue,tce.desc_ue as uedescription
                                        FROM sec_unegsxuser u
                                        INNER JOIN tags t on (u.tagref = t.tagref) 
                                        INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref) 
                                        JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = t.tagref AND tb_sec_users_ue.ue = tce.ue
                                        WHERE tce.active = 1 
                                        AND u.userid = '" . $_SESSION['UserID'] . "' 
                                        AND tb_sec_users_ue.userid = '" . $_SESSION['UserID'] . "'
                                        ORDER BY tce.ue, uedescription ASC";
                                        $result=DB_query($SQLx, $db);
                                        $cbmPostUE = $_POST['selectUnidadEjecutoraFiltro'];
                                        while ($myrow=DB_fetch_array($result)) {
                                            $selected="";
                                            if (!empty($cbmPostUE)) {
                                                foreach ($cbmPostUE as $key => $value) {
                                                    if ($value != -1) {
                                                        if ($myrow['ue'] == $value) {
                                                            $selected="selected";
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                            echo "<option ".$selected." value='" . $myrow['ue'] . "'>" .$myrow['ue']." ".$myrow['uedescription'] . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-inline row" style="display: none;">
                            <div class="col-md-3">
                                <span><label>Tipo de Docto: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="cbTipodocumento" name="cbTipodocumento" class="form-control selectGeneral" style="width: 100%;"> 
                                    <option value=''>TODOS</option>
                                    <?php
                                        $SQL = "SELECT typeid,typename
                                        FROM systypescat
                                        WHERE typeid IN(10,110,119)
                                        ORDER BY typename";
                                        $ErrMsg = _ ( 'No transactions were returned by the SQL because' );
                                        $TransResult = DB_query ( $SQL, $db, $ErrMsg );
                                        while ( $myrow = DB_fetch_array ( $TransResult ) ) {
                                            if ($myrow ['typeid'] == $tipodocumento) {
                                                echo "<option selected value='" . $myrow ['typeid'] . "'>" . $myrow ['typename'] . "</option>";
                                            } else {
                                                echo "<option value='" . $myrow ['typeid'] . "'>" . $myrow ['typename'] . "</option>";
                                            }
                                        }
                                        if ($tipodocumento == 'otros') {
                                            echo "<option selected value='otros'>"._("Otros")."</option>";
                                        } else {
                                            echo "<option value='otros'>"._("Otros")."</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <br>
                        <component-text-label label="Recibo No:" id="txtrecibono" name="txtrecibono" placeholder="Recibo No." value="<?php echo $recibono ?>" ></component-text-label>
                        <br>
                        <component-text-label label="Pase de cobro: " id="txtPaseDeCobro" name="txtPaseDeCobro" placeholder="Pase de cobro" value="<?php echo $paseDeCobro ?>" ></component-text-label>
                        <br>
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>Usuario: </label></span>
                            </div>
                            <div class="col-md-9">
                                <?php               
                                    echo "
                                    <select name='UserName' id='UserName' class='form-control selectGeneral'> ";
                                    $sql = "SELECT userid, realname FROM www_users ORDER BY realname ASC";
                                    if ($permisouser == 0) {
                                        echo "<option selected Value=''>" . "Sin selección";
                                    } else {
                                        echo "<option selected Value=''>" . "Sin selección";
                                    }
                                    $result = DB_query ( $sql, $db, '', '' );

                                    while ( $myrow = DB_fetch_array ( $result ) ) {
                                        if (isset($_POST['UserName']) && $myrow ['userid'] == $_POST ['UserName']) {
                                            echo "<option selected Value='" . $myrow ['userid'] . "'>" . $myrow ['realname'] . '</option>';
                                        } else {
                                            echo "<option  Value='" . $myrow ['userid'] . "'>" . $myrow ['realname'] . '</option>';
                                        }
                                    }
                                    echo "</select>";
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <component-text-label label="IC:" id="cliente" name="cliente" placeholder="IC"  value="<?php echo $cliente ?>"></component-text-label> 
                        <component-checkbox label="Cliente Fijo:"  name="clientefijo" <?php $checked ?> style="display: none;"></component-checkbox>
                        <br>
                        <component-text-label label="Contribuyente:" id="contribu" name="contribu" placeholder="Contribuyente"  value="<?php echo $contribuyente ?>"></component-text-label> 
                        <br>
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>Objeto Principal: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="selectObjetoPrincipal" name="selectObjetoPrincipal" class="form-control selectObjetoPrincipal selectGeneral"></select>
                            </div>
                        </div>
                        <br>
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>Estatus: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="selectEstatus" name="selectEstatus" class="form-control selectGeneral">
                                    <option value="-1">Seleccionar...</option>
                                    <option value="1" <?php echo ($_POST['selectEstatus'] == '1' ? 'selected' : ''); ?> >Vigente</option>
                                    <option value="2" <?php echo ($_POST['selectEstatus'] == '2' ? 'selected' : ''); ?> >Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <component-date-label label="Desde: " type='date' name="txtfechadesde" value="<?php echo $fechadesde ;?>" size='10' ></component-date-label>
                        <br>
                        <component-date-label label="Hasta: "  type='date' name="txtfechahasta" value="<?php echo $fechahasta ;?>" size='10' ></component-date-label>
                        <?php if( Havepermission ( $_SESSION ['UserID'], 2536, $db ) == 1) { ?>
                        <br>
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>Sólo por Obj. Principal: </label></span>
                            </div>
                            <div class="col-md-9">
                                <input type="checkbox" id="obligatorio" name="obligatorio" value="1" <?php echo $_POST ['obligatorio'] == '1' ? 'checked = "checked"' : ''; ?>>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="row"></div>
                    <div align="center">
                        <br>
                        <component-button  id="buscarPases" type='submit' name='buscar' class="glyphicon glyphicon-search" value='Buscar'></component-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
if (isset($_POST['comentario'])) {
    unset($_SESSION['comentario']);
    unset($_SESSION['liga']);
    $_SESSION["comentario"] = $_POST['comentario'];
    $_SESSION["liga"] = $_POST['liga'];
}
if (isset ( $_GET ['delete'] )) {
	$transno = $_GET ['transno'];
	$SQLValCan = "	SELECT *
				  	From debtortrans
					Where abs(ovamount+ovgst)=0
					and debtortrans.transno = '" . $transno . "'
                    AND type = '" . $tipodefacturacion . "'";
	$resultValCan = DB_query ( $SQLValCan, $db ); // //
	if (DB_num_rows ( $resultValCan ) > 0) {
		prnMsg ( _ ( 'El recibo ya se encuentra cancelado, favor de verificarlo' ), 'error' );
	} else {
		$permiso = Havepermission ( $_SESSION ['UserID'], 126623, $db );
		echo "<table border='0' cellpadding='0' cellspacing='0' width='80%' bgcolor='#eeeeee'>
				<tr>";
		if ($permiso == 0) {
			echo "<td>&nbsp;</td>";
		} else {
			echo "<td style='text-align:center; font-size:14pt;' colspan=3>";
			echo _ ( 'Confirmar que deseas cancelar el Recibo No.' ) . " " . $transno . " ? ";
			$liga_si="CustomerReceiptCancel.php?confirmdelete=yes&serie=" . $serie . "&rfc=" . $rfc . "&folio=" . $folio . "&keyfact=" . $keyfact . "&transno=" . $transno . "&txtfechadesde=" . $fechadesde . "&txtfechahasta=" . $fechahasta . "&txtrecibono=" . $recibono . "&cbounidadnegocio=" . $unidadnegocio."&ban=yes";
			echo "</td>
				</tr>
				<tr>
					<td class='numero_normal'><a href='". "' style='font-size:14pt;'".">";
			echo "";
			echo "</a><button type='submit' value='SI' name='confirmdelete' style='cursor:pointer; border:0; background-color:transparent;'>
        				<img src='images/b_si_25.png' title='SI'>
         				</button>";
			$liga_no= "CustomerReceiptCancel.php?txtfechadesde=" . $fechadesde . "&txtfechahasta=" . $fechahasta . "&txtrecibono=" . $recibono . "&cbounidadnegocio=" . $unidadnegocio;
			echo "<a href='" .$liga_no . "' style='font-size:14pt'>";
			echo "";
			echo "<button type='submit' value='NO' name='btnNo' style='cursor:pointer; border:0; background-color:transparent;'>
        				<img src='images/b_no_25.png' title='NO'>
         				</button></a></td>";
		echo "	</tr>
				<tr>
					<td class='texto_normal' style='text-align:center'>"._("Comentarios:")."</td>
				</tr>
				<tr>
					<td style='text-align: center;' colspan='3'>
					<textarea rows='4' cols='50' ";
			echo ' onclick=txtno();
				   onblur=txt(); ';
			echo  " name='comentario' id='comentario'>Escribe un comentario...</textarea>
					</td>
				<tr>";
			echo "<tr><td><input type='hidden' name='liga' id='liga' value='" . $liga_si . "'></td></tr>";

		}
        echo "</tr></table>";

	}
} elseif (isset ( $_POST ['buscar'] )) {


?>

<div style="overflow-x:scroll;overflow-y:scroll;">
    
   <table id="tablaRecibos" class="table table-bordered">
   <tr class="header-verde">
   <th colspan="16"> 
                Buscar: 
                <input type="text" id="txtFiltroCuentas" name="txtFiltroCuentas"  value="" placeholder="" autocomplete="off"  style="color:black;width:170px;">
    </th>
   </tr>

	<tr class="header-verde">
    <th style="text-align: center;" >Folio</th>
	<th style="text-align: center;">UR</th>
    <th style="text-align: center;">UE</th>
	<th style="text-align: center;">Fecha</th>
	<!-- <th style="display: none;">Folio SAT</th> -->
	<th style="text-align: center;">No. Contribuyente</th>
	<th style="text-align: center;">Referencia</th>
	<th style="text-align: center;">Comentarios</th>
	<th style="text-align: center;">Periodo</th>
	<th style="text-align: center;">Monto</th>
	<!-- <th style="background-color: #a5a5a5; display: none;" >IVA</th> -->
	<th style="text-align: center;">Total</th>
    <th style="text-align: center;">Total Cancelado</th>
	<th style="text-align: center;">Usuario</th>
    <th style="text-align: center;">Fiscal</th>

	<th style="text-align: center;"><span class="glyphicon glyphicon-print"></span></th>
	<th style="text-align: center;"><span class="glyphicon glyphicon-print"></span></th>
	<th style="text-align: center;"><span class="glyphicon glyphicon-remove"></span></th>
    <?php
	if (Havepermission ( $_SESSION ['UserID'], 1124, $db ) == 1) {
        ?>
		<!-- <th style="display: none;"><img src='images/email.png'></th> -->
        <?php
	}else{
        ?>
		<!-- <th style="display: none;"></th> -->
        <?php
	}
	if (Havepermission ( $_SESSION ['UserID'], 943, $db ) == 1) {
        ?>
		<!-- <th style="display: none;"colspan=2>Mod. Cobrador</th> -->
        <?php
	}else{
        ?>
		<!-- <th style="display: none;" colspan=2></th> -->
        <?php
	}
	
	echo "</tr>";
    ?>
    <tbody id="mytable">

    <?php
    // Consulta principal
    $sqlWhere = " AND debtortrans.trandate between '".$from."' AND '".$to."' 
    AND ADDDATE(STR_TO_DATE('" . $fechahasta2 . "', '%d/%m/%Y'),INTERVAL 1 DAY) ";

    if ($_POST ['cbTipodocumento'] != '' and $_POST ['cbTipodocumento'] != 'otros') {
        $sqlWhere .= " AND debtortrans.type = '" . $_POST ['cbTipodocumento'] . "'";
    } else if ($_POST ['cbTipodocumento'] == 'otros') {
        $sqlWhere .= " AND debtortrans.type NOT IN(10,110,119)";
    }

    if ($contribuyente != '') {
        $sqlWhere .= " AND debtorsmaster.name like '%" . $contribuyente . "%'";
    }

    if (isset($_POST['selectObjetoPrincipal']) && !empty($_POST['selectObjetoPrincipal'])) {
        $sqlWhere .= " AND salesorders.fromstkloc = '".$_POST['selectObjetoPrincipal']."' ";
    }

    if (isset($_POST['cbounidadnegocio'])) {
        foreach ($_POST['cbounidadnegocio'] as $supplierId) {
            $typesflag[] = "'$supplierId'";
        }
        $sqlWhere .= " AND debtortrans.tagref IN (" . implode(',', $typesflag) . ") ";
    }

    if (isset($_POST['selectUnidadEjecutoraFiltro'])) {
        foreach ($_POST['selectUnidadEjecutoraFiltro'] as $supplierId) {
            $typesflag[] = "'$supplierId'";
        }
        $sqlWhere .= " AND debtortrans.nu_ue IN (" . implode(',', $typesflag) . ") ";
    }

    foreach ($_POST['cbounidadnegocio'] as $supplierId) {
        $typesflag[] = "'$supplierId'";
    }
    // $sqlWhere .= " AND mbflag IN (" . implode(',', $typesflag) . ") ";

    if($_POST['clientefijo']==true){
        if ($cliente != 0) {
            $sqlWhere .= " and debtortrans.debtorno = '" . $cliente . "'";
        }
    }else{
        if ($cliente != 0) {
            $sqlWhere .= " AND debtortrans.debtorno like '%" . $cliente . "%'";
        }
    }

    $sqlWhere .= " AND sec_objetoprincipalxuser.userid = '".$_SESSION['UserID']."' ";

    $sqlJoin = " JOIN sec_objetoprincipalxuser ON (sec_objetoprincipalxuser.loccode = salesorders.fromstkloc) ";

    if (strlen ( $_POST['UserName'] ) > 0) {
        $sqlWhere .= " AND debtortrans.userid = '" . $_POST['UserName'] . "'";
    }

    if (isset($_POST['selectEstatus']) && $_POST['selectEstatus'] != '-1') {
        if ($_POST['selectEstatus'] == 1) {
            // Vigentes
            $sqlWhere .= " AND debtortrans.ovamount != 0 ";
        } else if ($_POST['selectEstatus'] == 2) {
            // cancelados
            $sqlWhere .= " AND debtortrans.ovamount = 0 ";
        }
    }

    if ($_POST['obligatorio'] != '') {
        // solo filtrar por objeto principal
        if (isset($_POST['selectObjetoPrincipal']) && !empty($_POST['selectObjetoPrincipal'])) {
            $sqlWhere = " AND debtortrans.trandate between '".$from."' AND '".$to."' 
            AND ADDDATE(STR_TO_DATE('" . $fechahasta2 . "', '%d/%m/%Y'),INTERVAL 1 DAY) 
            AND salesorders.fromstkloc = '".$_POST['selectObjetoPrincipal']."' ";
            $sqlJoin = "";
        }
    }

    if ($recibono != '') {
        // solo filtrar por recibo
        $sqlWhere = " AND debtortrans.transno = " . $recibono;
    }

    if ($paseDeCobro != '') {
        // solo filtrar por recibo
        $sqlWhere = " AND salesorders.orderno = " . $paseDeCobro;
    }

    $SQL = "SELECT debtortrans.tagref,
    debtortrans.nu_ue,
    tb_cat_unidades_ejecutoras.desc_ue,
    debtortrans.flagfiscal, 
    debtortrans.transno,
    debtortrans.type,
    debtortrans.debtorno,
    debtortrans.branchcode,
    debtortrans.reference,
    debtortrans.id,
    abs(debtortrans.ovamount) AS ovamount,
    debtortrans.invtext,
    abs(debtortrans.ovgst) AS ovgst,
    abs(debtortrans.alloc) AS alloc,
    abs(debtortrans.ovgstcancel + debtortrans.ovamountcancel) AS ovamountcancel,
    tags.tagdescription,
    debtorsmaster.debtorno,
    debtorsmaster.name,
    debtortrans.trandate,
    debtortrans.type AS tipo,
    day(debtortrans.trandate) AS daytrandate,
    month(debtortrans.trandate) AS monthtrandate,
    year(debtortrans.trandate) AS yeartrandate,
    usrcortecaja.u_status,
    debtortrans.folio,
    debtortrans.id,
    debtortrans.order_,
    legalbusinessunit.taxid,
    legalbusinessunit.address5,
    tags.tagdescription,
    tags.typeinvoice,
    debtortrans.id AS recibo,
    transid_allocto,
    www_users.userid, 
    realname, 
    Xmls.Fiscal AS Timbrado,
    salesorders.orderno,
    log_cancelacion_sustitucion.folio AS folioAnt,
    salesorders.comments
    FROM debtortrans LEFT JOIN Xmls ON Xmls.transNo=debtortrans.transno and Xmls.type=debtortrans.type 
    LEFT JOIN log_cancelacion_sustitucion ON log_cancelacion_sustitucion.transNo = debtortrans.transno AND log_cancelacion_sustitucion.type = debtortrans.type
    INNER JOIN tags ON debtortrans.tagref = tags.tagref
    INNER JOIN tb_cat_unidades_ejecutoras ON debtortrans.nu_ue = tb_cat_unidades_ejecutoras.ue
    INNER JOIN debtorsmaster ON debtortrans.debtorno = debtorsmaster.debtorno
    INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    LEFT JOIN usrcortecaja ON debtortrans.tagref = usrcortecaja.tag
    AND day(debtortrans.trandate) = day(usrcortecaja.fechacorte)
    AND month(debtortrans.trandate) = month(usrcortecaja.fechacorte)
    AND year(debtortrans.trandate) = year(usrcortecaja.fechacorte)
    LEFT JOIN www_users ON www_users.userid = debtortrans.userid
    LEFT JOIN (SELECT transid_allocto,transid_allocfrom FROM custallocns ) AS custallocns ON custallocns.transid_allocfrom = debtortrans.id
    LEFT JOIN debtortrans debtortransFactura ON debtortransFactura.id = custallocns.transid_allocto
    LEFT JOIN salesorders ON salesorders.orderno = debtortransFactura.order_
    ".$sqlJoin."
    WHERE debtortrans.type = 12 ".$sqlWhere." 
    GROUP BY debtortrans.tagref,
    debtortrans.transno,
    debtortrans.debtorno,
    debtortrans.branchcode,
    debtortrans.reference,
    debtortrans.id,
    debtorsmaster.debtorno";

    // Agregar cancelados
    
    $sqlWhere = " AND debtortrans.trandate between '".$from."' AND '".$to."' 
    AND ADDDATE(STR_TO_DATE('" . $fechahasta2 . "', '%d/%m/%Y'),INTERVAL 1 DAY) ";

    if ($contribuyente != '') {
        $sqlWhere .= " AND debtorsmaster.name like '%" . $contribuyente . "%'";
    }

    if (isset($_POST['cbounidadnegocio'])) {
        foreach ($_POST['cbounidadnegocio'] as $supplierId) {
            $typesflag[] = "'$supplierId'";
        }
        $sqlWhere .= " AND debtortrans.tagref IN (" . implode(',', $typesflag) . ") ";
    }

    if (isset($_POST['selectUnidadEjecutoraFiltro'])) {
        foreach ($_POST['selectUnidadEjecutoraFiltro'] as $supplierId) {
            $typesflag[] = "'$supplierId'";
        }
        $sqlWhere .= " AND debtortrans.nu_ue IN (" . implode(',', $typesflag) . ") ";
    }

    foreach ($_POST['cbounidadnegocio'] as $supplierId) {
        $typesflag[] = "'$supplierId'";
    }
    // $sqlWhere .= " AND mbflag IN (" . implode(',', $typesflag) . ") ";

    if($_POST['clientefijo']==true){
        if ($cliente != 0) {
            $sqlWhere .= " and debtortrans.debtorno = '" . $cliente . "'";
        }
    }else{
        if ($cliente != 0) {
            $sqlWhere .= " AND debtortrans.debtorno like '%" . $cliente . "%'";
        }
    }

    if (strlen ( $_POST['UserName'] ) > 0) {
        $sqlWhere .= " AND debtortrans.userid = '" . $_POST['UserName'] . "'";
    }

    if (isset($_POST['selectEstatus']) && $_POST['selectEstatus'] != '-1') {
        if ($_POST['selectEstatus'] == 1) {
            // Vigentes
            $sqlWhere .= " AND debtortrans.ovamount != 0 ";
        } else if ($_POST['selectEstatus'] == 2) {
            // cancelados
            $sqlWhere .= " AND debtortrans.ovamount = 0 ";
        }
    }

    if ($recibono != '') {
        // solo filtrar por recibo
        $sqlWhere = " AND debtortrans.transno = " . $recibono;
    }

    $SQL .= " UNION

    SELECT
    debtortrans.tagref,
    debtortrans.nu_ue,
    tb_cat_unidades_ejecutoras.desc_ue,
    debtortrans.flagfiscal, 
    debtortrans.transno,
    debtortrans.type,
    debtortrans.debtorno,
    debtortrans.branchcode,
    debtortrans.reference,
    debtortrans.id,
    abs(debtortrans.ovamount) AS ovamount,
    debtortrans.invtext,
    abs(debtortrans.ovgst) AS ovgst,
    abs(debtortrans.alloc) AS alloc,
    abs(debtortrans.ovgstcancel + debtortrans.ovamountcancel) AS ovamountcancel,
    tags.tagdescription,
    debtorsmaster.debtorno,
    debtorsmaster.name,
    debtortrans.trandate,
    debtortrans.type AS tipo,
    day(debtortrans.trandate) AS daytrandate,
    month(debtortrans.trandate) AS monthtrandate,
    year(debtortrans.trandate) AS yeartrandate,
    '' as u_status,
    debtortrans.folio,
    debtortrans.id,
    debtortrans.order_,
    legalbusinessunit.taxid,
    legalbusinessunit.address5,
    tags.tagdescription,
    tags.typeinvoice,
    debtortrans.id AS recibo,
    '' as transid_allocto,
    www_users.userid, 
    realname, 
    0 AS Timbrado,
    0 AS orderno,
    0 AS folioAnt,
    '' AS comments
    FROM debtortrans
    INNER JOIN tags ON debtortrans.tagref = tags.tagref
    INNER JOIN tb_cat_unidades_ejecutoras ON debtortrans.nu_ue = tb_cat_unidades_ejecutoras.ue
    INNER JOIN debtorsmaster ON debtortrans.debtorno = debtorsmaster.debtorno
    INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    LEFT JOIN www_users ON www_users.userid = debtortrans.userid
    WHERE debtortrans.type = 12  
    AND debtortrans.ovamount = 0 ".$sqlWhere." 
    ORDER BY tagref,
    0+folio,
    trandate";

    $ErrMsg = _ ( 'No transactions were returned by the SQL because' );
    // echo "<pre>".$SQL."</pre>";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    
	$k = 1;
	$tmonto = 0;
	$ttotal = 0;
    $ttotalCancelado = 0;
	while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $Aplicado =0;
        $select_allcto = 'SELECT debtortrans.id
        					FROM custallocns join debtortrans ON  custallocns.transid_allocto = debtortrans.id and debtortrans.type = 80 AND abs(debtortrans.alloc)>0
        					WHERE transid_allocto="'.$myrow['transid_allocto'].'"';
        $REs_allacto = DB_query ( $select_allcto, $db, $ErrMsg );
        $aplicados = DB_num_rows($REs_allacto);
        if($aplicados>0){
            $Aplicado = 1;
        }
        $banCancelado = 0;
		if (strpos ($myrow ['invtext'],"CANCELADO") !== false ) {
			echo '<tr style="background-color: #d27786;">';
            $banCancelado = 1;
		} else {
			if ($k == 1) {
				echo '<tr bgcolor="#eeeeee">';
				$k = 0;
			} else {
				echo '<tr bgcolor="#ffffff">';
				$k = 1;
			}
		}

		$tipofacturacionxtag = $myrow ['typeinvoice'];
		$InvoiceNoTAG = $myrow ['folio'];
		$separa = explode ( '|', $InvoiceNoTAG );
		$serie = $separa [1];
		$folio = $separa [0];
		$rfc = $myrow ['taxid'];
		$keyfact = $myrow ['address5'];
		$ReenvioXSA = 'CustomerReceiptCancel.php?ReenviaXSA=Yes&cbounidadnegocio=' . $myrow ['tagref'] . '&txtfechadesde=' . $fechadesde . '&txtfechahasta=' . $fechahasta . '&txtrecibono=' . $myrow ['transno'] . '&DebtorNo=' . $myrow ['debtorno'] . '&systype_doc=12&serie=' . $serie . '&folio=' . $folio;
		$ReenviarXSA = "&nbsp;&nbsp;&nbsp;<a href=" . $ReenvioXSA . "><img src='images/email_20.png' alt='Reenviar SAT' border=0></a>";
		$nofactura = 0;
		$factura = "";
		$sqlfactura = "SELECT case when instr(folio,'|')>0 then folio else concat(type,'-',transno) end as folio, type
						FROM custallocns
							inner join debtortrans  ON custallocns.transid_allocto = debtortrans.id
						WHERE custallocns.transid_allocfrom=" . $myrow ['recibo'];
		$resultbanco = DB_query ( $sqlfactura, $db, $ErrMsg );
		while ( $myrowfacturas = DB_fetch_array ( $resultbanco ) ) {
			if ($nofactura == 0) {
				$factura = '(' . $myrowfacturas ['folio'];
			} else {
				$factura = $factura . '; ' . $myrowfacturas ['folio'];
			}
			$nofactura = $nofactura + 1;
			$tipodoctocargo = $myrowfacturas ['type'];
		}

		if ($factura != "") {
			$factura = $factura . ')';
		}
        echo "<td class='numero_normal'>" . $myrow ['transno'] . "</td>";
        echo "<td class='texto_normal2'>" . $myrow ['tagref'] .' - '. $myrow ['tagdescription'] . "</td>";
        echo "<td class='texto_normal2'>" . $myrow ['nu_ue'] .' - '. $myrow ['desc_ue'] . "</td>";
		echo "<td class='numero_normal' nowrap>" . $myrow ['yeartrandate'] . "-" . $myrow ['monthtrandate'] . "-" . $myrow ['daytrandate'] . "</td>";
        $sql = "SELECT log.*, order_, tagref
				FROM log_complemento_sustitucion as log
				INNER JOIN debtortrans ON debtortrans.transno = log.transno AND log.type = debtortrans.type
				WHERE sustitucion_from = ".$myrow['id'];
		$resultado = DB_query($sql,$db);

		$sustitucion = "";
		if(DB_num_rows($resultado) > 0){
			$row = DB_fetch_array($resultado);

			$sustitucion = "<br>  <span style='font-size: 9.5px;'>Sutituci&oacute;n: <b>".$row['folio']."</b></span>";
        }
		$ligaSustitucion = $rootpath . "/PDFInvoice.php?Type=" . $myrow ['type'] . '&tipodocto=1&legal=' . $legaid . '&TransNo=' . $myrow ['transno'] . '&Tagref=' . $myrow ['tagref'] . '&OrderNo=' . $OrderNo.'&sustitucion=1';
		echo "<td class='texto_normal2'>" . $myrow ['branchcode'] .' - '. $myrow ['name'] . "</td>";
		echo "<td class='texto_normal2'>" . $myrow ['invtext'] . "</td>";
		echo "<td class='texto_normal2'>" . $myrow ['comments'] . "</td>";
        $SQL = "SELECT
        DISTINCT
        CONCAT(SUBSTRING(tb_administracion_contratos.id_periodo, 1, 4), ' ', cat_Months.mes) as id_periodo
        FROM salesorders
        JOIN salesorderdetails on salesorderdetails.orderno = salesorders.orderno
        JOIN tb_administracion_contratos ON tb_administracion_contratos.id_administracion_contratos = salesorderdetails.id_administracion_contratos
        JOIN cat_Months ON cat_Months.u_mes = SUBSTRING(tb_administracion_contratos.id_periodo, 5, 2)
        WHERE
        salesorders.orderno = '".$myrow['orderno']."'
        AND salesorderdetails.id_administracion_contratos <> 0
        ORDER BY tb_administracion_contratos.id_periodo ASC";
        $resultPeriodos = DB_query ( $SQL, $db );
        $periodosContratos = "";
        while ( $myrowPeriodsContratos = DB_fetch_array ( $resultPeriodos ) ) {
            if (empty($periodosContratos)) {
                $periodosContratos = $myrowPeriodsContratos['id_periodo'];
            } else {
                $periodosContratos .= ", ".$myrowPeriodsContratos['id_periodo'];
            }
        }
        echo '<td style="text-align:center">'.$periodosContratos.'</td>';
		echo "<td class='numero_normal2'>" . number_format ( $myrow ['ovamount'], 2 ) . "</td>";
		echo "<td class='numero_normal2'>" . number_format ( ($myrow ['ovamount'] + $myrow ['ovgst']), 2 ) . "</td>";
        echo "<td class='numero_normal2'>" . number_format ( ($myrow ['ovamountcancel']), 2 ) . "</td>";
		echo "<td class='numero_normal2'>" .$myrow ['userid']. ' - ' . $myrow['realname'] . "</td>";
        $flagfiscal=$myrow['flagfiscal'];
        $timbrado=$myrow['Timbrado'];
        if ($flagfiscal==1) {
        echo "<td class='numero_normal2'>" . '<img style="width:20px; height:20px; display:block;
        margin:auto;" src="images/fin.png" alt=""></a>' . "</td>";
        }
        else{
            echo "<td class='numero_normal2'></td>";
        }
		echo "<td class='numero_normal'>";
		$tmonto = $tmonto + $myrow ['ovamount'];
		$ttotal = $ttotal + ($myrow ['ovamount'] + $myrow ['ovgst']);
        $ttotalCancelado = $ttotalCancelado + $myrow ['ovamountcancel'];
		if ($tipofacturacionxtag == 1) {
			if ($InvoiceNoTAG != "") {
				echo '<a href="' . $_SESSION ['XSA'] . 'xsamanager/downloadCfdWebView?serie=' . $serie . '&folio=' . $folio . '&tipo=PDF&rfc=' . $rfc . '&key=' . $keyfact . '" target="_blank">';
				echo '<img src="images/imprimir_20x20.png" title="' . _ ( 'Imprimir Recibo Electronico' ) . '" alt=""></a>';
			}
		} elseif ($tipofacturacionxtag == 2) {
			$liga = "PDFReceiptCFD.php";
			echo "<a href='" . $liga . "?TransNo=" . $myrow ['transno'] . "' target='_blank'>";
			echo '<span class="glyphicon glyphicon-print"></span>';
			echo "</a>";
		} elseif ($tipofacturacionxtag == 3) {
			$liga = "PDFReceiptTemplate.php";
			echo "<a href='" . $liga . "?TransNo=" . $myrow ['transno'] . "' target='_blank'>";
			echo '<span class="glyphicon glyphicon-print"></span>';
			echo "</a>";
		} else {
			$query = "SELECT idXml FROM Xmls  WHERE transno=" . $myrow ['transno'] . " and type=" . $myrow ['tipo'];
			$result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
			if (DB_num_rows ( $result ) > 0) {
				$liga = "PDFInvoice.php?";
			} else {
				if (preg_match ( "/gruposervillantas/", $_SESSION ['DatabaseName'] )) {
					$liga = "PDFInvoice.php?";
				} else {
					$liga = "PDFInvoice.php?";
				}
				if (preg_match ( "/mservice/", $_SESSION ['DatabaseName'] )) {
					$liga = "PDFInvoice.php?";
				}
                if($_SESSION['ImpTiketPV']==1 and $myrow ['tipo']==12) {
                    $liga = 'PDFReceiptTicket_v2.php?';
                }
			}
			if ($tipodoctocargo == '90'){
				$liga = "PDFReceipt.php?";
			}
			echo "<a href='" . $liga . '&' . SID . "&OrderNo=" . $myrow ['order_'] . "&TransNo=" . $myrow ['transno'] . "&Type=" . $myrow ['tipo'] . "&Tagref=" . $myrow ['tagref'] . "' target='_blank'>";
			echo '<span class="glyphicon glyphicon-print"></span>';
			echo "</a>";
		}
		echo "</td>";
		echo "<td class='numero_normal'>";
		if ($tipofacturacionxtag == 2) {
			$liga = "PDFReceiptTicket.php";
			echo "<a href='" . $liga . "?TransNo=" . $myrow ['transno'] . "' target='_blank'>";
			echo "<img src='images/imprimir_20x20.png' title='" . _ ( 'Imprimir Ticket' ) . "' alt=''>";
			echo "</a>";
		}
        echo "</td>";
        $folioCorte = "0";
        $sqlValidacion2 = "SELECT 
        debtortrans.nu_foliocorte,
        debtortrans.tagref,
        debtortrans.nu_ue,
        debtortrans.type,
        debtortrans.id,
        debtortrans.ovamountcancel,
        debtortrans.ovamount,
        debtortrans.ovgstcancel,
        debtortrans.ovgst,
        custallocns.transid_allocto,
        debtortransFactura.order_
        FROM debtortrans  
        LEFT JOIN custallocns ON  debtortrans.id = custallocns.transid_allocfrom
        LEFT JOIN debtortrans debtortransFactura ON debtortransFactura.id = custallocns.transid_allocto
        Where debtortrans.type = 12 AND debtortrans.transno = '".$myrow ['transno']."'" ;
        $resultSelectVal2 = DB_query($sqlValidacion2, $db);
        
        while ($row = DB_fetch_array($resultSelectVal2)) {
            $folioCorte  = $row ['nu_foliocorte'];
        }
        $permisoCancelacionResivo = Havepermission ( $_SESSION ['UserID'], 206, $db );
        if( $permisoCancelacionResivo == 1){
            echo '<td style="text-align: center;">';
            if($folioCorte == "0"){
                echo ' <a onclick="fnModificar(\''.$myrow ['transno'].'\')"><span class="glyphicon glyphicon-remove"></span></a>';
            }
			echo ' </td>';
        }

		if ($permisoCancelacionResivo == 0) {
			echo "<td>&nbsp;</td>";
		} else {
			if (is_null ( $myrow ['u_status'] )) {
				$u_statuscortecaja = - 1;
			} else {
				$u_statuscortecaja = $myrow ['u_status'];
			}
			if ((strpos ($myrow ['invtext'],"CANCELADO") !== false ) || $u_statuscortecaja == 2) {

                if($u_statuscortecaja == 2){
                    echo "<td style='font-weight: bold;font-size: 10px; padding: 3px; text-align: center;'>"._('Corte Caja Cerrado')."</td>";
                }else{
                    echo "<td>&nbsp;</td>";
                }
			} else {
                $sqlrecibo = "
                            SELECT date_format(debtortrans.origtrandate, '%Y-%m-%d') as fecha,
                                  debtortrans.tagref
                            FROM debtortrans
                            WHERE type = 12
                            AND transno = '".$myrow ['transno']."'";
                $rerecibo = DB_query($sqlrecibo, $db);
                $mrrecibo = DB_fetch_array($rerecibo);
                $unidadrec = $mrrecibo['tagref'];
                $fecharec = $mrrecibo['fecha'];
                // Validaciones para facturación Anticipos
                $vrFlagAnt = false;
                if(!empty($myrow['transid_allocto'])){
                    $sqlSaldoAnt = "
                            SELECT *
                            FROM debtortrans
                            WHERE type = 130
                            AND ref1 = '".$myrow['transid_allocto']."'";
                    
                    debug_sql($sqlSaldoAnt, __LINE__,$debug_sql,__file__);
                    $resaldoA = DB_query($sqlSaldoAnt, $db);
                    if(DB_num_rows($resaldoA)>0){
                        while ( $rowsA = DB_fetch_array($resaldoA)) {
                            $sqlRel = " SELECT *
                                            FROM custallocns
                                            WHERE transid_allocfrom=".$rowsA['id'];
                        
                            debug_sql($sqlRel, __LINE__,$debug_sql,__file__);
                            $resRel = DB_query($sqlRel, $db);
                            if(DB_num_rows($resRel)>0){
                                $vrFlagAnt = true;
                                break;
                            }
                        }
                    }
                }
                if($vrFlagAnt == false){

                    if (($u_statuscortecaja == 0) and ($myrow ['ovamount'] != 0)  ) {
    					if ($myrow ['daytrandate'] == Date ( 'd' ) and $myrow ['monthtrandate'] == Date ( 'm' ) and $myrow ['yeartrandate'] == Date ( 'Y' ) AND $Aplicado==0 ) {
    						// echo 'entra';
    						echo "<td class='numero_normal' style='display: none;' >";
    						echo "<a href='CustomerReceiptCancel.php?delete=yes&serie=" . $serie . "&rfc=" . $rfc . "&folio=" . $folio . "&transno=" . $myrow ['transno'] . "&txtfechadesde=" . $fechadesde . "&txtfechahasta=" . $fechahasta . "&txtrecibono=" . $recibono . "&cbounidadnegocio=" . $unidadnegocio . "&keyfact=" . $keyfact . "'>";
    						echo "<img src='images/obsoleto_23x23.png' width='20' border=0 title='" . _ ( 'Cancelar Recibo' ) . ': ' . $myrow ['transno'] . "' alt=''>";
    						echo "</a></td>";
    					}elseif(Havepermission ( $_SESSION ['UserID'], 378, $db ) == 1 AND $Aplicado==0  AND $u_statuscortecaja <>2) { 
    						echo "<td class='numero_normal'>";
    						echo "<a href='CustomerReceiptCancel.php?delete=yes&serie=" . $serie . "&rfc=" . $rfc . "&folio=" . $folio . "&transno=" . $myrow ['transno'] . "&txtfechadesde=" . $fechadesde . "&txtfechahasta=" . $fechahasta . "&txtrecibono=" . $recibono . "&cbounidadnegocio=" . $unidadnegocio . "&keyfact=" . $keyfact . "'>";
    						echo "<img src='images/obsoleto_23x23.png' width='20' border=0 title='" . _ ( 'Cancelar Recibo' ) . ': ' . $myrow ['transno'] . "' alt=''>";
    						echo "</a></td>";
    					} else {
                            echo "<td>";
                            echo "&nbsp;";   
                            echo "</td>";
    					} //
    				} 
    				elseif (Havepermission ( $_SESSION ['UserID'], 378, $db ) == 1 AND $Aplicado==0  AND $u_statuscortecaja <>2) {
    					echo "<td class='numero_normal'>";
    					echo "<a href='CustomerReceiptCancel.php?delete=yes&serie=" . $serie . "&rfc=" . $rfc . "&folio=" . $folio . "&transno=" . $myrow ['transno'] . "&txtfechadesde=" . $fechadesde . "&txtfechahasta=" . $fechahasta . "&txtrecibono=" . $recibono . "&cbounidadnegocio=" . $unidadnegocio . "&keyfact=" . $keyfact . "'>";
    					echo "<img src='images/obsoleto_23x23.png' width='20' border=0 title='" . _ ( 'Cancelar Recibo' ) . ': ' . $myrow ['transno'] . "' alt=''>";
    					echo "</a></td>";
    				} else {
    					
    				}
                }else{
                    echo "<td class='numero_normal'>Documentos Relacionados </td>";
                }

			}
		}
		// echo "<td style='text-align:center;'>".$ReenviarXSA."</td>";
		if ((Havepermission ( $_SESSION ['UserID'], 1124, $db ) == 1) || $Aplicado==0) {

            if ($flagfiscal==0 || $timbrado==1) {
            $link_Regenera = "<a href='CustomerReceiptCancel.php?&Regenera=1&iddocto=" . $myrow ['id'] . "&folio=" . $myrow ['folio'] . "'
                                        title='Reimpresion'><img src='images/email_20.png' TITLE='Reimpresion' ALT='" . _ ( 'Reimpresion' ) . "'></a>";
            }
            else{
               $link_Regenera = "<a href='CustomerReceiptCancel.php?&Regenera=1&iddocto=" . $myrow ['id'] . "&folio=" . $myrow ['folio'] . "'
                                        title='Envia SAT'><img src='images/email_20.png' TITLE='Envia SAT' ALT='" . _ ( 'Reimpresion' ) . "'></a>";
            }
			
		} 
		if ((Havepermission ( $_SESSION ['UserID'], 943, $db ) == 1)  || $Aplicado==0){
			$link_modCobrador = "<a target='_blank' href='ModifcaCobrardorRecibo.php?type=" . $myrow ['tipo'] . "&transno=" . $myrow ['transno'] . "'
										title='Modificar Vendedor'><img src='images/lapiz_20x20.png' TITLE='MODIFICAR COBRADOR' ALT='" . _ ( 'Modificar Cobrador' ) . "'></a>";
		} else {
			echo '<td>&nbsp;</td>';
		}
		echo "</tr>";
	}
		echo '<tr>';
			echo '<td colspan="8" style="text-align:right;">' . _('Total') . ':&nbsp;</td>';
			echo '<td style="text-align:right;">' . number_format($tmonto,2) . '</td>';
			echo '<td style="text-align:right;">' . number_format($ttotal,2) . '</td>';
            echo '<td style="text-align:right;">' . number_format($ttotalCancelado,2) . '</td>';
			echo '<td colspan="6" ></td>';
        echo '</tr>';
        ?>
        </tbody>
        <?php
        ?>
        <div class="row"></div>
            <div align="center">
             <br>
             <a class="glyphicon glyphicon-save-file btn btn-default botonVerde" href="#" id="test" onClick="javascript:fnExcelReport();">Descargar</a>
        <br>
        <br>
        <br>
        </div>
    
        <?php
	echo "</table>";

    echo "</div>";
}

echo "<br><br>";
echo '</form>';
?>
<?php

$selectObjetoPrincipal = 0;
if (isset($_POST['selectObjetoPrincipal'])) {
    $selectObjetoPrincipal = $_POST['selectObjetoPrincipal'];
}
?>
<script type="text/javascript">
    var selectObjetoPrincipal = "<?php echo $selectObjetoPrincipal; ?>";

    // Write on keyup event of keyword input element
    $(document).ready(function(){
        if (selectObjetoPrincipal != "0") {
            fnSeleccionarDatosSelect("selectObjetoPrincipal", selectObjetoPrincipal);
        }
        $("#txtFiltroCuentas").keyup(function(){
            _this = this;
            // Show only matching TR, hide rest of them
            $.each($("#mytable tr"), function() {
                if($(this).text().toLowerCase().indexOf($(_this).val().toLowerCase()) === -1)
                $(this).hide();
                else
                $(this).show();
            });
        });
    });
</script>

<script type="text/javascript">
    function desplegarDatosCFDI(id){
    	console.log("id: "+id);
    	$.get("DatosParaSustitucionCFDI.php", {sinheader: "1",ID:id}, function(htmlexterno){
            console.log("htmlexterno: ");
            $('#modaldatosfactura').find("#datosHTML").html(htmlexterno);
            $('#modaldatosfactura').modal('show');
        });
    }
  </script>
<script>
function txtno(){
	if(document.form1.comentario.value=="Escribe un comentario...")
		{document.form1.comentario.value="";}
	}
function txt(){
	if(document.form1.comentario.value=="")
		{document.form1.comentario.value="Escribe un comentario..."}
}
</script>

<script>
function fnExcelReport() {
     var tab_text = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
     tab_text = tab_text + '<meta charset="UTF-8">';
     tab_text = tab_text + '<head><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
     tab_text = tab_text + '<x:Name>Test Sheet</x:Name>';
     tab_text = tab_text + '<x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet>';
     tab_text = tab_text + '</x:ExcelWorksheets></x:ExcelWorkbook></xml></head><body>';
     tab_text = tab_text + "<table border='1px'>";
    //get table HTML code
     tab_text = tab_text + $('#tablaRecibos').html();
     tab_text = tab_text + '</table></body></html>';
     var data_type = 'data:application/vnd.ms-excel';
     var ua = window.navigator.userAgent;
     var msie = ua.indexOf("MSIE ");
     //For IE
     if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
          if (window.navigator.msSaveBlob) {
          var blob = new Blob([tab_text], {type: "application/csv;charset=utf-8;"});
          navigator.msSaveBlob(blob, 'Recibos de pago.xls');
          }
     } 
    //for Chrome and Firefox 
    else {
     $('#test').attr('href', data_type + ', ' + encodeURIComponent(tab_text));
     $('#test').attr('download', 'Recibos de pago.xls');
    }
    }
</script>
<?php
include 'includes/footer_Index.inc';
?>