function buscarUsuarios() {
    $.get("servicio.php?usuarios", function (usuarios) {
        $("#tbodyUsuarios").html("")
    
        for (let x in usuarios) {
            const usuario = usuarios[x]
    
            $("#tbodyUsuarios").append(`<tr>
                <td>${usuario.id_usuario}</td>
                <td>${usuario.nombre}</td>
                <td>${usuario.email}</td>
                <td>${usuario.password}</td>
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
        $.post("servicio.php?modificarProducto", $(this).serialize(), function (respuesta) {
            if (respuesta == "correcto") {
                alert("Producto modificado correctamente")
                $("#frmProducto").get(0).reset()
                buscarProductos()
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

    $.get("servicio.php?editarProducto", {
        id: id
    }, function (productos) {
        const producto = productos[0]

        $("#txtId").val(producto.id)
        $("#txtNombre").val(producto.nombre)
        $("#cboCategoria").val(producto.categoria)
        $("#txtPrecio").val(producto.precio)
        $("#txtExistencias").val(producto.existencias)
    })
})

$(document).on("click", ".btn-eliminar", function (event) {
    const id = $(this).data("id")

    if (!confirm("Deseas eliminar este producto?")) {
        return
    }

    $.post("servicio.php?eliminarProducto", {
        txtId: id
    }, function (respuesta) {
        if (respuesta == "correcto") {
            alert("Producto eliminado correctamente")
            buscarProductos()
        }
    })
})
