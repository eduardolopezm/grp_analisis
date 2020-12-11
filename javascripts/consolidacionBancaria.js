/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
var meses={'01':'Enero','02':'Febrero','03':'Marzo',
           '04':'Abril','05':'Mayo','06':'Junio',
           '07':'Julio','08':'Agosto','09':'Septiembre',
           '10':'Octubre','11':'Noviembre','12':'Diciembre' };
  //$('.selectMeses').append(contenido);
var arreglomeses=[];
for (var i in meses){
        if (meses.hasOwnProperty(i)) {
           arreglomeses.push({'value': i, 'texto': meses [i]} );
                    //contenidomeses += "<option value='"+i+"'>"+meses[i]+"</option>"
            }

}
arreglomeses.sort(function (a, b) {
          var aValue = parseInt(a.value);
          var bValue = parseInt(b.value);
          // ASC
          return aValue == bValue ? 0 : aValue < bValue ? -1 : 1;
          // DESC
          //return aValue == bValue ? 0 : aValue > bValue ? -1 : 1;
});        
   

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
    		html+='<option select value="'+info[i].cuenta+'"> '+info[i].banco;+' </option> ';
    		//console.log(info[i].cuenta+info[i].banco);
    	}
    	var html1='';
    	html1+='<div class="col-xs-3 col-md-8"><select name="'+idSel+'" id="'+idSel+'" class="'+idSel+'">';
    	html1+=html;
    	html1+=' </select> </div>';
    	$("#"+idAdd).empty();
    	$("#"+idAdd).append('<div class="text-left col-xs-12 col-md-12"><div class="col-md-4"> <b>Banco origen:</b></div> '+html1+'</div>');

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

function fnObtenerMes(){
	var datos=[];
	dataObj ={
	proceso: 'getMesServidor',
	};  
	$.ajax({
	method: "POST",
	dataType:"json",
	async:false,
	cache:false,
	url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
	data:dataObj
	})
	.done(function( data ) {    
	if(data.result){
	        mes=data.contenido.MesActual;
	        anio=data.contenido.anioActual;
	        datos.push(mes);
	        datos.push(anio);
	      }else{
	      	console.log("No se obtuvo datos fijar mes actual");
	      }
	})
	.fail(function(result) {
	console.log("Error al obtener mes actual");
	console.log( result );
	});

	return datos;
}
 $(document).ready(function(){

	fnCuentaBanco("banco","bancoselect");
	
	var mesAnio=fnObtenerMes();
	var mesActual=mesAnio[0];
	var anioActual=mesAnio[1];
	fnCrearDatosSelect(arreglomeses, '#selectMeses',mesActual);
	fnFormatoSelectGeneral('#selectMeses');
 
 });

