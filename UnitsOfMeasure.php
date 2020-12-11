<?php
/**
 * ABC de Unidades de Medida
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

include "includes/SecurityUrl.php";
$PageSecurity = 15;
include('includes/session.inc');
$funcion=141;
$title = traeNombreFuncion($funcion, $db);

include('includes/header.inc');
include('includes/SecurityFunctions.inc');
?>
<link rel="stylesheet" href="css/listabusqueda.css" />

<?php

if ( isset($_GET['SelectedMeasureID']) )
	$SelectedMeasureID = $_GET['SelectedMeasureID'];
elseif (isset($_POST['SelectedMeasureID']))
	$SelectedMeasureID = $_POST['SelectedMeasureID'];

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strpos($_POST['MeasureName'],'&')>0 OR strpos($_POST['MeasureName'],"'")>0) {
		$InputError = 1;
		prnMsg( _('La unidad de medida no debe contener el caracter') . " '&' " . _('o el caracter') ." '",'error');
	}
	if (trim($_POST['MeasureName']) == '') {
		$InputError = 1;
		prnMsg( _('La unidad de media no puede estar vacía'), 'error');
	}
	if (trim($_POST['txtCodigo']) == '') {
		$InputError = 1;
		prnMsg( _('Agregar un nombre a la unidad de medida de la lista del SAT'), 'error');
	}

	if ($_POST['SelectedMeasureID']!='' AND $InputError !=1) {

		/*SelectedMeasureID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM unitsofmeasure
				WHERE unitid <> " . $SelectedMeasureID ."
				AND unitname ".LIKE." '" . $_POST['MeasureName'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('La unidad de medida no puede ser llamada así, ya existe una con el mismo nombre'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very carefull here
			// idealy this is one of those sets that should be in a stored procedure simce even the checks are
			// relavant
			$sql = "SELECT unitname FROM unitsofmeasure
				WHERE unitid = " . $SelectedMeasureID;
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				//
				$myrow = DB_fetch_row($result);
				$OldMeasureName = $myrow[0];
				$sql = array();
				// antes estaba con WHERE unitname ".LIKE." '".$OldMeasureName."'"; 
				//
				$sql[] = "UPDATE unitsofmeasure
					SET unitname='" . $_POST['MeasureName'] . "',
						unitdecimal = '".$_POST['unitdecimal']."',
						mbflag = '".$_POST['MBFlag']."',
						c_ClaveUnidad = '".$_POST['txtCodigo']."'
					WHERE unitid = '".$SelectedMeasureID."'";
				$sql[] = "UPDATE stockmaster
					SET units='" . $_POST['MeasureName'] . "'
					WHERE units = '" . $OldMeasureName . "'";
				$sql[] = "UPDATE contracts
					SET units='" . $_POST['MeasureName'] . "'
					WHERE units = '" . $OldMeasureName . "'";
			} else {
				$InputError = 1;
				prnMsg( _('La unidad de medida no existe.'),'error');
			}
		}
		$msg = _('Unit of measure changed');
	} elseif ($InputError !=1) {
		/*SelectedMeasureID is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM unitsofmeasure
				WHERE unitname " .LIKE. " '".$_POST['MeasureName'] ."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('La unidad de Medida no puede ser creada porque ya existe una con el mismos nombre'),'error');
		} else {
			$sql = "INSERT INTO unitsofmeasure (
						unitname, unitdecimal, mbflag, c_ClaveUnidad)
				VALUES (
					'" . $_POST['MeasureName'] ."', '".$_POST['unitdecimal']."', '".$_POST['MBFlag']."', '".$_POST['txtCodigo']."'
					)";
		}
		$msg = _('Nueva unidad de medida agregada');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$tmpErr = _('No se Pudo actualizar la unidad de medida');
			$tmpDbg = _('El SQL ha fallado') . ':';
			foreach ($sql as $stmt ) {
				$result = DB_query($stmt,$db, $tmpErr,$tmpDbg,true);
				//echo $stmt;
				if(!$result) {
					$InputError = 1;
					break;
				}
			}
			if ($InputError!=1){
				$result = DB_Txn_Commit($db);
			} else {
				$result = DB_Txn_Rollback($db);
			}
		} else {
			$result = DB_query($sql,$db);
		}
		prnMsg($msg,'success');
	}
	unset ($SelectedMeasureID);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureName']);
	unset ($_POST['txtCodigo']);
	unset ($_POST['txtNombre']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the unit of measure the ID is just a secure way to find the unit of measure
	$sql = "SELECT unitname FROM unitsofmeasure
		WHERE unitid = " . $SelectedMeasureID;
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('No se puede eliminar esta unidad de medida porque ya no existe'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldMeasureName = $myrow[0];
		$sql= "SELECT COUNT(*) FROM stockmaster WHERE units ".LIKE." '" . $OldMeasureName . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se puede eliminar esta unidad de medida porque los artículos de inventario se han creado utilizando esta unidad de medida'),'warn');
			// echo '<br>' . _('Lista de') . ' ' . $myrow[0] . ' ' . _('artículos en el inventario que refieren esta unidad de medida') . '</font>';
		} else {
			$sql= "SELECT COUNT(*) FROM contracts WHERE units ".LIKE." '" . $OldMeasureName . "'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				prnMsg( _('No se puede eliminar esta unidad de medida porque los contratos se han creado utilizando esta unidad de medida'),'warn');
				echo '<br>' . _('List de') . ' ' . $myrow[0] . ' ' . _('contratos que refieren a esta unidad de medida') . '</font>';
			} else {
				$sql="DELETE FROM unitsofmeasure WHERE unitname ".LIKE."'" . $OldMeasureName . "'";
				$result = DB_query($sql,$db);
				prnMsg( $OldMeasureName . ' ' . _('La unidad de medida ha sido borrada') . '!','success');
			}
		}

	} //end if account group used in GL accounts
	unset ($SelectedMeasureID);
	unset ($_GET['SelectedMeasureID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureID']);
	unset ($_POST['MeasureName']);
	unset ($_POST['txtCodigo']);
	unset ($_POST['txtNombre']);
}

 if (!isset($SelectedMeasureID)) {
	/* An unit of measure could be posted when one has been edited and is being updated
	or GOT when selected for modification
	SelectedMeasureID will exist because it was sent with the page in a GET .
	If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of account groups will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
   
	$sql = "SELECT unitsofmeasure.unitid,
			unitsofmeasure.unitname,
			unitsofmeasure.unitdecimal,
			unitsofmeasure.mbflag,
			unitsofmeasure.c_ClaveUnidad,
			sat_unitsofmeasure.Nombre,
			sat_unitsofmeasure.Simbolo
			FROM unitsofmeasure
			LEFT JOIN sat_unitsofmeasure ON sat_unitsofmeasure.c_ClaveUnidad = unitsofmeasure.c_ClaveUnidad
			ORDER BY unitid";

	$ErrMsg = _('No se pudo obtener la unidad de medida');
	$result = DB_query($sql,$db,$ErrMsg);
?>


  <table class="table table-bordered">
    
    
        <tr class="header-verde">

        <?php
        echo "
		<th class='text-center'>" . _('Unidades de Medida') . "</th>
		<th class='text-center'>" . _('Decimales') . "</th>
		<th class='text-center'>" . _('Tipo de Producto') . "</th>
        <th class='text-center'>" . _('Facturación') . "</th>

        <th class='text-center'>" . _('Modificar') . "</th>
        <th class='text-center'>" . _('Eliminar') . "</th>
		</tr>";

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		if ($myrow[3]=='B'){
			$tipoproducto = 'Bien';
		} elseif ($myrow[3]=='D'){
			$tipoproducto='Servicio';
		} 

		echo "<td class='text-center'>" . $myrow[1] . "</td>";
		echo "<td class='text-center'>" . $myrow[2] . "</td>";
		echo "<td class='text-center'>" . $tipoproducto . "</td>";

		$Codigo = "";
		$Descripcion = "";
		$Simbolo = "";
		
		if (!empty($myrow[4])) {
			$Codigo = $myrow[4];
		}
		if (!empty($myrow[5])) {
			$Descripcion = " | " . $myrow[5];
		}
		if (!empty($myrow[6])) {
			$Simbolo = " | " . $myrow[6];
		}
		echo '<td>'.$Codigo.$Descripcion.$Simbolo.'</td>';


		echo '<td class="text-center"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedMeasureID=' . $myrow[0] . '">' . _('Modificar') . '</a></td>';
		echo '<td class="text-center"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedMeasureID=' . $myrow[0] . '&delete=1">' . _('Eliminar') .'</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';
} //end of ifs and buts!


if (isset($SelectedMeasureID)) {
	echo '<div class="centre"><a href=' . $_SERVER['PHP_SELF'] . '?' . SID .'>' . _('Revisar unidades de medida') . '</a></div>';
}

echo '<p>';

if (! isset($_GET['delete'])) {
    ?>

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
            <b>Información Agregar/Modificar</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
    	<div class="panel-body">
			<?php
				echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
				if (isset($SelectedMeasureID)) {
					//editing an existing section

					$sql = "SELECT unitsofmeasure.unitid,
					unitsofmeasure.unitname,
					unitsofmeasure.unitdecimal,
					unitsofmeasure.mbflag,
					unitsofmeasure.c_ClaveUnidad,
					sat_unitsofmeasure.Nombre,
					sat_unitsofmeasure.Simbolo
					FROM unitsofmeasure
					LEFT JOIN sat_unitsofmeasure ON sat_unitsofmeasure.c_ClaveUnidad = unitsofmeasure.c_ClaveUnidad
					WHERE unitid=" . $SelectedMeasureID;

					$result = DB_query($sql, $db);
					if ( DB_num_rows($result) == 0 ) {
						prnMsg( _('Could not retrieve the requested unit of measure, please try again.'),'warn');
						unset($SelectedMeasureID);
					} else {
						$myrow = DB_fetch_array($result);

						$_POST['MeasureID'] = $myrow['unitid'];
						$_POST['MeasureName']  = $myrow['unitname'];
						$_POST['unitdecimal'] = $myrow['unitdecimal'];
						$_POST['MBFlag']  = $myrow['mbflag'];
						$_POST['txtCodigo']  = $myrow['c_ClaveUnidad'];
						$_POST['txtNombre']  = $myrow['Nombre'] . ($myrow['Simbolo'] != "" ? " (" . $myrow['Simbolo'] . ")" : "");

						echo "<input type=hidden name='SelectedMeasureID' VALUE='" . $_POST['MeasureID'] . "'>";
						// echo "<table>";
					}

				} else {
					$_POST['MeasureName']='';
					// echo "<table>";
				}
			?>
			<div class="col-md-4">
				<component-text-label label="Unidad de Medida:" name='MeasureName' size=30 maxlength=30  placeholder="Unidad de Medida"  value="<?php echo $_POST['MeasureName'] ;?>"></component-text-label>
			</div>
			<div class="col-md-4">
				<div class="form-inline row">
					<div class="col-md-3">
						<span><label>Tipo de Producto: </label></span>
					</div>
					<div class="col-md-9">
						<select name="MBFlag" class="form-control" style="width: 100%;">
							<?php
								if (!isset($_POST['MBFlag']) or $_POST['MBFlag']=='B' OR !isset($_POST['MBFlag']) OR $_POST['MBFlag']==''){
									echo '<option selected value="B">' . _('Bien') . '</option>';
								} else {
									echo '<option value="B">' . _('Bien') . '</option>';
								}

								if (isset($_POST['MBFlag']) and $_POST['MBFlag']=='D'){
									echo '<option selected value="D">' . _('Servicio') . '</option>';
								} else {
									echo '<option value="D">' . _('Servicio') . '</option>';
								}
							?>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<component-text-label label="Decimales:"  name="unitdecimal" size=30 maxlength=30 placeholder="Decimales" title="Decimales" value="<?php echo $_POST['unitdecimal'] ;?>"></component-text-label>
			</div>

			<div class="row"></div>

			<div class="col-md-4">
				<component-number-label label="Código de Facturación:" id="txtCodigoVer" name="txtCodigoVer" placeholder="Facturación"  value="<?php echo $_POST['txtCodigo'] ;?>" readonly></component-number-label>
			<input type='hidden' name='txtCodigo' id='txtCodigo' value='<?php echo $_POST['txtCodigo'] ;?>'>
			</div>

			<div class="col-md-4">
				<component-text-label label="Nombre de Facturación:"  name='txtNombre' id='txtNombre'  placeholder="Nombre de Facturación" value="<?php echo $_POST['txtNombre'] ;?>"></component-text-label>
			</div>

			<div class="col-md-4">
			</div>

			<div class="row"></div>

			<div align="center">
				<component-button type="Submit" name="submit"  class="glyphicon glyphicon-floppy-disk"  value="Guardar"></component-button>
			</div>
		
			<?php
			// echo "</table>";
			echo '</form>';
			?>
        </div>
    </div>
  </div>
</div>

<?php
/*
/*
-----------------------------------------
Fin Criterios de Busqueda
-----------------------------------------

*/

} //end if record deleted no point displaying form to add record

?>
<script type="text/javascript">

	var NombresCode={};
	
	$(document).ready(function() {        
        getProductos();
    });

    function getProductos()
    {
        $.ajax({
          method: "POST",
          dataType:"json",
          url: "UnitsOfMeasure_Model.php",
          data:'option=allunitsofmeasure'
        })
        .done(function( data ) {
            console.log(data);
            if(data.result)
            {
                infounitsofmeasure = data.contenido.infounitsofmeasure;
				console.log(infounitsofmeasure);
                $( "#txtNombre").autocomplete({
                    source: infounitsofmeasure,
                    select: function( event, ui ) {
                        
                        $( this ).val( ui.item.Nombre + " (" + ui.item.Simbolo + ")");
                        $( "#txtCodigoVer" ).val( ui.item.c_ClaveUnidad );
                        $( "#txtCodigo" ).val( ui.item.c_ClaveUnidad );
                        //console.log(item);
                        NombresCode = { c_ClaveUnidad: ui.item.c_ClaveUnidad, Nombre: ui.item.Nombre};

                        return false;
                    }
                })
                .autocomplete( "instance" )._renderItem = function( ul, item ) {

					return $( "<li>" )
					.append( "<a>" + item.c_ClaveUnidad + " | "+ item.Nombre + " | " + item.Simbolo +"</a>" )
					.appendTo( ul );

                };  
            }
            //console.log(infounitsofmeasure);
        })
        .fail(function(result) {
            console.log( result );
        });
    }
</script>
<!--Lista de la unidad de medida-->
<script type="text/javascript" src="lib/bootstrap/js/3.3.6/bootstrap.min.js"></script>

<?php
include 'includes/footer_Index.inc';
?>

