<?php

/* 
Elaboro: Desarrollador
FECHA DE MODIFICACION: 30-ene-2013
CAMBIOS:
   1. Se modifico consulta de disponibilidad de inventario de acuerdo a area de negocio
   FIN CAMBIOS
*/
// resultados de busqueda de productos
if (isset($_POST['Search']) or isset($_POST['NextO']) or isset($_POST['PrevO']) or isset($_POST['Go1'])){
// verificar razon social de la unidad de trabajo
        $SQLRazonsocial="select legalid from tags where tagref ='". $_SESSION['ItemsNotes'.$identifier]->Tagref."'";
        $ErrMsg = _('The demand for this product from') . ' ' . $loccationstock . ' ' . _('cannot be retrieved because');
        $LegalResult = DB_query($SQLRazonsocial,$db,$ErrMsg,$DbgMsg);
        
        if (DB_num_rows($LegalResult)==1){
          $LegalRow = DB_fetch_row($LegalResult);
          $LegalID =  $LegalRow[0];
        } else {
          $LegalID =0;
        }


        echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'&identifier='.$identifier . '&CurrAbrev='.$_SESSION['ItemsNotes'.$identifier]->CurrAbrev.'&Tagref='.$_SESSION['ItemsNotes'.$identifier]->Tagref.'"& name="SelectParts" method=post>';
        if ($_POST['Keywords']!=='' AND $_POST['StockCode']=='') {
                $msg='</b><div class="page_help_text">' . _('La descripcion del producto se utilizo en la busqueda') . '.</div>';
        } elseif ($_POST['StockCode']!=='' AND $_POST['Keywords']=='') {
                $msg='</b><div class="page_help_text">' . _('Codigo de Producto se utilizo en la busqueda') . '.</div>';
        } elseif ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
                $msg='</b><div class="page_help_text">' . _('Categoria de producto se utilizo en la busqueda') . '.</div>';
        } elseif ($_POST['Keywords']=='' AND $_POST['StockCode']=='' AND $_POST['StockLock']!='All') {
                $msg='</b><div class="page_help_text">' . _('Categoria de producto se utilizo en la busqueda') . '.</div>';
        }
        if(strlen($_POST['Autor'])>0 or strlen($_POST['Editorial'])>0){
	    $_SESSION['ProductSearch']=1;
	    
	    $where = "";
	    $whereAd = "";
	    if ($_POST['StockCat']!="All"){
	    	$where.=" AND stockmaster.categoryid = '".$_POST['StockCat']."'";
	    	$whereAd.=" AND stockmaster.categoryid = '".$_POST['StockCat']."'";
	    }
	    if ($_POST['StockLock']!="All"){
	    	$where.=" AND locstock.loccode = '".$_POST['StockLock']."'";
	    	$whereAd.=" AND locstock.loccode = '".$_POST['StockLock']."'";
	    
	    }
	    if ($_POST['StockCode']!=""){
	    	$where.=" AND stockmaster.stockid like '%".$_POST['StockCode']."%'";
	    
	    }
	    	
	    if ($_POST['Keywords']!==''){
	    	$whereAd.=" AND stockmaster.longdescription like '%".$_POST['Keywords']."%'";
	    }
	    $sqlrelations = "								 UNION
	    
									SELECT stockmaster.stockid,
                                        stockmaster.description,
                                        stockmaster.units,
                                        tags.legalid,
                                        sum(locstock.quantity) as quantity ,
                                        disponibilidad.disponible,
                                        disponibilidad.redinvoice,
                                        sum(locstock.ontransit) as ontransit ,
                                        COALESCE(MIN(prices.price),9999999) as price,
                                        stockcategory.categorydescription,locstock.loccode
									FROM stockmaster,(
												 select stockmaster.*
												from stockmaster
												FORCE INDEX(stockmulti)
												JOIN stockrelations ON stockmaster.stockid = stockrelations.stockid and stockidrel like 'b%'
												where 1=1
	    										$whereAd
												AND stockmaster.mbflag <>'G'
												AND stockmaster.discontinued=0) as stockrelations,
										tags,
											( SELECT stockmaster.stockid,
														sum(locstock.quantity-locstock.ontransit) as disponible,
														stockcategory.redinvoice
												   FROM stockmaster, stockcategory ,locstock,sec_loccxusser ,sec_stockcategory
												   WHERE stockmaster.categoryid=stockcategory.categoryid
												   AND stockmaster.stockid=locstock.stockid
												   AND sec_stockcategory.categoryid= stockcategory.categoryid
												   AND sec_stockcategory.userid='admin'
													AND sec_loccxusser.userid='admin'
													AND stockmaster.mbflag <>'G'
													AND sec_loccxusser.loccode=locstock.loccode
													AND sec_loccxusser.userid='admin'
													AND stockmaster.stockid LIKE '%{$_POST['StockCode']}%'
													AND stockmaster.discontinued=0
													GROUP BY stockmaster.stockid) as disponibilidad,
											locstock,
											locations,
											prices,
											sec_stockcategory,
											sec_loccxusser,
											stockcategory
										WHERE tags.tagref=locations.tagref
										AND locstock.stockid = stockmaster.stockid
										AND locstock.loccode = locations.loccode
										and stockmaster.stockid = prices.stockid and typeabbrev = 'PL' and currabrev = 'MXN' and (debtorno = '108' or debtorno = '') and (branchcode = '108' or branchcode = '')
										and sec_stockcategory.categoryid= stockmaster.categoryid AND sec_stockcategory.userid='admin'
										and sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='admin'
										and ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)
										AND locations.tagref = 1
										 AND  stockmaster.categoryid=stockcategory.categoryid
										Group By stockmaster.stockid,stockmaster.manufacturer,locstock.loccode";
	}
        if (isset($_POST['Keywords']) AND strlen($_POST['Keywords'])>2 or  $_SESSION['ProductSearch']==1) {
                
                //insert wildcard characters in spaces
                $_POST['Keywords'] = strtoupper($_POST['Keywords']);
                $i=0;
                $SearchString = '%';
                while (strpos($_POST['Keywords'], ' ', $i)) {
                        $wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
                        $SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
                        $i=strpos($_POST['Keywords'],' ',$i) +1;
                }
                $SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';
                if ($_POST['StockCat']=='All'){
                        
                        /*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
                        /* ORDENA PRIMERO POR CANTIDAD DISPONIBLE EN ALMACENES DEL USUARIO Y DESPUES POR PRECIO */
                        //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')		
                        $SQL = "SELECT stockmaster.stockid, 
										stockmaster.description, 
										stockmaster.units,
										tags.legalid, 
										sum(locstock.quantity) as quantity , 
										disponibilidad.disponible,
										stockcategory.redinvoice,
										COALESCE(MIN(prices.price),9999999) as price,
										sum(locstock.ontransit) as ontransit,
										stockcategory.categorydescription,
										locstock.loccode
                                FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity-locstock.ontransit) as disponible
                                        FROM stockmaster, stockcategory , locstock, sec_loccxusser , sec_stockcategory
                                        WHERE stockmaster.categoryid = stockcategory.categoryid
                                                AND stockmaster.stockid=locstock.stockid
                                                AND sec_stockcategory.categoryid= stockcategory.categoryid
                                                AND sec_stockcategory.userid='".$_SESSION['UserID']."'
                                                AND sec_loccxusser.userid='".$_SESSION['UserID']."'
                                                ";
                                                
                        $SQL = $SQL . "  	AND stockmaster.mbflag <>'G' 
                                                AND sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."'
                                                AND stockmaster.description " . LIKE . " '". $SearchString ."'";
                                                if (strlen($_POST['Autor'])>0 ){
                                                        $SQL= $SQL. " AND stockmaster.stockautor like '%". $_POST['Autor']."%'";	
                                                }
                                                if (strlen($_POST['Editorial'])>0 ){
                                                        $SQL= $SQL. " AND stockmaster.manufacturer like '%". $_POST['Editorial']."%'";	
                                                }
			$SQL =$SQL."  AND stockmaster.discontinued=0
                                                AND sec_loccxusser.loccode = locstock.loccode";
                                                
                        if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                                $SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
                        }
                        
                        if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                               // $SQL= $SQL. " AND locstock.loccode ='". $_SESSION['loccode']."'";	
                        }
                        
                        $SQL = $SQL. "
                                        GROUP BY stockmaster.stockid ) as
                                disponibilidad, stockcategory, locstock, sec_loccxusser, locations,tags,
                                stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
                                        typeabbrev = '".$_SESSION['ItemsNotes'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['ItemsNotes'.$identifier]->CurrAbrev."' and
                                        (debtorno = '".$_SESSION['ItemsNotes'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['ItemsNotes'.$identifier]->Branch."' or branchcode = '')
                                WHERE stockmaster.stockid = disponibilidad.stockid AND tags.tagref=locations.tagref 
                                        AND stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid";
                                        //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                        $SQL = $SQL . "  AND stockmaster.mbflag <>'G' AND 
                                        sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
                                        stockmaster.description " . LIKE . " '". $SearchString ."' AND stockmaster.discontinued=0";
                                        
                        if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                                $SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
                        }
                        if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                               // $SQL= $SQL. " AND locstock.loccode ='". $_SESSION['loccode']."'";	
                        }
                        //PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
                        if (true){
                                $SQL= $SQL. " AND tags.legalid ='". $LegalID."'";	
                        }
                        
                        $SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
                        $SQL = $SQL. " GROUP BY stockmaster.stockid,
                                        stockmaster.description,
                                        stockmaster.units,
                                        tags.legalid,
                                        disponibilidad.disponible,
                                        stockcategory.redinvoice";
                        if ($_SESSION['FindByStockRelation']==1){
                        	$SQL = $SQL.$sqlrelations;
                        	$SQL = $SQL. "
                                ORDER BY categorydescription, disponible desc, price, stockid, legalid";
                        }else{
                        	$SQL = $SQL. "
                                ORDER BY stockcategory.categorydescription, disponibilidad.disponible desc, price, stockmaster.stockid, tags.legalid";
                        }
                        
                                
                } else {
                        
                        /*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
                        $SQL = "SELECT stockmaster.stockid, 
										stockmaster.description, 
										stockmaster.units,
										tags.legalid, 
										sum(locstock.quantity) as quantity , 
										disponibilidad.disponible,
										stockcategory.redinvoice,
										COALESCE(MIN(prices.price),9999999) as price,
										sum(locstock.ontransit) as ontransit,
										stockcategory.categorydescription,
										locstock.loccode
                                FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity-locstock.ontransit) as disponible
                                FROM stockmaster, stockcategory ,locstock,sec_loccxusser , sec_stockcategory
                                WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid
                                        AND sec_stockcategory.categoryid= stockcategory.categoryid
                                        AND sec_stockcategory.userid='".$_SESSION['UserID']."'
                                        AND sec_loccxusser.userid='".$_SESSION['UserID']."'
                                        ";
                                
                                //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                        $SQL = $SQL . "  AND stockmaster.mbflag <>'G' AND 
                                sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
                                stockmaster.description " . LIKE . " '". $SearchString ."'";
                                                if (strlen($_POST['Autor'])>0 ){
                                                        $SQL= $SQL. " AND stockmaster.stockautor like '%". $_POST['Autor']."%'";	
                                                }
                                                if (strlen($_POST['Editorial'])>0 ){
                                                        $SQL= $SQL. " AND stockmaster.manufacturer like '%". $_POST['Editorial']."%'";	
                                                }
			$SQL =$SQL."   AND stockmaster.discontinued=0";
                        if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                                $SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
                        }
                        if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                              //  $SQL= $SQL. " AND locstock.loccode ='". $_SESSION['loccode']."'";	
                        }
                        $SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
                        $SQL = $SQL. "
                                GROUP BY stockmaster.stockid
                                ) as disponibilidad, stockcategory ,locstock,sec_loccxusser, locations,tags,
                                stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
                                                                typeabbrev = '".$_SESSION['ItemsNotes'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['ItemsNotes'.$identifier]->CurrAbrev."' and
                                                                (debtorno = '".$_SESSION['ItemsNotes'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['ItemsNotes'.$identifier]->Branch."' or branchcode = '')
                                WHERE stockmaster.stockid = disponibilidad.stockid AND  tags.tagref=locations.tagref AND  tags.tagref=locations.tagref AND
                                stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid";
                                        //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                        $SQL = $SQL . "  AND stockmaster.mbflag <>'G' AND 
                                sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
                                stockmaster.description " . LIKE . " '". $SearchString ."' AND stockmaster.discontinued=0";
                        if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                                $SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
                        }
                        if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                              //  $SQL= $SQL. " AND locstock.loccode ='". $_SESSION['loccode']."'";	
                        }
                        //PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
                         if (true){
                                $SQL= $SQL. " AND tags.legalid ='". $LegalID."'";	
                        }
                        
                        
                        $SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
                        $SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
                        $SQL = $SQL. " GROUP BY stockmaster.stockid,
                                        stockmaster.description,
                                        stockmaster.units,
                                        tags.legalid,
                                        disponibilidad.disponible,
                                        stockcategory.redinvoice";
                        
                        if ($_SESSION['FindByStockRelation']==1){
                        	$SQL = $SQL.$sqlrelations;
                        	$SQL = $SQL. "
                                ORDER BY categorydescription,disponible desc,price, stockid, loccode";
                        }else{
                        	$SQL = $SQL. "
                                ORDER BY stockcategory.categorydescription,disponibilidad.disponible desc,price, stockmaster.stockid, locstock.loccode";
                        }
                       
                }

        } elseif (strlen($_POST['StockCode'])>0){
        		
                $_POST['StockCode'] = strtoupper($_POST['StockCode']);
                $SearchString = $_POST['StockCode']; //'%' . $_POST['StockCode'] . '%';
                if ($_POST['StockCat']=='All'){
                	echo 'si';
                        /*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
                        $SQL = "SELECT stockmaster.stockid, 
										stockmaster.description, 
										stockmaster.units,
										tags.legalid, 
										sum(locstock.quantity) as quantity , 
										disponibilidad.disponible,
										stockcategory.redinvoice,
										COALESCE(MIN(prices.price),9999999) as price,
										sum(locstock.ontransit) as ontransit,
										stockcategory.categorydescription,
										locstock.loccode
                                FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity-locstock.ontransit) as disponible
                                FROM stockmaster, stockcategory ,locstock,sec_loccxusser ,sec_stockcategory
                                WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid
                                        AND sec_stockcategory.categoryid= stockcategory.categoryid
                                        AND sec_stockcategory.userid='".$_SESSION['UserID']."'
                                        AND sec_loccxusser.userid='".$_SESSION['UserID']."'
                                        ";
                          //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                        $SQL = $SQL . "	AND stockmaster.mbflag <>'G' AND 
                                sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
                                stockmaster.stockid " . LIKE . " '%". $SearchString ."%' ";
                                                if (strlen($_POST['Autor'])>0 ){
                                                        $SQL= $SQL. " AND stockmaster.stockautor like '%". $_POST['Autor']."%'";	
                                                }
                                                if (strlen($_POST['Editorial'])>0 ){
                                                        $SQL= $SQL. " AND stockmaster.manufacturer like '%". $_POST['Editorial']."%'";	
                                                }
			$SQL =$SQL."   AND stockmaster.discontinued=0";
                        if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                                $SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
                        }
                        if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                         //       $SQL= $SQL. " AND locstock.loccode ='". $_SESSION['loccode']."'";	
                        }
                        $SQL = $SQL. "
                                GROUP BY stockmaster.stockid
                                ) as disponibilidad, stockcategory ,locstock,sec_loccxusser, locations,tags,
                                stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
                                                                typeabbrev = '".$_SESSION['ItemsNotes'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['ItemsNotes'.$identifier]->CurrAbrev."' and
                                                                (debtorno = '".$_SESSION['ItemsNotes'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['ItemsNotes'.$identifier]->Branch."' or branchcode = '')
                                WHERE stockmaster.stockid = disponibilidad.stockid AND tags.tagref=locations.tagref AND 
                                stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid";
                                //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                                $SQL = $SQL . " AND stockmaster.mbflag <>'G' AND 
                                sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
                                stockmaster.stockid " . LIKE . " '%". $SearchString ."%' AND stockmaster.discontinued=0";
                        if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                                $SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
                        }
                        if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                              //  $SQL= $SQL. " AND locstock.loccode ='". $_SESSION['loccode']."'";	
                        }
                        //PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
                        if (true){
                                $SQL= $SQL. " AND tags.legalid ='". $LegalID."'";	
                        }
                        
                        
                        $SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
                        $SQL = $SQL. " GROUP BY stockmaster.stockid,
                                        stockmaster.description,
                                        stockmaster.units,
                                        tags.legalid,
                                        disponibilidad.disponible,
                                        stockcategory.redinvoice";
                        if ($_SESSION['FindByStockRelation']==1){
                        	$SQL = $SQL.$sqlrelations;
                        	 $SQL = $SQL. " 
                                ORDER BY categorydescription,disponible desc,price, stockid, loccode";
                              
                        }else{
                        	 $SQL = $SQL. " 
                                ORDER BY stockcategory.categorydescription,disponibilidad.disponible desc,price, stockmaster.stockid, locstock.loccode";
                      }
                         
                                
                } else {
                        
                        /*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
                        $SQL = "SELECT stockmaster.stockid, 
										stockmaster.description, 
										stockmaster.units,
										tags.legalid, 
										sum(locstock.quantity) as quantity , 
										disponibilidad.disponible,
										stockcategory.redinvoice,
										COALESCE(MIN(prices.price),9999999) as price,
										sum(locstock.ontransit) as ontransit,
										stockcategory.categorydescription,
										locstock.loccode
                                FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity-locstock.ontransit) as disponible
                                FROM stockmaster, stockcategory ,locstock,sec_loccxusser , sec_stockcategory
                                WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid
                                        AND sec_stockcategory.categoryid= stockcategory.categoryid
                                        AND sec_stockcategory.userid='".$_SESSION['UserID']."'
                                        AND sec_loccxusser.userid='".$_SESSION['UserID']."'
                                        ";
                                //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                        $SQL = $SQL . " AND stockmaster.mbflag <>'G' AND 
                                sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
                                stockmaster.stockid " . LIKE . " '". $SearchString ."'";
                                                if (strlen($_POST['Autor'])>0 ){
                                                        $SQL= $SQL. " AND stockmaster.stockautor like '%". $_POST['Autor']."%'";	
                                                }
                                                if (strlen($_POST['Editorial'])>0 ){
                                                        $SQL= $SQL. " AND stockmaster.manufacturer like '%". $_POST['Editorial']."%'";	
                                                }
			$SQL =$SQL."   AND stockmaster.discontinued=0";
                        if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                                $SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
                        }
                        if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                            //    $SQL= $SQL. " AND locstock.loccode ='". $_SESSION['loccode']."'";	
                        }
                        $SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
                        $SQL = $SQL. "
                                GROUP BY stockmaster.stockid
                                ) as disponibilidad, stockcategory ,locstock,sec_loccxusser, locations,tags,
                                stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
                                                                typeabbrev = 'PL' and currabrev = 'MXN' and
                                                                debtorno = '' and branchcode = ''									
                                WHERE stockmaster.stockid = disponibilidad.stockid AND tags.tagref=locations.tagref AND 
                                stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid ";
                                //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                $SQL = $SQL . "	AND stockmaster.mbflag <>'G' AND 
                                sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
                                stockmaster.stockid " . LIKE . " '". $SearchString ."' AND stockmaster.discontinued=0";
                        if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                                $SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
                        }
                        if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                                $SQL= $SQL. " AND locstock.loccode ='". $_SESSION['loccode']."'";	
                        }
                        //PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
                        if (true){
                                $SQL= $SQL. " AND tags.legalid ='". $LegalID."'";	
                        }
                        
                        
                        $SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
                        $SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
                        $SQL = $SQL. " GROUP BY stockmaster.stockid,
                                        stockmaster.description,
                                        stockmaster.units,
                                        tags.legalid,
                                        disponibilidad.disponible,
                                        stockcategory.redinvoice";
                        
                        if ($_SESSION['FindByStockRelation']==1){
                        	$SQL = $SQL.$sqlrelations;
                        	$SQL = $SQL. "
                                ORDER BY categorydescription,disponible desc,price, stockid, loccode";
                        
                        }else{
                        	$SQL = $SQL. "
                                ORDER BY stockcategory.categorydescription,disponibilidad.disponible desc,price, stockmaster.stockid, locstock.loccode";
                        }
                        
                                
                }

        } else {
                if ($_SESSION['ProductSearch']==0 and ($_POST['StockCat']=='All' or strlen($_POST['StockCat'])<=0 or $_POST['StockLock']!='All')){
                        //if ($_POST['StockCat']=='All' or strlen($_POST['StockCat'])<=0){
                                /* BUSCAR TODOS LOS PRODUCTOS, NO BUEN A IDEA */
                       /* if (strlen($_POST['Keywords']) > 0) {
                                ?><script type="text/javascript">
                                        alert("Escribe por lo menos tres caracteres en campo de descripcion para evitar que la busqueda tarde mucho tiempo...");
                                </script><?
                                exit;
                        } else {
                                
                                ?><script type="text/javascript">
                                        alert("Selecciona alguno de los criterios de busqueda para evitar que la busqueda tarde mucho tiempo...");
                                </script><?
                                exit;
                        }*/
                        //}	
                      
                	
                	$SQL = "SELECT stockmaster.stockid,
										stockmaster.description,
										stockmaster.units,
										tags.legalid,
										sum(locstock.quantity) as quantity ,
										disponibilidad.disponible,
										stockcategory.redinvoice,
										COALESCE(MIN(prices.price),9999999) as price,
										sum(locstock.ontransit) as ontransit,
										stockcategory.categorydescription,
										locstock.loccode
                                FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity-locstock.ontransit) as disponible
                                FROM stockmaster, stockcategory ,locstock,sec_loccxusser, sec_stockcategory
                                WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid
                                        AND sec_stockcategory.categoryid= stockcategory.categoryid
                                        AND sec_stockcategory.userid='".$_SESSION['UserID']."'
                                        AND sec_loccxusser.userid='".$_SESSION['UserID']."'
                                        ";
                	//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                	$SQL = $SQL . " AND stockmaster.mbflag <>'G' AND
                                sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND
                                stockmaster.discontinued=0";
                	if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                		$SQL= $SQL. " AND locstock.loccode like '%". $_POST['StockLock']."%'";
                	}
                	
                	//$SQL= $SQL. " AND stockmaster.categoryid like '%" . $_POST['StockCat'] . "%' ";
                	$SQL = $SQL. "
                                GROUP BY stockmaster.stockid
                                ) as disponibilidad, stockcategory ,locstock,sec_loccxusser, locations inner join tags on tags.tagref=locations.tagref,
                                stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
                                                                typeabbrev = 'PL' and currabrev = 'MXN' and
                                                                debtorno = '' and branchcode = ''
                                WHERE stockmaster.stockid = disponibilidad.stockid AND
                                stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid ";
                	//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                	$SQL = $SQL . "	AND stockmaster.mbflag <>'G' AND
                                sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND
                                stockmaster.discontinued=0";
                	if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                		$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";
                	}
                	
                	//PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
                	if (true){
                		$SQL= $SQL. " AND tags.legalid ='". $LegalID."'";
                	}
                	
                	
                	//$SQL= $SQL. " AND stockmaster.categoryid like '%" . $_POST['StockCat'] . "%' ";
                	$SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
                	$SQL = $SQL. " GROUP BY stockmaster.stockid,
                                        stockmaster.description,
                                        stockmaster.units,
                                        tags.legalid,
                                        disponibilidad.disponible,
                                        stockcategory.redinvoice
                                        ";
                	if ($_SESSION['FindByStockRelation']==1){
                		$SQL = $SQL.$sqlrelations;
                		$SQL = $SQL. "
                                ORDER BY categorydescription,disponible desc,price, stockid, loccode";
                	
                	}else{
                		$SQL = $SQL. "
                                ORDER BY stockcategory.categorydescription,disponibilidad.disponible desc,price, stockmaster.stockid, locstock.loccode";
                	}
                	 
                	
                	//echo '<pre>sql:'.$SQL;
                                
                } else {
                        if ($_SESSION['ProductSearch']==1 and $_POST['StockCat']=='All'){
                                $_POST['StockCat']="";
                        }
                        /*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
                        $SQL = "SELECT stockmaster.stockid, 
										stockmaster.description, 
										stockmaster.units,
										tags.legalid, 
										sum(locstock.quantity) as quantity , 
										disponibilidad.disponible,
										stockcategory.redinvoice,
										COALESCE(MIN(prices.price),9999999) as price,
										sum(locstock.ontransit) as ontransit,
										stockcategory.categorydescription,
										locstock.loccode
                                FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity-locstock.ontransit) as disponible
                                FROM stockmaster, stockcategory ,locstock,sec_loccxusser, sec_stockcategory
                                WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid
                                        AND sec_stockcategory.categoryid= stockcategory.categoryid
                                        AND sec_stockcategory.userid='".$_SESSION['UserID']."'
                                        AND sec_loccxusser.userid='".$_SESSION['UserID']."'
                                        ";
                                //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                        $SQL = $SQL . " AND stockmaster.mbflag <>'G' AND 
                                sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
                                stockmaster.discontinued=0";
                        if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                                $SQL= $SQL. " AND locstock.loccode like '%". $_POST['StockLock']."%'";	
                        }
                        if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                               // $SQL= $SQL. " AND locstock.loccode ='". $_SESSION['loccode']."'";	
                        }
                        $SQL= $SQL. " AND stockmaster.categoryid like '%" . $_POST['StockCat'] . "%' ";
                        $SQL = $SQL. "
                                GROUP BY stockmaster.stockid
                                ) as disponibilidad, stockcategory ,locstock,sec_loccxusser, locations inner join tags on tags.tagref=locations.tagref,
                                stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
                                                                typeabbrev = 'PL' and currabrev = 'MXN' and
                                                                debtorno = '' and branchcode = ''									
                                WHERE stockmaster.stockid = disponibilidad.stockid AND 
                                stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid ";
                                //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
                        $SQL = $SQL . "	AND stockmaster.mbflag <>'G' AND 
                                sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
                                stockmaster.discontinued=0";
                        if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
                                $SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
                        }
                        if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                                $SQL= $SQL. " AND locstock.loccode ='". $_SESSION['loccode']."'";	
                        }
                        //PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
                        if (true){
                                $SQL= $SQL. " AND tags.legalid ='". $LegalID."'";	
                        }
                        
                        
                        $SQL= $SQL. " AND stockmaster.categoryid like '%" . $_POST['StockCat'] . "%' ";
                        $SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
                        $SQL = $SQL. " GROUP BY stockmaster.stockid,
                                        stockmaster.description,
                                        stockmaster.units,
                                        tags.legalid,
                                        disponibilidad.disponible,
                                        stockcategory.redinvoice
                                        ";
                        if ($_SESSION['FindByStockRelation']==1){
                        	$SQL = $SQL.$sqlrelations;
                        	$SQL = $SQL. "
                                ORDER BY categorydescription,disponible desc,price, stockid, loccode";
                        
                        }else{
                        	$SQL = $SQL. "
                                ORDER BY stockcategory.categorydescription,disponibilidad.disponible desc,price, stockmaster.stockid, locstock.loccode";
                        }
                       
                  }
        }
                
        //echo $SQL;
       //echo "<pre>sql:".$SQL."<br>";
        //exit;
        
        if (isset($_POST['NextO'])) {
                $Offset = $_POST['nextlistO'];
        }
        if (isset($_POST['PrevO'])) {
                $Offset = $_POST['previousO'];
        }
        if (isset($_POST['Go1'])){
                $Offset = $_POST['Offset1'];
        }
        if (!isset($Offset) or $Offset<0) {
                $Offset=0;
        }
        if (!isset($_POST['num_reg'])){
                $num_reg=$_SESSION['DisplayRecordsMax'];
        }else{
                $num_reg=$_POST['num_reg'];
        }
        //echo "<br>sql:<br>".$SQL;
        $SearchResult = DB_query($SQL,$db);
        $ListCount=DB_num_rows($SearchResult);
        $ListPageMax=ceil($ListCount/$num_reg);
        //$SQL = $SQL . ' LIMIT ' . $_SESSION['DisplayRecordsMax'].' OFFSET '.number_format($_SESSION['DisplayRecordsMax']*$Offset);
        $SQL = $SQL . ' LIMIT '.$num_reg.' OFFSET '. number_format($Offset * $num_reg) ;
        
        $ErrMsg = _('Existe un problema de seleccion de  para mostrar parte por que');
        $DbgMsg = _('El SQL usado para obtener producto fue');
        $SearchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);
        $SearchResultArray = DB_query($SQL,$db,$ErrMsg, $DbgMsg);
       
        
        if (DB_num_rows($SearchResult)==0 ){
                prnMsg (_('No hay productos disponibles que cumplan los criterios especificados'),'info');
        }
        if (DB_num_rows($SearchResult)==1){
                $myrow=DB_fetch_array($SearchResult);
                $NewItem = $myrow['stockid'];
                DB_data_seek($SearchResult,0);
        }
        if (DB_num_rows($SearchResult)<$num_reg){
                $Offset=0;
        }
}elseif(isset($_POST['Search']) or isset($_POST['NextO']) or isset($_POST['PrevO']) or isset($_POST['Go1']) and $_SESSION['MultipleBilling']==1){//cambia por tipo de facturacion multiple
        
}
//end of if search

//echo "<pre>".$SQL;

//*************************************Mostrar productos despues de la busqueda
if (isset($SearchResult)) {
        echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'&identifier='.$identifier . ' method=post name="orderform">';
        echo '<table width=100% ><tr><td colspan=6>';
        
        echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ';
        echo _('Seleccion de Productos') . '</p>
                <input type=hidden name="lineaxs" value='.$totallineas.'>
                </div>';
        echo '<td></tr>';
        if ($ListPageMax >1) {
                if ($Offset==0){
                        $Offsetpagina=1;	
                } else {
                        $Offsetpagina=$Offset+1;
                }
                echo '<tr>
                        <td colspan=6 style="text-align:center;"><b>'.$Offsetpagina. ' </b> ' . _('de') . '<b> ' . $ListPageMax . ' </b> ' . _('Paginas') . '.  ' . _('Ir a la Pagina') . ':';
                        echo '<select name="Offset1">';
                                $ListPage=0;
                                while($ListPage < $ListPageMax) {			
                                        if ($ListPage == $Offset) {
                                                echo '<option VALUE=' . $ListPage . ' selected>' . ($ListPage+1) . '</option>';
                                        } else {
                                                echo '<option VALUE=' . $ListPage . '>' . ($ListPage+1) . '</option>';
                                        }
                                                $ListPage++;
                                                $Offsetpagina=$Offsetpagina+1;
                                        }
                                echo '</select>
                        
                        <input type="text" name="num_reg" size=4 value="' .$num_reg. '">
                                <input type=submit style="width:50px;" name="Go1" size=5 VALUE="' . _('IR') . '">
                        </td></tr>';
        }
        echo '<tr><td><input type="hidden" name="previousO" value='.number_format($Offset-1).'><input tabindex='.number_format($j+7).' type="submit" name="PrevO" value="'._('Anterior').'"></td>';
        echo '<td style="text-align:center" colspan=2><input type="hidden" name="nextlistO" value='.number_format($Offset+1).'><input tabindex='.number_format($j+9).' type="submit" name="NextO" value="'._('Siguiente').'"></td>';
        echo '<td colspan=4><input type="hidden" name="order_items" value=1><input tabindex='.number_format($j+8).' type="submit" value="'._('Ordenar').'">';
        echo '<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'">';
        echo '<input type="hidden" name="StockLock" value="'.$_POST['StockLock'].'">';
        echo '<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'">';
        echo '<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'">';
        echo '</td></tr>';
        // array para rowspan por codigooooo
        $xsuc=0;
        $vertabla2=0;
        $listaloccxstock=array();
        $counter_sucbus=0;

        while ($myrows=DB_fetch_array($SearchResultArray)) {
                $stsucursal=$myrows['stockid'];
                if ($xsuc!=0 and $stsucursal!=$stsucursalant){
                        $listaloccxstock[$counter_sucbus]=$xsuc;
                        $counter_sucbus=$counter_sucbus+1;
                        $xsuc=0;
                }
                $xsuc=$xsuc+1;
                $stsucursalant=$stsucursal;
        }
        $listaloccxstock[$counter_sucbus]=$xsuc+1;
        //Fin de array para colspan
        $TableHeader = '<tr><th style="font-face:verdana;font-size:10px">' . _('Codigo') . '</th> ';
        // Tabla con listado de precios
     if ($_SESSION['FindByStockRelation'] == 1){
        
        	$TableHeader =  $TableHeader. " <th style='font-face:verdana;font-size:10px'>Equivalente</th>
											<th style='font-face:verdana;font-size:10px'>Similares</th>
											<th style='font-face:verdana;font-size:10px'>Sugeridos</th>";
        }
        
         $TableHeader =  $TableHeader.'  <th style="font-face:verdana;font-size:10px" >' . _('Descripcion') . '</th>
                        <th style="font-face:verdana;font-size:10px">' . _('Almacen') . '</th>
                        <th style="font-face:verdana;font-size:10px">' . _('Tipo') . '</th>		
                        <th style="font-face:verdana;font-size:10px">' . _('Disp:') . '</th>';
         
        $TableHeader =$TableHeader .' <th style="font-face:verdana;font-size:10px">' . _('Cant:') . '</th>';
        $TableHeader =$TableHeader .' <th style="font-face:verdana;font-size:10px">' . _('%Desc') . '</th>';
        $TableHeader =$TableHeader .' <th style="font-face:verdana;font-size:10px">' . _('Div') . '</th>';
        
        $SQLprice =" SELECT distinct salestypes.typeabbrev,salestypes.sales_type
                FROM salestypes, sec_pricelist
                WHERE sec_pricelist.pricelist = salestypes.typeabbrev
                        AND sec_pricelist.userid='" . $_SESSION['UserID'] . "'
                        OR salestypes.typeabbrev='".$_SESSION['ItemsNotes'.$identifier]->DefaultSalesType."'
                ORDER BY salestypes.sales_type";
        $prices = DB_query($SQLprice,$db);
        $listaprecio=array();
        $listacolorprecio=array();
        $countlista=0;
        while ($myrows=DB_fetch_array($prices)) {
                $lprecio=$myrows['sales_type'];
                $abrelist=$myrows['typeabbrev'];
                $TableHeader =$TableHeader .' <th style="font-face:verdana;font-size:10px">
                <a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&identifier='.$identifier . '&lineaxs='.$totallineas.'&StockCat='.$_POST['StockCat'].'&StockLock='.$_POST['StockLock'].'&SalesType='.trim($myrows['typeabbrev']).'&StockCode='.trim($_POST['StockCode']).'&PartSearch=Yes&Search=Yes&Keywords='.$_POST['Keywords'].'">'.$lprecio . '</a></th>';
                $listaprecio[$countlista]=$abrelist;
                $countlista=$countlista+1;
        }
        $listapreciototal=$countlista;
        
        $TableHeader =$TableHeader .'</tr>';
        $j = 1;
        $k = 0; //row colour counter
        $sucursales='';
        $xsuc=1;
        $vertabla=0;
        $codigoactual="";
        echo $TableHeader;
        $counter_sucbus=0;
        $SQLLocations="SELECT locations.loccode,
                        locationname
                 FROM locations, sec_loccxusser, tags
                 WHERE locations.loccode=sec_loccxusser.loccode
                       AND sec_loccxusser.userid='".$_SESSION['UserID']."'
                       AND tags.tagref=locations.tagref
                       AND locations.temploc=0
                       AND tags.legalid = '" .  $LegalID . "'";
                    if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
                      //  $SQLLocations=$SQLLocations."AND locations.loccode = '" .  $_SESSION['loccode'] . "'";
                    }
                 $SQLLocations=$SQLLocations."ORDER BY locationname";
        //echo $SQLLocations;
        while ($myrow=DB_fetch_array($SearchResult)) {
                $loccationstock=$myrow['loccode'];
                $ImageSource = _('No Image');
                $redinvoice=$myrow['redinvoice'];
                $qohsql = "SELECT areacode FROM tags 
                           WHERE   tagref = '" .  $_SESSION['ItemsNotes'.$identifier]->Tagref . "'";
                $qohresult =  DB_query($qohsql,$db);
                $qohrow = DB_fetch_row($qohresult);
                //$qoh =  $qohrow[1];
                //$codigobranch=$qohrow[0];
                $codigoarea=$qohrow[0];
                
                if ($codigoactual!=$myrow['stockid']){
                        if ($xsuc!=1) {
                                echo "</td></tr>";
                        }
                }
                
                if ($codigoactual!=$myrow['stockid']){
                        if ($k==1) {$k=0;} else {$k=1;}; //CAMBIA EL COLOR DE LA LINEA EN CADA PRODUCTO !!
                }
                
                if ($k==1){
                        echo '<tr class="EvenTableRows">';
                        $classtd="EvenTableRows";
                } else {
                        echo '<tr class="OddTableRows">';
                        $classtd="OddTableRows";
                }
                
                if ($codigoactual!=$myrow['stockid']){
                        echo '<td style="text-align:center; font-face:verdana;font-size:10px; vertical-align:top;">
                                <a style="text-align:center; font-face:verdana;font-size:10px; vertical-align:top;" target="_blank" href="' . $rootpath . '/StockStatus.php?' . SID .'&identifier='.$identifier . '&StockID=' . $myrow['stockid'] . '&DebtorNo=' . $_SESSION['ItemsNotes'.$identifier]->DebtorNo . '">' . $myrow['stockid'] . '</a>
                        </td>';
                        if ($_SESSION['FindByStockRelation'] == 1){
                        
                        	$rel = 	$_POST['relacion'];
                        	$qry = "Select * FROM stockrelations where stockid = '".$myrow['stockid']."'
								Order by typerelation
								";
                        	$rs = DB_query($qry,$db);
                        	$relacion1="";
                        	$relacion2="";
                        	$relacion3="";
                        	while ($rows = DB_fetch_array($rs)){
                        		if ($rows['typerelation']==1)
                        			$relacion1.=$rows['stockidrel']."<br>";
                        		 
                        		if ($rows['typerelation']==2)
                        			$relacion2.=$rows['stockidrel']."<br>";
                        		 
                        		if ($rows['typerelation']==3)
                        			$relacion3.=$rows['stockidrel']."<br>";
                        	}
                        	echo "<td style='text-align:center;vertical-align:top;font-face:verdana;font-size:10px'>".$relacion1."</td>";
                        	echo "<td style='text-align:center;vertical-align:top;font-face:verdana;font-size:10px'>".$relacion2."</td>";
                        	echo "<td style='text-align:center;vertical-align:top;font-face:verdana;font-size:10px'>".$relacion3."</td>";
                        }
                        echo "<td style='font-face:verdana;font-size:12px' nowrap ><b>".$myrow['description']."</b></td>";
                        $counter_sucbus=$counter_sucbus+1;
                } else {
                        echo '<td></td><td></td>';
                }
                
                //}
                
                $sqlLL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
                        FROM salesorderdetails,
                             salesorders,tags
                        WHERE salesorders.orderno = salesorderdetails.orderno AND tags.tagref=salesorders.tagref
                       /* and salesorders.tagref='" . $_SESSION['ItemsNotes'.$identifier]->Tagref . "'*/ AND
                        salesorderdetails.completed=0 AND
                        salesorders.quotation=0 AND
                        salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
       
               $ErrMsg = _('The demand for this product from') . ' ' . $loccationstock . ' ' . _('cannot be retrieved because');
               $DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
       
               if (DB_num_rows($DemandResult)==1){
                 $DemandRow = DB_fetch_row($DemandResult);
                 $DemandQty =  $DemandRow[0];
               } else {
                 $DemandQty =0;
               }
                
                $stockid=$myrow['stockid'];
                
                //$Available = $myrow['quantity'];
                $Available = ($myrow['quantity']-$myrow['ontransit']) - $DemandQty;
                
                //echo 'entra'.$Available;
                $vertabla=$vertabla+1;

                if (in_array($codigobranch,$listaloccxbussines)){
                        $tipocaja="text";
                }else{
                        $tipocaja="text";
                }
                   echo'<td>';
                    $result2 = DB_query($SQLLocations,$db);
                    echo "<select name='stockbill". $j  . "'>";
                    while ($myrow2 = DB_fetch_array($result2)) {
                            echo "<option value=". $myrow2['loccode'] . ">" . $myrow2['locationname'];
                    }
                    $codigobranch="";
                    
                echo '</td>';
              //  echo "<td style='font-face:verdana;font-size:10px' nowrap class=".$classtd.">".$qoh."</td>";
				$qry = "Select mbflag FROM stockmaster
						WHERE stockid = '". $myrow['stockid']."'";
				$rs = DB_query($qry,$db);
				$row = DB_fetch_array($rs);
				$tipo = $row['mbflag'];
				
				$desc = "";
				switch ($tipo){
					case 'A':
						$desc = "Ensamblado";
						break;
					
					case 'K':
						$desc = "Kit";
						break;

					case 'M':
						$desc = "Fabricado/Manufacturado";
						break;
					
					case 'G':
						$desc = "Phantom";
						break;

					case 'B':
						$desc = "Comprado";
						break;
					
					case 'D':	
						$desc = "Servicio/Mano de Obra";
						break;
				}
				
                echo "<td style='text-align:center' title='$desc' class=".$classtd.">$tipo</td>";
             
                
                if ($Available > 0) {
                        echo "<td style='text-align:right' class=".$classtd."><b>".number_format($Available,0)."</b></td>";
                } elseif($myrow['redinvoice']==0) {
                        //echo "<td style='text-align:right' class=".$classtd.">-</td>";
                        echo "<td style='text-align:right' class=".$classtd."><b>".number_format($Available,0)."<b></td>";
                }else{
                        
                        echo "<td style='text-align:right' class=".$classtd."><b>-<b></td>";
                }
                
                echo '<td style="text-align:center">
                        <input class="number" onKeyPress="return restrictToNumbers(this, event)" tabindex='.number_format($j+7).' type="'.$tipocaja.'" size=4 name="itm'.$myrow['stockid'].'|'.$j.'" value=0>
                </td>';
                
                echo '<td style="text-align:center">
					<input class="number" onKeyPress="return restrictToNumbers(this, event)"  type="'.$tipocaja.'" size=4 name="itemdesc'.$myrow['stockid'].'" value=0>
				</td>';
                
                echo '<td style="text-align:center">
					<input type="checkbox" title="Dividir en partidas x cantidades unitarias"  name="itemchk'.$myrow['stockid'].'">
				</td>';
                
               
                
                //echo 'moneda'.$_SESSION['ItemsNotes'.$identifier]->CurrAbrev;
                //echo "<td style='text-align:right'>$".number_format($Pricex,2)."</td>";
                for($countlista=0;$countlista<$listapreciototal;$countlista++) {
                        $listapreciox=$listaprecio[$countlista];
                        
                        $Pricey = GetPriceWTAX($stockid, $_SESSION['ItemsNotes'.$identifier]->DebtorNo,$listapreciox,$_SESSION['ItemsNotes'.$identifier]->CurrAbrev, $codigoarea, $db);
                        
                        $separa = explode('|',$Pricey);
                        
                        $bgcolorlista = $separa[1];
                        
                        $Pricex = $separa[0];
                        
                        if ($_SESSION['ItemsNotes'.$identifier]->DefaultSalesType==$listapreciox){
                                if ($Pricex>0){
                                        $valorlista='<b>'.number_format($Pricex,2).'</b>';
                                } else{
                                        $valorlista='N/A';
                                }
                        }else{
                                if ($Pricex>0){
                                        $valorlista=number_format($Pricex,2);
                                } else{
                                        $valorlista='N/A';
                                }
                        }
                        
                        if ($bgcolorlista=='#FFFFFF') {
                                //SI ES FONDO BLANCO, Y SI ES LA LISTA SELECCIONADA SOMBREALA CON AMARILLO
                                if ($_SESSION['ItemsNotes'.$identifier]->DefaultSalesType!=$listapreciox){
                                        echo "<td style='font-face:verdana;font-size:10px;text-align:right;' class=".$classtd." >".$valorlista."</td>";
                                } else {
                                        echo "<td style='font-face:verdana;font-size:10px;text-align:right;background-color:#C9C900' class=".$classtd." >".$valorlista."</td>";
                                }
                        } else {
                                echo "<td style='font-face:verdana;font-size:10px;text-align:right;background-color:".$bgcolorlista."' class=".$classtd." >".$valorlista."</td>";
                        }
                        //echo $_SESSION['ItemsNotes'.$identifier]->DefaultSalesType;
                }
                
                if ($j==1) $jsCall = '<script  type="text/javascript">defaultControl(document.SelectParts.itm'.$myrow['stockid'].'|'.$codigobranch.');</script>';
                $codigoactual=$myrow['stockid'];
                $xsuc=$xsuc+1;
                $j++;
                
        }#fin de while de productos
        if ($xsuc!=1){
                echo "</tr></table></td></tr>";	
        }
        echo '</table></form>';
        echo $jsCall;
}#end if SearchResults to show
?>