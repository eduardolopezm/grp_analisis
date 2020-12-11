<?php
/**
 * Carga de Inventario Inicial
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Modelos para las operaciones del panel de Suficiencia Manual
 */
//
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 661;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

if (isset($_POST['separador'])) {
    $separador = $_POST['separador'];
} else{
    if (isset($_GET['separador'])){
        $separador = $_GET['separador'];
    }
    else{
        $separador = ",";
    }
}
if (isset($_POST['subir']))
{
    $subir = $_POST['subir'];
}

//CONDICIONES AL LEER EL ARCHIVO
//echo 'entraaaaaaaaa'.$separador;   
if (isset($_POST['mostrar']) and $separador<>''){

    $nombre_archivo = $_FILES['userfile']['name'];
    $tipo_archivo = $_FILES['userfile']['type'];
    $tamano_archivo = $_FILES['userfile']['size'];

    $filename = 'pricelists/'.$nombre_archivo;
	//echo 'entraaaaaaaaa'.$tipo_archivo;   
    if ($tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='text/csv'){
  
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)){
            # UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO
        
            #Declara variables
        
            $tieneerrores=0;
            $lineatitulo=0;
            $mincolumnas=5;
        
            $columnaalmacen=0;
            $columnacodigo=1;
            $columnaexist=2;
            $columnacosto=3;
            $columnaoptimo=4;
            $columnafecha=5;
            $columnalote=6;
            $columnapuerto=7;
            $columnapedimento=8;
            $columnanetrada=9;

		   $lineas = file($filename);
            $cont=0;
            $j=0;
            $flagerror=false;
            foreach ($lineas as $line_num =>$line){
                $datos = explode($separador, $line); # Convierte en array cada una de las lineas
                $columnaslinea = count($datos);
				if ($j > 0){

					if ($columnaslinea < 9){
					  $error = _('FALTAN COLUMNAS EN LA LINEA No. ') . $j;
					  prnMsg($error,'error');
					  $flagerror=true;
					  
					}
					
					$alm = trim($datos[$columnaalmacen]);
					$qry = "Select * FROM locations
							where loccode = '$alm'";
					$r = DB_query($qry,$db);
					if (DB_num_rows($r)==0){
					  $error = _('EL DATO DEL ALMACEN NO ESTA REGISTRADO EN LA BASE DE DATOS. VERIFICAR LINEA No. ') . $j;
					  prnMsg($error,'error');
					  $flagerror=true;
					
					}
										

					$cod = trim($datos[$columnacodigo]);	
					$qry = "Select * FROM stockmaster
							where stockid = '$cod'";
					$r = DB_query($qry,$db);
					if (DB_num_rows($r)==0){
					  $error = _('EL DATO DEL PRODUCTO NO ESTA REGISTRADO EN LA BASE DE DATOS. VERIFICAR LINEA No. ') . $j;
					  prnMsg($error,'error');
					  $flagerror=true;
					
					}
				
					$qry = "Select * FROM locstock
							where loccode = '$alm'
							and stockid = '$cod'";
					$r = DB_query($qry,$db);
					if (DB_num_rows($r)==0){
					  $error = _('EL PRODUCTO NO TIENE ASIGNADO ALMACEN. VERIFICAR LINEA No. ') . $j;
					  prnMsg($error,'error');
					  $flagerror=true;
					
					}
					
					$lote = trim($datos[$columnalote]);
					$qry = "SELECT stockmaster.controlled
							FROM stockmaster
							WHERE stockid = '$cod'";
					$r = DB_query($qry,$db);
					$controlled = DB_fetch_array($r);
					if (($controlled['controlled'] == 1) and ($lote == "")){
						$error = _('Es obligatorio agregar el numero de lote o serie ') . $j;
						prnMsg($error,'error');
						$flagerror=true;
							
					}

					$j++;
				}//j > 0
			}
        }
    }
}

//FIN DE CONDICIONES
if(isset($_GET['subir'])){
    $subir='nosubir';
}elseif(isset($_POST['subir'])){
    $subir='sisubir';
    
}else{
    $subir='';
}

//ALTA DE INFORMACION
if ($subir=='sisubir' ){
  $nombre_archivo = $_POST['archivo'];

  $filename = 'pricelists/'.$nombre_archivo;
    
  $columnaalmacen=0;
  $columnacodigo=1;
  $columnaexist=2;     
  $columnacosto=3;
  $columnaoptimo=4;
  $columnafecha=5;
  $columnalote=6;
  $columnapuerto=7;
  $columnapedimento=8;
  $columnanetrada=9;
  $j=0;  
  $lineas = file($filename);
  $Result = DB_Txn_Begin($db);
  
  foreach($lineas as $line_num =>$line){
  	$separador  = ',';
	
	$datos = explode($separador, $line);

	if($j>0){

		$almacen=addslashes(trim($datos[$columnaalmacen]));
		$codigo=addslashes(trim($datos[$columnacodigo]));
		$exist=addslashes(trim($datos[$columnaexist]));
		$costo=addslashes(trim($datos[$columnacosto]));
		$optimo=addslashes(trim($datos[$columnaoptimo]));
		$fecha=addslashes(trim($datos[$columnafecha]));
		$lote=addslashes(trim($datos[$columnalote]));
		$puerto=addslashes(trim($datos[$columnapuerto]));
		$pedimento=addslashes(trim($datos[$columnapedimento]));
		$entrada=addslashes(trim($datos[$columnanetrada]));
		
		if ($exist==""){
			$exist=0;
		}

		 $qry = "SELECT tagref, ln_ue FROM locations WHERE loccode = '$almacen'";
		 $rs = DB_query($qry,$db);
		 $row = DB_fetch_array($rs);
		 $LocTagRef = $row['tagref'];
		 $ln_ue = $row['ln_ue'];

		  // insertar mov de entrada inicial
			$AdjustmentNumber = GetNextTransNo(300,$db);
			$SQLAdjustmentDate=Date('Y-m-d');
			$narrative='Carga Datos _ 06-08-2013';
			if ($exist=="")
				$exist=0;
			
		   $PeriodNo=0;
		   $EstimatedAvgCostXlegal=$costo;
		   $SQL = "INSERT INTO stockmoves (
					  stockid,
					  type,
					  transno,
					  loccode,
					  trandate,
					  prd,
					  narrative,
					  qty,
					  newqoh,
					  tagref,
					  standardcost,
		   			  avgcost,
		   			  ln_ue)
				   VALUES (
					  '" . $codigo. "',
					  300,
					  " . $AdjustmentNumber . ",
					  '$almacen',
					  '" . $fecha . "',
					  " . $PeriodNo . ",
					  '" . $narrative ." Usuario:".$_SESSION['UserID']."',
					  '" . $exist . "',
					  '" . $exist . "',
					  '" . $LocTagRef ."',
					  '" . $EstimatedAvgCostXlegal ."',
					  '" . $EstimatedAvgCostXlegal ."',
					  '" . $ln_ue ."'
				  )";
				  $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
				  $DbgMsg =  _('The following SQL to insert the stock movement record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				 
		    $stockmoveno = DB_Last_Insert_ID($db, "stockmoves", "stkmoveno");
		    //
		    $sql = "UPDATE locstock
				   SET quantity= quantity+'$exist',
				   reorderlevel='$optimo'
				   WHERE stockid = '$codigo'
				   AND loccode='$almacen'";
		    $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			//echo "<br>" . $sql;
		    $SQL = "DELETE
		    		FROM stockserialitems
		    		WHERE stockid = '".$codigo."'
		    		AND loccode = '".$almacen."'";
		   $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		    $SQL = "INSERT INTO stockserialitems (stockid,
		    									 loccode, 
		    									serialno, 
		    									quantity, 
		    									standardcost, 
		    									customs, 
		    									customs_number, 
		    									customs_date, 
		    									pedimento)
					VALUE ('".$codigo."',
							'".$almacen."',
							'".$lote."',
							'".$exist."',
							'".$costo."',
							'".$puerto."',
							'".$pedimento."',
							'".$entrada."',
							'".$pedimento."')";
		    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		    
		    $SQL = "INSERT INTO stockserialmoves (stockmoveno,
		    										stockid,
		    										serialno,
		    										moveqty, 
		    										standardcost)
					VALUE ('".$stockmoveno."',
							'".$codigo."',
							'".$lote."',
							'".$exist."',
							'".$costo."')";
		    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		    $result = 0;
		    $sql = "SELECT tags.legalid
					FROM locations
					INNER JOIN tags ON locations.tagref = tags.tagref
					WHERE locations.loccode = '".$almacen."'";
		    $result = DB_query($sql, $db);
		    $myrow1 = DB_fetch_array($result);
		    $razonsocial = $myrow1['legalid'];
		    
		    $sql = "SELECT *
					FROM stockcostsxlegal
					WHERE stockcostsxlegal.stockid = '".$codigo."'
					AND stockcostsxlegal.legalid = '".$razonsocial."'";
		    $result = DB_query($sql, $db);
		    
		    if(DB_num_rows($result) <= 0){
		    	$SQL = "INSERT INTO stockcostsxlegal (legalid,
		    										stockid,
		    										qty,
		    										avgcost,
		    										Comments)
						VALUE ('".$razonsocial."',
								'".$codigo."',
								'".$exist."',
								'".$costo."',
								'Carga Inicial atravez de layout')";
		    	$ErrMsg = _("No se puedo agregar el costos");
		    	// echo '<pre>'.$SQL;
		    	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		    }else{
		    	$SQL = "UPDATE stockcostsxlegal
						SET qty = '".$exist."',
							avgcost = '".$costo."',
							Comments = 'Se actualizo a travez del layout de cargas'
						WHERE stockid = '".$codigo."'
						AND legalid = '".$razonsocial."'";
		    	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		    }
	}//if j>0
	$j++;            
  }
  $Result = DB_Txn_Commit($db);
  prnMsg("Bienes agredados correctamente",'sucess');
              
}


?>

<form method='post' enctype='multipart/form-data' action='<?php echo $_SERVER['PHP_SELF'] . '?' . SID  ?>' name="form">
	<div class="row" style="margin-top: 1%;">
		<div class="col-md-12">
			<div class="table-responsive">
				<table class="table table-bordered table-condensed fts12">
					<thead>
						<tr class="header-verde">
							<td colspan="12" style="text-align:center">
								<b>Formato de columnas del archivo a subir (Sensible a mayusculas y minusculas)</b>
							</td>
						</tr>

						<tr valign="top" class="header-verde">
							<?php 
								$cssLinea=" display: none;";
								if(isset($_POST['mostrar'])){
									$cssLinea="";
								}
							?>
							<td style="text-align:center;<?php echo $cssLinea ?>"><b>#</b></td>
							<td style="text-align:center;"><b>Almacen(codigo)</b></td>
							<td style="text-align:center;"><b>Producto(codigo)</b></td>
							<td style="text-align:center;"><b>Existencia</b></td>
							<td style="text-align:center;"><b>Costo</b></td>
							<td style="text-align:center;"><b>Optimo</b></td>
							<td style="text-align:center;"><b>Fecha</b><br>Ej. aaaa-mm-dd</td>
							<td style="text-align:center;"><b>Lote y/o Serie</b></td>
							<td style="text-align:center;"><b>Puerto</b></td>
							<td style="text-align:center;"><b>Pedimento</b></td>
							<td style="text-align:center;"><b>Fecha Entrada</b><br>Ej. aaaa-mm-dd</td>
						</tr>
					</thead>
					<tbody>

						<?php
						if(isset($_POST['mostrar'])){


							$k=0;
							$j=0;
							if(file($filename)!=''){
								$lineas = file($filename);
								//RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO 
								//$cont=0;
								foreach ($lineas as $line_num =>$line)
								{
									$datos = explode($separador, $line); # Convierte en array cada una de las lineas
									$columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador
									if($j>0){
										//ASIGNACION DE CADA POSICION DEL ARREGLO A UNA VARIABLE
										$codigo=$datos[$columnacodigo];
										$almacen=$datos[$columnaalmacen];
										$exist=$datos[$columnaexist];
										$costo=$datos[$columnacosto];
										$optimo=$datos[$columnaoptimo];

										if ($k==1){
											echo '<tr class="EvenTableRows">';                                         
											$k=0;
										} else {
											echo '<tr class="OddTableRows">';
											$k=1;
										}

										printf("<td><font size=2>%s</td>
										<td style='text-align:center;'><font size=2>%s</td>
										<td style='text-align:center;'><font size=2>%s</td>
										<td style='text-align:center;'><font size=2>%s</td>
										<td style='text-align:right;'><font size=2>%s</td>
										<td style='text-align:right;'><font size=2>%s</td>              
										<td style='text-align:center;'><font size=2></td>              
										<td style='text-align:center;'><font size=2></td>              
										<td style='text-align:center;'><font size=2></td>              
										<td style='text-align:center;'><font size=2></td>              
										<td style='text-align:center;'><font size=2></td>              
										</tr>",
										($j + 1),
										$almacen,
										$codigo,
										$exist,
										number_format($costo),
										$optimo
										);
									}
									$j++;//numeracion de lineas//
								}
							}
						}

						?>
						
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="row"  style="margin-top: 1%;margin-bottom:2%;">
		<?php
		if(!isset($_POST['mostrar'])){
		?>

			<div class="col-md-4">
				<font size=2>Archivo (.csv o .txt): </font>
				<input type='file' name='userfile' size=50 >
			</div>
			<div class="col-md-4">
		    	<component-text-label label="Separador:" id="separador" name="separador" value="<?php echo $separador ?>" size="1" maxlength="1"></component-text-label>
		    </div>

		    <br />
		    <br />

			<div class="col-md-12" style="margin-top: 1%;">
				<component-button type="submit" id="mostrar" name="mostrar" class="glyphicon glyphicon-file"  value="Mostrar Informaci&oacute;n"></component-button>
				<a href="LoadProductExist.php" class="btn btn-default botonVerde glyphicon glyphicon-remove"> Cancelar </a>
			</div>
		<?php
		}else{
		?>
			<div class="col-md-12" style="margin-top: 1%;">
				<input  type='hidden'  name='archivo' value='<?php  echo $nombre_archivo ?>'>
				<component-button type="submit" id="subir" name="subir" class="glyphicon glyphicon-open-file"  value="Subir Informaci&oacute;n"></component-button>
				<a href="LoadProductExist.php" class="btn btn-default botonVerde glyphicon glyphicon-remove"> Cancelar </a>
				
			</div>
		<?php
		}
		?>
	</div>
</form>

<?php
include('includes/footer_Index.inc'); 
?>