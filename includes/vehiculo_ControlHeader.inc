<?php
/*
 vehiculo control prueba

*/


/* ELEMENTOS SELECCIONADOS DEL FLUJO NORMAL */
if (isset($_POST['selMovimiento'])) {
    
    //echo "ENTRO";
    for ($i=0;$i<=count($_POST['selMovimiento'])-1; $i++) {
        
        //echo $i."-".$_POST['selMovimiento'][$i]."<br>".$_POST['Oper']."<br>";
        //echo $_POST['ResourceId_GLOBAL']."<br>";
        
        $umovto = $_POST['selMovimiento'][$i];
        
        $sql='Select vehicle_movimientos.*,
                        www_users.realname
                FROM vehicle_movimientos LEFT JOIN www_users ON vehicle_movimientos.u_user = www_users.userid
		WHERE u_movimiento ='.$umovto;
	$ErrMsg = _('The authentication details cannot be recovered because');
	$ResultMov=DB_query($sql,$db,$ErrMsg);
        $myrowMov=DB_fetch_array($ResultMov);
        
        //String con todas las id seleccionadas para volver a marcarlas como
        //seleccionadas una vez terminadas las operaciones con ellas !!!
        $stringFind = $stringFind . '-' . $umovto;
    
            
        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "cambiaMontos")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['concepto']."<br>".$myrowMov['descripcion']."<br>".$myrowMov['cargo']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                        set Cargo = '".$_POST['Cargo_'.$umovto]."',
                            concepto = '".$_POST['Concepto_'.$umovto]."',
                            u_user = '".$_POST['ResourceId2_'.$umovto]."',
                            confirmado = 0";
            
            $sql = $sql . " where u_movimiento = ". $umovto ."";
                        
                        
            $resul = DB_query($sql,$db);
            
            if ($_POST['ResourceId_GLOBAL'] != '*') {
                
                $recanterior = $myrowMov['realname'];
                
                 $sql='Select vehicle_movimientos.*,
                                www_users.realname
                        FROM vehicle_movimientos LEFT JOIN www_users ON vehicle_movimientos.u_user = www_users.userid
                        WHERE u_movimiento ='.$umovto;
                $ErrMsg = _('The authentication details cannot be recovered because');
                $ResultMov=DB_query($sql,$db,$ErrMsg);
                $myrowMov=DB_fetch_array($ResultMov);
                
                $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                                VALUES ('".$umovto."','de:".$recanterior."',
                                        Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','cambio recurso')";
                        
                $result2= DB_query($sql2,$db);
            }

            //echo $sql."<BR>";
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "confMovimiento")){
            
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
        
            $sql = "update vehicle_movimientos
                    set confirmado = 0, fecha_compromiso = fecha
                    where u_movimiento = '". $umovto ."'";
                    
            $resul = DB_query($sql,$db);
        
           
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "inhabilita")){
            
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set activo = 0
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            //echo $sql."<BR>";
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "habilita")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movcomentarios
                    set activo = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "borraMovimiento")){
            
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "DELETE From vehicle_movcomentarios
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "pendMovimiento")){
            
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            $sql = "update vehicle_movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set prioridad = prioridad - 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            $sql = "update vehicle_movimientos
                    set prioridad = prioridad + 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = SUBDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,7)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 WEEK)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
                    
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = ".$_POST['diaDirecto']." 
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = DATE(CONCAT(anio,'-',mes,'-',dia))
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia5")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia5")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "copynextmonth")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','copia de tarea a una nueva',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
          
            $sql = "insert vehicle_movimientos
                    select null,u_proyecto,dia,mes,anio,concepto,descripcion,
                            cargo,civa,cretencion,abono,aiva,aretencion,referencia,prioridad,u_user,
                            u_movimiento_rec,1,UserId,0,0,0,IVA,fecha,u_entidad,u_cajaChica,
                            periodo_dev,erp,fecha,activo,grupo_contable,catcode,idstatus,fecha_compromiso
                    from vehicle_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            $newMovto = $_SESSION['LastInsertId'];
            
            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "copynextweek")){
           $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','copia de tarea a una nueva',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "insert vehicle_movimientos
                    select null,u_proyecto,dia,mes,anio,concepto,descripcion,
                            cargo,civa,cretencion,abono,aiva,aretencion,referencia,prioridad,u_user,
                            u_movimiento_rec,1,UserId,0,0,0,IVA,fecha,u_entidad,u_cajaChica,
                            periodo_dev,erp,fecha,activo,grupo_contable,catcode,idstatus,fecha_compromiso
                    from vehicle_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            $newMovto = $_SESSION['LastInsertId'];
            
            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 WEEK)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
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
    
        $sql='Select vehicle_movimientos.*,
                        www_users.realname,
                        prdstatussimple.nombre as status
                FROM vehicle_movimientos LEFT JOIN www_users ON vehicle_movimientos.u_user = www_users.userid
                            LEFT JOIN prdstatussimple ON prdstatussimple.idstatus=vehicle_movimientos.idstatus
		WHERE u_movimiento ='.$umovto;
        
        
	$ErrMsg = _('The authentication details cannot be recovered because');
	$ResultMov=DB_query($sql,$db,$ErrMsg);
        $myrowMov=DB_fetch_array($ResultMov);
        
        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "setEstatus")){          
            
            $sql = "update vehicle_movimientos
                    set idstatus = ".$_GET['idstatus']."
                    where u_movimiento = '". $umovto ."'";
             echo '<pre>'.$sql;       
            $resul = DB_query($sql,$db);
            
            $estadoAnterior = $myrowMov['status'];
            
            $sql='Select vehicle_movimientos.*,
                            www_users.realname,
                            prdstatussimple.nombre as status
                    FROM vehicle_movimientos LEFT JOIN www_users ON vehicle_movimientos.u_user = www_users.userid
                                LEFT JOIN prdstatussimple ON prdstatussimple.idstatus=vehicle_movimientos.idstatus
                    WHERE u_movimiento ='.$umovto;
            
            
            $ErrMsg = _('The authentication details cannot be recovered because');
            $ResultMov=DB_query($sql,$db,$ErrMsg);
            $myrowMov=DB_fetch_array($ResultMov);
            
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$estadoAnterior."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
        
           
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "confMovimiento")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set confirmado = 0, fecha_compromiso = fecha
                    where u_movimiento = '". $umovto ."'";
                    
            $resul = DB_query($sql,$db);
        
           
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "inhabilita")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set activo = 0
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "habilita")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set activo = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "borraMovimiento")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "DELETE From vehicle_movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "pendMovimiento")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set prioridad = prioridad - 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            
            $sql = "update vehicle_movimientos
                    set prioridad = prioridad + 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);
            $sql = "update vehicle_movimientos
                    set fecha = SUBDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update vehicle_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update vehicle_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update vehicle_movimientos
                    set dia = ".$_POST['diaDirecto']." 
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set fecha = DATE(CONCAT(anio,'-',mes,'-',dia))
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia5")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);


            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia5")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update vehicle_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);


            $sql = "update vehicle_movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 7 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
            $sql2="INSERT INTO vehicle_movcomentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";
                    
            $result2= DB_query($sql2,$db);

            $sql = "update vehicle_movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 7 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update vehicle_movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }
    
}

?>