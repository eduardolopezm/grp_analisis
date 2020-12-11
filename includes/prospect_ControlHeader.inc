<?php
/*
   desarrollo- 17/NOVIEMBRE/2011 - Agregue campo de operaciones a tabla de comentarios.
				Mejore el formato y asigne algunos colores para hacer mas
				relevante la informacion.
				
   desarrollo- 16/NOVIEMBRE/2011 - Agregue en inserts de copias los campons de fecha compromiso y idstatus
   desarrollo- 11/JULIO/2011  - Agregue funciones de siguiente y anterior semana para selecciones individuales
    asi como modificacion de referencia y conceptos.

*/

/* ELEMENTOS SELECCIONADOS DEL FLUJO NORMAL */
if (isset($_POST['selMovimiento'])) {
    
    //echo "ENTRO";
    for ($i=0;$i<=count($_POST['selMovimiento'])-1; $i++) {
        
        //echo $i."-".$_POST['selMovimiento'][$i]."<br>".$_POST['Oper']."<br>";
        //echo $_POST['ResourceId_GLOBAL']."<br>";
        
        $umovto = $_POST['selMovimiento'][$i];
        
        $sql='Select prospect_movimientos.*,
                        www_users.realname
                FROM prospect_movimientos LEFT JOIN www_users ON prospect_movimientos.u_user = www_users.userid
		WHERE u_movimiento ='.$umovto;
        //echo $sql;
	$ErrMsg = _('The authentication details cannot be recovered because');
	$ResultMov=DB_query($sql,$db,$ErrMsg);
        $myrowMov=DB_fetch_array($ResultMov);
        
        //String con todas las id seleccionadas para volver a marcarlas como
        //seleccionadas una vez terminadas las operaciones con ellas !!!
        $stringFind = $stringFind . '-' . $umovto;
    
            
        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "cambiaMontos")){
        	if($_SESSION['UserID'] == "aenriquez"){
        		echo '<br>entro<br>';
        	}
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['concepto']."<br>".$myrowMov['descripcion']."<br>".$myrowMov['cargo']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            if(isset($_POST['Concepto_'.$umovto])) {
	            $sql = "update prospect_movimientos
	                        set Cargo = '".str_replace(",", "",$_POST['Cargo_'.$umovto])."',
	                            concepto = '".str_replace(",", "",$_POST['Concepto_'.$umovto])."',
	                            descripcion = '".str_replace(",", "",$_POST['Descripcion_'.$umovto])."',
	                            catcode = '".$_POST['tipoMovimiento_'.$umovto]."'";
	            $sql = $sql . " where u_movimiento = ". $umovto ."";
	           
	                        
	            $resul = DB_query($sql,$db);
            }
            
            
            if ($_POST['ResourceId_GLOBAL'] != '*') {
            	
            	$sql = "update prospect_movimientos
	                        set u_user = '".$_POST['ResourceId_GLOBAL']."'
	                     where u_movimiento = '".$umovto."'";
            	$resul = DB_query($sql, $db);
            }
             
            
            if ($_POST['EstatusId_GLOBAL']!="*"){
            	
            	$sql = "update prospect_movimientos
                    set idstatus = '".$_POST['EstatusId_GLOBAL']."'
                    where u_movimiento = '". $umovto ."'";
            	
            	$resul = DB_query($sql,$db);
            }
            
            ////
            
            if ($_POST['ResourceId_GLOBAL'] != '*') {
                
                $recanterior = $myrowMov['realname'];
                
                 $sql='Select prospect_movimientos.*,
                                www_users.realname
                        FROM prospect_movimientos LEFT JOIN www_users ON prospect_movimientos.u_user = www_users.userid
                        WHERE u_movimiento ='.$umovto;
                $ErrMsg = _('The authentication details cannot be recovered because');
                $ResultMov=DB_query($sql,$db,$ErrMsg);
                $myrowMov=DB_fetch_array($ResultMov);
                
                $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                                VALUES ('".$umovto."','de:".$recanterior."',
                                        Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','cambio recurso')";
                        
                $result2= DB_query($sql2,$db);
            }

            //echo $sql."<BR>";
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "confMovimiento")){
            
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
        
            $sql = "update prospect_movimientos
                    set confirmado = 0, fecha_compromiso = fecha
                    where u_movimiento = '". $umovto ."'";
                    
            $resul = DB_query($sql,$db);
        
           
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "inhabilita")){
            
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set activo = 0
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            //echo $sql."<BR>";
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "habilita")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set activo = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "borraMovimiento")){
            
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "DELETE From prospect_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "pendMovimiento")){
            
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set prioridad = prioridad - 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            $sql = "update prospect_movimientos
                    set prioridad = prioridad + 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = SUBDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,7)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 WEEK)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
                    
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set dia = ".$_POST['diaDirecto']." 
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = DATE(CONCAT(anio,'-',mes,'-',dia))
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia5")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia5")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "copynextmonth")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','copia de tarea a una nueva',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
          
            $sql = "insert prospect_movimientos
                    select null,u_proyecto,dia,mes,anio,concepto,descripcion,
                            cargo,civa,cretencion,abono,aiva,aretencion,referencia,prioridad,u_user,
                            u_movimiento_rec,1,UserId,0,0,0,IVA,fecha,u_entidad,u_cajaChica,
                            periodo_dev,erp,fecha,activo,grupo_contable,catcode,idstatus,fecha_compromiso
                    from prospect_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            $newMovto = $_SESSION['LastInsertId'];
            
            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "copynextweek")){
           $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','copia de tarea a una nueva',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "insert prospect_movimientos
                    select null,u_proyecto,dia,mes,anio,concepto,descripcion,
                            cargo,civa,cretencion,abono,aiva,aretencion,referencia,prioridad,u_user,
                            u_movimiento_rec,1,UserId,0,0,0,IVA,fecha,u_entidad,u_cajaChica,
                            periodo_dev,erp,fecha,activo,grupo_contable,catcode,idstatus,fecha_compromiso
                    from prospect_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            $newMovto = $_SESSION['LastInsertId'];
            
            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 WEEK)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
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
    
        $sql='Select prospect_movimientos.*,
                        www_users.realname,
                        prdstatussimple.nombre as status
                FROM prospect_movimientos LEFT JOIN www_users ON prospect_movimientos.u_user = www_users.userid
                            LEFT JOIN prdstatussimple ON prdstatussimple.idstatus=prospect_movimientos.idstatus
		WHERE u_movimiento ='.$umovto;
        
        
	$ErrMsg = _('The authentication details cannot be recovered because');
	$ResultMov=DB_query($sql,$db,$ErrMsg);
        $myrowMov=DB_fetch_array($ResultMov);
        
        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "setEstatus")){
            
            
            
            $sql = "update prospect_movimientos
                    set idstatus = ".$_GET['idstatus']."
                    where u_movimiento = '". $umovto ."'";
                    
            $resul = DB_query($sql,$db);
            
            $estadoAnterior = $myrowMov['status'];
            
            $sql='Select prospect_movimientos.*,
                            www_users.realname,
                            prdstatussimple.nombre as status
                    FROM prospect_movimientos LEFT JOIN www_users ON prospect_movimientos.u_user = www_users.userid
                                LEFT JOIN prdstatussimple ON prdstatussimple.idstatus=prospect_movimientos.idstatus
                    WHERE u_movimiento ='.$umovto;
            
            
            $ErrMsg = _('The authentication details cannot be recovered because');
            $ResultMov=DB_query($sql,$db,$ErrMsg);
            $myrowMov=DB_fetch_array($ResultMov);
            
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$estadoAnterior."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
        
           
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "confMovimiento")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set confirmado = 0, fecha_compromiso = fecha
                    where u_movimiento = '". $umovto ."'";
                    
            $resul = DB_query($sql,$db);
        
           
        }
        elseif((isset($_GET['Oper'])) and ($_GET['Oper'] == "confMovimiento")){
        	$sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
        
        	$result2= DB_query($sql2,$db);
        
        	$sql = "update prospect_movimientos
                    set confirmado = 0, fecha_compromiso = fecha
                    where u_movimiento = '". $umovto ."'";
        
        	$resul = DB_query($sql,$db);
        
        	 
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "inhabilita")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set activo = 0
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "habilita")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set activo = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "borraMovimiento")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "DELETE From prospect_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "pendMovimiento")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            }elseif((isset($_GET['Oper'])) and ($_GET['Oper'] == "pendMovimiento")){
            	$sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_GET['Oper']."')";
            
            	$result2= DB_query($sql2,$db);
            
            	$sql = "update prospect_movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";
            
            	$result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set prioridad = prioridad - 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update prospect_movimientos
                    set prioridad = prioridad + 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            }elseif((isset($_GET['Oper'])) and ($_GET['Oper'] == "anteriorPrioridad")){
            	$sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
            
            	$result2= DB_query($sql2,$db);
            
            	$sql = "update prospect_movimientos
                    set prioridad = prioridad - 1
                    where u_movimiento = '". $umovto ."'";
            //
            	$result = DB_query($sql,$db);
            
            }elseif((isset($_GET['Oper'])) and ($_GET['Oper'] == "siguientePrioridad")){
            	$sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
            
            	$result2= DB_query($sql2,$db);
            
            	$sql = "update prospect_movimientos
                    set prioridad = prioridad + 1
                    where u_movimiento = '". $umovto ."'";
            
            	$result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            $sql = "update prospect_movimientos
                    set fecha = SUBDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update prospect_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update prospect_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update prospect_movimientos
                    set dia = ".$_POST['diaDirecto']." 
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set fecha = DATE(CONCAT(anio,'-',mes,'-',dia))
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia5")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);


            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia5")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update prospect_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);


            $sql = "update prospect_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 7 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
            $sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update prospect_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 7 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update prospect_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }
    
}

?>