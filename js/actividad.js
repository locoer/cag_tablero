$(document).ready( function () {
	$("#registro_puntos .card-body input[type='number']").each( function() {
		$(this).change(resumen_puntos);
	});
	
	resumen_puntos();
	
	$('#fecha').on('changeDate', function() {
		var fecha = $('#fecha').datepicker('getFormattedDate');
		$('#input_fecha').val(fecha);
		$("#card_fecha .card-footer button").removeClass("d-none");
		$("#seleccion_fecha").html( "Registrar actividad del día: " + fecha_formato(fecha) );
		$("#res_input_fecha").val(fecha);
		$("#txt_seleccion_fecha").text(fecha_formato(fecha));
		$("button.siguiente").removeClass("d-none").addClass("activo");
	});
	
	$(".siguiente").each( function () {
		$(this).click( function () {
			siguiente_card ();
		});
	});
	
	$(".regresar").each ( function () {
		$(this).click( function () {
			regresar_card ();
		});
	});
	
	agrega_validaciones ();
});

function fecha_formato (fecha) {
	var fecha_1 = new Date(fecha);
	var fecha_f = new Date(fecha_1.getTime() + fecha_1.getTimezoneOffset()*60000);
	var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: "America/Mexico_City" }; 
	var fecha_formato = fecha_f.toLocaleDateString('es-MX', options);
	return fecha_formato;
}

function siguiente_card () {
	$("#cont_registro .card.activo").removeClass("activo").addClass("d-none").next().removeClass("d-none").addClass("activo");
	$("button.regresar").removeClass("d-none").addClass("activo");
	$("button#guardar").removeClass("d-none").addClass("activo");
	$("button.siguiente").removeClass("activo").addClass("d-none");
	
}

function regresar_card () {
	$("#cont_registro .card.activo").removeClass("activo").addClass("d-none").prev().removeClass("d-none").addClass("activo");
	$("button.regresar").removeClass("activo").addClass("d-none");
	$("button#guardar").removeClass("activo").addClass("d-none");
	$("button.siguiente").removeClass("d-none").addClass("activo");
}

function agrega_validaciones () {
	$(".card input[type='number']").each( function () {
		$(this).change( function () {
			if( $(this).val() < 0 ) {
				$(this).val(0);
			}
		});
	});
}

function resumen_puntos () {
	var total = 0;
	$("#registro_puntos .card-body input").each( function () {
		cantidad = Number($(this).val());
		puntos = Number( $(this).data('puntos') );
		if ( cantidad > 0 ) {
			total += (cantidad * puntos);
		}
	});
	$("#total_puntos").val(total);
}