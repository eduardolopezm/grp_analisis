<?php
/**
 * Preferencías de la Dependencia
 *
 * @category Configuración
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 20/09/2017
 * Fecha Modificación: 20/09/2017
 * Configuración de movimientos contables
 */

$PageSecurity = 10;
include 'includes/session.inc';
$title = _('Parametrización de la Dependencia');
$funcion = 87;
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'javascripts/libreriasGrid.inc';

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

if (isset($Errors)) {
    unset($Errors);
}

//initialise no input errors assumed initially before we test
$InputError = 0;
$Errors     = array();
$i          = 1;

echo '<div name="divTabla" id="divTabla">
          <div id="divCatalogo" name="divCatalogo"></div>
      </div>';

if (isset($_POST['submit'])) {
    /* actions to take once the user has clicked the submit button
    ie the page has called itself with some user input */
    //first off validate inputs sensible

    if (strlen($_POST['CoyName']) > 150 or strlen($_POST['CoyName']) == 0) {
        $InputError = 1;
        prnMsg(_('El nombre de la Dependencia debe de ser capturado y debe de ser cincuenta caracteres de largo o menos'), 'error');
        $Errors[$i] = 'CoyName';
        $i++;
    }
    if (strlen($_POST['RegOffice1']) > 40) {
        $InputError = 1;
        prnMsg(_('La linea uno de la direccion debe de ser cuarenta caracteres o menos de largo'), 'error');
        $Errors[$i] = 'RegOffice1';
        $i++;
    }
    if (strlen($_POST['RegOffice2']) > 40) {
        $InputError = 1;
        prnMsg(_('La linea dos de la direccion debe de ser cuarenta caracteres o menos de largo'), 'error');
        $Errors[$i] = 'RegOffice2';
        $i++;
    }
    if (strlen($_POST['RegOffice3']) > 40) {
        $InputError = 1;
        prnMsg(_('La linea tres de la direccion debe de ser cuarenta caracteres o menos de largo'), 'error');
        $Errors[$i] = 'RegOffice3';
        $i++;
    }
    if (strlen($_POST['RegOffice4']) > 40) {
        $InputError = 1;
        prnMsg(_('La linea cuatro de la direccion debe de ser cuarenta caracteres o menos de largo'), 'error');
        $Errors[$i] = 'RegOffice4';
        $i++;
    }
    if (strlen($_POST['RegOffice5']) > 20) {
        $InputError = 1;
        prnMsg(_('La linea cinco de la direccion debe de ser veinte caracteres o menos de largo'), 'error');
        $Errors[$i] = 'RegOffice5';
        $i++;
    }
    if (strlen($_POST['RegOffice6']) > 15) {
        $InputError = 1;
        prnMsg(_('La linea seis de la direccion debe de ser quince caracteres o menos de largo'), 'error');
        $Errors[$i] = 'RegOffice6';
        $i++;
    }
    if (strlen($_POST['Telephone']) > 25) {
        $InputError = 1;
        prnMsg(_('El campo Telefono debe de ser veinticinco caracteres o menos de largo'), 'error');
        $Errors[$i] = 'Telephone';
        $i++;
    }
    if (strlen($_POST['Fax']) > 25) {
        $InputError = 1;
        prnMsg(_('El campo Numero de Fax debe de ser veinticinco caracteres o menos de largo'), 'error');
        $Errors[$i] = 'Fax';
        $i++;
    }
    if (strlen($_POST['Email']) > 55) {
        $InputError = 1;
        prnMsg(_('El campo Email debe de ser cincuenta y cinco caracteres o menos de largo'), 'error');
        $Errors[$i] = 'Email';
        $i++;
    }
    if (strlen($_POST['Email']) > 0 and !IsEmailAddress($_POST['Email'])) {
        $InputError = 1;
        prnMsg(_('El campo Email no esta formado correctamente'), 'error');
        $Errors[$i] = 'Email';
        $i++;
    }

    if ($InputError != 1) {
        $reg = "UPDATE legalbusinessunit SET regimenfiscal='" . $_POST['regimen'] . "' WHERE empresafiscal=1 ";
        DB_query($reg, $db);

        $sql = "UPDATE companies SET
                coyname='" . $_POST['CoyName'] . "',
                companynumber = '" . $_POST['CompanyNumber'] . "',
                gstno='" . $_POST['GSTNo'] . "',
                regoffice1='" . $_POST['RegOffice1'] . "',
                regoffice2='" . $_POST['RegOffice2'] . "',
                regoffice3='" . $_POST['RegOffice3'] . "',
                regoffice4='" . $_POST['RegOffice4'] . "',
                regoffice5='" . $_POST['RegOffice5'] . "',
                regoffice6='" . $_POST['RegOffice6'] . "',
                telephone='" . $_POST['Telephone'] . "',
                fax='" . $_POST['Fax'] . "',
                email='" . $_POST['Email'] . "',

                currencydefault='" . $_POST['CurrencyDefault'] . "',
                debtorsact='" . $_POST['DebtorsAct'] . "',
                pytdiscountact='" . $_POST['PytDiscountAct'] . "',
                creditorsact='" . $_POST['CreditorsAct'] . "',
                payrollact='" . $_POST['PayrollAct'] . "',
                grnact='" . $_POST['GRNAct'] . "',
                exchangediffact='" . $_POST['ExchangeDiffAct'] . "',
                purchasesexchangediffact='" . $_POST['PurchasesExchangeDiffAct'] . "',
                retainedearnings='" . $_POST['RetainedEarnings'] . "',
                gllink_debtors='" . $_POST['GLLink_Debtors'] . "',
                gllink_creditors='" . $_POST['GLLink_Creditors'] . "',
                gllink_stock='" . $_POST['GLLink_Stock'] . "',
                freightact='" . $_POST['FreightAct'] . "',
                gllink_notesdebtors='" . $_POST['gllink_notesdebtors'] . "',
                gllink_advancesdebtors='" . $_POST['gllink_advancesdebtors'] . "',
                gllink_moratorios='" . $_POST['gllink_moratorios'] . "',
                gltempcashpayment='" . $_POST['gltempcashpayment'] . "',
                gltempcheckpayment='" . $_POST['gltempcheckpayment'] . "',
                gltempccpayment='" . $_POST['gltempccpayment'] . "',
                gltemptransferpayment='" . $_POST['gltemptransferpayment'] . "',
                gltempcheckpostpayment='" . $_POST['gltempcheckpostpayment'] . "',
                gllink_taxadvance='" . $_POST['taxadvance'] . "',
                gllink_Invoice='" . $_POST['Invoice'] . "',
                creditnote='" . $_POST['creditnote'] . "',
                debitnote='" . $_POST['debitnote'] . "',
                gllink_loccpuente='" . $_POST['gllink_loccpuente'] . "',
                gllink_acreeddiversos='" . $_POST['gllink_acreeddiversos'] . "',
                gllink_intpordevengar='" . $_POST['gllink_intpordevengar'] . "',
                gllink_intdevengados='" . $_POST['gllink_intdevengados'] . "',
                gllink_dxctransferenciacredito='" . $_POST['gllink_dxctransferenciacredito'] . "',
                gllink_deudoresdiversos='" . $_POST['gllink_deudoresdiversos'] . "',
                gllink_retencioniva='" . $_POST['gllink_retencioniva'] . "',
                gllink_retencionhonorarios='" . $_POST['gllink_retencionhonorarios'] . "',
                gllink_retencionCedular='" . $_POST['gllink_retencionCedular'] . "',
                gllink_retencionFletes='" . $_POST['gllink_retencionFletes'] . "',
                gllink_retencionComisiones='" . $_POST['gllink_retencionComisiones'] . "',
                gllink_retencionarrendamiento='" . $_POST['gllink_retencionarrendamiento'] . "',
                gllink_retencionIVAarrendamiento='" . $_POST['gllink_retencionIVAarrendamiento'] . "',
                gllink_sobrantesfaltantescaja='" . $_POST['gllink_sobrantesfaltantescaja'] . "',
                gllink_purchasesexchangediffactutil='" . $_POST['gllink_purchasesexchangediffactutil'] . "',
                gllink_exchangediffactutil='" . $_POST['gllink_exchangediffactutil'] . "',
                gllink_shipmentclose='" . $_POST['gllink_shipmentclose'] . "',
                gltempsuppconsignment='" . $_POST['GRNAct2'] . "',
                gllink_presupuestalingresoEjecutar='" . $_POST['gllink_presupuestalingresoEjecutar'] . "',
                gllink_presupuestalingresoModificado='" . $_POST['gllink_presupuestalingresoModificado'] . "',
                gllink_presupuestalingresoDevengado='" . $_POST['gllink_presupuestalingresoDevengado'] . "',
                gllink_presupuestalingresoRecaudado='" . $_POST['gllink_presupuestalingresoRecaudado'] . "',
                gllink_presupuestalegresoEjercer='" . $_POST['gllink_presupuestalegresoEjercer'] . "',
                gllink_presupuestalegresoModificado='" . $_POST['gllink_presupuestalegresoModificado'] . "',
                gllink_presupuestalegresocomprometido='" . $_POST['gllink_presupuestalegresocomprometido'] . "',
                gllink_presupuestalegresodevengado='" . $_POST['gllink_presupuestalegresodevengado'] . "',
                gllink_presupuestalegresopagado='" . $_POST['gllink_presupuestalegresopagado'] . "',
                gllink_presupuestalegresoejercido='" . $_POST['gllink_presupuestalegresoejercido'] . "',
                        gllink_presupuestalegreso='" . $_POST['gllink_presupuestalegreso'] . "',
                        gllink_presupuestalingreso='" . $_POST['gllink_presupuestalingreso'] . "'
            WHERE coycode=1";

        $ErrMsg = _('La parametrizacion de la Dependencia no pudo ser actualizada por');
        $result = DB_query($sql, $db, $ErrMsg);
        prnMsg(_('Parametrizacion de Dependencia Actualizada'), 'success');

        /* Alter the exchange rates in the currencies table */

        /* Get default currency rate */
        $sql    = 'SELECT rate from currencies WHERE currabrev="' . $_POST['CurrencyDefault'] . '"';
        $result = DB_query($sql, $db);

        $myrow           = DB_fetch_row($result);
        $NewCurrencyRate = $myrow[0];

        /* Set new rates */
        $sql = 'UPDATE currencies SET rate=rate/' . $NewCurrencyRate;

        $ErrMsg = _('No se pudo actualizar los tipos de cambio');
        $result = DB_query($sql, $db, $ErrMsg);

        /* End of update currencies */

        $ForceConfigReload = true; // Required to force a load even if stored in the session vars
        include 'includes/GetConfig.php';
        $ForceConfigReload = false;
    } else {
        prnMsg(_('Validacion fallida') . ', ' . _('no hubo actualizaciones o eliminaciones'), 'warn');
    }
} /* end of if submit */

echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '>';
echo '<table>';

if ($InputError != 1) {
    $sql = "SELECT *
            FROM companies 
            INNER JOIN legalbusinessunit ON coycode=empresafiscal
            WHERE coycode=1";

    $ErrMsg = _('La parametrizacion de la Dependencia no pudo ser obtenida por');
    $result = DB_query($sql, $db, $ErrMsg);

    $myrow = DB_fetch_array($result);

    $_POST['CoyName']                        = $myrow['coyname'];
    $_POST['GSTNo']                          = $myrow['gstno'];
    $_POST['CompanyNumber']                  = $myrow['companynumber'];
    $_POST['RegOffice1']                     = $myrow['regoffice1'];
    $_POST['RegOffice2']                     = $myrow['regoffice2'];
    $_POST['RegOffice3']                     = $myrow['regoffice3'];
    $_POST['RegOffice4']                     = $myrow['regoffice4'];
    $_POST['RegOffice5']                     = $myrow['regoffice5'];
    $_POST['RegOffice6']                     = $myrow['regoffice6'];
    $_POST['Telephone']                      = $myrow['telephone'];
    $_POST['Fax']                            = $myrow['fax'];
    $_POST['Email']                          = $myrow['email'];
    $_POST['regimen']                        = $myrow['regimenfiscal'];
    $_POST['CurrencyDefault']                = $myrow['currencydefault'];
    $_POST['DebtorsAct']                     = $myrow['debtorsact'];
    $_POST['PytDiscountAct']                 = $myrow['pytdiscountact'];
    $_POST['CreditorsAct']                   = $myrow['creditorsact'];
    $_POST['PayrollAct']                     = $myrow['payrollact'];
    $_POST['GRNAct']                         = $myrow['grnact'];
    $_POST['ExchangeDiffAct']                = $myrow['exchangediffact'];
    $_POST['PurchasesExchangeDiffAct']       = $myrow['purchasesexchangediffact'];
    $_POST['RetainedEarnings']               = $myrow['retainedearnings'];
    $_POST['gllink_notesdebtors']            = $myrow['gllink_notesdebtors'];
    $_POST['gllink_advancesdebtors']         = $myrow['gllink_advancesdebtors'];
    $_POST['gllink_moratorios']              = $myrow['gllink_moratorios'];
    $_POST['gltempcashpayment']              = $myrow['gltempcashpayment'];
    $_POST['gltempcheckpayment']             = $myrow['gltempcheckpayment'];
    $_POST['gltempccpayment']                = $myrow['gltempccpayment'];
    $_POST['gltemptransferpayment']          = $myrow['gltemptransferpayment'];
    $_POST['gltempcheckpostpayment']         = $myrow['gltempcheckpostpayment'];
    $_POST['GLLink_Debtors']                 = $myrow['gllink_debtors'];
    $_POST['GLLink_Creditors']               = $myrow['gllink_creditors'];
    $_POST['GLLink_Stock']                   = $myrow['gllink_stock'];
    $_POST['FreightAct']                     = $myrow['freightact'];
    $_POST['taxadvance']                     = $myrow['gllink_taxadvance'];
    $_POST['Invoice']                        = $myrow['gllink_Invoice'];
    $_POST['creditnote']                     = $myrow['creditnote'];
    $_POST['debitnote']                      = $myrow['debitnote'];
    $_POST['gllink_loccpuente']              = $myrow['gllink_loccpuente'];
    $_POST['gllink_acreeddiversos']          = $myrow['gllink_acreeddiversos'];
    $_POST['gllink_intpordevengar']          = $myrow['gllink_intpordevengar'];
    $_POST['gllink_intdevengados']           = $myrow['gllink_intdevengados'];
    $_POST['gllink_dxctransferenciacredito'] = $myrow['gllink_dxctransferenciacredito'];
    $_POST['gllink_deudoresdiversos']        = $myrow['gllink_deudoresdiversos'];
    $_POST['gllink_retencioniva']            = $myrow['gllink_retencioniva'];
    $_POST['gllink_retencionhonorarios']     = $myrow['gllink_retencionhonorarios'];

    $_POST['gllink_retencionCedular']             = $myrow['gllink_retencionCedular'];
    $_POST['gllink_retencionFletes']              = $myrow['gllink_retencionFletes'];
    $_POST['gllink_retencionComisiones']          = $myrow['gllink_retencionComisiones'];
    $_POST['gllink_retencionarrendamiento']       = $myrow['gllink_retencionarrendamiento'];
    $_POST['gllink_retencionIVAarrendamiento']    = $myrow['gllink_retencionIVAarrendamiento'];
    $_POST['gllink_purchasesexchangediffactutil'] = $myrow['gllink_purchasesexchangediffactutil'];
    $_POST['gllink_exchangediffactutil']          = $myrow['gllink_exchangediffactutil'];

    $_POST['gllink_sobrantesfaltantescaja']         = $myrow['gllink_sobrantesfaltantescaja'];
    $_POST['gllink_shipmentclose']                  = $myrow['gllink_shipmentclose'];
    $_POST['GRNAct2']                               = $myrow['gltempsuppconsignment'];
    $_POST['gllink_presupuestalingreso']            = $myrow['gllink_presupuestalingreso'];
    $_POST['gllink_presupuestalingresoEjecutar']    = $myrow['gllink_presupuestalingresoEjecutar'];
    $_POST['gllink_presupuestalingresoModificado']  = $myrow['gllink_presupuestalingresoModificado'];
    $_POST['gllink_presupuestalingresoDevengado']   = $myrow['gllink_presupuestalingresoDevengado'];
    $_POST['gllink_presupuestalingresoRecaudado']   = $myrow['gllink_presupuestalingresoRecaudado'];
    $_POST['gllink_presupuestalegreso']             = $myrow['gllink_presupuestalegreso'];
    $_POST['gllink_presupuestalegresoEjercer']      = $myrow['gllink_presupuestalegresoEjercer'];
    $_POST['gllink_presupuestalegresoModificado']   = $myrow['gllink_presupuestalegresoModificado'];
    $_POST['gllink_presupuestalegresocomprometido'] = $myrow['gllink_presupuestalegresocomprometido'];
    $_POST['gllink_presupuestalegresodevengado']    = $myrow['gllink_presupuestalegresodevengado'];
    $_POST['gllink_presupuestalegresopagado']       = $myrow['gllink_presupuestalegresopagado'];
    $_POST['gllink_presupuestalegresoejercido']     = $myrow['gllink_presupuestalegresoejercido'];
}

echo '<div class="panel panel-default"><!-- Datos de la Dependencia -->
    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-6 col-xs-6">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelInformacionempresa" aria-expanded="false" aria-controls="collapse" class="collapsed"><span class="glyphicon glyphicon-chevron-down"></span>
                    Información de la Dependencia
                </a>
            </div>
        </h4>
    </div>
    <div id="PanelInformacionempresa" name="PanelInformacionempresa" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>
        
       <div class="text-left container">
       
        <div' . (in_array('CoyName', $Errors) ? 'class="form-group has-error"' : '') .'>
            <component-text-label label="Nombre (para títulos de reportes):" id="CoyName" Name="CoyName" placeholder="Nombre (para títulos de reportes)" maxlength="50"
        value="'. stripslashes($_POST['CoyName']) .'"></component-text-label>
        </div>
        <br/>
      
        <div' . (in_array('CoyNUmber', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Número Oficial de Dependencia (RFC):" id="CompanyNumber" name="CompanyNumber" placeholder="Número Oficial de Dependencia (RFC)" maxlength="20"
                          value="'.$_POST['CompanyNumber'] .'"></component-text-label>
         </div>
     <br>

     <div' . (in_array('TaxRef', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Referencia de Autoridad de Impuestos:" id="GSTNo" name="GSTNo" placeholder="Referencia de Autoridad de Impuestos" maxlength="20"
                          value="'.$_POST['GSTNo'] .'"></component-text-label>
         </div>
         <br>
     <div' . (in_array('RegOffice1', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Calle y Número:" id="RegOffice1" name="RegOffice1" placeholder="Calle y Número" maxlength="40"
                          value="'.stripslashes($_POST['RegOffice1']) .'"></component-text-label>
    </div>
         <br>

      <div' . (in_array('RegOffice2', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Colonia:" id="RegOffice2" name="RegOffice2" placeholder="Colonia" maxlength="40"
                          value="'.stripslashes($_POST['RegOffice2']) .'"></component-text-label>
    </div>
         <br>

    <div' . (in_array('RegOffice3', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Municipio:" id="RegOffice3" name="RegOffice3" placeholder="Municipio" maxlength="40"
                          value="'.stripslashes($_POST['RegOffice2']) .'"></component-text-label>
    </div>
         <br>
     <div' . (in_array('RegOffice4', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Ciudad:" id="RegOffice4" name="RegOffice4" placeholder="Ciudad" maxlength="40"
                          value="'.stripslashes($_POST['RegOffice4']) .'"></component-text-label>
    </div>
         <br>

         <div' . (in_array('RegOffice5', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Código Postal:" id="RegOffice5" name="RegOffice5" placeholder="Código Postal" maxlength="20"
                          value="'.stripslashes($_POST['RegOffice5']) .'"></component-text-label>
        </div>
         <br>

         <div' . (in_array('RegOffice6', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Estado:" id="RegOffice6" name="RegOffice6" placeholder="Estado" maxlength="15"
                          value="'.stripslashes($_POST['RegOffice6']) .'"></component-text-label>
        </div>
         <br>
    
         <div' . (in_array('Telephone', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Número de Télefono:" id="Telephone" name="Telephone" placeholder="Número de Télefono" maxlength="40"
                          value="'.stripslashes($_POST['Telephone']) .'"></component-text-label>
        </div>
         <br>

          <div' . (in_array('Fax', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Número de Fax:" id="Fax" name="Fax" placeholder="Número de Fax" maxlength="25"
                          value="'.stripslashes($_POST['Fax']) .'"></component-text-label>
        </div>
         <br>

           <div' . (in_array('Email', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Dirección Email:" id="Email" name="Email" placeholder="Dirección Email" maxlength="50"
                          value="'.stripslashes($_POST['Email']) .'"></component-text-label>
        </div>
         <br>

            <div' . (in_array('regimen', $Errors) ? 'class="form-group has-error"' : '') .'>
        <component-text-label label="Regímen Fiscal:" id="regimen" name="regimen" placeholder="Regímen Fiscal" maxlength="50"
                          value="'.stripslashes($_POST['regimen']) .'"></component-text-label>
        </div>
         <br>

  ';
/*
//nombre de la Dependencia
echo '<tr><td style="text-align: right;">' . _('Nombre') . ' (' . _('para titulos de reportes') . '):</td>
    <td><input ' . (in_array('CoyName', $Errors) ? 'class="inputerror"' : '') . ' tabindex="1" type="Text" Name="CoyName" value="' . stripslashes($_POST['CoyName']) . '" size=52 maxlength=50></td>
</tr>'; 

//rfc
echo '<tr><td style="text-align: right;">' . _('Numero Oficial de Dependencia (RFC)') . ':</td>
    <td><input ' . (in_array('CoyNumber', $Errors) ? 'class="inputerror"' : '') . ' tabindex="2" type="Text" Name="CompanyNumber" value="' . $_POST['CompanyNumber'] . '" size=22 maxlength=20></td>
    </tr>';

// referencia de autoridad
echo '<tr><td style="text-align: right;">' . _('Referencia de Autoridad de Impuestos') . ':</td>
    <td><input ' . (in_array('TaxRef', $Errors) ? 'class="inputerror"' : '') . ' tabindex="3" type="Text" Name="GSTNo" value="' . $_POST['GSTNo'] . '" size=22 maxlength=20></td>
</tr>';

//calle numero
echo '<tr><td style="text-align: right;">' . _('Calle y Numero') . ':</td>
    <td><input ' . (in_array('RegOffice1', $Errors) ? 'class="inputerror"' : '') . ' tabindex="4" type="Text" Name="RegOffice1" size=42 maxlength=40 value="' . stripslashes($_POST['RegOffice1']) . '"></td>
</tr>';

echo '<tr><td style="text-align: right;">' . _('Colonia') . ':</td>
    <td><input ' . (in_array('RegOffice2', $Errors) ? 'class="inputerror"' : '') . ' tabindex="5" type="Text" Name="RegOffice2" size=42 maxlength=40 value="' . stripslashes($_POST['RegOffice2']) . '"></td>
</tr>';

echo '<tr><td style="text-align: right;">' . _('Municipio') . ':</td>
    <td><input ' . (in_array('RegOffice3', $Errors) ? 'class="inputerror"' : '') . ' tabindex="6" type="Text" Name="RegOffice3" size=42 maxlength=40 value="' . stripslashes($_POST['RegOffice3']) . '"></td>
</tr>';

echo '<tr><td style="text-align: right;">' . _('Ciudad') . ':</td>
    <td><input ' . (in_array('RegOffice4', $Errors) ? 'class="inputerror"' : '') . ' tabindex="7" type="Text" Name="RegOffice4" size=42 maxlength=40 value="' . stripslashes($_POST['RegOffice4']) . '"></td>
</tr>';

echo '<tr><td style="text-align: right;">' . _('Codigo Postal') . ':</td>
    <td><input ' . (in_array('RegOffice5', $Errors) ? 'class="inputerror"' : '') . ' tabindex="8" type="Text" Name="RegOffice5" size=22 maxlength=20 value="' . stripslashes($_POST['RegOffice5']) . '"></td>
</tr>';

echo '<tr><td style="text-align: right;">' . _('Estado') . ':</td>
    <td><input ' . (in_array('RegOffice6', $Errors) ? 'class="inputerror"' : '') . ' tabindex="9" type="Text" Name="RegOffice6" size=17 maxlength=15 value="' . stripslashes($_POST['RegOffice6']) . '"></td>
</tr>'; 

echo '<tr><td style="text-align: right;">' . _('Numero de Telefono') . ':</td>
    <td><input ' . (in_array('Telephone', $Errors) ? 'class="inputerror"' : '') . ' tabindex="10" type="Text" Name="Telephone" size=26 maxlength=25 value="' . $_POST['Telephone'] . '"></td>
</tr>'; 

echo '<tr><td style="text-align: right;">' . _('Numero de Fax') . ':</td>
    <td><input ' . (in_array('Fax', $Errors) ? 'class="inputerror"' : '') . ' tabindex="11" type="Text" Name="Fax" size=26 maxlength=25 value="' . $_POST['Fax'] . '"></td>
</tr>'; 

echo '<tr><td style="text-align: right;">' . _('Direccion Email') . ':</td>
    <td><input ' . (in_array('Email', $Errors) ? 'class="inputerror"' : '') . ' tabindex="12" type="Text" Name="Email" size=50 maxlength=55 value="' . $_POST['Email'] . '"></td>
</tr>'; 
echo '<tr><td style="text-align: right;">' . _('Regimen Fiscal') . ':</td>
    <td><input ' . (in_array('regimen', $Errors) ? 'class="inputerror"' : '') . ' tabindex="12" type="Text" Name="regimen" size=50 maxlength=55 value="' . $_POST['regimen'] . '"></td>
</tr>';*/

$result = DB_query("SELECT currabrev, currency FROM currencies", $db);

//echo '<tr><td style="text-align: right;">' . _('Moneda Local') . ':</td><td>
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Moneda Local: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="CurrencyDefault" tabindex="13" Name=CurrencyDefault class="monedaLocal">';


while ($myrow = DB_fetch_array($result)) {
    if ($_POST['CurrencyDefault'] == $myrow['currabrev']) {
        echo "<option selected VALUE='" . $myrow['currabrev'] . "'>" . $myrow['currency'];
    } else {
        echo "<option VALUE='" . $myrow['currabrev'] . "'>" . $myrow['currency'];
    }
} //end while loop

DB_free_result($result);
echo '</select></div></div> <br>';

echo '   </div><!--container-->
    </div><!--fin contenido -->
    </div><!-- fin panel datos de la Dependencia -->';
//echo '</select></td></tr>';
//echo 'sssss:jjjjjj DebtorsAct:'.$_POST['DebtorsAct'];

// ocultar por el momento esta parde de clientes que no se usa para GRP
$result = DB_query("SELECT accountcode,
            accountname
        FROM chartmaster,
            accountgroups
        WHERE chartmaster.group_=accountgroups.groupname
        AND accountgroups.pandl=0 and 1=0
        ORDER BY chartmaster.accountcode", $db);

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable de Cuentas X Cobrar') . ':</td><td><select tabindex="14" Name=DebtorsAct>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['DebtorsAct'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable de Cuentas X Pagar') . ':</td><td><select tabindex="15" Name=CreditorsAct>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['CreditorsAct'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable de Nominas') . ':</td><td><select tabindex="16" Name=PayrollAct>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['PayrollAct'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Transito de Productos Recibidos') . ':</td><td><select tabindex="17" Name=GRNAct>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['GRNAct'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);
echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Transito de Productos') . ':</td><td><select tabindex="17" Name="GRNAct2">';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['GRNAct2'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);
echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable de Utilidad del Ejercicio') . ':</td><td><select tabindex="18" Name=RetainedEarnings>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['RetainedEarnings'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);
echo '</select></td></tr>';
//**********************************************************************************************
//***************** INICIO AGREGAR CAMPOS DE CUENTAS CONTABLES**********************************
//******************ANTICIPOS CLIENTES, DOCUMENTOS X COBRAR*************************************

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Documentos X Cobrar') . ':</td>';
echo '<td><select tabindex="18" Name=gllink_notesdebtors>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_notesdebtors'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);
echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Anticipos Clientes') . ':</td>';
echo '<td><select tabindex="18" Name=gllink_advancesdebtors>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_advancesdebtors'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);
echo '</select></td></tr>';

//**********************************************************************************************
//***************** FIN AGREGAR CAMPOS DE CUENTAS CONTABLES**********************************
//****************************ANTICIPOS CLIENTES************************************************

//**********************************************************************************************
//***************** INICIO AGREGAR CAMPOS DE CUENTAS CONTABLES**********************************
//*****************************INTERESES MORATORIOS*********************************************

$result = DB_query("SELECT accountcode,
            accountname
        FROM chartmaster,
            accountgroups
        WHERE chartmaster.group_=accountgroups.groupname
        AND accountgroups.pandl=1 AND 1=0
        ORDER BY chartmaster.accountcode", $db);

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Intereses Moratorios') . ':</td>';
echo '<td><select tabindex="18" Name=gllink_moratorios>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_moratorios'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);
echo '</select></td></tr>';

//**********************************************************************************************
//***************** FIN AGREGAR CAMPOS DE CUENTAS CONTABLES**********************************
//*****************************INTERESES MORATORIOS*********************************************

$result = DB_query("SELECT accountcode,
            accountname
        FROM chartmaster,
            accountgroups
        WHERE chartmaster.group_=accountgroups.groupname
        AND accountgroups.pandl=0 AND 1=0
        ORDER BY chartmaster.accountcode", $db);

//**********************************************************************************************
//******************* INICIO AGREGAR  CAMPOS DE CUENTA PUENTE***********************************
//**********************************************************************************************
echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Puente Efectivo') . ':</td>';
echo '<td><select tabindex="18" Name=gltempcashpayment>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gltempcashpayment'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);
echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Puente Cheques') . ':</td>';
echo '<td><select tabindex="18" Name=gltempcheckpayment>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gltempcheckpayment'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);
echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Puente TDC') . ':</td>';
echo '<td><select tabindex="18" Name=gltempccpayment>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gltempccpayment'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);
echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Puente Transferencias') . ':</td>';
echo '<td><select tabindex="18" Name=gltemptransferpayment>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gltemptransferpayment'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);
echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Puente Cheque Postfechado') . ':</td>';
echo '<td><select tabindex="18" Name=gltempcheckpostpayment>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gltempcheckpostpayment'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_free_result($result);
echo '</select></td></tr>';

//**********************************************************************************************
//******************* FIN AGREGAR  CAMPOS DE CUENTA PUENTE***********************************
//**********************************************************************************************
echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Cargos de Embarque y Envio') . ':</td><td><select tabindex="19" Name=FreightAct>';

$result = DB_query('SELECT accountcode,
            accountname
        FROM chartmaster,
            accountgroups
        WHERE chartmaster.group_=accountgroups.groupname
        AND accountgroups.pandl=1 AND 1=0
        ORDER BY chartmaster.accountcode', $db);

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['FreightAct'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Variaciones de Intercambio de Ventas') . ':</td><td><select tabindex="20" Name=ExchangeDiffAct>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['ExchangeDiffAct'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Variaciones de Intercambio de Compras') . ':</td><td><select tabindex="21" Name=PurchasesExchangeDiffAct>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['PurchasesExchangeDiffAct'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';
echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Variaciones <b>Utilidad</b> de Intercambio de Ventas') . ':</td><td><select tabindex="20" Name=gllink_exchangediffactutil>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_exchangediffactutil'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Variaciones <b>Utilidad</b> de Intercambio de Compras') . ':</td><td><select tabindex="20" Name=gllink_purchasesexchangediffactutil>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_purchasesexchangediffactutil'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Descuentos de Pagos') . ':</td><td><select tabindex="22" Name=PytDiscountAct>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['PytDiscountAct'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

$result2 = DB_query("SELECT accountcode,
            accountname
        FROM chartmaster,
            accountgroups
        WHERE chartmaster.group_=accountgroups.groupname
        AND accountgroups.pandl=1 AND 1=0
        ORDER BY chartmaster.accountcode", $db);

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Facturas Contado Pend.') . ':</td><td><select tabindex="22" Name=Invoice>';

$result = DB_query("SELECT accountcode,
            accountname
        FROM chartmaster,
            accountgroups
        WHERE chartmaster.group_=accountgroups.groupname AND 1=0
        -- AND accountgroups.pandl=0 
        ORDER BY chartmaster.accountcode", $db);

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['Invoice'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable IVA Anticipos Clientes') . ':</td><td><select tabindex="22" Name=taxadvance>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['taxadvance'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result2, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Notas de Credito') . ':</td><td><select tabindex="22" Name=creditnote>';

while ($myrow = DB_fetch_row($result2)) {
    if ($_POST['creditnote'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result2, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Notas de Cargo') . ':</td><td><select tabindex="22" Name=debitnote>';

while ($myrow = DB_fetch_row($result2)) {
    if ($_POST['debitnote'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Puente Matriz Sucursales') . ':</td><td><select tabindex="22" Name=gllink_loccpuente>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_loccpuente'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

//CUENTA CONTABLE PARA SOBRANTES O FALTANTES DE CAJA
echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Sobrantes Faltantes Caja') . ':</td><td><select tabindex="22" Name=gllink_sobrantesfaltantescaja>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_sobrantesfaltantescaja'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

//CUENTA PARA CIERRES DE EMBARQUE
echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Provision Cierre de Embarques') . ':</td><td><select tabindex="22" Name=gllink_shipmentclose>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_shipmentclose'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo "<tr style='display: none;'><td colspan='2'><hr></td></tr>";
echo "<tr style='display: none;' height='20px'><td colspan='2' style='text-align:center; font-weight:bold;'>INTEGRACION VENTA PAGARES</td></tr>";

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Acreedores Diversos') . ' (' . _('SOFOM') . '):</td><td><select tabindex="22" Name=gllink_acreeddiversos>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_acreeddiversos'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Deudores Diversos') . ' (' . _('SOFOM') . '):</td><td><select tabindex="22" Name=gllink_deudoresdiversos>';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_deudoresdiversos'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

DB_data_seek($result, 0);

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Interes por Devengar') . ' (' . _('SOFOM') . '):</td><td><select tabindex="22" Name=gllink_intpordevengar>';
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_intpordevengar'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
echo '</select></td></tr>';

$result = DB_query('SELECT accountcode, accountname FROM chartmaster, accountgroups
        WHERE chartmaster.group_=accountgroups.groupname 
        AND accountgroups.pandl=1 AND 1=0
        ORDER BY chartmaster.accountcode', $db);

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable Intereses Devengados') . ' (' . _('SOFOM') . '):</td><td><select tabindex="22" Name=gllink_intdevengados>';
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_intdevengados'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
echo '</select></td></tr>';

$result = DB_query("SELECT accountcode, accountname 
        FROM chartmaster, accountgroups
        WHERE chartmaster.group_=accountgroups.groupname AND accountgroups.pandl=0 AND 1=0
        ORDER BY chartmaster.accountcode", $db);

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Cuenta Contable DxC Transferencia Credito') . ' (' . _('SOFOM') . '):</td><td><select tabindex="22" Name=gllink_dxctransferenciacredito>';
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_dxctransferenciacredito'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
echo '</select></td></tr>';

echo "<tr><td colspan='2'><hr></td></tr>";

$result=DB_query("SELECT accountcode, accountname
                    FROM chartmaster
                    WHERE chartmaster.accountcode LIKE '2.1.1.%' AND nu_nivel=9 AND ln_clave='09'
                    ORDER BY chartmaster.accountcode", $db);

//echo "<tr height='20px'><td colspan='2' style='text-align:center; font-weight:bold;'>INTEGRACION CHEQUES PROVEEDORES</td></tr>";

echo '<div class="panel panel-default"><!-- Datos de la Dependencia -->
    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-12 col-xs-12">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelIntegracionCheques" aria-expanded="false" aria-controls="collapse" class="collapsed"><span class="glyphicon glyphicon-chevron-down"></span>
                    INTEGRACIÓN CHEQUES PROVEEDORES
                </a>
            </div>
        </h4>
    </div>
    <div id="PanelIntegracionCheques" name="PanelIntegracionCheques" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>
       <div class="text-left container">';

echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Cuenta Contable de Retención de IVA HONORARIOS: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_retencioniva" tabindex="13" Name=gllink_retencioniva class="ivahonorarios">';

//echo '<tr><td style="text-align: right;">' . _('Cuenta Contable de Retencion de IVA HONORARIOS') . ':</td><td><select tabindex="22" Name=gllink_retencioniva>';

$elementosselect= "";

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_retencioniva'] == $myrow[0]) {
        $elementosselect.= "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        $elementosselect.= "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

echo $elementosselect;
echo '</select></div></div><br>';

DB_data_seek($result, 0);

//echo '<tr><td style="text-align: right;">' . _('Cuenta Contable de Retencion de ISR HONORARIOS') . ':</td><td><select tabindex="22" Name=gllink_retencionhonorarios>';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Cuenta Contable de Retencion de ISR HONORARIOS: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_retencionhonorarios" tabindex="13" Name=gllink_retencionhonorarios class="isrhonorarios">';

$elementosselect= "";
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_retencionhonorarios'] == $myrow[0]) {
        $elementosselect.= "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        $elementosselect.= "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
echo $elementosselect;
echo '</select></div></div> <br>';

echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Cuenta Contable de Retencion IVA Arredamiento: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_retencionIVAarrendamiento" tabindex="13" Name=gllink_retencionIVAarrendamiento class="ivaarrendamiento">';

$elementosselect= "";
DB_data_seek($result, 0);

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_retencionIVAarrendamiento'] == $myrow[0]) {
        $elementosselect.= "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        $elementosselect.= "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

echo $elementosselect;
echo '</select></div></div> <br>';

//echo '<tr><td style="text-align: right;">' . _('Cuenta Contable de Retencion ISR Arredamiento') . ':</td><td><select tabindex="22" Name=gllink_retencionarrendamiento>';

echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Cuenta Contable de Retencion ISR Arredamiento: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_retencionarrendamiento" tabindex="13" Name=gllink_retencionarrendamiento class="israrrendamiento">';

$elementosselect= "";
DB_data_seek($result, 0);

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_retencionarrendamiento'] == $myrow[0]) {
        $elementosselect.= "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        $elementosselect.= "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

echo $elementosselect;
echo '</select></div></div> <br>';

//echo '<tr><td style="text-align: right;">' . _('Cuenta Contable de Retencion x Comisiones') . ':</td><td><select tabindex="22" Name=gllink_retencionComisiones>';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Cuenta Contable de Retencion x Comisiones: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_retencionComisiones" tabindex="13" Name=gllink_retencionComisiones class="retencioncomision">';

$elementosselect= "";
DB_data_seek($result, 0);

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_retencionComisiones'] == $myrow[0]) {
        $elementosselect.= "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        $elementosselect.= "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop

echo $elementosselect;
echo '</select></div></div> <br>';

//echo '<tr><td style="text-align: right;">' . _('Cuenta Contable de Retencion x Fletes') . ':</td><td><select tabindex="22" Name=gllink_retencionFletes>';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Cuenta Contable de Retencion x Fletes: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_retencionFletes" tabindex="13" Name=gllink_retencionFletes class="retencionfletex">';

$elementosselect= "";
DB_data_seek($result, 0);

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_retencionFletes'] == $myrow[0]) {
        $elementosselect.= "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        $elementosselect.= "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
echo $elementosselect;
echo '</select></div></div> <br>';

echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Cuenta Contable de Retencion Cedular: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_retencionCedular" tabindex="13" Name=gllink_retencionCedular class="retencioncedular">';

$elementosselect= "";
DB_data_seek($result, 0);

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_retencionCedular'] == $myrow[0]) {
        $elementosselect.= "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        $elementosselect.= "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
echo $elementosselect;
echo '</select></div></div> <br>';
//echo "<tr><td colspan='2'><hr></td></tr>";

// fin integracion de chueques
echo ' </div><!--container-->
</div><!--fin contenido -->
</div><!-- fin panel datos de la Dependencia -->';


// CUENTAS DE ARMONIZACION CONTABLE PRESUPUESTAL
echo '<div class="panel panel-default"><!-- Datos de la Dependencia -->
    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-12 col-xs-12">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelArmonizacionContable" aria-expanded="false" aria-controls="collapse" class="collapsed"><span class="glyphicon glyphicon-chevron-down"></span>
                    CUENTAS DE ARMONIZACIÓN CONTABLE
                </a>
            </div>
        </h4>
    </div>
    <div id="PanelArmonizacionContable" name="PanelArmonizacionContable" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>

        <div class="text-left container">';

echo '<div class="text-center"><h4>CUENTAS INGRESO</h4></div><br>';
//echo "<tr height='20px'><td colspan='2' style='text-align:center; font-weight:bold;'>CUENTAS INGRESO</td></tr>";

//echo '<tr><td style="text-align: right;">' . _('ESTIMADO') . ':</td><td><select tabindex="22" Name="gllink_presupuestalingreso">';
$consulta= "SELECT accountcode, accountname
            FROM chartmaster
            WHERE chartmaster.accountcode LIKE '8.1.%' AND nu_nivel=9 AND ln_clave='09'
            ORDER BY chartmaster.accountcode";

$result= DB_query($consulta, $db);

echo '<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>ESTIMADO: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalingreso" tabindex="13" Name="gllink_presupuestalingreso" class="presupuestaingreso">';

$elementosselect= "";
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalingreso'] == $myrow[0]) {
        $elementosselect.= "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        $elementosselect.= "<option VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
echo $elementosselect;
echo '</select></div></div><br>';

//echo '<tr><td style="text-align: right;">' . _('POR EJECUTAR') . ':</td><td><select tabindex="22" Name="gllink_presupuestalingresoEjecutar">';
echo '<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>POR EJECUTAR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalingresoEjecutar" tabindex="13" Name=gllink_presupuestalingresoEjecutar class="porejecutar">';

$elementosselect= "";
DB_data_seek($result, 0);

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalingresoEjecutar'] == $myrow[0]) {
        $elementosselect.= "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        $elementosselect.= "<option VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
echo $elementosselect;
echo '</select></div></div><br>';

//echo '<tr><td style="text-align: right;">' . _('MODIFICADO') . ':</td><td><select tabindex="22" Name="gllink_presupuestalingresoModificado">';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>MODIFICADO: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalingresoModificado" tabindex="13" Name=gllink_presupuestalingresoModificado class="modificado">';

DB_data_seek($result, 0);

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalingresoModificado'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
//echo '</select></td></tr>';
echo '</select></div></div> <br>';

//echo '<tr><td style="text-align: right;">' . _('DEVENGADO') . ':</td><td><select tabindex="22" Name="gllink_presupuestalingresoDevengado">';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>DEVENGADO: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalingresoDevengado" tabindex="13" Name=gllink_presupuestalingresoDevengado class="devengado">';
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalingresoDevengado'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
echo '</select></div></div> <br>';

//echo '<tr><td style="text-align: right;">' . _('RECAUDADO') . ':</td><td><select tabindex="22" Name="gllink_presupuestalingresoRecaudado">';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>RECAUDADO: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalingresoRecaudado" tabindex="13" Name=gllink_presupuestalingresoRecaudado class="devengado">';
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalingresoRecaudado'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
//echo '</select></td></tr>';
echo '</select></div></div> <br>';


// CUENTAS DE ARMONIZACION CONTABLE PRESUPUESTAL
/*$result=DB_query("SELECT accountcode, accountname
                    FROM chartmaster
                    WHERE chartmaster.accountcode LIKE '8.2.%'
                    ORDER BY chartmaster.accountcode", $db);
*/
$result= DB_query("SELECT accountcode, accountname
                    FROM chartmaster
                    WHERE chartmaster.accountcode LIKE '8.2.%' AND nu_nivel=9 AND ln_clave='09'
                    ORDER BY chartmaster.accountcode", $db);


echo '<div class="text-center"><h4>CUENTAS EGRESO</h4> </div>';
//echo "<tr><td colspan='2'><hr></td></tr>";

//echo "<tr height='20px'><td colspan='2' style='text-align:center; font-weight:bold;'>CUENTAS EGRESO</td></tr>";

//echo '<tr><td style="text-align: right;">' . _('APROBADO') . ':</td><td><select tabindex="22" Name="gllink_presupuestalegreso">';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>APROBADO: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalegreso" tabindex="13" Name=gllink_presupuestalegreso class="aprobado">';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalegreso'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . utf8_encode($myrow[1]) . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
echo '</select></div></div> <br>';
//echo '</select></td></tr>';

//echo '<tr><td style="text-align: right;">' . _('POR EJERCER') . ':</td><td><select tabindex="22" Name="gllink_presupuestalegresoEjercer">';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>POR EJERCER: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalegresoEjercer" tabindex="13" Name=gllink_presupuestalegresoEjercer class="porejercer">';
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalegresoEjercer'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
//echo '</select></td></tr>';
echo '</select></div></div> <br>';

//echo '<tr><td style="text-align: right;">' . _('MODIFICADO') . ':</td><td><select tabindex="22" Name="gllink_presupuestalegresoModificado">';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>MODIFICADO: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalegresoModificado" tabindex="13" Name=gllink_presupuestalegresoModificado class="modificadoegreso">';
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalegresoModificado'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
//echo '</select></td></tr>';
echo '</select></div></div> <br>';

//echo '<tr><td style="text-align: right;">' . _('COMPROMETIDO') . ':</td><td><select tabindex="22" Name="gllink_presupuestalegresocomprometido">';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>COMPROMETIDO: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalegresocomprometido" tabindex="13" Name=gllink_presupuestalegresocomprometido class="comprometidoegreso">';
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalegresocomprometido'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
//echo '</select></td></tr>';
echo '</select></div></div> <br>';

//echo '<tr><td style="text-align: right;">' . _('DEVENGADO') . ':</td><td><select tabindex="22" Name="gllink_presupuestalegresodevengado">';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>DEVENGADO: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalegresodevengado" tabindex="13" Name=gllink_presupuestalegresodevengado class="devengadoegreso">';
while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalegresodevengado'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
//echo '</select></td></tr>';
echo '</select></div></div> <br>';

//echo '<tr><td style="text-align: right;">' . _('EJERCIDO') . ':</td><td><select tabindex="22" Name="gllink_presupuestalegresoejercido">';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>EJERCIDO: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalegresoejercido" tabindex="13" Name=gllink_presupuestalegresoejercido class="ejercidoegreso">';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalegresoejercido'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
//echo '</select></td></tr>';
echo '</select></div></div> <br>';

//echo '<tr><td style="text-align: right;">' . _('PAGADO') . ':</td><td><select tabindex="22" Name="gllink_presupuestalegresopagado">';
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>PAGADO: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_presupuestalegresopagado" tabindex="13" Name=gllink_presupuestalegresopagado class="pagadoegreso">';

while ($myrow = DB_fetch_row($result)) {
    if ($_POST['gllink_presupuestalegresopagado'] == $myrow[0]) {
        echo "<option selected VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    } else {
        echo "<option  VALUE='" . $myrow[0] . "'>" . $myrow[1] . ' (' . $myrow[0] . ')';
    }
} //end while loop
DB_data_seek($result, 0);
//echo '</select></td></tr>';
echo '</select></div></div> <br>';

echo '</div><!--container--> 
      </div><!--fin contenido -->
      </div><!-- fin panel datos de la Dependencia -->';

echo '<div class="panel panel-default"><!-- Datos del Panel -->
    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-12 col-xs-12">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelInventarios" aria-expanded="false" aria-controls="collapse" class="collapsed"><span class="glyphicon glyphicon-chevron-down"></span>
                    CUENTAS DE INVENTARIOS
                </a>
            </div>
        </h4>
    </div>
    <div id="PanelInventarios" name="PanelInventarios" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>
        <div class="text-left container">';

echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Habilita interfase contable Inventarios: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="GLLink_Stock" tabindex="25" name=GLLink_Stock class="GLLink_Stock">';
if ($_POST['GLLink_Stock'] == 0) {
    echo '<option selected VALUE=0>' . _('No') . '</option>';
    echo '<option VALUE=1>' . _('Si') . '</option>';
} else {
    echo '<option selected VALUE=1>' . _('Si') . '</option>';
    echo '<option VALUE=0>' . _('No') . '</option>';
}

echo '</select></div></div><br>';
        
echo '</div><!--container--> 
      </div><!--fin contenido -->
      </div><!-- fin panel datos del Panel -->';

echo "<tr><td colspan='2'><hr></td></tr>";

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Habilita interfase contable Cuentas X Cobrar') . ':</td><td><select tabindex="23" Name=GLLink_Debtors>';

if ($_POST['GLLink_Debtors'] == 0) {
    echo '<option selected VALUE=0>' . _('No');
    echo '<option VALUE=1>' . _('Yes');
} else {
    echo '<option selected VALUE=1>' . _('Yes');
    echo '<option VALUE=0>' . _('No');
}

echo '</select></td></tr>';

echo '<tr style="display: none;"><td style="text-align: right;">' . _('Habilita interfase contable Cuentas X Pagar') . ':</td><td><select tabindex="24" Name=GLLink_Creditors>';

if ($_POST['GLLink_Creditors'] == 0) {
    echo '<option selected VALUE=0>' . _('No');
    echo '<option VALUE=1>' . _('Yes');
} else {
    echo '<option selected VALUE=1>' . _('Yes');
    echo '<option VALUE=0>' . _('No');
}

echo '</select></td></tr>';

echo '</table>';

echo '<div class="centre" style="color:#fff">
        <input class="btn bgc8" tabindex="26" type="Submit" Name="submit" value="' . _('Actualizar Datos') . '">
    </div>';

 //echo '</form>';

include 'includes/footer_Index.inc';
?>
<script type="text/javascript">
    fnFormatoSelectGeneral(".monedaLocal");
    fnFormatoSelectGeneral(".ivahonorarios");
    fnFormatoSelectGeneral(".isrhonorarios");
    fnFormatoSelectGeneral(".ivaarrendamiento");
    fnFormatoSelectGeneral(".israrrendamiento");
    fnFormatoSelectGeneral(".retencioncomision");
    fnFormatoSelectGeneral(".retencioncedular");
    fnFormatoSelectGeneral(".retencionfletex");
    fnFormatoSelectGeneral(".presupuestaingreso");
    fnFormatoSelectGeneral(".porejecutar");
    fnFormatoSelectGeneral(".modificado");
    fnFormatoSelectGeneral(".devengado");
    fnFormatoSelectGeneral(".aprobado");
    fnFormatoSelectGeneral(".porejercer");
    fnFormatoSelectGeneral(".modificadoegreso");
    fnFormatoSelectGeneral(".comprometidoegreso");
    fnFormatoSelectGeneral(".devengadoegreso");
    fnFormatoSelectGeneral(".ejercidoegreso");
    fnFormatoSelectGeneral(".pagadoegreso");
    fnFormatoSelectGeneral(".GLLink_Stock");
</script>