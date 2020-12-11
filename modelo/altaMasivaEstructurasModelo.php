<?php
/**
* @date: 26/12/2017
* @package: ap_grp
* @version 0.1
* 
* para el funcionamiento del alta masiva de los catálogos
*/
/* INCLUCIONES NECESARIAS */
require_once "./lib/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php";

/* DECLARACION DE CLASE A EJECUTAR*/
class altaMasivaEstructurasModelo extends masterController
{
	function __construct($db)
	{
		# asignación indispensable para el uso de la variable de conexión
		$this->db = $db;
		$this->pathFilesUpload = './ArchivosSubidos/CargaMasiva/';
		$this->tbl = array(
			'programatica'=>'tb_cat_estructura_programatica_temp', 'economica'=>'tb_cat_estructura_economica_temp',
			'administrativa'=>'tb_cat_estructura_administrativa_temp', 'relacion'=>'tb_cat_relacion_pp_partida_temp'
		);
		$this->campos = array(
			'programatica'=>array(
				'A'=>['campo'=>'id_nu_ur','type'=>'string'],
				'B'=>['campo'=>'id_nu_fi','type'=>'string'],
				'C'=>['campo'=>'id_nu_fu','type'=>'number'],
				'D'=>['campo'=>'id_nu_sf','type'=>'number'],
				'E'=>['campo'=>'id_nu_rg','type'=>'string'],
				'F'=>['campo'=>'id_nu_ai','type'=>'string'],
				'G'=>['campo'=>'id_nu_pp','type'=>'string'],
				'H'=>['campo'=>'ln_anexo','type'=>'string'],
				'INDEX'=>'id_nu_estructura_programatica'
			),
			'economica'=>array(
				'A'=>['campo'=>'id_nu_partida','type'=>'string'],
				'B'=>['campo'=>'id_nu_tg','type'=>'number'],
				'C'=>['campo'=>'id_nu_ff','type'=>'number'],
				'INDEX'=>'id_nu_estructura_administrativa'
			),
			'administrativa'=>array(
				'A'=>['campo'=>'id_nu_ur','type'=>'string'],
				'B'=>['campo'=>'id_nu_auxiliar','type'=>'string'],
				'C'=>['campo'=>'id_nu_ef','type'=>'string'],
				'INDEX'=>'id_nu_estructura_economica'
			),
			'relacion'=>array(
				'A'=>['campo'=>'id_nu_pp','type'=>'string'],
				'B'=>['campo'=>'id_nu_partida','type'=>'string'],
				'INDEX'=>'id_nu_relacion_pp_partida'
			),
		);
		$this->cells = array('A','B','C','D','E','F','G','H','I','J','K','L','M','M','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		/**
		 * NOTE: En caso de que se realice modificaciones en la configuración de la tabla "budgetConfigClave"
		 * 		 es necesario colocar la configuración adecuada en la siguiente variable.
		 */
		$this->sheetsComparison = array(
			'programatica'=>array(
				'A'=>[ 'tabla'=>'tags', 'col'=>'tagref' ],
				'B'=>[ 'tabla'=>'g_cat_finalidad', 'col'=>'id_finalidad' ],
				'C'=>[ 'tabla'=>'g_cat_funcion', 'col'=>'id_funcion' ],
				'D'=>[ 'tabla'=>'g_cat_sub_funcion', 'col'=>'id_subfuncion' ],
				'E'=>[ 'tabla'=>'g_cat_reasignacion', 'col'=>'cprg' ],
				'F'=>[ 'tabla'=>'tb_cat_actividad_institucional', 'col'=>'cain'],
				'G'=>[ 'tabla'=>'tb_cat_programa_presupuestario', 'col'=>'cppt' ],
				'H'=>[ 'tabla'=>'tb_cat_componente_presupuestario', 'col'=>'cp', 'aux'=>3]
			),
			'economica'=>array(
				'A'=>[ 'tabla'=>'tb_cat_partidaspresupuestales_partidaespecifica', 'col'=>'partidacalculada' ],
				'B'=>[ 'tabla'=>'g_cat_tipo_de_gasto', 'col'=>'ctga' ],
				'C'=>[ 'tabla'=>'g_cat_fuente_de_financiamiento', 'col'=>'cfin' ],
			),
			'administrativa'=>array(
				'A'=>[ 'tabla'=>'tags', 'col'=>'tagref' ],
				'B'=>[ 'tabla'=>'tb_cat_unidades_ejecutoras', 'col'=>'ln_aux1' ],
				'C'=>[ 'tabla'=>'g_cat_geografico', 'col'=>'cg' ],
			),
			'relacion'=>array(
				'A'=>[ 'tabla'=>'g_cat_ppi', 'col'=>'pyin' ],
				'B'=>[ 'tabla'=>'tb_cat_partidaspresupuestales_partidaespecifica', 'col'=>'partidacalculada' ],
			),
		);
		# se reinician las tablas
		$this->resetTables();
	}

	public function uploadFiles()
	{
		$data = ['success'=>false,'msg'=>'Ocurrió un error al momento de generar la información.'];
		$docs = $_FILES['archivos'];
		if(!empty($docs)){
			foreach ($docs['name'] as $key => $doc) {
				$doc = $this->getDocument($docs,$key);
				$dataFile = $this->moveFile($doc);
				if($dataFile['success']){
					$content = $this->getDataFromExcel($dataFile['name']);
					$data = $this->checkAndGenerate($content);
				}
				else if($doc->error==0){ $data['msg'] = 'No se pudo cargar el archivo. Favor de contactar con el administrador.'; }
				else{ $data['msg'] = $this->getUploadError($doc->error).' Favor de contactar con el administrador.'; }
			}
		}
		else{ $data['msg'] = 'Es necesario adjuntar un documento.'; }
		return $this->response($data);
	}

	public function getDataFromExcel($nameFile)
	{
		$data = array();
		$namesSheet = array('programatica','economica','administrativa','relacion');
		$excel = PHPExcel_IOFactory::load($nameFile);
		$sheets = $excel->getAllSheets();
		foreach ($sheets as $k => $sheet) {
			if($k>3){ break; }
			$name = $namesSheet[$k];
			$dSheet = $sheet->toArray(null,true,true,true);
			$start = $this->checkStart($dSheet, $name);
			$data[$name] = array( 'data' => $dSheet, 'start' => $start );
		}
		return $data;
	}

	public function checkAndGenerate($sheets)
	{
		$data = array('success'=>false,'msg'=>'Ocurrió un error al momento de generar la información.');
		$flag = 0;
		$rand = rand(1,15);
		DB_Txn_Begin($this->db);
		try {
			foreach ($sheets as $titulo => $sheet) {
				$dSheet = $sheet['data'];
				$numCells = count($dSheet[$sheet['start']]);
				for ($i=($sheet['start']+1); $i < count($dSheet); $i++) { 
					# procesamiento de row según la hoja en donde se encuentre
					foreach ($dSheet[$i] as $cell => $val) {
						$comparison = empty($this->sheetsComparison[$titulo][$cell])? '' : $this->sheetsComparison[$titulo][$cell];
						# si no se cuenta con comparacion significa de se tomo una columna mas de lo que se esperaba
						# solo se procesan las columnas que se pueden comparar
						if(!empty($comparison)){
							# generación del query para la consulta
							if(!empty($comparison['aux'])){
								switch ($comparison['aux']) {
									# @NOTE: en caso de que el auxiliar cuente con los 7 ceros comentar la siguiente linea
									// case 3: $val = substr($val,0,(strlen($val)-7)); break;
									case 1:
										# sin definir
										break;
								}
							}
							if(!empty($comparison['conb'])){
								switch ($comparison['conb']) {
									case 'int': $val = (int)$val; break;
								}
							}
							$sql = 'SELECT * FROM '.$comparison['tabla'].' WHERE '.$comparison['col']." = '$val'";
							$result = DB_query($sql,$this->db);
							// $data['result'] = DB_fetch_array($result);
							# comprobación de existencia en base
							if(DB_num_rows($result) != 0){
								$tblTemp = $this->tbl[$titulo];
								$camposTemp = $this->campos[$titulo][$cell];
								$campo = $camposTemp['campo'];
								$typeCamp = $camposTemp['type'];

								if($cell == 'A'){
									$sqlRow = "INSERT INTO $tblTemp ($campo) ".$this->getValuesFromType($val, $typeCamp);
								}else{
									$INDEX = $this->campos[$titulo]['INDEX'];
									$sqlSel = "SELECT MAX($INDEX) FROM $tblTemp";
									$reSqlSel = DB_query($sqlSel,$this->db);
									$sel = DB_fetch_row($reSqlSel);
									$up = $this->getValuesFromType($val, $typeCamp, $campo, true);
									$sqlRow = "UPDATE $tblTemp $up WHERE  $INDEX = ".$sel[0];
								}
								$resultRow = DB_query($sqlRow, $this->db);

								# en caso de error se elimina todo y sale del loop
								if($resultRow!=true){
									$data['msg'] = 'No se pudo registrar la información de la celda '.$cell
										.' dentro de la linea '.$i.' de la hoja '.$titulo
										.' dentro de la información temporal.';
									$flag = 1;
									DB_Txn_Rollback($this->db);
									break;
								}
							}else{
								$data['msg'] = 'El dato de la celda '.$cell
									.' dentro en la linea '.$i.' de la hoja '
									.$titulo.'. No coincide con lo esperado o no se encuentra registrada';
								$flag = 1;
								DB_Txn_Rollback($this->db);
								break;
							}
						}
					} // fin rows
					if($flag){ break; }
				} // fin sheet
				if($flag){ break; }
			} // fin Excel

			# Si flag es 0 se pasa la información de las tablas temporales a consolidar la información
			if($flag == 0){
				$f = 1;
				$tablasTemp = [
					'programatica'=>['tbl'=>'tb_cat_estructura_programatica', 'cols'=>'id_nu_ur,id_nu_fi,id_nu_fu,id_nu_sf,id_nu_rg,id_nu_ai,id_nu_pp,ln_anexo' ],
					'economica'=>['tbl'=>'tb_cat_estructura_economica', 'cols'=>'id_nu_partida,id_nu_tg,id_nu_ff'],
					'administrativa'=>['tbl'=>'tb_cat_estructura_administrativa', 'cols'=>'id_nu_ur,id_nu_auxiliar,id_nu_ef'],
					'relacion'=>['tbl'=>'tb_cat_relacion_pp_partida', 'cols'=>'id_nu_pp,id_nu_partida']
				];
				foreach ($tablasTemp as $k => $valor) {
					$tabla = $valor['tbl'];
					$cols = $valor['cols'];
					$sqlIntCon = " INSERT INTO $tabla ($cols) SELECT $cols FROM ".$tabla."_temp";
					$respSqlInt = DB_query($sqlIntCon,$this->db);
					if($respSqlInt!=true){
						$data['msg'] = 'No se logro consolidar la información. Favor de contactar con el administrador.';
						$f = 0;
						DB_Txn_Rollback($this->db);
						break;
					}
				}
				if($f){
					$data['success'] = true;
					$data['msg'] = 'Se realizo con éxito la carga de los datos a los catálogos.';
				}
			}
			DB_Txn_Commit($this->db);
		} catch (Exception $e) {
			$data['msg'] = $e->getMessage().' Contactar al administrador';
			DB_Txn_Rollback($this->db);
		}
		return $data;
	}

	public function getValuesFromType($valor, $typeCamp, $campo='', $update=false)
	{
		$txt = "";
		switch ($typeCamp) {
			case 'string':
				$txt = $update? " SET $campo = '$valor' " : " VALUES('$valor') ";
				break;
			default:
				$txt = $update? " SET $campo = $valor " : " VALUES($valor) ";
				break;
		}
		return $txt;
	}

	public function checkStart($data, $type)
	{
		$pattern = '';
		$flag = 1;
		switch ($type) {
			case 'programatica':
			case 'administrativa':
				$pattern = 'UR';
				break;
			case 'economica':
				$pattern = 'PARTIDA';
				break;
			case 'relacion':
				$pattern = 'PP';
				break;
		}
		foreach ($data as $k => $v) {
			if($v['A'] == $pattern){
				$flag = $k;
			}
		}
		return $flag;
	}

	public function moveFile($doc)
	{
		$name = $this->pathFilesUpload.$doc->name;
		$data = ['name'=>$name,'success'=>false,'temp'=>$doc->temp_name];
		if(is_uploaded_file($doc->temp_name)){
			$data['success'] = move_uploaded_file($doc->temp_name, $name);
		}
		return $data;
	}

	public function getDocument($docs,$key)
	{
		$data = array(
			'error'=>$docs['error'][$key],
			'name'=>$docs['name'][$key],
			'size'=>$docs['size'][$key],
			'temp_name'=>$docs['tmp_name'][$key],
			'type'=>$docs['type'][$key],
		);
		return (object)$data;
	}

	public function getUploadError($err)
	{
		$errors = array(
			1=>'El archivo subido es mas grande de lo permitido.',
			'El Archivo subido es mas grande que lo indicado en el campo de carga.',
			'Se subió parcialmente el archivo.',
			'No se cargo ningún archivo.',
			'Falta la carpeta de almacenamiento temporal.',
			'No se puede escribir el archivo en el disco.',
			'Una de las exenciones de carga se detuvo inesperadamente.'
		);
		return $errors[$err];
	}

	public function resetTables()
	{
		$data = ['msg'=>''];
		foreach ($this->tbl as $k => $tabla) {
			$sql = "DELETE FROM `$tabla`;"/*" ALTER TABLE `$tabla` AUTO_INCREMENT = 1;"*/;
			$result = DB_query($sql,$this->db);
			if($result!=true){
				$data['msg'] = 'No se pudo reiniciar la tabla '.$k.'. Favor de contactar con el administrador.';
			}
		}
		return $this->response($data);
	}

	public function prueba($value='')
	{
		$data = ['success'=>true,'msg'=>'si jala ~(^.^ ~)'];
		return $this->response($data);
	}

	# depreciada a la fecha 28.12.17
	public function createTable(&$dataBase, $type, $rand)
	{
		$sql = 'CREATE TABLE ';
		switch ($type) {
			case 'programatica':
				$sql .= " `tb_cat_estructura_programatica_$rand` ( ".
							" `id_nu_estructura_programatica` int(11) AUTO_INCREMENT PRIMARY KEY, ".
							" `id_nu_ur` varchar(5), ".
							" `id_nu_fi` int(11), ".
							" `id_nu_fu` int(11), ".
							" `id_nu_sf` int(11), ".
							" `id_nu_rg` int(10), ".
							" `id_nu_ai` int(11), ".
							" `id_nu_pp` int(11), ".
							" `ln_anexo` varchar(100) ".
						" ) ";
				break;
			case 'administrativa':
				$sql .= " `tb_cat_estructura_administrativa_$rand` ( ".
						" `id_nu_estructura_administrativa` AUTO_INCREMENT PRIMARY KEY, ".
						" `id_nu_ur` varchar(5), ".
						" `id_nu_auxiliar` int(11), ".
						" `id_nu_ef` int(11) ".
					" ) ";
				break;
			case 'economica':
				$sql .= " `tb_cat_estructura_economica_$rand` ( ".
						" `id_nu_estructura_economica` int(11) AUTO_INCREMENT PRIMARY KEY, ".
						" `id_nu_partida` int(11), ".
						" `id_nu_tg` int(11), ".
						" `id_nu_ff` int(11) ".
					" )";
				break;
			case 'relacion':
				$sql .= " `tb_cat_relacion_pp_partida_$rand` ( ".
						" `id_nu_relacion_pp_partida` int(11) AUTO_INCREMENT PRIMARY KEY, ".
						" `id_nu_pp` int(11), ".
						" `id_nu_partida` int(11) ".
					" ) ";
				break;
		}
		$sql.=' ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		return array('success'=>DB_query($sql,$dataBase),'sql'=>$sql);
	}

	# depreciada a la fecha 28.12.17
	public function dropTable(&$dataBase,$type,$rand)
	{
		$sql = "DROP TABLE ";
		switch ($type) {
			case 'programatica':
				$sql .= "tb_cat_estructura_programatica_$rand;";
				break;
			case 'administrativa':
				$sql .= "tb_cat_estructura_administrativa_$rand;";
				break;
			case 'economica':
				$sql .= "tb_cat_estructura_economica_$rand;";
				break;
			case 'relacion':
				$sql = "tb_cat_relacion_pp_partida_$rand;";
				break;
		}
		return DB_query($sql,$dataBase);
	}
}

