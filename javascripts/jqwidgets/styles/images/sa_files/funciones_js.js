// LIBRERIA DE FUNCIONES DEL JOBSITE


//***************************
//FUNCION: m4luz
//FECHA: 28/07/2000
//PARAMETROS DE ENTRADA: objeto
//***************************
function m4luz(objeto){
	objeto.style.filter= "light(enabled=1)";
	objeto.filters.light.addAmbient(200,200,200,100);
	objeto.filters.light.addPoint(50,40,40,0,40,100,100);
}
//***************************
//FUNCION: m4oscuridad
//FECHA: 28/07/2000
//PARAMETROS DE ENTRADA: objeto
//***************************
function m4oscuridad(objeto){
	objeto.style.filter="light(enabled=0)";
	objeto.style.filter="shadow(enabled=0)";
}
//***************************
//FUNCION: m4luzgenerico
//FECHA: 28/07/2000
//PARAMETROS DE ENTRADA: objeto,R,G,B,X,Y,Z
//X -->  Coordena x del punto de luz 
//Y -->  Coordena y del punto de luz
//Z -->  Coordena z del punto de luz
//R -->  rojo (0-255)
//G -->  verde (0-255)
//B -->  azul (0-255)
//***************************
function m4luzgenerico(objeto,R,G,B,X,Y,Z){
	objeto.style.filter= "light(enabled=1)";
	objeto.filters.light.addAmbient(130,255,255,100);
	objeto.filters.light.addPoint(X,Y,Z,R,G,B,255);
}
//***************************
//FUNCION: m4sombra
//FECHA: 28/07/2000
//PARAMETROS DE ENTRADA:objeto
//***************************
function m4sombra(objeto){
	objeto.style.filter="shadow(color=#BBDDFF,direction=90,enabled=1)";
}	
//***************************
//FUNCION: m4luznoname
//FECHA: 28/07/2000
//PARAMETROS DE ENTRADA:objeto
//***************************
function m4luznoname(objeto){
	objeto.style.filter= "light(enabled=1)";
	objeto.filters.light.addAmbient(255,255,255,100);
	objeto.filters.light.addPoint(50,40,40,0,40,100,100);
}
//***************************
//FUNCION: m4ambientegenerico
//FECHA: 28/07/2000
//PARAMETROS DE ENTRADA: objeto,R,G,B,I
//***************************
function m4ambientegenerico(objeto,R,G,B,I){
	objeto.style.filter= "light(enabled=1)";
	objeto.filters.light.addAmbient(R,G,B,I);
	objeto.filters.light.addPoint(50,40,40,0,40,100,100);
}

//********************************************************
//FUNCION: m4luztotal
//FECHA: 20/10/2000
//PARAMETROS DE ENTRADA: objeto,ra,ga,ba,xp,yp,zp,rp,gp,bp
//********************************************************


 function m4luztotal (objeto,ra,ga,ba,xp,yp,zp,rp,gp,bp){
     var num_arg = m4luztotal.arguments.length;
     var color_amb = false;
     var coord_punto = false;
     var color_punto = false;
     var RA = 255;
     var GA = 255;
     var BA = 255;
     var IA = 100;
     var XP = 50;
     var YP = 40;
     var ZP = 40;
     var RP = 0;
     var GP = 40;
     var BP = 100;
     var IP = 100;
    if (num_arg == 0) {
        alert (msg_79) }
    else if (num_arg == 1) {
        objeto.style.filter = "light(enabled=1)";
        objeto.filters.light.addAmbient(RA,GA,BA,IA);
         objeto.filters.light.addPoint(XP,YP,ZP,RP,GP,BP,IP);
       }
  else if (num_arg > 1) {
       
    
        if ((typeof(ra)!= "string") && (typeof(ga)!= "string") && (typeof(ba) != "string")) { color_amb =true};
        if ((typeof(xp)!= "string") && (typeof(yp)!= "string") && (typeof(zp)!= "string")) { coord_punto = true};
        if ((typeof(rp)!= "string") && (typeof(gp)!= "string") && (typeof(bp)!= "string")) { color_punto = true};
      
        }
        
    if (color_amb == true) {
         if ((coord_punto == false) && (color_punto == false)) {
                objeto.style.filter = "light(enabled=1)";
                objeto.filters.light.addAmbient(ra,ga,ba,IA);
                objeto.filters.light.addPoint(XP,YP,ZP,RP,GP,BP,IP);
              }
               if ((coord_punto == false) && (color_punto == true)) {
                  objeto.style.filter = "light(enabled=1)";
                  objeto.filters.light.addAmbient(ra,ga,ba,IA);
                  objeto.filters.light.addPoint(XP,YP,ZP,rp,gp,bp,IP);
                  }
                    if ((coord_punto == true) && (color_punto == false)) {
                      objeto.style.filter = "light(enabled=1)";
                      objeto.filters.light.addAmbient(ra,ga,ba,IA);
                      objeto.filters.light.addPoint(xp,yp,zp,RP,GP,BP,IP);
                      }
                        if ((coord_punto == true) && (color_punto == true)) {
                          objeto.style.filter = "light(enabled=1)";
                          objeto.filters.light.addAmbient(ra,ga,ba,IA);
                          objeto.filters.light.addPoint(xp,yp,zp,rp,gp,bp,IP);
                          }
           }
           
      else if (color_amb == false) {
               if ((coord_punto == false) && (color_punto == true)) {
                  objeto.style.filter = "light(enabled=1)";
                  objeto.filters.light.addAmbient(RA,GA,BA,IA);
                  objeto.filters.light.addPoint(XP,YP,ZP,rp,gp,bp,IP);
                  }
                    if ((coord_punto == true) && (color_punto == false)) {
                      objeto.style.filter = "light(enabled=1)";
                      objeto.filters.light.addAmbient(RA,GA,BA,IA);
                      objeto.filters.light.addPoint(xp,yp,zp,RP,GP,BP,IP);
                      }
                          if ((coord_punto == true) && (color_punto == true)) {
                         objeto.style.filter = "light(enabled=1)";
                         objeto.filters.light.addAmbient(RA,GA,BA,IA);
                         objeto.filters.light.addPoint(xp,yp,zp,rp,gp,bp,IP);
                          }
           }
       
           }





//+++++++++++++++++++++Ventana de dialogo para Netscape++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//+++++++++++++++++++++Variables necesarias en la pagina para la ventana de dialogo (incluido el calendario)++++++++++++++++++++++++

//var Nav4 = ((navigator.appName == "Netscape") && (parseInt(navigator.appVersion) == 4));
//var dialogWin = new Object(); //Objeto que contiene a la ventana de dialogo

// Parametros:
//    url -- URL de la pagina a ser cargada en la ventada de dialogo
//    width -- anchura de la ventana de dialogo
//    height -- altura de la ventana de dialogo
//    returnFunc -- referencia a la funcion de la pagina que llama a la de la ventana de dialogo y que
//                  actua con el dato retornado por la de la ventana de dialogo
//    args -- [opcional] cualquier dato que se necesite pasar a la ventana de dialogo

//***************************
//FUNCION: m4y2k
//***************************
function m4y2k(number)    { return (number < 1000) ? number + 1900 : number; }

//***************************
//FUNCION: m4openDialog
//***************************

function m4openDialog(url, name, width, height, returnFunc, objeto, args) {
	//Comprobacion de que no hay un dialogo abierto ya
	if (!dialogWin.win || (dialogWin.win && dialogWin.win.closed)) {
		// Inicializacion de las propiedades del Objeto de dialogo
		dialogWin.returnFunc = returnFunc;
		dialogWin.objeto =  objeto;
		dialogWin.returnedValue = "";
		dialogWin.args = args;
		dialogWin.url = url;
		dialogWin.width = width;
		dialogWin.height = height;
		dialogWin.name = name;
		//Propiedades iniciales para su uso cuando se utilice para abrir e calendario de Netscape
		var now = new Date();
		dialogWin.month =now.getMonth();
		dialogWin.year = y2k(now.getYear());
		//Nombre unico un objeto de dialogo
		//dialogWin.name = (new Date()).getSeconds().toString()
		//if (Nav4) {
			// Centrado en la ventana principal (la que me crea)
			dialogWin.left = window.scrX + 
			   ((window.outerWidth - dialogWin.width) / 2);
			dialogWin.top = window.screenY + 
			   ((window.outerHeight - dialogWin.height) / 2);
			var attr = "screenX=" + dialogWin.left + 
			   ",screenY=" + dialogWin.top + ",resizable=yes,scrollbars=yes,width=" + 
			   dialogWin.width + ",height=" + dialogWin.height;
			// Genero el dialogo y me aseguro de que tiene el foco
		dialogWin.win=window.open(dialogWin.url, dialogWin.name, attr);
		dialogWin.win.focus()
	} else {
		dialogWin.win.focus()
	}
}
//***************************
//FUNCION: m4calendarioie
//FECHA:
//PARAMETROS DE ENTRADA: objeto es el "objeto" en el que el calendario va a retornar la fecha. 
//COMENTARIOS: Utilizar solo con ie.
//***************************
function m4calendarioie(objeto){
  	  fecha = showModalDialog('/servlet/CheckSecurity/JSP/jobsite_g/calendarioie.jsp', objeto.value,'dialogWidth=318pt;dialogHeight=195pt;maximize=no;minimize=no;border=thin;center=yes;help=no;');
	  objeto.value = fecha;
}

//FUNCION: m4calendario
//FECHA:
//PARAMETROS DE ENTRADA: objeto: es el "objeto" en el que el calendario va a retornar la fecha.
//COMENTARIOS: Valida para ie y ns 
//***************************	
function m4calendario(objeto) 
   {
     if (!document.all) {
	  openDialog('/servlet/CheckSecurity/JSP/jobsite_g/calendarions.jsp','Calendario', '290', '290', m4returnFunc(objeto), objeto,'1')}
	else{
	  fecha = showModalDialog('/servlet/CheckSecurity/JSP/jobsite_g/calendarioie.jsp', objeto.value,'dialogWidth=255pt;dialogHeight=151pt;maximize=no;minimize=no;border=thin;center=yes;help=no;');
	  objeto.value = fecha;
    }
}	
//***************************
//FUNCION: m4returnFunc
//FECHA:
//PARAMETROS DE ENTRADA: objeto
//***************************	
function m4returnFunc(objeto){
 objeto.value = dialogWin.returnedValue;
}
//***************************
//FUNCION: m4fechaingles
//FECHA:
//PARAMETROS DE ENTRADA:esp
//COMENTARIOS: Función para cambiar el formato de las fechas de inglés a español
//***************************	
function m4fechaingles(esp){
	var dia=esp.substring(2,0);
	var mes=esp.substring(5,3);
	var ano=esp.substring(10,6);
	ing = ano + "-" + mes +"-"+ dia;
return(ing);
}
//***************************
//FUNCION: m4navegar
//FECHA:22/09/2000
//PARAMETROS DE ENTRADA:URL,parametros,valores 
//***************************	
function m4navegar(URL,parametros,valores){
if (m4navegar.arguments.lenght == 0){
alert(msg_80);
}
else{
if ((typeof(parametros) != "undefined") && (typeof(valores) != "undefined")){ 
var salida = "";
var cadena = "";
for (i=0;i < parametros.length-1; i++){
cadena = cadena + parametros[i] + "=" + valores[i] + "&";
}
cadena = cadena +  parametros[parametros.length-1] + "=" + valores[parametros.length-1];
if ((parametros.length !=0) && (valores.length !=0) && (valores.length == parametros.length)){
salida = "/servlet/CheckSecurity/JSP/"+ URL + "?" + cadena;}
else{
salida = "/servlet/CheckSecurity/JSP/"+ URL;}
//alert(salida);
location.href=salida;
}
else{ location.href= "/servlet/CheckSecurity/JSP/"+ URL;}
}
}
//***************************
//FUNCION: m4url
//FECHA: 25/09/2000
//COMENTARIOS: Devuelve la dirección de la página actual.
//***************************	
function m4url() {
var zURL = location.href;
return zURL;
}
//***************************
//FUNCION: m4titulo
//FECHA: 25/09/2000
//COMENTARIOS: Devuelve el título de la página actual.
//***************************	
function m4titulo() {
var ztitulo = document.title;
return (ztitulo);
}
//***************************
//FUNCION: m4fechahoy
//FECHA:
//COMENTARIOS: Devuelve la fecha de hoy en dd-mm-yyyy
//***************************	
function m4fechahoy(){	
	d = new Date();
	var d,s = "";
	if (d.getDate() < 10)
		{ s += 0;}
	s += d.getDate()+ "-";
	if (d.getMonth()+1 < 10)
		 { s += 0;}
	s +=(d.getMonth()+1)+ "-";
	s += d.getYear();
	return(s);	
}
//***************************
//FUNCION: m4sustituiramp
//FECHA: 2/10/2000
//COMENTARIOS: Sustituye el & de la cadena por el caracter que se le pase.
//***************************	
function m4sustituiramp (cadena, sustituto) {
var zamp = /&/g;
var zcadena_sin_amps = cadena.replace (zamp, sustituto);
return (zcadena_sin_amps);
}
//***************************************************************
//FUNCION: m4select
//FECHA: 2/10/2000
//PARAMETROS: idselect (String), modo (String) (valores posibles text,value)
//COMENTARIOS: Funcion que captura el valor de una select, retorna el text 
//o el value del option seleccionado, o el String "vacio" si no hay seleccionado ninguno
//***************************************************************
function m4select(idselect,modo){
	var objselect = document.all[idselect];

	if (typeof(objselect) != "undefined")
	{
		
		switch(modo)
		{
		case "text" :
			//alert(objselect.options[objselect.selectedIndex].text);
			return objselect.options[objselect.selectedIndex].text;
		case "value" :
			//alert(objselect.options[objselect.selectedIndex].value);
			return objselect.options[objselect.selectedIndex].value;
		default :
			alert(msg_88);
			return "vacio";
		}
	}
	else
	{
	alert(msg_81);
	}
}

//***************************************************************
//FUNCION: m4focus
//FECHA: 10/10/2000
//***************************************************************
function m4focus(idform,idobjeto){
if (typeof(document.forms[idform].elements[idobjeto]) != "undefined")
{
document.forms[idform].elements[idobjeto].focus();
}
else
{
alert(msg_82);
}
}
//***************************************************************
//FUNCION: m4valor
//FECHA: 10/10/2000
//***************************************************************
function m4valor(idform,idobjeto,valor,modo)
{
	var objeto = document.forms[idform].elements[idobjeto];
	if (typeof(objeto) != "undefined"){
		switch(modo)
		{
		case "set" :
			objeto.value = valor;
		case "get" :
			return objeto.value;
		default : 
		alert(msg_83);
		}
	}
	else
	{
	alert(msg_84);
	return "Objeto no definido \nObjeto.value no definido";
	}
}
//***************************************************************
//FUNCION: m4write
//FECHA: 10/10/2000
//***************************************************************
function m4write(text,elemento,tipo,id,idobjetopadre)
{
if ((m4write.arguments.length > 1) && (document.all)){
   var parentElem = document.all["capa_cuerpo"];
   var oNewElement=document.createElement(elemento);
   if(tipo){
      oNewElement.type=tipo;
      oNewElement.value=text;
      oNewElement.id=id;
   }
   if(text){
      if(elemento.toLowerCase()!="input"){
         var oNewText=document.createTextNode(text);
         oNewElement.appendChild(oNewText);
      }
      
   }
   if(idobjetopadre){
	 var objPadre = document.all[idobjetopadre];
     objPadre.appendChild(oNewElement);
   }
   else{
      parentElem.appendChild(oNewElement);
   }
	//alert(document.all[id].value);
}
else 
{
if (typeof(text) == "string"){
document.write(text);
}
else
{
alert(msg_85);
} 
}
}
//***************************************************************
//FUNCION: m4objeto
//FECHA: 10/10/2000
//***************************************************************
function m4objeto(idobjeto,idform)
{
if (idform){
if (typeof(document.forms[idform].elements[idobjeto]) != "undefined"){
return document.forms[idform].elements[idobjeto];}
else
{
alert(msg_86);
}
}
else
{
if (document.all)
{
return document.all[idobjeto];
}
else
{
alert(msg_87);
}
}
}

//***************************
//FUNCION: m4ventana
//FECHA:
//PARAMETROS DE ENTRADA: dir, direccion a la que se dirige.
//alto: alto de ventana
//ancho:ancho de ventana
//COMENTARIOS: Valida para ie y ns 
//***************************	
function m4ventana(dir,ancho,alto){
	if (!document.all) {	
		if (typeof(ventana) != "object"){
			ventana = new m4dialogwin("ventana",objeto);
		}
		//ventana.objeto = objeto;
		ventana.m4opendialog(dir,ancho,alto);
	}
	else{
		fecha = showModalDialog(dir, 0,'dialogWidth=' + ancho + 'pt;dialogHeight=' + alto + 'pt;maximize=no;minimize=no;border=thin;center=yes;help=no;');
	  	//objeto.value = fecha;
    }
}	

//***************************
//FUNCION: m4ventana
//FECHA:
//PARAMETROS DE ENTRADA: dir, direccion a la que se dirige.
//alto: alto de ventana
//ancho:ancho de ventana
//COMENTARIOS: Valida para ie y ns 
//***************************	
function m4ventana2(dir,ancho,alto){
	if (!document.all) {	
		if (typeof(ventana) != "object"){
			ventana = new m4dialogwin("ventana",objeto);
		}
		//ventana.objeto = objeto;
		ventana.m4opendialog(dir,ancho,alto);
	}
	else{
		fecha = showModalDialog(dir, 0,'dialogWidth=' + ancho + 'pt;dialogHeight=' + alto + 'pt;maximize=no;minimize=no;border=thin;center=yes;help=no;');
	  	//objeto.value = fecha;
    }
}	

//***************************
//FUNCION: m4ventana
//FECHA:
//PARAMETROS DE ENTRADA: dir, direccion a la que se dirige.
//alto: valida fecha
//ancho:ancho de ventana
//COMENTARIOS: Valida para ie y ns 
//***************************	
function str2date(fechastr){
	//var fechastr = "24-01-1975";
	var fecha = new Date();
	fecha.setYear(fechastr.substring(6,10));
	fecha.setMonth(fechastr.substring(3,5) - 1);
	fecha.setDate(fechastr.substring(0,2));
	return(fecha);
}
function strformatodate(fechastr){
	//var fechastr = "24-01-1975";
	var fecha = new Date();
	fecha.setYear(fechastr.substring(0,4));
	//alert(fechastr.substring(0,4));
	fecha.setMonth(fechastr.substring(5,7) - 1);
	//alert(fechastr.substring(5,7));
	fecha.setDate(fechastr.substring(8,10));
	//alert(fechastr.substring(8,10));
	return(fecha);
}

function validaNumEntero(str){
//Valida para ie5 en adelante y ns 4 en adelante
//regresa true si es un n&uacute;mero sin decimales y sin punto decimal
//	var reg1str = /^\d+\.{0,1}\d*$/
//	alert(reg1str.test("1.1"));
	var rexp = "^\\d+$";
	var reg1 = new RegExp(rexp);
	return(reg1.test(str));
}

function validaRFC(str){
//Valida para ie5 en adelante y ns 4 en adelante
//regresa true si es un RFC correctamente escrito, no valida fechas ni carcateres
//simplemente la forma del RFC, ya sea con homoclave o sin ella
//	var reg1str = /^[a-z,A-Z]{4}\d{6}([a-z,A-Z,0-9]{3})*$/
//	alert(reg1str.test("asdS800423"));
	var rexp = "^[a-z,A-Z]{4}\\d{6}([a-z,A-Z,0-9]{3})*$";
	var reg1 = new RegExp(rexp);
	return(reg1.test(str));
}

function validaCURP(str){
//Valida para ie5 en adelante y ns 4 en adelante
//regresa true si es un RFC correctamente escrito, no valida fechas ni carcateres
//simplemente la forma del RFC, ya sea con homoclave o sin ella
//	var reg1str = /^[a-z,A-Z]{4}\d{6}([a-z,A-Z,0-9]{8})+$/
//	alert(reg1str.test("asdS800423345rfvgt"));
	var rexp = "^[a-z,A-Z]{4}\\d{6}([a-z,A-Z,0-9]{8})+$";
	var reg1 = new RegExp(rexp);
	return(reg1.test(str));
}
function validaEmail(str){
//Valida para ie5 en adelante y ns 4 en adelante
//regresa true si es valido y false si no
//	var reg1str = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/
//	return(reg1str.test(str));
	var rexp = "^\\w+([\\.-]?\\w+)*@\\w+([\\.-]?\\w+)*(\\.\\w{2,3})+$";
	var reg1 = new RegExp(rexp);
	return(reg1.test(str));
}
function validaNumEntero(str){
//Valida para ie5 en adelante y ns 4 en adelante
//regresa true si es un n&uacute;mero sin decimales y sin punto decimal
//	var reg1str = /^\d+\.{0,1}\d*$/
//	alert(reg1str.test("1.1"));
	var rexp = "^\\d+$";
	var reg1 = new RegExp(rexp);
	return(reg1.test(str));
}
