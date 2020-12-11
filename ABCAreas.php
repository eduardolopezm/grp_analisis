<?php
include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Areas');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/*$funcion=13;
include('includes/SecurityFunctions.inc');
*/

if (isset($_POST['area'])) {
	$area= $_POST['area'];
} else {
	$area="";
}	

if (isset($_POST['UserTask'])) {
	$UserTask= $_POST['UserTask'];
} else {
	$UserTask="";
}

if (isset($_POST['matriz'])) {
	$matriz= $_POST['matriz'];
} else {
	$matriz="";
}


$num_reg=999;

if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}

//esta es la variable con la que nos guiamos para saber ssi es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['areacode'])) {
	$areacode = $_GET['areacode'];
} elseif (isset($_POST['areacode'])) {
	$areacode = $_POST['areacode'];
}

$InputError = 0;
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
		if (isset($_POST['desline']) && strlen($_POST['desline'])<3){
		$InputError = 1;
		prnMsg(_('El nombre del Area debe ser de al menos 3 caracteres de longitud'),'error');
		}
		if (isset($_POST['areacode']) && strlen($_POST['areacode'])<1){
		$InputError = 2;
		prnMsg(_('El valor del Codigo debe ser de al menos 1 caracter de longitud'),'error');
		}
	unset($sql);
	
	if (isset($_POST['modificar'])and ($InputError != 1) and ($InputError != 2)) {
		
		$sql = "UPDATE areas
		SET areadescription='" .$_POST['desline']." ',
		usertask='" .$_POST['UserTask']." ',
		regioncode='" .$_POST['code']."'
		WHERE areacode='$areacode'";
		
		$ErrMsg = _('La actualización del Area fracaso porque');
		prnMsg( _('El Area').' ' .$_POST['desline'] . ' ' . _(' se ha actualizado.'),'info');
		
	} elseif (isset($_GET['borrar'])and ($InputError != 1) and ($InputError != 2)) {
			$sql= "select count(*) from tags where areacode='".$_POST['areacode']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				prnMsg( _('No se da de alta el Area por que ya hay un registro guardado'),'error');
			}else{
				$sql="DELETE FROM areas WHERE areacode='" . $_GET['areacode']."'";
				prnMsg(_('El area a sido eliminada ') . '!','info');
			}
	} elseif (isset($_POST['enviar'])and ($InputError != 1) and ($InputError != 2)) {
			$sql= "select count(*) from areas where areacode='".$_POST['areacode']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta el Area por que ya hay un registro guardado'),'error');
		} else {
			$sql = "INSERT INTO areas (areacode,areadescription,regioncode,usertask)
			VALUES ('".$_POST['areacode']."','".$_POST['desline']."','".$_POST['code']."','".$_POST['UserTask']."')";
			$ErrMsg = _('La inserccion del Area fracaso porque');
			prnMsg( _('El Area').' ' .$_POST['desline'] . ' ' . _('se ha creado.'),'info');
			echo $sql;
		}
	}
	unset($_POST['desline']);
	unset($_POST['code']);
	unset($_POST['areacode']);
	unset($areacode);	
}

if (isset($sql) && $InputError != 1 && ($InputError != 2)) {
	$result = DB_query($sql,$db,$ErrMsg);
	if ($pagina=='Stock' and isset($_POST['enviar'])) {
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/StockCategories.php?" . SID . "'>";	
	}
}

if (isset($_POST['Go1'])) {
	$Offset = $_POST['Offset1'];
	$_POST['Go1'] = '';
}

if (!isset($_POST['Offset'])) {
	$_POST['Offset'] = 0;
} else {
	if ($_POST['Offset']==0) {
		$_POST['Offset'] = 0;
	}
}

if (isset($_POST['Next'])) {
	$Offset = $_POST['nextlist'];
}

if (isset($_POST['Prev'])) {
	$Offset = $_POST['previous'];
}

echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

if (!isset($areacode) ) {
	echo '<table><tr>
	<td>
		' . _('Area') . '<br><input type="text" name="area" value="'.$area.'" size=25 maxlength=55>
	</td>
	<td>
		' . _('Matriz') . '<br><input type="text" name="matriz" value="'.$matriz.'" size=25 maxlength=55>
	</td>
	<td valign=bottom>
		<input type=submit name=buscar value=' . _('Buscar') . '>
	</td></tr></table>';
	echo "<div class='centre'><hr width=50%></div><br>";
	if ($Offset==0) {
		$numfuncion=1;
	} else {
		$numfuncion=$num_reg*$Offset+1;
	}
	$Offsetpagina=1;
	
	
//esta parte sirve para mostrar la primera tabla con todos los registros existentes

	
	$sql = "SELECT a.areacode,a.areadescription,a.regioncode,r.name
		FROM areas as a
		INNER JOIN regions as r ON (a.regioncode=r.regioncode) AND (a.areacode <>'' ) " ;	
	if (strlen($area)>=1) {
	$sql=$sql." AND (a.areadescription like '%".$area."%')";
	}
	
	if (strlen($matriz)>=1) {
	$sql=$sql." AND (r.name like '%".$matriz."%')";
	
	}
	$sql=$sql." ORDER BY  r.name, a.areadescription";
	$result = DB_query($sql,$db);
	
	$sql = "SELECT a.areacode,a.areadescription,a.regioncode,r.name
		FROM areas as a
		INNER JOIN regions as r ON (a.regioncode=r.regioncode) AND (a.areacode <>'' )  " ;	
	if (strlen($area)>=1) {
	$sql=$sql." AND (a.areadescription like '%".$area."%')";
	}
	if (strlen($matriz)>=1) {
	$sql=$sql." AND (r.name like '%".$matriz."%')";
	
	}
	if ( DB_num_rows($result) == 0 ) {
		
		prnMsg( _('No hay registros con esa Busqueda.'),'warn');
	}
		
	$sql=$sql." ORDER BY  r.name, a.areadescription"; 
	$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
	
	/*if($_SESSION['UserID'] == "admin"){
		echo '<pre>'.$sql;
	}*/
	$result = DB_query($sql,$db);
	$ListCount=DB_num_rows($result);
	$ListPageMax=ceil($ListCount/$num_reg);

	if (!isset($areacode)) {
		echo "<div class='centre'>" ._('LISTADO DE AREAS'). "</div>";
		echo '<table width=50%>';
		echo '	<tr>';
		if ($ListPageMax >1) {
			if ($Offset==0) {
				$Offsetpagina=1;	
			} else {
				$Offsetpagina=$Offset+1;
		            }
			echo '<td>'.$Offsetpagina. ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ':';
			echo '<select name="Offset1">';
		        $ListPage=0;
					while($ListPage < $ListPageMax) {
						
						if ($ListPage == $Offset) {
							echo '<option VALUE=' . $ListPage . ' selected>' . ($ListPage+1) . '</option>';
						} else {
							echo '<option VALUE=' . $ListPage . '>' . ($ListPage+1) . '</option>';
						}
						$ListPage++;
						$Offsetpagina=$Offsetpagina+1;
					}
			echo '</select></td>
			<td><input type="text" name="num_reg" size=1 value="' .$num_reg. '"></td>
			<td>
				<input type=submit name="Go1" VALUE="' . _('Ir') . '">
			</td>';
			if ($Offset>0){
			echo '<td align=center cellpadding=3 >
				<input type="hidden" name="previous" value='.number_format($Offset-1).'>
				<input tabindex='.number_format($j+7).' type="submit" name="Prev" value="'._('Anterior').'">
			</td>';
			};
			if ($Offset<>$ListPageMax-1) {
			echo'<td style="text-align:right">
				<input type="hidden" name="nextlist" value='.number_format($Offset+1).'>
				<input tabindex='.number_format($j+9).' type="submit" name="Next" value="'._('Siguiente').'">
			</td>';
			}
		}
		echo'</tr>
		</table>';
		
	}
	
	echo '<table border=1 width=50%>';
	echo "<tr><th>" . _('Codigo') . "</th>
	<th>" . _('Sucursal') . "</th>
	<th>" . _('Matriz') . "</th>
	<th></th>
	<th></th>
        </tr>";
	
	$k=0; //row colour counter
	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		if ($myrow['activo']==1) {
			$myrow['activo']='Si';
		} else {
			$myrow['activo']='No';
		}
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td style='text-align:center;'><a href=\"%s&areacode=%s\">Modificar</a></td>
			<td style='text-align:center;'><a href=\"%s&areacode=%s&borrar=1\">Eliminar</a></td>
			</tr>",
			$myrow['areacode'],
			$myrow['areadescription'],
			$myrow['regioncode'].'-'.$myrow['name'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['areacode'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['areacode']
			);
		$numfuncion=$numfuncion+1;
	} 
	echo '</table>';
	echo '</form>';
}


if (isset($areacode)) {
            $sql = "SELECT *
		FROM areas
		WHERE areacode='". $areacode."'" ;
		$result = DB_query($sql, $db);
                $myrow = DB_fetch_array($result);
		$_POST['areacode']=$myrow['areacode'];
		$_POST['desline'] = $myrow['areadescription'];
		$_POST['code']=$myrow['regioncode'];
		$_POST['UserTask']=$myrow['usertask'];
		
            
            }
if (isset($areacode)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Areas existentes') . '</a></div>';
}
echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

echo '<br>';
/*if(isset($_POST['areacode'])) {
	echo "<input type=hidden name='areacode' VALUE='" . $_POST['areacode'] . "'>";
}*/
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE AREAS'). "</div><br>";
	echo '<table>';
	echo'<tr><td>' . _('Codigo del Area') . ":</td>
	<td><input type='text' name='areacode' size=40 maxlength=100 VALUE='" .$_POST['areacode'] . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Nombre del Area') . ":</td>
	<td><input type='text' name='desline' size=40 maxlength=100 VALUE='" .$_POST['desline'] . "'></td></tr>
	<tr>";
	echo '<td>' . _('Matriz:') . '</td>';
	echo'<td><select Name="code"><br>';
	   $sql = "SELECT regioncode as region, name as name FROM regions order by name";
	    $grupo= DB_query($sql,$db);
	      while ($myrowgrupo=DB_fetch_array($grupo,$db)){
		$grupobase=$myrowgrupo['region'];
		if ($_POST['code']==$grupobase)
		  { 
		    echo '<option  VALUE="' . $myrowgrupo['region'] .  '  " selected>' .$myrowgrupo['name'];
		  }
		else
		  {
		    echo '<option  VALUE="' . $myrowgrupo['region'] .  '" >' .$myrowgrupo['name'];
		  }
	      }
	echo '</td>';
	echo '</tr><tr>';
	echo '<td>' . _('Usuario Default de Produccion: ') . '</td>';
	echo'<td><select Name="UserTask"><br>';
	  $sql = "SELECT usuario.userid, usuario.realname,perfil.active
		FROM www_users as usuario
		INNER JOIN sec_profilexuser as perfilxu ON usuario.userid=perfilxu.userid
		INNER JOIN sec_profiles as perfil ON perfilxu.profileid=perfil.profileid
		WHERE perfil.active='1'
		AND usuario.realname <> ''
		ORDER BY usuario.realname";
	$result = DB_query($sql,$db,$ErrMsg);
	    echo '<option  VALUE="" selected>Ninguno ';
	while($row=DB_fetch_array($result,$db)) {
		//echo '<option '.($_SESSION['UserTask'] == $row['userid']?'selected ':'').'value="'.$row['userid'].'">'.$row['realname'];

	    if ($row['userid'] ==$_POST['UserTask']){ 
                echo '<option  VALUE="' . $row['userid'] .  '  " selected>' .$row['realname'];
            }else{
                echo '<option  VALUE="' . $row['userid'] .  '" >' .$row['realname'];
            }
	}
	echo '</td>';
	echo'</table>';
	if (!isset($areacode)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($areacode)) {
	
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	
	}
include('includes/footer_Index.inc');
?>