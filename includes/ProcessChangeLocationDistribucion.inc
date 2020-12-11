<?php
$Result = DB_Txn_Begin($db);
$Total=$_POST['Total'];
for ($i=1;$i <= $Total;$i++){
	if($_POST['Sel' . $i]==true){
		$ordenitem=$_POST['requisitionno_' . $i];
		$SQLdiponible=" SELECT statusdetail
                        FROM requisitionorderdetails
			WHERE podetailitem='" . $ordenitem . "'";
		$DemandResult = DB_query($SQLdiponible,$db,$ErrMsg,$DbgMsg);
		if (DB_num_rows($DemandResult)==1){
		 $DemandRow = DB_fetch_row($DemandResult);
		 $Status=  $DemandRow[0];
		}
		if ($Status=='Solicitud'){
			$sql="UPDATE requisitionorderdetails
			      SET fromstocklocation='".$_POST['LocationChange']."'
			      WHERE podetailitem=".$ordenitem;
			$ErrMsg =  _('La Actualizacion del proveedor no fue posible');
			$DbgMsg = _('El SQL utilizado es');
			$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}
	}
}
$Result=DB_Txn_Commit($db);
prnMsg( _('<B>EL CAMBIO DE ALMACEN SE REALIZO DE MANERA EXITOSA...</B>'),'success');
?>