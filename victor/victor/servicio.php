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
  "tipo" => "mysql",
  "servidor" => "46.28.42.226",
  "bd" => "u760464709_24005224_bd",
  "usuario" => "u760464709_24005224_usr",
  "contrasena" => "8PEd!gd5x+Sb"
));

if (isset($_GET["iniciarSesion"])) {
  $select->where("usuario", "=", $_POST["usuario"]);
  $select->where_and("contrasena", "=", $_POST["contrasena"]);

  if (count($select->execute())) {
    echo "correcto";
  } else {
    echo "error";
  }
} elseif (isset($_GET["usuarios"])) {

  $select = $con->select(
    "view_usuarios_favoritos",
    "*
    "
  );

  $select->limit(20);

  header("Content-Type: application/json");
  echo json_encode($select->execute());
} elseif (isset($_GET["editarProducto"])) {
  $id = $_GET["id"];

  $select = $con->select("productos", "*");
  $select->where("id", "=", $id);

  header("Content-Type: application/json");
  echo json_encode($select->execute());
}

///////////miooooo/////////////
elseif (isset($_GET["usuariosCombo"])) {

  $select = $con->select("usuarios", "id_usuario AS value, nombre AS label");
  $select->orderby("nombre ASC");

  $array = array(
    array("index" => 0, "value" => "", "label" => "Selecciona una opción")
  );

  foreach ($select->execute() as $x => $usuario) {
    $array[] = array(
      "index" => $x + 1,
      "value" => $usuario["value"],
      "label" => $usuario["label"]
    );
  }

  header("Content-Type: application/json");
  echo json_encode($array);
}



////////miooooo/////////
elseif (isset($_GET["eliminarFavorito"])) {

  if (!isset($_POST["id_usuario"])) {
    echo "Falta id_usuario";
    exit;
  }

  $delete = $con->delete("favoritos");
  $delete->where("id_usuario", "=", $_POST["id_usuario"]);

  if ($delete->execute()) {
    echo "correcto";
  } else {
    echo "error";
  }
} elseif (isset($_GET["ciudadesCombo"])) {

  $select = $con->select("ciudades", "id_ciudad AS value, nombre AS label");
  $select->orderby("nombre ASC");

  $array = array(
    array("index" => 0, "value" => "", "label" => "Selecciona una opción")
  );

  foreach ($select->execute() as $x => $usuario) {
    $array[] = array(
      "index" => $x + 1,
      "value" => $usuario["value"],
      "label" => $usuario["label"]
    );
  }

  header("Content-Type: application/json");
  echo json_encode($array);


} elseif (isset($_GET["agregarFavorito"])) {

  if (!isset($_POST["id_usuario"]) || !isset($_POST["id_ciudad"])) {
    echo "faltan_datos";
    exit;
  }

  $insert = $con->insert("favoritos", "id_usuario, id_ciudad");
  $insert->value($_POST["id_usuario"]);
  $insert->value($_POST["id_ciudad"]);

  if ($insert->execute()) {
    echo "correcto";
  } else {
    echo "error";
  }
} elseif (isset($_GET["editarFavorito"])) {

  $id = $_GET["id"];

  $select = $con->select("favoritos", "*");
  $select->where("id_favorito", "=", $id);

  header("Content-Type: application/json");
  echo json_encode($select->execute());
}


?>