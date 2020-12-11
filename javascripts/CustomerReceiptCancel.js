 /**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesús Reyes Santos 
 * @version 0.1
 */
var url = "modelo/CustomerReceiptCancelModelo.php";
var proceso = "";
var anyo = "";


$(document).ready(function() {
   //Mostrar Catalogo
  
});



/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar() {


   var transno = $('#transno').val();
   var comentario = $('#txtComentario').val();
   var msg = "";
   
   
   if (transno == "" || transno == null || transno == 0 || transno == "0" || transno === "undefined" || comentario == "" || comentario == null || comentario == 0 || comentario == "0" || comentario === "undefined") {
	   if (transno == "" || transno == null || transno == 0 || transno == "0" || transno === "undefined" ) {
		   msg += '<p>No se ha encontrado un pase de cobro</p>';
	   }
	   if (comentario == "" || comentario == null || comentario == 0 || comentario == "0" || comentario === "undefined"  ) {
		   msg += '<p>El comentario no puede ir vacio </p>';
	   }
	   
		
	   

	   $('#divMensajeOperacion').removeClass('hide');
	   $('#divMensajeOperacion').empty();
	   $('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
	   
   }else{
        muestraCargandoGeneral();
	   $('#ModalUR').modal('hide');
	   dataObj = {
		   option: 'cancelarRecibo',
		   transno: transno,
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
        ocultaCargandoGeneral();
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
        ocultaCargandoGeneral();
		   console.log("ERROR");
		   console.log(result);
	   });
   }
}
/**
* Muestra formulario para modificar la información del registro seleccionado
* @param  {String} ur Código del Registro para obtener la información
*/
function fnModificar(transno) {
   $('#divMensajeOperacion').addClass('hide');

			
			   $("#transno").val(""+transno);
			  
			     
		
		   var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Cancelación de recibo de pago ' + transno + '</h3>';
		   $('#ModalUR_Titulo').empty();
		   $('#ModalUR_Titulo').append(titulo);
		   $('#ModalUR').modal('show');

}

function fnExcelReport() {
	var tab_text = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
	tab_text = tab_text + '<meta charset="UTF-8">';
	tab_text = tab_text + '<head><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
	tab_text = tab_text + '<x:Name>Test Sheet</x:Name>';
	tab_text = tab_text + '<x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet>';
	tab_text = tab_text + '</x:ExcelWorksheets></x:ExcelWorkbook></xml></head><body>';
	tab_text = tab_text + "<table border='1px'>";
	
   //get table HTML code
	tab_text = tab_text + $('#tablaRecibos').html();
	tab_text = tab_text + '</table></body></html>';

	var data_type = 'data:application/vnd.ms-excel';
	
	var ua = window.navigator.userAgent;
	var msie = ua.indexOf("MSIE ");
	//For IE
	if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
		 if (window.navigator.msSaveBlob) {
		 var blob = new Blob([tab_text], {type: "application/csv;charset=utf-8;"});
		 navigator.msSaveBlob(blob, 'Recibos de pago.xls');
		 }
	} 
   //for Chrome and Firefox 
   else {
	$('#test').attr('href', data_type + ', ' + encodeURIComponent(tab_text));
	$('#test').attr('download', 'Recibos de pago.xls');
   }


   }

 

