<?php

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL & ~E_DEPRECATED);

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Allow: GET, POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
  http_response_code(200);
  exit;
}

if (isset($_GET["PING"])) {
  exit;
}

date_default_timezone_set("America/Matamoros");

if (isset($_GET["DATETIME"])) {
  echo date("Y-m-d H:i:s");
  exit;
}


// ------------------------------------------------------
// ------------------------------------------------------
// Debajo de este comentario irá la configuración a la BD
// y las funciones del servicio para la aplicación móvil.

require "conexion.php";
require "enviarCorreo.php";

$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "46.28.42.226",
  "bd"         => "u760464709_24005224_bd",
  "usuario"    => "u760464709_24005224_usr",
  "contrasena" => "8PEd!gd5x+Sb"
));

/*
$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "localhost",
  "bd"         => "clima_app",
  "usuario"    => "root",
  "contrasena" => ""
));
/*
echo "conexion_ok";
exit;
*/
//--------------------------------------------------------//

if (isset($_GET["iniciarSesion"])) {
  $select = $con->select("usuarios", "id");
  $select->where("usuario", "=", $_POST["usuario"]);
  $select->where_and("contrasena", "=", $_POST["contrasena"]);

  if (count($select->execute())) {
    echo "correcto";
  }
  else {
    echo "error";
  }
}

elseif (isset($_GET["consultas"])) {

    $select = $con->select(
        "view_consultas_detalle",
        "*"
    );

        if (!empty($_GET["buscar"])) {
            $select->where("descripcion", "LIKE", $_GET["buscar"]);
        }

        $select->orderby("id_consulta DESC");
        $select->limit(10);

        header("Content-Type: application/json");
        echo json_encode($select->execute());
}


elseif (isset($_GET["eliminarConsulta"])) {

    $delete = $con->delete("consultas_clima");
    $delete->where("id_consulta", "=", $_POST["txtId"]);

    if ($delete->execute()) {
        echo "correcto";
    } else {
        echo "error";
    }
}

elseif (isset($_GET["ciudadesCombo"])) {

    $select = $con->select("ciudades", "id_ciudad, nombre, pais");
    $select->orderby("nombre ASC");

    header("Content-Type: application/json");
    echo json_encode($select->execute());
}

elseif (isset($_GET["usuariosCombo"])) {

    $select = $con->select("usuarios", "id_usuario, nombre, email");
    $select->orderby("nombre ASC");

    header("Content-Type: application/json");
    echo json_encode($select->execute());
}


elseif (isset($_GET["agregarConsulta"])) {

    $insert = $con->insert(
        "consultas_clima",
        "id_usuario, id_ciudad, temperatura, descripcion, fecha_consulta"
    );

    $insert->value($_POST["cboUsuario"]);
    $insert->value($_POST["cboCiudad"]);
    $insert->value($_POST["txtTemperatura"]);
    $insert->value($_POST["txtDescripcion"]);
    $insert->value(date("Y-m-d H:i:s"));

    if ($insert->execute()) {
        echo "correcto";
        exit;
    } else {
        echo "error";
        exit;
    }
}



elseif (isset($_GET["obtenerConsulta"])) {

    $select = $con->select("consultas_clima", "*");
    $select->where("id_consulta", "=", $_GET["id"]);

    header("Content-Type: application/json");
    echo json_encode($select->execute());
}



elseif (isset($_GET["modificarConsulta"])) {

    $update = $con->update("consultas_clima");
    $update->set("id_usuario", $_POST["cboUsuario"]);
    $update->set("id_ciudad", $_POST["cboCiudad"]);
    $update->set("temperatura", $_POST["txtTemperatura"]);
    $update->set("descripcion", $_POST["txtDescripcion"]);
    $update->where("id_consulta", "=", $_POST["txtIdConsulta"]);

    if ($update->execute()) {
        echo "correcto";
    } else {
        echo "error";
    }
}

?>
