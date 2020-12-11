<?php
	define('FPDF_FONTPATH','./fonts/');
	include ('includes/fpdf.php');
	 
	class rptTareasFacturacion extends FPDF {
		
		function Header(){
			global $db;
			
			$qry = "Select * FROM prdproyectos WHERE idproyecto='".$_GET['u_proyecto']."'";
			$rs = DB_query($qry,$db);
			$row = DB_fetch_array($rs);
			$proy = strtoupper($row['nombre']);
			$cliente = strtoupper($row['contacto']);
			
			$fi = $_GET['fi'];
			$fi = substr($fi,8,2)."-".substr($fi,5,2)."-".substr($fi,0,4);
			$ff = $_GET['ff'];
			$ff = substr($ff,8,2)."-".substr($ff,5,2)."-".substr($ff,0,4);
			
			$this->Image('.'.$rootpath . '/' . $_SESSION['LogoFile'],10,5);
			$this->SetFont('helvetica','B',10);
			$this->cell('',5,"QUERETARO, ".date("d")." DE ".glsnombremeslargo(date("m"))." DEL ".date("Y"),'0',1,'R');
			$this->SetXY(50,15);
			$this->cell('',5,$proy,'0',1,'L');
			$this->SetX(50);
			$this->cell('',5,"ATENCION A: ".$cliente,'0',1,'L');
			$this->ln();
			$this->SetX(50);
			$this->cell('',5,"PERIODO DESDE: ".$fi." HASTA ".$ff,'0',1,'L');
			
			$this->Ln();			
			$this->SetY(40);
			//$this->cell(200,4," ",'B',1,'L');			
			$this->SetFont('helvetica','B',9);
			$this->cell('',5,"DETALLE DE TAREAS REALIZADAS",'0',1,'C');
			/*$this->Ln();	
			$this->cell(20,4,'Fecha',0,0,'C');
			$this->cell(50,4,'Concepto',0,0,'C');
			$this->cell(90,4,'Descripcion',0,0,'C');
			$this->cell(15,4,'Horas',0,0,'C');
			$this->cell(20,4,'Asignado a',0,1,'C');
			*/
			$this->Ln();	

		}
		
		function exportPDF(){
			$this->fpdf('P','mm','Letter');
			
			$this->AliasNbPages();
			$this->SetAutoPageBreak(false);
			$this->AddPage();
			
			$this->SetFillColor(255,255,255);
			$fecha = "";
			
			$this->SetFont('helvetica','B',9);
			$this->cell(20,4,'Fecha',0,0,'C');
			$this->cell(50,4,'Concepto',0,0,'C');
			$this->cell(90,4,'Descripcion',0,0,'C');
			$this->cell(15,4,'Horas',0,0,'C');
			$this->cell(20,4,'Asignado a',0,1,'C');

			$this->SetFont('helvetica','',6);
			foreach($_SESSION['rptTareasFacturacion']['items'] as $arrdatos){
				$fechahora = $arrdatos['fecha'];
				$f = substr($fechahora,0,10);
				$f = substr($f,8,2)."-".substr($f,5,2)."-".substr($f,0,4);
				$h = substr($fechahora,11,5);
				$dato=$h;
				if ($f!=$fecha){
					$fecha=$f;
					$dato=$f." ".$h;
				}
			
				$this->cell(20,3,$dato,0,0,'C');
				
				$actX = $this->GetX();
				$actY = $this->GetY();
				$this->MultiCell(50,3,$arrdatos['concepto'],0,'L');
				$posYMultiCell = $this->GetY();
				$maxposy = $posYMultiCell;
				$this->SetXY($actX+50,$actY);// para regresar a la posicion sigte

				$actX = $this->GetX();
				$this->MultiCell(90,3,$arrdatos['descripcion'],0,'L');
				$posYMultiCell = $this->GetY();
				if ($posYMultiCell > $maxposy)
					$maxposy = $posYMultiCell;
				
				$this->SetXY($actX+90,$actY);// para regresar a la posicion sigte
				$this->cell(15,3,$arrdatos['horas'],0,0,'C');
				$this->cell(20,3,$arrdatos['recurso'],0,1,'L');
				
				$this->SetY($maxposy);
				$this->cell(200,3," ",'T',1,'L');
				if ($this->GetY()>=250){
					$this->AddPage();		
					$this->SetFont('helvetica','B',9);
					$this->cell(20,4,'Fecha',0,0,'C');
					$this->cell(50,4,'Concepto',0,0,'C');
					$this->cell(90,4,'Descripcion',0,0,'C');
					$this->cell(15,4,'Horas',0,0,'C');
					$this->cell(20,4,'Asignado a',0,1,'C');
					$this->SetFont('helvetica','',6);
				}
			}
			$this->SetFont('helvetica','B',8);
			$this->cell(160,3,'Total Horas',0,0,'R');
			$this->cell(15,3,$_SESSION['rptTareasFacturacion']['total'],0,1,'C');
			
			//agregar tabla resumen de horas x recurso y categoria
			if ($this->GetY()>=150)
				$this->AddPage();		
			else
				$this->Ln(5);

			global $db;
			$this->SetFont('helvetica','B',7);
			
			$qry = "SELECT catcode,description FROM tasks_categories ORDER BY tasks_categories.description";
			$res = DB_query($qry,$db);
			$this->cell(35,3,'Recurso',1,0,'C');
			$arrcateg = array();
			$arrcattot=array();
			while ($rows = DB_fetch_array($res)){
				$this->cell(14,3,trim($rows['description']),1,0,'C');
				$arrcateg[]=$rows['catcode'];
				$arrcattot[$rows['catcode']] = 0;
			}
			$this->cell(8,3,'',1,0,'C');//no tienen categ
			$arrcateg[]=-1;
			$arrcattot[-1] = 0;
			
			$this->cell(12,3,'TOTAL',1,0,'C');
			
			$this->Ln();
			
			$fi = $_GET['fi'];
			$ff = $_GET['ff'];
			$whereconditions = "";
			if ($_GET['profile'])		
				$whereconditions.=" and www_users.userid IN (SELECT userid from sec_profilexuser where profileid=".$_GET['profile'].")";

			if ($_GET['recurso'] and $_GET['recurso']!='*')
				$whereconditions.=" and tasks_horas.urecurso= '".$_GET['recurso']."'";

			$sql="SELECT  
						tasks_horas.urecurso as idproyecto,
						www_users.realname as recurso,
						CASE WHEN tasks_categories.catcode IS NULL THEN
							-1 ELSE tasks_categories.catcode END as catcode,
						tasks_categories.description as category,
						sum(tasks_horas.horas) AS horas
					FROM
						tasks_horas 
					LEFT JOIN tasks_movimientos ON tasks_horas.idtarea = tasks_movimientos.u_movimiento
					INNER JOIN www_users ON tasks_horas.urecurso = www_users.userid
					LEFT JOIN prdproyectos ON tasks_movimientos.u_proyecto = prdproyectos.idproyecto
					LEFT JOIN tasks_categories ON tasks_categories.catcode = tasks_movimientos.catcode
					LEFT JOIN prdstatussimple AS prdstatus ON tasks_movimientos.idstatus = prdstatus.idstatus
								
					WHERE  tasks_horas.fechainicio >= '$fi' and tasks_horas.fechafin <= '$ff'
						and tasks_movimientos.u_proyecto = ".$_GET['u_proyecto']."			
						AND erp = 0  
						$whereconditions
					GROUP BY
							tasks_horas.urecurso,
							www_users.realname,
							www_users.costoHr,
							CASE WHEN tasks_categories.catcode IS NULL THEN
							-1 ELSE tasks_categories.catcode END,
							tasks_categories.description
						ORDER BY
							www_users.realname,tasks_horas.fechainicio,
							CASE WHEN tasks_categories.description IS NULL THEN
							-1 ELSE tasks_categories.description END,
							tasks_categories.description";
			
			$recurso="";				
			$rs = DB_query($sql,$db);
			while ($myregs = DB_fetch_array($rs)){
				$hrs = $myregs['horas'];
				$catcode = $myregs['catcode'];
				if ($recurso != $myregs['recurso']){
					$recurso = $myregs['recurso'];
					$arrrecursovalues[$recurso] = array();
				}
			
				$arrrecursovalues[$recurso][$catcode] = $hrs;					
			}
			/*echo "<pre>";
			print_r($arrrecursovalues);
			exit;*/
			//escribir el resultado en la tabla
			$this->SetFont('helvetica','',7);
			$tothrsrow=0;
			$line=0;
			foreach($arrrecursovalues as $rec=>$arrcat){
					if ($line%2==0)
						$this->SetFillColor(235,235,235);
					else	
						$this->SetFillColor(220,220,220);
						
					$this->cell(35,3,$rec,1,0,'L',1);				
					foreach($arrcateg as $cat){
						$wx=14;
						if ($cat==-1)
							$wx = 8;
							
						if ($arrcat[$cat]=="")
							$this->cell($wx,3,"--",1,0,'C',1);
						else{	
							$this->cell($wx,3,$arrcat[$cat],1,0,'C',1);
							$tothrsrow+=$arrcat[$cat];
							$arrcattot[$cat]+=$arrcat[$cat];
						}
					}
					$this->cell(12,3,$tothrsrow,1,0,'C',1);
					
					$this->Ln();
					$line++;
					$tothrsrow=0;
					
			}	
			$this->SetFont('helvetica','B',7);
			$this->cell(35,3,'TOTALES',1,0,'L',1);								
			if ($line%2==0)
				$this->SetFillColor(235,235,235);
			else	
				$this->SetFillColor(220,220,220);

			$tothrsrow=0;
			foreach($arrcateg as $cat){
				$wx=14;
				if ($cat==-1)
					$wx = 8;
					
				$this->cell($wx,3,$arrcattot[$cat],1,0,'C',1);
				$tothrsrow+=$arrcattot[$cat];
			}
			$this->cell(12,3,$tothrsrow,1,0,'C',1);
			
			//horas asignadas por categoria al proyecto
			$this->Ln(15);
			$tothpre=0;
			$this->cell(35,3,'HRS PRESUPUESTADAS',1,0,'L');
			
			$cond = "and mes between month('$fi') and month('$ff') and anio = year('$fi')";
			if ((int)substr($fi,0,4) < (int)substr($ff,0,4))
				$cond = "and ( (mes between month('$fi') and 12 and anio = year('$fi')) or (mes between 1 and month('$ff') and anio = year('$ff')) )";		
				
			
			foreach($arrcateg as $cat){
				$wx=14;
				if ($cat==-1)
					$wx = 8;
				
				$qry = "Select sum(horas) as horas
						from tasks_projectLimits 
						where u_proyecto=".$_GET['u_proyecto']." 
						$cond
						and catcode = '$cat'";
	
				$rsp = DB_query($qry,$db);
				$rowp = DB_fetch_array($rsp);
				$hrsp = $rowp['horas'];
				if ($hrsp=="")
					$hrsp=0;		
					
				$this->cell($wx,3,$hrsp,1,0,'C');
				$tothpre+=$hrsp;
				if ($hrsp > 0)
					$arrcattot[$cat] = $arrcattot[$cat]/$hrsp;
			}
			$this->cell(12,3,$tothpre,1,0,'C');
			$this->Ln();
			$this->cell(35,3,'%HRS',1,0,'L');
			foreach($arrcateg as $cat){
				$wx=14;
				if ($cat==-1)
					$wx = 8;
					
				$this->cell($wx,3,number_format($arrcattot[$cat]*100,2)."%",1,0,'C');
				$tothrsrow+=$arrcattot[$cat];
			}
			$this->cell(12,3,number_format(($tothrsrow/$tothpre)*100,2)."%",1,0,'C');	
			
			$this->Ln(15);

			$whereconditions="";
			if ($_GET['profile'])		
				$whereconditions.=" and www_users.userid IN (SELECT userid from sec_profilexuser where profileid=".$_GET['profile'].")";

			if ($_GET['recurso'] and $_GET['recurso']!='*')
				$whereconditions.=" and tasks_movimientos.u_user= '".$_GET['recurso']."'";

			//relacion de tareas pendientes
			$sql = "select tasks_categories.catcode,tasks_categories.description as nombre,count(u_movimiento) as tareas ,sum(cargo) as hrsprogr
					from tasks_movimientos
						left join tasks_categories
						ON tasks_movimientos.catcode = tasks_categories.catcode
						INNER JOIN www_users ON tasks_movimientos.u_user = www_users.userid
					where tasks_movimientos.u_proyecto=".$_GET['u_proyecto']."
					and tasks_movimientos.idstatus not in (5,6,8,10)
					$whereconditions
					Group By tasks_categories.catcode
					";
			$rst = DB_query($sql,$db);
			if (DB_num_rows($rst) > 0){
				$posX=70;
				$this->SetX($posX);
				$this->SetFont('helvetica','B',7);
				
				$this->cell(25,3,'Categorias',1,0,'C');
				$this->cell(20,3,'Tareas Pend.',1,0,'C');
				$this->cell(20,3,'Hrs Progr',1,1,'C');
				$tottareas = 0;
				$tothrs = 0;
				$this->SetFont('helvetica','',7);
				while ($rowt = DB_fetch_array($rst)){
					$this->SetX($posX);
					$this->cell(25,3,$rowt['nombre'],1,0,'L');
					$this->cell(20,3,$rowt['tareas'],1,0,'C');
					$this->cell(20,3,$rowt['hrsprogr'],1,1,'C');
					$tottareas+=$rowt['tareas'];
					$tothrs+=$rowt['hrsprogr'];
				}
				$this->SetX($posX);
				$this->SetFont('helvetica','B',7);
				$this->cell(25,3,'TOTALES',1,0,'L');
				$this->cell(20,3,$tottareas,1,0,'C');
				$this->cell(20,3,$tothrs,1,1,'C');
				
			}
			
			$this->OutPut('','I');
			
			
			die();	
		}
		
		function Footer(){
			$this->SetFont('helvetica','B',7);
			$this->SetY(-10);
			$this->cell('',5,'Pagina '.$this->PageNo().'/{nb}',0,0,'R');
		}
	
	}



?>