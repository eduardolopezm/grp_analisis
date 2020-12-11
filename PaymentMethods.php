<?php
/**
 * Abc métodos de pagos del SAT
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 15/10/2018
 * Fecha Modificación: 13/08/2018
 * Función para dar de alta los métodos de pago del sat para el proceso de ingresos
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 125;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

if (isset ( $_GET ['SelectedPaymentID'] ))
    $SelectedPaymentID = $_GET ['SelectedPaymentID'];
elseif (isset ( $_POST ['SelectedPaymentID'] ))
    $SelectedPaymentID = $_POST ['SelectedPaymentID'];

if (isset ( $_POST ['submit'] )) {
    $InputError = 0;

    if (trim ( $_POST ['MethodID'] ) == "") {
        $InputError = 1;
        prnMsg ( _ ( 'El código del sat no puede ser vacío' ), 'error' );
    }
    
    if (strpos ( $_POST ['MethodName'], '&' ) > 0 or strpos ( $_POST ['MethodName'], "'" ) > 0) {
        $InputError = 1;
        prnMsg ( _ ( 'El nombre no puede contener el caracter' ) . " '&' " . _ ( 'o el caracter' ) . " '", 'error' );
    }

    if (trim ( $_POST ['MethodName'] ) == "") {
        $InputError = 1;
        prnMsg ( _ ( 'El nombre no puede ser vacío' ), 'error' );
    }

    if ($_POST ['SelectedPaymentID'] != '' and $InputError != 1) {
        $sql = "SELECT count(*) 
        FROM paymentmethodssat 
        WHERE paymentid <> '" . $SelectedPaymentID . "'
        AND paymentname " . LIKE . " '" . $_POST ['MethodName'] . "'";
        $result = DB_query ( $sql, $db );
        $myrow = DB_fetch_row ( $result );
        if ($myrow [0] > 0) {
            $InputError = 1;
            prnMsg ( _ ( 'Ya existe un método de pago con el mismo nombre' ), 'error' );
        } else {
            $sql = "SELECT paymentname 
            FROM paymentmethodssat 
            WHERE paymentid = '" . $SelectedPaymentID . "'";
            $result = DB_query ( $sql, $db );
            if (DB_num_rows ( $result ) != 0) {
                $myrow = DB_fetch_row ( $result );
                $OldName = $myrow [0];

                $sql = "UPDATE paymentmethodssat
                SET paymentname = '" . $_POST ['MethodName'] . "',
                receiptuse = '" . $_POST ['receiptuse'] . "',
                invoiceuse = '" . $_POST ['invoiceuse'] . "',
                sn_tesoreriaTipoPago = '" . $_POST ['sn_tesoreriaTipoPago'] . "'
                WHERE paymentid = '" . $SelectedPaymentID . "'";
            } else {
                $InputError = 1;
                prnMsg ( _ ( 'El método de pago no existe' ), 'error' );
            }
        }
        $msg = _('El Registro ' . $SelectedPaymentID . ' ha sido actualizado');
        $ErrMsg = _ ( 'No se pudo actualizar el metodo de pago' );
    } elseif ($InputError != 1) {
        $sql = "SELECT count(*) FROM paymentmethodssat 
        WHERE paymentid = '" . $_POST ['MethodID'] . "'";
        $result = DB_query ( $sql, $db );
        $myrow = DB_fetch_row ( $result );
        if ($myrow [0] > 0) {
            $InputError = 1;
            prnMsg ( _ ( 'El código del sat ya se existe' ), 'error' );
        } else {
            $sql = "INSERT INTO paymentmethodssat (
            paymentid, 
            paymentname, 
            receiptuse,
            invoiceuse,
            sn_tesoreriaTipoPago)
            VALUES (
            '" . $_POST ['MethodID'] . "',
            '" . $_POST ['MethodName'] . "',
            '" . $_POST ['receiptuse'] . "',
            '" . $_POST ['invoiceuse'] . "',
            '" . $_POST ['sn_tesoreriaTipoPago'] . "'
            )";
        }
        $msg = _('El Registro' . $_POST["MethodID"] . ' ha sido creado');
        $ErrMsg = _ ( 'No pudo insertartse metodo de pago' );
    }
    
    if ($InputError != 1) {
        $result = DB_query ( $sql, $db, $ErrMsg );
        prnMsg ( $msg, 'success' );

        unset ( $SelectedPaymentID );
        unset ( $_GET ['SelectedPaymentID'] );
        unset ( $_GET ['delete'] );
        unset ( $_POST ['SelectedPaymentID'] );
        unset ( $_POST ['MethodID'] );
        unset ( $_POST ['MethodName'] );
        unset ( $_POST ['receiptuse'] );
        unset ( $_POST ['invoiceuse'] );
        unset ( $_POST ['sn_tesoreriaTipoPago'] );
    }
} elseif (isset ( $_GET ['delete'] )) {
    $sql = "SELECT paymentname FROM paymentmethodssat 
    WHERE paymentid = '" . $SelectedPaymentID . "'";
    $result = DB_query ( $sql, $db );
    if (DB_num_rows ( $result ) == 0) {
        prnMsg ( _ ( 'El método de pago no existe' ), 'error' );
    } else {
        $myrow = DB_fetch_row ( $result );
        $OldMeasureName = $myrow [0];
        $sql = "SELECT COUNT(*) 
        FROM tb_debtortrans_forma_pago 
        WHERE ln_paymentid = '".$SelectedPaymentID."'";
        $result = DB_query ( $sql, $db );
        $myrow = DB_fetch_row ( $result );
        if ($myrow [0] > 0) {
            prnMsg(_('No se puede eliminar '.$SelectedPaymentID.' tiene '.$myrow[0].' transacciones generadas'),'error');
        } else {
            $sql = "DELETE FROM paymentmethodssat 
            WHERE paymentid = '" . $SelectedPaymentID . "'";
            $result = DB_query ( $sql, $db );
            prnMsg( _('El Registro ' . $SelectedPaymentID . ' ha sido eliminado') ,'success');
        }
    }
    unset ( $SelectedPaymentID );
    unset ( $_GET ['SelectedPaymentID'] );
    unset ( $_GET ['delete'] );
    unset ( $_POST ['SelectedPaymentID'] );
    unset ( $_POST ['MethodID'] );
    unset ( $_POST ['MethodName'] );
    unset ( $_POST ['receiptuse'] );
    unset ( $_POST ['invoiceuse'] );
    unset ( $_POST ['sn_tesoreriaTipoPago'] );
}

if (! isset ( $SelectedPaymentID )) {
    $sql = "SELECT paymentid,
    paymentname,
    receiptuse,
    invoiceuse,
    sn_tesoreriaTipoPago
    FROM paymentmethodssat
    ORDER BY paymentid ASC, paymentname ASC";
    $ErrMsg = _ ( 'Could not get payment methods because' );
    $result = DB_query ( $sql, $db, $ErrMsg );
    
    echo "<table class='table table-striped table-bordered'>
    <tr class='tableHeaderVerde'>
    <th style='text-align: center;'>" . _ ( 'Código SAT' ) . "</th>
    <th style='text-align: center;'>" . _ ( 'Nombre SAT' ) . "</th>
    <th style='text-align: center;'>" . _ ( 'Usar en pases de cobro' ) . "</th>
    <th style='text-align: center;'>" . _ ( 'Usar en recibos' ) . "</th>
    <th style='text-align: center;'>" . _ ( 'Usar en tesoria' ) . "</th>
    <th style='text-align: center;'>" . _ ( 'Modificar' ) . "</th>
    <th style='text-align: center;'>" . _ ( 'Eliminar' ) . "</th>
    </tr>";
    
    while ( $myrow = DB_fetch_array ( $result ) ) {
        echo '<tr class="EvenTableRows">';
        echo '<td style="text-align:center;">' . $myrow ['paymentid'] . '</td>';
        echo '<td style="text-align:center;">' . $myrow ['paymentname'] . '</td>';
        echo '<td style="text-align:center;">' . ($myrow ['invoiceuse'] ? _ ( 'Si' ) : _ ( 'No' )) . '</td>';
        echo '<td style="text-align:center;">' . ($myrow ['receiptuse'] ? _ ( 'Si' ) : _ ( 'No' )) . '</td>';
        echo '<td style="text-align:center;">' . ($myrow ['sn_tesoreriaTipoPago'] ? _ ( 'Si' ) : _ ( 'No' )) . '</td>';
        echo '<td style="text-align:center;"><a href="' . $_SERVER ['PHP_SELF'] . '?' . SID . '&SelectedPaymentID=' . $myrow ['paymentid'] . '"><span class="glyphicon glyphicon-edit"></span></a></td>';
        // echo '<td style="text-align:center;"><a href="' . $_SERVER ['PHP_SELF'] . '?' . SID . '&SelectedPaymentID=' . $myrow ['paymentid'] . '&delete=1"><span class="glyphicon glyphicon-trash"></span></a></td>';
        echo "<td style='text-align: center;'>
            <a href='".$_SERVER['PHP_SELF']."?SelectedPaymentID=".$myrow['paymentid']."&delete=yes' onclick=\"return confirm('" . _("Desea eliminar ".$myrow['paymentid']." - ".$myrow['paymentname']." ?") . "');\"><span class='glyphicon glyphicon-trash'></span></a>
        </td>";
        echo '</tr>';
    }
    echo '</table>';
}
    
echo "<form method='post' action=" . $_SERVER ['PHP_SELF'] . '?' . SID . '>';

$readonly = '';
if (isset ( $SelectedPaymentID )) {
    $readonly = 'readonly="true"';

    $sql = "SELECT paymentid,
    paymentname,
    receiptuse,
    invoiceuse,
    sn_tesoreriaTipoPago
    FROM paymentmethodssat
    WHERE paymentid = '".$SelectedPaymentID."'";

    $result = DB_query ( $sql, $db );
    if (DB_num_rows ( $result ) == 0) {
        prnMsg ( _ ( 'Could not retrieve the requested payment method, please try again.' ), 'warn' );
        unset ( $SelectedPaymentID );
    } else {
        $myrow = DB_fetch_array ( $result );
        $_POST ['MethodID'] = $myrow ['paymentid'];
        $_POST ['MethodName'] = $myrow ['paymentname'];
        $_POST ['receiptuse'] = $myrow ['receiptuse'];
        $_POST ['invoiceuse'] = $myrow ['invoiceuse'];
        $_POST ['sn_tesoreriaTipoPago'] = $myrow ['sn_tesoreriaTipoPago'];
        
        echo "<input type=hidden name='SelectedPaymentID' VALUE='" . $SelectedPaymentID . "'>";
    }
}

if (!isset($_POST['MethodID'])) {
    $_POST['MethodID'] = '';
}

if (!isset($_POST['MethodName'])) {
    $_POST['MethodName'] = '';
}

if (!isset($_POST['receiptuse'])) {
    $_POST['receiptuse'] = 0;
}

if (!isset($_POST['invoiceuse'])) {
    $_POST['invoiceuse'] = 0;
}

if (!isset($_POST['sn_tesoreriaTipoPago'])) {
    $_POST['sn_tesoreriaTipoPago'] = 0;
}

?>
<div align="left">
    <!--Panel Busqueda-->
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title row">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne" style="margin-left: 20px;">
                Información Agregar/Modificar
                </a>
            </h4>
        </div>
        <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
                <div class="col-md-4 col-xs-12">
                    <component-text-label label="Código SAT:" id="MethodID" name="MethodID" placeholder="Código SAT" title="Código SAT" value="<?php echo $_POST['MethodID']; ?>" maxlength="2" <?php echo $readonly; ?>></component-text-label>
                </div>
                <div class="col-md-4 col-xs-12">
                    <component-text-label label="Nombre:" id="MethodName" name="MethodName" placeholder="Nombre" title="Nombre" value="<?php echo $_POST['MethodName']; ?>" maxlength="20"></component-text-label>
                </div>
                <div class="col-md-4 col-xs-12">
                    <div class="form-inline row">
                        <div class="col-md-3">
                            <span><label>Usar en pases de cobro: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="invoiceuse" name="invoiceuse" class="form-control selectGeneral">
                                <option value="0" <?php echo ($_POST['invoiceuse'] == 0 ? "selected" : ""); ?>>No</option> 
                                <option value="1" <?php echo ($_POST['invoiceuse'] == 1 ? "selected" : ""); ?>>Si</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row"></div>
                <div class="col-md-4 col-xs-12">
                    <div class="form-inline row">
                        <div class="col-md-3">
                            <span><label>Usar en recibos: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="receiptuse" name="receiptuse" class="form-control selectGeneral">
                                <option value="0" <?php echo ($_POST['receiptuse'] == 0 ? "selected" : ""); ?>>No</option> 
                                <option value="1" <?php echo ($_POST['receiptuse'] == 1 ? "selected" : ""); ?>>Si</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-xs-12">
                    <div class="form-inline row">
                        <div class="col-md-3">
                            <span><label>Usar en tesoria: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="sn_tesoreriaTipoPago" name="sn_tesoreriaTipoPago" class="form-control selectGeneral">
                                <option value="0" <?php echo ($_POST['sn_tesoreriaTipoPago'] == 0 ? "selected" : ""); ?>>No</option> 
                                <option value="1" <?php echo ($_POST['sn_tesoreriaTipoPago'] == 1 ? "selected" : ""); ?>>Si</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div align="center">
    <component-button type="submit" id="submit" name="submit" class="glyphicon glyphicon-floppy-disk" value="Guardar"></component-button>
    <?php if (isset($SelectedPaymentID)): ?>
        <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="PaymentMethods.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
    <?php endif ?>
    <br><br>
</div>
<?php

echo '</form>';

include 'includes/footer_Index.inc';
