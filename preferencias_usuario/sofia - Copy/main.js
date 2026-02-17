$(document).ready(function() {
    // Cargar preferencias al iniciar
    function cargarPreferencias() {
        $.post("servicio.php", {accion: "obtener_preferencias"}, function(respuesta) {
            if (respuesta.unidad_temperatura) {
                $(`input[name="unidad"][value="${respuesta.unidad_temperatura}"]`).prop('checked', true);
            }
            if (respuesta.tema) {
                $(`input[name="tema"][value="${respuesta.tema}"]`).prop('checked', true);
                $('body').removeClass('claro oscuro').addClass(respuesta.tema);
            }
        }, "json");
    }

    // Cambio de tema en tiempo real
    $('input[name="tema"]').on('change', function() {
        $('body').removeClass('claro oscuro').addClass($(this).val());
    });

    // Guardar preferencias
    $('#btnGuardar').on('click', function() {
        const datos = {
            accion: 'guardar_preferencias',
            unidad: $('input[name="unidad"]:checked').val(),
            tema: $('input[name="tema"]:checked').val()
        };
        
        $.post("servicio.php", datos, function(respuesta) {
            if (respuesta.status === 'ok') {
                alert('¡Preferencias guardadas!');
            }
        }, "json");
    });

    // Cargar preferencias al inicio
    cargarPreferencias();
});