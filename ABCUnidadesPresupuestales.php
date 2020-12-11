<?php
//error_reporting(E_ALL);
//error_reporting(-1);
//ini_set('display_errors', 1);
/* 04 - NOV -2014 ****************************
 * Craar ABC de Ramos
 * Reberiano Ramirez**************************/

include('includes/session.inc');
$title = _('ABC Ramos');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/MiscFunctions.inc');
// $funcion = ;
// include('includes/SecurityFunctions.inc');

// Datos de tabla a admisitrar
$tableName = 'tags';
$tableIdColumn = 'tagref';
$tableColumns = array('tagref', 'tagdescription','anhofiscal');
$tableTitle = array ('Clave','Descripcion','A&ntildeo');
$showColumns = array(1,1,1);
$fieldsObl= array(1,3,4);
$fieldNum=array(1,0,1);
$permiterepetidos=array(0,0,1);
$titulo_abc=" Unidades Presupuestales";
/*
//campos que contienen el valor a insertar/modificar
$datosDeCampos[];
$i=0;
$arrlength=count($cars);
while ($i<$arraylength) {
    $datosDeCampos[$i]=$_POST['campo'.$i];
    $i++;
}
echo "<br>Datos de los campos: ".explode('|',$datosDeCampos,0)."<br>";
*/

if (isset ( $_POST ['area'] )) {
	$area = $_POST ['area'];
} else {
	$area = "";
}

if (isset ( $_POST ['UserTask'] )) {
	$UserTask = $_POST ['UserTask'];
} else {
	$UserTask = "";
}

if (isset ( $_POST ['matriz'] )) {
	$matriz = $_POST ['matriz'];
} else {
	$matriz = "";
}
$num_reg = 999;

if (isset ( $_POST ['num_reg'] )) {
	$num_reg = $_POST ['num_reg'];
}
if (isset ( $_GET ['areacode'] )) {
	$areacode = $_GET ['areacode'];
} elseif (isset ( $_POST ['areacode'] )) {
	$areacode = $_POST ['areacode'];
}

$InputError = 0;
if (isset($_POST['enviar']) or isset($_GET['borrar']) or isset($_POST['modificar'])) {
    $inputError=0;
    $i=0;
    foreach ($fieldsObl as $fieldsNecesa){
        
        $long = strlen($_POST['campo'.$i]);
        if($long<$fieldsObl[$i]){
            prnMsg ( _ ( 'Campo '.$tableTitle[$i].' debe tener al menos '.$fieldsObl[$i].' caracteres.' ), 'warn' );
            $InputError = 1;
        }
        if($fieldNum[$i]==1){
            if(!is_numeric($_POST['campo'.$i])){
                $InputError=1;
                prnMsg ( _ ( 'Campo '.$tableTitle[$i].' debe  ser numerico.' ), 'warn' );
            }
        }
        if($permiterepetidos[$i]==0){
            $sql="SELECT ".  implode("", $tableColumns)." FROM ".$tableName." WHERE ".$tableColumns[$i]."='".$_POST['campo'.$i].'\' AND anhofiscal='.  date('Y');
            if(isset($_POST['campo'.$i]) and $_POST['campo'.$i]<>''){
            $res=  DB_query($sql, $db);
            $noReg=  DB_num_rows($res);
            }
            if($noReg>0){
                $InputError=1;
                prnMsg ( _ ( 'Campo '.$tableTitle[$i].' ya existe con ese valor: '.$_POST['campo'.$i].'' ), 'error' );
            }
        }
        $i++;
    }
    
    if(isset($_GET['borrar'])){
        $sql = "DELETE FROM $tableName WHERE ".$tableColumns[0]." = '$areacode'";
        DB_query($sql, $db);
        unset($areacode);
    }
    if(isset($_POST['enviar']) and $InputError!=1){
        $i=0;
        $fields=array();
        foreach ($tableColumns as $value) {
           $fields[]=$_POST['campo'.$i];
           $i++;
        }
        $sql = "INSERT INTO $tableName (" . implode(',', $tableColumns) . ") VALUES ('" . implode('\',\'', $fields)."')";
        //echo "<pre>".$sql;
        //exit;
        DB_query($sql, $db);
        $ErrMsg = _ ( 'La inserccion del '.$titulo_abc.' fracaso porque' );
            prnMsg ( _ ( 'El '.$titulo_abc ) . ' ' . $_POST ['campo1'] . ' ' . _ ( 'se ha creado.' ), 'warn' );
    }
    if(isset($_POST['modificar']) and $InputError!=1){
        $i=0;
        foreach ($tableColumns as $valuees) {
            $fields.=$tableColumns[$i]."= '".$_POST['campo'.$i]."', ";
            $i++;
        }
        $fields = substr ($fields, 0, strlen($fields) - 2);
        $sql ="UPDATE ".$tableName." SET ".$fields. " WHERE ".$tableColumns[0]."='".$areacode."' ";
        /*echo "<br>".$sql;
        echo "<br>".$fields;
        exit;
         */
        DB_query($sql, $db);
        unset($areacode);
    }
    
}//Fin de insertar/modificar

if (isset ( $sql ) && $InputError != 1 && ($InputError != 2)) {
	$result = DB_query ( $sql, $db, $ErrMsg );
	if ($pagina == 'Stock' and isset ( $_POST ['enviar'] )) {
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/StockCategories.php?" . SID . "'>";
	}
}

//Variables para pasar de pagina
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

echo "<form method='post' action=" . $_SERVER ['PHP_SELF'] . "?" . SID . ">";

if (! isset ( $areacode ) or isset($_GET['borrar']) or isset($_POST['modificar'])) {
	echo '<table><tr>
	<td>
		' . _ ( ''.$tableTitle[0] ) . '<br><input type="text" name="" value="' . $area . '" size=25 maxlength=55>
	</td>
	<td>
		' . _ ( ''.$tableTitle[1] ) . '<br><input type="text" name="" value="' . $matriz . '" size=25 maxlength=55>
	</td>
        <td>';
            $i=0;
            $res=DB_query("select MIN(anhofiscal) as min, MAX(anhofiscal) as max FROM ".$tableName, $db);
            $myrow=  DB_fetch_array($res);
            $min=$myrow['min'];
            $max=$myrow['max'];
            //echo "<br>Min: ".$min;
            //echo "<br>Max: ".$max;
            $anhoActual=date("Y");
            //echo "<br>Max: ".$anhoActual; 
            while($min<=$max){
                if($min==$anhoActual){
                    $opciones.="<option selected value=".$min.">$min</option>";
                }else{
                $opciones.="<option value=".$min.">$min</option>";
                }
                $min++;
            }
            echo "<select name='selectanho'>".$opciones."</select>";
    echo '</td>
	<td valign=bottom>
		<input type=submit name=buscar value=' . _ ( 'Buscar' ) . '>
	</td></tr></table>';
	echo "<div class='centre'><hr width=50%></div><br>";
	if ($Offset == 0) {
		$numfuncion = 1;
	} else {
		$numfuncion = $num_reg * $Offset + 1;
	}
	$Offsetpagina = 1;
	
	// esta parte sirve para mostrar la primera tabla con todos los registros existentes
	
	$sql = "SELECT ".  implode(", ",$tableColumns)."
		FROM ".$tableName." as a ";
        if(!isset($_POST['buscar'])){
            $sql.=" WHERE anhofiscal=DATE_FORMAT(now(),'%Y') ";
        }else{
            $sql.=" WHERE anhofiscal='".$_POST['selectanho']."'";
        }
	if (strlen ( $area ) >= 1) {
		$sql = $sql . " AND (a.".$tableColumns[0]." like '%" . $area . "%')";
	}
	
	if (strlen ( $matriz ) >= 1) {
		$sql = $sql . " AND (a.".$tableColumns[1]." like '%" . $matriz . "%')";
	}
	$sql = $sql . " ORDER BY  a.".$tableColumns[0].", a.".$tableColumns[1]."";
	$result = DB_query ( $sql, $db );
	
	$sql = "SELECT ".  implode(", ",$tableColumns)."
		FROM ".$tableName." as a ";
        
        if(!isset($_POST['buscar'])){
            $sql.=" WHERE anhofiscal=DATE_FORMAT(now(),'%Y') ";
        }else{
            $sql.=" WHERE anhofiscal='".$_POST['selectanho']."'";
        }
        
	if (strlen ( $area ) >= 1) {
		$sql = $sql . " AND (a.".$tableColumns[0]." like '%" . $area . "%')";
	}
	
	if (strlen ( $matriz ) >= 1) {
		$sql = $sql . " AND (a.".$tableColumns[1]." like '%" . $matriz . "%')";
	}
	if (DB_num_rows ( $result ) == 0) {
		
		prnMsg ( _ ( 'No hay registros con esa Busqueda.' ), 'warn' );
	}
	
	$sql = $sql . " LIMIT " . $num_reg . " OFFSET " . ($Offset * $num_reg);
	
	/*
	 * if($_SESSION['UserID'] == "admin"){ echo '<pre>'.$sql; }
	 */
	$result = DB_query ( $sql, $db );
	$ListCount = DB_num_rows ( $result );
	$ListPageMax = ceil ( $ListCount / $num_reg );
	
	// / fin consulta join
	
	// echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
	if (! isset ( $areacode )) {
		echo "<div class='centre'>" . _ ( 'LISTADO DE'.$titulo_abc ) . "</div>";
		echo '<table width=50%>';
		echo '	<tr>';
		if ($ListPageMax > 1) {
			if ($Offset == 0) {
				$Offsetpagina = 1;
			} else {
				$Offsetpagina = $Offset + 1;
			}
			echo '<td>' . $Offsetpagina . ' ' . _ ( 'de' ) . ' ' . $ListPageMax . ' ' . _ ( 'Paginas' ) . '. ' . _ ( 'Ir a la Pagina' ) . ':';
			echo '<select name="Offset1">';
			$ListPage = 0;
			while ( $ListPage < $ListPageMax ) {
				
				if ($ListPage == $Offset) {
					echo '<option VALUE=' . $ListPage . ' selected>' . ($ListPage + 1) . '</option>';
				} else {
					echo '<option VALUE=' . $ListPage . '>' . ($ListPage + 1) . '</option>';
				}
				$ListPage ++;
				$Offsetpagina = $Offsetpagina + 1;
			}
			echo '</select></td>
			<td><input type="text" name="num_reg" size=1 value="' . $num_reg . '"></td>
			<td>
				<input type=submit name="Go1" VALUE="' . _ ( 'Ir' ) . '">
			</td>';
			if ($Offset > 0) {
				echo '<td align=center cellpadding=3 >
				<input type="hidden" name="previous" value=' . number_format ( $Offset - 1 ) . '>
				<input tabindex=' . number_format ( $j + 7 ) . ' type="submit" name="Prev" value="' . _ ( 'Anterior' ) . '">
			</td>';
			}
			;
			if ($Offset != $ListPageMax - 1) {
				echo '<td style="text-align:right">
				<input type="hidden" name="nextlist" value=' . number_format ( $Offset + 1 ) . '>
				<input tabindex=' . number_format ( $j + 9 ) . ' type="submit" name="Next" value="' . _ ( 'Siguiente' ) . '">
			</td>';
			}
		}
		echo '</tr>
		</table>';
	}
	
	echo '<table border=1 width=50%>';
        
        foreach ($tableTitle as $encabezados) {
            if($showColumns[$i]==1){
            echo "<th class='titulos_principales'>" . _ ( ''.$tableTitle[$i] ) . "";
            echo"</th>";
            }
            $i++;
        }
	$i=0;
        foreach ($tableTitle as $encabezados) {
            if($showColumns[$i]==1){
            $cols.= "<td >%s</td>";
            }
        $i++;
        }
	$k = 0; // row colour counter
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		if ($myrow ['activo'] == 1) {
			$myrow ['activo'] = 'Si';
		} else {
			$myrow ['activo'] = 'No';
		}
                $anho=date('Y');
                if($anho==$_POST['selectanho'] or !isset($_POST['selectanho'])){
                    $isthisy="<td><a href=\"%s&areacode=%s\">Modificar</a></td>
			<td><a href=\"%s&areacode=%s&borrar=1\">Eliminar</a></td>";
                }
		printf ( "".$cols."
			".$isthisy."
			</tr>", $myrow [0], $myrow [1], $myrow [2], $_SERVER ['PHP_SELF'] . '?' . SID, $myrow ['0'], $_SERVER ['PHP_SELF'] . '?' . SID, $myrow ['0'] );
		$numfuncion = $numfuncion + 1;
	}
	echo '</table>';
	echo '</form>';
        
}

if (isset ( $areacode )) {
	$sql = "SELECT ".  implode(",", $tableColumns)." 
		FROM ".$tableName." 
		WHERE ".$tableColumns[0]."='" . $areacode . "'";
	$result = DB_query ( $sql, $db );
	$myrow = DB_fetch_array ( $result );
        $i=0;
        foreach ($tableColumns as $posts) {
            $_POST ['campo'.$i] = $myrow [$i];
            $i++;
        }
}
else{
  $i=0;
        foreach($tableColumns as $fields){
            unset($_POST['campo'.$i]);
            $i++;
        }  
}
if (isset ( $areacode )) {
        echo "<input type='hidden' name='areacode' value='".$areacode."'>";
	echo "<div class='centre'><a href='" . $_SERVER ['PHP_SELF'] . "?" . SID . "'>" . _ ( ucwords(strtolower($titulo_abc)).' existentes' ) . '</a></div>';
}
echo "<form  method='post' action=" . $_SERVER ['PHP_SELF'] . "?" . SID . ">";

echo '<br>';
/*
 * if(isset($_POST['areacode'])) { echo "<input type=hidden name='areacode' VALUE='" . $_POST['areacode'] . "'>"; }
 */
echo "<div class='centre'><hr width=60%>" . _ ( 'ALTA/MODIFICACION DE '.$titulo_abc ) . "</div><br>";
echo '<table>';
$i=0;
foreach ($tableTitle as $campos) {
    if($showColumns[$i]==1){
         echo '<tr><td>' . _ ( ''.$tableTitle[$i] ) . ":</td>
            <td><input type='text' name='campo".$i."' size=40 maxlength=100 VALUE='" . $_POST ['campo'.$i] . "'></td></tr>
            <tr>";
        echo '</tr>';
    }
    else{
        echo '<tr><td>' . "</td>
            <td><input type='hidden' name='campo".$i."' size=40 maxlength=100 VALUE='" . $_POST ['campo'.$i] . "'></td></tr>
            <tr>";
        echo '</tr>';
    }
    $i++;
}

echo '</table>';
if (! isset ( $areacode )) {
	echo "<div class='centre'><input type='Submit' name='enviar' value='" . _ ( 'Enviar' ) . "'></div>";
} // aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
elseif (isset ( $areacode )) {
	
	echo "<div class='centre'><input type='Submit' name='modificar' value='" . _ ( 'Actualizar' ) . "'></div>";
}

include('includes/footer.inc');
?>