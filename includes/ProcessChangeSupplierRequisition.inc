<?php
$Result = DB_Txn_Begin($db);
$Total=$_POST['Total'];
for ($i=1;$i <= $Total;$i++){
	if($_POST['Sel' . $i]==true){
		$ordenitem=$_POST['requisitionno_' . $i];
		$sql="UPDATE requisitionorderdetails
		      SET supplierno='".$_POST['SupplierChange']."'
		      WHERE podetailitem=".$ordenitem;
		$ErrMsg =  _('La Actualizacion del proveedor no fue posible');
                $DbgMsg = _('El SQL utilizado es');
                $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	}
}
$Result=DB_Txn_Commit($db);
prnMsg( _('<B>EL CAMBIO DE PROVEEDOR SE REALIZO DE MANERA EXITOSA...</B>'),'success');
?>