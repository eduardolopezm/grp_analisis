/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */

var transnoTransfer=[];
var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
$( document ).ready(function() {
	//fijar fecha desde el servidor
	fnFijarFecha();

	setTimeout(function(){ fnBuscarDatos(1); }, 10);

	if (document.querySelector(".selectOperacionTesoreria")) {
      // Muestra los tipos de operación para un compromiso
      dataObj = {
            option: 'mostrarPagosTesoreria'
        };
      fnSelectGeneralDatosAjax('.selectOperacionTesoreria', dataObj, 'modelo/componentes_modelo.php', 0);
    }

	$("#btnVerificarRadicado").click(function(){
		muestraCargandoGeneral();
		setTimeout(function(){
			datos=fnChecarSeleccionadosPagos();
			requis=datos[9];
			fechas=datos[10];
			fnVerificarRadicado(requis,fechas);
		}, 1000);
	});

	$('#btnJusCancel').click(function(){
		datos=$('#textAreaJusCan').val();
		if(datos.length>0 && (datos!='') ) {
			if($('#selectTypeCancel').val()!=0){
				fnCancelarCheque();
			}else{
				$('#ModalCancelarCh').modal('hide');
				muestraModalGeneral(3, titulo,'Necesita seleccionar el tipo cancelación');
			}
		} else {
			$('#ModalCancelarCh').modal('hide');
			muestraModalGeneral(3, titulo,'Se necesita una justificación mínima de 50 caracteres');
		}
	});

	$('#btnGenerarCheque').click(function(){
		fnNuevoFolio();
	});
}); 

function fnChecarSeleccionadosPagos(){
    var id='';
    var fila='';
    var arrayId=[];
    var arrayFila=[];
    var arrayRequi=[];
    var arrayTagrefs=[];
    var arrayMontos=[];
    var arrayProv=[];
    var arrayFact=[];
    var facturas=[]; 
    var folio='';
    var banco='';
    var sn='';
    var typeOp='';
    var typeOpName='';
    var arraySn=[];
    var arrayfolios=[];
	var arrayBancos=[];
	var arrayRequisSinliga=[]
	var arrayFechas=[];
	var arrayTypes=[];
	var arrayTypesName=[];
    var griddata = $('#divTabla > #divDatos').jqxGrid('getdatainformation');
  	var contadorSelecc=0;  
  
    for (var i = 0; i < griddata.rowscount; i++) {
        checkpagos = $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'checkPagos');
        if(checkpagos==true){
        	datos = $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'id');
        	tagref= $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'un');
        	monto=  $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'ovat');
        	prov=   $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'idprov');
        	factura= $('#divTabla > #divDatos').jqxGrid('getcellvalue',i,'fact');
        	// si el folio de repite  quiere decir que unificado
        	folio= $('#divTabla > #divDatos').jqxGrid('getcellvalue',i,'fo2');
			banco= $('#divTabla > #divDatos').jqxGrid('getcellvalue',i,'bancoOrigen');
			requiSinliga= $('#divTabla > #divDatos').jqxGrid('getcellvalue',i,'requiSinliga');
			fecha=$('#divTabla > #divDatos').jqxGrid('getcellvalue',i,'fecha');
			sn=$('#divTabla > #divDatos').jqxGrid('getcellvalue',i,'estatusN');
			typeOp=$('#divTabla > #divDatos').jqxGrid('getcellvalue',i,'type');
			typeOpName=$('#divTabla > #divDatos').jqxGrid('getcellvalue',i,'tipoOperacion');
        
        	temporal=datos.split("-");
        	
        	arrayId.push(temporal[0]); //0
        	arrayFila.push(temporal[1]); //1
        	arrayRequi.push(temporal[2]); //2
        	arrayMontos.push(monto); //3
        	arrayTagrefs.push(tagref); //4
        	arrayProv.push(prov);  //5
        	arrayFact.push(factura); //6
        	arrayfolios.push(folio); //7
			arrayBancos.push(banco); //8
			arrayRequisSinliga.push(requiSinliga);
			arrayFechas.push(fecha);
        	arraySn.push(sn);
        	arrayTypes.push(typeOp);
        	arrayTypesName.push(typeOpName);
        	contadorSelecc++;
        }
    }
   	//console.log(contadorSelecc);
    //if(contadorSelecc<=1){

  	facturas.push(arrayId); //0
  	facturas.push(arrayFila); //1
  	facturas.push(arrayRequi); //2
  	facturas.push(arrayTagrefs); //3
  	facturas.push(arrayMontos); //4
  	facturas.push(arrayProv); //5
  	facturas.push(arrayFact); //6
  	facturas.push(arrayfolios);//7
	facturas.push(arrayBancos);//8
	facturas.push(arrayRequisSinliga);//9
	facturas.push(arrayFechas);//10  
	facturas.push(arraySn);//11
	facturas.push(arrayTypes);//12
	facturas.push(arrayTypesName);//13

	/*}else{

	} */
	// fin validacion de solo una

   return facturas;//solicitudes;
}

function fnVerificar(estadoAnterior,estado2=0){
		//estadoAnterior es la variable  ParaPoderCambiarEstado
		var fila=[];
		var datos=[];
		var filas=[];
		var folios=[];
		var montos=[];
		var filas=[];
		var ids=[];
		var id='';
		var estado='';
		var mensaje="";
		var puedoAvanzar=false;
		var respuesta=[];
		var contadorCuantosAvanzar=0;
 		datos=fnChecarSeleccionadosPagos();
 
		/*$("input:checkbox[class=selMovimiento]:checked").each(function () {
		      id= $(this).val();
		      fila =  $(this).attr('id');
		 	  fila=fila.replace("selMInput","");
		}); */
  		//console.log(fila[0]+" " +fila[1]);
  		if(datos.length>0){

  		ids=datos[0];
  		filas=datos[1];
  		requis=datos[2];
  		folios=datos[7];
  		montos=datos[4];
  
  		//console.log(ids[0]+" " +filas[0]);
  		//console.log(filas+ "filas");
  		
  		for(a=0;a<ids.length;a++){
		if(filas[a].length<=0){
				
			    	// muestraModalGeneral(3, titulo,'Seleccioné una factura.');
			    	//mensaje="";
		}else{
			estado =  $('#divTabla > #divDatos').jqxGrid('getcellvalue',filas[a], 'estado');
			estado = estado.replace(/<\/?div[^>]*>/g,"");
			estado = estado.replace(/<\/?span[^>]*>/g,"");

			switch (estado){
					
					case 'Pendiente de pago':
					/*	
			    	 muestraModalGeneral(3, titulo,"Es necesario programar el pago."); */
			    	 mensaje+="<br><i class='glyphicon glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>"+"Es necesario programar el pago en requisición <b>"+(requis[a])+"</b>";

					break;
					case 'Programado':
					mensaje+="<br><i class='glyphicon glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>"+"El pago ha sido programado en requisición <b>"+(requis[a])+"</b>";
					break;
					case 'Autorizado':
					mensaje+="<br><i class='glyphicon glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>"+'Ya fue autorizado el pago en requisición <b>'+(requis[a])+"</b>";
					break;
					case 'Finalizado':
					mensaje+="<br><i class='glyphicon glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>"+"El pago ya fue hecho en requisición <b>"+(requis[a])+"</b>";
					break;

					case 'enviado a SICOF':
					
					break;
					
						
				}

				if(estadoAnterior==estado){
				
					contadorCuantosAvanzar++;
				}
				if(estado2!=0){

					if(estado2==estado){
						contadorCuantosAvanzar++;
					}
				}
		}// fin else
		
	} // fin for
		
		if(contadorCuantosAvanzar==ids.length && (contadorCuantosAvanzar!=0) ){
			puedoAvanzar=true;
		}
	

		//  posicionElminar=[];
		//  temp='';
		//  foliosChecar=[];
		
		//  idsEnviar=[];
		//  idsFinales=[];
		//  unicos=[];

		//  //separo folio  y id
		//  for(j=0;j<folios.length;j++){
		//  	temp=folios[j].split("-");
		//  	foliosChecar.push(temp[0]);
		//  	idsEnviar.push(temp[1]);
		//  }
		//  //  checa quue folios se repiten  y anoto la posicion
		
		//   $.each(foliosChecar, function(i, el){

  //    		if($.inArray(el,unicos) === -1){ // solo dejar estos
  //    		unicos.push(el);

  //    		} else{ // se repite  en
  //    			posicionElminar.push(i);
  //    		}
		// }); 

		  
		//   //elmino del array los ids que se repiten 
		//   if(posicionElminar.length>0){

		//   for(x=0;x<posicionElminar.length;x++){
		//   	//idsEnviar.indexOf(x);
		//   	idsEnviar.splice(x, 1);
		//   	console.log(" "+" se repite-->"+posicionElminar[x]);
		  	
		//   	}
		//   }

		//    for(a=0;a<idsEnviar.length;a++){
		//   	console.log(idsEnviar[a] +" a enviar-->");
		//   }
		// console.log(idsEnviar[a] +"a enviar");
			}else{
					mensaje="Seleccioné una factura";
			}
		respuesta.push(puedoAvanzar); //0
		respuesta.push(mensaje); //1
		//respuesta.push(idsEnviar); //2
		//respuesta.push(posicionElminar);// 3 
		
		respuesta.push(ids); //2 //antes de unificados
		respuesta.push(montos); //3
		respuesta.push(folios); //4
		respuesta.push(datos[8]); //5
		respuesta.push(datos[10]);// 6
		respuesta.push(datos[11]);//7

		return (respuesta);

}

function fnFijarFecha(){

	dataObj = { 
	        proceso: 'getFechaServidor',
	 
	      };
	
	$.ajax({
          async:false,
          cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.Fecha;
	    
			$("#fechaCancelar").val(""+info[0].fechaDMY); 
			
            $("#dateDesde").val(""+info[0].fechaDMY); 
			$("#dateHasta").val(""+info[0].fechaDMY); 
			$("#desdeCancelar").val(""+info[0].fechaDMY);
			$("#hastaCancelar").val(""+info[0].fechaDMY);
	    	/*$("#dateHasta").val(""+info[0].fechaDMY); 
	    	*/

	    	 //var mes = ("0" + (this.getMonth() + 1)).slice(-2);
	    	 var d = new Date();
	    	 var n = d.getMonth();
	    	 n+=1;
	    	 for(i=1;i<=9;i++)
	    	 {
	    	 
	    	 	if(n==i)
	    	 	{
	    	 		n="0"+i;

	    	 	}
	    	 }
			 mes = n;
			 var today = new Date();
			 var ultimodia = new Date(today.getFullYear(), today.getMonth()+1, 0);
			 ultimodia=ultimodia.getDate();
			
			 
	    	
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});


}

function fnBuscarDatos(busqueda=0){
	muestraCargandoGeneral();
	$("#divDatos").fadeOut();
	$("#btnBuscar").fadeOut();
	var dateDesde  = $('#dateDesde').val();
	var dateHasta  = $('#dateHasta').val();
	var recibo     = $('#txtRecibo').val();
	var proveedor  = $('#txtProv').val();
	var recibo 	   = $('#txtReciboNo').val();
	//var razonSocial=$( "#selectRazonSocial option:selected" ).val();
	var razonUnidadNegocio=$("#selectUnidadNegocio option:selected" ).val();
	var tipoDocumento=$("#selectTipoDocumentoPagosaProveedores option:selected" ).val();
	var estatusTesoSelect=$("#estatusTesoSelect option:selected" ).val();

	var tipoOperacion = "";
	var selectOperacionTesoreria = document.getElementById('selectOperacionTesoreria');
    for ( var i = 0; i < selectOperacionTesoreria.selectedOptions.length; i++) {
        if (i == 0) {
            tipoOperacion = "'"+selectOperacionTesoreria.selectedOptions[i].value+"'";
        }else{
            tipoOperacion = tipoOperacion+", '"+selectOperacionTesoreria.selectedOptions[i].value+"'";
        }
    }

    var tagref = "";
	var selectUnidadNegocio = document.getElementById('selectUnidadNegocio');
    for ( var i = 0; i < selectUnidadNegocio.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            tagref = "'"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }else{
            tagref = tagref+", '"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }
    }

    var ue = '';
	if ($('#selectUnidadEjecutora').val() != '-1') {
		ue = $('#selectUnidadEjecutora').val();
	}

	dataObj = { 
	        proceso: 'buscarDatos',
	        dateDesde:dateDesde,
	        dateHasta:dateHasta,
	        recibo:recibo,
	        proveedor:proveedor,
	        //razonSocial:razonSocial,
	        razonUnidadNegocio:razonUnidadNegocio,
	        tipoDocumento:tipoDocumento,
	        estatus:estatusTesoSelect,
	        tipoOperacion: tipoOperacion,
	        noCompromiso: $('#txtNoCompromiso').val().trim(),
	        noDevengado: $('#txtNoDevengado').val().trim(),
	        reciboTransferencia: $('#txtReciboNoTransferencia').val().trim(),
	        tagref: tagref,
	        ue: ue
	      };
	
	$.ajax({
          async:false,
          cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
	      data:dataObj


	  })
	.done(function( data ) {
		
	 if(data.result){
	    	//Si trae informacion
	   fnLimpiarTabla('divTabla', 'divDatos');

	    var	datos=data.contenido.DatosPagos;
	    if(datos.length>0){
		

	    var	datosTotales=data.contenido.DatosTotales;
	   	var datosTotalesAplicados= data.contenido.DatosTotalesAplicados;
	   	var datosTotalesPendientes= data.contenido.DatosTotalesPendientes;


	   	dataFuncionJason = data.contenido.DatosPagos;
		columnasNombres = data.contenido.columnasNombres;
		columnasNombresGrid = data.contenido.columnasNombresGrid;
		var columnasDescartarExportar = [0];
	    	
	    var columnasDescartarExportar = [];
	    var columnasExcel= 	  [3, 4, 5, 7, 8, 9, 10, 12, 14, 15, 16, 17, 18, 19, 22, 23];
        var columnasVisuales= [0, 3, 4, 5, 6, 8, 9, 10, 11, 13, 15, 16, 17, 18, 19, 22, 23];

        nombreExcel=data.contenido.nombreExcel;
        
        fnAgregarGrid_Detalle(dataFuncionJason, data.contenido.columnasNombres, data.contenido.columnasNombresGrid, 'divDatos', ' ', 1, columnasExcel, false,true, '', columnasVisuales, nombreExcel);
 
        $('#divTabla > #divDatos').jqxGrid({columnsheight:'35px'});
	    //$('#divTabla > #divDatos').jqxGrid({width:'80%'});
 
	    if ($(".estadoEje")[0])
	    {
 			//existe
 			$('.estadoEje').css({background:"blue !important",color:"white !important"});
		} else {
  
		}
	    	/*fnEjecutarVueGeneral(); */
	    }else{
			fnLimpiarTabla('divTabla', 'divDatos');

			if(busqueda==0){
				muestraModalGeneral(3, titulo,'La búsqueda no obtuvo datos.');
			}else{

			}
			
	    	
	    }

	    	
	 }// si hay data

	         	
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});

ocultaCargandoGeneral();
	   $("#divDatos").fadeIn();
   	  $("#btnBuscar").fadeIn();
}

function fnProgramarPago(){
	datos=fnChecarSeleccionadosPagos();
	valores=$('#idpp').val();
	movimientos=valores.split(",");
	var transno=movimientos; //$('#idpp').val();
	//var BankAccount= $('#bankAccount').val();
	var Tipopago= $('#Tipopago').val();
	//var numchequeuser=$('#numchequeuser').val();
	var FechaPago=$('#FechaPago').val();
	//var UnificarPagoDescripcion= $('#UnificarPagoDescripcion').val();
	//alert(FechaPago);
	
	if ($('#txtReferenciaProgramar').val().trim() == '' || $('#txtReferenciaProgramar').val().trim() == null) {
		// Vacia la referencia
		var mensaje = 'Agregar referencia para continuar con el proceso';
		muestraMensajeTiempo(mensaje, 1, 'mensajesValidaciones', 10000);
		return true;
	}
	
	dataObj = { 
	        proceso: 'programarPago',
	        transno_act: transno,
	        urs:datos[3],
	        montos:datos[4],
	        FechaPago:FechaPago,
	        sn:datos[11],
	        referencia: $('#txtReferenciaProgramar').val().trim()
	        /*BankAccount:BankAccount,
	        Tipopago:Tipopago,
	        numchequeuser:numchequeuser,
	        FechaPago:FechaPago,
	        UnificarPagoDescripcion:UnificarPagoDescripcion */
	 		
	      };
	
	$.ajax({
          async:false,
          cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
	
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido;
			
	    	 muestraModalGeneral(3, titulo,info);
	    	//muestraMensaje(info, 1, 'OperacionMensaje', 5000);
	    	/*$("#dateDesde").val(""+info[0].fechaDMY); 
	    	$("#dateHasta").val(""+info[0].fechaDMY); */
	    	$('#ModalProgramarPago').modal('hide');
	    	fnBuscarDatos(1);
	    	$("#pagosnuevosDetectados").fadeIn("slow");
	    	$('#txtReferenciaProgramar').val('');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    //muestraMensaje('Error al programar fecha.',3, 'OperacionMensaje', 5000);
	  	muestraModalGeneral(3, titulo,'Error al programar fecha.');
	});
}

function fnProgramarPagoModal(){
	respuesta=  fnVerificar('Pendiente de pago'); //Pendiente de pago
	var  cadena='';
	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Programación del Pago </h3>';
	if(respuesta[0]==true){
		$("#btnFechaPago").prop('onclick', null);
		$("#btnFechaPago").attr('onclick', 'fnProgramarPago()');
		$("#facturaProgramar").empty();
		$("#mensajesValidaciones").empty();
		//$("#facturaProgramar").append('<h5 > Selecciona la fecha del pago</h5> ');
		$('#ModalProgramarPago_Titulo').empty();
		$('#ModalProgramarPago_Titulo').append(titulo);
		$('#ModalProgramarPago').modal('show');
		//onclick="fnProgramarPago()"
		//$("#btnFechaPago").prop('onclick', null);
		//array=respuesta[2];
		//$("#btnFechaPago").attr('onclick', 'fnProgramarPago(' + (array) + ')'); //.click('function(90'+datos);
		datos=respuesta[2];
		for(a=0;a<datos.length;a++){
			cadena+=datos[a]+",";
		}
		cadena=cadena.slice(0,-1);
		$("#idpp").val(""+cadena);
	} else{
		mensaje='Seleccioné una factura';
		muestraModalGeneral(3, titulo,mensaje+respuesta[1]+"");
	}
}

function fnReprogramar(){
	//datos=fnChecarSeleccionadosPagos();
	valores=$('#idpp').val();
	movimientos=valores.split(",");
	var transno=movimientos;
	var FechaPago=$('#FechaPago').val();
	var UnificarPagoDescripcion= $('#UnificarPagoDescripcion').val();

	if ($('#txtReferenciaProgramar').val().trim() == '' || $('#txtReferenciaProgramar').val().trim() == null) {
		// Vacia la referencia
		var mensaje = 'Agregar referencia para continuar con el proceso';
		muestraMensajeTiempo(mensaje, 1, 'mensajesValidaciones', 10000);
		return true;
	}

	dataObj = { 
		proceso: 'reprogramarPago',
		transno_act: transno,
		FechaPago:FechaPago,
		referencia: $('#txtReferenciaProgramar').val().trim()
	};
	$.ajax({
          async:false,
          cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		if(data.result){
			info=data.contenido;
			muestraModalGeneral(3, titulo,info);
			$('#ModalProgramarPago').modal('hide');
			fnBuscarDatos(1);
			$("#pagosnuevosDetectados").fadeIn("slow");
			$('#txtReferenciaProgramar').val('');
		}
	})
	.fail(function(result) {
		//console.log(ErrMsg);
		console.log( result );
		//muestraMensaje('Error al programar fecha.',3, 'OperacionMensaje', 5000);
		muestraModalGeneral(3, titulo,'Error al programar fecha.');
	});
}

function fnReProgramarPagoModal(){
  respuesta=  fnVerificar('Programado','Autorizado'); //Pendiente de pago
  var cadena='';
  if( (respuesta[0]==true) ){
	$("#btnFechaPago").prop('onclick', null);
	$("#btnFechaPago").attr('onclick', 'fnReprogramar()');

	$("#facturaProgramar").empty();
	$("#mensajesValidaciones").empty();
	$('#txtReferenciaProgramar').val('');
	//<!--$("#facturaProgramar").append('<h5 > Selecciona la fecha del pago</h5> ');-->
	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span>Programación del Pago </h3>';
	$('#ModalProgramarPago_Titulo').empty();
	$('#ModalProgramarPago_Titulo').append(titulo);
	$('#ModalProgramarPago').modal('show');
	//onclick="fnProgramarPago()"
	//array=respuesta[2];
	//.click('function(90'+datos);
	datos=respuesta[2];
	for(a=0;a<datos.length;a++){
	cadena+=datos[a]+",";
	}
	cadena=cadena.slice(0,-1);
	$("#idpp").val(""+cadena);
  } else{
	mensaje=''; // 'Seleccioné una factura';
	muestraModalGeneral(3, titulo,mensaje+respuesta[1]+""); 
  }
}

function fnPagarModal(){
	respuesta=  fnVerificar('Autorizado');
	if(respuesta[0]==true){
		datos=respuesta[2];
		muestraModalGeneralConfirmacion(3, titulo, '¿Desea realizar el pago de las operaciones seleccionadas?','' ,'fnPagar()');
		// muestraModalGeneralConfirmacion(3, titulo, '¿Desea salir sin guardar cambios?', '', 'fnCancelar()');
	}else{
		mensaje='';

		var griddata = $('#divTabla > #divDatos').jqxGrid('getdatainformation');
		for (var i = 0; i < griddata.rowscount; i++) {
			checkpagos = $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'checkPagos');
			if(checkpagos==true){
				fo2 = $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'fo2');
				estadoSinliga = $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'estadoSinliga');

				mensaje += 'El Folio número '+fo2+' ya está '+estadoSinliga+' <br>';
			}
		}

		if(mensaje==''){
			mensaje='Seleccioné un Pago';
		}

		muestraModalGeneral(3, titulo,mensaje+respuesta[1]);
	}
}

 function fnCuentaBanco(idAdd,idSel){
 	var datos=new Array();
 	dataObj = { 
	        proceso: 'getBanco',
	 
	      };

	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
	      data:dataObj,
	      async:false,
	      cache:false
	  })
	.done(function( data ) {

		
		
	    if(data.result){
	    	
	    info=data.contenido.DatosBanco;
	    html='';
	    for(i in info){
    		html+='<option select value="'+info[i].cuenta+'" </option> '+info[i].banco;
    		//console.log(info[i].cuenta+info[i].banco);
    	}
    	var html1='';
    	html1+='<select name="'+idSel+'" id="'+idSel+'" class="'+idSel+'">';
    	html1+=html;
    	html1+=' </select>';
    	$("#"+idAdd).empty();
    	$("#"+idAdd).append('<span><b>Banco origen:</b> '+html1+'</span>');
   //  	console.log($('.'+idSel));
    

		   $('.'+idSel).multiselect({
                enableFiltering: true,
                filterBehavior: 'text',
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '100%',
                numberDisplayed: 1,
                includeSelectAllOption: true
            });
            //$('#'+id).multiselect('dataprovider', options);
            
			$('.multiselect-container').css({ 'max-height': "220px" });
            $('.multiselect-container').css({ 'overflow-y': "scroll" });
            $('.'+idSel).multiselect('rebuild');

	    }else{

	    	
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    
	});
	return  datos;
 }

 function fnDatosFactura(idfact)
 {
 	
 	var info;
 	dataObj = { 
	        proceso:    'datosFactura',
	        idfactura:  idfact//$("#idpp").val()
	 
	      };

	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
	      data:dataObj,
	      async:false,
	      cache:false
	  })
	.done(function( data ) {
	    if(data.result){
	    	info=data.contenido.DatosFactura;
	    	//alert(info[0].ovamount);
	    }else{
	    	
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	   
	});

	return  info;
 }

function fnPagar(){
	respuesta=  fnVerificar('Autorizado');
	id=respuesta[2];
	// console.log("respuesta: "+JSON.stringify(respuesta));
	datosAux=fnVerificarMatriz(id);
	if(datosAux[1]==true){
		$('#ModalGeneral').modal('hide');
		muestraCargandoGeneral();

		setTimeout(function(){

			var datosSelMovimiento = new Array();
			datosSelMovimiento.push($("#selMovimiento1").val());
			var datosIdfactura = new Array();
			datosIdfactura.push($("#idfactura").val());
			var datosStatus = new Array();
			datosStatus.push($("#status").val());
			var datosSupplierid = new Array();
			datosSupplierid.push($("#supplierid").val());
			var datosTagref = new Array();
			datosTagref.push($("#tagref").val());
			var datosRate = new Array();
			datosRate.push($("#rate").val());
			var datosFoliorefe = new Array();
			datosFoliorefe.push($("#foliorefe").val());
			var datosSaldo = new Array();
			datosSaldo.push($("#saldo").val());

			dataObj = { 
				proceso: 'pago2',
				Tipopago:  $( "#selectTipoPagoTesoreria option:selected" ).val(),
				//BankAccount:  $("#bankAccount").val(),
				tipocambio:   $("#tipocambio").val(),
				//selMovimiento: datosSelMovimiento,
				saldo: datosSaldo,
				idfactura:     datosIdfactura,
				status:        datosStatus,
				TransNo:      $("#transno").val(),
				supplierid:    datosSupplierid,
				tagref:        datosTagref, 
				rate:		   datosRate,
				diffonexch:   $("#diffonexch").val(),
				foliorefe:	datosFoliorefe,
				//numchequeuser: $('#numchequeuser').val(),
				//ChequeNum: '',
				ExRate: '1',
				FunctionalExRate: '1',
				//FechaPago: '2017-09-01',
				ids:respuesta[2],
				montos:respuesta[3],
				folios:respuesta[4],
				bancosOrigen:respuesta[5],
				fechas:respuesta[6]
				//unificar:true
			};
		
			$.ajax({
				method: "POST",
				dataType:"json",
				url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
				data:dataObj,
				async:false,
				cache:false
			})
			.done(function( data ) {
				if(data.result){
					//Si trae informacion
					info=data.contenido;
					//alert(info);
					$('#ModalPago').modal('hide');

					muestraModalGeneral(3, titulo, 'Se hicieron pagos correctamente',''); 

					setTimeout(function(){ fnBuscarDatos(); }, 1000);
					ocultaCargandoGeneral();
				}else{
					muestraModalGeneral(3, titulo, data.contenido,''); 
					ocultaCargandoGeneral();
				}
			})
			.fail(function(result) {
				console.log("ERROR");
				console.log( result );
				$('div').removeClass("modal-backdrop");
				ocultaCargandoGeneral();
			});

			/*$('#btnPago').prop('disabled', false);*/
			$('#btnPago').hide();
			$('#btnCerrarPago').prop('disabled', false);
		}, 1000);
	}else{
	   	muestraModalGeneral(3, titulo,datosAux[0]);
	}
}

$('.componenteFeriadoClase').css({color:"#1B693F !important"});

function fnObtenerClase()
{
	$("input:checkbox[class=selMovimiento]:checked").each(function () {
		var clase=  $(this).closest('td').next('td').find('span').attr('class');
		//salert(clase);
	});
}

// proceso de autorizacion
function fnAutorizar(){
	muestraCargandoGeneral();
	$("#selectTipoPagoTesoreria select").val("0");
	$('#ligaPoliza').empty();
	$('#botonTipoTransferencia').empty();
	$('#verDocumentoTrans').empty();

	respuesta=  fnVerificar('Programado'); //cambio24

	if(respuesta[0]==true){
		valores=respuesta[2];
		var  cadena='';
		for(a=0;a<valores.length;a++){
			cadena+=valores[a]+",";
		}
		cadena=cadena.slice(0,-1);
		$("#idpp").val(""+cadena);
		respuesta2=fnValidarMismoProveedor();
		datosAux=fnVerificarMatriz(valores);

		if(datosAux[1]==true){
			if(respuesta[7]!='-9'){
				fnAutorizarModal1(respuesta2);
			}else{
				autorizarPagos('-9');
			}
		}else{
			muestraModalGeneral(3, titulo,datosAux[0]);
		}
	}else{
		mensaje='';

		var griddata = $('#divTabla > #divDatos').jqxGrid('getdatainformation');
		for (var i = 0; i < griddata.rowscount; i++) {
			checkpagos = $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'checkPagos');
			if(checkpagos==true){
				fo2 = $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'fo2');
				estadoSinliga = $('#divTabla > #divDatos').jqxGrid('getcellvalue', i, 'estadoSinliga');

				mensaje += 'El Folio número '+fo2+' ya está '+estadoSinliga+' <br>';
			}
		}

		if(mensaje==''){
			mensaje='Seleccioné un Pago';
		}

		muestraModalGeneral(3, titulo,mensaje+respuesta[1]);
	}

	ocultaCargandoGeneral();
}

function fnUnificarPagos(){
	var griddata = $('#tablaUnificarPago > #datosUnificarPago').jqxGrid('getdatainformation');
  	var contadorSelecc=0;  
  	var mensaje='';
  	var datos=[];
    var tmIds=[];
    var tmRequi=[];
    var tmTag=[];
    var tmfact=[];
    var totalPagar=0;
    var fechas=[];
    
    
	 for (var i = 0; i < griddata.rowscount; i++) {
	        checkpagos = $('#tablaUnificarPago > #datosUnificarPago').jqxGrid('getcellvalue', i, 'checkboxUnificar');
	        if(checkpagos==true){
	        	id= $('#tablaUnificarPago > #datosUnificarPago').jqxGrid('getcellvalue', i, 'id');
	        	requi= $('#tablaUnificarPago > #datosUnificarPago').jqxGrid('getcellvalue', i, 'requi');
	        	tagref=  $('#tablaUnificarPago > #datosUnificarPago').jqxGrid('getcellvalue', i, 'tagref');
	        	factura=   $('#tablaUnificarPago > #datosUnificarPago').jqxGrid('getcellvalue', i, 'factura');
	        	total= $('#tablaUnificarPago > #datosUnificarPago').jqxGrid('getcellvalue',i,'totalPagar');
	        	fecha=$('#divTabla > #divDatos').jqxGrid('getcellvalue',i,'fecha');
	        	totalPagar+=total;
	        	
			    tmIds.push(id);
				tmRequi.push(requi);
				tmTag.push(tagref);
				fechas.push(fecha);

				//tmfact.push(

	        	contadorSelecc++;
	        	
	        }

	          
	    }
	if(contadorSelecc>1){
		mensaje='';
	}else if(contadorSelecc==1){
		mensaje='Al menos debe seleccionar dos pagos.';
	} else{
		mensaje='Seleccioné  los pagos a unificar';
	}

	if(mensaje==''){
		datos.push(mensaje);
		datos.push(tmIds);
	    datos.push(tmRequi);
	    datos.push(tmTag);
	    //datos.push(tmfact);
		datos.push(totalPagar);
		datos.push(fechas);
	}else{

		datos.push(mensaje);
	}

	return  datos;
}
function fnTablaUnificarPago(totalPagar,ids,requis,tagref,factura,nfilas, nombreExcel) {

	 var datosUnificarPago = new Array(); 
	 numeroDeFilas=nfilas;
  	
  	for(i=0;i<numeroDeFilas;i++){
	 var filas={};
    filas['checkboxUnificar']=false;
    filas['id']=ids[i];
    filas['requi']=requis[i];
    filas['tagref']=tagref[i];
    filas['factura']=factura[i];
    filas['totalPagar']=totalPagar[i];
    
   	datosUnificarPago[i]=filas;
   
  	}

    fnLimpiarTabla('tablaUnificarPago', 'datosUnificarPago');

    columnasNombres = '';
    columnasNombres += "[";
    columnasNombres += "{ name: 'checkboxUnificar', type: 'bool' },";
    columnasNombres += "{ name: 'id', type: 'string' },";
    columnasNombres += "{ name: 'requi',type:'string'},";
    columnasNombres += "{ name: 'tagref',type:'string'},";
    columnasNombres += "{ name: 'factura',type:'string'},";
    
    columnasNombres += "{ name: 'totalPagar', type: 'string' },";
    columnasNombres += "]";
    // Columnas para el GRID
    columnasNombresGrid = '';
    columnasNombresGrid += "[";
    //columnasNombresGrid += " { text: '', datafield: 'id1', width: '5%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
    columnasNombresGrid += " { text: '',           datafield: 'checkboxUnificar', width: '9%', cellsalign: 'center', align: 'center', hidden: false, columntype: 'checkbox' },";
    columnasNombresGrid += " { text: 'IDS',        datafield: 'id', width: '18%', align: 'center',hidden: true,cellsalign: 'center' },";
    columnasNombresGrid += " { text: 'Requisición',datafield: 'requi', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
    columnasNombresGrid += " { text: 'UR',datafield: 'tagref', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
    columnasNombresGrid += " { text: 'Factura'    ,datafield: 'factura', width: '25%', align: 'center',hidden: false,cellsalign: 'center' },";
    
    columnasNombresGrid += " { text: 'Total',       datafield: 'totalPagar', width: '26%', cellsalign: 'center', align: 'center', hidden: false },";

    columnasNombresGrid += "]";

    var columnasExcel = [1, 2, 3];
    var columnasVisuales = [0, 1,2,3];


    fnAgregarGrid_Detalle(datosUnificarPago, columnasNombres, columnasNombresGrid, 'datosUnificarPago', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
     $('#tablaUnificarPago > #datosUnificarPago').jqxGrid({width:'100%', autoheight: 'true'});
}
function fnValidarMismoProveedor(){
	datos=fnChecarSeleccionadosPagos();
	
	provedor=datos[5];
	contador=0;
	esElmismoProveedor=false;
    unicos=[];
    totalPagar=0;
    datosRetorno=[];
    totalXrequi=datos[4],

    $.each(provedor, function(i, el){
     if($.inArray(el,unicos) === -1){
     	totalPagar+=totalXrequi[i];
     	unicos.push(el);
     } 
     
	});
   
    if(unicos.length==1 && (provedor.length>1)){
    esElmismoProveedor=true;
    
    }else{
      esElmismoProveedor=false;
        
    }
  datosRetorno.push(esElmismoProveedor);
  datosRetorno.push(totalPagar);
  datosRetorno.push(datos[0]);
  datosRetorno.push(datos[1]);
  datosRetorno.push(datos[2]);
  datosRetorno.push(totalXrequi);
  datosRetorno.push(datos[3]);
  datosRetorno.push(datos[6]);
  
  
  
  return datosRetorno;
}

function fnVerificarMatriz(id){
	var $datos=new Array();

	dataObj = { 
		proceso: 'validarMatrizPagado',
		id: id
	};
	$.ajax({
		method: "POST",
		dataType:"json",
		url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
		data:dataObj,
		async:false,
        cache:false
	})
	.done(function( data ) {
		if(data.result){
			$datos=data.contenido;
		}
	})
	.fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});

	return $datos;
}

function autorizarPagos(reposicion=0){
	var transno=''; 
	var  bancoOri=[];
	var  typePay='';
	var  valor='';

	datos=fnChecarSeleccionadosPagos();

	if(reposicion==0){
		$('#ModalProgramarPago').modal('hide');
		valor=$("#selectTipoPagoTesoreria option:selected" ).val();
		bancoOri.push($('#bank2 option:selected').val());
		typePay=$( "#selectTipoPagoTesoreria option:selected" ).val();
	}else{
		bancoOri=datos[8];
		typePay='02';
		valor='02';
	}

	if (valor == '03' && ($("#txtClaveRastreo").val().trim() == '' || $("#txtClaveRastreo").val().trim() == null)) {
		// Si es transferencia y la clave de rastreo esta vacia
		var mensaje = 'Agregar clave de rastreo para continuar con el proceso';
		muestraMensajeTiempo(mensaje, 1, 'mensajesValidacionesAutorizar', 10000);
		return true;
	}

	if(valor!=0){
		valores=$('#idpp').val();
		movimientos=valores.split(",");
		transno=movimientos;
		
		dataObj = { 
			proceso: 'autorizar',
			id: datos[0],
			banco:bancoOri,
			tipoPago:typePay,
			fechas:datos[10],
			ur:	datos[3],
			sn:datos[11],
			folios:datos[7],
			claveRastreo: $("#txtClaveRastreo").val().trim()
		};
				  		
		$.ajax({
            async:false,
            cache:false,
			method: "POST",
			dataType:"json",
			url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
			data:dataObj
		})
		.done(function( data ) {
			if(data.result){
				$('#ModalAutorizar').modal('hide');
				$("#txtClaveRastreo").val('');
				setTimeout(function(){
					muestraModalGeneral(3, titulo,data.contenido);
				}, 100);
				setTimeout(function(){
					fnBuscarDatos(1);
				}, 2000);
			} else {
				$('#ModalAutorizar').modal('hide');
				$("#txtClaveRastreo").val('');
				setTimeout(function(){
					muestraModalGeneral(3, titulo,data.contenido);
				}, 100);
				setTimeout(function(){
					fnBuscarDatos(1);
				}, 2000);
			}
		})
		.fail(function(result) {
			console.log("ERROR");
			console.log( result );
			ocultaCargandoGeneral();
			$('#ModalAutorizar').modal('hide');
			setTimeout(function(){
				muestraModalGeneral(3, titulo,"No se autorizaron pagos intente de nuevo");
			}, 100);
		});
	}else{
		$('#verDocumentoTrans').empty();
		//muestraMensaje('Seleccione tipo de pago', 3, 'verDocumentoTrans', 5000);
		$("#verDocumentoTrans").fadeIn();
		$("#verDocumentoTrans").append('<div class="col-xs-12 col-md-12 btn text-center" style="background-color:#f2dede !important;color:#a94442;"><h4>Seleccione tipo de pago.</h4></div> <br> <br>');
		setTimeout(function(){
			$("#verDocumentoTrans").fadeOut();
			$("#verDocumentoTrans").empty();
		}, 4100);
	}		
}

function fnAutorizarModal1(respuesta2){
	$('#ModalAutorizar').draggable();  
	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span>Autorizar pago.</h3>';
	$('#ModalAutorizar_Titulo').empty();
	$('#ModalAutorizar_Titulo').append(titulo);
	$('#ModalAutorizar').modal('show');

	$("#idpp").val(""+id);
	fnCuentaBanco("banco2","bank2");

	$("#tipocambio").empty();
	$("#tipocambio").val('No');

	$("#selMovimiento1").empty();
	$("#selMovimiento1").val($("#idpp").val());

	$("#idfactura").empty();
	$("#idfactura").val($("#idpp").val());

	if(respuesta2[0]==true){
		$("#btnUnificarPago").show();
		if(respuesta2[2].length>1){
			fnTablaUnificarPago(respuesta2[5],respuesta2[2],respuesta2[4],respuesta2[6],respuesta2[7],respuesta2[3].length, 'prueba');
		}
		//console.log("total unificado" + (respuesta2[1]));
	}else{
		$("#btnUnificarPago").hide();
		fnLimpiarTabla('tablaUnificarPago', 'datosUnificarPago');
	}

	$('#btnPago').show();
	/* fin  datos pago */
}
//fin proceso de autorizacion

$(document).on('click','.selMovimiento',function(){

  var id;
 
 
var proveedor=$("#txtProv").val();	
if(!proveedor){//si esta vacio
	$('.selMovimiento').on('change', function() {
		    $('.selMovimiento').not(this).prop('checked', false); 
		    fnObtenerClase(); 
		});
}

});


$(document).on('cellselect', '#divTabla > #divDatos', function(event) {
    solicitudEnlace = event.args.datafield;

    if (solicitudEnlace == 'requi') {
        fila = event.args.rowindex;
        enlace = $('#divTabla > #divDatos').jqxGrid('getcellvalue', fila, 'requi');
        if ($('#ligadetalle' + fila).length > 0) {
         	id = $('<div>').append(enlace).find('a:first').attr('data-info');
         	fnDatosDetalles(id);

        }
    }else if(solicitudEnlace == 'tipoPago'){
    	fila = event.args.rowindex;
        enlace = $('#divTabla > #divDatos').jqxGrid('getcellvalue', fila, 'tipoPago');
        if ($('#ligaCheque' + fila).length > 0) {
         	
         	 var href = $('<div>').append(enlace).find('a:first').attr('href');
        	
   
 
		data='<object data="' +href+ '" width="100%" height="401px" type="application/pdf"></object>';
		// muestraModalGeneral(3, titulo,data);
		 window.open(href,'_blank');
		 //'<object data="' + 	data + '" width="100%" height="401px" type="application/pdf"></object>'
/*<iframe src="http://docs.google.com/gview?url=http://www.pdf995.com/samples/pdf.pdf&embedded=true" 
style="width:500px; height:500px;" frameborder="0"></iframe> */

        }else if($('#ligaTransferencia' + fila).length > 0){
		
			var id = $('<div>').append(enlace).find('a:first').attr('data-id');
        	//$('#idpp').val(id);
        	console.log(id+" Datos transferencia");
	    	/*temporal=id.split("-");
	    	dato=temporal[0]; */

			fnGenerarLayoutTransferenciaDetalle(id);
        }
    }else if (solicitudEnlace == 'estado') {
    		fila = event.args.rowindex;
    		enlace = $('#divTabla > #divDatos').jqxGrid('getcellvalue', fila, 'estado');
         	if ($('#ligaPoliza' + fila).length > 0) {
         	 var href = $('<div>').append(enlace).find('a:first').attr('href');
        	//window.open(href,"_blank");
        	//alert(href);
       	
		//data='<iframe src="'+href+'"  class="embed-responsive-item"  frameborder="0" width="100%";  height="40%" > </iframe>';
			data='<object data="' +href+ '" width="100%" height="401px" type="application/pdf"></object>';
		 //muestraModalGeneral(3, titulo,data);
		 window.open(href,'_blank');
			}

        } 
});
$(document).on('change','#selectTipoPagoTesoreria',function(){
  $('#verDocumentoTrans').empty();

  $("#txtClaveRastreo").val('');
  if ($("#selectTipoPagoTesoreria option:selected" ).val() == '03') {
  	// Si es trasnferencia
	$("#txtClaveRastreo").prop("disabled", false);
  } else {
  	$("#txtClaveRastreo").prop("disabled", true);
  }
});


function fnGenerarLayoutTransferencia(){
	ntransno=$('#idpp').val();
	
	valores= valores.slice(0,-1);
	var jsonData = new Array();
    var obj = new Object();
    obj.transno =ntransno; //transnoTransfer; 
    jsonData.push(obj);
    console.log(jsonData);
    console.log(transnoTransfer);
	liga= fnGenerarArchivoLayoutSinModal('244','20',jsonData,2,'transferencia','transferencia');
	$("#verDocumentoTrans").empty();
	$("#botonTipoTransferencia").empty();
	$("#verDocumentoTrans").append(liga);

	transnoTransfer=[];

}
function fnGenerarLayoutTransferenciaDetalle(cadena){

    $("#idpp").val();
	$("#idpp").val(""+cadena);

	ntransno=$('#idpp').val();
	
	console.log(ntransno);
	valores=$('#idpp').val();
	valores= valores.slice(0,-1);
	var jsonData = new Array();
    var obj = new Object();
    obj.transno =ntransno; //transnoTransfer; 
    jsonData.push(obj);
    console.log(jsonData);
    console.log(transnoTransfer);
	liga= fnGenerarArchivoLayoutSinModal('244','20',jsonData,2,'transferencia','transferencia');
	//liga=


muestraModalGeneral(3, titulo,liga,'');

	return liga;

	

}
function  fnAccionTipoPago(idTipoPago){
	//transno=datosFactura[0].transno;
	switch(idTipoPago){

		case '01':
			//orden de pago sn definir
		break;

		/*case '02':
      
      	fnInsertarMovimientoBancarioChueque();
		datos = "PrintCheque_01.php?TransNo=" +transno+ "&type=20&folio=" +"89" + "&periodno=39";
		$("#verDocumentoTrans").empty();
		$("#verDocumentoTrans").append('<div class="text-center"></div><object data="' + datos + '" width="100%" height="401px" type="application/pdf"><embed src="' + datos + '" type="application/pdf" />     </object>');

		break; */

		case '03': 
       	fnGenerarLayoutTransferencia();
		break;

	}
}

$(document).on('click','#btnAccionTipoPago',function(){
  	idTipoPago=	$(this).val();
	fnAccionTipoPago(idTipoPago);
});




$(document).on('cellbeginedit', '#divTabla > #divDatos', function(event) {
   // $(this).jqxGrid('setcolumnproperty', 'checkPagos', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'id', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'un', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'requi', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'estado', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'tipoPago', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'fo2', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'fact', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'prov', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ova', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ovg', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ovat', 'editable', false);
   // $(this).jqxGrid('setcolumnproperty', 'imprimir', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'obs', 'editable', false);

});

$(document).on('click','#ModalGeneral_Mensaje',function(e){
	e.stopPropagation();//detiene el evento del padre
	$('#ModalGeneral_Mensaje').draggable({
		handle: ".modal-header"
	});
});

$(document).ready(function(){
	$("#ModalGeneral_Mensaje").click(function(e){
		 e.stopPropagation();
	});

	$('#ModalGeneral_Mensaje').draggable({
       handle: ".modal-header"
     });
	
	$("#btnUnificarPago").click(function(){
		datos	=fnUnificarPagos();
		ids=datos[1];
		requis=datos[2];
		tags=datos[3];

		if(datos[0]==''){
			valor=$("#selectTipoPagoTesoreria option:selected" ).val();
			if(valor!=0){
				fnEnviarDatosUnificados(ids,requis,tags,datos[4],datos[5]);
			}else{
				$('#verDocumentoTrans').empty();
				//muestraMensaje('Seleccione tipo de pago', 3, 'verDocumentoTrans', 5000);
				$("#verDocumentoTrans").fadeIn();
				$("#verDocumentoTrans").append('<div class="col-xs-12 col-md-12 btn text-center" style="background-color:#f2dede !important;color:#a94442;"><h4>Seleccione tipo de pago.</h4></div> <br> <br>');
				setTimeout(function(){
				$("#verDocumentoTrans").fadeOut();
				$("#verDocumentoTrans").empty();
				}, 4100);
			}
		}else{
			$("#verDocumentoTrans").fadeIn();
			$("#verDocumentoTrans").append('<div class="col-xs-12 col-md-12 btn text-center" style="background-color:#f2dede !important;color:#a94442;"><h4>'+datos[0]+'</h4></div> <br> <br>');
			setTimeout(function(){
			$("#verDocumentoTrans").fadeOut();
			$("#verDocumentoTrans").empty();
			}, 4100);
		}
	});

     fnFormatoSelectGeneral(".estatusTesoSelect");
	 $("#btnUnificarPago").hide();
	 
	 $('#btnCancelarCheque').click(function(){
			
			estatus = $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', 0, 'estatusCheque');
			if(estatus!='Cancelado'){
			muestraModalGeneralConfirmacion(3, titulo, '¿Desea cancelar el cheque '+$("#txtNoCheque").val() +'?','' ,'fnJustificaCancelar()');
			
		}else{
			muestraModalGeneral(3, titulo, 'El cheque ya fue cancelado','');	
		}
	 });


	 fnCuentaBanco("bancoCancelar","bancoselectcancelar");



});

//fnEnviarDatosUnificados(ids,requis,tags,datos[4]);
function fnEnviarDatosUnificados(ids,requis,tags,total,fechas){
	//alert($( "#selectTipoPagoTesoreria option:selected" ).val());

	if ($("#selectTipoPagoTesoreria option:selected" ).val() == '03' && ($("#txtClaveRastreo").val().trim() == '' || $("#txtClaveRastreo").val().trim() == null)) {
		// Si es transferencia y la clave de rastreo esta vacia
		var mensaje = 'Agregar clave de rastreo para continuar con el proceso';
		muestraMensajeTiempo(mensaje, 1, 'mensajesValidacionesAutorizar', 10000);
		return true;
	}

    dataObj = {
        proceso: 'datosUnificados',
        ids:ids,
        requis:requis,
        tags:tags,
        total:total,
        fechas:fechas,
		tipoPago:$( "#selectTipoPagoTesoreria option:selected" ).val(),
		banco:$('#bank2 option:selected').val(),
		claveRastreo: $("#txtClaveRastreo").val().trim()
    };

    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
        async: false,
        cache:false,
        data: dataObj
    })
    .done(function(data) {
        if (data.result) {
            $('#ligaPoliza').empty();
			$('#botonTipoTransferencia').empty();
			$('#verDocumentoTrans').empty();
			$('#ModalAutorizar').modal('hide');

			muestraModalGeneral(3, titulo, data.contenido);

			setTimeout(function(){ fnBuscarDatos(); }, 10);

			ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
       // console.log("ERROR");
		console.log("Error al unificar pago 1616");
		//muestraCargandoGenera
		$('#ModalAutorizar').modal('hide');

		setTimeout(function(){ muestraModalGeneral(3,titulo,"No se unifico el pago intente de nuevo"); }, 5);
		
		ocultaCargandoGeneral();
		$('div').removeClass("modal-backdrop");
    });
}

function  fnSinChequeSelec(){
	muestraModalGeneral(3, titulo,"Seleccione un cheque");
}

function fnJustificaCancelar(){
	datos=fnChecarSeleccionadosCancelar();
	if(datos[0].length>0){
		fnFormatoSelectGeneral(".selectTypeCancel");
		$('#ModalCancelarCh').modal('show');
		$('#ModalCancelarCh').draggable();  
	}else{
		fnSinChequeSelec();
	}
}

function fnNuevoFolio(){
	datos=fnChecarSeleccionadosCancelar();
	// console.log("datos: "+JSON.stringify(datos));
	if(datos.length>0){
		// console.log("ChequeNum: "+JSON.stringify(datos[1]));
		// console.log("origen 2: "+JSON.stringify(datos[2]));
		dataObj = {
			proceso: 'nuevoFolio',
			ChequeNum:datos[1],
			origen:datos[2],
			transno:datos[0],
			ids:datos[4],
			type:datos[8],
			nu_type:datos[9]
		};
		$.ajax({
			method: "POST",
			dataType: "json",
			url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
			async: false,
			cache:false,
			data: dataObj
		})
		.done(function(data) {
			if (data.result) {
				if(data.contenido!=''){
					setTimeout(function(){ fnBuscarDatos(1); }, 10);
					setTimeout(function(){ muestraModalGeneral(3, titulo, data.contenido,''); fnChequeBuscar(1);}, 500); 
				}
				// }else{
				// 	 muestraModalGeneral(3, titulo,data.contenido);
				// 	 fnChequeBuscar();
				// }
			}
		})
		.fail(function(result) {
			console.log("ERROR");
			console.log(result);
			ocultaCargandoGeneral();
		});
	}else{
		fnSinChequeSelec();
	}
}

function fnJusticarReprint(){
	//f
	//muestraModalGeneral(3, titulo, '¿Desea cancelar el cheque '+$("#txtNoCheque").val() +'?','');
	textarea='<div ><h5>Justificación de la reimpresion del cheque </h5> <textarea class="form-control" id="textAreaJusCan" rows="6"> </textarea> <br></div>';
	
	muestraModalGeneral(3, titulo,textarea);
	//$("#ModalGeneral_Pie").append(' <button class="btn  botonVerde glyphicon glyphicon-trash" id="btnJusCancel">Cancelar</button>');
	$( '<button class="btn  botonVerde glyphicon glyphicon-trash" id="btnJusCancel">Cancelar cheque</button>' ).insertBefore( "#btnCerrarModalGeneral" );
	$("#textAreaJusCan").css({"resize": "none"});
	$("#btnCerrarModalGeneral").addClass("");
}

// $(document).on('click','#btnJusCancel',function(){
// 	//console.log("bye");
// 	fnCancelarCheque();
// });
function fnCancelarCheque(){
	$('#ModalCancelarCh').modal('hide');
	muestraCargandoGeneral();
	setTimeout(function(){ 
		datos=fnChecarSeleccionadosCancelar();
		// console.log("datos: "+JSON.stringify(datos));
		// console.log("tipoCancel: "+$("#selectTypeCancel :selected").text());
		// console.log("cancelacion: "+$('#selectTypeCancel').val());
		// return true;
		dataObj = {
			proceso: 'cancelarCheque',
			transno:datos[0],
			ChequeNum:datos[1],
			origen:datos[2],
			ids:datos[4],
			fechas:datos[5],
			justificacion:$("#textAreaJusCan").val(),
			tag: datos[6],
			tipoCancel:$("#selectTypeCancel :selected").text(),
			cancelacion:$('#selectTypeCancel').val(),
			status2:datos[7],
			type:datos[8],
			nu_type:datos[9]
		};
		$.ajax({
			method: "POST",
			dataType: "json",
			url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
			async: false,
			cache:false,
			data: dataObj
		})
		.done(function(data) {
			if (data.result) {
				if(data.contenido==''){
					muestraModalGeneral(3, titulo,'No hay cheque para cancelar.');
				}else{
					ocultaCargandoGeneral();
					$('#textAreaJusCan').val("");
					muestraModalGeneral(3, titulo,data.contenido);
					fnChequeBuscar();
					setTimeout(function(){ fnBuscarDatos(1); }, 10);
				}
			}
		})
		.fail(function(result) {
			ocultaCargandoGeneral();
			console.log("ERROR");
			console.log(result);
			muestraModalGeneral(3, titulo,"Hubo error al cancelar el cheque intente nuevamente");
		});
	}, 1000);
}
function  fnChequeBuscar(busqueda=0){

	       dataObj = {
           proceso: 'buscarChequesCR',
           cheque:$("#txtNoCheque").val(),
           banco:$('#bancoselectcancelar option:selected').val(),
           desdeCancelar:$('#desdeCancelar').val(),
           hastaCancelar:$('#hastaCancelar').val()
           
        };

		//muestraCargandoGeneral();
        $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
            async: false,
            cache:false,
            data: dataObj
		})
	
        .done(function(data) {
            if (data.result) {
			if(	data.contenido.datos.length>0){

				fnLimpiarTabla('tablaChequesCR', 'datosChequesCR');
				//fnAgregarGrid_Detalle(data.contenido.datos, data.contenido.columnasNombres, data.contenido.columnasNombresGrid, 'datosAlmacen', ' ', 1, columnasExcel, false,true, '', columnasVisuales, nombreExcel);
				// Columnas para el GRID
				var colRtotal = ", aggregates: [{'<b>Total</b>' :" +
					"function (aggregatedValue, currentValue) {" +
					"var total = currentValue;" +
					"return aggregatedValue + total;" +
					"}" +
					"}] ";
	
				columnasNombres = '';
				columnasNombres += "[";
				columnasNombres += "{ name: 'id1', type: 'bool'},"; 
				columnasNombres += "{ name: 'fecha', type: 'string'},";
				columnasNombres += "{ name: 'tag', type: 'string' },";
				columnasNombres += "{ name: 'requi', type: 'string' },";
				columnasNombres += "{ name: 'estatus',type:'string'},";
				columnasNombres += "{ name: 'chequeno', type: 'string' },";
				columnasNombres += "{ name: 'banco', type: 'string' },";
				columnasNombres += "{ name: 'estatusCheque', type: 'string' },";
				columnasNombres += "{ name: 'historial', type: 'string' },";
				
				
				columnasNombres += "{ name: 'tipoPago', type: 'string' },";
				
				columnasNombres += "{ name: 'factura', type: 'number' },";
				columnasNombres += "{ name: 'monto', type: 'number' },";
				columnasNombres += "{ name: 'observaciones', type: 'string' },";
				columnasNombres += "{ name: 'id', type: 'string' },";
				columnasNombres += "{ name: 'transno', type: 'string' },";
				columnasNombres += "{ name: 'origen', type: 'string' },";
				columnasNombres += "{ name: 'status2', type: 'string' },";

				columnasNombres += "{ name: 'type', type: 'string' },";
				columnasNombres += "{ name: 'nu_type', type: 'string' }";
	
				columnasNombres += "]";
				//Columnas para el GRID
				columnasNombresGrid = '';
				columnasNombresGrid += "[";
				columnasNombresGrid += " { text: '', datafield: 'id1', width: '4%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
				columnasNombresGrid += " { text: 'Fecha', datafield: 'fecha', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
				columnasNombresGrid += " { text: 'UR',datafield: 'tag', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
				columnasNombresGrid += " { text: 'Requisición',datafield: 'requi', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
				columnasNombresGrid += " { text: 'Estatus Pago', datafield: 'estatus', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
				columnasNombresGrid += " { text: 'Tipo', datafield: 'tipoPago', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
				
				columnasNombresGrid += " { text: 'Folio', datafield: 'chequeno', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
				columnasNombresGrid += " { text: 'Banco', datafield: 'banco', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
				
				columnasNombresGrid += " { text: 'Estatus cheque', datafield: 'estatusCheque', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
				columnasNombresGrid += " { text: 'Historial', datafield: 'historial', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
				
				columnasNombresGrid += " { text: 'Factura', datafield: 'factura', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
				columnasNombresGrid += " { text: 'Monto', datafield: 'monto', width: '8%', cellsalign: 'right', align: 'center', hidden: false ,cellsformat: 'C2'" + colRtotal + "},";  //,cellsformat: 'C2'".$colRtotal."},";
				columnasNombresGrid += " { text: 'Observaciones', datafield: 'observaciones', width: '30%', cellsalign: 'left', align: 'center', hidden: false },";
			   
				columnasNombresGrid += " { text: 'id', datafield: 'id', width: '1%', cellsalign: 'center', align: 'center', hidden: true },";
				columnasNombresGrid += " { text: 'transno', datafield: 'transno', width: '1%', cellsalign: 'center', align: 'center', hidden: true },";
				columnasNombresGrid += " { text: 'origen', datafield: 'origen', width: '14%', cellsalign: 'center', align: 'center', hidden: true },";
				columnasNombresGrid += " { text: 'status2', datafield: 'status2', width: '1%', cellsalign: 'center', align: 'center', hidden: true },";

				columnasNombresGrid += " { text: 'type', datafield: 'type', width: '1%', cellsalign: 'center', align: 'center', hidden: true },";
				columnasNombresGrid += " { text: 'nu_type', datafield: 'nu_type', width: '1%', cellsalign: 'center', align: 'center', hidden: true }";
	
				//columnasNombresGrid += " { text: 'Total', datafield: 'cantidad', width: '19%', cellsalign: 'right', align: 'center',cellsformat: 'C2', hidden:false"+colRtotal+"}";
				columnasNombresGrid += "]";
	
				var columnasExcel = [1,2,3,4,5,6,7,10,11,12];
				var columnasVisuales = [0,1,2,3,4,5,6,7,8,9,10,11,12];
				nombreExcel = data.contenido.nombreExcel;
			
				$("#infoCancelarCheque").fadeIn("slow");
				fnAgregarGrid_Detalle(data.contenido.datos, columnasNombres, columnasNombresGrid, 'datosChequesCR', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
				$('#tablaChequesCR > #datosChequesCR').jqxGrid({width:'100%', autoheight: 'true'});
				ocultaCargandoGeneral();
			}else{
				ocultaCargandoGeneral();
			
				if(busqueda==0){

				muestraModalGeneral(3, titulo,'No se encontro información para mostrar '+$("#txtNoCheque").val());
				}
				fnLimpiarTabla('tablaChequesCR', 'datosChequesCR');
				$("#infoCancelarCheque").fadeOut("slow");
				}
			} else {
				ocultaCargandoGeneral();
			}

        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });
}

$(document).on('cellbeginedit', '#tablaChequesCR > #datosChequesCR', function(event) {
	// $(this).jqxGrid('setcolumnproperty', 'checkPagos', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'id', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'tag', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'requi', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'estatus', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'chequeno', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'factura', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'monto', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'observaciones', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'estatusCheque', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'tipoPago', 'editable', false);
	 $(this).jqxGrid('setcolumnproperty', 'historial', 'editable', false);
	 
	
 });



$(document).on('cellselect', '#tablaChequesCR > #datosChequesCR', function(event) {
    solicitudEnlace = event.args.datafield;
    fila = event.args.rowindex;
    transno = $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', fila, 'transno');

    if (solicitudEnlace == 'estatusCheque') {
       
		
		ncheque = $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', fila, 'chequeno');
       // if ($('#ligadetalle' + fila).length > 0) {
         	//id = $('<div>').append(enlace).find('a:first').attr('data-info');
         fnDetalleCRCheque(transno,ncheque);

       // }
    }else if(solicitudEnlace == 'historial'){
    
    	
    	fnHistorial(transno);


    }
});

function  fnDetalleCRCheque(transno,ncheque){
 
	dataObj = {
		proceso: 'detalleCancelacionCR',
		transno:transno,
		ncheque:ncheque
	};
	muestraCargandoGeneral();

	$.ajax({
		method: "POST",
		dataType: "json",
		url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
		async: false,
		cache:false,
		data: dataObj
	})
	.done(function(data) {
		if (data.result) {
			datos=data.contenido.datos;
			if(datos.length>0){

			
	
			
			html='<div class="col-xs-12 col-md-12">  <div class="col-xs-12 col-md-4"> <component-text-label label="Cheque: " id="txtReferencia" name="txtcheque1" placeholder="Cheque" value="'+ncheque+'" disabled="true"></component-text-label></div>'+
				'<div class="col-xs-12 col-md-4"> <component-text-label label="Fecha: " id="txtReferencia" name="txtref1" placeholder="Fecha" value="'+datos[0].fecha+'" disabled="true"></component-text-label></div>'+
            	 '<div class="col-xs-12 col-md-4"><component-text-label label="Tipo pago: " id="txtProveedor" name="txttp1" placeholder="Tipo pago" value="'+datos[0].tipoPago+'" disabled="true"></component-text-label></div>'+
            	'<div class="col-xs-12 col-md-12"> <br> <br> <component-textarea-label label="Justificación: " id="txtJustificacion" name="txtJustificación" placeholder="Justificación" value="'+datos[0].justificacion+'" disabled="true" rows="3"></component-textarea-label ></div> </div>';
			
			muestraModalGeneral(4, titulo,html);
			fnEjecutarVueGeneral('ModalGeneral_Mensaje');
			$('#txtJustificacion').prop("disabled", true);
			ocultaCargandoGeneral();
		}else{
			muestraModalGeneral(4, titulo,"No hay detalles de cancelación o reposición");
			ocultaCargandoGeneral();
		}
			

		} else{
			ocultaCargandoGeneral();
		}

	})
	.fail(function(result) {
		console.log("ERROR");
		console.log(result);
		ocultaCargandoGeneral();
	});
}



function  fnHistorial(transno){
 
	dataObj = {
		proceso: 'historial',
		transno:transno
	};
	//muestraCargandoGeneral();

	$.ajax({
		method: "POST",
		dataType: "json",
		url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
		async: false,
		cache:false,
		data: dataObj
	})
	.done(function(data) {
		if (data.result) {
			datos=data.contenido.historial;
			console.log(datos);
			if(datos.length>0){

			 muestraModalGeneral(4, titulo, '<div id="tablaHistorial"> <div id="datosHistorial"> </div> </div> </div>');

			
			fnLimpiarTabla('tablaHistorial', 'datosHistorial');

            columnasNombres = '';
            columnasNombres += "[";
           
            columnasNombres += "{ name: 'cheque', type: 'string' },";
            columnasNombres += "{ name: 'tipoPago', type: 'string'},";
            columnasNombres += "{ name: 'justificacion', type: 'string'},";
            columnasNombres += "{ name: 'tipo', type: 'string'},";
        	columnasNombres += "{ name: 'fecha', type: 'string'},";
                
            columnasNombres += "]";

            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: 'Folio cheque', datafield: 'cheque', width: '10%', cellsalign: 'center', align: 'center',columntype: 'string',hidden: false },";
            columnasNombresGrid += " { text: 'Tipo Pago',datafield: 'tipoPago', width: '10%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Justificación',datafield: 'justificacion', width: '30%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Tipo',datafield: 'tipo', width: '25%', align: 'center',hidden: false,cellsalign: 'center' },";
            
            columnasNombresGrid += " { text: 'Fecha',datafield: 'fecha', width: '25%', align: 'center',hidden: false,cellsalign: 'center' },";
          
            columnasNombresGrid += "]";

            var columnasExcel = [ 1,2,3,4];
            var columnasVisuales = [0,1,2,3,4];
            nombreExcel = data.contenido.nombreExcel;

            fnAgregarGrid_Detalle(datos, columnasNombres, columnasNombresGrid, 'datosHistorial', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
		

			ocultaCargandoGeneral();
		}else{
			muestraModalGeneral(4, titulo,"No hay detalles de cancelación o reposición");
			ocultaCargandoGeneral();
		}
			

		} else{
			ocultaCargandoGeneral();
		}

	})
	.fail(function(result) {
		console.log("ERROR");
		console.log(result);
		ocultaCargandoGeneral();
	});
}
function  fnVerificarRadicado(requis,fechas){
 
	dataObj = {
		proceso: 'checarRadicado',
		requis:requis,
		fechas:fechas
	
	};
	//muestraCargandoGeneral();

	$.ajax({
		method: "POST",
		dataType: "json",
		url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
		async: false,
		cache:false,
		data: dataObj
	})
	.done(function(data) {
		if (data.result) {
			ocultaCargandoGeneral();
			 
		noalcanza=data.contenido.datosRadicadoNoAlcanza;
		datosRadicado=data.contenido.datosRadicado;
		
		 	//console.log(noalcanza);
		 if(noalcanza!=''){
			//muestraModalGeneral(3, titulo,datosRadicado);
			muestraModalGeneral(3, titulo,noalcanza);
		 }else{
			muestraModalGeneral(3, titulo,'Las requisiciones seleccionadas  tienen radicado '+"<button id='dRa1' class='btn botonVerde' onclick='deglocesRadicado()'> Detalle </button><br><div id='detalleRadicadoDesgloce' style='display:none;'>"+datosRadicado+"</div>");
		 }
		//
		//muestraModalGeneral(3, titulo,noalcanza);
	
	}
    // if (data.result) {
	// 	console.log(data.contenido.datosRequi);
	// }
	})
	.fail(function(result) {
		console.log("ERROR");
		console.log(result);
		ocultaCargandoGeneral();
	});
}

function fnChecarSeleccionadosCancelar(){
	var id='';
	var fila='';

	var  cancelar=[]; 
	var  arrayTransno=[]; //7
	var  arrayCheque=[]; //8
	var  arrayOrigen=[]; //8
	var  arrayUnion=[];
	var  ids=[];
	var  fechas=[];
	var  tags=[];
	var  status2Array=[];

	var  typeArray=[];
	var  nu_typeArray=[];

	var griddata = $('#tablaChequesCR > #datosChequesCR').jqxGrid('getdatainformation');
	var contadorSelecc=0;

	for (var i = 0; i < griddata.rowscount; i++) {
		checkpagos = $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', i, 'id1');
		if(checkpagos==true){
			transno= $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', i, 'transno');
			cheque = $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', i, 'chequeno');
			origen=  $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', i, 'origen');
			id=		 $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', i, 'id');
			fecha=	 $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', i, 'fecha');
			tag= 	 $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', i, 'tag');
			status2= 	 $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', i, 'status2');

			type= 	 $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', i, 'type');
			nu_type= 	 $('#tablaChequesCR > #datosChequesCR').jqxGrid('getcellvalue', i, 'nu_type');

			ids.push(id);
			fechas.push(fecha);
			arrayTransno.push(transno); //7
			arrayCheque.push(cheque); //8
			arrayOrigen.push(origen); //8
			arrayUnion.push(cheque+"-"+origen);
			tags.push(tag);
			status2Array.push(status2);

			typeArray.push(type);
			nu_typeArray.push(nu_type);

			contadorSelecc++;
		}
	}

	cancelar.push(arrayTransno);//0
	cancelar.push(arrayCheque);//1
	cancelar.push(arrayOrigen);//2
	cancelar.push(arrayUnion);//3
	cancelar.push(ids);//4
	cancelar.push(fechas);//5
	cancelar.push(tags);//6
	cancelar.push(status2Array);//7

	cancelar.push(typeArray);//8
	cancelar.push(nu_typeArray);//9

	return cancelar;
}

//fnFormatoSelectGeneral(".monedaLocal");

function triggetmultiselect(){
    $('#multiple_id').multiselect();
}

$(document).on('click','#btnCerrarModalGeneral',function(){
    ocultaCargandoGeneral();
   $('div').removeClass("modal-backdrop");
   if (document.getElementById("ModalSpinerGeneral")){
    //document.getElementById("ModalSpinerGeneral").remove();
    $('#ModalSpinerGeneral').modal('hide');
    }
});

/**
 * Función para realizar las operaciones de reversa de documentos
 * pendientes de pago para que que se cancele la factura de pago agregada
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @return {[type]} [description]
 */
function fnOperacionesReversa(confirmacion = 0) {
	// Operaciones para realizar la reversa de documentos pendientes de pago
	var datos=fnChecarSeleccionadosPagos();
	if(datos.length > 0) {
		var ids = datos[0];
		var arrayTypes = datos[12];
		var arrayTypesName = datos[13];
		var tipoNoContemprado = 0;
		var mensaje = "";

		for(a=0; a<ids.length; a++) {
			// if (arrayTypes[a] != '20') {
			// 	// Reversa no contemplada para ese documento
			// 	tipoNoContemprado = 1;
			// 	mensaje += "<p>Proceso no contemplado para la operación "+arrayTypesName[a]+"</p>";
			// }
			// console.log("id: "+ids[a]+" - type: "+arrayTypes[a]);
		}

		if (tipoNoContemprado == 1) {
			// Mostrar que documentos no han sido contemplados
			muestraModalGeneral(4, titulo, mensaje);
			return false;
		}

		if (confirmacion == 0) {
			// Confirmar proceso
			if(ids.length > 1) {
				// Es mas de una factura
				mensaje = "<p>¿Está seguro de rechazar las operaciones seleccionadas?</p>";
			} else {
				mensaje = "<p>¿Está seguro de rechazar la operación seleccionada?</p>";
			}
        	muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnOperacionesReversa(1)");
        	return true;
		}

		var respuesta = fnValidarEstatusMovimientos(ids);
		if (respuesta != '') {
			// Mostrar mensaje
			muestraModalGeneral(3, titulo, respuesta);
		} else {
			// Realizar reversa
			respuesta = fnRealizarReversaFacturas(ids);
			muestraModalGeneral(3, titulo, respuesta);
		}

		// Cargar datos panel
		fnBuscarDatos(1);
	} else {
		// Sin seleccion de docuementos
		mensaje='Seleccioné una factura';
		muestraModalGeneral(3, titulo, mensaje);
	}
}

/**
 * Función para validar los estatus de los documentos seleccionados
 * @param  {[type]} infoDoc Array con información de los documentos
 * @return {[type]}         [description]
 */
function fnValidarEstatusMovimientos(infoDoc) {
	// Funcion para validar que los documentos seleccionados esten en pednientes de pago
	var respuesta = '';
	
	dataObj = {
		proceso: 'validarEstatusReversar',
		infoDoc: infoDoc
	};

	$.ajax({
		method: "POST",
		dataType: "json",
		url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
		async: false,
		cache:false,
		data: dataObj
	})
	.done(function(data) {
		if (data.result) {
			// ocultaCargandoGeneral();
			respuesta = data.Mensaje;
		} else {
            respuesta = data.Mensaje;
		}
	})
	.fail(function(result) {
		respuesta = 'Ocurrio un problema al realizar el proceso';
		console.log("ERROR");
		console.log(result);
		// ocultaCargandoGeneral();
	});
	
	return respuesta;
}

/**
 * Función para realizar la reversa de las facturas seleccionadas
 * @param  {[type]} infoDoc Array con información de los documentos
 * @return {[type]}         [description]
 */
function fnRealizarReversaFacturas(infoDoc) {
	// Función para realizar la reversa de las facturas seleccionadas
	var respuesta = '';
	
	dataObj = {
		proceso: 'reversarDocumentos',
		infoDoc: infoDoc
	};

	$.ajax({
		method: "POST",
		dataType: "json",
		url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
		async: false,
		cache:false,
		data: dataObj
	})
	.done(function(data) {
		if (data.result) {
			// ocultaCargandoGeneral();
			respuesta = data.Mensaje;
		} else {
            respuesta = data.Mensaje;
		}
	})
	.fail(function(result) {
		console.log("ERROR");
		console.log(result);
		// ocultaCargandoGeneral();
	});

	return respuesta;
}
