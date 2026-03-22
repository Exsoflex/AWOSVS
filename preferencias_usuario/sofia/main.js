

var token = localStorage.getItem("jwt");

if (token) {
    $.ajaxSetup({
        headers: {
            Authorization: `Bearer ${token}`
        }
    });
}

    obtenerPreferencias();
    buscarPreferencias();
    $('input[name="tema"]').change(function() {
        aplicarTema($(this).val());
    });

    $('#btnGuardar').click(function() {
        guardarPreferencias();
    });


//////
function obtenerPreferencias() {
    $.get("servicio.php", {
        obtener_preferencias: true
    }, function (datos) {
        
        if (datos.tema) {
            aplicarTema(datos.tema);
            $('input[name="tema"][value="' + datos.tema + '"]').prop('checked', true);
        }
        
      
        if (datos.unidad_temperatura) {
           
            const unidadValor = datos.unidad_temperatura === 'Celsius' ? 'C' : 'F';
            $('input[name="unidad"][value="' + unidadValor + '"]').prop('checked', true);
        }
        
        if (datos.logueado === false) {
            console.log("Usuario no logueado - preferencias temporales");
        }
    }, "json");
}
/////////pdsa

function aplicarTema(tema) {
    $('body').removeClass('claro oscuro').addClass(tema);
}

////////////////////////////////////////

function guardarPreferencias() {
    const unidadSeleccionada = $('input[name="unidad"]:checked').val();
    const temaSeleccionado = $('input[name="tema"]:checked').val();
    
    // Convertir de C F a Celsius Fahrenheit 
    const unidadBackend = unidadSeleccionada === 'C' ? 'Celsius' : 'Fahrenheit';
    
    $.post("servicio.php?guardar_preferencias", {
        unidad: unidadBackend,
        tema: temaSeleccionado
    }, function (respuesta) {
        if (respuesta === "correcto") {
            console.log(" Preferencias guardadas permanentemente");
       
            $('#btnGuardar').text(' Guardado!').removeClass('btn-primary').addClass('btn-success');
            setTimeout(() => {
                $('#btnGuardar').text('Guardar Cambios').removeClass('btn-success').addClass('btn-primary');
            }, 2000);
        } else if (respuesta === "error_no_session") {
            console.log("preferencias temporales");
        } else {
            console.log(" Error al guardar preferencias");
        }
    });
}

//////////////////////////////////////////
function buscarPreferencias() {
  $.get("servicio.php?preferencias", function (preferencias) {

    $("#tbodyProductos").html("")

    for (let x in preferencias) {
      const pref = preferencias[x]

      $("#tbodyProductos").append(`
        <tr>
          <td>${pref.id_usuario}</td>
          <td>${pref.nombre}</td>
          <td>${pref.preferencias}</td>
     <td><button class="btn btn-danger btn-eliminar" data-id="${pref.id_usuario}">Eliminar</button></td>
        </tr>
      `)
    }
  }, "json")   
}


$(document).on("click", ".btn-eliminar", function (event) {

    const idUsuario = $(this).data("id")

    if (!confirm("¿Deseas eliminar las preferencias de este usuario?")) {
        return
    }

    $.post("servicio.php?eliminarpreferencia", {
        id_usuario: idUsuario
    }, function (respuesta) {
        if (respuesta === "correcto") {
            alert("Preferencias eliminadas")
            buscarPreferencias()
           
        }
        else if (respuesta === "error") {
            alert(":(")
        }
    })
})


/////////////
$("#formAgregarPreferencias").submit(function (event) {
    event.preventDefault();

const idUsuario = $("#txtusuario").val();
const unidad = $('#formAgregarPreferencias input[name="unidad"]:checked').val();
const tema = $('#formAgregarPreferencias input[name="tema"]:checked').val();

    $.post("servicio.php?agregar_preferencia_sp", {
        id_usuario: idUsuario,
        unidad: unidad,
        tema: tema
    }, function(respuesta) {
        
        if (respuesta === "correcto") {
            alert("Preferencias agregadas correctamente");
            buscarPreferencias(); 
            $("#formAgregarPreferencias")[0].reset(); 
        } else {
            alert("Error al agregar preferencias");
        }
    });
});





$("#btnModificar").click(function (event) {
    event.preventDefault();

    const idUsuario = $("#txtusuario").val();
    const unidad = $('#formAgregarPreferencias input[name="unidad"]:checked').val();
    const tema = $('#formAgregarPreferencias input[name="tema"]:checked').val();

    $.post("servicio.php?modificar_preferencia", {
        id_usuario: idUsuario,
        unidad: unidad,
        tema: tema
    }, function (respuesta) {

        if (respuesta === "correcto") {
            alert("Preferencias modificadas correctamente");
            buscarPreferencias();
            $("#formAgregarPreferencias")[0].reset();

        } else {

            alert("Error al modificar preferencias");

        }

    });

});






