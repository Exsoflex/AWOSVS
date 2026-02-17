function buscarUsuarios() {
    $.get("servicio.php?usuarios", function (usuarios) {
        $("#tbodyProductos").html("")

        for (let x in usuarios) {
            const usuario = usuarios[x]
            const favoritos = usuarios[x]


            $("#tbodyProductos").append(`
            <tr>
                <td>${usuario.id}</td>
                <td>${usuario.usuario}</td>
                <td>${usuario.ciudades_favoritas}</td>
                <td>${usuario.total_favoritos}</td>
                <td><button class="btn btn-danger btn-eliminar" data-id="${usuario.id}">Eliminar
                <button class="btn btn-info btn-editar mb-1 me-1" data-id="${usuario.id}">Editar</button>

                </td>


            </tr>
        `)
        }
    })

}

buscarUsuarios()
////////////mioooooooo/////////////////
$.get("servicio.php?usuariosCombo", function (usuarios) {

    $("#cboFavoritos").html("")

    for (let x in usuarios) {
        const usuario = usuarios[x]

        $("#cboFavoritos").append(`
            <option value="${usuario.value}">
                ${usuario.label}
            </option>
        `)
    }
})
//////////////miooooooooooo/////////////////
$("#frmUsuarios").submit(function (event) {
    event.preventDefault()



    $.post(
        "servicio.php?agregarFavorito",
        $(this).serialize(),
        function (respuesta) {

            if (respuesta === "correcto") {
                alert("Ciudad favorita guardada correctamente")

                $("#frmUsuarios")[0].reset()
                buscarUsuarios()
            }
            else {
                alert("Error: " + respuesta)
            }
        }
    )
})

$(document).on("click", ".btn-eliminar", function () {

    const idUsuario = $(this).data("id")

    if (!confirm("¿Deseas eliminar las ciudades favoritas de este usuario?")) {
        return
    }

    $.post("servicio.php?eliminarFavorito", {
        id_usuario: idUsuario
    }, function (respuesta) {
        if (respuesta === "correcto") {
            alert("Ciudades favoritas eliminadas")
            buscarUsuarios()
        }
        else if (respuesta === "error") {
            alert("Este usuario no cuenta con ciudades favoritas")
        }
    })
})

$.get("servicio.php?ciudadesCombo", function (usuarios) {

    $("#cboCiudades").html("")

    for (let x in usuarios) {
        const usuario = usuarios[x]

        $("#cboCiudades").append(`
            <option value="${usuario.value}">
                ${usuario.label}
            </option>
        `)
    }
})


