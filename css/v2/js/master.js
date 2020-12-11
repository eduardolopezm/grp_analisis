/*
* .
*/
// JavaScript Document

//$(document).ready(function () {
        $('._program-widget-head').click(function () {
            $('._program-widget').removeClass('selected');
            $('._program-widget-head').removeClass('selected');
            $('._program-widget-body').slideUp('medium');
            if (!$(this).next().is(":visible")) {
                $(this).next().slideDown();
                $(this).closest('._program-widget').addClass('selected');
                $(this).closest('._program-widget-head').addClass('selected');
            }
        });

        $('._component-widget-head').click(function () {
            $('._component-widget').removeClass('selected');
            $('._component-widget-head').removeClass('selected');
            $('._component-widget-body').slideUp('medium');
            if (!$(this).next().is(":visible")) {
                $(this).next().slideDown();
                $(this).closest('._component-widget').addClass('selected');
                $(this).closest('._component-widget-head').addClass('selected');
            }
        });

        $('._section-widget-head').click(function () {
            $('._section-widget').removeClass('selected');
            $('._section-widget-head').removeClass('selected');
            $('._section-widget-body').slideUp('medium');
            if (!$(this).next().is(":visible")) {
                $(this).next().slideDown();
                $(this).closest('._section-widget').addClass('selected');
                $(this).closest('._section-widget-head').addClass('selected');
            }
        });

        $('._instance-head').click(function () {
            $('._instance-head').removeClass('selected');
            $('._instance-body').slideUp('medium');
            if (!$(this).next().is(":visible")) {
                $(this).next().slideDown();
                $(this).closest('._instance-head').addClass('selected');
            }
        });
        /*
        $('._state-head').click(function () {
            $('._state-head').removeClass('selected');
            $('._state-body').slideUp('medium');
            if (!$(this).next().is(":visible")) {
                $(this).next().slideDown();
                $(this).closest('._state-head').addClass('selected');
            }
        });
        */
//});




/*
$(document).ready(function () {
    $('.program-widget-head').click(function () { // Programas
        $('.program-widget-head').removeClass('selected');
        $('.program-widget-body').slideUp('medium');
        $(this).next().slideDown('medium');
        $(this).closest('.program-widget-head').addClass('selected');
        $('.component-widget-head').removeClass('selected');
        $('.component-widget-body').slideUp('medium');
        $('.section-widget-body').slideUp('medium');
        //ProgramScroll($(this).closest('.program-widget-head'));
    });


    function ProgramScroll(P) {
        $('body,html').stop(true, true).animate({
            scrollTop: P.offset().top
        }, 1000);
    }
    $('.component-widget-head').click(function () { // Componentes
        $('.component-widget-head').removeClass('selected');
        $('.component-widget-body').slideUp('medium');
        $(this).next().slideDown('medium');
        $(this).closest('.component-widget-head').addClass('selected');
        //ComponentScroll($(this).closest('.component-widget-head'));
    });
    function ComponentScroll(C) {
        $('body,html').stop(true, true).animate({
            scrollTop: C.offset().top
        }, 1000);
    }
    $('.section-widget-head').click(function () { // Secciones (Avance y fechas de gestion)
        $('.section-widget-head').removeClass('selected');
        $('.section-widget-body').slideUp('medium');
        $(this).next().slideDown('medium');
        $(this).closest('.section-widget-head').addClass('selected');
    });
    
    $('.state-head').click(function () {
        $('.state-head').removeClass('selected');
        $('.state-body').slideUp('medium');
        $(this).next().slideDown('medium');
        $(this).closest('.state-head').addClass('selected');
    });
 
});
*/
function Show(selected,uclick) { // Menu año
    var objShow = document.getElementById(selected);
    var estado = $(selected).css('display');
    var selector = uclick.toString();
    var selectora = '#';
    var res = selectora.concat(selector);

    if (estado == 'none') {
        $(selected).slideDown("medium");
        //$('._component-widget-head').addClass('selected');
        $(res).addClass('selected');
    } else {
        $(selected).slideUp("medium");
        //$('._component-widget-head').removeClass('selected');
        $(res).removeClass('selected');
    }

     
            
};

function Show1(selected,uclick) { // Menu año
    var objShow = document.getElementById(selected);
    var estado = $(selected).css('display');
    //var selector= $(uclick)document.getElementById;
    var selector = uclick.toString();
    var selectora = '#';
    var res = selectora.concat(selector);
    //alert(uclick);
    if (estado == 'none') {
        $(selected).slideDown("medium");
        $(res).addClass('selected');
    } else {
        $(selected).slideUp("medium");
        $(res).removeClass('selected');
    }

     
            
};

//--------------------------------------------------------------------------------

function OpenPrograma() {
   // $('.program-widget-head').click(function () { // Programas
        $('.program-widget-head').removeClass('selected');
        $('.program-widget-body').slideUp('medium');
        $(this).next().slideDown('medium');
        $(this).closest('.program-widget-head').addClass('selected');
        $('.component-widget-head').removeClass('selected');
        $('.component-widget-body').slideUp('medium');
        $('.section-widget-body').slideUp('medium');
        //ProgramScroll($(this).closest('.program-widget-head'));
    };


    //-- Script Pagina principal

    // Busqueda de un componente
    function BusqComponente() {

        ShowPopup();
        var count = document.getElementById("Ddl_Programa").options.length;

        if (count > 0) {

            var ok = document.getElementById("Container_Lnk_Srch");
            ok.click();
        }
    }


    // indice 2
    function indexchange() {
        var dd = document.getElementById("Ddl_Programa").selectedIndex = 2;
    }


    function ShowPopup() {
        var BKGround = document.getElementById("PopupBackground").style.display = "block";
        var Msg = document.getElementById("PopupProcess").style.display = "block";

    }


    function HidePopup() {
        var BKGround = document.getElementById("PopupBackground").style.display = "none";
        var Msg = document.getElementById("PopupProcess").style.display = "none";

    }