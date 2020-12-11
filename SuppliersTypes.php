<?php
/**
 * ABC Tipos de Proveedor
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 15/11/2017
 * Fecha Modificación: 15/11/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity = 15;
include('includes/session.inc');
$funcion=850;
$title = traeNombreFuncion($funcion, $db);
include "includes/SecurityUrl.php";
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$mensaje_emergente= "";
$procesoterminado= 0;

if (isset($_POST['SelectedType'])) {
    $SelectedType = strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
    $SelectedType = strtoupper($_GET['SelectedType']);
}

if (isset($Errors)) {
    unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {
    $flaginsert = 0;
    $InputError = 0;

    $i=1;
    if (strlen($_POST['typename']) >100) {
        $InputError = 1;
        //echo prnMsg(_('El tipo de proveedor debe ser menor de 100 caracteres'),'error');
        $mensaje_emergente= "El tipo de proveedor debe ser menor de 100 caracteres";
        $procesoterminado= 3;
        $Errors[$i] = 'SuppliersType';
        $i++;
    }

    if (strlen($_POST['typename'])==0) {
        $InputError = 1;
        //echo prnMsg(_('El tipo de proveedor debe ser mayor a un caracter'),'error');
        $mensaje_emergente= "El tipo de proveedor debe ser mayor a un carácter";
        $procesoterminado= 3;
        $Errors[$i] = 'SuppliersType';
        $i++;
    }

    $checksql = "SELECT count(*)
		     FROM supplierstype
		     WHERE typename = '" . $_POST['typename'] . "'";
    $checkresult=DB_query($checksql, $db);
    $checkrow=DB_fetch_row($checkresult);
    if ($checkrow[0]>0) {
        $InputError = 1;
        //echo prnMsg(_('Ya existe ese tipo de proveedor').' '.$_POST['typename'],'error');
        $mensaje_emergente= "Ya existe ese tipo de proveedor ".$_POST['typename'];
        $procesoterminado= 3;
        $Errors[$i] = 'SuppliersName';
        $i++;
    }
    
    if (isset($SelectedType) and $InputError !=1) {
        if ($_POST['aplica'] == 1) {
            $sql = "UPDATE supplierstype
			SET typename = '" . $_POST['typename'] . "',
				aplicareembolsocaja = '".$_POST['aplica']."',
				aplicaretencion = '".$_POST['aplicaretencion']."'
						WHERE typeid = '$SelectedType'";
        } else {
            $sql = "UPDATE supplierstype
			SET typename = '" . $_POST['typename'] . "',
				aplicareembolsocaja = 0,
				aplicaretencion = '".$_POST['aplicaretencion']."'
							WHERE typeid = '$SelectedType'";
        }
        
        //$msg = _('El tipo de proveedor') . ' ' . $SelectedType . ' ' .  _('a sido actualizado');
        $mensaje_emergente= "El tipo de proveedor ".$_POST['typename']." ha sido Actualizado";
        $procesoterminado= 1;
    } elseif ($InputError !=1) {
        $checkSql = "SELECT count(*)
			     FROM supplierstype
			     WHERE typeid = '" . $_POST['typeid'] . "'";

        $checkresult = DB_query($checkSql, $db);
        $checkrow = DB_fetch_row($checkresult);

        if ($checkrow[0] > 0) {
            $InputError = 1;
            //prnMsg( _('El tipo de proveedor ') . $_POST['typeid'] . _(' ya existe.'),'error');
            $mensaje_emergente= "El tipo de proveedor ".$_POST['typeid'];
            $procesoterminado= 3;
        } else {
            $flaginsert = 1;
            if ($_POST['aplica'] == 1) {
                $sql = "INSERT INTO supplierstype
						(typename, aplicareembolsocaja,aplicaretencion)
					VALUES ('" . $_POST['typename'] . "','".$_POST['aplica']."','".$_POST['aplicaretencion']."')";
            } else {
                $sql = "INSERT INTO supplierstype
						(typename, aplicareembolsocaja,aplicaretencion)
					VALUES ('" . $_POST['typename'] . "','".$_POST['aplica']."','".$_POST['aplicaretencion']."')";
            }
    
            //$msg = _('El tipo de proveedor') . ' ' . $_POST["typename"] .  ' ' . _('ha sido creado');
            $mensaje_emergente= "El tipo de proveedor ".$_POST['typename']." ha sido Agregado";
            $procesoterminado= 1;
            $checkSql = "SELECT count(typeid)
			     FROM supplierstype";
            $result = DB_query($checkSql, $db);
            $row = DB_fetch_row($result);
        }
    }

    if ($InputError !=1) {
        $result = DB_query($sql, $db);
        if ($flaginsert == 1) {
            $tipoid = DB_Last_Insert_ID($db, 'supplierstype', 'typeid');
            
            $sql = "INSERT INTO sec_supplierxuser (typeid, 
													userid)
					VALUES('".$tipoid."', '".$_SESSION['UserID']."')";
            $result = DB_query($sql, $db);
        }

        $sql = "SELECT confvalue
					FROM config
					WHERE confname='DefaultSuppliersType'";
        $result = DB_query($sql, $db);
        $SuppliersTypeRow = DB_fetch_row($result);
        $DefaultSuppliersType = $SuppliersTypeRow[0];

        $checkSql = "SELECT count(*)
			     FROM supplierstype
			     WHERE typeid = '" . $DefaultSuppliersType . "'";
        $checkresult = DB_query($checkSql, $db);
        $checkrow = DB_fetch_row($checkresult);

        if ($checkrow[0] == 0) {
            $sql = "UPDATE config
					SET confvalue='" . $_POST['typeid'] . "'
					WHERE confname='DefaultSuppliersType'";
            $result = DB_query($sql, $db);
            $_SESSION['DefaultSuppliersType'] = $_POST['typeid'];
        }
        //prnMsg($msg,'success');
        unset($SelectedType);
        unset($_POST['typeid']);
        unset($_POST['typename']);
    }
} elseif (isset($_GET['delete'])) {
    $sql= "SELECT COUNT(*)
	       FROM debtortrans
	       WHERE debtortrans.type='$SelectedType'";
    $ErrMsg = _('El número de transacciones usadas para este tipo de proveedor podrian no ser recuperadas');
    $result = DB_query($sql, $db, $ErrMsg);

    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        //prnMsg(_('No puede elimanar este tipo porque tiene transacciones en uso') . '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('transacciones usadas por este tipo'),'error');
        $mensaje_emergente= "Existen ".$myrow[0]." transacciones para el tipo de Proveedor";
        $procesoterminado= 3;
    } else {
        $sql = "SELECT COUNT(*) FROM suppliers WHERE typeid='$SelectedType'";
        $ErrMsg = _('El número de transacciones usadas por este tipo podrían no ser recuperadas porque');
        $result = DB_query($sql, $db, $ErrMsg);
        $myrow = DB_fetch_row($result);
        if ($myrow[0]>0) {
            //prnMsg (_('No puede eliminar este tipo, porque los proveedors estan configuragos para hacer uso de el') . '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('proveedors con este tipo'));
            $mensaje_emergente= "Existen ".$myrow[0]." Proveedores configurados con el tipo seleccionado";
            $procesoterminado= 3;
        } else {
            $sql="DELETE FROM supplierstype WHERE typeid='$SelectedType'";
            $ErrMsg = _('El tipo de proveedor no puede ser eliminado porque');
            $result = DB_query($sql, $db, $ErrMsg);
            //prnMsg(_('El tipo de proveedor') . $SelectedType  . ' ' . _('a sido eliminado') ,'success');
            $mensaje_emergente= "El tipo de Proveedor ".$SelectedType." ha sido Eliminado";
            $procesoterminado= 1;
            unset($SelectedType);
            unset($_GET['delete']);
        }
    } //end if sales type used in debtor transactions or in customers set up
} elseif (isset($_POST['actualizar'])) {
    if ($_POST['aplica'] == 1) {
        $sql = "UPDATE supplierstype
			SET typename = '" . $_POST['typename'] . "',
				aplicareembolsocaja = '".$_POST['aplica']."',
				aplicaretencion = '".$_POST['aplicaretencion']."'
					WHERE typeid = '$SelectedType'";
        $result = DB_query($sql, $db);
    } else {
        $sql = "UPDATE supplierstype
			SET typename = '" . $_POST['typename'] . "',
				aplicareembolsocaja = 0,
				aplicaretencion = '".$_POST['aplicaretencion']."'
				WHERE typeid = '$SelectedType'";
        $result = DB_query($sql, $db);
    }
    
    unset($SelectedType);
    unset($_POST['typeid']);
    unset($_POST['typename']);
    //$msg = _('El tipo de proveedor') . ' ' . $SelectedType . ' ' .  _('a sido actualizado');
    $mensaje_emergente= "El tipo de proveedor ".$_POST['typename']." ha sido Actualizado";
    $procesoterminado= 1;
    //echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Tipos de Proveedores') . '" alt="">' . _('Configuraciï¿½n de Tipos de Proveedores') . '</p>';
}

if (!isset($SelectedType)) {
    $sql = 'SELECT typeid, typename FROM supplierstype';
    $result = DB_query($sql, $db);
    echo '<table class="table table-bordered" border=1 width=70%>
	<tr class="header-verde">
		<th style="text-align:center;">Id</th>
		<th style="text-align:center;">Tipo</th>
		<th></th>
		<th></th>
	</tr>';
    $k=0;
    while ($myrow = DB_fetch_row($result)) {
        echo '<tr>';
        echo '<td style="text-align:center;">'.$myrow[0].'</td>';
        echo '<td>'.$myrow[1].'</td>';
        $enc = new Encryption;
        $url = "&SelectedType=>" . $myrow[0];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        echo '<td style="text-align:center;"><a href="'.$_SERVER['PHP_SELF'].'?'.$liga.'">Modificar</td>';
        $enc = new Encryption;
        $url = "&SelectedType=>" . $myrow[0] . "&delete=>yes";
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        echo '<td style="text-align:center;"><a href="'.$_SERVER['PHP_SELF'].'?'.$liga.'">Eliminar</td>';
        // onclick="return confirm(\'¿Estas seguro que quieres eliminar este tipo de proveedor?\'); "
        echo '</tr>';
    }
    echo '</table>';
}

if (isset($SelectedType)) {
    echo '<div class="centre"><p><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Tipos de Proveedores') . '</a></div><p>';
}
if (! isset($_GET['delete'])) {
    echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

    if (isset($SelectedType) and $SelectedType!='') {
        $sql = "SELECT typeid,
			       typename,
			       aplicareembolsocaja,
			       aplicaretencion
		        FROM supplierstype
		        WHERE typeid='$SelectedType'";

        $result = DB_query($sql, $db);
        $myrow = DB_fetch_array($result);

        $_POST['typeid'] = $myrow['typeid'];
        $_POST['typename']  = $myrow['typename'];
        $_POST['aplica'] = $myrow['aplicareembolsocaja'];
        $_POST['aplicaretencion'] = $myrow['aplicaretencion'];
        echo "<input type=hidden name='SelectedType' VALUE=" . $SelectedType . ">";
        echo "<input type=hidden name='typeid' VALUE=" . $_POST['typeid'] . ">";
    }

    if (!isset($_POST['typename'])) {
        $_POST['typename']='';
    }

    if (!isset($_POST['aplicaretencion']) or $_POST['aplicaretencion'] == "") {
        $_POST['aplicaretencion'] = 0;
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
                <component-text-label label="Tipo:" id="typename" name="typename" 
                        placeholder="Tipo" title="Tipo" maxlength="99"
                        value="<?php echo $_POST['typename']; ?>"></component-text-label>
            </div>
            <div class="col-md-4 col-xs-12">
                <div class="row" style="text-align: left;">
                    <div class="col-xs-12 col-md-12" >
                        <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                            <span><label>Aplica a Reembolso: </label></span>
                        </div>
                        <div class="col-xs-9 col-md-9">
                            <select id="aplica" name="aplica" class="form-control aplica">
                            <?php
                            if ($_POST['aplica'] == 1) {
                                echo'<option selected value=1>Aplica</option>';
                                echo'<option  value=0>No Aplica</option>';
                            } else {
                                echo'<option value=1>Aplica</option>';
                                echo'<option selected value=0>No Aplica</option>';
                            }
                            ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xs-12">
                <div class="row" style="text-align: left;">
                    <div class="col-xs-12 col-md-12" >
                        <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                            <span><label>Aplica a Retención: </label></span>
                        </div>
                        <div class="col-xs-9 col-md-9">
                            <select id="aplicaretencion" name="aplicaretencion" class="form-control aplicaretencion">
                            <option value="0">Seleccionar...</option>
                            <?php
                            if ($_POST['aplicaretencion'] == 0) {
                                echo "<option selected value='0'>No Aplica Retención</option>";
                                echo "<option value='1'>Aplica Retención</option>";
                            } elseif ($_POST['aplicaretencion'] == 1) {
                                echo "<option  value='0'>No Aplica Retención</option>";
                                echo "<option selected value='1'>Aplica Retención</option>";
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
    </div>
    <div class="center">
        <?php if (isset($_GET['SelectedType'])) { ?>
        <component-button type="submit" id="actualizar" name="actualizar" value="Actualizar" class="glyphicon glyphicon-edit"></component-button>
        <?php } else {  ?>
        <component-button type="submit" id="submit" name="submit" value="Agregar" class="glyphicon glyphicon-plus"></component-button>
        <?php } ?>
    </div>
    <?php
    echo '</form>';
}

include('includes/footer_Index.inc');

if ($procesoterminado != 0) {
    fnmuestraModalGeneral($procesoterminado, $mensaje_emergente);
}
?>
<script type="text/javascript">
    // Aplicar formato del SELECT
    fnFormatoSelectGeneral(".aplica");
    fnFormatoSelectGeneral(".aplicaretencion");
</script>