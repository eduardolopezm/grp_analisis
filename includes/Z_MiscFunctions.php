<?php
	function add_ceros($numero,$ceros) 
    {
            $order_diez = explode(".",$numero); 
            $dif_diez = $ceros - strlen($order_diez[0]); 
            for($m = 0 ; $m < $dif_diez;$m++) 
            { 
                    @$insertar_ceros .= 0;
            } 
            return $insertar_ceros .= $numero; 
    }
    
    
    function add_cerosstring($numero,$ceros) 
    {
            //$order_diez = explode(".",$numero);
            
            $dif_diez = $ceros - strlen($numero);
            for($m = 0 ; $m < $dif_diez;$m++){
                    @$insertar_ceros .= "0";
            }
            
            return $insertar_ceros .= $numero; 
    }
    
    function add_spacesstring($numero,$ceros) 
    {
            
            $dif_diez = $ceros - strlen($numero); 
            for($m = 0 ; $m < $dif_diez;$m++) 
            { 
                    @$insertar_ceros .= '+';
            } 
            return $numero .= $insertar_ceros ; 
    }
?>
