<?php

if (isset($_POST['selMovimiento'])) {

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

			}
		}
	}

    //echo "ENTRO";
    for ($i=0;$i<=count($_POST['selMovimiento'])-1; $i++) {

        $umovto = $_POST['selMovimiento'][$i];

        $stringFind = $stringFind . '-' . $umovto;

        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "cambiaMontos")){
        	//echo "entraaaaa";


		    $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".CharClean(htmlspecialchars_decode($myrowMov['concepto'],ENT_NOQUOTES))."<br>".CharClean(htmlspecialchars_decode($myrowMov['descripcion'],ENT_NOQUOTES))."<br>".$myrowMov['cargo']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";




            $result2= DB_query($sql2,$db);


		    if (isset($_POST['comentarioGlobal']) AND strlen($_POST['comentarioGlobal']) > 0) {

			$sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
				VALUES ('".$umovto."','".CharClean(htmlspecialchars_decode($_POST['comentarioGlobal']))."',
					Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','comentario')";

			$result2= DB_query($sql2,$db);
		    }
		    //


			if ($_POST['ResourceId_GLOBAL'] != '*') {
			        $sql = "update workorders set u_user = '".$_POST['ResourceId_GLOBAL']."'";
					$sql = $sql . " where wo = ". $umovto ."";
					//echo '<pre>1'.$sql;
	                $resul = DB_query($sql,$db);

	                $recanterior = $myrowMov['realname'];

	                $sql='Select workorders.*,
	                                www_users.realname
	                        FROM workorders LEFT JOIN www_users ON workorders.u_user = www_users.userid
	                        WHERE wo ='.$umovto;
	                $ErrMsg = _('The authentication details cannot be recovered because');
	                $ResultMov=DB_query($sql,$db,$ErrMsg);
	                $myrowMov=DB_fetch_array($ResultMov);

	                $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
	                                VALUES ('".$umovto."','de:".$recanterior."',
	                                        Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','cambio recurso')";

	                $result2= DB_query($sql2,$db);


			}



			if ($_POST['EstatusId_GLOBAL'] != '*') {
					$posible=true;


					if ($posible == true){

						$sql = "select wocontrolpanel_status.flagarmado,
										wocontrolpanel_status.flagpublicado,
										wocontrolpanel_status.orden
								from wocontrolpanel_status
								where wocontrolpanel_status.idstatus = '".$_POST['EstatusId_GLOBAL']."'
								AND wocontrolpanel_status.panelcontrol = '".$_POST['funcion']."'";
						$result = DB_query($sql, $db);
						while($myrow = DB_fetch_array($result)){
							$flag = $myrow['flagarmado'];
							$ordenNuevoestatus = $myrow['orden'];
						}
						$sql = "Select wocontrolpanel_status.orden,
										wocontrolpanel_status.flagpublicado
								from workorders
								inner join wocontrolpanel_status ON workorders.idstatus = wocontrolpanel_status.idstatus
								Where workorders.wo = '".$umovto."'";
						$result = DB_query($sql, $db);
						while($myrow = DB_fetch_array($result)){
							$flagpublicado = $myrow['flagpublicado'];
							$ordenactualestatus = $myrow['orden'];
						}

						if($flagpublicado == 1){

							if($ordenNuevoestatus < $ordenactualestatus){
							}else {
								$sql = "update workorders set idstatus = '".$_POST['EstatusId_GLOBAL']."'";
								$sql = $sql . " where wo = ". $umovto ."";
								//echo '<pre>2'.$sql;
								$resul = DB_query($sql,$db);
							}
						}else{
							$sql = "update workorders set idstatus = '".$_POST['EstatusId_GLOBAL']."'";
							$sql = $sql . " where wo = ". $umovto ."";
							//echo '<pre>2'.$sql;
							$resul = DB_query($sql,$db);
						}
						if($ordenNuevoestatus < $ordenNuevoestatus)

						//if($flag == 1){
						// ACTUALIZA A RECIBIDO EN CUALQUIER CAMBIO DE ESTATUS PARA QUE NO PERMITA BORRAR OT
							$sql = "update  woitems
									set woitems.qtyrecd = 1
									Where woitems.wo = '".$umovto."'";
							$resul = DB_query($sql, $db);
						//}

					    $recanterior = $myrowMov['statusname'];

					    $sql='Select workorders.*,
									www_users.realname,
									wocontrolpanel_status.nombre as statusname
								FROM workorders LEFT JOIN www_users ON workorders.u_user = www_users.userid
								LEFT JOIN wocontrolpanel_status ON workorders.idstatus = wocontrolpanel_status.idstatus
								WHERE wo ="'.$umovto.'"
								AND wocontrolpanel_status.panelcontrol ="'.$_POST['funcion'].'"';
					    $ErrMsg = _('The authentication details cannot be recovered because');
					    $ResultMov=DB_query($sql,$db,$ErrMsg);
					    $myrowMov=DB_fetch_array($ResultMov);

					    $sql = "SELECT wocontrolpanel_status.flagcancelado
								FROM wocontrolpanel_status
								WHERE wocontrolpanel_status.idstatus = '".$_POST['EstatusId_GLOBAL']."'";
					    $resultcomen = DB_query($sql, $db);
					   	$myrowcoment = DB_fetch_array($resultcomen);
					    if(DB_num_rows($resultcomen) <> 0){
					    	if($myrowcoment['flagcancelado'] == 1){

					    		$sql3 = "SELECT wocontrolpanel_comcancelacion.comcancelaciondes
								FROM wocontrolpanel_comcancelacion
								WHERE wocontrolpanel_comcancelacion.comcancelacionactivo = 1
								AND wocontrolpanel_comcancelacion.comcancelacionid = '".$_POST['comcancelacionid']."'";
					    		$result3 = DB_query($sql3, $db);
					    		$myrow3 = DB_fetch_array($result3);

					    		$sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion,comcancelacionid)
	                                VALUES ('".$umovto."','".CharClean(htmlspecialchars_decode($_POST['comentarioGlobal']))."',
	                                        Now(),0,'".$_POST['EstatusId_GLOBAL']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','cambio estatus','".$_POST['comcancelacionid']."')";

					    		$result2= DB_query($sql2,$db);
					    	}else{
					    		$sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
	                                VALUES ('".$umovto."','".CharClean(htmlspecialchars_decode($_POST['comentarioGlobal']))."',
	                                        Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','cambio estatus')";

					    		$result2= DB_query($sql2,$db);
					    	}

					    }else{
					    	$sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
	                                VALUES ('".$umovto."','".CharClean(htmlspecialchars_decode($_POST['comentarioGlobal']))."',
	                                        Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','cambio estatus')";

					    	$result2= DB_query($sql2,$db);
					    }



					}
			}



		    $dia = str_replace(",", "",$_POST['Dia_'.$umovto]);
		    $mes = str_replace(",", "",$_POST['Mes_'.$umovto]);
		    $anio = str_replace(",", "",$_POST['Anio_'.$umovto]);
		    $sql = "update workorders
	                    set startdate = DATE(CONCAT('$anio','-','$mes','-','$dia'))
	                    where wo = '". $umovto ."'";


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "confMovimiento")){

            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set confirmado = 0, fecha_compromiso = fecha
                    where u_movimiento = '". $umovto ."'";


        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "inhabilita")){

            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set activo = 0
                    where u_movimiento = '". $umovto ."'";



        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "habilita")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set activo = 1
                    where u_movimiento = '". $umovto ."'";



        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "borraMovimiento")){

            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";



            $sql = "DELETE From wocontrolpanel_horas
                    where wo = '". $umovto ."'";
	    	$sql = "DELETE From workorders
                    where wo = '". $umovto ."'";



        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "pendMovimiento")){

            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";

            //$result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){

            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set prioridad = if(prioridad > 1, prioridad - 1,1)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){

            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);
            $sql = "update workorders
                    set prioridad = prioridad + 1
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "cambiahora")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','Cambio hora')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = concat(DATE_FORMAT(startdate,'%Y-%m-%d'),' ','".$_POST['hora']."')
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = SUBDATE(startdate,1)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);

            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
            $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = ADDDATE(startdate,1)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);

            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = ADDDATE(startdate,7)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = SUBDATE(startdate,INTERVAL 1 WEEK)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = ADDDATE(startdate,INTERVAL 1 MONTH)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = SUBDATE(startdate,INTERVAL 1 MONTH)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = SUBDATE(startdate,INTERVAL 1 YEAR)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = ADDDATE(startdate,INTERVAL 1 YEAR)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);
            $dia = $_POST['diaDirecto'];
            $sql = "update workorders
                    set startdate = concat(year(startdate),'-',month(startdate),'-$dia')
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }
            }elseif ((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaHoraDirecto")){
            	$sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            	$result2= DB_query($sql2,$db);
            	$dia = $_POST['diaDirecto'];
            	$hora = $_POST['hora'];
            	$sql = "update workorders
            	set startdate = concat(year(startdate),'-',month(startdate),'-$dia',' $hora:00')
            	where wo = '". $umovto ."'";
            	/*if($_SESSION['UserID'] == 'admin'){
            	echo '<pre>'.$sql;
            	}*/
            	$result = DB_query($sql,$db);
          }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaHOY")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = current_date
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                      $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaMANANA")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);


            $sql = "update workorders
                    set startdate = ADDDATE(Now(),INTERVAL 1 DAY)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia5")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = ADDDATE(startdate,INTERVAL 5 DAY)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia5")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = SUBDATE(startdate,INTERVAL 5 DAY)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "copynextmonth")){




        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "copynextweek")){


        }
    }

}

 elseif (isset($_GET['wo']) and isset($_GET['Oper']) and $_GET['Oper'] <> 'cancelar') {



        $umovto = $_GET['wo'];

        $sql='Select workorders.*,
                        www_users.realname,
                        wocontrolpanel_status.nombre as status
                FROM workorders LEFT JOIN www_users ON workorders.u_user = www_users.userid
                            LEFT JOIN wocontrolpanel_status ON wocontrolpanel_status.idstatus=workorders.idstatus
		WHERE wo ="'.$umovto.'"
		AND wocontrolpanel_status.panelcontrol = "'.$_POST['funcion'].'"';

	$ErrMsg = _('The authentication details cannot be recovered because');
	$ResultMov=DB_query($sql,$db,$ErrMsg);
        $myrowMov=DB_fetch_array($ResultMov);

        if((isset($_POST['Oper'])) and ($_POST['Oper'] == "setEstatus")){

            $estadoAnterior = $myrowMov['idstatus'];
		    $estadoAnteriorNombre = $myrowMov['status'];

		    $posible=true;
		    //verificar que si es 2(E1),4(E2),6(E3),8(E4) no haya ninguna OT con ese status
		    if ($_GET['idstatus']==2 || $_GET['idstatus']==4 || $_GET['idstatus']==6 || $_GET['idstatus']==8){
		    	$qry = "Select wo FROM workorders WHERE idstatus = '".$_GET['idstatus']."'";
		    	$r = DB_query($qry,$db);
		    	if (DB_num_rows($r) > 0){
		    		$posible=false;
		    		prnMsg('Existen Ordenes de Trabajo con el estatus que intenta asignar y solo debe haber una','warning');
		    	}

		    }

		    if ($posible){
	            $sql = "update workorders
	                    set idstatus = ".$_GET['idstatus']."
	                    where wo = '". $umovto ."'";

	            $resul = DB_query($sql,$db);


	            $sql='Select workorders.*,
	                            www_users.realname,
	                            wocontrolpanel_status.nombre as status
	                    FROM workorders LEFT JOIN www_users ON workorders.u_user = www_users.userid
	                    LEFT JOIN wocontrolpanel_status ON wocontrolpanel_status.idstatus=workorders.idstatus
	                    WHERE wo ="'.$umovto.'"
	                    AND wocontrolpanel_status.panelcontrol = "'.$_POST['funcion'].'"';


	            $ErrMsg = _('The authentication details cannot be recovered because');
	            $ResultMov=DB_query($sql,$db,$ErrMsg);
	            $myrowMov=DB_fetch_array($ResultMov);

	            $sql2="INSERT INTO wocontrolpoanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
	                    VALUES ('".$umovto."','de:".$estadoAnteriorNombre."',
	                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

	            $result2= DB_query($sql2,$db);
		    }




        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "confMovimiento")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            //$result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set confirmado = 0, fecha_compromiso = fecha
                    where u_movimiento = '". $umovto ."'";

            //$resul = DB_query($sql,$db);


        }
        elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "inhabilita")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            //$result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set activo = 0
                    where u_movimiento = '". $umovto ."'";

            //$result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "habilita")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            //$result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set activo = 1
                    where u_movimiento = '". $umovto ."'";

            //$result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "borraMovimiento")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

           // $result2= DB_query($sql2,$db);

	    $sql = "DELETE From tasks_horas
                    where idtarea = '". $umovto ."'";

           // $result = DB_query($sql,$db);

            $sql = "DELETE From tasks_movimientos
                    where u_movimiento = '". $umovto ."'";

           // $result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "pendMovimiento")){
            $sql2="INSERT INTO tasks_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','***',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            //$result2= DB_query($sql2,$db);

            $sql = "update tasks_movimientos
                    set confirmado = 1
                    where u_movimiento = '". $umovto ."'";

            //$result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorPrioridad")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set prioridad = if(prioridad >1,prioridad - 1,1)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguientePrioridad")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['prioridad']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set prioridad = prioridad + 1
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorHora")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);
            $sql = "update workorders
                    set startdate = SUBDATE(startdate,INTERVAL 1 HOUR)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteHora")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);
            $sql = "update workorders
                    set startdate = ADDDATE(startdate,INTERVAL 1 HOUR)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);
            $sql = "update workorders
                    set startdate = SUBDATE(startdate,1)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = ADDDATE(startdate,1)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);

            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorMes")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = SUBDATE(startdate,INTERVAL 1 MONTH)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteMes")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = ADDDATE(startdate,INTERVAL 1 MONTH)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorAnio")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = SUBDATE(startdate,INTERVAL 1 YEAR)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }

        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteAnio")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = ADDDATE(startdate,INTERVAL 1 YEAR)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "diaDirecto")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $dia = $_POST['diaDirecto'];

            $sql = "update workorders
                    set startdate = concat(year(startdate),'-',month(startdate),'-$dia')
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteDia5")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);


            $sql = "update workorders
                    set startdate = ADDDATE(startdate,INTERVAL 5 DAY)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);
            $sql = "SELECT workorders.startdate,
            				workorders.orderno,
            				workorders.lineno
            		FROM workorders
            		WHERE workorders.wo = '". $umovto ."'";
            $resultfecha = DB_query($sql, $db);
            while($myrowfecha = DB_fetch_array($resultfecha)){
            	$startdate = $myrowfecha['startdate'];
            	$orderno = $myrowfecha['orderno'];
            	$lineno = $myrowfecha['lineno'];
            }
            $fechastart = str_replace("-", "/", $startdate);
            $fechastart = trim(substr($fechastart, 0,10));
                    $sqlfecha ="SELECT salesstockproperties.stkcatpropid
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						INNER JOIN stockcatSelTypes ON stockcatSelTypes.seltypeid = stockcatproperties.controltype
						WHERE stockcatSelTypes.classname = 'date'
            			AND salesstockproperties.orderno = '".$orderno."'
						AND salesstockproperties.orderlineno = '".$lineno."'";
            $resultfecha = DB_query($sqlfecha, $db);
            if($resultfecha > 0){
            	while($myrowfechasales = DB_fetch_array($resultfecha)){
            		$sql = "UPDATE salesstockproperties
							SET salesstockproperties.valor = '".$fechastart."',
								salesstockproperties.InvoiceValue = '".$fechastart."'
							WHERE salesstockproperties.orderno = '".$orderno."'
							AND salesstockproperties.orderlineno = '".$lineno."'
							AND salesstockproperties.stkcatpropid = '".$myrowfechasales['stkcatpropid']."'";
            		$resultfecha = DB_query($sql, $db);
            	}
            }


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorDia5")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = SUBDATE(startdate,INTERVAL 5 DAY)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "siguienteSemana")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);


            $sql = "update workorders
                    set startdate = ADDDATE(stardate,INTERVAL 7 DAY)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);


        }elseif((isset($_POST['Oper'])) and ($_POST['Oper'] == "anteriorSemana")){
            $sql2="INSERT INTO wocontrolpanel_comentarios (wo,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
                    VALUES ('".$umovto."','de:".$myrowMov['fecha']."',
                            Now(),0,'".$myrowMov['idstatus']."','".$myrowMov['u_user']."','".$_SESSION['UserID']."','".$_POST['Oper']."')";

            $result2= DB_query($sql2,$db);

            $sql = "update workorders
                    set startdate = SUBDATE(startdate,INTERVAL 7 DAY)
                    where wo = '". $umovto ."'";

            $result = DB_query($sql,$db);

        }

}

if(isset($_GET['Oper']) and $_GET['Oper'] == 'cancelar'){
	$flagcancelar = ValidaCancelarWO($db,$_GET['wo']);
	if($flagcancelar == 0){
		$flaginserto = CancelarWo($db, $_GET['wo']);
	}
}


// Entra para cerrar ordenes de trabajo seleccionadas
if (isset($_POST["CerrarOT"]))
{
	if (isset($_POST['selMovimiento']))
	{
		for ($i=0;$i<=count($_POST['selMovimiento'])-1; $i++)
		{
			$umovto = $_POST['selMovimiento'][$i];  // obtiene el numero de la orden de trabajo
			$msjCierre= CerrarOrdenTrabajo($umovto);

			prnMsg($msjCierre, "info");
		}

		if ($_SESSION['CompanyRecord']['gllink_stock']==1)
		{
			if ($_SESSION['WeightedAverageCosting']==1){
				prnMsg(_('The item cost as calculated from the work order has been applied against the weighted average cost and the necessary GL journals created to update stock as a result of closing this work order'),'success');
			} else {
				prnMsg(_('The work order has been closed and general ledger entries made for the variances on the work order'),'success');
			}
		} else {
			if ($_SESSION['WeightedAverageCosting']==1){
				prnMsg(_('The item costs resulting from the work order have been applied against the weighted average stock value of the items on the work order, and the work order has been closed'),'success');
			} else {
				prnMsg(_('The work order has been closed'),'success');
			}
		}
	}
}

?>
