<?php
    //******************************************************************************************************************	    
    //********************************* CAMPOS EXTRA *******************************************************************
    //******************************************************************************************************************
    $totalcampos=$_POST['TotalPropDefault_'.$lineaorden];
    if ($totalcampos>0){
            for ($i=0;$i<$totalcampos;$i++){
                    $stockid=$_POST['PropDefaultval'.$lineaorden.'_'.$i];
                    $valorstock=$_POST['PropDefault'.$lineaorden.'_'.$i];
                    $tipoobj=$_POST['tipoobjeto'.$lineaorden.'_'.$i];
                    $consulta=$_POST['consulta'.$lineaorden.'_'.$i];
                    $campo=$_POST['campo'.$lineaorden.'_'.$i];
                    if ($tipoobjeto=='checkbox'){
                            if ($tipoobjeto==true){
                                    $valorstock="SI";
                            }else{
                                    $valorstock="NO";
                            }
                    }
                    $valorbase=$valorstock;
                    
                    if (strlen($consulta)>5){
                            $sqlcampos=$consulta.' and '. $campo.' = '.$valorstock;
                            $DbgMsg = _('El SQL utilizado para obtener el valor del campo es');
                            $ErrMsg = _('No se pudo obtener el valor, por que');
                            $Result = DB_query($sqlcampos,$db,$ErrMsg,$DbgMsg,true);
                            $Rowcampos = DB_fetch_array($Result);
                            $valorbase=$Rowcampos[1];
                    }
                    if ($valorstock=="0" and strlen($consulta)>5){
                            $sqlcampos=$consulta.' and salesmanname like "%sin trabajador%" and area= '.$_SESSION['Tagref'].' limit 1';
                            $DbgMsg = _('El SQL utilizado para obtener el valor del campo es');
                            $ErrMsg = _('No se pudo obtener el valor, por que');
                            $Result = DB_query($sqlcampos,$db,$ErrMsg,$DbgMsg,true);
                            $Rowcampos = DB_fetch_array($Result);
                            $valorbase=$Rowcampos[1];
                            $valorstock=$Rowcampos[0];
                    }
                    if ($valorstock!="0"){
                        $existeprop=ValidaSalesProperty($stockid,$OrderNo,$StockItem->LineNumber,trim($valorstock),11,$db);
			    if ($existeprop==0){
                                $SQL = "INSERT INTO salesstockproperties (
                                                stkcatpropid,
                                                orderno,
                                                orderlineno,
                                                valor,
                                                InvoiceValue,
                                                typedocument
                                        )
                                        VALUES
                                        (
                                                ". $stockid . ",
                                                " . $OrderNo . ",
                                                " . $StockItem->LineNumber . ",
                                                '" . trim($valorstock) . "',
                                                '" . trim($valorbase) . "',
                                                11
                                        )";
                                $ErrMsg="Error al insertar los valores extra";	
                                $Result = DB_query($SQL,$db,$ErrMsg);
                            }
                    }
            }
    }
    //******************************************************************************************************************	    
    //*********************************AQUI TERMINA CAMPOS EXTRA *******************************************************
    //******************************************************************************************************************
?>