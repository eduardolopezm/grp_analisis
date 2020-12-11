/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesús Reyes Santos 
 * @version 0.1
 */
var url = "modelo/selectSalesOrderModelo.php";
var proceso = "";
var anyo = "";


$(document).ready(function() {
   //Mostrar Catalogo
  
});



/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar() {


   var orderNo = $('#orderNo').val();
   var comentario = $('#txtComentario').val();
   var msg = "";
   
   
   if (orderNo == "" || orderNo == null || orderNo == 0 || orderNo == "0" || orderNo === "undefined" || comentario == "" || comentario == null || comentario == 0 || comentario == "0" || comentario === "undefined") {
	   if (orderNo == "" || orderNo == null || orderNo == 0 || orderNo == "0" || orderNo === "undefined" ) {
		   msg += '<p>No se ha encontrado un pase de cobro</p>';
	   }
	   if (comentario == "" || comentario == null || comentario == 0 || comentario == "0" || comentario === "undefined"  ) {
		   msg += '<p>El comentario no puede ir vacio </p>';
	   }
	   
		
	   

	   $('#divMensajeOperacion').removeClass('hide');
	   $('#divMensajeOperacion').empty();
	   $('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
	   
   }else{
	   $('#ModalUR').modal('hide');
	   dataObj = {
		   option: 'AgregarCatalogo',
		   orderNo: orderNo,
		   comentario: comentario

	   };
	   $.ajax({
		   async:false,
		   cache:false,
		   method: "POST",
		   dataType: "json",
		   url: url,
		   data: dataObj
	   }).done(function(data) {
		  
               muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
               console.log("Estoy en el success");

        
                function showpanel() {     
                    $( "#SearchOrders" ).trigger( "click" );
                 }
                
                 // use setTimeout() to execute
                 setTimeout(showpanel, 1000);

                

                
            //    $(".SearchOrders").click(function(e){
            //     newButton_Click($(this),e)
            //   })
               
         
	   }).fail(function(result) {
		   console.log("ERROR");
		   console.log(result);
	   });
   }
}
/**
* Muestra formulario para modificar la información del registro seleccionado
* @param  {String} ur Código del Registro para obtener la información
*/
function fnModificar(orderNo) {
	console.log(orderNo);
	$('#divMensajeOperacion').addClass('hide');

	$("#orderNo").val(""+orderNo);
	$("#txtComentario").val(""+$("#txtComents_"+orderNo).val());

	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Comentario de Pase de Cobro ' + orderNo + '</h3>';
	$('#ModalUR_Titulo').empty();
	$('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

function fnCancelar(orderNo) {

    		// console.log(orderNo);
   			$('#divMensajeOperacion').addClass('hide');

             
			   $("#orderNo").val(""+orderNo);
			  
			   
		
		   var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Cancelación de Pase de Cobro ' + orderNo + '</h3>';
		   $('#ModalCancelar_Titulo').empty();
		   $('#ModalCancelar_Titulo').append(titulo);
		   $('#ModalCancelar').modal('show');

}


function fnAgregarCancelacion() {


	var orderNo = $('#orderNo').val();
	var comentario = $('#txtComentarioCancel').val();
	var msg = "";
	
	
	if (orderNo == "" || orderNo == null || orderNo == 0 || orderNo == "0" || orderNo === "undefined" || comentario == "" || comentario == null || comentario == 0 || comentario == "0" || comentario === "undefined") {
		if (orderNo == "" || orderNo == null || orderNo == 0 || orderNo == "0" || orderNo === "undefined" ) {
			msg += '<p>No se ha encontrado un pase de cobro</p>';
		}
		if (comentario == "" || comentario == null || comentario == 0 || comentario == "0" || comentario === "undefined"  ) {
			msg += '<p>El comentario no puede ir vacio </p>';
		}
		
		 
		
 
		$('#divMensajeOperacion').removeClass('hide');
		$('#divMensajeOperacion').empty();
		$('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		
	}else{
		$('#ModalCancelar').modal('hide');
		dataObj = {
			option: 'Cancelacion',
			orderNo: orderNo,
			comentario: comentario
 
		};
		$.ajax({
			async:false,
			cache:false,
			method: "POST",
			dataType: "json",
			url: url,
			data: dataObj
		}).done(function(data) {
		   
				muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
				
 
		 
				//  function showpanel() {     
				// 	 $( "#SearchOrders" ).trigger( "click" );
				//   }
				 
				  
				//   setTimeout(showpanel, 1000);
 
				 
 
				 
			 //    $(".SearchOrders").click(function(e){
			 //     newButton_Click($(this),e)
			 //   })
				
		  
		}).fail(function(result) {
			console.log("ERROR");
			console.log(result);
		});
	}
 }

