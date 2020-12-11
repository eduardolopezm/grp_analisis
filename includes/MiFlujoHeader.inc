<?php
/*
   desarrollo- 11/JULIO/2011  - Agregue funciones de siguiente y anterior semana para selecciones individuales asi como modificacion de referencia y conceptos.
*/

/* ELEMENTOS SELECCIONADOS DEL FLUJO NORMAL */
if (isset($_POST['selMovimiento'])) {
    
    //echo "ENTRO";
    for ($i=0;$i<=count($_POST['selMovimiento'])-1; $i++) {
        
        //echo $i."-".$_POST['selMovimiento'][$i]."<br>".$_POST['Oper']."<br>";
        $umovto = $_POST['selMovimiento'][$i];
        
        //String con todas las id seleccionadas para volver a marcarlas como
        //seleccionadas una vez terminadas las operaciones con ellas !!!
        $stringFind = $stringFind . '-' . $umovto;
    
        
        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "cambiaMontos")){
            
            if (isset($_POST['asignaSubCtaDirecta']) and $_POST['asignaSubCtaDirecta'] != 'novalida') {
                
                $sql = "update Movimientos
                        set TipoMovimientoId = '".$_POST['asignaSubCtaDirecta']."'";
                            
                if ($_POST['BankAccount_GLOBAL'] != '*')
                        $sql = $sql . ", u_banco = '".$_POST['BankAccount_GLOBAL']."' where u_movimiento = ". $umovto ."";
                else
                        $sql = $sql . " where u_movimiento = ". $umovto ."";
                            
                //echo $sql;            
                $resul = DB_query($sql,$db);
                            
            } elseif (isset($_POST['Concepto_'.$umovto])) {
                
                $sql = "update Movimientos
                        set Cargo = '".str_replace(",", "",$_POST['Cargo_'.$umovto])."',
                            Abono = '".str_replace(",", "",$_POST['Abono_'.$umovto])."',
                            referencia = '".str_replace(",", "",$_POST['Ref_'.$umovto])."',
                            concepto = '".str_replace(",", "",$_POST['Concepto_'.$umovto])."',
                            descripcion = '".str_replace(",", "",$_POST['Descripcion_'.$umovto])."',
                            tagref = '".$_POST['tagref_'.$umovto]."',		
                            TipoMovimientoId = '".$_POST['tipoMovimiento_'.$umovto]."'";
                            
                if (isset($_POST['entidadNegocio1']) AND $_POST['entidadNegocio1'] != '*') {
                         $sql = $sql . ", u_entidad = ".$_POST['entidadNegocio1']."";   
                }
                
                if ($_POST['BankAccount_GLOBAL'] != '*')
                        $sql = $sql . ", u_banco = '".$_POST['BankAccount_GLOBAL']."' where u_movimiento = ". $umovto ."";
                else
                        $sql = $sql . " where u_movimiento = ". $umovto ."";
                            
                            
                            
                $resul = DB_query($sql,$db);
            } elseif (isset($_POST['entidadNegocio1']) AND $_POST['entidadNegocio1'] != '*') {
                $sql = "update Movimientos
                        set u_entidad = ".$_POST['entidadNegocio1']."";   
                $sql = $sql . " where u_movimiento = ". $umovto ."";
                            
                $resul = DB_query($sql,$db);
            }
            
            
            
            //echo $sql."<BR>";
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "confMovimiento")){
            
            $sql = "update Movimientos
                    set confirmado = 0
                    where u_movimiento = '". $umovto ."'";
                    
            $resul = DB_query($sql,$db);
        
           
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "inhabilita")){
            
            $sql = "update Movimientos
                    set activo = 0
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            //echo $sql."<BR>";
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "habilita")){
            
            $sql = "update Movimientos
                    set activo = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "borraMovimiento")){
            
            $sql = "DELETE From Movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "pendMovimiento")){
            
            $sql = "update Movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            
            $sql = "update Movimientos
                    set prioridad = prioridad - 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            
            $sql = "update Movimientos
                    set prioridad = prioridad + 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
          
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,7)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
          
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 WEEK)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
          
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
                    
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
          
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            
            $sql = "update Movimientos
                    set dia = ".$_POST['diaDirecto'].",
                        mes = ".$_POST['mesDirecto'].",
                        anio = ".$_POST['anioDirecto']."
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set fecha = CONCAT(anio,'/',mes,'/',dia)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia5")){
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia5")){
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "copynextmonth")){
          
            $sql = "insert into Movimientos
            		(u_movimiento,u_empresa,dia,mes,anio,concepto,descripcion,
                     cargo,civa,cretencion,abono,aiva,aretencion,referencia,prioridad,u_banco,
                     u_movimiento_rec,confirmado,UserId,TipoMovimientoId,estimado,convenio,IVA,vencimiento,u_entidad,u_cajaChica,
                     periodo_dev,erp,fecha,activo,grupo_contable)
                    select null,u_empresa,dia,mes,anio,concepto,descripcion,
                            cargo,civa,cretencion,abono,aiva,aretencion,referencia,prioridad,u_banco,
                            u_movimiento_rec,1,UserId,TipoMovimientoId,0,convenio,IVA,fecha,u_entidad,u_cajaChica,
                            periodo_dev,erp,fecha,activo,grupo_contable
                    from Movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            $newMovto = $_SESSION['LastInsertId'];
            
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "copynextweek")){
          
            $sql = "insert into Movimientos
            		(u_movimiento,u_empresa,dia,mes,anio,concepto,descripcion,
                     cargo,civa,cretencion,abono,aiva,aretencion,referencia,prioridad,u_banco,
                     u_movimiento_rec,confirmado,UserId,TipoMovimientoId,estimado,convenio,IVA,vencimiento,u_entidad,u_cajaChica,
                     periodo_dev,erp,fecha,activo,grupo_contable)
                    select null,u_empresa,dia,mes,anio,concepto,descripcion,
                            cargo,civa,cretencion,abono,aiva,aretencion,referencia,prioridad,u_banco,
                            u_movimiento_rec,1,UserId,TipoMovimientoId,0,convenio,IVA,fecha,u_entidad,u_cajaChica,
                            periodo_dev,erp,fecha,activo,grupo_contable
                    from Movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            $newMovto = $_SESSION['LastInsertId'];
            
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 WEEK)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $newMovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }
    }
    
}/* ELEMENTOS SELECCIONADOS DEL FLUJO DE CXC */

elseif (isset($_POST['selMovimientoCXC'])) {
    
    for ($i=0;$i<=count($_POST['selMovimientoCXC'])-1; $i++) {
        
        //echo $i."-".$_POST['selMovimiento'][$i]."<br>".$_POST['Oper'];
        $umovto = $_POST['selMovimientoCXC'][$i];
        
        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            
            $sql = "update debtortrans
                    set priority = priority - 1
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            
            $sql = "update debtortrans
                    set priority = priority + 1
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
          
            $sql = "update debtortrans
                    set promisedate = CASE WHEN promisedate is null THEN SUBDATE(trandate,1) ELSE SUBDATE(promisedate,1) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,7)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
          
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 WEEK)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
          
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
                    
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
          
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            
            $sql = "update Movimientos
                    set dia = ".$_POST['diaDirecto'].",
                        mes = ".$_POST['mesDirecto'].",
                        anio = ".$_POST['anioDirecto']."
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set fecha = CONCAT(anio,'/',mes,'/',dia)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }
    }
    
}/* ELEMENTOS SELECCIONADOS DEL FLUJO DE CXPAGAR */

elseif (isset($_POST['selMovimientoCXP'])) {
    
    for ($i=0;$i<=count($_POST['selMovimientoCXP'])-1; $i++) {
        
        //echo $i."-".$_POST['selMovimiento'][$i]."<br>".$_POST['Oper'];
        $umovto = $_POST['selMovimientoCXP'][$i];
        
        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            
            $sql = "update supptrans
                    set priority = priority - 1
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            
            $sql = "update supptrans
                    set priority = priority + 1
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN SUBDATE(trandate,1) ELSE SUBDATE(promisedate,1) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN ADDDATE(trandate,1) ELSE ADDDATE(promisedate,1) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN ADDDATE(trandate,7) ELSE ADDDATE(promisedate,7) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN SUBDATE(trandate,INTERVAL 1 WEEK) ELSE SUBDATE(promisedate,INTERVAL 1 WEEK) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN ADDDATE(trandate,INTERVAL 1 MONTH) ELSE ADDDATE(promisedate,INTERVAL 1 MONTH) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN SUBDATE(trandate,INTERVAL 1 MONTH) ELSE SUBDATE(promisedate,INTERVAL 1 MONTH) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN SUBDATE(trandate,INTERVAL 1 YEAR) ELSE SUBDATE(promisedate,INTERVAL 1 YEAR) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN ADDDATE(trandate,INTERVAL 1 YEAR) ELSE ADDDATE(promisedate,INTERVAL 1 YEAR) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            
            $sql = "update supptrans
                    set promisedate = CONCAT(DATE_FORMAT(supptrans.promisedate,'%Y-%m'),'-',".$_POST['diaDirecto'].")
                    where id = '". $umovto ."'";
                    echo $sql;
            $result = DB_query($sql,$db);
        
        }
    }
    
} else {
    
    /* TODAS LAS OPERACIONES INDIVIDUALES */
    $umovto = $_GET['u_movimiento'];
        
        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "confMovimiento")){
            
            $sql = "update Movimientos
                    set confirmado = 0
                    where u_movimiento = '". $umovto ."'";
                    
            $resul = DB_query($sql,$db);
        
           
        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "inhabilita")){
            
            $sql = "update Movimientos
                    set activo = 0
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "habilita")){
            
            $sql = "update Movimientos
                    set activo = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "borraMovimiento")){
            
            $sql = "DELETE From Movimientos
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "pendMovimiento")){
            
            $sql = "update Movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            
            $sql = "update Movimientos
                    set prioridad = prioridad - 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            
            $sql = "update Movimientos
                    set prioridad = prioridad + 1
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
          
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,1)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
          
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 MONTH)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
          
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
          
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 1 YEAR)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            
            $sql = "update Movimientos
                    set dia = ".$_POST['diaDirecto']." 
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia5")){
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia5")){
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 5 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
            $sql = "update Movimientos
                    set fecha = ADDDATE(fecha,INTERVAL 7 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
            $sql = "update Movimientos
                    set fecha = SUBDATE(fecha,INTERVAL 7 DAY)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
            $sql = "update Movimientos
                    set dia = DAY(fecha),
                        mes = MONTH(fecha),
                        anio = YEAR(fecha)
                    where u_movimiento = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridadCXC")){
            
            $sql = "update debtortrans
                    set priority = priority - 1
                    where id = '". $umovto ."'";
            
            $result = DB_query($sql,$db);
            
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridadCXC")){
            
            $sql = "update debtortrans
                    set priority = priority + 1
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDiaCXC")){
          
             $sql = "update debtortrans
                    set duedate = CASE WHEN duedate is null THEN SUBDATE(trandate,1) ELSE SUBDATE(duedate,1) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDiaCXC")){
          
             $sql = "update debtortrans
                    set duedate = CASE WHEN duedate is null THEN ADDDATE(trandate,1) ELSE ADDDATE(duedate,1) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemanaCXC")){
          
            $sql = "update debtortrans
                    set duedate = CASE WHEN duedate is null THEN SUBDATE(trandate,INTERVAL 1 WEEK) ELSE SUBDATE(duedate,INTERVAL 1 WEEK) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemanaCXC")){
          
            $sql = "update debtortrans
                    set duedate = CASE WHEN duedate is null THEN ADDDATE(trandate,INTERVAL 1 WEEK) ELSE ADDDATE(duedate,INTERVAL 1 WEEK) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            echo $sql;
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMesCXC")){
          
            $sql = "update debtortrans
                    set duedate = CASE WHEN duedate is null THEN SUBDATE(trandate,INTERVAL 1 MONTH) ELSE SUBDATE(duedate,INTERVAL 1 MONTH) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMesCXC")){
          
            $sql = "update debtortrans
                    set duedate = CASE WHEN duedate is null THEN ADDDATE(trandate,INTERVAL 1 MONTH) ELSE ADDDATE(duedate,INTERVAL 1 MONTH) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnioCXC")){
          
            $sql = "update debtortrans
                    set duedate = CASE WHEN duedate is null THEN SUBDATE(trandate,INTERVAL 1 YEAR) ELSE SUBDATE(duedate,INTERVAL 1 YEAR) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnioCXC")){
          
            $sql = "update debtortrans
                    set duedate = CASE WHEN duedate is null THEN ADDDATE(trandate,INTERVAL 1 YEAR) ELSE ADDDATE(duedate,INTERVAL 1 YEAR) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridadCXP")){
            
            $sql = "update supptrans
                    set priority = priority - 1
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridadCXP")){
            
            $sql = "update supptrans
                    set priority = priority + 1
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDiaCXP")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN SUBDATE(trandate,1) ELSE SUBDATE(promisedate,1) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDiaCXP")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN ADDDATE(trandate,1) ELSE ADDDATE(promisedate,1) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemanaCXP")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN ADDDATE(trandate,7) ELSE ADDDATE(promisedate,7) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemanaCXP")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN SUBDATE(trandate,INTERVAL 1 WEEK) ELSE SUBDATE(promisedate,INTERVAL 1 WEEK) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMesCXP")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN ADDDATE(trandate,INTERVAL 1 MONTH) ELSE ADDDATE(promisedate,INTERVAL 1 MONTH) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMesCXP")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN SUBDATE(trandate,INTERVAL 1 MONTH) ELSE SUBDATE(promisedate,INTERVAL 1 MONTH) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnioCXP")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN SUBDATE(trandate,INTERVAL 1 YEAR) ELSE SUBDATE(promisedate,INTERVAL 1 YEAR) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
            
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnioCXP")){
          
            $sql = "update supptrans
                    set promisedate = CASE WHEN promisedate is null THEN ADDDATE(trandate,INTERVAL 1 YEAR) ELSE ADDDATE(promisedate,INTERVAL 1 YEAR) END
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirectoCXP")){
            
            $sql = "update supptrans
                    set dia = ".$_POST['diaDirecto'].",
                        
                    where id = '". $umovto ."'";
                    
            $result = DB_query($sql,$db);
        
        }
    
}

?>