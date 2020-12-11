<?php
/**
 * ABC de Almacén
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity = 8;
include('includes/session.inc');
$funcion=138;
$title = traeNombreFuncion($funcion, $db);
include "includes/SecurityUrl.php";
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$mensaje_emergente= "";
$procesoterminado= 0;

if (isset($_GET['SelectedLocation'])) {
    $SelectedLocation = $_GET['SelectedLocation'];
} elseif (isset($_POST['SelectedLocation'])) {
    $SelectedLocation = $_POST['SelectedLocation'];
}

if (isset($_POST['submit'])) {
    if (empty($_POST['flaglocationto']) == true) {
        $_POST['flaglocationto'] = $_POST['LocCode'];
    }

    $InputError = 0;

    $_POST['LocCode']=strtoupper($_POST['LocCode']);
    if (trim($_POST['LocCode']) == '') {
        $InputError = 1;
        $mensaje_emergente= "<p>El Código de Almacén no puede estar vacío</p>";
        $procesoterminado= 3;
    }

    if (isset($SelectedLocation) and $InputError !=1) {
        if (isset($_POST['Managed']) and $_POST['Managed'] == 'on') {
            $_POST['Managed'] = 1;
        } else {
            $_POST['Managed'] = 0;
        }
        
        /* AGREGUE ESTE CAMPO PARA INDICAR QUE ES UN ALMACEN TEMPORAL (P/SERVICIOS)*/
        if (isset($_POST['shownote']) and $_POST['shownote'] == 'on') {
            $_POST['shownote'] = 1;
        } else {
            $_POST['shownote'] = 0;
        }
        
        $sql = "UPDATE locations SET
                loccode='" . $_POST['LocCode'] . "',
                locationname='" . $_POST['LocationName'] . "',
                deladd1='" . $_POST['DelAdd1'] . "',
                deladd2='" . $_POST['DelAdd2'] . "',
                deladd3='" . $_POST['DelAdd3'] . "',
                deladd4='" . $_POST['DelAdd4'] . "',
                deladd5='" . $_POST['DelAdd5'] . "',
                deladd6='" . $_POST['DelAdd6'] . "',
                areacod='" . $_POST['areacod'] . "',
                tel='" . $_POST['Tel'] . "',
                fax='" . $_POST['Fax'] . "',
                email='" . $_POST['Email'] . "',
                contact='" . $_POST['Contact'] . "',
                taxprovinceid ='" . $_POST['TaxProvince'] . "',
                tagref = '" . $_POST['tag'] . "',
                temploc = '" . $_POST['TempLoc'] . "',
                shownote = '" . $_POST['shownote'] . "',
                flaglocationto = '".$_POST['flaglocationto']."',
                managed = '" . $_POST['Managed'] . "',
                ln_ue = '" . $_POST['selectUnidadEjecutora'] . "' 
            WHERE loccode = '$SelectedLocation'";

        $ErrMsg = _('Ocurrio un error actualiando el') . ' ' . $SelectedLocation . ' ' . _('registro de Almacén porque');
        $DbgMsg = _('El SQL utilizado para actualizar el Almacén fue');

        $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

        $mensaje_emergente= "<p>El Registro de Almacén ha sido actualizado</p>";
        $procesoterminado= 1;

        unset($_POST['LocCode']);
        unset($_POST['LocationName']);
        unset($_POST['DelAdd1']);
        unset($_POST['DelAdd2']);
        unset($_POST['DelAdd3']);
        unset($_POST['DelAdd4']);
        unset($_POST['DelAdd5']);
        unset($_POST['DelAdd6']);
        unset($_POST['Tel']);
        unset($_POST['Fax']);
        unset($_POST['Email']);
        unset($_POST['TaxProvince']);
        unset($_POST['Managed']);
        unset($_POST['TempLoc']);
        unset($_POST['tag']);
        unset($SelectedLocation);
        unset($_POST['Contact']);
        unset($_POST['flaglocationto']);
        unset($_POST['selectUnidadEjecutora']);
    } elseif ($InputError !=1) {
        if ($_POST['Managed'] == 'on') {
            $_POST['Managed'] = 1;
        } else {
            $_POST['Managed'] = 0;
        }

        if (isset($_POST['shownote']) and $_POST['shownote'] == 'on') {
            $_POST['shownote'] = 1;
        } else {
            $_POST['shownote'] = 0;
        }
        
        $consulta= "SELECT loccode FROM locations WHERE loccode='".$_POST['LocCode']."'";
        $resultado = DB_query($consulta, $db);

        if (!DB_fetch_array($resultado)) {
            $sql = "INSERT INTO locations (
                        loccode,
                        locationname,
                        deladd1,
                        deladd2,
                        deladd3,
                        deladd4,
                        deladd5,
                        deladd6,
                        tel,
                        fax,
                        email,
                        contact,
                        taxprovinceid,
                        managed,
                        temploc,
                        shownote,
                        areacod,
                        tagref,
                        flaglocationto,
                        ln_ue
                        )
                VALUES (
                    '" . $_POST['LocCode'] . "',
                    '" . $_POST['LocationName'] . "',
                    '" . $_POST['DelAdd1'] ."',
                    '" . $_POST['DelAdd2'] ."',
                    '" . $_POST['DelAdd3'] . "',
                    '" . $_POST['DelAdd4'] . "',
                    '" . $_POST['DelAdd5'] . "',
                    '" . $_POST['DelAdd6'] . "',
                    '" . $_POST['Tel'] . "',
                    '" . $_POST['Fax'] . "',
                    '" . $_POST['Email'] . "',
                    '" . $_POST['Contact'] . "',
                    " . $_POST['TaxProvince'] . ",
                    " . $_POST['Managed'] . ",
                    " . $_POST['TempLoc'] . ",
                    " . $_POST['shownote'] . ",
                    '" . $_POST['areacod'] . "',
                    '" . $_POST['tag'] . "',
                    '".$_POST['flaglocationto']."',
                    '".$_POST['selectUnidadEjecutora']."'
                )";

            $ErrMsg =  _('Ocurrio un error insertando el registro de Almacen porque');
            $Dbgmsg =  _('El SQL utilizado para insertar el registro de Almacen fue');
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

            // Agregar permiso para accesar al almacen que creo ese usuario
            $sql = "INSERT INTO sec_loccxusser (userid, loccode)
                    VALUES ('".$_SESSION['UserID']."', '".$_POST['LocCode']."')";
            $ErrMsg =  _('Ocurrio un error insertando el registro de Almacen porque');
            $Dbgmsg =  _('El SQL utilizado para insertar el registro de Almacen fue');
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

            $sql = "INSERT INTO locstock (loccode, stockid, quantity, reorderlevel)
                SELECT '" . $_POST['LocCode'] . "', stockmaster.stockid, 0, 0 FROM stockmaster";

            $ErrMsg =  _('Ocurrio un error insertando los registros the existencias para todos los productos pre-existentes porque');
            $DbgMsg =  _('El SQL utilizado para insertar los registros de existencias fue');
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

            $mensaje_emergente= '<p>El nuevo registro de Almacén ha sido insertado </p>';
            $mensaje_emergente.= "<p>Registros de existencias por cada producto fueron agregados para el nuevo Almacén</p>";
            $procesoterminado= 1;

            unset($_POST['LocCode']);
            unset($_POST['LocationName']);
            unset($_POST['DelAdd1']);
            unset($_POST['DelAdd2']);
            unset($_POST['DelAdd3']);
            unset($_POST['DelAdd4']);
            unset($_POST['DelAdd5']);
            unset($_POST['DelAdd6']);
            unset($_POST['Tel']);
            unset($_POST['Fax']);
            unset($_POST['Email']);
            unset($_POST['TaxProvince']);
            unset($_POST['Managed']);
            unset($_POST['TempLoc']);
            unset($SelectedLocation);
            unset($_POST['Contact']);
            unset($_POST['tag']);
            unset($_POST['flaglocationto']);
            unset($_POST['selectUnidadEjecutora']);
        } else {
            $mensaje_emergente= '<p>El código que desea guardar ya existe en el catalogo.</p>';
            $procesoterminado= 2;
        }
    }

    $result = DB_query('SELECT COUNT(taxid) FROM taxauthorities', $db);
    $NoTaxAuths =DB_fetch_row($result);

    $DispTaxProvincesResult = DB_query('SELECT taxprovinceid FROM locations', $db);
    $TaxCatsResult = DB_query('SELECT taxcatid FROM taxcategories', $db);
    if (DB_num_rows($TaxCatsResult) > 0) {
        while ($myrow=DB_fetch_row($DispTaxProvincesResult)) {
            $NoTaxRates = DB_query('SELECT taxauthority FROM taxauthrates WHERE dispatchtaxprovince=' . $myrow[0], $db);
            if (DB_num_rows($NoTaxRates) < $NoTaxAuths[0]) {
                $DelTaxAuths = DB_query('DELETE FROM taxauthrates WHERE dispatchtaxprovince=' . $myrow[0], $db);
                while ($CatRow = DB_fetch_row($TaxCatsResult)) {
                    $sql = 'INSERT INTO taxauthrates (taxauthority,
                                        dispatchtaxprovince,
                                        taxcatid)
                            SELECT taxid,
                                ' . $myrow[0] . ',
                                ' . $CatRow[0] . '
                            FROM taxauthorities';

                    $InsTaxAuthRates = DB_query($sql, $db);
                }
                DB_data_seek($TaxCatsResult, 0);
            }
        }
    }
} elseif (isset($_GET['delete'])) {
    $CancelDelete = 0;
    $sql= "SELECT COUNT(*) FROM salesorders WHERE fromstkloc='$SelectedLocation'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);

    if ($myrow[0]>0) {
        $CancelDelete = 1;
        //prnMsg(_('No puedo eliminar este Almacen porque registros de ventas han sido creados entregando productos de este almacen'), 'advertencia');
        //echo  _('Existen') . ' ' . $myrow[0] . ' ' . _('pedidos de venta con este código de Almacén');
        $mensaje_emergente= '<p>Existen '.$myrow[0].' Pedidos de Venta con este código de Almacén</p>';
        $procesoterminado= 3;
    } else {
        $sql= "SELECT COUNT(*) FROM stockmoves WHERE stockmoves.loccode='$SelectedLocation'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0]>0) {
            $CancelDelete = 1;
            //prnMsg(_('No puedo eliminar este Almacen porque registros de movimientos de existencias han sido creados con este Almacen'), 'advertencia');
            //echo '<br>' . _('Existen') . ' ' . $myrow[0] . ' ' . _('Movimientos de existencias con este código de Almacen');
            $mensaje_emergente= '<p>Existen '.$myrow[0].' Movimientos de existencias con este código de Almacén</p>';
            $procesoterminado= 3;
        } else {
            $sql= "SELECT COUNT(*) FROM locstock WHERE locstock.loccode='$SelectedLocation' AND locstock.quantity !=0";
            $result = DB_query($sql, $db);
            $myrow = DB_fetch_row($result);
            if ($myrow[0]>0) {
                $CancelDelete = 1;
                // prnMsg(_('No se puede eliminar esta ubicacion porque existen registros de ubicaci—n de valores y tienen una cantidad disponible que no es igual a 0'), 'warn');
                // echo '<br> ' . _('Hay') . ' ' . $myrow[0] . ' ' . _(' Artículos disponible con existencias en este código de ubicaci—n');
                $mensaje_emergente= '<p>Existen '.$myrow[0].' Artículos disponible con existencias en este código de ubicación</p>';
                $procesoterminado= 3;
            } else {
                $sql= "SELECT COUNT(*) FROM www_users WHERE www_users.defaultarea='$SelectedLocation'";
                $result = DB_query($sql, $db);
                $myrow = DB_fetch_row($result);
                if ($myrow[0]>0) {
                    $CancelDelete = 1;
                    // prnMsg(_('No se puede eliminar esta ubicacion porque es la ubicaci—n predeterminada para un usuario') . '. ' . _('El registro de usuario se debe modificar primero'), 'warn');
                    // echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('users using this location as their default location');
                    $mensaje_emergente= '<p>Existen '.$myrow[0].' Usuarios configurados con el Almacén</p>';
                    $procesoterminado= 3;
                } else {
                    $sql= "SELECT COUNT(*) FROM bom WHERE bom.loccode='$SelectedLocation'";
                    $result = DB_query($sql, $db);
                    $myrow = DB_fetch_row($result);
                    if ($myrow[0]>0) {
                        $CancelDelete = 1;
                        //prnMsg(_('No se puede eliminar esta ubicacion porque es la ubicacion predeterminada para una lista de materiales') . '. ' . _('La lista de materiales se debe modificar primero'), 'warn');
                        //echo '<br> ' . _('Hay') . ' ' . $myrow[0] . ' ' . _('componentes de lista de materiales utilizando esta ubicacion');
                        $mensaje_emergente= '<p>Existen '.$myrow[0].' Componentes de lista de materiales utilizando esta ubicación</p>';
                        $procesoterminado= 3;
                    } else {
                        $sql= "SELECT COUNT(*) FROM workcentres WHERE workcentres.location='$SelectedLocation'";
                        $result = DB_query($sql, $db);
                        $myrow = DB_fetch_row($result);
                        if ($myrow[0]>0) {
                            $CancelDelete = 1;
                            //prnMsg(_('No se puede eliminar esta ubicacion porque es utilizada por algunos registros de los centros de trabajo'), 'warn');
                            //echo '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('centros de trabaja que utilizan esta ubicacion');
                            $mensaje_emergente= '<p>Existen '.$myrow[0].' Centros de trabaja que utilizan esta ubicación</p>';
                            $procesoterminado= 3;
                        } else {
                            $sql= "SELECT COUNT(*) FROM workorders WHERE workorders.loccode='$SelectedLocation'";
                            $result = DB_query($sql, $db);
                            $myrow = DB_fetch_row($result);
                            if ($myrow[0]>0) {
                                $CancelDelete = 1;
                                //prnMsg(_('No se puede eliminar esta ubicacion porque es utilizado por algunos registros de ordenes de trabajo'), 'warn');
                                //echo '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('ordenes de trabajo utilizanda en esta ubicaci—n');
                                $mensaje_emergente= '<p>Existen '.$myrow[0].' Ordenes de Trabajo utilizando en esta ubicación</p>';
                                $procesoterminado= 3;
                            } else {
                                $sql= "SELECT COUNT(*) FROM custbranch WHERE custbranch.defaultlocation='$SelectedLocation'";
                                $result = DB_query($sql, $db);
                                $myrow = DB_fetch_row($result);
                                if ($myrow[0]>0) {
                                    $CancelDelete = 1;
                                    //prnMsg(_('No se puede eliminar esta ubicacion porque es utilizada por algunos registros de sucursales como la ubicacion predeterminada para entregar desde'), 'warn');
                                    //echo '<br> ' . _('Hay') . ' ' . $myrow[0] . ' ' . _('ramas configuradas para utilizar esta ubicacion por defecto');
                                    $mensaje_emergente= '<p>Existen '.$myrow[0].' Ramas configuradas para utilizar esta ubicación por defecto</p>';
                                    $procesoterminado= 3;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if (! $CancelDelete) {
        $result = DB_query("SELECT taxprovinceid FROM locations WHERE loccode='" . $SelectedLocation . "'", $db);
        $TaxProvinceRow = DB_fetch_row($result);
        $result = DB_query("SELECT COUNT(taxprovinceid) FROM locations WHERE taxprovinceid=" .$TaxProvinceRow[0], $db);
        $TaxProvinceCount = DB_fetch_row($result);
        if ($TaxProvinceCount[0]==1) {
            $result = DB_query('DELETE FROM taxauthrates WHERE dispatchtaxprovince=' . $TaxProvinceRow[0], $db);
        }

        $result= DB_query("DELETE FROM locstock WHERE loccode ='" . $SelectedLocation . "'", $db);
        $result = DB_query("DELETE FROM locations WHERE loccode='" . $SelectedLocation . "'", $db);

        //prnMsg(_('Almacen') . ' ' . $SelectedLocation . ' ' . _('ha sido eliminado') . '!', 'success');
        $mensaje_emergente= '<p>Almacén '.$SelectedLocation.' ha sido eliminado</p>';
        $procesoterminado= 1;
        unset($SelectedLocation);
    }
    unset($SelectedLocation);
    unset($_GET['delete']);
}

if (!isset($SelectedLocation)) {
    $sql = "SELECT locations.loccode,
            locationname,
            taxprovinces.taxprovincename as description,
            managed,
            CONCAT(locations.tagref, ' - ' , tags.tagdescription) as tagdescription,
            temploc,
            CONCAT(tb_cat_unidades_ejecutoras.ue, ' - ' , tb_cat_unidades_ejecutoras.desc_ue) as uedescription
        FROM locations 
        LEFT JOIN taxprovinces ON locations.taxprovinceid=taxprovinces.taxprovinceid
        LEFT JOIN tags ON locations.tagref=tags.tagref
        LEFT JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = locations.tagref AND tb_cat_unidades_ejecutoras.ue = locations.ln_ue 
        ORDER BY tagdescription ASC, uedescription ASC";

    $result = DB_query($sql, $db);

    if (DB_num_rows($result)==0) {
        prnMsg(_('No hay lugares que coincidan con un registro provincia tributaria para mostrar. Compruebe que las provincias tributarias se configuran para todos los lugares de despacho'), 'error');
    }

    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-striped">';
    echo "<thead class='header-verde'>";
    echo '<tr>
            <th style="text-align: center;">' . _('Código') . '</th>
            <th style="text-align: center;">' . _('UR') . '</th>
            <th style="text-align: center;">' . _('UE') . '</th>
            <th style="text-align: center;">' . _('Descripción Almacén') . '</th>
            <th style="text-align: center;">' . _('Inv') . '</th>
            <th style="text-align: center;">' . _('Temp') . '</th>
            <th colspan="2" style="text-align: center;">' . _('Acciones') . '</th>
        </tr>';
    echo "</thead>";

    $ind = 0;

    while ($myrow = DB_fetch_array($result)) {
        $ind = $ind + 1;

        if ($myrow['managed'] == 1) {
            $myrow['managed'] = _('Yes');
        } else {
            $myrow['managed'] = _('No');
        }
        
        if ($myrow['temploc'] == 1) {
            $myrow['temploc'] = _('Yes');
        } else {
            $myrow['temploc'] = _('No');
        }
        
        echo "<tr>";
        echo "<td style='text-align: center;'>".$myrow['loccode']."</td>";
        echo "<td>".$myrow['tagdescription']."</td>";
        echo "<td>".$myrow['uedescription']."</td>";
        echo "<td>".$myrow['locationname']."</td>";
        echo "<td style='text-align: center;'>".$myrow['managed']."</td>";
        echo "<td style='text-align: center;'>".$myrow['temploc']."</td>";
        $enc = new Encryption;
        $url = "&SelectedLocation=>" . $myrow['loccode'];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        echo "<td style='text-align: center;'><a href='".$_SERVER['PHP_SELF']."?".$liga."'>" . _('Modificar')."</a></td>";
        $enc = new Encryption;
        $url = "&SelectedLocation=>" . $myrow['loccode'] . "&delete=>1";
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        echo "<td style='text-align: center;'><a href='".$_SERVER['PHP_SELF']."?".$liga."'>" . _('Eliminar')."</a></td>";

        echo "</tr>";
    }

    echo '</table>';
    echo "</div>";
}

if (isset($SelectedLocation)) {
    echo "<p>";
    echo '<div class="centre"><a href="'.$_SERVER['PHP_SELF'].'">'. _('Revisar Almac&eacute;n').'</a></div>';
    echo "</p>";
}

if (!isset($_GET['delete'])) {
    echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";

    if (isset($SelectedLocation)) {
        $sql = "SELECT loccode,
                locationname,
                deladd1,
                deladd2,
                deladd3,
                deladd4,
                deladd5,
                deladd6,
                contact,
                fax,
                tel,
                email,
                taxprovinceid,
                managed,
                tagref,
                temploc,
                shownote,
                areacod,
                flaglocationto,
                ln_ue
            FROM locations
            WHERE loccode='$SelectedLocation'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_array($result);

        $_POST['LocCode'] = $myrow['loccode'];
        $_POST['LocationName']  = $myrow['locationname'];
        $_POST['DelAdd1'] = $myrow['deladd1'];
        $_POST['DelAdd2'] = $myrow['deladd2'];
        $_POST['DelAdd3'] = $myrow['deladd3'];
        $_POST['DelAdd4'] = $myrow['deladd4'];
        $_POST['DelAdd5'] = $myrow['deladd5'];
        $_POST['DelAdd6'] = $myrow['deladd6'];
        $_POST['Contact'] = $myrow['contact'];
        $_POST['Tel'] = $myrow['tel'];
        $_POST['Fax'] = $myrow['fax'];
        $_POST['Email'] = $myrow['email'];
        $_POST['TaxProvince'] = $myrow['taxprovinceid'];
        $_POST['Managed'] = $myrow['managed'];
        $_POST['TempLoc'] = $myrow['temploc'];
        $_POST['tag'] = $myrow['tagref'];
        $_POST['shownote'] = $myrow['shownote'];
        $_POST['areacod'] = $myrow['areacod'];
        $_POST['flaglocationto'] = $myrow['flaglocationto'];
        $_POST['selectUnidadEjecutora'] = $myrow['ln_ue'];
    } else {
        if (!isset($_POST['LocCode'])) {
            $_POST['LocCode'] = '';
        }
    }
    if (!isset($_POST['LocationName'])) {
        $_POST['LocationName'] = '';
    }
    if (!isset($_POST['Contact'])) {
        $_POST['Contact'] = '';
    }
    if (!isset($_POST['DelAdd1'])) {
        $_POST['DelAdd1'] = '';
    }
    if (!isset($_POST['DelAdd2'])) {
        $_POST['DelAdd2'] = '';
    }
    if (!isset($_POST['DelAdd3'])) {
        $_POST['DelAdd3'] = '';
    }
    if (!isset($_POST['DelAdd4'])) {
        $_POST['DelAdd4'] = '';
    }
    if (!isset($_POST['DelAdd5'])) {
        $_POST['DelAdd5'] = '';
    }
    if (!isset($_POST['DelAdd6'])) {
        $_POST['DelAdd6'] = '';
    }
    if (!isset($_POST['Tel'])) {
        $_POST['Tel'] = '';
    }
    if (!isset($_POST['Fax'])) {
        $_POST['Fax'] = '';
    }
    if (!isset($_POST['Email'])) {
        $_POST['Email'] = '';
    }
    if (!isset($_POST['Managed'])) {
        $_POST['Managed'] = 0;
    }
    if (!isset($_POST['TempLoc'])) {
        $_POST['TempLoc'] = 0;
    }
    if (!isset($_POST['tag'])) {
        $_POST['tag'] = '0';
    }
    if (!isset($_POST['selectUnidadEjecutora'])) {
        $_POST['selectUnidadEjecutora'] = '-1';
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
                <?php
                if (isset($SelectedLocation)) {
                    echo "<input type=hidden name=SelectedLocation VALUE=" . $SelectedLocation . '>';
                    echo "<input type=hidden name=LocCode VALUE=" . $_POST['LocCode'] . '>';
                    echo '<component-label-text label="Código:" id="lblCodigo" name="lblCodigo" value="'.$_POST['LocCode'].'"></component-label-text>';
                } else {
                    echo '<component-text-label label="Código:" id="LocCode" name="LocCode" 
                        placeholder="Código" title="Código" maxlength="3"
                        value="'.$_POST['LocCode'].'"></component-text-label>';
                }
                ?>
                <br>
                <div class="row" style="text-align: left;">
                    <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                        <span><label>UR: </label></span>
                    </div>
                    <div class="col-xs-9 col-md-9">
                        <select id="tag" name="tag" class="form-control tag" onchange="fnCambioUnidadResponsableGeneral('tag','selectUnidadEjecutora')">
                        <option value="0">Seleccionar...</option>
                        <?php
                        $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
                        FROM sec_unegsxuser u, tags t 
                        WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' ".$condicion." 
                        ORDER BY t.tagref ";
                        $result=  DB_query($SQL, $db);
                        while ($myrow=DB_fetch_array($result)) {
                            if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']) {
                                echo '<option selected value=' . $myrow['tagref'] . '>' .$myrow['tagdescription'].'</option>';
                            } else {
                                echo '<option value=' . $myrow['tagref'] . '>' .$myrow['tagdescription'].'</option>';
                            }
                        }
                        ?>
                        </select>
                    </div>
                </div>
                <br>
                <component-text-label label="Ciudad:" id="DelAdd3" name="DelAdd3" 
                        placeholder="Ciudad" title="Ciudad" maxlength="40"
                        value="<?php echo $_POST['DelAdd3']; ?>"></component-text-label>
                <br>
                <component-text-label label="Pais:" id="DelAdd6" name="DelAdd6" 
                        placeholder="Pais" title="Pais" maxlength="15"
                        value="<?php echo $_POST['DelAdd6']; ?>"></component-text-label>
                <br>
                <component-text-label label="Email:" id="Email" name="Email" 
                        placeholder="Email" title="Email" maxlength="55"
                        value="<?php echo $_POST['Email']; ?>"></component-text-label>
            </div>
            <div class="col-md-4 col-xs-12">
                <component-text-label label="Nombre:" id="LocationName" name="LocationName" 
                        placeholder="Nombre" title="Nombre" 
                        value="<?php echo $_POST['LocationName']; ?>"></component-text-label>
                <br>
                <div class="row" style="text-align: left;">
                    <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                        <span><label>UE: </label></span>
                    </div>
                    <div class="col-xs-9 col-md-9">
                        <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control UnidadEjecutora">
                        <option value="-1">Seleccionar...</option>
                        <?php
                        $SQL = "SELECT t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription, tce.ue as ue, CONCAT(tce.ue, ' - ', tce.desc_ue) as uedescription 
                        FROM sec_unegsxuser u
                        INNER JOIN tags t on (u.tagref = t.tagref)
                        INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref)
                        WHERE tce.active = 1 and u.userid = '" . $_SESSION['UserID'] . "' ".$condicion."
                        ORDER BY t.tagref, tce.ue ASC";
                        $result=  DB_query($SQL, $db);
                        while ($myrow=DB_fetch_array($result)) {
                            if (isset($_POST['selectUnidadEjecutora']) and $_POST['selectUnidadEjecutora']==$myrow['ue']) {
                                echo '<option selected value=' . $myrow['ue'] . '>' .$myrow['uedescription'].'</option>';
                            } else {
                                echo '<option value=' . $myrow['ue'] . '>' .$myrow['uedescription'].'</option>';
                            }
                        }
                        ?>
                        </select>
                    </div>
                </div>
                <br>
                <component-text-label label="Calle:" id="DelAdd1" name="DelAdd1" 
                        placeholder="Calle" title="Calle" maxlength="40"
                        value="<?php echo $_POST['DelAdd1']; ?>"></component-text-label>
                <br>
                <component-text-label label="Estado:" id="DelAdd4" name="DelAdd4" 
                        placeholder="Estado" title="Estado" maxlength="40"
                        value="<?php echo $_POST['DelAdd4']; ?>"></component-text-label>
                <br>
                <component-text-label label="Teléfono:" id="Tel" name="Tel" 
                        placeholder="Teléfono" title="Teléfono" maxlength="30"
                        value="<?php echo $_POST['Tel']; ?>"></component-text-label>
                <!-- <br> -->
                <div style="display: none;">
                    <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                        <span><label>Área: </label></span>
                    </div>
                    <div class="col-xs-9 col-md-9">
                        <select id="areacod" name="areacod" class="form-control "><!-- clase formato areacod -->
                        <option value="0">Seleccionar...</option>
                        <?php
                        $sql = "SELECT areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
                        FROM areas 
                        JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
                        WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
                        GROUP BY areas.areacode, areas.areadescription";
                        $result=  DB_query($SQL, $db);
                        while ($myrow=DB_fetch_array($result)) {
                            if ($myrow['areacode'] == $_POST['areacod']) {
                                echo "<option selected value='" . $myrow["areacode"] . "'>" . $myrow['name'] . '</option>';
                            } else {
                                echo "<option value='" . $myrow['areacode'] . "'>" . $myrow['name'] . '</option>';
                            }
                        }
                        ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xs-12">
                <component-text-label label="Contacto:" id="Contact" name="Contact" 
                        placeholder="Contacto" title="Contacto" maxlength="30"
                        value="<?php echo $_POST['Contact']; ?>"></component-text-label>
                <br>
                <component-text-label label="Colonia:" id="DelAdd2" name="DelAdd2" 
                        placeholder="Colonia" title="Colonia" maxlength="40"
                        value="<?php echo $_POST['DelAdd2']; ?>"></component-text-label>
                <br>
                <component-text-label label="Código Postal:" id="DelAdd5" name="DelAdd5" 
                        placeholder="Código Postal" title="Código Postal" maxlength="20"
                        value="<?php echo $_POST['DelAdd5']; ?>"></component-text-label>
                <br>
                <component-text-label label="Fax:" id="Fax" name="Fax" 
                        placeholder="Fax" title="Fax" maxlength="30"
                        value="<?php echo $_POST['Fax']; ?>"></component-text-label>
                <br>
                <div class="row" style="text-align: left;">
                    <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                        <span><label>Impuestos: </label></span>
                    </div>
                    <div class="col-xs-9 col-md-9">
                        <select id="TaxProvince" name="TaxProvince" class="form-control TaxProvince">
                        <!-- <option value="0">Seleccionar...</option> -->
                        <?php
                        $TaxProvinceResult = DB_query('SELECT taxprovinceid, taxprovincename FROM taxprovinces', $db);
                        while ($myrow=DB_fetch_array($TaxProvinceResult)) {
                            if ($_POST['TaxProvince']==$myrow['taxprovinceid']) {
                                echo '<option selected VALUE=' . $myrow['taxprovinceid'] . '>' . $myrow['taxprovincename'] . '</option>';
                            } else {
                                echo '<option VALUE=' . $myrow['taxprovinceid'] . '>' . $myrow['taxprovincename'] . '</option>';
                            }
                        }
                        ?>
                        </select>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <table>
        <tr style="display: none;"><td><?php echo _('Localizacion Temporal') . ':'; ?></td>
        <td><?php
            echo '<select name="TempLoc">';
        if ($_POST['TempLoc']==0) {
            echo '<option selected VALUE="0">' . _('Ventas');
        } else {
            echo '<option VALUE="0">' . _('Ventas');
        }
        if ($_POST['TempLoc']==1) {
            echo '<option selected VALUE="1">' . _('Transito');
        } else {
            echo '<option VALUE="1">' . _('Transito');
        }
        if ($_POST['TempLoc']==2) {//
            echo '<option selected VALUE="2">' . _('Consignacion');
        } else {
            echo '<option VALUE="2">' . _('Consignacion');
        }
        if ($_POST['TempLoc']==3) {
            echo '<option selected VALUE="3">' . _('Recepcion Directa');
        } else {
            echo '<option VALUE="3">' . _('Recepcion Directa');
        }
        if ($_POST['TempLoc']==3) {
            echo '<option selected VALUE="5">' . _('Alm Proveedor');
        } else {
            echo '<option VALUE="5">' . _('Alm Proveedor');
        }
            echo '</select></td></tr>';
            
            echo "<tr style='display: none;'><td>". _('Almacen Orden Trabajo').":</td>";
            echo "<td><input type='text' name='flaglocationto' value='".$_POST['flaglocationto']."'>";
        ?>      
        </td></tr>
        <tr style="display: none;"><td><?php echo _('Control de Inventarios') . ':'; ?></td>
        <td><input type='checkbox' name='Managed' <?php if ($_POST['Managed'] == 1) { echo ' checked'; }?> /></td></tr>
        <tr style="display: none;"><td><?php echo _('Mostrar En Notas para Maquinaria') . ':'; ?></td>
        <td><input type='checkbox' name='shownote'<?php if ($_POST['shownote'] == 1) { echo ' checked'; }?> /></td></tr>
    </table>
    <div class="center">
        <!-- <input type="Submit" name="submit" value="<?php echo _('Procesa Informacion'); ?>"> -->
        <component-button type="submit" id="submit" name="submit" value="Procesa Información"></component-button>
    </div>
    </form>
<?php }
include('includes/footer_Index.inc');
if ($procesoterminado != 0) {
    fnmuestraModalGeneral($procesoterminado, $mensaje_emergente);
}
?>
<script type="text/javascript">
    // Aplicar formato del SELECT
    fnFormatoSelectGeneral(".areacod");
    fnFormatoSelectGeneral(".tag");
    fnFormatoSelectGeneral(".UnidadEjecutora");
    fnFormatoSelectGeneral(".TaxProvince");
</script>
