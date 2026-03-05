<?php

session_start();

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

require "conexion.php";

$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "46.28.42.226",
  "bd"         => "u760464709_24005224_bd",
  "usuario"    => "u760464709_24005224_usr",
  "contrasena" => "8PEd!gd5x+Sb"
));

//////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////
// OBTENER PREFERENCIAS DEL USUARIO 
if (isset($_GET["obtener_preferencias"])) {

    if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
        echo json_encode([
            'unidad_temperatura' => 'Celsius',
            'logueado' => false
        ]);
        exit;
    }

    $stmt = $con->ejecutar(
        "SELECT id_usuario, nombre, preferencias
         FROM vista_preferencias_completo
         WHERE id_usuario = ?",
        [$_SESSION['id_usuario']]
    );

    $pref = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'id_usuario' => $pref['id_usuario'] ?? null,
        'nombre' => $pref['nombre'] ?? null,
        'preferencias' => $pref['preferencias'] ?? null,
        'logueado' => true
    ]);
    exit;
}

////////////////////////////////////////////////////
// MOSTRAR TABLA DE PREFERENCIAS
elseif (isset($_GET["preferencias"])) {
  $select = $con->select("vista_preferencias_completo");
    
  header("Content-Type: application/json");
  echo json_encode($select->execute());

}

/////////////////////////////////ELIMINAR PREFERENCIAS DE UN USUARIO
elseif (isset($_GET["eliminarpreferencia"])) {
    $idUsuario = $_POST["id_usuario"];
       $prepare = $con->prepare("CALL eliminar_preferencias (:p_id_usuario)");
      $prepare->bindParam(":p_id_usuario", $idUsuario);    

    // Comprobar si se borró algo
   $prepare->execute();

      if ($prepare->rowCount() > 0) {
        echo "correcto";
     } 
      else {
      echo "error";
    }

    exit;
}
/////////////////////////////////
// GUARDAR PREFERENCIAS
elseif (isset($_GET["guardar_preferencias"])) {
    
    if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
        echo "error_no_session";
        exit;
    }
    
    $unidad = ($_POST['unidad'] === 'Fahrenheit') ? 'Fahrenheit' : 'Celsius';
    $tema = ($_POST['tema'] === 'oscuro') ? 'oscuro' : 'claro';
    
    // Verificar si ya existen preferencias
$stmt = $con->ejecutar(
    "SELECT id_usuario FROM preferencias_usuario WHERE id_usuario = ?",
    [$_SESSION['id_usuario']]
);    if($stmt->rowCount() > 0) {
        // Actualizar
        $con->ejecutar(
            "UPDATE preferencias_usuario 
             SET unidad_temperatura = ?, tema = ? 
             WHERE id_usuario = ?",
            [$unidad, $tema, $_SESSION['id_usuario']]
        );
        echo "correcto";
    } else {
        // Insertar
        $con->ejecutar(
            "INSERT INTO preferencias_usuario 
             (id_usuario, unidad_temperatura, tema) 
             VALUES (?, ?, ?)",
            [$_SESSION['id_usuario'], $unidad, $tema]
        );
        echo "correcto";
    }
    exit;
}




elseif (isset($_GET["agregar_preferencia_sp"])) {

    $prepare = $con->prepare("CALL insertar_preferencias(:p_id_usuario, :p_unidad_temperatura, :p_tema)");

    $prepare->bindParam(":p_id_usuario", $_POST["id_usuario"]);
    $prepare->bindParam(":p_unidad_temperatura", $_POST["unidad"]);
    $prepare->bindParam(":p_tema", $_POST["tema"]);
    
    $prepare->execute();

    echo "correcto";
    exit;
}


elseif (isset($_GET["modificar_preferencia"])) {
    $prepare = $con->prepare("CALL actualizar_preferencias(:p_id_usuario, :p_unidad_temperatura, :p_tema)");

    $prepare->bindParam(":p_id_usuario", $_POST["id_usuario"]);
    $prepare->bindParam(":p_unidad_temperatura", $_POST["unidad"]);
    $prepare->bindParam(":p_tema", $_POST["tema"]);

    $prepare->execute();

    echo "correcto";
    exit;
}



?>      