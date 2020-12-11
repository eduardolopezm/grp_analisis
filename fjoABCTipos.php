<?php
	include ('includes/session.inc');
	
	$title = _ ( 'ABC TIPOS ' );

	include ('includes/header.inc');
	include ('includes/SQL_CommonFunctions.inc');
	
	$funcion=1597;
	//include('includes/SecurityFunctions.inc');
	
	if (isset ( $_POST ['UserTask'] )) {
		$UserTask = $_POST ['UserTask'];
	} else {
		$UserTask = "";
	}
	
	
	$num_reg = 25;
	
	if (isset ( $_POST ['num_reg'] )) {
		$num_reg = $_POST ['num_reg'];
	}
	
	
	if (isset ( $_GET ['kindcategoryid'])) {
		$kindcategoryid = $_GET ['kindcategoryid'];
	} elseif (isset ( $_POST ['kindcategoryid'])){
		$kindcategoryid = $_POST['kindcategoryid'];
	}
	
	$InputError=0;
	if(isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar'])){
		if (isset ( $_POST ['funcion'] ) && strlen ( $_POST ['funcion'] ) < 3) {
			$InputError = 1;
			prnMsg ( _ ( 'El nombre de la incidencia debe ser de al menos 3 caracteres de longitud' ), 'error' );
		}
		//aqui va llamado a funciones
		unset($sql);
		//MODIFICAR NUEVO REGISTRO
		if (isset($_POST['modificar']) and ($InputError!=1) and ($InputError!=2)){
			
			$sql = "UPDATE fjokindcategory SET kindcategory='".strtoupper($_POST['kindcategory'])."'
					WHERE kindcategoryid='".$_POST['kindcategoryid']."'";
			
			
			$result=DB_query($sql, $db);
			
			//aqui romper variables
			unset ($_POST['kindcategoryid']);
			unset ( $insertar );
			unset ( $sql );
			unset ( $consulta );
			
		//BORRAR REGISTRO
		}elseif (isset ($_GET['borrar']) and ($InputError!=1) and ($InputError!=2)){
			$sql="DELETE FROM fjokindcategory WHERE kindcategoryid=$kindcategoryid";
			$result=DB_query($sql, $db);
			
			
		//AGREGAR NUEVO REGISTRO
		}elseif (isset ($_POST['enviar']) and ($InputError!=1) and ($InputError!=2)){
			$myrow=DB_fetch_row($result);
			if($myrow[0] > 0){
				prnMsg( _ ('Ya existe registro guardado con misma clave'), 'error');
			}else{
				$sql = "INSERT INTO fjokindcategory (kindcategoryid,kindcategory)
						VALUES(null, '". strtoupper($_POST['kindcategory'])."')";
				$msg=_ ('Registro Insertado Satisfactoriamente');
				$result=DB_query($sql, $db);
				//$SupportToolsID = DB_Last_Insert_ID($db,'tasks_supporttools','kindcategoryid');
			}	
		}
		//aqui rompe variables
		unset ( $kindcategoryid );
		
	}
	
	if (isset ( $sql ) && $InputError != 1 && ($InputError != 2)) {
		//$result = DB_query ( $sql, $db, $ErrMsg );
	}
	
	if (isset ( $_POST ['Go1'] )) {
		$Offset = $_POST ['Offset1'];
		$_POST ['Go1'] = '';
	}
	
	if (! isset ( $_POST ['Offset'] )) {
		$_POST ['Offset'] = 0;
	} else {
		if ($_POST ['Offset'] == 0) {
			$_POST ['Offset'] = 0;
		}
	}
	
	if (isset ( $_POST ['Next'] )) {
		$Offset = $_POST ['nextlist'];
	}
	
	if (isset ( $_POST ['Prev'] )) {
		$Offset = $_POST ['previous'];
	}
	
	
	
	echo "<tr>";
	echo "<td style='text-align:center;'>";
	echo "<font class='texto_lista'>
	            		<img src='images/facturacion.png' title='Reporte Facturacion' width=25 height=25 hspace=10 align='top'><b>" . $title . "</b>
	            	  </font>
	            	</td>";
	echo "</tr>
	        		<tr>
	        		  <td>
	        		    &nbsp;
	        		  </td>
	        	    </tr>";
	echo "</table>";
	//FORMULARIO MODIFICAR
	echo "<form method='post' action=".$_SERVER['PHP_SELF']. "?".SID.">";
	if(!isset ($kindcategoryid)){
		echo "<div class='centre'><hr width=50%></div><br>";
		if ($Offset == 0) {
			$numfuncion = 1;
		} else {
			$numfuncion = $num_reg * $Offset + 1;
		}
		$Offsetpagina = 1;
		
		
		$sql="SELECT * FROM fjokindcategory";
		$result=DB_query($sql, $db);
		
		if(DB_num_rows($result)==0){
			prnMsg(_('No hay registros'),'Error');
		}
		$result = DB_query ( $sql, $db );
		
		$ListCount=DB_num_rows($result);
		$ListPageMax = ceil ( $ListCount / $num_reg );
		
		if(!isset($kindcategoryid)){
			echo "<div class='left' align='center'>" . _ ( 'LISTADO DE TIPOS' ) . "</div>";
			echo '</tr>
			</table>';
		}
		echo "<br>
				<table width='80%' border=0>
		 <tr bgcolor='#192d3c'>
		                  <td>
							&nbsp;
		                  </td>
		          		</tr>";
				
				
				
				
				echo"<table cellspacing=0 border=1 align='center' width='80%' bordercolor=#aeaeae cellpadding=3 colspan=0 style='margin:auto;'>
				$pdflink
						<tr>
				<td class='titulos_principales'>ID</td>
				<td class='titulos_principales'>TIPO</td>
				<td class='titulos_principales' colspan=2>ACCIONES</td>
				</tr>";
		$con = 0; // row colour counter
		while ( $myrow = DB_fetch_array ( $result ) ) {
			if ($con == 1) {
				echo '<tr class="EvenTableRows">';
				$con = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$con = 1;
			}
			if ($myrow ['activo'] == 1) {
				$myrow ['activo'] = 'Si';
			} else {
				$myrow ['activo'] = 'No';
			}
			printf ( "<td>%s</td>
				  <td>%s</td>
				  <td style='text-align:center;'><a href=\"%s&kindcategoryid=%s\">Modificar</a></td>
				  <td style='text-align:center;'><a href=\"%s&kindcategoryid=%s&borrar=1\">Borrar</a></td>
				  </tr>", $myrow ['kindcategoryid'], $myrow ['kindcategory'], $_SERVER ['PHP_SELF'] . '?' . SID, $myrow ['kindcategoryid'], $_SERVER ['PHP_SELF'] . '?' . SID, $myrow ['kindcategoryid'] );
			$numfuncion = $numfuncion + 1;
			
			
			
			
			/*echo "<td>".$myrow['kindcategoryid']."</td>";
			echo "<td>".$myrow['kindcategory']."</td>";
			echo "<td style='text-align:center;'><a href='".$_SERVER['PHP_SELF'] . "?" . SID."&kindcategoryid=".$myrow['kindcategoryid']."'>Modificar</a></td>";
			echo "<td style='text-align:center;'><a href='".$_SERVER['PHP_SELF'] . "?" . SID."&kindcategoryid=".$myrow['kindcategoryid']."&borrar=1'>Borrar</a></td>";
			echo "</tr>";
			$numfuncion = $numfuncion + 1;*/
		}
		echo "</table>";
		echo "</form>";
	}
	
	//LINK MODIFICAR
	if (isset ( $kindcategoryid )) {
		$sql = "SELECT * FROM fjokindcategory WHERE kindcategoryid='" . $kindcategoryid . "'";
		$result = DB_query ( $sql, $db );
		$myrow = DB_fetch_array ( $result );
		
		$_POST ['kindcategoryid'] = $myrow ['kindcategoryid'];
		$_POST ['kindcategory'] = $myrow ['kindcategory'];
	}
	if (isset ( $kindcategoryid )) {
		echo "<div class='centre'><a href='" . $_SERVER ['PHP_SELF'] . "?" . SID . "'>" . _ ( 'REGRESAR <---' ) . '</a></div>';
	}
	//FORMULARIO INSERTA
	
	echo "<br><form  method='post' action=" . $_SERVER ["PHP_SELF"] . "?" . SID . ">";
	echo '<table border=0 width=80%>';
	echo '<tr><td>' . _ ( '' ) . "</td><td><input type='hidden' name='kindcategoryid' size=30 maxlength=100 VALUE='" . $_POST ['kindcategoryid'] . "'></td></tr>";
	echo '<tr><td>' . _ ( 'TIPO' ) . ":</td><td><input name='kindcategory' type='text' value='" . $_POST ['kindcategory'] . "'></td></tr>";
	echo '</table>';
	
	
	/*echo "<table border=0 width=80%>";
	echo "<tr><td>' . _ ( '' ) . ':</td><td><input type='hidden' name='kindcategoryid' size=30 maxlength=100 VALUE='" . $_POST ['kindcategoryid'] . "'></td></tr>";
	echo "<tr><td>' . _ ( 'TIPO' ) . ':</td><td><input name='kindcategory' type='text' value='" . $_POST ['kindcategory'] . "'></td></tr>";
	echo "</table>";*/
	if (! isset ( $kindcategoryid )) {
		echo "<br><div class='centre'><input type='Submit' name='enviar' value='" . _ ( 'Agregar' ) . "'></div>";
	}
	elseif (isset ( $kindcategoryid )) {
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _ ( 'Actualizar' ) . "'></div>";
	}
	echo "</form>";
	include ('includes/footer.inc');

?>