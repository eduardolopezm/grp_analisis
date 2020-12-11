<?php
/**
 * Alta de Factura de Compra
 *
 * @category Proceso
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/09/2017
 * Fecha Modificación: 01/09/2017
 * Proceso para la alta de factura de compra
 */
////
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// ini_set('log_errors', 1);
// ini_set('error_log', dirname(__FILE__) . '/<error_log class="txt"></error_log>');

$funcion=2313;
$PageSecurity = 5;
include('includes/DefineSuppTransClassFacComV1.php');
include('includes/session.inc');
$title = _('Registro de factura');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
include "includes/SecurityUrl.php";

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$decimalesTipoCambio = 8;

$mensaje_emergente= "";
$procesoterminado= 0;

if (empty($_SESSION['TCDecimals']) == false) {
    $decimalesTipoCambio = $_SESSION['TCDecimals'];
}

if (isset($_POST['fechacambio'])) {
    //$fecha           = $_POST['FechaPoliza'];
    //$fechacompleta = $_POST['FechaPoliza'].date("H:i:s");
    $fechacambio   = $_POST['fechacambio'];
    $date_local = date_create($_POST['fechacambio']);
    $_SESSION['SuppTrans']->TranDate = date_format($date_local, 'd/m/Y');
} else {
    if (isset($_GET['year'])) {
        $ToYear=$_GET['year'];
    } else if (isset($_POST['ToYear'])) {
        $ToYear=$_POST['ToYear'];
    } else {
        $ToYear=date('Y');
    }
        
    if (isset($_GET['mes'])) {
        $ToMes=$_GET['mes'];
    } else if (isset($_POST['ToMes'])) {
        $ToMes=$_POST['ToMes'];
    } else {
        $ToMes=date('m');
    }
      
    if (isset($_GET['dia'])) {
        $ToDia=$_GET['dia'];
    } else if (isset($_POST['ToDia'])) {
        $ToDia=$_POST['ToDia'];
    } else {
        $ToDia=date('d');
    }
    
    $_SESSION['SuppTrans']->TranDate = $ToDia.'/'.$ToMes.'/'.$ToYear;
    $fechacambio=$ToYear.'-'.$ToMes.'-'.$ToDia;
    if ($fechacambio="--") {
        $fechacambio=date('Y-m-d');
    }
}

if (isset($_GET['total'])) {
    $_SESSION['totald']=$_GET['total'];
} else if (isset($_POST['total'])) {
    $_SESSION['totald']=$_POST['total'];
}

echo '<input type="hidden" name="total" value="'.$_SESSION['totald'].'" >';
if (isset($_POST['unidadnegocio'])) {
    $unidadnegocio = $_POST['unidadnegocio'];
} else {
    $unidadnegocio = $_GET['unidadnegocio'];
}

$legalid = "";
if (isset($_POST['legalid'])) {
    $legalid = $_POST['legalid'];
} else if (isset($_GET['legalid'])) {
    $legalid = $_GET['legalid'];
}

if ($legalid=='' and $unidadnegocio!='') {
    $sql='SELECT legalid FROM tags WHERE tagref="'.$unidadnegocio.'" ';
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    $legalid=$myrow[0];
}
if (!isset($_SESSION['SuppTrans']->SupplierName)) {
    $sql='SELECT suppname FROM suppliers WHERE supplierid="'.$_GET['SupplierID'].'"';
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    $SupplierName=$myrow[0];
} else {
    $SupplierName=$_SESSION['SuppTrans']->SupplierName;
}

if (isset($_SESSION['TruncarDigitos'])) {
    $digitos=$_SESSION['TruncarDigitos'];
} else {
    $digitos=4;
}

$termindicator=0;
// variable goodreceived para saber si arreglo se inicializo en recepcion de productos
if (isset($_GET['SupplierID']) and $_GET['SupplierID']!='' and !isset($_GET['GoodRecived'])) {
    if (isset($_SESSION['SuppTrans'])) {
        unset($_SESSION['SuppTrans']->GRNs);
        unset($_SESSION['SuppTrans']->GLCodes);
        unset($_SESSION['SuppTrans']);
    }
    if (isset($_SESSION['SuppTransTmp'])) {
        unset($_SESSION['SuppTransTmp']->GRNs);
        unset($_SESSION['SuppTransTmp']->GLCodes);
        unset($_SESSION['SuppTransTmp']);
    }
    $_SESSION['SuppTrans'] = new SuppTrans;
    
     $sql = "SELECT suppliers.suppname,
            suppliers.supplierid,
            paymentterms.terms,
                        paymentterms.termsindicator,
            paymentterms.daysbeforedue,
            paymentterms.dayinfollowingmonth,
            suppliers.currcode,
            currencies.rate AS exrate,
            suppliers.taxgroupid,
            suppliers.typeid,
            taxgroups.taxgroupdescription
        FROM suppliers,
            taxgroups,
            currencies,
            paymentterms,
            taxauthorities
        WHERE suppliers.taxgroupid=taxgroups.taxgroupid
        AND suppliers.currcode=currencies.currabrev
        AND suppliers.paymentterms=paymentterms.termsindicator
        AND suppliers.supplierid = '" . $_GET['SupplierID'] . "'";
     
    $ErrMsg = _('The supplier record selected') . ': ' . $_GET['SupplierID'] . ' ' ._('cannot be retrieved because');
    $DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    $myrow = DB_fetch_array($result);
    
    $_SESSION['SuppTrans']->SupplierName = $myrow['suppname'];
    $_SESSION['SuppTrans']->TermsDescription = $myrow['terms'];
    $_SESSION['SuppTrans']->CurrCode = $myrow['currcode'];
    $_SESSION['SuppTrans']->ExRate = $myrow['exrate'];
    if ($_SESSION['SuppTrans']->CurrCode!=$_SESSION['CountryOfOperation']) {
        $_SESSION['SuppTrans']->ExRate=GetCurrencyRateByDate($fechacambio, $_SESSION['SuppTrans']->CurrCode, $db);
    }
    $_SESSION['SuppTrans']->TaxGroup = $myrow['taxgroupid'];
    $_SESSION['SuppTrans']->TaxGroupDescription = $myrow['taxgroupdescription'];
    $_SESSION['SuppTrans']->SupplierID = $myrow['supplierid'];
        $_SESSION['SuppTrans']->DueDate=$myrow['termsindicator'];
    if ($myrow['daysbeforedue'] == 0) {
         $_SESSION['SuppTrans']->Terms = '1' . $myrow['dayinfollowingmonth'];
    } else {
         $_SESSION['SuppTrans']->Terms = '0' . $myrow['daysbeforedue'];
    }
    $_SESSION['SuppTrans']->SupplierID = $_GET['SupplierID'];
    
    $typesupplier=$myrow['typeid'];
    $_SESSION['SuppTrans']->LocalTaxProvince = 1;
    $_SESSION['SuppTrans']->GetTaxesOthers();
    $_SESSION['SuppTrans']->GLLink_Creditors = $_SESSION['CompanyRecord']['gllink_creditors'];
    $_SESSION['SuppTrans']->GRNAct = $_SESSION['CompanyRecord']['grnact'];
    $accountxtype=SupplierAccount($typesupplier, 'gl_accountsreceivable', $db);
    $_SESSION['SuppTrans']->CreditorsAct = $accountxtype;
    //$_SESSION['SuppTrans']->CreditorsAct = $_SESSION['CompanyRecord']['creditorsact'];
    $_SESSION['SuppTrans']->InvoiceOrCredit = 'Invoice';
} elseif (!isset($_SESSION['SuppTrans'])) {
    prnMsg(_('Debe seleccionar el Proveedor para realizar una Factura'), 'warn');
    echo '<component-button type="submit" id="btnRegresar" name="btnRegresar" class="glyphicon glyphicon-share-alt" value="Regresar" onclick="fnRegresarPanel(); return false;"></component-button>';
    include('includes/footer_Index.inc');
    exit();
}

if (isset($_POST['ExRate'])) {
    if (strlen($_POST['ToMes']) == 1) {
        $mes = '0'.$_POST['ToMes'];
    } else {
        $mes = $_POST['ToMes'];
    }
    if (strlen($_POST['ToDia']) == 1) {
        $dia = '0'.$_POST['ToDia'];
    } else {
        $dia  = $_POST['ToDia'];
    }
    
    $anio = $_POST['ToYear'];
    //$fechacambio=$ToYear.'-'.$ToMes.'-'.$ToDia;
    
    if ($_POST['CurrAbrevHandle'] != $_SESSION['SuppTrans']->CurrCode) {
        $_SESSION['SuppTrans']->CurrCode=$_POST['CurrAbrevHandle'];
        if ($_SESSION['SuppTrans']->CurrCode==$_SESSION['CountryOfOperation']) {
            $_POST['ExRate']=1;
        } else {
            $ratetipo=GetCurrencyRateByDate($fechacambio, $_SESSION['SuppTrans']->CurrCode, $db);
            
            $_POST['ExRate']=1/$ratetipo;
            $_SESSION['SuppTrans']->ExRate=$_POST['ExRate'];
            
            if (isset($_POST['ChangeTC'])) {
                if ($_POST['ExRate']>1) {
                    $_POST['ExRate']=1/$_POST['ExRate'];
                } else {
                    $_POST['ExRate']=$_SESSION['SuppTrans']->ExRate;
                }
            } else {
                $_POST['ExRate']=$_SESSION['SuppTrans']->ExRate;
            }
        }
    } else {
        if ($_SESSION['SuppTrans']->CurrCode==$_SESSION['CountryOfOperation']) {
            $_POST['ExRate']=1;
        } else {
            if (isset($_POST['ChangeTC'])) {
                if ($_POST['ExRate']>1) {
                    $_POST['ExRate']=1/$_POST['ExRate'];
                }
            }
        }
    }
    
    $_SESSION['SuppTrans']->ExRate = $_POST['ExRate'];
    $_SESSION['SuppTrans']->Comments = $_POST['Comments'];
    //$_SESSION['SuppTrans']->TranDate = $dia.'/'.$mes.'/'.$_POST['ToYear'] ;

    //-----------------------Modificar---------------------------
    //  echo '<br>Supptrans: '.$_SESSION['SuppTrans']->Terms;
    //        echo '<br>Dia: '.$dia;
    //        echo '<br>Mes: '.$mes;
    //        echo '<br>Anio: '.$anio;
    if (ltrim(substr($_SESSION['SuppTrans']->Terms, 0, 1), '0')=='1') {
        $_SESSION['SuppTrans']->DueDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, (int)$mes+1, ltrim(substr($_SESSION['SuppTrans']->Terms, 1), '0'), (int) $anio));
    } else {
        $_SESSION['SuppTrans']->DueDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, (int) $mes, (int) $dia + (int) ltrim(substr($_SESSION['SuppTrans']->Terms, 1), '0'), (int)$anio));
    }
    //        echo '<br>Due date: '.$_SESSION['SuppTrans']->DueDate;
    //--------------------------------
    /*
        //
        if (substr( $_SESSION['SuppTrans']->Terms,0,1)=='1') {  
            $_SESSION['SuppTrans']->DueDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')+1, substr( $_SESSION['SuppTrans']->Terms,1),Date('y')));
        } else {
            $_SESSION['SuppTrans']->DueDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d') + (int) substr( $_SESSION['SuppTrans']->Terms,1),Date('y')));
        } 
    */  //--------------------------------
    
    $_SESSION['SuppTrans']->SuppReference = $_POST['SuppReference'];
    
    $_SESSION['SuppTrans']->SuppReferenceFiscal = $_POST['SuppReferenceFiscal'];
    
    //echo '<br><pre>GRNs:'.print_r($_SESSION['SuppTrans']->GRNs);
    //echo '<br><pre>OvAmount:'.print_r($_SESSION['SuppTrans']->OvAmount);
    if ($_SESSION['SuppTrans']->GLLink_Creditors == 1) {
        /*The link to GL from creditors is active so the total should be built up from GLPostings and GRN entries
        if the link is not active then OvAmount must be entered manually. */
        $_SESSION['SuppTrans']->OvAmount = 0; /* for starters */
        if (count($_SESSION['SuppTrans']->GRNs) > 0) {
            foreach ($_SESSION['SuppTrans']->GRNs as $GRN) {
                $_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + (($GRN->This_QuantityInv * $GRN->ChgPrice) * (1-($GRN->Desc1/100)) * (1-($GRN->Desc2/100)) * (1-($GRN->Desc3/100)));
            }
        }
        if (count($_SESSION['SuppTrans']->GLCodes) > 0) {
            foreach ($_SESSION['SuppTrans']->GLCodes as $GLLine) {
                $_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + $GLLine->Amount;
            }
        }
        if (count($_SESSION['SuppTrans']->Shipts) > 0) {
            foreach ($_SESSION['SuppTrans']->Shipts as $ShiptLine) {
                $_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + $ShiptLine->Amount;
            }
        }
        $_SESSION['SuppTrans']->OvAmount = round($_SESSION['SuppTrans']->OvAmount, $digitos);
    } else {
         $_SESSION['SuppTrans']->OvAmount = round($_POST['OvAmount'], $digitos);
    }
    //prnMsg(_('Monto Total ').$_SESSION['SuppTrans']->OvAmount, 'warn');
    //echo '<pre>OvAmoun 3:'.$_SESSION['SuppTrans']->OvAmount;
}

if ($_SESSION['UserID']=='admin') {
    //echo "<pre>";
    //print_r($_SESSION['SuppTrans']);
}

$sql='SELECT taxid FROM suppliers WHERE supplierid="'.$_SESSION['SuppTrans']->SupplierID.'"';
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$supplierRFC=$myrow[0];

if (!isset($_POST['PostInvoice'])) {
    if (isset($legalid) and $legalid != '*') {
        if (isset($_POST['GRNS'])) {
                $enc = new Encryption;
                $url = "&moneda=>".$_POST['CurrAbrevHandle']."&unidadnegocio=>".$unidadnegocio
                ."&legalid=>".$_POST['legalid']."&dia=>".$_POST['ToDia']."&mes=>".$_POST['ToMes']."&year=>".$_POST['ToYear'];
                $url = $enc->encode($url);
                $liga= "URL=" . $url;
                echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/SuppInvGRNs.php?" . $liga . "'>";
                echo '<div class="centre">' .
                "<a href='".$rootpath."/SuppInvGRNs.php?'>" . _('Click aquí para continuar') . '</a></div><br>';
                exit();
        }
        if (isset($_POST['Shipts']) and $_POST['Shipts'] == _('Alta contra Embarque')) {
                echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/SuppShiptChgs.php?" . SID . "&unidadnegocio=" . $unidadnegocio . "&legalid=".$_POST['legalid']."&dia=". $_POST['ToDia'] ."&mes=". $_POST['ToMes'] ."&year=". $_POST['ToYear'] ."'>";
                echo '<div class="centre">' .
                "<a href='" . $rootpath . "/SuppShiptChgs.php?" . SID . "'>" . _('Click aquí para continuar') . '</a></div><br>';
                exit;
        }
        if (isset($_POST['GL']) and $_POST['GL'] == _('Captura Movimientos Contables')) {
                echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/SuppTransGLAnalysis.php?" . SID . "&unidadnegocio=" . $unidadnegocio . "&legalid=".$_POST['legalid']."&dia=". $_POST['ToDia'] ."&mes=". $_POST['ToMes'] ."&year=". $_POST['ToYear'] ."&legalid=$legalid'>";
                echo '<div class="centre">' .
                "<a href='" . $rootpath . "/SuppTransGLAnalysis.php?" . SID . "&unidadnegocio=" . $unidadnegocio . "&legalid=$legalid'>" . _('click here') . '</a> ' . _('Click aquí para continuar') . '.</div><br>';
                exit;
        }
    } else {
        echo "<br><div class='centre'>* Seleccione la Dependencia para opciones de recibir contra productos o Movimientos Contables!</div>";
    }
    // "</th><th>" . _('Moneda') .
    // </th><th>" . _('Impuestos')
    
    echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . " enctype=multipart/form-data>";
    echo "<form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post name=form1>";

    $sqlWhere = "";
    if (trim($unidadnegocio) != '' && trim($unidadnegocio) != '0') {
        $sqlWhere = " AND t.tagref = '".$unidadnegocio."' ";
    }
    $sql = "SELECT t.tagref, 
    CONCAT(t.tagref,' - ',t.tagdescription) as tagdescription, 
    t.tagdescription, 
    legalbusinessunit.legalid,
    legalbusinessunit.legalname
    FROM sec_unegsxuser u,tags t 
    JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
    WHERE u.tagref = t.tagref
    and u.userid = '" . $_SESSION['UserID'] . "'
    and (t.legalid = '".$legalid."' OR '*'= '".$legalid."') 
    ".$sqlWhere."
    ORDER BY legalbusinessunit.legalid,
    legalbusinessunit.legalname,
    t.tagdescription";
    $LocnResult = DB_query($sql, $db);
    ?>
<!--Panel Busqueda-->
<div class="panel panel-default" style="display: none;">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelOrdenesCompra" aria-expanded="true" aria-controls="collapseOne">
            <b>Información</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelOrdenesCompra" name="PanelOrdenesCompra" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">
        
        <!--Izquierda-->
        <div class="col-md-4 text-left">
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>UR: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="unidadnegocio" name="unidadnegocio" class="form-control unidadnegocio">
                        <?php if (DB_num_rows($LocnResult) > 1) { ?>
                            <option selected value='0'>Seleccionar...</option>
                        <?php } ?>
                        <?php
                        while ($LocnRow=DB_fetch_array($LocnResult)) {
                            echo "<option value='" . $LocnRow['tagref'] . "'";
                            if (($LocnRow['tagref'])==($unidadnegocio)) {
                                echo 'selected';
                            }
                            echo ">" . $LocnRow['tagdescription']."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <!--Medio-->
        <div class="col-md-4 text-left">
            <component-label-text label="Proveedor:" id="txtLabelProveedor" name="txtLabelProveedor" 
            value="<?php echo $_SESSION['SuppTrans']->SupplierID . ' - ' . $_SESSION['SuppTrans']->SupplierName; ?>"></component-label-text>
        </div>
        <!--Derecha-->
        <div class="col-md-4 text-left">
            <component-label-text label="Condiciones de Pago:" id="txtLabelCondicionesPago" name="txtLabelCondicionesPago"
            value="<?php echo $_SESSION['SuppTrans']->TermsDescription; ?>"></component-label-text>
        </div>
        <div class="col-md-12" align="center">
            <?php
            if (isset($legalid) and $legalid != '*') {
                echo '<component-button type="submit" id="GRNS" name="GRNS" class="glyphicon glyphicon-plus" value="Agregar Factura"></component-button>';
                echo "&nbsp;&nbsp;";
                if (count($_SESSION['SuppTrans']->GRNs) == 0) {
                    echo '<component-button type="submit" id="btnRegresar" name="btnRegresar" class="glyphicon glyphicon-share-alt" value="Regresar" onclick="fnRegresarPanel(); return false;"></component-button>';
                }
            } else {
                echo "<div class='centre'>Seleccionar UR para realizar el Proceso de Recepción</div>";
            }
            ?>
        </div>

      </div>
    </div>
</div>
    <?php
    echo '<select name="legalid" id="" class="" style="display:none;">';
    $SQL = "SELECT legalbusinessunit.legalid, CONCAT(legalbusinessunit.legalid, ' - ', legalbusinessunit.legalname) as legalname 
            FROM sec_unegsxuser u, tags t 
            JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid 
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' 
            GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY legalbusinessunit.legalid ";
    $result=DB_query($SQL, $db);
    
    echo "<option selected value='*'>Seleccionar Todas...</option>";
    
    while ($myrow=DB_fetch_array($result)) {
        if (isset($legalid) and $legalid==$myrow["legalid"]) {
            echo '<option selected value=' . $myrow['legalid'] . '>' .$myrow['legalname'].'</option>';
        } else {
            echo '<option value=' . $myrow['legalid'] . '>' .$myrow['legalname'].'</option>';
        }
    }
    echo '</select>';

    echo "<input type=hidden name=CurrAbrevHandle value='".$_SESSION['SuppTrans']->CurrCode."'>";

    echo"<input type=hidden class='number' size=11 maxlength=15 name='ExRate' VALUE=" . round($_SESSION['SuppTrans']->ExRate, $decimalesTipoCambio) . '>';
    
    // <th>" . _('Desc 1') . "</th>
    // <th>" . _('Desc 2') . "</th>
    // <th>" . _('Desc 3') . "</th>
    // <th colspan=2 style='display: none;'>" . _('Impuestos') . "</th>
    // print_r($_SESSION['SuppTrans']->GRNs);
    if (count($_SESSION['SuppTrans']->GRNs)>0) {
        echo '<div align="center"><h4>Detalle de factura</h4></div>';
        echo '<table class="table table-bordered" style="margin:0 auto" cellpadding=2 border=1>';
        // <th>#</th>
        $tableheader = "<tr class='header-verde'>
                <th style='text-align: center;'>" . _('UE') . "</th>
                <th style='text-align: center;'>" . _('Código') . "</th>
                <th style='text-align: center;'>" . _('Descripción') . "</th>
                <th style='text-align: center;'>" . _('Recepción') . "</th>
                <th style='text-align: center;'>" . _('Cantidad') . "</th>
                <th style='text-align: center;'>" . _('Precio') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</th>
                <th style='text-align: center;'>" . _('Total') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th></tr>';
        echo $tableheader;
        $TotalGRNValue = 0;
        $totaltaxvalue = 0;
        $arraytaxs= array();

        $unidadEjecutoraAnt = "";
        $unidadEjecutoraNameAnt = "";
        $unidadEjecutoraSeleccionadas = "";
        foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN) {
            //Buscar el iva del producto
            $SQLsearchatax="SELECT stockmaster.taxcatid, taxcategories.taxvalue, taxcategories.taxcatname
            FROM stockmaster
            JOIN taxcategories ON taxcategories.taxcatid=stockmaster.taxcatid
            WHERE stockid='".$EnteredGRN->ItemCode."';";
            $rowssearchtax=  DB_fetch_array(DB_query($SQLsearchatax, $db));
            $totaltaxvalue+=(($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)))*$rowssearchtax['taxvalue'];
            $arraytaxs[]=$rowssearchtax['txvalue'];

            // <td style="text-align:right;">'. $EnteredGRN->Desc1 .'%</td>
            // <td style="text-align:right;">'. $EnteredGRN->Desc2 .'%</td>
            // <td style="text-align:right;">'. $EnteredGRN->Desc3 .'%</td>
            // <td style="text-align:left; display: none;">'. $rowssearchtax['taxcatname'] .'</td>
            // <td style="text-align:right; display: none;">$ '.number_format((($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)))*$rowssearchtax['taxvalue'], $digitos).'</td>
            
            if ($unidadEjecutoraAnt == '') {
                $unidadEjecutoraSeleccionadas .= $EnteredGRN->unidadEjecutora;
                $unidadEjecutoraAnt = $EnteredGRN->unidadEjecutora;
                $unidadEjecutoraNameAnt = $EnteredGRN->unidadEjecutoraName;
                $mensaje_emergente .= '<p>Unidad Ejecutora Seleccionada</p>';
                $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$EnteredGRN->unidadEjecutora.' - '.$EnteredGRN->unidadEjecutoraName.'</p>';
            } else if ($unidadEjecutoraAnt != '' && $unidadEjecutoraAnt != $EnteredGRN->unidadEjecutora) {
                if (strpos($unidadEjecutoraSeleccionadas, $EnteredGRN->unidadEjecutora) === false) {
                    $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$EnteredGRN->unidadEjecutora.' - '.$EnteredGRN->unidadEjecutoraName.'</p>';
                }
                $unidadEjecutoraSeleccionadas .= " , ".$EnteredGRN->unidadEjecutora;
                $procesoterminado = 2;
            }

            $_SESSION['SuppTrans']->unidadEjecutoraGeneral = $EnteredGRN->unidadEjecutora;

            // <td>' . $EnteredGRN->GRNNo . '</td>' .
            echo '<tr>
            <td>' . $EnteredGRN->unidadEjecutora . ' - ' . $EnteredGRN->unidadEjecutoraName . '</td>'
            . '<td style="text-align: center;">' . $EnteredGRN->ItemCode .
            '</td>
            <td>' . $EnteredGRN->ItemDescription . '</td>' .
            '<td style="text-align: center;">'.$EnteredGRN->deliverydate.'</td>'.
            '<td style="text-align:center;">'.
            ($EnteredGRN->This_QuantityInv) . '</td><td style="text-align:right;">$ ' .
            number_format($EnteredGRN->ChgPrice, $_SESSION['DecimalPlaces'], '.', ',') . '</td>
            <td style="text-align:right;">$ ' .
            number_format(($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)), $_SESSION['DecimalPlaces'], '.', ',') . '</td>
            </tr>';
            $TotalGRNValue = $TotalGRNValue + (($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)));
        }

        $mensajeUR = "";
        if ($procesoterminado != 0) {
            $mensajeUR = "Recepciones con Unidad Ejecutora diferentes. No se puede realizar la Factura";
        }

        echo '<tr><td colspan="5" align="right" style="color: red;">'.$mensajeUR.'</td>
                        <td style="text-align:right; display: none;"><b>$'.  number_format($totaltaxvalue, $_SESSION['DecimalPlaces'], '.', ',').'</td>
            <td style="text-align:right;"><b>$' . number_format($TotalGRNValue, $_SESSION['DecimalPlaces'], '.', ',') . '</U></font></td></tr>';
        echo '</table>';
    }
    if (count($_SESSION['SuppTrans']->Shipts) > 0) {
        echo '<table style="text-align:center; margin:0 auto" cellpadding=2>';
        $TableHeader = "<tr><th>" . _('Shipment') . "</th>
                <th>" . _('Amount') . '</th></tr>';
        echo $TableHeader;
        $TotalShiptValue = 0;
        foreach ($_SESSION['SuppTrans']->Shipts as $EnteredShiptRef) {
            echo '<tr><td>' . $EnteredShiptRef->ShiptRef . '</td><td align=right>' .
                number_format($EnteredShiptRef->Amount, $digitos) . '</td></tr>';
            $TotalShiptValue = $TotalShiptValue + $EnteredShiptRef->Amount;
    
            $i++;
            if ($i > 15) {
                $i = 0;
                echo $TableHeader;
            }
        }
        echo '<tr><td colspan=2 align=right><font size=4 color=blue>' . _('Total') . ':</font></td>
            <td align=right><font size=4 color=BLUE><U>' .  number_format($TotalShiptValue, $digitos) . '</U></font></td></tr></table>';
    }
    
    if ($_SESSION['SuppTrans']->GLLink_Creditors == 1) {
        if (count($_SESSION['SuppTrans']->GLCodes) > 0) {
            echo '<br><table cellpadding=2>';
            $TableHeader = "<tr>
                    <th>" . _('U.Neg') ."</th>
                    <th>" . _('Account') .
                    "</th><th>" . _('Name') .
                    "</th><th>" . _('Amount') . '<br>' . _('in') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</th>
                    <th>" . _('Shipment') ."</th>
                    <th>" . _('Job') .  "</th>
                    <th>" . _('Narrative') . '</th></tr>';
            echo $TableHeader;
            $TotalGLValue = 0;
            foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode) {
                $sql = "Select tagdescription FROM tags WHERE tagref = '".$EnteredGLCode->tagref."'";
                $rowtag = DB_query($sql, $db);
                $regtag = DB_fetch_array($rowtag);
                $tagname = $regtag[0];
                
                echo '<tr><td>'.$tagname.'</td>
                        <td>' . $EnteredGLCode->GLCode . '</td><td>' . $EnteredGLCode->GLActName .
                    '</td><td style=text-align:right>' . number_format($EnteredGLCode->Amount, $digitos) .
                    '</td><td>' . $EnteredGLCode->ShiptRef . '</td><td>' .$EnteredGLCode->JobRef .
                    '</td><td>' . $EnteredGLCode->Narrative . '</td></tr>';
                $TotalGLValue = $TotalGLValue + $EnteredGLCode->Amount;
            }
            echo '<tr><td colspan=3 align=right><font size=4 color=blue>' . _('Total') .  ':</font></td>
                    <td style=text-align:right><font size=4 color=blue><U>' .  number_format($TotalGLValue, $digitos) . '</U></font></td>
                </tr></table>';
        }
        if (!isset($TotalGRNValue)) {
            $TotalGRNValue = 0;
        }
        if (!isset($TotalGLValue)) {
            $TotalGLValue = 0;
        }
        if (!isset($TotalShiptValue)) {
            $TotalShiptValue = 0;
        }
        
        $_SESSION['SuppTrans']->OvAmount = $TotalGRNValue + $TotalGLValue + $TotalShiptValue;
        
        if (count($_SESSION['SuppTrans']->GRNs)>0) {
            echo '<div class="row"></div>';
            echo '<br>';
            echo '<div class="col-md-4 col-xs-12">';
            if (isset($_GET['folio'])   & !isset($_POST['SuppReference'])) {
                echo '<component-text-label label="Folio Factura:" id="SuppReference" name="SuppReference" value="'.$_GET['folio'].'" maxlength="20"></component-text-label>';
            } else {
                echo '<component-text-label label="Folio Factura:" id="SuppReference" name="SuppReference" value="'.$_SESSION['SuppTrans']->SuppReference.'" maxlength="20"></component-text-label>';
            }
            echo '</div>';
            echo '<div class="col-md-4 col-xs-12">';
            if (isset($_GET['uuid'])   & !isset($_POST['SuppReferenceFiscal'])) {
                echo '<component-text-label label="UUID:" id="SuppReferenceFiscal" name="SuppReferenceFiscal" value="'.$_GET['uuid'].'" maxlength="50"></component-text-label>';
            } else {
                echo '<component-text-label label="UUID:" id="SuppReferenceFiscal" name="SuppReferenceFiscal" value="'.$_SESSION['SuppTrans']->SuppReferenceFiscal.'" maxlength="50"></component-text-label>';
            }
            echo '</div>';
            echo '<div class="col-md-4 col-xs-12">';
            echo '<component-date-label label="Fecha Factura:" id="fechacambio" name="fechacambio" placeholder="Desde" title="Desde" value="'.date_format(date_create($fechacambio), 'd-m-Y').'"></component-date-label>';
            echo '</div>';
            echo '<div align="center"><h4>Monto por pagar al proveedor: $ '.number_format($_SESSION['SuppTrans']->OvAmount, $_SESSION['DecimalPlaces'], '.', ',').'</h4></div>';
            echo '<div class="row"></div>';

            //VALIDACION DE PERMISO A USUARIO PARA PODER CAMBIAR LA FECHA DE FACTURA
            $permiso2 =Havepermission($_SESSION['UserID'], 775, $db);
            $permiso =Havepermission($_SESSION['UserID'], 410, $db);

            // echo '<table style="text-align:center; margin:0 auto"><tr>';
            // echo "<td>";
            // echo "" . _('Fecha Factura') . ":</br>";
            // echo '<input type="text" id="datepicker" name="fechacambio" value="'.$fechacambio.'" READONLY=false> yyyy-mm-dd</input>';

            // echo "</td></tr></table>";

            // echo '<br><table style="text-align:center; margin:0 auto"><tr><td>' . _('Monto en moneda del proveedor') . ':</td><td colspan=2 align=right>$' .
            //     number_format($_SESSION['SuppTrans']->OvAmount, $digitos) . '</td></tr>';
        }
    } else {
        if (count($_SESSION['SuppTrans']->GRNs)>0) {
            echo '<div class="row"></div>';
            echo '<div class="col-md-4 col-xs-12">';
            echo '<component-text-label label="Monto Moneda:" id="OvAmount" name="OvAmount" value="'.number_format($_SESSION['SuppTrans']->OvAmount, $digitos).'" maxlength="10"></component-text-label>';
            echo '</div>';
        }
    }
    if (count($_SESSION['SuppTrans']->GRNs)>0) {
        //                echo '<tr>';
        //      //echo "<td colspan=2><input type=button disabled name='ToggleTaxMethod' VALUE='" . _('Modificar Calculo de Impuestos')."></td>";
        //                echo "<td><select name='OverRideTax' onChange='ReloadForm(form1.ToggleTaxMethod)' disabled>";
        //
        //      if ($_POST['OverRideTax']=='Man'){
        //          echo "<option VALUE='Auto'>" . _('Automatic') . "<option selected VALUE='Man'>" . _('Manual');
        //      } else {
        //          echo "<option selected VALUE='Auto'>" . _('Automatic') . "<option VALUE='Man'>" . _('Manual');
        //      }
        //      echo '</select></td></tr>';
        echo "<input type='hidden' name='OverRideTax' value='Auto'>";
        $TaxTotal =0;
        foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {
            if (isset($_POST['TaxRate'  . $Tax->TaxCalculationOrder])) {
                $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate = $_POST['TaxRate'  . $Tax->TaxCalculationOrder]/100;
            }
            if (!isset($_POST['OverRideTax']) or $_POST['OverRideTax']=='Auto') {
                                //echo '<label>'. $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100 .'</label>';
                // echo  '<input type=hidden class="number" name=TaxRate' . $Tax->TaxCalculationOrder . ' maxlength=4 size=4 VALUE=' . $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100 . '>';
                echo  '<input type=hidden class="number" name=TaxRate' . $Tax->TaxCalculationOrder . ' maxlength=4 size=4 VALUE="0">';
                
                if ($Tax->TaxOnTax ==1) {
                    $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * ($_SESSION['SuppTrans']->OvAmount + $TaxTotal);
                } else {
                    $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * $_SESSION['SuppTrans']->OvAmount;
                }
                // echo '<input type=hidden name="TaxAmount'  . $Tax->TaxCalculationOrder . '"  VALUE=' . round($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount, $digitos) . '>';
                echo '<input type=hidden name="TaxAmount'  . $Tax->TaxCalculationOrder . '"  VALUE="0">';
                
                //echo '</td><td align=right>' . number_format($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,2);
            } else { /*Tax being entered manually accept the taxamount entered as is*/
                //          if (!isset($_POST['TaxAmount'  . $Tax->TaxCalculationOrder])) {
                //              $_POST['TaxAmount'  . $Tax->TaxCalculationOrder]=0;
                //          }
                $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_POST['TaxAmount'  . $Tax->TaxCalculationOrder];
                // echo  ' <input type=hidden name=TaxRate' . $Tax->TaxCalculationOrder . ' VALUE=' . $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100 . '>';
                echo  ' <input type=hidden name=TaxRate' . $Tax->TaxCalculationOrder . ' VALUE="0">';
    
                // echo '</td><td><input type=TEXT class="number" size=12 maxlength=12 name="TaxAmount'  . $Tax->TaxCalculationOrder . '"  VALUE=' . round($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount, $digitos) . '>';
                echo '</td><td><input type=TEXT class="number" size=12 maxlength=12 name="TaxAmount'  . $Tax->TaxCalculationOrder . '"  VALUE="0">';
            }
            
            $TaxTotal += $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount;
            echo '</td></tr>';
                        //$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount=$totaltaxvalue;
                        $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount=0;
            //                        $DisplayTotal = number_format(( $_SESSION['SuppTrans']->OvAmount + number_format($totaltaxvalue, 2)), 2);
            //                        $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate =(100/$DisplayTotal)*$totaltaxvalue;
            //                        echo '<br>Contenido de Tax:'.print_r($Tax);
        }
                
        $_SESSION['SuppTrans']->OvAmount = round($_SESSION['SuppTrans']->OvAmount, $digitos);
        //$DisplayTotal = number_format(( $_SESSION['SuppTrans']->OvAmount + $totaltaxvalue), 2);
        $DisplayTotal = number_format(( $_SESSION['SuppTrans']->OvAmount), $_SESSION['DecimalPlaces'], '.', ',');
        //echo '<tr><td>' . _('IVA') . ':</td><td colspan=2 align=right>$ ' . number_format($totaltaxvalue, $digitos) . '</td></tr>';
        
        echo '<div class="row"></div>';
        echo '<div align="center"><h4>Total Factura: $ '.$DisplayTotal.'</h4></div>';
        echo '<div class="row"></div>';
        
        /*$sql = 'SELECT taxcategories.taxcatid,
                        taxcategories.taxcatname
                FROM taxcategories
                where flagdiot=1
                ';
        $result = DB_query($sql, $db);
        $totalivas = 0;
        while ($myrow = DB_fetch_array($result)){
            echo "<tr><td>".$myrow['taxcatname']."</td>";
            echo "<td><input type=text name='iva_".$myrow['taxcatid']."' value=".$_POST['iva_'.$myrow['taxcatid']].">";
            echo "<td><input type=hidden name='idiva_".$myrow['taxcatid']."' value=".$myrow['taxcatid']."></td></tr>";
            $totalivas = $totalivas + 1;
        }
        echo "<tr><td></td><td><input type='hidden' name='totalivas' value='".$totalivas."'></td></tr>";
        */
        
        echo '<div class="row"></div>';
        echo '<div class="col-md-3 col-xs-12">';
        echo '</div>';
        echo '<div class="col-md-6 col-xs-12">';
        echo '<component-textarea-label label="Comentarios: " id="Comments" name="Comments" placeholder="Comentarios" title="Comentarios" cols="3" rows="4" value="'.$_SESSION['SuppTrans']->Comments.'"></component-textarea-label>';
        echo '</div>';
        echo '<div class="col-md-3 col-xs-12">';
        echo '</div>';
        echo '<div class="row"></div>';
        echo '<div align="center">';
        
        // echo '<component-button type="submit" id="btnRegresar" name="btnRegresar" class="glyphicon glyphicon-share-alt" value="Regresar" onclick="fnRegresarPanel(); return false;"></component-button>';
        
        if ($procesoterminado == 0) {
            echo '<component-button type="submit" id="PostInvoice" name="PostInvoice" class="glyphicon glyphicon-ok" value="Procesa"></component-button>';
        }
        if (isset($legalid) and $legalid != '*') {
            echo '<component-button type="submit" id="GRNS" name="GRNS" class="glyphicon glyphicon-share-alt" value="Regresar"></component-button>';
        }
        echo '</div>';
        
        /* NECESARIO DEFINIR COMO MANEJARIAMOS LOS MOVIMIENTOS PARA QUE NO AFECTE A MANEJO DE PRESUPUESTOS */
        if ($_SESSION['MosRazonSocialAlter'] == 1) {
            echo '<tr><td>'. _('Razon Social Alterna') . '</td>';
            
            echo '<td><select name="alt_tagref">';
            echo "<option value='0'>SELECCIONA UNA...</option>";
            $sql = 'SELECT distinct legalbusinessunit.legalid, legalbusinessunit.legalname
                FROM legalbusinessunit';
                
            $LocnResult = DB_query($sql, $db);
            while ($LocnRow=DB_fetch_array($LocnResult)) {
                echo "<option value='" . $LocnRow['legalid'] . "'";
                    
                if (intval($LocnRow['legalid'])==intval($unidadnegocio)) {
                    echo ' selected';
                }
                echo ">" . $LocnRow['legalname'];
            }
            
            echo '</select></td>';
            echo'</tr>';
        }
    }
    echo '</table>';
} else {
    //echo "<br>ovamount:" . $_SESSION['SuppTrans']->OvAmount;
    foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {
        //      if (isset($_POST['TaxRate'  . $Tax->TaxCalculationOrder])){
        //          $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate = $_POST['TaxRate'  . $Tax->TaxCalculationOrder]/100;
        //      }
        //      if ($_POST['OverRideTax']=='Auto' OR !isset($_POST['OverRideTax'])){
        //          /*Now recaluclate the tax depending on the method */
        //          if ($Tax->TaxOnTax ==1){
        //              $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * ($_SESSION['SuppTrans']->OvAmount + $TaxTotal);
        //          } else { /*Calculate tax without the tax on tax */
        //              $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * $_SESSION['SuppTrans']->OvAmount;
        //          }
        //      } else { /*Tax being entered manually accept the taxamount entered as is*/
        //          $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_POST['TaxAmount'  . $Tax->TaxCalculationOrder];
        //      }
    }
    
    $TaxTotal=0;
    foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {
        //echo "<br>entro" . $Tax->TaxOvAmount;
        $TaxTotal +=  $Tax->TaxOvAmount;
    }
        
    //echo "<br>1.- " . $TaxTotal;
    //echo "<br>2.- " . $_SESSION['SuppTrans']->OvAmount;
    $InputError = false;
    if ($DisplayTotal != $_SESSION['totald'] & $_SESSION['totald'] > 0) {
        //$InputError = True;
        //prnMsg(_('El monto de la factura debe conincidir con el XML...') ,'error');
    } elseif (empty($unidadnegocio) || $unidadnegocio == '0') {
        $InputError = true;
        prnMsg(_('Seleccione una UR...'), 'error');
    } elseif ($TaxTotal + $_SESSION['SuppTrans']->OvAmount <= 0) {
        $InputError = true;
        prnMsg(_('La factura no puede ser procesada porque el monto total es menor o igual a 0'), 'error');
    } elseif (strlen($_SESSION['SuppTrans']->SuppReference)<1) {
        $InputError = true;
        prnMsg(_('La Factura no puede ser procesada porque falta capturar el Folio'), 'error');
    } elseif (strlen($_SESSION['SuppTrans']->SuppReferenceFiscal)<1) {
        $InputError = true;
        prnMsg(_('La Factura no puede ser procesada porque falta capturar el UUID'), 'error');
    } elseif ($_SESSION['FutureDate']==1) {
        // if (Date1GreaterThanDate2($_SESSION['SuppTrans']->TranDate, date("d/m/Y"))==1 and Havepermission($_SESSION['UserID'], 410, $db)==0) {
        //     prnMsg(_('La fecha es posterior y no cuenta con los permisos para realizar esta operación'), 'error');
        //     $InputError = true;
        // }
    } elseif ($_SESSION['SuppTrans']->ExRate <= 0) {
        $InputError = true;
        prnMsg(_('La factura no puede ser procesada porque el tipo de cambio capturado es negativo o cero'), 'error');
    } elseif ($_SESSION['SuppTrans']->OvAmount < round($TotalShiptValue + $TotalGLValue + $TotalGRNValue, $digitos)) {
        prnMsg(_('El total de la factura ingresado es menor que la suma de los cargos por envío'), 'error');
        $InputError = true;
    } else {
        $sql = "SELECT count(*) 
            FROM supptrans 
            WHERE supplierno='" . $_SESSION['SuppTrans']->SupplierID . "' 
            AND supptrans.suppreference='" . $_POST['SuppReference'] . "'
            AND abs(supptrans.ovamount+supptrans.ovgst)>0";
        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sql to check for the previous entry of the same invoice failed');
        $DbgMsg = _('The following SQL to start an SQL transaction was used');
        $result=DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
        $myrow=DB_fetch_row($result);
        if ($myrow[0] >= 1) {
            prnMsg(_('El No. de Factura de Referencia') . ' : ' . $_POST['SuppReference'] . ' ' . _('ya ha sido capturado') . '. ' . _('No puede ser capturado de nuevo'), 'error');
            $InputError = true;
        }
    }
    $sql = "SELECT count(*)
            FROM supptrans
            WHERE supplierno='" . $_SESSION['SuppTrans']->SupplierID . "'
            AND supptrans.suppreference='" . $_POST['SuppReference'] . "'
            AND abs(ovamount+ovgst)>0";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sql to check for the previous entry of the same invoice failed');
    $DbgMsg = _('The following SQL to start an SQL transaction was used');
    //echo '<pre>'.$sql;
    
    $result=DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
    $myrow=DB_fetch_row($result);
    if ($myrow[0] >= 1) {
        prnMsg(_('El No. de Factura de Referencia') . ' : ' . $_POST['SuppReference'] . ' ' . _('ya ha sido capturado') . '. ' . _('No puede ser capturado de nuevo'), 'error');
        $InputError = true;
    }

    // Validar UUID
    $sql = "SELECT reffiscal, suppreference FROM supptrans
            WHERE supptrans.reffiscal = '" . $_POST['SuppReferenceFiscal'] . "'
            AND abs(ovamount+ovgst)>0";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('');
    $DbgMsg = _('');
    $result=DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
    if (DB_num_rows($result) > 0) {
        $rowDato = DB_fetch_array($result);
        prnMsg(_('El UUID') . ' : ' . $_POST['SuppReferenceFiscal'] . ' ' . _('ya ha sido capturado en la Factura ') . $rowDato['suppreference'] . ' . ' . _('No puede ser capturado de nuevo'), 'error');
        $InputError = true;
    }
    
    // if ((($_SESSION['SuppTrans']->TranDate) < date("d/m/Y")) and Havepermission($_SESSION['UserID'], 775, $db)==0) {
    //     prnMsg(_('La fecha es anterior a la actual y no cuenta con los permisos para realizar esta operación'), 'error');
    //     echo '<br>';
    //     echo '->TranDate'.$_SESSION['SuppTrans']->TranDate;
    //     echo '<br>';
    //     echo 'date'.date("d/m/Y");
    //     echo '<br>';
    //     echo 'fecha_actual:'.$fecha_actual.' Fecha Cambio:'.$fechacambio." Fecha aval:".$fechaval ;
    //     echo '<br>';

    //     $enc = new Encryption;
    //     $url = "&SupplierID=>".$_SESSION['SuppTrans']->SupplierID;
    //     $url = $enc->encode($url);
    //     $liga= "URL=" . $url;

    //     echo '<br><div class="centre"><a href="' . $rootpath . '/SupplierInvoice.php?' . $liga . '">' . _('Capturar otra Fecha de Factura ') . '</a></div>';
    //     include('includes/footer_Index.inc');
    //     exit();
    // }
    // if (Havepermission($_SESSION['UserID'], 1323, $db) == 1) {
    //     $fechaval = FormatDateForSQL($_SESSION['SuppTrans']->TranDate);
    //     $mesfact = substr($fechaval, 5, 2);
    //     $yearfact = substr($fechaval, 0, 4);
    //     $diafact = substr($fechaval, 8, 2);
    //     $mesactual = date('m');
    //     $Yearactual = date('Y');//
    //     if ($mesfact <> $mesactual) {
    //         prnMsg(_('No tiene permiso de generar una factura al proveedor con mes diferente al actual'), "error");
    //         echo '<br>';
            
    //         $enc = new Encryption;
    //         $url = "&SupplierID=>".$_SESSION['SuppTrans']->SupplierID;
    //         $url = $enc->encode($url);
    //         $liga= "URL=" . $url;

    //         echo '<br><div class="centre"><a href="' . $rootpath . '/SupplierInvoice.php?&' . $liga . '">' . _('Capturar otra Fecha de Factura ') . '</a></div>';

    //         include('includes/footer_Index.inc');
    //         exit();
    //     }
    // }
    
    $SQLInvoiceDate = FormatDateForSQL($_SESSION['SuppTrans']->TranDate);
    $fecha_actual=date("Y/m/d");
    $permiso2 =Havepermission($_SESSION['UserID'], 775, $db);
    $permiso =Havepermission($_SESSION['UserID'], 410, $db);
    $permisofuncion=0;
    $permisofuncion2=0;
    $permisofuncion3=0;
    //VALIDA LA FECHA INTRODUCIDA POR EL USUARIO DE ACUERDO A SUS PERMISOS
    if (($SQLInvoiceDate > $fecha_actual) and $permiso==1) {
        $permisofuncion=1;
    }
    if (($SQLInvoiceDate <= $fecha_actual) and $permiso2==1) {
        $permisofuncion2=1;
    }
    if (($SQLInvoiceDate == $fecha_actual) and $permiso2==0 or $permiso==0) {
        $permisofuncion3=1;
    }
    
    
    $totalIvas = 0;
    /*$totalfact = round($_SESSION['SuppTrans']->OvAmount, 2);
    for ($contivas=1; $contivas <= $_POST['totalivas']; $contivas++) {
        if (is_numeric($_POST['iva_'.$contivas])) {
            $totalIvas += $_POST['iva_'.$contivas];
        }
    }
    
    if ($totalIvas > 0) {
        $diffIvas = abs($totalfact - $totalIvas);
        if (($diffIvas >= 0 && $diffIvas <= 1) == false) {
            $InputError = true;
            prnMsg(_('El subtotal de la factura no coincide con el de impuestos, favor de verificarlo'), 'error');
        }
    }*/
    
    $SQLProv = "SELECT suppliers.supplierid,suppliers.suppname
                    FROM suppliers
                    WHERE suppliers.supplierid = '".$_SESSION['SuppTrans']->SupplierID."'";
        
    $ResProv = DB_query($SQLProv, $db);
    $RowProv = DB_fetch_array($ResProv);
    $nomProv = $RowProv['suppname'];

    // Validacion Fecha de Recepcion Inicio
    $numRecepciones = 1;
    $fechaRecepcion = "";
    $fechaRecepcionVisual = "";
    $fechaFactura = date_create($fechacambio);
    foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN) {
        if (empty($fechaRecepcion)) {
            // Primera vez mostrar fecha
            $fechaRecepcion = date_create($EnteredGRN->deliverydate);
            $fechaRecepcionVisual = $EnteredGRN->deliverydate;
        } else {
            if ($fechaRecepcion < date_create($EnteredGRN->deliverydate)) {
                // Si fecha anterior es menor a la nueva
                $fechaRecepcion = date_create($EnteredGRN->deliverydate);
                $fechaRecepcionVisual = $EnteredGRN->deliverydate;
                $numRecepciones ++;
            }
        }
    }
    if ($fechaFactura > $fechaRecepcion && $numRecepciones == 1) {
        // No facurar ya que la fecha es mayor a la recepcion
        // echo "<br>No factuar";
        prnMsg(_('La fecha de Recepción es ') . $fechaRecepcionVisual . _(' es menor a la de Factura ') . $fechacambio, 'error');
        $InputError = true;
    }
    // Validacion Fecha de Recepcion Fin
    
    // if ($permisofuncion==1 and $InputError == false or $permisofuncion2==1 and $InputError == false or $permisofuncion3==1 and $InputError == false) {
    if ($InputError == false) {
        $Result = DB_Txn_Begin($db);
            
        if (isset($_POST['PostInvoice'])) {
            $tipodocto = 20;
        }

        $InvoiceNo = GetNextTransNo($tipodocto, $db);
        /////$PeriodNo = GetPeriod($_SESSION['SuppTrans']->TranDate, $db, $unidadnegocio);
        $PeriodNo = GetPeriod( ( isset($_SESSION['ejercicioFiscal'])&&$_SESSION['ejercicioFiscal']!=date('Y') ? date('d')."/12/$_SESSION[ejercicioFiscal]" : $_SESSION['SuppTrans']->TranDate ), $db, $unidadnegocio);
        $SQLInvoiceDate = FormatDateForSQL($_SESSION['SuppTrans']->TranDate);
        $today = getdate();

        // Folio de la poliza por unidad ejecutora
        $folioPolizaUe = fnObtenerFolioUeGeneral($db, $unidadnegocio, $_SESSION['SuppTrans']->unidadEjecutoraGeneral, $tipodocto);
                
        /***********************************************************************/
        
        if ($_SESSION['SuppTrans']->GLLink_Creditors == 1) {
            $LocalTotal = 0;
                        
            // insertar momento contable para la factura
            //GeneraMovimientoContablePresupuesto($tipodocto, "COMPROMETIDO", "DEVENGADO", $InvoiceNo, $PeriodNo,  $_SESSION['SuppTrans']->OvAmount + $TaxTotal, $unidadnegocio, $SQLInvoiceDate, $db);
            
            //RECORRE ARREGLO DE MOVIMIENTOS CONTABLES
            foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode) {
                            $unidadnegocioGL=$EnteredGLCode->tagref;
                
                            $SQL = "INSERT INTO gltrans (type, 
                                                            typeno, 
                                                            trandate, 
                                                            periodno, 
                                                            account, 
                                                            narrative, 
                                                            amount, 
                                                            jobref,
                                                            tag) 
                                            VALUES ('" . $tipodocto . "', 
                                                            '" .$InvoiceNo . "', 
                                                            '" . $SQLInvoiceDate . "', 
                                                            '" . $PeriodNo . "', 
                                                            '" . $EnteredGLCode->GLCode . "', 
                                                            '" . $_SESSION['SuppTrans']->SupplierID .' '.$nomProv. ' ' . $EnteredGLCode->Narrative .' '. $EnteredGRN->SerieNo . "', 
                                                            '" . round($EnteredGLCode->Amount/ $_SESSION['SuppTrans']->ExRate, $digitos) . "', 
                                                            '" . $EnteredGLCode->JobRef . "',
                                                            '" . $unidadnegocioGL . "')";
                
                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');
                $DbgMsg = _('The following SQL to insert the GL transaction was used');
                //$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                
                $Narrative=' Prov. '.$_SESSION['SuppTrans']->SupplierID .' '.$nomProv.' '.$supplierRFC. ' @ ' . $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal." ".$_SESSION['SuppTrans']->TranDate."@".$EnteredGLCode->Narrative.' '. $_SESSION['SuppTrans']->Comments.' '. $EnteredGRN->SerieNo;
                //$Narrative=$_SESSION['SuppTrans']->SupplierID .' '.$nomProv.' '.$supplierRFC. ' ' . $EnteredGLCode->Narrative .' '. $EnteredGRN->SerieNo.' @ '. $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments." ".$_SESSION['SuppTrans']->TranDate;
                $montocontable=round($EnteredGLCode->Amount/ $_SESSION['SuppTrans']->ExRate, $digitos);
                // ejecuta funcion para insertar cargo en tabla de contabilidad
                
                $ISQL = Insert_Gltrans(
                    $tipodocto,
                    $InvoiceNo,
                    $SQLInvoiceDate,
                    $PeriodNo,
                    $EnteredGLCode->GLCode,
                    $Narrative,
                    $unidadnegocioGL,
                    $_SESSION['UserID'],
                    $_SESSION['SuppTrans']->ExRate,
                    '',
                    '',
                    '',
                    0,
                    0,
                    '',
                    0,
                    $_SESSION['SuppTrans']->SupplierID,
                    0,
                    $montocontable,
                    $db,
                    '',
                    'GASTO',
                    $EnteredGLCode->JobRef
                );
                //var_dump('<br>Consulta SQL 1035: '.$ISQL);
                $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
                $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
                // Se comento pero no se usaba- $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
                
                $LocalTotal += round($EnteredGLCode->Amount/ $_SESSION['SuppTrans']->ExRate, $digitos);
            }
            
            //RECORRE ARREGLO DE FACTURACION CONTRA EMBARQUE
            foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg) {
                $SQL = "INSERT INTO gltrans (type, 
                                typeno, 
                                trandate, 
                                periodno, 
                                account, 
                                narrative, 
                                amount,
                                tag) 
                            VALUES ( '". $tipodocto .  "' ,
                                '".$InvoiceNo . "', 
                                '" . $SQLInvoiceDate . "', 
                                '" . $PeriodNo . "', 
                                '" . $ShiptChg->account . "', 
                                '" . $_SESSION['SuppTrans']->SupplierID.' '.$nomProv . ' '. $supplierRFC.' '. _('Shipment charge against') . ' ' . $ShiptChg->ShiptRef . "', 
                                '" . $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate . "',
                                '".$unidadnegocio."')";
                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the shipment') .' ' . $ShiptChg->ShiptRef . ' ' . _('could not be added because');
                $DbgMsg = _('The following SQL to insert the GL transaction was used');
                //$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                $Narrative= ' Prov. '.$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' @ ' . $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal." ".$_SESSION['SuppTrans']->TranDate._('Gastos de envio') . ' ' . $ShiptChg->ShiptRef.' '. $_SESSION['SuppTrans']->Comments;
                //$Narrative= $_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' ' . _('Gastos de envio') . ' ' . $ShiptChg->ShiptRef.' @ '. $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments." ".$_SESSION['SuppTrans']->TranDate;
                $montocontable=round($ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate, $digitos);
                // ejecuta funcion para insertar cargo en tabla de contabilidad
                $ISQL = Insert_Gltrans(
                    $tipodocto,
                    $InvoiceNo,
                    $SQLInvoiceDate,
                    $PeriodNo,
                    $ShiptChg->account,
                    $Narrative,
                    $unidadnegocio,
                    $_SESSION['UserID'],
                    $_SESSION['SuppTrans']->ExRate,
                    '',
                    '',
                    '',
                    0,
                    0,
                    '',
                    0,
                    $_SESSION['SuppTrans']->SupplierID,
                    0,
                    $montocontable,
                    $db,
                    '',
                    'GASTO EMBARQUE',
                    $EnteredGLCode->JobRef
                );
                //var_dump('<br>Consulta SQL 1093: '.$ISQL);
                $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
                $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
                // Se comento pero no se usaba- $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
                
                $LocalTotal += $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate;
            }
            
            //RECORRE ARREGLO FACTURA CONTRA PRODUCTOS RECIBIDOS
            foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN) {
                 $datosProv=$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. " - ". $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments;
                /* inserta mov de costo de factura en tabla de movimientos*/
                if (strlen($EnteredGRN->ItemCode)>0 or $EnteredGRN->ItemCode != '') {
                    $costofactproveedoruno=((($EnteredGRN->ChgPrice) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)))  / $_SESSION['SuppTrans']->ExRate);
                    $TextoOrden=" - ORDEN DE COMPRA: ".$EnteredGRN->realorderno;
                    $isql = "insert into stockmoves (stkmoveno,stockid,type,transno,loccode,trandate,debtorno,
                                branchcode,price,prd,reference,qty,discountpercent,standardcost,show_on_inv_crds,newqoh,
                                hidemovt,narrative,warranty,tagref,discountpercent1,discountpercent2,totaldescuento,avgcost,standardcostv2, ln_ue)
                                values(NULL,'" . $EnteredGRN->ItemCode . "','" . $tipodocto . "','" . $InvoiceNo . "','" . $EnteredGRN->location  . "',Now(),'','','". $costofactproveedoruno . "','" . $PeriodNo . "',
                                'FACTURA DE COMPRA: ".$InvoiceNo." - RECEPCION:".$EnteredGRN->GRNNo.$TextoOrden ."','0','0','" . $costofactproveedoruno . "','1','0','0',
                                'FACTURA DE COMPRA: ".$InvoiceNo." - RECEPCION:".$EnteredGRN->GRNNo.$TextoOrden.' '. $EnteredGRN->SerieNo .' '.$datosProv."',
                                '0','" . $unidadnegocio . "','0','0','0','0','0', '".$EnteredGRN->unidadEjecutora."')";
                    $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockmoves debido a ');
                    $DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
                    $Result = DB_query($isql, $db, $ErrMsg, $DbgMsg, true);
                }
                
                //if (strlen($EnteredGRN->ShiptRef)==0 OR $EnteredGRN->ShiptRef == 0){ se comento porque la poliza sale descuadrada ya que si no entra al if no inserta registros en gltrans, solo el cargo a proveedor y por lo tanto la poliza queda descuadrada
                if ($EnteredGRN->StdCostUnit * $EnteredGRN->This_QuantityInv != 0) {
                    if ($EnteredGRN->consignment == 0) {
                        $cuentapuenteprov = $_SESSION['SuppTrans']->GRNAct;
                        //echo 'entra 1';
                    } else {
                        $cuentapuenteprov = $_SESSION['CompanyRecord']['gltempsuppconsignment'];
                        //echo 'entra 2';
                    }
                    if (empty($cuentapuenteprov) == true or $cuentapuenteprov == "") {
                        $cuentapuenteprov = $_SESSION['CompanyRecord']['grnact'];
                    }
                    $SQL = "INSERT INTO gltrans (type, 
                                typeno, 
                                trandate, 
                                periodno, 
                                account, 
                                narrative, 
                                amount,
                                tag) 
                            VALUES ('" . $tipodocto . "',
                                '" . $InvoiceNo . "', 
                                '" . $SQLInvoiceDate . "', 
                                '" . $PeriodNo . "', 
                                '" . $cuentapuenteprov . "', 
                                '" . $_SESSION['SuppTrans']->SupplierID.' '.$nomProv . " - " . _('GRN') . " " . $EnteredGRN->GRNNo . " - " . DB_escape_string(htmlspecialchars_decode($EnteredGRN->ItemCode, ENT_NOQUOTES)) . " x " . $EnteredGRN->This_QuantityInv . " @  " .
                            _('std cost of proveedor') . " " . $EnteredGRN->StdCostUnit  ." TC:".$_SESSION['SuppTrans']->ExRate." ". $EnteredGRN->SerieNo . "', 
                                '" . ($EnteredGRN->StdCostUnit/ $_SESSION['SuppTrans']->ExRate) * $EnteredGRN->This_QuantityInv . "',
                                '".$unidadnegocio."')";
                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');
                    $DbgMsg = _('The following SQL to insert the GL transaction was used');
                    //echo '<pre>sql:'.$SQL;

                    //$Result = DB_query($SQL, $db, $ErrMsg, $Dbg, True);
                    $Narrative = ' Prov. '.$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' @ ' . $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal." ".$_SESSION['SuppTrans']->TranDate." @ ". _('Recepcion Compra:') . " " . $EnteredGRN->GRNNo . " - " . (htmlspecialchars_decode($EnteredGRN->ItemCode, ENT_NOQUOTES))
                                . " x " . $EnteredGRN->This_QuantityInv . " @  " ._('costo de proveedor') . " " . $EnteredGRN->StdCostUnit  ." TC:".$_SESSION['SuppTrans']->ExRate." ". $EnteredGRN->SerieNo .' @ '.$_SESSION['SuppTrans']->Comments;
                    //$Narrative= $_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. " - " . _('Recepcion Compra:') . " " . $EnteredGRN->GRNNo . " - " . (htmlspecialchars_decode($EnteredGRN->ItemCode,ENT_NOQUOTES)) . " x " . $EnteredGRN->This_QuantityInv . " @  " .
                    //      _('costo de proveedor') . " " . $EnteredGRN->StdCostUnit  ." TC:".$_SESSION['SuppTrans']->ExRate." ". $EnteredGRN->SerieNo .' @ '. $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments." ".$_SESSION['SuppTrans']->TranDate;
                    $montocontable=round(($EnteredGRN->StdCostUnit/ $_SESSION['SuppTrans']->ExRate) * $EnteredGRN->This_QuantityInv, $digitos);
                    // ejecuta funcion para insertar cargo en tabla de contabilidad
                    $ISQL = Insert_Gltrans(
                        $tipodocto,
                        $InvoiceNo,
                        $SQLInvoiceDate,
                        $PeriodNo,
                        $cuentapuenteprov,
                        $Narrative,
                        $unidadnegocio,
                        $_SESSION['UserID'],
                        $_SESSION['SuppTrans']->ExRate,
                        '',
                        '',
                        DB_escape_string(htmlspecialchars_decode($EnteredGRN->ItemCode, ENT_NOQUOTES)),
                        $EnteredGRN->This_QuantityInv,
                        $EnteredGRN->GRNNo,
                        $EnteredGRN->location,
                        0,
                        $_SESSION['SuppTrans']->SupplierID,
                        $EnteredGRN->PONo,
                        $montocontable,
                        $db,
                        '',
                        'INVENTARIO',
                        $EnteredGLCode->JobRef
                    );
                    //var_dump('<br>Consulta SQL 1185: '.$ISQL);
                    $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
                    $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
                    // Se comento pero si se usaba- $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
                }
                    $PurchPriceVar = round($EnteredGRN->This_QuantityInv * (( (($EnteredGRN->ChgPrice) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)))  / $_SESSION['SuppTrans']->ExRate) - (($EnteredGRN->StdCostUnit/ $EnteredGRN->rategr))), $digitos); //aqui se quito /$EnteredGRN->rategr porque el valor de StdCostUnit ya esta en pesos
                    //echo 'variacion precio:'.$PurchPriceVar.'<br>precio ini:'.$EnteredGRN->StdCostUnit.' <br>precio fin:'.$EnteredGRN->ChgPrice .'<br> rate gr'.$EnteredGRN->rategr;
                if ($PurchPriceVar !=0 and (strlen($EnteredGRN->ItemCode)>0 or $EnteredGRN->ItemCode != '')) {
                    $StockGLCode = GetStockGLCode($EnteredGRN->ItemCode, $db);
                    // si debe llevar cuenta de variacion de precio
                    $EstimatedAvgCostVariacion=$PurchPriceVar;
                    if ($_SESSION['WeightedAverageCosting']==1) {
                        // trae costo promedio anterior OrderPrice
                        $EstimatedAvgCostVariacion=$PurchPriceVar;
                        $datosProv=$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. " - ". $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments;
                            
                        $sqlprod="select * from stockmaster where stockid='".$EnteredGRN->ItemCode."'";
                        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');
                        $DbgMsg = _('The following SQL to insert the GL transaction was used');
                        $Result = DB_query($sqlprod, $db, $ErrMsg, $DbgMsg, true);
                        if (DB_num_rows($Result)>0) {
                            $myrow = DB_fetch_array($Result);
                            $controled = $myrow['controlled'];
                            $serialized = $myrow['serialised'];
                            if ($controled==1 or $serialized==1) {
                                $nuevocostoserie=$PurchPriceVar/$EnteredGRN->This_QuantityInv;
                                $BatchNoserial = GetNextTransNo(591, $db);
                                $TextoOrden=" ORDEN DE COMPRA: ".$EnteredGRN->PONo;
                                $isql = "insert into stockmoves(stkmoveno,stockid,type,transno,loccode,trandate,debtorno,
                                        branchcode,price,prd,reference,qty,discountpercent,standardcost,show_on_inv_crds,newqoh,
                                        hidemovt,narrative,warranty,tagref,discountpercent1,discountpercent2,totaldescuento,avgcost,standardcostv2,ref3, ln_ue)
                                        values(NULL,'" . $EnteredGRN->ItemCode . "','591','" . $BatchNoserial . "','" . $EnteredGRN->location  . "',Now(),'','','". $EstimatedAvgCost . "','" . $PeriodNo . "',
                                        'AJUSTE DE COSTO','0','0','" . $nuevocostoserie . "','1','0','0',
                                        'AJUSTE COSTO X CAMBIO DE COSTO EN FACTURA DE COMPRA CON RECEPCION:".$EnteredGRN->GRNNo.$TextoOrden .' '.$EnteredGRN->SerieNo.' '.$datosProv.' '."',
                                        '0','" . $unidadnegocio . "','0','0','0','0','0','$InvoiceNo', '".$EnteredGRN->unidadEjecutora."')";
                                $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockmoves debido a ');
                                //var_dump('<br>Consulta SQL'.$isql);
                                $DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
                                $Result = DB_query($isql, $db, $ErrMsg, $DbgMsg, true);
                                    
                                $stkmovenovendidas = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');
                                    
                                    
                                /***actualizacion de costos en stockserialmoves***/
                                $sqlserials="select * from stockserialmoves where stockmoveno=".$EnteredGRN->stkmoveno;
                                $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockmoves debido a ');
                                //var_dump('<br>Consulta SQL'.$sqlserials);
                                $DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
                                $IResult = DB_query($sqlserials, $db, $ErrMsg, $DbgMsg, true);
                                if ($myrow=DB_fetch_array($IResult)) {
                                    $serialno=$myrow['serialno'];
                                    $sqlserialno="select *
                                                  from stockserialitems
                                                  where stockid='".$EnteredGRN->ItemCode."'
                                                and serialno='".$serialno."'
                                                and loccode='".$EnteredGRN->location."'";
                                    $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockmoves debido a ');
                                    $DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
                                    $IResultdos = DB_query($sqlserialno, $db, $ErrMsg, $DbgMsg, true);
                                        
                                    while ($myrowseries=DB_fetch_array($IResultdos)) {
                                        $cantidad=$myrowseries['quantity'];
                                        //$NewCost=$myrowseries['standardcost']+$nuevocostoserie;
                                        if ($cantidad>0) {
                                            $BatchNoserial = GetNextTransNo(590, $db);
                                            $datosProv=$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. " - ". $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments;
                                                
                                            $TextoOrden=" DE LA ORDEN DE COMPRA: ".$EnteredGRN->PONo;
                                            $isql = "insert into stockmoves(stkmoveno,stockid,type,transno,loccode,trandate,debtorno,
                                                    branchcode,price,prd,reference,qty,discountpercent,standardcost,show_on_inv_crds,newqoh,
                                                    hidemovt,narrative,warranty,tagref,discountpercent1,discountpercent2,totaldescuento,avgcost,standardcostv2, ln_ue)
                                                    values(NULL,'" . $EnteredGRN->ItemCode  . "','590','" . $BatchNoserial . "','" . $EnteredGRN->location . "',Now(),'','',0,'" . $PeriodNo . "',
                                                    'AJUSTE DE COSTO x NUM SERIE".$TextoOrden."','0','0','" . $NewCost . "','1','0','0',
                                                            'AJUSTE COSTO".$TextoOrden.' '.$EnteredGRN->SerieNo."','0','" . $unidadnegocio . ' '.$datosProv."',
                                                                    '0','0','0','0','0', '".$EnteredGRN->unidadEjecutora."')";
                                            $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockmoves debido a ');
                                            $DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
                                            //var_dump('<br>Consulta SQL'.$isql);
                                            $Result = DB_query($isql, $db, $ErrMsg, $DbgMsg, true);
                                                
                                            $stkmoveno = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');
                                            $isql = "insert into stockserialmoves(stkitmmoveno, stockmoveno, stockid,serialno,moveqty,standardcost,orderno,orderdetailno)
                                                values(NULL,'" . $stkmoveno . "','" . $EnteredGRN->ItemCode . "','" . $serialno . "','0','" . $NewCost . "','0','0')";
                                            $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockserialmoves debido a ');
                                            $DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
                                            //var_dump('<br>Consulta SQL'.$isql);
                                            $Result = DB_query($isql, $db, $ErrMsg, $DbgMsg, true);
                                        } else {
                                            $costoserie=($costoserie+$myrowseries['standardcost'])+$nuevocostoserie;
                                            $costoserie2=$costoserie2+$nuevocostoserie;
                                        }
                                        $sqlupdate="update stockserialitems
                                                    set standardcost=standardcost+".$nuevocostoserie."
                                                    where stockid='".$EnteredGRN->ItemCode."'
                                                    and serialno='".$serialno."'
                                                    and loccode='".$EnteredGRN->location."'";
                                        $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockserialmoves debido a ');
                                        $DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
                                        //var_dump('<br>Consulta SQL'.$sqlupdate);
                                        $Result = DB_query($sqlupdate, $db, $ErrMsg, $DbgMsg, true);
                                    }
                                    if ($costoserie2>0) {
                                        $sqlupdate="update stockmoves
                                                    set standardcost=".$costoserie2.",
                                                    avgcost=".$costoserie2."
                                                    where stkmoveno='".$stkmovenovendidas."'";
                                        $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockserialmoves debido a ');
                                        $DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
                                        //var_dump('<br>Consulta SQL'.$sqlupdate);
                                        $Result = DB_query($sqlupdate, $db, $ErrMsg, $DbgMsg, true);
                                    }
                                }
                            } else {
                                $BatchNoserial = GetNextTransNo(591, $db);
                                $TextoOrden=" DE LA ORDEN DE COMPRA: ".$EnteredGRN->PONo;
                                $datosProv=$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. " - ". $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments;
                                    
                                $isql = "insert into stockmoves(stkmoveno,stockid,type,transno,loccode,trandate,debtorno,
                                        branchcode,price,prd,reference,qty,discountpercent,standardcost,show_on_inv_crds,newqoh,
                                        hidemovt,narrative,warranty,tagref,discountpercent1,discountpercent2,totaldescuento,avgcost,standardcostv2,ref3, ln_ue)
                                        values(NULL,'" . $EnteredGRN->ItemCode . "','591','" . $BatchNoserial . "','" . $EnteredGRN->location  . "',Now(),'','','". $EstimatedAvgCostVariacion . "','" . $PeriodNo . "',
                                        'AJUSTE COSTO X CAMBIO DE COSTO EN FACTURA DE COMPRA CON RECEPCION:".$EnteredGRN->GRNNo.$TextoOrden."','0','0','" . $EstimatedAvgCostVariacion . "','1','0','0',
                                                'AJUSTE COSTO X CAMBIO DE COSTO EN FACTURA DE COMPRA CON RECEPCION:".$EnteredGRN->GRNNo.$TextoOrden .' '.$datosProv."','0','" . $unidadnegocio . "','0','0','0','0','0','$InvoiceNo', '".$EnteredGRN->unidadEjecutora."')";
                                    
                                $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockmoves debido a ');
                                $DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
                                //var_dump('<br>Consulta SQL'.$isql);
                                $Result = DB_query($isql, $db, $ErrMsg, $DbgMsg, true);
                                    
                                $stkmovenovendidas = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');
                            }  /*fin de producto serializado*/
                        }// FIN DE VALIDACION SI PRODUCTO EXISTE
                            
                        if ($EnteredGRN->consignment == 0) {
                            $cuentalmacen = $_SESSION['SuppTrans']->GRNAct;
                        } else {
                            $cuentalmacen = $_SESSION['CompanyRecord']['gltempsuppconsignment'];
                        }
                        if (empty($cuentalmacen) == true or $cuentalmacen == "") {
                            $cuentalmacen = $_SESSION['CompanyRecord']['grnact'];
                        }
                            
                        if ($cuentalmacen=='') {
                            $cuentalmacen=0;
                        }
                        $SQL = 'INSERT INTO gltrans (type, 
                                typeno, 
                                trandate, 
                                periodno, 
                                account, 
                                narrative, 
                                amount,
                                        tag) 
                                VALUES (' . $tipodocto .  ', ' .
                             $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . ', ' . $StockGLCode['purchpricevaract'] .
                             ", '" . $_SESSION['SuppTrans']->SupplierID.' '.$nomProv . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo .
                             ' - ' . DB_escape_string(htmlspecialchars_decode($EnteredGRN->ItemCode, ENT_NOQUOTES))  . _(' variacion de precio') . ' ' .
                             number_format($EstimatedAvgCostVariacion, $digitos) .' '. $EnteredGRN->SerieNo.
                             "', " . ($EstimatedAvgCostVariacion) . ',
                                 '.$unidadnegocio.')';
                        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');
                        $DbgMsg = _('The following SQL to insert the GL transaction was used');
                        //$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                        $Narrative=' Prov. '.$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' @ ' . $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal." ".$_SESSION['SuppTrans']->TranDate." @ "._('Recepcion Compra:') . " " . $EnteredGRN->GRNNo . " - " . (htmlspecialchars_decode($EnteredGRN->ItemCode, ENT_NOQUOTES)) . " x " . $EnteredGRN->This_QuantityInv . " @  " .
                                _(' variacion de precio')  . " " . round($EstimatedAvgCostVariacion, $digitos)  ." TC:".$_SESSION['SuppTrans']->ExRate." ". $EnteredGRN->SerieNo.$_SESSION['SuppTrans']->Comments;
                        //$Narrative= $_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. " - " . _('Recepcion Compra:') . " " . $EnteredGRN->GRNNo . " - " . (htmlspecialchars_decode($EnteredGRN->ItemCode,ENT_NOQUOTES)) . " x " . $EnteredGRN->This_QuantityInv . " @  " .
                        //          _(' variacion de precio')  . " " . round($EstimatedAvgCostVariacion, 2)  ." TC:".$_SESSION['SuppTrans']->ExRate." ". $EnteredGRN->SerieNo .' @ '. $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments." ".$_SESSION['SuppTrans']->TranDate;
                        $montocontable=round($EstimatedAvgCostVariacion, $digitos);
                        // ejecuta funcion para insertar cargo en tabla de contabilidad
                        $ISQL = Insert_Gltrans(
                            $tipodocto,
                            $InvoiceNo,
                            $SQLInvoiceDate,
                            $PeriodNo,
                            $StockGLCode['purchpricevaract'],
                            $Narrative,
                            $unidadnegocio,
                            $_SESSION['UserID'],
                            $_SESSION['SuppTrans']->ExRate,
                            '',
                            '',
                            DB_escape_string(htmlspecialchars_decode($EnteredGRN->ItemCode, ENT_NOQUOTES)),
                            $EnteredGRN->This_QuantityInv,
                            $EnteredGRN->GRNNo,
                            $EnteredGRN->location,
                            0,
                            $_SESSION['SuppTrans']->SupplierID,
                            $EnteredGRN->PONo,
                            $montocontable,
                            $db,
                            '',
                            'INVENTARIO',
                            $EnteredGLCode->JobRef
                        );
                        //var_dump('<br>Consulta SQL 1379: '.$ISQL);
                        $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
                        $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
                        // Se comento pero no se usaba- $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
                            
                        // SI precio en dolares se modifico carga o abona al inventario
                        //echo 'moneda compra:'.$_SESSION['SuppTrans']->CurrCode.'<br>Moneda pais:'.$_SESSION['CountryOfOperation'];
                        if ($EnteredGRN->StdCostUnit==$EnteredGRN->ChgPrice and $_SESSION['SuppTrans']->CurrCode!=$_SESSION['CountryOfOperation']) {
                            $SQL = 'INSERT INTO gltrans (type, 
                                            typeno, 
                                            trandate, 
                                            periodno, 
                                            account, 
                                            narrative, 
                                            amount,
                                            tag) 
                                    VALUES (' . $tipodocto . ', ' .
                                 $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . ', ' .$cuentalmacen.
                                 ", '" . $_SESSION['SuppTrans']->SupplierID.' '.$nomProv . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo .
                                 ' - ' . DB_escape_string(htmlspecialchars_decode($EnteredGRN->ItemCode, ENT_NOQUOTES))  . _(' variacion de tipo de cambio') . ' ' .
                                 number_format($EstimatedAvgCostVariacion, $digitos)  .' '. $EnteredGRN->SerieNo.
                                 "', " . -($EstimatedAvgCostVariacion) . ',
                                     '.$unidadnegocio.')';
                            //echo '<pre>sql:'.$SQL;
                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');
                            $DbgMsg = _('The following SQL to insert the GL transaction was used');
                        //  $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                            $Narrative=' Prov. '.$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' @ ' . $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal." ".$_SESSION['SuppTrans']->TranDate._('Recepcion Compra:') . " " . $EnteredGRN->GRNNo . " - " . (htmlspecialchars_decode($EnteredGRN->ItemCode, ENT_NOQUOTES)) . " x " . $EnteredGRN->This_QuantityInv . " @  " .
                                    _(' variacion de tipo de cambio')  . " " . round(-$EstimatedAvgCostVariacion, $digitos)  ." TC:".$_SESSION['SuppTrans']->ExRate." ". $EnteredGRN->SerieNo.$_SESSION['SuppTrans']->Comments;
                            //$Narrative= $_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. " - " . _('Recepcion Compra:') . " " . $EnteredGRN->GRNNo . " - " . (htmlspecialchars_decode($EnteredGRN->ItemCode,ENT_NOQUOTES)) . " x " . $EnteredGRN->This_QuantityInv . " @  " .
                                //      _(' variacion de tipo de cambio')  . " " . round(-$EstimatedAvgCostVariacion, 2)  ." TC:".$_SESSION['SuppTrans']->ExRate." ". $EnteredGRN->SerieNo .' @ '. $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments." ".$_SESSION['SuppTrans']->TranDate;
                            $montocontable=round(-$EstimatedAvgCostVariacion, $digitos);
                            $ISQL = Insert_Gltrans(
                                $tipodocto,
                                $InvoiceNo,
                                $SQLInvoiceDate,
                                $PeriodNo,
                                $cuentalmacen,
                                $Narrative,
                                $unidadnegocio,
                                $_SESSION['UserID'],
                                $_SESSION['SuppTrans']->ExRate,
                                '',
                                '',
                                DB_escape_string(htmlspecialchars_decode($EnteredGRN->ItemCode, ENT_NOQUOTES)),
                                $EnteredGRN->This_QuantityInv,
                                $EnteredGRN->GRNNo,
                                $EnteredGRN->location,
                                0,
                                $_SESSION['SuppTrans']->SupplierID,
                                $EnteredGRN->PONo,
                                $montocontable,
                                $db,
                                '',
                                'INVENTARIO',
                                $EnteredGLCode->JobRef
                            );
                            //var_dump('<br>Consulta SQL 1436: '.$ISQL);
                            $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
                            $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
                            // Se comento pero no se usaba- $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
                        }
                    }// fin de insertar mov de variacion de precio
                }// FIN de variacion de precio
                    
                    $LocalTotal += round(((($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)))) / $_SESSION['SuppTrans']->ExRate, $digitos);
                //}// fin de shipref
            } /*Thats the end of the GL postings */
            
            /*DEPENDIENDO SI SE INSERTA UNA FACTURA O CONSIGNACION SE HACEN LOS SIGUIENTES MOVIMIENTOS*/
            if (isset($_POST['PostInvoice'])) {
                            /*for($contivas=1;$contivas <=$_POST['totalivas'];$contivas++){

                                    $totalimpuesto += $_POST['iva_'.$contivas];
                            }*/
                foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {
                    $totalfact = round($_SESSION['SuppTrans']->OvAmount, $digitos);
                }
                
                /*if(round($totalimpuesto, 2) <> round($totalfact,2)){
                                    prnMsg(_('El subtotal de la factura no coincide con el de impuestos, favor de verificarlo'),'error');
                                    exit;
                }*/
                            /*********************************************************/
                
                foreach ($_SESSION['SuppTrans']->GRNs as $producto) {
                    //buscar margen automatico para costo en categoria de inventario
                    $qry = "SELECT margenautcost, taxauthrates.taxrate, stockcategory.stockact
                    FROM stockcategory
                    INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
                                        INNER JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
                    WHERE stockmaster.stockid = '" . $producto->ItemCode . "'";
                
                    $rsm = DB_query($qry, $db);
                    $rowm = DB_fetch_array($rsm);
                    $porcentaje_impuesto= 1 + $rowm['taxrate'];
                    $cuenta_categoria= $rowm['stockact'];
                                
                    $importe_producto= ($producto->ChgPrice * $producto->This_QuantityInv) * (1-($producto->Desc1/100)) * (1-($producto->Desc2/100)) * (1-($producto->Desc3/100));
                    $Narrative = ' Prov. '.$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' @ ' . $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal." ".$_SESSION['SuppTrans']->TranDate.
                                _('Inv') . ' ' .$_SESSION['SuppTrans']->SuppReference . ' ' . $Tax->TaxAuthDescription . ' ' . number_format($rowm['taxrate']*100, $digitos) . '% ' . $_SESSION['SuppTrans']->CurrCode .' '. number_format($rowm['taxrate']*$importe_producto, 2) . ' @ ' . _('exch rate') . ' ' . $_SESSION['SuppTrans']->ExRate.
                    $montocontable=round(($rowm['taxrate']*$importe_producto)/ $_SESSION['SuppTrans']->ExRate, $digitos);
                                            
                    $ISQL = Insert_Gltrans(
                        $tipodocto,
                        $InvoiceNo,
                        $SQLInvoiceDate,
                        $PeriodNo,
                        $cuenta_categoria,
                        $Narrative,
                        $unidadnegocio,
                        $_SESSION['UserID'],
                        $_SESSION['SuppTrans']->ExRate,
                        '',
                        '',
                        '',
                        0,
                        0,
                        '',
                        0,
                        $_SESSION['SuppTrans']->SupplierID,
                        '',
                        $montocontable,
                        $db,
                        '',
                        $Tax->TaxAuthDescription,
                        $EnteredGLCode->JobRef
                    );
                    //var_dump('<br>Consulta SQL 1507: '.$ISQL);
                    $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
                    $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
                    // Se comento pero si se usaba- $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
                }
                                    
            //                                foreach ($_SESSION['SuppTrans']->Taxes as $Tax)
            //                              {
                    /* Now the TAX account */
            //                  if ($Tax->TaxOvAmount <>0)
            //                                      {
                            
            /*                                         $SQL = 'INSERT INTO gltrans (type,
                                                    typeno,
                                                           trandate,
                                                            periodno,
                                                            account,
                                                            narrative,
                                                            amount,
                                                            tag)
                                            VALUES (' . $tipodocto . ', ' .
                                                    $InvoiceNo . ",
                                                    '" . $SQLInvoiceDate . "',
                                                    " . $PeriodNo . ',
                                                    ' . $Tax->TaxGLCode . ",
                                                    '" . $_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' - ' . _('Inv') . ' ' .$_SESSION['SuppTrans']->SuppReference .' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal. ' ' . $Tax->TaxAuthDescription . ' ' . number_format($Tax->TaxRate*100,2) . '% ' . $_SESSION['SuppTrans']->CurrCode .' '.
                                                        $Tax->TaxOvAmount  . ' @ ' . _('exch rate') . ' ' . $_SESSION['SuppTrans']->ExRate .' '.$_SESSION['SuppTrans']->Comments."',
                                                    " . round( $Tax->TaxOvAmount/ $_SESSION['SuppTrans']->ExRate, 2) . ',
                                                    '.$unidadnegocio.')';
                                            
                                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the tax could not be added because');
                                            $DbgMsg = _('The following SQL to insert the GL transaction was used');
                                            //$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                                            $Narrative = ' Prov. '.$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' @ ' . $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal." ".$_SESSION['SuppTrans']->TranDate.
                                            _('Inv') . ' ' .$_SESSION['SuppTrans']->SuppReference . ' ' . $Tax->TaxAuthDescription . ' ' . number_format($Tax->TaxRate*100,2) . '% ' . $_SESSION['SuppTrans']->CurrCode .' '.$Tax->TaxOvAmount  . ' @ ' . _('exch rate') . ' ' . $_SESSION['SuppTrans']->ExRate.
                                            //$Narrative= $_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' - ' . _('Inv') . ' ' .$_SESSION['SuppTrans']->SuppReference . ' ' . $Tax->TaxAuthDescription . ' ' . number_format($Tax->TaxRate*100,2) . '% ' . $_SESSION['SuppTrans']->CurrCode .' '.$_SESSION['SuppTrans']->Comments;
                                             //         $Tax->TaxOvAmount  . ' @ ' . _('exch rate') . ' ' . $_SESSION['SuppTrans']->ExRate.' @ '. $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments." ".$_SESSION['SuppTrans']->TranDate;
                                            $montocontable=round( $Tax->TaxOvAmount/ $_SESSION['SuppTrans']->ExRate, 2);
                                            
                                            $ISQL = Insert_Gltrans($tipodocto,$InvoiceNo,$SQLInvoiceDate,$PeriodNo, $Tax->TaxGLCode,$Narrative,$unidadnegocio ,$_SESSION['UserID'], $_SESSION['SuppTrans']->ExRate,
                                                            '','','',0,0,'',0,$_SESSION['SuppTrans']->SupplierID ,'', $montocontable,$db,'',$Tax->TaxAuthDescription ,$EnteredGLCode->JobRef);
                                            
                                            //var_dump('<br>Consulta SQL'.$ISQL);
                                            $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
                                            $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
                                            $Result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
                            
                    }
                } */
                            
                                
                
                /* inserta el movimiento a proveedores o a consignacion*/
                if (isset($_POST['PostInvoice'])) {
                                    $cuentacontable = $_SESSION['SuppTrans']->CreditorsAct;
                                    $monto = round(($LocalTotal + ( $_SESSION['SuppTrans']->Taxes[0]->TaxOvAmount / $_SESSION['SuppTrans']->ExRate)), $digitos);
                                    $monto = abs($monto);
                }
                                
                //                                echo '<br>MontoContable:'.$monto;
                //                                echo '<br>LocalTotal:'.$LocalTotal;
                //                                echo '<br>TaxTotal:'.$TaxTotal;
                //                                echo '<br>Taxes:'.$_SESSION['SuppTrans']->Taxes[0]->TaxOvAmount;
                //                                echo '<br>Exrate:'.$_SESSION['SuppTrans']->ExRate;
                //                                exit;
                $SQL = 'INSERT INTO gltrans (type,
                                typeno,
                                trandate,
                                periodno,
                                account,
                                narrative,
                                amount,
                                tag)
                        VALUES (' . $tipodocto . ', ' .
                            $InvoiceNo
                            . ", '" . $SQLInvoiceDate
                            . "', " . $PeriodNo
                            . ', ' . $cuentacontable
                            . ", '" . $_SESSION['SuppTrans']->SupplierID.' '.$nomProv . ' - ' . _('Inv') . ' ' . $_SESSION['SuppTrans']->SuppReference . ' ' . $_SESSION['SuppTrans']->CurrCode .' '.
                                number_format($_SESSION['SuppTrans']->OvAmount + $TaxTotal, $digitos)  . ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate . "', "
                            . (-1*$monto)
                            . ', ' . $unidadnegocio.')';
                                
                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the control total could not be added because');
                $DbgMsg = _('The following SQL to insert the GL transaction was used');
                //$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                $Narrative = ' Prov. '.$_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' @ ' . $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal." ".$_SESSION['SuppTrans']->TranDate._('Inv') . ' ' . $_SESSION['SuppTrans']->SuppReference . ' ' . $_SESSION['SuppTrans']->CurrCode .' '.
                        number_format($_SESSION['SuppTrans']->OvAmount + $TaxTotal, $digitos)  . ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate.$_SESSION['SuppTrans']->Comments;
                //$Narrative=  $_SESSION['SuppTrans']->SupplierID.' '.$nomProv .' '.$supplierRFC. ' - ' . _('Inv') . ' ' . $_SESSION['SuppTrans']->SuppReference . ' ' . $_SESSION['SuppTrans']->CurrCode .' '.
                    //          number_format( $_SESSION['SuppTrans']->OvAmount + $TaxTotal,2)  . ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate .' @ '. $_SESSION['SuppTrans']->SuppReference.' | '.$_SESSION['SuppTrans']->SuppReferenceFiscal.' '. $_SESSION['SuppTrans']->Comments." ".$_SESSION['SuppTrans']->TranDate;
                $montocontable= (-1*$monto);
                $ISQL = Insert_Gltrans(
                    $tipodocto,
                    $InvoiceNo,
                    $SQLInvoiceDate,
                    $PeriodNo,
                    $cuentacontable,
                    $Narrative,
                    $unidadnegocio,
                    $_SESSION['UserID'],
                    $_SESSION['SuppTrans']->ExRate,
                    '',
                    '',
                    '',
                    0,
                    0,
                    '',
                    0,
                    $_SESSION['SuppTrans']->SupplierID,
                    '',
                    $montocontable,
                    $db,
                    '',
                    'PROVEEDORES',
                    $EnteredGLCode->JobRef
                );
                //var_dump('<br>Consulta SQL 1623: '.$ISQL);

                $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
                $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
                // Se comento pero no se usaba- $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
                //Buscar dias por tipo de pago
                                $SQLind='SELECT
                                                paymentterms.daysbeforedue
                                        FROM suppliers,
                                                taxgroups,
                                                currencies,
                                                paymentterms,
                                                taxauthorities
                                        WHERE suppliers.taxgroupid=taxgroups.taxgroupid
                                        AND suppliers.currcode=currencies.currabrev
                                        AND suppliers.paymentterms=paymentterms.termsindicator
                                        AND suppliers.supplierid ="'.$_SESSION['SuppTrans']->SupplierID.'"';
                                $rowsind=  DB_fetch_array(DB_query($SQLind, $db));
                                $daysbeforedue=$rowsind['daysbeforedue'];
            //                                echo '<br>DaysBefore: '.$daysbeforedue;
                                $date_venc=date('Y-m-d', strtotime('+'.$daysbeforedue.' day'));
                                $_SESSION['SuppTrans']->DueDate=$date_venc;
                //                                echo '<br>FEcha vencimiento:'.$date_venc;
                //                                echo '<br>Due date(A):'.$_SESSION['SuppTrans']->DueDate;
                if (empty($_POST['alt_tagref'])) {
                    $_POST['alt_tagref'] = "0";
                }
                $SQL = 'INSERT INTO supptrans (transno,
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
                    nu_anio_fiscal)
                VALUES ('. $InvoiceNo . ",
                    '" . $unidadnegocio. "', 
                    " . $tipodocto . " , 
                    '" . $_SESSION['SuppTrans']->SupplierID . "',
                    '" . $_SESSION['SuppTrans']->SuppReference . "',
                    '".$_SESSION['SuppTrans']->SuppReferenceFiscal."',
                    now(),              
                    '" . $SQLInvoiceDate . "', 
                    '" .$_SESSION['SuppTrans']->DueDate. "', 
                    " . $_SESSION['SuppTrans']->OvAmount . ', 
                    ' . $TaxTotal . ', 
                    ' .  $_SESSION['SuppTrans']->ExRate . ",
                    
                    '" . $_SESSION['SuppTrans']->Comments . "', 
                    '" . $_SESSION['SuppTrans']->CurrCode  . "', 
                    '".$_POST['alt_tagref']."',
                    '" . $_SESSION['SuppTrans']->unidadEjecutoraGeneral  . "',
                    '" . $_SESSION['ejercicioFiscal']  . "')";
                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier invoice transaction could not be added to the database because');
                $DbgMsg = _('The following SQL to insert the supplier invoice was used');
                //var_dump('<br>Consulta SQL'.$ISQL);
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                $SuppTransID = DB_Last_Insert_ID($db, 'supptrans', 'id');
                $sqllegal = "SELECT tags.legalid
                             FROM tags
                             WHERE tags.tagref = '".$unidadnegocio."'";
                $resultlegal = DB_query($sqllegal, $db);
                $myrowlegal2 = DB_fetch_array($resultlegal);
                
                /*Inserta en tabla relacion de supptrans con ivas */
                /*for($contivas=1;$contivas <=$_POST['totalivas'];$contivas++){
                    if(isset($_POST['iva_'.$contivas]) and $_POST['iva_'.$contivas] <> ""){
                        $SQL = "INSERT INTO supptransimpuesto (supptransimpuesto.taxcatid,
                                         supptransimpuesto.ovgst,
                                         supptransimpuesto.supptransid)
                            VALUES(
                            '".$_POST['idiva_'.$contivas]."',
                            '".$_POST['iva_'.$contivas]."',
                            '".$SuppTransID."')";
                        $Result = DB_query($SQL, $db);
                    }
                }*/
                
                /*Insertar en la tabla de suppcontrarecibo para generar el contra recibo *///
                
                $SQL = "INSERT INTO suppcontrarecibo (
                        type,
                        transno,
                        tagref,
                        legalid,
                        fechafactura,
                        userid,
                        foliofactura,
                        supplierid,
                        ovamount,
                        ovgst,
                        comments
                        )
                        VALUES (
                        '".$tipodocto."',
                        '".$InvoiceNo."',
                        '".$unidadnegocio."',
                        '".$myrowlegal2['legalid']."',
                        NOW(),
                        '".$_SESSION['UserID']."',
                        '".$_SESSION['SuppTrans']->SuppReference."',
                        '".$_SESSION['SuppTrans']->SupplierID."',
                        '".$_SESSION['SuppTrans']->OvAmount."',
                        '".$TaxTotal."',
                        '" . $_SESSION['SuppTrans']->Comments . "'
                        )";
                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier invoice transaction could not be added to the database because');
                $DbgMsg = _('The following SQL to insert the supplier invoice was used');
                //var_dump('<br>Consulta SQL'.$ISQL);
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                $SuppConReciboID = DB_Last_Insert_ID($db, 'suppcontrarecibo', 'id');
                /*Insertar en la tabla de suppcontrarecibo para generar el contra recibo */
                
                /* Insert the tax totals for each tax authority where tax was charged on the invoice */
                                //echo '<pre>Impuestos: '.print_r($_SESSION['SuppTrans']->Taxes);
                foreach ($_SESSION['SuppTrans']->Taxes as $TaxTotals) {
                    $SQL = 'INSERT INTO supptranstaxes (supptransid,
                            taxauthid,
                            taxamount)
                        VALUES (' . $SuppTransID . ',
                            ' . $TaxTotals->TaxAuthID . ',
                            ' . $TaxTotals->TaxOvAmount . ')';
                    $ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier transaction taxes records could not be inserted because');
                    $DbgMsg = _('The following SQL to insert the supplier transaction taxes record was used:');
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }
                
                /* Now update the GRN and PurchOrderDetails records for amounts invoiced */
                foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN) {
                    $SQL = 'UPDATE purchorderdetails
                        SET qtyinvoiced = qtyinvoiced + ' . $EnteredGRN->This_QuantityInv .', invoice_rate = ' . "'" . $_SESSION['SuppTrans']->ExRate . "'" . ',
                            actprice = ' . (($EnteredGRN->ChgPrice) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100))) . ' 
                        WHERE podetailitem = ' . $EnteredGRN->PODetailItem;
                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity invoiced of the purchase order line could not be updated because');
                    $DbgMsg = _('The following SQL to update the purchase order details was used');
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                    
                    $SQL = 'UPDATE grns
                        SET quantityinv = quantityinv + ' . $EnteredGRN->This_QuantityInv . '
                        WHERE grnno = ' . $EnteredGRN->GRNNo;
                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity invoiced off the goods received record could not be updated because');
                    $DbgMsg = _('The following SQL to update the GRN quantity invoiced was used');
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                    // $sqlcom = "SELECT clavepresupuestal
                    //             FROM purchorderdetails
                    //             WHERE podetailitem = '". $EnteredGRN->PODetailItem."'";
                    $sqlcom = "SELECT purchorderdetails.clavepresupuestal, purchorders.realorderno, purchorders.requisitionno
                    FROM purchorderdetails
                    JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
                    WHERE purchorderdetails.podetailitem = '".$EnteredGRN->PODetailItem."'";
                    $resultcom = DB_query($sqlcom, $db);
                    $myrowcom = DB_fetch_array($resultcom);
                    $clavepresupuestalcom = $myrowcom['clavepresupuestal'];
                    $realordernocom = $myrowcom['realorderno'];
                    $requisitionnocom = $myrowcom['requisitionno'];
                    
                    $pricesupp=(($EnteredGRN->ChgPrice) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)));
                    // INSERTA DETALLE DE FACTURA POR PRODUCTOS
                    $SQL="INSERT INTO supptransdetails(supptransid,stockid,description,price,qty,orderno,grns,tagref_det, 
                    clavepresupuestal, ln_clave_iden, requisitionno, comments, period)
                    VALUES(".$SuppTransID.",'".DB_escape_string(htmlspecialchars_decode($EnteredGRN->ItemCode, ENT_NOQUOTES))."',
                    '".DB_escape_string(htmlspecialchars_decode($EnteredGRN->ItemDescription, ENT_NOQUOTES))."','".$pricesupp."',
                    '".$EnteredGRN->This_QuantityInv ."','".$EnteredGRN->PONo."','".$EnteredGRN->GRNNo."','".$EnteredGRN->tagref."', 
                    '".$EnteredGRN->clavepresupuestal."', '".$EnteredGRN->ln_clave_iden."', '".$requisitionnocom."', '".$EnteredGRN->comments."', '".$PeriodNo."')";
                    //var_dump('<br>Consulta SQL'.$ISQL);
                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se puede registrar el detalle de la factura del proveedor');
                    $DbgMsg = _('El SQL utilizado es');
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                     
                    $ivacom = $EnteredGRN->Taxes[0]->TaxRate;//$TaxTotal/$_SESSION['SuppTrans']->OvAmount;
                    $montocom = ($pricesupp * $EnteredGRN->This_QuantityInv);
                    $totaliva = $montocom * $ivacom;
                    
                    // echo '<br>Monto: '.$montocom;
                    // echo '<br>Impuesto: '.$totaliva;
                    // exit;
                    /********************************************************/
                    $montopresupuestal= truncateFloat(($montocom ), $digitos);
                    // GeneraMovimientoContablePresupuesto($tipodocto, "DEVENGADO", "EJERCIDO", $InvoiceNo, $PeriodNo, $montopresupuestal, $EnteredGRN->tagref, $SQLInvoiceDate, $clavepresupuestalcom, $EnteredGRN->PONo, $db);
                    // // Log Presupuesto
                    // $descriptionLog = "Alta de Factura Orden de Compra";
                    // $agregoLog = fnInsertPresupuestoLog($db, $tipodocto, $InvoiceNo, $EnteredGRN->tagref, $clavepresupuestalcom, $PeriodNo, $montopresupuestal, 260, "", $descriptionLog); // Abono
                    // $agregoLog = fnInsertPresupuestoLog($db, $tipodocto, $InvoiceNo, $EnteredGRN->tagref, $clavepresupuestalcom, $PeriodNo, $montopresupuestal * -1, 261, "", $descriptionLog); // Cargo

                    $infoClaves = array();
                    $infoClaves[] = array(
                        'accountcode' => $clavepresupuestalcom
                    );
                    $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                    if (!$respuesta['result']) {
                        $data['msg'] .= $respuesta['mensaje'];
                            $flag++;
                            continue;
                    }
                    $PeriodNo = $respuesta['periodo'];
                    $fechapoliza = $respuesta['fecha'];
                    
                    GeneraMovimientoContablePresupuesto(
                        $tipodocto,
                        "COMPROMETIDO",
                        "DEVENGADO",
                        $InvoiceNo,
                        $PeriodNo,
                        $montopresupuestal,
                        $EnteredGRN->tagref,
                        $fechapoliza,
                        $clavepresupuestalcom,
                        $EnteredGRN->PONo,
                        $db,
                        false,
                        '',
                        '',
                        $EnteredGRN->comments,
                        $EnteredGRN->unidadEjecutora,
                        1,
                        0,
                        $folioPolizaUe
                    );
                    // Log Presupuesto
                    $descriptionLog = "Factura Orden de Compra ".$realordernocom.". Requisición ".$requisitionnocom;
                    $agregoLog = fnInsertPresupuestoLog($db, $tipodocto, $InvoiceNo, $EnteredGRN->tagref, $clavepresupuestalcom, $PeriodNo, $montopresupuestal, 259, "", $descriptionLog, 1, '', 0, $EnteredGRN->unidadEjecutora); // Abono
                    $agregoLog = fnInsertPresupuestoLog($db, $tipodocto, $InvoiceNo, $EnteredGRN->tagref, $clavepresupuestalcom, $PeriodNo, $montopresupuestal * -1, 260, "", $descriptionLog, 1, '', 0, $EnteredGRN->unidadEjecutora); // Cargo

                    // Suficiencia por requisicion
                    $SQL = "SELECT tb_suficiencias.nu_transno, tb_suficiencias.nu_estatus, tb_suficiencias.nu_type
                    FROM tb_suficiencias 
                    LEFT JOIN purchorders ON purchorders.orderno = tb_suficiencias.sn_orderno
                    WHERE purchorders.requisitionno = '".$EnteredGRN->requisitionno."' ORDER BY tb_suficiencias.nu_transno 
                    DESC LIMIT 1";
                    $datosSuf = DB_query($SQL,$db);
                    $myrowSuf = DB_fetch_array($datosSuf);

                    // Obtener periodos para comenzar la separacion de montos
                    $SQL = "SELECT periods.periodno, cat_Months.mes FROM chartdetailsbudgetbytag
                    JOIN periods ON YEAR(periods.lastdate_in_period) = chartdetailsbudgetbytag.anho
                    JOIN cat_Months ON u_mes = MONTH(periods.lastdate_in_period)
                    WHERE chartdetailsbudgetbytag.accountcode = '".$clavepresupuestalcom."'
                    AND periods.periodno <= '".GetPeriod(date('d/m/y'), $db)."'
                    ORDER BY periods.periodno DESC";
                    $ErrMsg = "No se obtuvieron los periodos de la clave ";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    $cantidadVal = abs($montopresupuestal);
                    $movimientoTipo = 'Suficiencia';
                    $TransResult = ""; // No reccorrer ciclo, esta pendiente el acomulado
                    while ($myrow = DB_fetch_array($TransResult)) {
                        // Validar y generar registros en log
                        $disponible = fnInfoPresupuesto($db, $clavepresupuestalcom, $myrow['periodno'], '', '', 0, 0, '', $myrowSuf['nu_type'], $myrowSuf['nu_transno'], 'Reduccion', '', '', '', 1, '', '', 0, 0);
                        
                        foreach ($disponible as $dispo) {
                            if (abs($cantidadVal) == 0) {
                                // Terminar operaciones
                                break;
                            }
                            
                            if ($dispo[$myrow['mes'].$movimientoTipo] > abs(0)) {
                                // Si tiene disponible registrar en el log
                                $cantidadRegistro = 0;
                                if ($dispo[$myrow['mes'].$movimientoTipo] >= abs($cantidadVal)) {
                                    // Registrar cantidad y ya que se tiene mas disponible
                                    $cantidadRegistro = abs($cantidadVal);
                                } else if (abs($cantidadVal) >= $dispo[$myrow['mes'].$movimientoTipo]) {
                                    // Registrar disponible ya que es mayor la cantidad
                                    $cantidadRegistro = abs($dispo[$myrow['mes'].$movimientoTipo]);
                                }

                                $cantidadVal = abs($cantidadVal) - abs($cantidadRegistro);

                                // Log Presupuesto
                                $descriptionLog = "Factura Orden de Compra ".$realordernocom.". Requisición ".$requisitionnocom;
                                $agregoLog = fnInsertPresupuestoLog($db, $tipodocto, $InvoiceNo, $EnteredGRN->tagref, $clavepresupuestalcom, $myrow['periodno'], $cantidadRegistro, 259, "", $descriptionLog, 1, '', 0, $EnteredGRN->unidadEjecutora); // Abono
                                $agregoLog = fnInsertPresupuestoLog($db, $tipodocto, $InvoiceNo, $EnteredGRN->tagref, $clavepresupuestalcom, $myrow['periodno'], $cantidadRegistro * -1, 260, "", $descriptionLog, 1, '', 0, $EnteredGRN->unidadEjecutora); // Cargo

                                if (!$agregoLog) {
                                    $respuesta = false;
                                }
                            }
                        }

                        if (abs($cantidadVal) == 0) {
                            // Terminar operaciones
                            break;
                        }
                    }

                    // Actualizar enlace poliza
                    $sql = "UPDATE gltrans SET purchno = '".$EnteredGRN->PONo."' 
                    WHERE type = '".$tipodocto."' AND typeno = '".$InvoiceNo."'";
                    $result =DB_query($sql, $db);

                    /********************************************************/
                    if (strlen($EnteredGRN->ShiptRef)>0 and $EnteredGRN->ShiptRef != '0') {
                        //echo '<br>entra'.$_SESSION['SuppTrans']->ExRate;
                        /* insert the shipment charge records */
                        $diffPrice = round($EnteredGRN->This_QuantityInv * (( (($EnteredGRN->ChgPrice) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)))  / $_SESSION['SuppTrans']->ExRate) - (($EnteredGRN->StdCostUnit/ $EnteredGRN->rategr))), $digitos); //aqui se quito /$EnteredGRN->rategr porque el valor de StdCostUnit ya esta en pesos
                        
                        $SQL = "INSERT INTO shipmentcharges (shiptref, 
                                            transtype, 
                                            transno, 
                                            stockid, 
                                            value,
                                            shipqty,
                                            diffprice
                                )  VALUES ('" . $EnteredGRN->ShiptRef . "', 
                                    '" . $tipodocto . "', 
                                    '" . $InvoiceNo . "', 
                                    '" . $EnteredGRN->ItemCode . "', 
                                    '" . ((($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)))) / $_SESSION['SuppTrans']->ExRate . "',
                                    '" . $EnteredGRN->This_QuantityInv . "',
                                    '" . $diffPrice . "'
                                )";
                        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment charge record for the shipment') .
                                     ' ' . $EnteredGRN->ShiptRef . ' ' . _('could not be added because');
                        $DbgMsg = _('The following SQL to insert the Shipment charge record was used');
                        //var_dump('<br>Consulta SQL'.$ISQL);
                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                    }
                } /* end of the loop to do the updates for the quantity of order items the supplier has invoiced */
                
                //Actualizacion de tabla de detalle
                foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode) {
                    $SQL="INSERT  INTO supptransdetails(supptransid,stockid,description,price,qty,orderno,grns,tagref_det)
                            VALUES(".$SuppTransID.",'".DB_escape_string(htmlspecialchars_decode($EnteredGLCode->GLCode, ENT_NOQUOTES))."',
                                    '".DB_escape_string(htmlspecialchars_decode($EnteredGLCode->GLActName, ENT_NOQUOTES))."','".$EnteredGLCode->Amount."',
                                    '1','0','0','".$EnteredGLCode->tagref."')";
                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se puede registrar el detalle de la factura del proveedor');
                    $DbgMsg = _('El SQL utilizado es');
                    //var_dump('<br>Consulta SQL'.$ISQL);
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }
                
                /*Add shipment charges records as necessary */
                foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg) {
                    $SQL = "INSERT INTO shipmentcharges (shiptref, 
                        transtype, 
                        transno, 
                        typecost,
                        value,
                        shipqty) 
                    VALUES ('" . $ShiptChg->ShiptRef . "', 
                        '" . $tipodocto . "', 
                        '" . $InvoiceNo . "', 
                        '" . $ShiptChg->typecostid . "', 
                        '" . $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate . "',
                        1
                    )";
                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment charge record for the shipment') . ' ' . $ShiptChg->ShiptRef . ' ' . _('could not be added because');
                    $DbgMsg = _('The following SQL to insert the Shipment charge record was used');
                    //var_dump('<br>Consulta SQL'.$ISQL);
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }
                                
                                $SQLGltrans = "SELECT sum(amount) as totalcargo
                                                FROM gltrans
                                                WHERE gltrans.typeno = '".$InvoiceNo."'
                                                AND gltrans.type = '".$tipodocto."'
                                                AND gltrans.amount >= 0";
                                //echo '<br>SQLcargo<pre>'.$SQLGltrans;
                                $ResGltrans = DB_query($SQLGltrans, $db);
                                $myrowGltrans = DB_fetch_array($ResGltrans);
                                $totalcargo = abs(number_format($myrowGltrans['totalcargo'], $digitos));
                                
                                $SQLGltrans = "SELECT sum(amount) as totalabono
                                                FROM gltrans
                                                WHERE gltrans.typeno = '".$InvoiceNo."'
                                                AND gltrans.type = '".$tipodocto."'
                                                AND gltrans.amount <= 0";
                               // echo '<br>SQLabono<pre>'.$SQLGltrans;
                                $ResGltrans = DB_query($SQLGltrans, $db);
                                $myrowGltrans = DB_fetch_array($ResGltrans);
                                $totalabono = abs(number_format($myrowGltrans['totalabono'], $digitos));
                                
                if ($totalcargo < $totalabono) {
                    $diferencia = $totalabono - $totalcargo;
                    $SQLGltrans = "SELECT gltrans.counterindex
                                            FROM gltrans
                                            WHERE gltrans.typeno = '".$InvoiceNo."'
                                            AND gltrans.type = '".$tipodocto."'
                                            AND gltrans.amount > 0
                                            ORDER BY gltrans.counterindex DESC
                                            LIMIT 1";
                    $ResGltrans = DB_query($SQLGltrans, $db);
                    $myrowGltrans = DB_fetch_array($ResGltrans);
                    $IdMovContUlt = $myrowGltrans['counterindex'];
                                    
                    $SQL = "UPDATE gltrans
                                            SET amount = amount +".$diferencia."
                                            WHERE counterindex =".$IdMovContUlt;
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                } elseif ($totalcargo > $totalabono) {
                    $diferencia = $totalcargo - $totalabono;
                     $SQLGltrans = "SELECT gltrans.counterindex
                                            FROM gltrans
                                            WHERE gltrans.typeno = '".$InvoiceNo."'
                                            AND gltrans.type = '".$tipodocto."'
                                            AND gltrans.amount < 0
                                            ORDER BY gltrans.counterindex DESC
                                            LIMIT 1";
                    $ResGltrans = DB_query($SQLGltrans, $db);
                    $myrowGltrans = DB_fetch_array($ResGltrans);
                    $IdMovContUlt = $myrowGltrans['counterindex'];
                                    
                    $SQL = "UPDATE gltrans
                                            SET amount = amount +".$diferencia."
                                            WHERE counterindex =".$IdMovContUlt;
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }
                
                //consulta de gltrans
                $sql= "SELECT
                    gltrans.type,
                    gltrans.typeno,
                    gltrans.tag,
                    gltrans.trandate,
                    gltrans.periodno,
                    sum(CASE WHEN gltrans.amount > 0 THEN gltrans.amount ELSE 0 END) as amount,
                    tags.tagdescription,
                    day(gltrans.trandate) as daytrandate,
                    month(gltrans.trandate) as monthtrandate,
                    year(gltrans.trandate) as yeartrandate,
                    legalbusinessunit.taxid,
                    legalbusinessunit.address5,
                    systypescat.typename
                FROM  tags, sec_unegsxuser, gltrans, legalbusinessunit, systypescat
                WHERE gltrans.tag = tags.tagref
                    and gltrans.type =" . $tipodocto . "
                    and gltrans.typeno='" . $InvoiceNo . "' 
                    and sec_unegsxuser.tagref = tags.tagref
                    and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' 
                    and legalbusinessunit.legalid = tags.legalid
                    and systypescat.typeid = gltrans.type
                GROUP BY gltrans.type, gltrans.typeno, gltrans.tag, gltrans.trandate,
                    gltrans.periodno, tags.tagdescription, legalbusinessunit.taxid,
                    legalbusinessunit.address5, systypescat.typename
                ORDER BY gltrans.trandate, gltrans.type, gltrans.typeno";
                //var_dump('<br>Consulta SQL'.$ISQL);
                
                $Result4 = DB_query($sql, $db, $ErrMsg);
                $myrow2=DB_fetch_array($Result4);
                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The SQL COMMIT failed because');
                $DbgMsg = _('The SQL COMMIT failed');
                //$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                $Result = DB_Txn_Commit($db);
                prnMsg(_('<br>La Factura ') . ' ' . $_SESSION['SuppTrans']->SuppReference . ' ' . _('del Proveedor ha sido Procesada exitosamente...'), 'success');
                // Agregar aqui las opciones finales del proceso
                echo "<br>";
                echo '<table class="table table-bordered">';
                echo '<tr class="header-verde">';
                echo '<th align="center" width="8%" colspan=1 style="text-align:center;"><b>'._("Factura de Proveedor").'</b></th>';
                echo '<th align="center" width="8%" colspan=1 style="text-align:center;"><b>'._("Proveedor").'</b></th>';
                echo '<th align="center" width="8%" colspan=1 style="text-align:center;"><b>'._("Total").'</b></th>';
                echo '<th align="center" width="8%" colspan=1 style="text-align:center;"><b>'._("XML").'</b></th>';
                echo '<th align="center" width="8%" colspan=1 style="text-align:center;"><b>'._("Contrarecibo").'</b></th>';
                echo '<th align="center" width="8%" colspan=1 style="text-align:center;"><b>'._("Póliza").'</b></th>';
                echo '<th align="center" width="8%" colspan=1><b>'._("Acciones Siguientes").'</b></th>';
                echo '</tr>';
                echo '<tr>';
                echo '<td align="center" width="8%" style="text-align:center;"><b>'.$myrow2['typeno']."</b></td>";
                //Busqueda de proveedor
                $SQLseasupp="SELECT suppname FROM suppliers WHERE supplierid='".$_SESSION['SuppTrans']->SupplierID."';";
                $rowseasupp=  DB_fetch_array(DB_query($SQLseasupp, $db));
                $suppliername=$rowseasupp['suppname'];
                echo '<td align="center" width="8%" style="text-align:center;"><b>'.$suppliername."</b></td>";
                echo '<td align="center" width="8%" style="text-align:center;"><b>$'.number_format($_SESSION['SuppTrans']->OvAmount, $digitos)."</b></td>";
                if ($_SESSION['subirxmlprov'] == 1) {
                    $liga2="SubirXMLProveedor.php?debtorno=".$_SESSION['SuppTrans']->SupplierID."&propietarioid=".$_SESSION['SuppTrans']->SupplierID."&NoOrden=".$SuppTransID."&tipopropietarioid=6&muetraarchivos=0";
                    echo "<td align='center' width='8%' style='text-align:center;'><a TARGET='_blank' href='" . $liga2 . "'>" . _('') . "<img src='".$rootpath."/images/subir_xml_21.png' title='" . _('Subir XML') . "' alt=''></a></td>";
                }

                // if (Havepermission($_SESSION['UserID'], 1128, $db) == 1) {
                    $enc = new Encryption;
                    $url = "&id=>".$SuppConReciboID;
                    $url = $enc->encode($url);
                    $liga= "URL=" . $url;

                    $liga2 ="id=".$SuppConReciboID;
                    echo "<td align='center' width='8%' style='text-align:center;'>
                    <a TARGET='_blank' href='PDFContraRecibo.php?".$liga."'>".('').'<img src="'.$rootpath.'/images/printer.png" title="' . _('Contra Recibo') . '" alt="">
                    </a></td>';
                // }
                //echo '<br><div class="centre"><a href="' . $rootpath . '/SupplierInvoice.php?&SupplierID=' .$_SESSION['SuppTrans']->SupplierID . '">' . _('Capturar otra Factura de este Proveedor') . '</a>';

                $enc = new Encryption;
                $url="FromCust=>1&ToCust=>1&PrintPDF=>Yes&type=>" . $myrow2['type'] . "&TransNo=>" . $myrow2['typeno'] . "&periodo=>" . $myrow2['periodno'] . "&trandate=>" . $myrow2['trandate'];
                $url = $enc->encode($url);
                $liga= "URL=" . $url;

                echo "<td align='center' width='8%' style='text-align:center;'>
                <a TARGET='_blank' href='PrintJournal.php?" . $liga . "'>
                <img src='".$rootpath."/images/printer.png' title='" . _('Póliza') . "' alt=''>
                </a></td>";

                echo "<td align='center' width='8%' style='text-align:center;'>";
                //echo "<a target='' href='SupplierInvoice.php?&SupplierID=".$_SESSION['SuppTrans']->SupplierID."' title=''>" . _('Nueva Factura de Proveedor') . "</a>";
                echo '<component-button type="submit" id="btnRegresar" name="btnRegresar" class="glyphicon glyphicon-share-alt" value="Regresar" onclick="fnRegresarPanel(); return false;"></component-button>';
                echo "</td>";
                echo '</tr>';
                echo '</table>';

                $mensaje_emergente .= '<p><strong>Factura de Proveedor: </strong> '.$_POST['SuppReference'].'</p>';
                $mensaje_emergente .= '<p><strong>Proveedor: </strong> '.$_SESSION['SuppTrans']->SupplierID.' - '.$suppliername.'</p>';
                $mensaje_emergente .= '<p><strong>Total: </strong> '.number_format($_SESSION['SuppTrans']->OvAmount, $_SESSION['DecimalPlaces'], '.', ',').'</p>';

                $procesoterminado = 1;
                $enc = new Encryption;
                $url = "&id=>".$SuppConReciboID;
                $url = $enc->encode($url);
                $liga= "URL=" . $url;

                $liga2 ="id=".$SuppConReciboID;
                $mensaje_emergente .= '<p><strong>Contrarecibo: </strong> <a TARGET="_blank" href="PDFContraRecibo.php?'.$liga.'"><img src="images/printer.png" title="Contra Recibo"></a></p>';

                $enc = new Encryption;
                $url = "&debtorno=>".$_SESSION['SuppTrans']->SupplierID."&propietarioid=>".$_SESSION['SuppTrans']->SupplierID."&NoOrden=>".$SuppTransID."&tipopropietarioid=>6&muetraarchivos=>0";
                $url = $enc->encode($url);
                $liga= "URL=" . $url;

                $liga2="&debtorno=".$_SESSION['SuppTrans']->SupplierID."&propietarioid=".$_SESSION['SuppTrans']->SupplierID."&NoOrden=".$SuppTransID."&tipopropietarioid=6&muetraarchivos=0";
                $mensaje_emergente .= '<p><strong>XML: </strong> <a TARGET="_blank" href="SubirXMLProveedor.php?'.$liga.'"><img src="images/subir_xml_21.png" title="XML"></a></p>';

                unset($_SESSION['SuppTrans']->GRNs);
                unset($_SESSION['SuppTrans']->Shipts);
                unset($_SESSION['SuppTrans']->GLCodes);
                unset($_SESSION['SuppTrans']);
            }
        }
    } else {
        $enc = new Encryption;
        $url = "&SupplierID=>".$_SESSION['SuppTrans']->SupplierID;
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

        $enc = new Encryption;
        $url = "&moneda=>".$_SESSION['SuppTrans']->CurrCode."&unidadnegocio=>".$unidadnegocio."&legalid=>".$legalid."&SupplierID=>".$_SESSION['SuppTrans']->SupplierID;
        $url = $enc->encode($url);
        $liga2= "URL=" . $url;

        // echo '<br><div class="centre"><a class="btn btn-default botonVerde" href="' . $rootpath . '/SuppInvGRNs.php?' . $liga2 . '">' . _('Regresar a Capturar Información') . '</a></div>';

        $enc = new Encryption;
        $url = "&unidadnegocio=>".$unidadnegocio."&legalid=>".$legalid."&dia=>".$dia."&mes=>".$mes."&year=>".$year;
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

        echo "<div align='center'><a href='$rootpath/SupplierInvoice.php?" . $liga ."' class='btn btn-default botonVerde glyphicon glyphicon-share-alt'>" . _('Regresar a Capturar Información') . '</a><br><br></div>';
    }
}

echo '</form>';
include('includes/footer_Index.inc');
//echo '<table align=left style="align-text:left;"><tr><td>';
//    echo '<pre> Taxes:<br>';echo print_r($_SESSION['SuppTrans']);
//    echo '<pre>POST: <br>';echo print_r($_POST);
//echo '</td></tr></table>';

// $mensaje_emergente .= '<div class="row"></div><div class="col-md-6 col-xs-12"><h4><strong>Dependencia:</strong> Hola como estas</h4></div><div class="col-md-6 col-xs-12"><h4><strong>UR:</strong> Bien</h4></div><div class="row"></div>';
// $mensaje_emergente .= '<h4><strong>Contrarecibo: </strong> <a TARGET="_blank" href="PDFContraRecibo.php?"><img src="images/printer.png" title="Contra Recibo"></a></h4>';


if ($procesoterminado == 2) {
    ?>
    <script type="text/javascript">
        var mensajeMod = '<?php echo $mensaje_emergente; ?>';
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, mensajeMod);
    </script>
    <?php
} else if ($mensaje_emergente != "" && $procesoterminado == 1) {
    ?>
    <script type="text/javascript">
        var mensajeMod = '<?php echo $mensaje_emergente; ?>';
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, mensajeMod, '', 'fnRegresarPanel()');
    </script>
    <?php
}
?>
<script type="text/javascript">
  // Aplicar formato del SELECT
fnFormatoSelectGeneral(".legalid");
fnFormatoSelectGeneral(".unidadnegocio");

/**
* Función para regresar al panel
* @return {[type]} [description]
*/
function fnRegresarPanel () {
    window.open("panel_recepcion_compra.php", "_self");
}
</script>