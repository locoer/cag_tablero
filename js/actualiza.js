$(document).ready( function () {
		var apiFile = (Window.File && Window.FileReader);
		if (apiFile == false) {
			alert("Tu navegador no permite arrastrar y soltar archivos");
			return;
		}
		var asoltar = document.getElementById("area_soltar");
		asoltar.addEventListener("drop", soltarArchivo);
		asoltar.addEventListener("dragover", moverArchivo);
		document.getElementById("click_arch").addEventListener('change', click_mas);
		
		$("#area_soltar").hover( function () {
			$(this).addClass("arch_sobre");
		}, function () {
			$(this).removeClass("arch_sobre");
		});
		function soltarArchivo(e) {
			e.stopPropagation();
			e.preventDefault();
			$("#area_soltar").addClass("arch_sobre");
			var archivos = e.dataTransfer.files;
			prepara_archivo(archivos);
		}
		function click_mas (e) {
			$("#area_soltar").addClass("arch_sobre");
			var archivos = e.target.files;
			prepara_archivo(archivos);
		}
		function prepara_archivo(archivos) {
			var archs = archivos.length;
			if (archs == 1) {
				var tamanio = archivos[0].size;
				var tipo = archivos[0].type;
			} else {
				alert('Favor de subir sólo 1 archivo a la vez');
				return;
			}
			if (tamanio > 3000000 || archs != 1 || tipo != 'application/vnd.ms-excel') {
				alert('Favor de subir un archivo de tipo CSV menor a 3 Mb y no del tipo: ' + tipo);
				return;
			} else {
				var formData = new FormData();
				formData.append('arch_act_pp', archivos[0]);
				//alert("Aquí mando el archivo al serveeeer");
				$.ajax({
				  url: "https://cohenag.com/tablerov2/ajax.php",
				  data: formData,
				  processData: false,
				  contentType: false,
				  type: 'POST',
				  success: function( respuesta ) {
					//alert(respuesta);
					$("#respuesta").html(respuesta);
					$("#area_soltar").removeClass("arch_sobre");
				  }
				});
			}
		}
		function moverArchivo(e){
			e.stopPropagation();
			e.preventDefault();
			$(this).addClass("arch_sobre");
		}
		function suelta_window (e) {
			e.stopPropagation();
			e.preventDefault();
			$(this).removeClass("arch_sobre");
		}

		$('#cont_mas').click(function() {
			$('#click_arch').trigger('click');
		});

		/*$('#click_arch').change(function() {
			var arch_mas = $(this).val();
			soltarArchivo(arch_mas);
		});*/
});