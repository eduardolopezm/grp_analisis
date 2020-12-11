<?php

/* $Id: FixedAssetCategories.php 6941 2014-10-26 23:18:08Z daintree $*/
error_reporting(E_ALL ^ E_NOTICE);


include('includes/session.inc');
$debug_sql = true;
$funcion=1846;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
$title = _('Mantenimiento de Categorias Activo FIjo');

$ViewTopic = 'FixedAssets';
$BookMark = 'AssetCategories';

include('includes/header.inc');
include('javascripts/libreriasGrid.inc');

if (isset($_GET['SelectedCategory'])) {
    $SelectedCategory = strtoupper($_GET['SelectedCategory']);
} elseif (isset($_POST['SelectedCategory'])) {
    $SelectedCategory = strtoupper($_POST['SelectedCategory']);
}
if (isset($_POST ['UnidNeg'])) {
    $UnidNeg = $_POST ['UnidNeg'];
} else {
    $UnidNeg = '';
}

if (isset($_POST['submit'])) {
    //initialise no input errors assumed initially before we test
    $InputError = 0;

    /* actions to take once the user has clicked the submit button
    ie the page has called itself with some user input */

    //first off validate inputs sensible

    $_POST['CategoryID'] = strtoupper($_POST['CategoryID']);

    if (strlen($_POST['CategoryID']) > 6 and !isset($SelectedCategory)) {
        $InputError = 1;
        prnMsg(_('The Fixed Asset Category code must be six characters or less long'), 'error');
    } elseif (strlen($_POST['CategoryID'])==0) {
        $InputError = 1;
        prnMsg(_('The Fixed Asset Category code must be at least 1 character but less than six characters long'), 'error');
    } elseif (strlen($_POST['CategoryDescription']) >100) {
        $InputError = 1;
        prnMsg(_('La descricipón de la categoría de Activo Fijo debe ser 100 caracteres o menos'), 'error');
    }

    if ($_POST['CostAct'] == $_SESSION['CompanyRecord']['debtorsact']
            or $_POST['CostAct'] == $_SESSION['CompanyRecord']['creditorsact']
            or $_POST['AccumDepnAct'] == $_SESSION['CompanyRecord']['debtorsact']
            or $_POST['AccumDepnAct'] == $_SESSION['CompanyRecord']['creditorsact']
            or $_POST['CostAct'] == $_SESSION['CompanyRecord']['grnact']
            or $_POST['AccumDepnAct'] == $_SESSION['CompanyRecord']['grnact']) {
        prnMsg(_('Las cuentas seleccionadas para grabar el costo o la depreciación acumulada no pueden ser las mismas que las cuentas de control, cuentas de crédito'), 'error');
    //mensaje original  accounts selected to post cost or accumulated depreciation to cannot be either of the debtors control account, creditors control account or GRN suspense accounts
        $InputError =1;
    }
    /*Make an array of the defined bank accounts */
    $SQL = "SELECT bankaccounts.accountcode
            FROM bankaccounts INNER JOIN chartmaster
            ON bankaccounts.accountcode=chartmaster.accountcode";
    $result = DB_query($sql, $db);
    $BankAccounts = array();
    $i=0;

    while ($Act = DB_fetch_row($result)) {
        $BankAccounts[$i]= $Act[0];
        $i++;
    }
    if (in_array($_POST['CostAct'], $BankAccounts)) {
        prnMsg(_('The asset cost account selected is a bank account - bank accounts are protected from having any other postings made to them. Select another balance sheet account for the asset cost'), 'error');
        $InputError=1;
    }
    if (in_array($_POST['AccumDepnAct'], $BankAccounts)) {
        prnMsg(_('The accumulated depreciation account selected is a bank account - bank accounts are protected from having any other postings made to them. Select another balance sheet account for the asset accumulated depreciation'), 'error');
        $InputError=1;
    }

    if (isset($SelectedCategory) and $InputError !=1) {
        /*SelectedCategory could also exist if submit had not been clicked this code
        would not run in this case cos submit is false of course  see the
        delete code below*/

        $_POST['fueluse']= ($_POST['fueluse']=="")?0:$_POST['fueluse'];
        $_POST['suggestserialnumber']= ($_POST['suggestserialnumber']=="")?0:$_POST['suggestserialnumber'];

        

        $sql = "UPDATE fixedassetcategories
                    SET categorydescription = '" . $_POST['CategoryDescription'] . "',
                        costact = '" . $_POST['CostAct'] . "',
                        depnact = '" . $_POST['DepnAct'] . "',
                        disposalact = '" . $_POST['DisposalAct'] . "',
                        accumdepnact = '" . $_POST['AccumDepnAct'] . "',
                        daysformaintenance = '" . $_POST['daysformaintenance'] . "',
                        depnrate = '" . $_POST['depnrate'] . "',
                        suggestserialnumber = '" . $_POST['suggestserialnumber'] . "',
                        fueluse = '" . $_POST['fueluse'] . "',
                        salesglaccount = '" . $_POST['salesglaccount'] . "',
                        chargeglaccount = '" . $_POST['chargeglaccount'] . "'
                WHERE categoryid = '".$SelectedCategory . "'";

        //echo $sql;

        $ErrMsg = _('No se pudo actualizar la categoría ') . $_POST['CategoryDescription'] . _('because');
        $result = DB_query($sql, $db, $ErrMsg);

        prnMsg(_('Se actualizó la Categoría') . ' ' . $_POST['CategoryDescription'], 'success');

        /*//actualizar en mantenimiento de categorias de inventario
        $sql = "UPDATE stockcategory
                    SET categorydescription = '" . $_POST['CategoryDescription'] . "',
                        costact = '" . $_POST['CostAct'] . "',
                        depnact = '" . $_POST['DepnAct'] . "',
                        disposalact = '" . $_POST['DisposalAct'] . "',
                        accumdepnact = '" . $_POST['AccumDepnAct'] . "'

                WHERE categoryid = '".$SelectedCategory . "'";

        $ErrMsg = _('No se pudo actualizar la categoría ') . $_POST['CategoryDescription'] . _('because');
        //$result = DB_query($sql, $db, $ErrMsg);*/
    } elseif ($InputError !=1) {
        $_POST['fueluse']= ($_POST['fueluse']=="")?0:1;
        $_POST['stockact'] = ($_POST['stockact']=="")?0:1;
        $_POST['suggestserialnumber'] = ($_POST['suggestserialnumber'])?0:1;

        $sql = "INSERT INTO fixedassetcategories (tagref,  categoryid,
                                                    categorydescription,
                                                    costact,
                                                    depnact,
                                                    disposalact,
                                                    accumdepnact,
                                                    daysformaintenance,
                                                    depnrate,
                                                    suggestserialnumber,
                                                    fueluse,
                                                    stockact,
                                                    salesglaccount,
                                                    chargeglaccount)
                                VALUES (100, '" . $_POST['CategoryID'] . "',
                                        '" . $_POST['CategoryDescription'] . "',
                                        '" . $_POST['CostAct'] . "',
                                        '" . $_POST['DepnAct'] . "',
                                        '" . $_POST['DisposalAct'] . "',
                                        '" . $_POST['AccumDepnAct'] . "',
                                        '" . $_POST['daysformaintenance'] . "',
                                        '" . $_POST['depnrate'] . "',
                                        '" . $_POST['suggestserialnumber'] . "',
                                        '" .$_POST['fueluse']  . "',
                                        '" . $_POST['stockact'] . "',
                                        '" . $_POST['salesglaccount'] . "',
                                        '" . $_POST['chargeglaccount'] . "'
                                        )";


                                        //echo $sql;
        $ErrMsg = _('No se pudo insertar la nueva categoría ') . $_POST['CategoryDescription'] . _('because');
        $result = DB_query($sql, $db, $ErrMsg);
        prnMsg(_('Un nuevo registro de categoría ha sido agregado ') . ' ' . $_POST['CategoryDescription'], 'success');

        
        /*//insertar en mantenimiento de categorias de inventario
        $sql = "INSERT INTO stockcategory ( categoryid,
                                            categorydescription,
                                            costact,
                                            depnact,
                                            disposalact,
                                            accumdepnact,
                                            flagactivofijo)
                                VALUES ('AF" . $_POST['CategoryID'] . "',
                                        '" . $_POST['CategoryDescription'] . "',
                                        '" . $_POST['CostAct'] . "',
                                        '" . $_POST['DepnAct'] . "',
                                        '" . $_POST['DisposalAct'] . "',
                                        '" . $_POST['AccumDepnAct'] . "',
                                        1)";
        $ErrMsg = _('No se pudo insertar la nueva categoría ') . $_POST['CategoryDescription'] . _('because');
        //$result = DB_query($sql, $db, $ErrMsg);*/
    }
    //run the SQL from either of the above possibilites

    unset($_POST['CategoryID']);
    unset($_POST['CategoryDescription']);
    unset($_POST['CostAct']);
    unset($_POST['DepnAct']);
    unset($_POST['DisposalAct']);
    unset($_POST['AccumDepnAct']);
    unset($_POST['daysformaintenance']);
    unset($_POST['depnrate']);
    unset($_POST['suggestserialnumber']);
    unset($_POST['fueluse']);
    unset($_POST['salesglaccount']);
    unset($_POST['chargeglaccount']);
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'fixedassets'

    $sql= "SELECT COUNT(*) FROM fixedassets WHERE fixedassets.assetcategoryid='" . $SelectedCategory . "'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        prnMsg(_('No se puede borrar esta categoria de activo fijo porque activos fijos han sido creados usando esta categoría') .
            '<br /> ' . _('Hay ') . ' ' . $myrow[0] . ' ' . _('activos fijos referidos por esta código de categoría'), 'warn');
    } else {
        $sql="UPDATE fixedassetcategories SET active = 0 WHERE categoryid='" . $SelectedCategory . "'";
        $result = DB_query($sql, $db);
        prnMsg(_('La categoría de activo fijo') . ' ' . $SelectedCategory . ' ' . _('ha sido eliminada '), 'success');
        

        /*//eliminar de tabla mantenimiento de categorias de inventario
        $sql="UPDATE stockcategory SET stkcactive = 0 WHERE categoryid='" . $SelectedCategory . "'";
        $result = DB_query($sql,$db);*/

        unset($SelectedCategory);
    } //end if stock category used in debtor transactions
}

if (!isset($SelectedCategory) or isset($_POST['submit'])) {
/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of stock categorys will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

    $sql = "SELECT categoryid,
                categorydescription,
                costact,
                depnact,
                disposalact,
                accumdepnact,
                daysformaintenance,
                depnrate,
                suggestserialnumber,
                fueluse,
                salesglaccount,
                chargeglaccount
            FROM fixedassetcategories 
            WHERE active = 1";
    $result = DB_query($sql, $db);


     $k=0; //row colour counter

     $info = array();

    while ($myrow = DB_fetch_array($result)) {
                    /*<td><a href="%sSelectedCategory=%s">' . _('Editar') . '</a></td>
                    <td><a href="%sSelectedCategory=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this fixed asset category? Additional checks will be performed before actual deletion to ensure data integrity is not compromised.') . '\');">' . _('Eliminar') . '</a></td>
                    </tr>*/

                    $info[] = array( 'categoryid' => $myrow ['categoryid'], 'categoryid2' => $myrow ['categoryid'], 'categoryid3' => $myrow ['categoryid'],
                        'Descripcion'=>$myrow['categorydescription'], 'CostoGL'  => $myrow['costact'], 'DepnGL' => $myrow['depnact'],
                        'DisposicionGL'=>$myrow['disposalact'], 'DisposicionAcumGL'  => $myrow['accumdepnact'], 'CuentaVentas' => $myrow['salesglaccount'],
                        'CuentaGastos'=>$myrow['chargeglaccount'], 'DiasParaMantenimiento'  => $myrow['daysformaintenance'], 'TasaDepreciacion' => $myrow['depnrate'], 'fueluse' => $myrow['fueluse']
                    );
                    /*,
                    $myrow['categoryid'],
                    $myrow['categorydescription'],
                    $myrow['costact'],
                    $myrow['depnact'],
                    $myrow['disposalact'],
                    $myrow['accumdepnact'],
                    $myrow['salesglaccount'],
                    $myrow['chargeglaccount'],
                    $myrow['daysformaintenance'],
                    $myrow['depnrate'],
                    ( $myrow['suggestserialnumber'] == 1 ? 'Si' : 'No' ),
                    ( $myrow['fueluse'] == 1 ? 'Si' : 'No' ),
                    htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
                    $myrow['categoryid'],
                    htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
                    $myrow['categoryid']);*/
    }
    //END WHILE LIST LOOP
    
    $nombreExcel = utf8_decode( str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY') );
    ?>

    <div align="center">
  <component-button type="button" id="btnAgregar" name="btnAgregar" onclick="fnAgregarCatalogoModal()" value="Nuevo" class="glyphicon glyphicon-plus"></component-button>
  
  <br>
  <br>
</div>


    <div id="grid"></div>

    <script type="text/javascript">

        function fnAgregarCatalogoModal(){
    

    var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Categoría de Activo Fijo</h3>';
    $('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
    $('#ModalUR').modal('show');
    
}

        function xxx() {
            
            return confirm ("¿Estás seguro que deseas borrar la categoría de activo fijo? Se ejecutarán verificaciones adicionales para asegurar que la integridad de datos no esté compromentida");
        }

        data = <?php echo json_encode($info);  ?>

         paginaactual = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>";

         source =
            {
                datatype: "json",
                datafields: [
                { name: 'categoryid', type: 'string' },
                { name: 'categoryid2', type: 'string' },
                { name: 'categoryid3', type: 'string' },
                    { name: 'Descripcion', type: 'string' },
                    { name: 'CostoGL', type: 'string' },
                    { name: 'DepnGL', type: 'string' },
                    { name: 'DisposicionGL', type: 'string' },
                    { name: 'DisposicionAcumGL', type: 'string' },
                    { name: 'CuentaVentas', type: 'string' },
                    { name: 'CuentaGastos', type: 'string' },
                    { name: 'DiasParaMantenimiento', type: 'string' },
                    { name: 'TasaDepreciacion', type: 'string' },
                    { name: 'SugerirSerie', type: 'bool' },
                    { name: 'UsaCombustible', type: 'bool' },
                    { name: 'Modificar', type: 'string' },
                    { name: 'Eliminar', type: 'string' }
                ],
                localdata: data
            };


           

            
        
        columnasExcel= [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        columnasVisuales= [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
$( document ).ready(function() {


    //Mostrar Catalogo
    //
    //
    //
     columnasNombres = [
                { name: 'categoryid', type: 'string' },
                { name: 'categoryid2', type: 'string' },
                { name: 'categoryid3', type: 'string' },
                    { name: 'Descripcion', type: 'string' },
                    { name: 'CostoGL', type: 'string' },
                    { name: 'DepnGL', type: 'string' },
                    { name: 'DisposicionGL', type: 'string' },
                    { name: 'DisposicionAcumGL', type: 'string' },
                    { name: 'CuentaVentas', type: 'string' },
                    { name: 'CuentaGastos', type: 'string' },
                    { name: 'DiasParaMantenimiento', type: 'string' },
                    { name: 'TasaDepreciacion', type: 'string' },
                    { name: 'SugerirSerie', type: 'bool' },
                    { name: 'fueluse', type: 'bool' },
                    { name: 'Modificar', type: 'string' },
                    { name: 'Eliminar', type: 'string' }
                ];

               
    var linkrenderer = function (row, column, value) {
                value = paginaactual+"?SelectedCategory="+value;
                var html = '<a target="_self" href="'+value+'">Modificar</a';
                return html;
            }

            var linkdeleterenderer = function (row, column, value) {23
            //<a href="%sSelectedCategory=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this fixed asset category? Additional checks will be performed before actual deletion to ensure data integrity is not compromised.') . '\');">' . _('Eliminar') . '</a>
            //
            value = paginaactual+"?SelectedCategory="+value+"&delete=yes";
        
                var html = '<a target="_self" href="'+value+'" onclick= "return xxx()">Eliminar</a';

                
                return html;
        }


         columnasNombresGrid = [
                { text: 'Id Categoría', datafield: 'categoryid', width: "7%"},
                
                    { text: 'Descripción', datafield: 'Descripcion', width: "7%" },
                    { text: 'Costo', datafield: 'CostoGL', width: "7%" },
                    { text: 'Depreciación', datafield: 'DepnGL', width: "8%" },
                    { text: 'Disposición', datafield: 'DisposicionGL', width: "7%" },
                    { text: 'Disposición Acum', datafield: 'DisposicionAcumGL', width: "10%" },
                    { text: 'Cuenta Ventas', datafield: 'CuentaVentas', width: "9%" },
                    { text: 'Cuenta Gastos', datafield: 'CuentaGastos', width: "9%" },
                    { text: 'Dias p/Manto', datafield: 'DiasParaMantenimiento', width: "7%" },
                    { text: 'Tasa Depreciación', datafield: 'TasaDepreciacion', width: "9%"},
                    /*{ text: 'Sugerir Serie al recibir OC', datafield: 'SugerirSerie', columntype: 'checkbox', width: "7%" },*/
                    { text: 'Usa Combustible', datafield: 'fueluse', columntype: 'checkbox', width: "10%" },
                 { text: 'Modificar', datafield: 'categoryid2', width: "5%", cellsrenderer: linkrenderer },   
                    { text: 'Eliminar', datafield: 'categoryid3', width: "5%", cellsrenderer: linkdeleterenderer}
                ];
    fnAgregarGrid_Detalle(data, columnasNombres, columnasNombresGrid, 'grid', ' ', 1, columnasExcel, false, false, "", columnasVisuales, "<?php echo $nombreExcel; ?>");

    $('form').appendTo($('#ModalUR_Mensaje'))

    
});



        
        
    </script>
<?php
}

//end of ifs and buts!

if (isset($SelectedCategory)) {
    echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' ._('Mostrar todas las categorias') . '</a></div>';
}
echo '<div id="forma">';
echo '<form id="CategoryForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedCategory) and !isset($_POST['submit'])) {
    //editing an existing fixed asset category
        $sql = "SELECT categoryid,
                    categorydescription,
                    costact,
                    depnact,
                    disposalact,
                    accumdepnact,
                    daysformaintenance,
                    depnrate,
                    suggestserialnumber,
                    fueluse,
                    salesglaccount,
                    chargeglaccount
                FROM fixedassetcategories
                WHERE categoryid='" . $SelectedCategory . "'";

        $result = DB_query($sql, $db);
        $myrow = DB_fetch_array($result);

    $_POST['CategoryID'] = $myrow['categoryid'];
    $_POST['CategoryDescription']  = $myrow['categorydescription'];
    $_POST['CostAct']  = $myrow['costact'];
    $_POST['DepnAct']  = $myrow['depnact'];
    $_POST['DisposalAct']  = $myrow['disposalact'];
    $_POST['AccumDepnAct']  = $myrow['accumdepnact'];
    $_POST['daysformaintenance']  = $myrow['daysformaintenance'];
    $_POST['depnrate']  = $myrow['depnrate'];
    $_POST['suggestserialnumber']  = $myrow['suggestserialnumber'];
    $_POST['fueluse']  = $myrow['fueluse'];
    $_POST['salesglaccount']  = $myrow['salesglaccount'];
    $_POST['chargeglaccount']  = $myrow['chargeglaccount'];
    
    echo '<input type="hidden" name="SelectedCategory" value="' . $SelectedCategory . '" />';
    echo '<input type="hidden" name="CategoryID" value="' . $_POST['CategoryID'] . '" />';
    echo '<table class="selection" style="margin:auto;">
        <tr>
            <td>' . _('Id Categoría') . ':</td>
            <td>' . $_POST['CategoryID'] . '</td>
        </tr>';
} else { //end of if $SelectedCategory only do the else when a new record is being entered
    if (!isset($_POST['CategoryID'])) {
        $_POST['CategoryID'] = '';
    }
    echo '<table class="selection" style="margin:auto;">';
        echo '<tr>';
            echo '<td>' . _('Id Categoría') . ':</td>';
    if ($_POST['CategoryID'] == '') {
        $etiqueta = '';
    }
            echo '<td>' . $etiqueta . '<input type="text" name="CategoryID" required="required" title="' . _('Enter the asset category code. Up to 6 alpha-numeric characters are allowed') . '" data-type="no-illegal-chars" size="7" maxlength="6" value="' . $_POST['CategoryID'] . '" /></td>
            </tr>';
}

//SQL to poulate account selection boxes
$sql = "SELECT accountcode,
                 accountname
        FROM chartmaster INNER JOIN accountgroups
        ON chartmaster.group_=accountgroups.groupname
        ORDER BY accountcode";

$BSAccountsResult = DB_query($sql, $db);

$sql = "SELECT accountcode,
                 accountname
        FROM chartmaster INNER JOIN accountgroups
        ON chartmaster.group_=accountgroups.groupname
        ORDER BY accountcode";

$PnLAccountsResult = DB_query($sql, $db);

$sql = "SELECT accountcode,
                 accountname
        FROM chartmaster INNER JOIN accountgroups
        ON chartmaster.group_=accountgroups.groupname
        ORDER BY accountcode";

$AccountsResult = DB_query($sql, $db);


if (!isset($_POST['CategoryDescription'])) {
    $_POST['CategoryDescription'] = '';
}


$check_suggestserialnumber = $_POST['suggestserialnumber'] == 1 ? 'checked' : '';
$check_fueluse = $_POST['fueluse'] == 1 ? 'checked' : '';

echo '<tr>
        <td>' . _('Descripción de Categoría') . ':</td>
        <td><input type="text" name="CategoryDescription" required="required" title="' . _('Enter the asset category description up to 20 characters') . '" style="width: 280px;" maxlength="20" value="' . $_POST['CategoryDescription'] . '" /></td>
    </tr>
    <tr>
        <td>' . _('Dias Para Mantenimiento') . ':</td>
        <td><input type="number" name="daysformaintenance"  title="' . _('Dias Maximo de Mantenimiento') . '" style="width: 280px;" maxlength="3" value="' . $_POST['daysformaintenance'] . '" /></td>
    </tr>

    <tr>
        <td>' . _('Tasa Depreciación (%)') . ':</td>
        <td><input type="number" name="depnrate" required="required" title="' . _('Enter the asset category description up to 20 characters') . '" style="width: 280px;" maxlength="20" value="' . $_POST['depnrate'] . '" /></td>
    </tr>

    <tr>
        <td>' . _('Usa Combustible') . ':</td>
        <td><input type="checkbox" name="fueluse" value="1"  ' . $check_fueluse . '/></td>
    </tr>

    <tr>
        <td>' . _('El costo de activos fijos') . ':</td>
        <td><select name="CostAct" required="required" style="width: 280px;" title="' . _('Select the general ledger account where the cost of assets of this category should be posted to. Only balance sheet accounts can be selected') . '" >';

while ($myrow = DB_fetch_array($BSAccountsResult)) {
    if (isset($_POST['CostAct']) and $myrow['accountcode']==$_POST['CostAct']) {
        echo '<option selected="selected" value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')</option>';
    } else {
        echo '<option value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')</option>';
    }
} //end while loop
echo '</select></td>
    </tr>
    <tr>
        <td>' . _('Ganancia y pérdida Depreciación') . ':</td>
        <td><select name="DepnAct" required="required" style="width: 280px;" title="' . _('Select the general ledger account where the depreciation of assets of this category should be posted to. Only profit and loss accounts can be selected') . '" >';

while ($myrow = DB_fetch_array($PnLAccountsResult)) {
    if (isset($_POST['DepnAct']) and $myrow['accountcode']==$_POST['DepnAct']) {
        echo '<option selected="selected" value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')</option>';
    } else {
        echo '<option value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')</option>';
    }
} //end while loop
echo '</select></td>
    </tr>';

DB_data_seek($PnLAccountsResult, 0);
echo '<tr>
        <td>' .  _('Ganancia o pérdida en la disposición') . ':</td>
        <td><select name="DisposalAct" required="required" style="width: 280px;" title="' . _('Select the general ledger account where the profit or loss on disposal on assets of this category should be posted to. Only profit and loss accounts can be selected') . '" >';
while ($myrow = DB_fetch_array($PnLAccountsResult)) {
    if (isset($_POST['DisposalAct']) and $myrow['accountcode']==$_POST['DisposalAct']) {
        echo '<option selected="selected" value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')' . '</option>';
    } else {
        echo '<option value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')' . '</option>';
    }
} //end while loop
echo '</select></td>
    </tr>';

DB_data_seek($BSAccountsResult, 0);
echo '<tr>
        <td>' . _('Balance de Depreciación acumulada') . ':</td>
        <td><select name="AccumDepnAct" required="required" style="width: 280px;" title="' . _('Select the general ledger account where the accumulated depreciation on assets of this category should be posted to. Only balance sheet accounts can be selected') . '" >';

while ($myrow = DB_fetch_array($BSAccountsResult)) {
    if (isset($_POST['AccumDepnAct']) and $myrow['accountcode']==$_POST['AccumDepnAct']) {
        echo '<option selected="selected" value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')' . '</option>';
    } else {
        echo '<option value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')' . '</option>';
    }
} //end while loop


echo '</select></td>
    </tr>';

echo '<tr>';
    echo '<td>' . _('Cuenta de Ventas') . ':</td>
        <td><select name="salesglaccount" required="required" style="width: 280px;" title="' . _('Seleccion la cuenta de ventas que se afectara a momento de rentar el activo fijo') . '" >';

while ($myrow = DB_fetch_array($AccountsResult)) {
    if (isset($_POST['salesglaccount']) and $myrow['accountcode']==$_POST['salesglaccount']) {
        echo '<option selected="selected" value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')' . '</option>';
    } else {
        echo '<option value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')' . '</option>';
    }
} //end while loop


echo '</select></td>
    </tr>';

DB_data_seek($AccountsResult, 0);

echo '<tr>';
    echo '<td>' . _('Cuenta de Gastos') . ':</td>
        <td><select name="chargeglaccount" required="required" style="width: 280px;" title="' . _('Seleccion la cuenta de gastos que se afectara a momento de hacer una orden de compra de un activo rentado') . '" >';

while ($myrow = DB_fetch_array($AccountsResult)) {
    if (isset($_POST['chargeglaccount']) and $myrow['accountcode']==$_POST['chargeglaccount']) {
        echo '<option selected="selected" value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')' . '</option>';
    } else {
        echo '<option value="'.$myrow['accountcode'] . '">' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')' . '</option>';
    }
} //end while loop


echo '</select></td>
    </tr>';



echo '</table>
    <br />';

echo '<div class="centre">
        <input type="submit" name="submit" value="' . _('Enviar Informacion') . '" />
    </div>
    </div>
    </form></div>';
?>
<!--Modal/Modificar-->
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
      <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">
        
        <div id="mensajesValidaciones" name="mensajesValidaciones"></div>
        <!--Mensaje o contenido-->
        
        
      
  

</div>
<div class="modal-footer">
  <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
</div>
</div>
</div>
</div>
<?php

include('includes/footer_Index.inc');
?>