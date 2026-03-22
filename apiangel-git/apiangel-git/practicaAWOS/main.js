(function() {
    var tkn = localStorage.getItem("jwt");
    
    if (!tkn) {
        window.location = "/AWOSVS/main/index.html";
        return;
    }

    const payload = JSON.parse(atob(tkn.split(".")[1]));
    const tipo = payload.sub.split("/")[2];

    if (tipo !== "1") {
        alert("No tienes permisos");
        window.location = "/AWOSVS/main/index.html";
        return;
    }

    $.ajaxSetup({
        headers: { Authorization: `Bearer ${tkn}` }
    });
})();

function buscarUsuarios() {
    $.get("servicio.php?usuarios", function (usuarios) {
        $("#tbodyUsuarios").html("")
    
        for (let x in usuarios) {
            const usuario = usuarios[x]
    
            $("#tbodyUsuarios").append(`<tr>
                <td>${usuario.id_usuario}</td>
                <td>${usuario.nombre}</td>
                <td>${usuario.email}</td>
                <td>${usuario.contrasena}</td>
                <td>${usuario.fecha_registro}</td>
                <td>${usuario.total_consultas}</td>
                <td>
                    <button class="btn btn-info btn-editar mb-1 me-1" data-id="${usuario.id_usuario}">Editar</button>
                    <button class="btn btn-danger btn-eliminar" data-id="${usuario.id_usuario}">Eliminar</button>
                </td>
            </tr>`)
        }
    })
}

buscarUsuarios()

$.get("servicio.php?categoriasCombo", function (categorias) {
    $("#cboCategoria").html("")

    for (let x in categorias) {
        const categoria = categorias[x]

        $("#cboCategoria").append(`<option value="${categoria.value}">
            ${categoria.label}
        </option>`)
    }
})

$("#frmUsuarios").submit(function (event) {
    event.preventDefault()

    if ($("#txtId").val()) {
        $.post("servicio.php?modificarUsuario", $(this).serialize(), function (respuesta) {
            if (respuesta == "correcto") {
                alert("Usuario modificado correctamente")
                $("#frmUsuarios").get(0).reset()
                buscarUsuarios()
            }
        })
        return
    }

    $.post("servicio.php?agregarUsuario", $(this).serialize(), function (respuesta) {
        if (respuesta != "0") {
            alert("Usuario agregado correctamente")
            $("#frmUsuarios").get(0).reset()
            buscarUsuarios()
        }
    })
})

$(document).on("click", ".btn-editar", function (event) {
    const id = $(this).data("id")
    console.log("hola profe")

    $.get("servicio.php?editarUsuario", {
        id: id
    }, function (usuarios) {
        const usuario = usuarios[0]

        $("#txtId").val(usuario.id_usuario)
        $("#txtNombre").val(usuario.nombre)
        $("#txtEmail").val(usuario.email)
        $("#txtContrasena").val(usuario.password)
    })
})

$(document).on("click", ".btn-eliminar", function (event) {
    const id = $(this).data("id")

    if (!confirm("Deseas eliminar este Usuario?")) {
        return
    }

    $.post("servicio.php?eliminarUsuario", {
        txtId: id
    }, function (respuesta) {
        if (respuesta == "correcto") {
            alert("Usuario eliminado correctamente")
            buscarUsuarios()
        }
    })
})
