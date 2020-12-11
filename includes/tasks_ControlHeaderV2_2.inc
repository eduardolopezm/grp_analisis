<?php
/*
 * desarrollo29/NOV/2012 - Actualiza proceso ahora funciona bien
    SP 21/AGOSTO/2012 - Enviar mail al mail capturado en el campo 'mailEnvio' del proyecto si la tarea se marca como "Envio a Cliente".

   desarrollo- 23/FEBRERO/2012 - Limpie cuando actualizas comentarios que elimine comillas o doble comilla y tambien que actualice descripcion
   desarrollo- 22/FEBRERO/2012 - Agregue campo de ultimo_inicio para calcular tiempo efectivo en una tarea
    
   desarrollo- 17/NOVIEMBRE/2011 - Agregue campo de operaciones a tabla de comentarios.
				Mejore el formato y asigne algunos colores para hacer mas
				relevante la informacion.
				
   desarrollo- 16/NOVIEMBRE/2011 - Agregue en inserts de copias los campons de fecha compromiso y idstatus
   desarrollo- 11/JULIO/2011  - Agregue funciones de siguiente y anterior semana para selecciones individuales
    asi como modificacion de referencia y conceptos. prueba priueba 2

*/

/* ELEMENTOS SELECCIONADOS DEL FLUJO NORMAL */
if (isset($_POST['selMovimiento'])) {
    
	// Enviar correo de notificación al usuario al cual se asignó la tarea
	if(empty($_POST['selMovimiento']) == FALSE) {
		if($_POST['ResourceId_GLOBAL'] != '*' && $_POST['ResourceId_GLOBAL'] != $_SESSION['UserID']) {
			$rs = DB_query("SELECT realname FROM www_users WHERE userid = '" . $_SESSION['UserID'] . "'", $db);
			$usuario = "";
			if($row = DB_fetch_array($rs)) {
				$usuario = ucwords(strtolower($row['realname']));
			}
			
			$rs = DB_query("SELECT email FROM www_users WHERE userid = '" . $_POST['ResourceId_GLOBAL'] . "'", $db);
			if($row = DB_fetch_array($rs)) {
				$email = $row['email'];
				/*if(IsEmailAddress($email)) {
					include('include/mail.php');
					$message = "$usuario ha asignado las siguientes tareas a tu perfil: " . implode(',', $_POST['selMovimiento']);
					$mail = new Mail();
					$mail->setTo($email);
					$mail->setSender("Sistema de Control de Tareas tecnoaplicada");
					$mail->setFrom("soporte@tecnoaplicada.com");
					$mail->setHtml($message);
					$mail->setSubject("Cambio de Recurso de Tarea");
					$mail->send();
					
				}*/
				/*include_once('phpmailer/class.phpmailer.php');
				$mailObj = new PHPMailer();
								
				$from = "soporte@tecnoaplicada.com";
				$senderName = "Sistema de Control de Tareas tecnoaplicada";
				$asunto = "Cambio de Recurso de Tarea";
				$message = "$usuario ha asignado las siguientes tareas a tu perfil: " . implode(',', $_POST['selMovimiento']);
				$mailObj->From = $from;
				$mailObj->FromName = $senderName;					
				$mailObj->Subject = $asunto;					
				$mailObj->MsgHTML($message);				

				$to = array_unique(explode(';', $email));
						
				foreach($to as $mail) {
					if(IsEmailAddress($mail)) {
						$mailObj->AddAddress($mail,''); 
					}
				}

				$mailObj->Send();
				*/
			}
		}
	}
	
    //echo "ENTRO";
    for ($i=0;$i<=count($_POST['selMovimiento'])-1; $i++) {
        
        $umovto = $_POST['selMovimiento'][$i];
        $sql='Select tasks_movimientos.*,
                        www_users.realname,
			prdstatussimple.nombre as statusname
                FROM tasks_movimientos LEFT JOIN www_users ON tasks_movimientos.u_user = www_users.userid
					LEFT JOIN prdstatussimple ON tasks_movimientos.idstatus = prdstatussimple.idstatus
		WHERE u_movimiento ='.$umovto;
	$ErrMsg = _('The authentication details cannot be recovered because');
	$ResultMov=DB_query($sql,$db,$ErrMsg);
        $myrowMov=DB_fetch_array($ResultMov);


		$email="";
		$asunto="";
		$mensaje="";
		$concepto = $myrowMov['concepto'];
		
		
		
		//enviar mail segun configuracion para envio de email del estatus para roles
		$qry="Select * FROM tasks_config_mails	WHERE idstatus = '".$_POST['EstatusId_GLOBAL']."'";
		$rsconf = DB_query($qry,$db);
		$email="";
		while ($myrow = DB_fetch_array($rsconf)){
			$idrol = $myrow['idrol'];
			//verificar a que usuario se le envia mail segun rol seleccionado para ese estatus y proyecto al que pertenece la tarea
			$sql = "Select www_users.email 
					from sec_ROLxuser 
					inner join www_users
						ON sec_ROLxuser.userid = www_users.userid
						and www_users.email is not null 
						and www_users.email<>''
					inner join sec_proyectoxuser
						ON sec_ROLxuser.userid = sec_proyectoxuser.userid
						and sec_proyectoxuser.idproyecto='".$myrowMov['u_proyecto']."'	
					where sec_ROLxuser.profileid='$idrol'
					";
			$rsemail = DB_query($rsemail,$db);
			while ($rowemail = DB_fetch_array($rsemail)){
				$email.=$rowemail['email'].";";
			}		
		
		}
			
		//enviar mail segun configuracion para envio de email del estatus para columnas
		$qry="Select * FROM tasks_config_mails_cols	WHERE idstatus = '".$_POST['EstatusId_GLOBAL']."'";
		$rsconf = DB_query($qry,$db);
		if ($myrow = DB_fetch_array($rsconf)){

			if ($myrow['projectmanager']==1){
				$sql = "Select www_users.email
						FROM prdproyectos
						left join www_users
							ON prdproyectos.idproyecto = www_users.userid 
						WHERE prdproyectos.idproyecto = '".$myrowMov['u_proyecto']."'
						";
				$rspm = DB_query($sql,$db);
				if (DB_num_rows($rspm) > 0){
					$regpm = DB_fetch_array($rspm);
					if ($regpm['email']!="")
						$email.=$regpm['email'].";";
									
				}		
			}
			
			if ($myrow['cliente']==1){
				$sql = "Select emailcontacto
						FROM prdproyectos
						WHERE idproyecto = '".$myrowMov['u_proyecto']."'
						";
				$rsclte = DB_query($sql,$db);
				if (DB_num_rows($rsclte) > 0){
					$regclte = DB_fetch_array($rsclte);
					if ($regclte['emailcontacto']!="")
						$email.=$regclte['emailcontacto'].";";
									
				}		
			
			}
			
			if ($myrow['usuarioalta']==1){
				$sql = "Select www_users.email
						FROM tasks_movimientos
						left join www_users
							ON tasks_movimientos.UserId = www_users.userid 
						WHERE tasks_movimientos.u_movimiento = '".$umovto."'
						";
		
				$rsalta = DB_query($sql,$db);
				if (DB_num_rows($rsalta) > 0){
					$regalta = DB_fetch_array($rsalta);
					if ($regalta['email']!="")
						$email.=$regalta['email'].";";
									
				}		
			
			}
			
			if ($myrow['usuarioasignado']==1){

				$sql = "Select www_users.email
						FROM tasks_movimientos
						left join www_users
							ON tasks_movimientos.u_user = www_users.userid 
						WHERE tasks_movimientos.u_movimiento = '".$umovto."'
						";
				if ($_POST['ResourceId_GLOBAL']!="*")
					$sql = "SELECT email FROM www_users WHERE userid = '".$_POST['ResourceId_GLOBAL']."'";
				
				$rsasig = DB_query($sql,$db);
				if (DB_num_rows($rsasig) > 0){
					$regasig = DB_fetch_array($rsasig);
					if ($regasig['email']!="")
						$email.=$regasig['email'].";";
									
				}		
			
			}

		}
		
		//echo "<pre>Emails: ".$email;
		
		if ($email != ""){
			$email = trim($email,";");
			
			$sql = "Select nombre
						FROM prdproyectos
						WHERE idproyecto = '".$myrowMov['u_proyecto']."'
						";
			$rsprd = DB_query($sql,$db);
			$rowprd = DB_fetch_array($rsprd);
			$proy = $rowprd['nombre'];
			$mensaje = "Proyecto: ".strtoupper($proy)."
						
						";
						
			//texto del mensaje sale del layout para este estatus
			$qry = "Select email_layout FROM prdstatussimple
					WHERE idstatus = '".$_POST['EstatusId_GLOBAL']."'";
			$rslayout = DB_query($qry,$db);
			$row = DB_fetch_array($rslayout);
			$mensaje.= $row['email_layout'];

			//buscar conceptos del mensaje para sustituirlos por su valor
			$qry = "Select fieldname,tablename,keyfield
					FROM tasks_statusxconcept
						INNER JOIN tasks_concept_mails
						ON tasks_statusxconcept.idconcept = tasks_concept_mails.idconcept
					WHERE idstatus= '".$_POST['EstatusId_GLOBAL']."'";
			$rsc = DB_query($qry,$db);
			while ($regc = DB_fetch_array($rsc)){
				$field = $regc['fieldname'];
				$table = $regc['tablename'];
				$keyfield = $regc['keyfield'];
				
				$sql = "Select $field as concepto FROM $table WHERE $keyfield = '$umovto'";
				if ($table == "tasks_comentarios")
					$sql = "Select $field as concepto FROM $table WHERE $keyfield = '$umovto' Order by fecha desc limit 1";

				if ($table == "tasks_horas")
					$sql = "Select $field as concepto FROM $table WHERE $keyfield = '$umovto' Order by fechafin desc limit 1";
				
				
				$r = DB_query($sql,$db);
				$rw = DB_fetch_array($r);
				$valor = $rw['concepto'];
				
				$mensaje = str_replace("%".$field."%",$valor,$mensaje);
			}
			
			$asunto = "Notificacion de estatus de tarea";
			$mensaje = str_replace("\n", "<br/>", $mensaje);

			//echo "<pre>Msg $mensaje";
			include_once('phpmailer/class.phpmailer.php');
			$mailObj = new PHPMailer();
							
			$from = "soporte@tecnoaplicada.com";
			$senderName = "Sistema de Control de Tareas tecnoaplicada";

			$mailObj->From = $from;
			$mailObj->FromName = $senderName;					
			$mailObj->Subject = $asunto;					
			$mailObj->MsgHTML($mensaje);				

			$to = array_unique(explode(';', $email));
					
			foreach($to as $mail) {
				if(IsEmailAddress($mail)) {
					$mailObj->AddAddress($mail,''); 
				}
			}
			$mailObj->Send();

		}
		
        //String con todas las id seleccionadas para volver a marcarlas como
        //seleccionadas una vez terminadas las operaciones con ellas !!!
        $stringFind = $stringFind . '-' . $umovto;
            
        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "cambiaMontos")){
	    
	    
	    if (isset($_POST['Concepto_'.$umovto])) {
		    $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".CharClean(htmlspecialchars_decode($_POST['Concepto_'.$umovto],ENT_NOQUOTES))."<br>".CharClean(htmlspecialchars_decode($_POST['Descripcion_'.$umovto],ENT_NOQUOTES))."<br>".$_POST['Cargo_'.$umovto]."',
                            Now(),0,'".$_POST['idestatus_'.$umovto]."','".$_POST['idrecurso_'.$umovto]."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
	    } else {
		    $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".CharClean(htmlspecialchars_decode($myrowMov['concepto'],ENT_NOQUOTES))."<br>".CharClean(htmlspecialchars_decode($myrowMov['descripcion'],ENT_NOQUOTES))."<br>".$myrowMov['cargo']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
	    }
	    
            
                    
            $result2= DB_query($sql2,$db);
	    
	    
	    if (isset($_POST['comentarioGlobal']) AND strlen($_POST['comentarioGlobal']) > 0) {
		// INSERTAR COMENTARIO GENERAL
		$sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
			VALUES ('".$umovto."','".CharClean(htmlspecialchars_decode($_POST['comentarioGlobal']))."',
				Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','comentario')";
			
		$result2= DB_query($sql2,$db);
	    }
	   // echo $_POST['ResourceId_GLOBAL'];
	    
	    if (isset($_POST['comentarioAdd_'.$umovto]) AND strlen($_POST['comentarioAdd_'.$umovto]) > 0) {
		// INSERTAR COMENTARIO GENERAL*
		$sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
			VALUES ('".$umovto."','".CharClean(htmlspecialchars_decode($_POST['comentarioAdd_'.$umovto]))."',
				Now(),0,'".$_POST['idestatus_'.$umovto]."','".$_POST['idrecurso_'.$umovto]."','".$_SESSION['UserID']."','comentario')";
			
		$result2= DB_query($sql2,$db);
	    }
            
	    if (isset($_POST['Concepto_'.$umovto]) AND strlen($_POST['Concepto_'.$umovto]) > 0 ) {
		//ACTUALIZA PARA CUANDO ESTAN ABIERTOS LOS CAMBIOS !!!!
		if ($_POST['prioridad_'.$umovto] == ""){
			$sql = "update tasks_movimientos
                        set Cargo = '".str_replace(",", "",$_POST['Cargo_'.$umovto])."',
			    dia = '".str_replace(",", "",$_POST['Dia_'.$umovto])."',
			    mes = '".str_replace(",", "",$_POST['Mes_'.$umovto])."',
			    anio = '".str_replace(",", "",$_POST['Anio_'.$umovto])."',
			 
                            concepto = '".str_replace(",", "",CharClean(htmlspecialchars_decode($_POST['Concepto_'.$umovto],ENT_NOQUOTES)))."',
			    descripcion = '".str_replace(",", "",CharClean(htmlspecialchars_decode($_POST['Descripcion_'.$umovto],ENT_NOQUOTES)))."',
                            catcode = '".$_POST['tipoMovimiento_'.$umovto]."',
			 
			    u_user = '".$_POST['idrecurso_'.$umovto]."',
			    idstatus = '".$_POST['idestatus_'.$umovto]."',
			    u_casouso = '".$_POST['idcasouso_modifica_'.$umovto]."',
			    idproceso = '".$_POST['idproceso_modifica_'.$umovto]."',
			 
			    hora = '".$_POST['setHoraIni_'.$umovto]."',
			    IdObjetivo = '".$_POST['IdObjetivos_'.$umovto]."',
			    modcode = '".$_POST['tipoModulo_'.$umovto]."'";
		} else {
				$sql = "update tasks_movimientos
                        set Cargo = '".str_replace(",", "",$_POST['Cargo_'.$umovto])."',
			    		dia = '".str_replace(",", "",$_POST['Dia_'.$umovto])."',
			    		mes = '".str_replace(",", "",$_POST['Mes_'.$umovto])."',
			    		anio = '".str_replace(",", "",$_POST['Anio_'.$umovto])."',
			    
                            concepto = '".str_replace(",", "",CharClean(htmlspecialchars_decode($_POST['Concepto_'.$umovto],ENT_NOQUOTES)))."',
			    		descripcion = '".str_replace(",", "",CharClean(htmlspecialchars_decode($_POST['Descripcion_'.$umovto],ENT_NOQUOTES)))."',
                            catcode = '".$_POST['tipoMovimiento_'.$umovto]."',
			    		prioridad = '".$_POST['prioridad_'.$umovto]."',
			    
			   		 u_user = '".$_POST['idrecurso_'.$umovto]."',
			    	idstatus = '".$_POST['idestatus_'.$umovto]."',
			    	u_casouso = '".$_POST['idcasouso_modifica_'.$umovto]."',
			    	idproceso = '".$_POST['idproceso_modifica_'.$umovto]."',
			    
			    	hora = '".$_POST['setHoraIni_'.$umovto]."',
			    	IdObjetivo = '".$_POST['IdObjetivos_'.$umovto]."',
			    	modcode = '".$_POST['tipoModulo_'.$umovto]."'";
		}
		$sql = $sql . " where u_movimiento = ". $umovto .""; 
		//if($_SESSION['UserID']=='admin'){echo '<pre>sql1:'.$sql;}
		$resul = DB_query($sql,$db);
		
	    } else {
		if ($_POST['ResourceId_GLOBAL'] != '*') {
		        $sql = "update tasks_movimientos set u_user = '".$_POST['ResourceId_GLOBAL']."'";
			$sql = $sql . ", anio = YEAR(NOW())";
			$sql = $sql . ", mes = MONTH(NOW())";
			$sql = $sql . ", dia = DAY(NOW())";
			$sql = $sql . ", hora = '00:00:00'";
			
			if ($_POST['EstatusId_GLOBAL'] != '*') {
			    $sql = $sql . ", idstatus = '".$_POST['EstatusId_GLOBAL']."'";                
			}
			if ($_POST['prioridad_'.$umovto] == ""){
			} else {
				$sql = $sql . ", prioridad = '".$_POST['prioridad_'.$umovto]."'";
			}
			
			$sql = $sql . " where u_movimiento = ". $umovto ."";
			//echo $sql;
			//echo '<pre>sql2:'.$sql;
                        $resul = DB_query($sql,$db);
		}
		
		if ($_POST['idproceso_modifica'] != '*') {
		        $sql = "update tasks_movimientos set idproceso = '".$_POST['idproceso_modifica']."'";
			$sql = $sql . " where u_movimiento = ". $umovto ."";
                        $resul = DB_query($sql,$db);

		}
		
		if ($_POST['EstatusId_GLOBAL'] != '*') {
			    $sql = "update tasks_movimientos set idstatus = '".$_POST['EstatusId_GLOBAL']."'";
			    if($_POST['prioridad_'.$umovto] == "") {
			    }else {
			    	$sql = $sql . ", prioridad = '".$_POST['prioridad_'.$umovto]."'";
			    }
			    $sql = $sql . " where u_movimiento = ". $umovto ."";
			    //echo '<pre>sql3:'.$sql;
			    $resul = DB_query($sql,$db);
		}
		if($_POST['prioridad_'.$umovto] == "") {
			
			if (isset($_POST['idcasouso_global']) AND $_POST['idcasouso_global'] != '-1') {
				$sql = 'update tasks_movimientos set';
				$sql = $sql . " u_casouso = '".$_POST['idcasouso_global']."'";
				$sql = $sql . " where u_movimiento = ". $umovto ."";
				//echo '<pre>sql4:'.$sql;
				$resul = DB_query($sql,$db);
			}
		}else {
			$sql = "update tasks_movimientos set prioridad = '".$_POST['prioridad_'.$umovto]."'";
		
			if (isset($_POST['idcasouso_global']) AND $_POST['idcasouso_global'] != '-1') {
				$sql = $sql . ", u_casouso = '".$_POST['idcasouso_global']."'";
			}
		
			$sql = $sql . " where u_movimiento = ". $umovto ."";
			//echo '<pre>sql4:'.$sql;
			$resul = DB_query($sql,$db);
		
	    	}
	    }
	    //Actualiza fecha
	    $sql = "update tasks_movimientos
                    set fecha = DATE(CONCAT(anio,'-',mes,'-',dia))
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            if ($_POST['ResourceId_GLOBAL'] != '*') {
                
                $recanterior = $myrowMov['realname'];
                
                 $sql='Select tasks_movimientos.*,
                                www_users.realname
                        FROM tasks_movimientos LEFT JOIN www_users ON tasks_movimientos.u_user = www_users.userid
                        WHERE u_movimiento ='.$umovto;
                $ErrMsg = _('The authentication details cannot be recovered because');
                $ResultMov=DB_query($sql,$db,$ErrMsg);
                $myrowMov=DB_fetch_array($ResultMov);
                
                $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                                VALUES ('".$umovto."','de:".$recanterior."',
                                        Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','cambio recurso')";
                        
                $result2= DB_query($sql2,$db);
            }
	    
	    if ($_POST['EstatusId_GLOBAL'] != '*') {
                
                $recanterior = $myrowMov['statusname'];
                
                 $sql='Select tasks_movimientos.*,
				www_users.realname,
				prdstatussimple.nombre as statusname
			FROM tasks_movimientos LEFT JOIN www_users ON tasks_movimientos.u_user = www_users.userid
						LEFT JOIN prdstatussimple ON tasks_movimientos.idstatus = prdstatussimple.idstatus
			WHERE u_movimiento ='.$umovto;
                $ErrMsg = _('The authentication details cannot be recovered because');
                $ResultMov=DB_query($sql,$db,$ErrMsg);
                $myrowMov=DB_fetch_array($ResultMov);
                
                $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                                VALUES ('".$umovto."','de:".$recanterior."',
                                        Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','cambio estatus')";
                        
                $result2= DB_query($sql2,$db);
            }

            //echo $sql."<BR><BR>";
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "confMovimiento")){
            
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
        
            $sql = "update tasks_movimientos
                    set confirmado = 0, fecha_compromiso = fecha
                    where u_movimiento = '". $umovto ."'";
                    
            $resul = DB_query($sql,$db);
        
           
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "inhabilita")){
            
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set activo = 0
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            //echo $sql."<BR>";
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "habilita")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set activo = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "borraMovimiento")){
            
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "DELETE From tasks_horas
                    where idtarea = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
	    
	    $sql = "DELETE From tasks_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
	    
	    
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "pendMovimiento")){
            
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
           
            $sql = "update tasks_movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set prioridad = prioridad - 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            $sql = "update tasks_movimientos
                    set prioridad = prioridad + 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = SUBDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,7)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 WEEK)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
                    
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set dia = ".$_POST['diaDirecto']." 
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = DATE(CONCAT(anio,'-',mes,'-',dia))
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaHOY")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set dia = DAY(Now()), mes = MONTH(Now()), anio = YEAR(Now())
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = DATE(CONCAT(anio,'-',mes,'-',dia))
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
	    
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaMANANA")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);


            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(Now(),INTERVAL 1 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
	    
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia5")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia5")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "copynextmonth")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','copia de tarea a una nueva',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
          
            $sql = "insert tasks_movimientos
                    select null,u_proyecto,dia,mes,anio,concepto,descripcion,
                            cargo,civa,cretencion,abono,aiva,aretencion,referencia,prioridad,u_user,
                            u_movimiento_rec,1,UserId,0,0,0,IVA,fecha,u_entidad,u_cajaChica,
                            periodo_dev,erp,fecha,activo,grupo_contable,catcode,idstatus,fecha_compromiso, modcode
                    from tasks_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            $newMovto = $_SESSION['LastInsertId'];
            
            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "copynextweek")){
           $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','copia de tarea a una nueva',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "insert tasks_movimientos
                    select null,u_proyecto,dia,mes,anio,concepto,descripcion,
                            cargo,civa,cretencion,abono,aiva,aretencion,referencia,prioridad,u_user,
                            u_movimiento_rec,1,UserId,0,0,0,IVA,fecha,u_entidad,u_cajaChica,
                            periodo_dev,erp,fecha,activo,grupo_contable,catcode,idstatus,fecha_compromiso, modcode
                    from tasks_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            $newMovto = $_SESSION['LastInsertId'];
            
            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 WEEK)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }
    }
    
}/* ELEMENTOS SELECCIONADOS DEL FLUJO DE CXC */

 elseif (isset($_GET['u_movimiento'])) {
    
    /* TODAS LAS OPERACIONES INDIVIDUALES */
    
        $umovto = $_GET['u_movimiento'];
    
        $sql='Select tasks_movimientos.*,
                        www_users.realname,
                        prdstatussimple.nombre as status
                FROM tasks_movimientos LEFT JOIN www_users ON tasks_movimientos.u_user = www_users.userid
                            LEFT JOIN prdstatussimple ON prdstatussimple.idstatus=tasks_movimientos.idstatus
		WHERE u_movimiento ='.$umovto;
        
	$ErrMsg = _('The authentication details cannot be recovered because');
	$ResultMov=DB_query($sql,$db,$ErrMsg);
        $myrowMov=DB_fetch_array($ResultMov);
        
        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "setEstatus")){
            
            $estadoAnterior = $myrowMov['idstatus'];
		    $estadoAnteriorNombre = $myrowMov['status'];
            
            $sql = "update tasks_movimientos
                    set idstatus = ".$_GET['idstatus']."
                    where u_movimiento = '". $umovto ."'";
                    
            $resul = DB_query($sql,$db);

            
            $sql='Select tasks_movimientos.*,
                            www_users.realname,
                            prdstatussimple.nombre as status
                    FROM tasks_movimientos LEFT JOIN www_users ON tasks_movimientos.u_user = www_users.userid
                                LEFT JOIN prdstatussimple ON prdstatussimple.idstatus=tasks_movimientos.idstatus
                    WHERE u_movimiento ='.$umovto;
            
            
            $ErrMsg = _('The authentication details cannot be recovered because');
            $ResultMov=DB_query($sql,$db,$ErrMsg);
            $myrowMov=DB_fetch_array($ResultMov);
            
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$estadoAnteriorNombre."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            if ($_GET['idstatus'] == 3 OR $_GET['idstatus'] == 7) {
                /* Quiere decir que se inicio el proceso de esta tarea, hay que fijar la ultima hora de inicio... */
                
                $sql = "update tasks_movimientos
                        set ultimo_inicio = NOW()
                        where u_movimiento = '". $umovto ."'";
                
                
                $resul = DB_query($sql,$db);    
            }
            
            if ($estadoAnterior == 3 OR $estadoAnterior == 7) {
                /* Quiere decir que estaba en proceso la tarea y que ahora no asi que podemos contabilizar tiempo efectivo de trabajo... */
                
                $sql = "update tasks_movimientos
                        set estimado = CASE WHEN (IFNULL(estimado,0) + TIME_TO_SEC(TIMEDIFF(NOW(),ultimo_inicio))) > 28800 THEN 28800 ELSE (IFNULL(estimado,0) + TIME_TO_SEC(TIMEDIFF(NOW(),ultimo_inicio))) END
                        where u_movimiento = '". $umovto ."'";
                    
                
                $resul = DB_query($sql,$db);    
            }
            
            
        
           
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "confMovimiento")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set confirmado = 0, fecha_compromiso = fecha
                    where u_movimiento = '". $umovto ."'";
                    
            $resul = DB_query($sql,$db);
        
           
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "inhabilita")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set activo = 0
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "habilita")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set activo = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "borraMovimiento")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
	    $sql = "DELETE From tasks_horas
                    where idtarea = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
	    
            $sql = "DELETE From tasks_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "pendMovimiento")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set prioridad = prioridad - 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update tasks_movimientos
                    set prioridad = prioridad + 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            $sql = "update tasks_movimientos
                    set fecha = SUBDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set dia = ".$_POST['diaDirecto']." 
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set fecha = DATE(CONCAT(anio,'-',mes,'-',dia))
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
	    
	
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia5")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);


            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
	    
	
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia5")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);


            $sql = "update tasks_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 7 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 7 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update tasks_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }
    
}

?>