function consultas(busqueda = "") {
    $.get("api_h/servicio.php", {
        consultas: true,
        buscar: busqueda
    }, function (datos) {

        $("#tbodyConsultas").html("");

        for (let i = 0; i < datos.length; i++) {
            const c = datos[i];

            $("#tbodyConsultas").append(`
                <tr>
                    <td>${c.id}</td>
                    <td>${c.Usuario}</td>
                    <td>${c.Ciudad}</td>
                    <td>${c.temperatura}°C</td>
                    <td>${c.descripcion}</td>
                    <td>${c.fechaHora}</td>
                    <td>
                    <button class="btn btn-outline-secondary btn-sm btn-eliminar" data-id="${c.id}">
                        🗑 Eliminar
                    </button>
                    </td>
                    <td>
                    <button class="btn btn-outline-secondary btn-sm btn-editar" data-id="${c.id}">
                         ✏ Editar
                    </button>
                    </td>
                </tr>
            `);
        }
    });
}

$("#buscar").on("keyup", function () {
    let texto = $(this).val();
    consultas(texto);
});

$(document).ready(function () {
    consultas();
});

//-------------------------------------------------------//

$(document).on("click", ".btn-eliminar", function () {
    const id = $(this).data("id");

    if (!confirm(`¿Deseas eliminar el registro ${id}?`)) {
        return;
    }

    $.post("api_h/servicio.php?eliminarConsulta", {
        txtId: id
    }, function (respuesta) {

        if (respuesta === "correcto") {
            alert("Consulta eliminada correctamente");
            consultas();
        } else {
            alert("Error al eliminar");
        }
    });
});
 
//-------------------------------------------------------//

$.get("api_h/servicio.php?ciudadesCombo", function (ciudades) {
    $("#cboCiudad").html("<option value=''>Selecciona ciudad</option>");

    for (let x in ciudades) {
        const ciudad = ciudades[x];

        $("#cboCiudad").append(`
            <option value="${ciudad.id_ciudad}">
                ${ciudad.nombre} - ${ciudad.pais}
            </option>
        `);
    }
});

//-------------------------------------------------------//

$.get("api_h/servicio.php?usuariosCombo", function (usuario) {
    $("#cboUsuario").html("<option value=''>Selecciona usuario</option>");

    for (let x in usuario) {
        const usuarios = usuario[x];

        $("#cboUsuario").append(`
            <option value="${usuarios.id_usuario}">
                ${usuarios.nombre} - ${usuarios.email}
            </option>
        `);
    }
});

//-------------------------------------------------------//

$("#frmConsulta").submit(function (event) {
    event.preventDefault();

    let url = "api_h/servicio.php?agregarConsulta";

    if ($("#txtIdConsulta").val()) {
        url = "api_h/servicio.php?modificarConsulta";
    }

    $.post(url, $(this).serialize(), function (respuesta) {
        if (respuesta === "correcto") {
            alert("Guardado correctamente");
            $("#frmConsulta")[0].reset();
            consultas();
        } else {
            alert("Error al guardar");
        }
    });
});

//-------------------------------------------------------//

$(document).on("click", ".btn-editar", function () {
    const id = $(this).data("id");

    $.get("api_h/servicio.php?obtenerConsulta", { id: id }, function (datos) {
        const c = datos[0];

        $("#txtIdConsulta").val(c.id_consulta);
        $("#cboUsuario").val(c.id_usuario);
        $("#cboCiudad").val(c.id_ciudad);
        $("#txtTemperatura").val(c.temperatura);
        $("#txtDescripcion").val(c.descripcion);
    });
});
