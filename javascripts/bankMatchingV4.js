/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0!

var yyyy = today.getFullYear();
if(dd<10){
    dd='0'+dd;
} 
if(mm<10){
    mm='0'+mm;
} 
var today = dd+'-'+mm+'-'+yyyy;



function fnCuentaBanco(idAdd,idSel){
 	var datos=new Array();
 	dataObj = { 
	        proceso: 'getBanco',
	        legalid: $("#selectUnidadNegocio").val()
	      };

	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/bankMatchingV4Modelo.php",
	      data:dataObj,
	      async:false,
	      cache:false
	  })
	.done(function( data ) {

		
		
	    if(data.result){
	    	
	    info=data.contenido.DatosBanco;
	    html='';
	    for(i in info){
    		html+='<option select value="'+info[i].cuenta+'">'+info[i].cuenta+' '+info[i].banco+' </option> ';
    		//console.log(info[i].cuenta+info[i].banco);
    	}
    	var html1='';
    	html1+='<div class="col-xs-3 col-md-8"><select name="'+idSel+'" id="'+idSel+'" class="'+idSel+'">';
    	html1+=html;
    	html1+=' </select> </div>';
    	$("#"+idAdd).empty();
    	$("#"+idAdd).append('<div class="text-left col-xs-12 col-md-12"><div class="col-md-4"> <b>Banco :</b></div> '+html1+'</div>');

    	fnFormatoSelectGeneral("."+idSel);


	    }else{
	    	console.log("No se obtuvo datos para cuenta bancaria");
	    	
	    }
	})
	.fail(function(result) {
		console.log("Error al obtener cuenta bancaria");
	    console.log( result );
	    
	});
	return  datos;
 }


function fnTipoTransaccion(idAdd,idSel){
 	var datos=new Array();
 	dataObj = { 
	        proceso: 'getTipoTransaccion',
	        legalid: $("#selectUnidadNegocio").val()
	      };

	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/bankMatchingV4Modelo.php",
	      data:dataObj,
	      async:false,
	      cache:false
	  })
	.done(function( data ) {

		
		
	    if(data.result){
	    	
	    info=data.contenido.DatosTransaccion;
	    html='';
	    for(i in info){
    		html+='<option select value="'+info[i].tipo+'">'+info[i].nombre+' </option> ';
    		//console.log(info[i].cuenta+info[i].banco);
    	}
    	var html1='';
    	html1+='<div class="col-xs-3 col-md-8"><select name="'+idSel+'" id="'+idSel+'" class="'+idSel+'">';
    	html1+=html;
    	html1+=' </select> </div>';
    	$("#"+idAdd).empty();
    	$("#"+idAdd).append('<div class="text-left col-xs-12 col-md-12 pt20"><div class="col-md-4"> <b>Tipo:</b></div> '+html1+'</div>');

    	fnFormatoSelectGeneral("."+idSel);


	    }else{
	    	console.log("No se obtuvo datos para cuenta bancaria");
	    	
	    }
	})
	.fail(function(result) {
		console.log("Error al obtener cuenta bancaria");
	    console.log( result );
	    
	});
	return  datos;
 }


 function fnMovimientos(){
 	
 	//html='';



 }

 function fnBuscar(){
 	
 	muestraCargandoGeneral();

 	setTimeout(function(){  
 	$("#btnBoton").hide();
 	var datos=new Array();
 	dataObj = { 
	        proceso: 'buscar2',
	        type:  $("#Type").val(),
	        desde: $("#dateDesde").val(),
	        hasta: $("#dateHasta").val(),
	        bank:  $("#bancoselect").val()

	      };

	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/bankMatchingV4Modelo.php",
	      data:dataObj,
	      async:false,
	      cache:false
	  })
	.done(function( data ) {

	    if(data.result){
	    	  ocultaCargandoGeneral();
	    	
	  //   	$("#datosConsolidacion").empty();
			// $("#datosConsolidacion").append('<div class=""><table class="table">'+data.contenido.Tabla+'</table> </div>');
			fnLimpiarTabla('tablaEstados', 'datosEstados');


            columnasNombres = '';
            columnasNombres += "[";
            columnasNombres += "{ name: 'checarcc', type: 'bool'},";
            columnasNombres += "{ name: 'fecha', type: 'string' },";
            columnasNombres += "{ name: 'concepto', type: 'string'},";
            columnasNombres += "{ name: 'retiro', type: 'number'},";
            columnasNombres += "{ name: 'deposito', type: 'number' },";
          	columnasNombres += "{ name: 'idEstado', type: 'string' },";  
            columnasNombres += "{ name: 'conciliado', type: 'string' },";
            columnasNombres += "{ name: 'estatus', type: 'string' },";
            columnasNombres += "]";
            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: '', datafield: 'checarcc', width: '4%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
            columnasNombresGrid += " { text: 'Fecha',datafield: 'fecha', width: '20%', align: 'center',hidden: false,cellsalign: 'center' },";
           
            columnasNombresGrid += " { text: 'Retiro', datafield: 'retiro', width: '20%', cellsalign: 'center', align: 'center',hidden: false },";
            
            columnasNombresGrid += " { text: 'Deposito', datafield: 'deposito', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Estatus', datafield: 'estatus', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
            
            columnasNombresGrid += " { text: '', datafield: 'idEstado', width: '4%', cellsalign: 'center', align: 'center', hidden: true },";
             columnasNombresGrid += " { text: 'Concepto', datafield: 'concepto', width: '35%', cellsalign: 'center', align: 'center',hidden: false },";
            columnasNombresGrid += " { text: '', datafield: 'conciliado', width: '4%', cellsalign: 'center', align: 'center', hidden: false },";
            
            /*columnasNombresGrid += " { text: '',datafield: '', width: '10%', align: 'center',hidden: false,cellsalign: 'center' },";
            */
            columnasNombresGrid += "]";

            var columnasExcel = [ 1,2,3,4];
            var columnasVisuales = [0,1,2,3,4];
            nombreExcel = data.contenido.nombreExcel;

            fnAgregarGrid_Detalle(data.contenido.datos, columnasNombres, columnasNombresGrid, 'datosEstados', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
            // $('#tablaEstados > #datosEstados').jqxGrid({cellclassname: cellclass});



           	fnLimpiarTabla('tablaMov', 'datosMov');


            columnasNombres = '';
            columnasNombres += "[";
            columnasNombres += "{ name: 'checarcc1', type: 'bool'},";
            columnasNombres += "{ name: 'fecha', type: 'string' },";
            columnasNombres += "{ name: 'tipo', type: 'string'},";
            columnasNombres += "{ name: 'folio', type: 'string'},";
            columnasNombres += "{ name: 'monto', type: 'number' },";
          	columnasNombres += "{ name: 'concepto', type: 'string' },";
          	columnasNombres += "{ name: 'idBank', type: 'string' },"; 
            columnasNombres += "{ name: 'conciliado', type: 'string' },";   
            columnasNombres += "{ name: 'estatus', type: 'string' },"; 
          
            columnasNombres += "]";
            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: '', datafield: 'checarcc1', width: '4%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
            columnasNombresGrid += " { text: 'Fecha',datafield: 'fecha', width: '20%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Tipo', datafield: 'tipo', width: '15%', cellsalign: 'center', align: 'center',hidden: false },";
            columnasNombresGrid += " { text: 'Folio', datafield: 'folio', width: '15%', cellsalign: 'center', align: 'center',hidden: false },";
            
            columnasNombresGrid += " { text: 'Monto', datafield: 'monto', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Estatus', datafield: 'estatus', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Concepto', datafield: 'concepto', width: '30%', cellsalign: 'center', align: 'center', hidden: false },";
            
            

            columnasNombresGrid += " { text: '', datafield: 'idBank', width: '4%', cellsalign: 'center', align: 'center', hidden: true},";
            
            columnasNombresGrid += " { text: '', datafield: 'conciliado', width: '4%', cellsalign: 'center', align: 'center', hidden: false },";
            /*columnasNombresGrid += " { text: '',datafield: '', width: '10%', align: 'center',hidden: false,cellsalign: 'center' },";
            */
            columnasNombresGrid += "]";

            var columnasExcel = [ 1,2,3,4];
            var columnasVisuales = [0,1,2,3,4];
            nombreExcel = data.contenido.nombreExcel;

            fnAgregarGrid_Detalle(data.contenido.movBan, columnasNombres, columnasNombresGrid, 'datosMov', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
           
           $("#leyendaMovgrp").empty();
		   $("#leyendaEstado").empty();
		   $tipo=$("#Type").val();

		   if($tipo==1){
	        $("#leyendaMovgrp").append( '<h4>Movimientos bancarios conciliados</h4>');
		    $("#leyendaEstado").append('<h4>Estados de cuenta conciliados</h4>');
	         
	       }else if($tipo==2){
	     	 $("#leyendaMovgrp").append( '<h4>Movimientos bancarios sin conciliación</h4>');
		     $("#leyendaEstado").append('<h4>Estados de cuenta sin conciliación</h4>');
	       }else{
	         $("#leyendaMovgrp").append( '<h4>Movimientos bancarios</h4>');
		     $("#leyendaEstado").append('<h4>Estados de cuenta</h4>');

	       }
		   $("#btnBoton").show();

	    }else{
	    	console.log("No se obtuvo datos para cuenta bancaria");
	    	ocultaCargandoGeneral();
	    }
	})
	.fail(function(result) {
		console.log("Error al obtener cuenta bancaria");
	    console.log( result );
	    muestraModalGeneral(4, titulo,"Hubo un error al realizar la búsqueda.");
	    ocultaCargandoGeneral();
	    
	});
	return  datos;
	}, 1000);
 }

function fnChecarSeleccionados(celda,gridT,check,movOrEstado=0){
        var datos= new Array();
        var griddata = $(gridT).jqxGrid('getdatainformation');
        var dato='';
        var conciliado='';
        var fecha='';
        var monto='';
        var fechas= new Array();
        var retorno= new Array();
        var montos= new Array();


        for (var i = 0; i < griddata.rowscount; i++){
            checa=  $(gridT).jqxGrid('getcellvalue',i, check);
           

            if(checa==true){
             // alert($('#tablaTipos > #datosTipos').jqxGrid('getcellvalue',i,celda));
            dato= $(gridT).jqxGrid('getcellvalue',i,celda)
            conciliado=$(gridT).jqxGrid('getcellvalue',i, 'conciliado');
            fecha=$(gridT).jqxGrid('getcellvalue',i, 'fecha');

            if(movOrEstado==0){//estado
            	aux1= $(gridT).jqxGrid('getcellvalue',i, 'retiro');
            	aux2= $(gridT).jqxGrid('getcellvalue',i, 'deposito');
            	monto=aux1+aux2;
            	console.log(aux1+" depo");
            	console.log(aux2+" retiro");
            	
            	montos.push(monto);
            }else{
            	monto=$(gridT).jqxGrid('getcellvalue',i, 'monto');
            	montos.push(monto);
            }
            
              
              fechas.push(fecha);
              datos.push(dato);
            }
        }
        retorno.push(datos);
        retorno.push(conciliado);
        retorno.push(montos);
        retorno.push(fechas);
        
       	return retorno;
}
function  fnConcialiacion(estado,movsBanks){
 	var datos=new Array();
 	dataObj = { 
	        proceso: 'conciliar',
	        estado:estado,
	        movsBanks:movsBanks

	      };

	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/bankMatchingV4Modelo.php",
	      data:dataObj,
	      async:false,
	      cache:false
	  })
	.done(function( data ) {

	    if(data.result){
	    	muestraModalGeneral(4, titulo,data.contenido);

 	setTimeout(function(){  
	    	fnBuscar();}, 2000);
 	  
	    }else{
	    	console.log("No se obtuvo la  concialiacion  el estado con los movimientos");
	    	  muestraModalGeneral(4, titulo,"No se puedo conciliar intente de nuevo");
	    	
	    }
	})
	.fail(function(result) {
		console.log("Hubo un error al conciliar  el estado con los movimientos ");
	    console.log( result );
	    muestraModalGeneral(4, titulo,"Hubo un  error al tratar de conciliar.");
	    
	});
}
 $(document).ready(function(){

	fnCuentaBanco("banco","bancoselect");
	fnTipoTransaccion("tipotransaccion","tiposelect");
	$("#dateDesde").val(""+today); 
	$("#dateHasta").val(""+today);
	
 	fnFormatoSelectGeneral(".typemov");

	 $("#btnBuscar").click(function(){
	 	fnBuscar();

	 });

	 $("#btnCBank").click(function(){

	 	datos=fnChecarSeleccionados('idEstado','#tablaEstados > #datosEstados','checarcc',0);
	 	estados=datos[0];
	 	ce=datos[1];
	 	montoEstado=datos[2];
	 	fechaEstado=datos[3];
	 	console.log(montoEstado+" montoestado");
	 	
	 	// console.log(estados.length +" numero");
	 	console.log(estados+" estados");
	 	console.log(ce+ " estado conciliado");
	 	if(estados.length>1){
	 	 	muestraModalGeneral(4, titulo,"No se puede seleccionar mas de un estado de cuenta para concialición.");
	 	}else if(estados.length==1){

	 		if(ce!='1'){
	 			console.log(ce+ "estado conc");
		 		datos=fnChecarSeleccionados('idBank','#tablaMov > #datosMov','checarcc1',1);
		 		movsBanks=datos[0];
		 		cm=datos[1];
		 		montoMov=datos[2];
		 		fechaMov=datos[3];
		 		if(movsBanks.length==1){
		 				console.log(cm +"estado movimiento");
		 			if(cm=='0'){
		 				montoEstado="'"+montoEstado+"'";
		 				montoMov="'"+montoMov+"'";
		 				fechaEstado="'"+fechaEstado+"'";
		 				fechaMov="'"+fechaMov+"'";
		 				// console.log((montoEstado + montoMov) );
		 				// console.log("monto total");
		 					if(montoEstado===montoMov &&(fechaEstado===fechaMov)){
		 						fnConcialiacion(estados,movsBanks);
				 				// console.log(movsBanks+"movimientos");
		 					}else{

		 						muestraModalGeneral(4, titulo,"No coincide monto o fecha entre estado y movimiento bancario no se puede conciliar.");

		 					}
		 						console.log(montoEstado+" "+montoMov);
				 			
		 				}else{
		 					muestraModalGeneral(4, titulo,"El movimiento bancario seleccionado ya fue conciliado.");
		 				}

		 		}else if(movsBanks.length>1){
		 			muestraModalGeneral(4, titulo,"No se puede seleccionar mas de un movimiento bancario para concialición.");
		 		}else{
		 			muestraModalGeneral(4, titulo,"Seleccione  un movimiento bancario.");
		 		}
	 		}else{
	 			muestraModalGeneral(4, titulo,"El estado de cuenta seleccionado ya fue conciliado.");
	 		}
	 		
	 		
	 	}else{
	 		muestraModalGeneral(4, titulo,"Seleccione  un  estado de cuenta para concialición.");
	 	}

	 	

	 });

 });

 /*
  columnasNombres += "{ name: 'fecha', type: 'string' },";
          
  */
$(document).on('cellbeginedit', '#datosMov', function(event) {
   // $(this).jqxGrid('setcolumnproperty', 'checkBoxProv', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'tipo', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'folio', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'monto', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'concepto', 'editable', false);
    
});

$(document).on('cellbeginedit', '#datosEstados', function(event) {
   // $(this).jqxGrid('setcolumnproperty', 'checkBoxProv', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'concepto', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'retiro', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'deposito', 'editable', false);
    
});

// $(document).on('cellselect', '#tablaTipos > #datosTipos', function(event) {
  
// });