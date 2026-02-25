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
$db = new Conexion();
//////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////
// OBTENER PREFERENCIAS DEL USUARIO 
if (isset($_GET["obtener_preferencias"])) {

    if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
        echo json_encode([
            'unidad_temperatura' => 'Celsius',
            'tema' => 'claro',
            'logueado' => false
        ]);
        exit;
    }

    $stmt = $db->ejecutar(
        "SELECT unidad_temperatura, tema
         FROM preferencias_usuario
         WHERE id_usuario = ?",
        [$_SESSION['id_usuario']]
    );

    $pref = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'unidad_temperatura' => $pref['unidad_temperatura'] ?? 'Celsius',
        'tema' => $pref['tema'] ?? 'claro',
        'logueado' => true
    ]);
    exit;
}

////////////////////////////////////////////////////
// MOSTRAR TABLA DE PREFERENCIAS
elseif (isset($_GET["preferencias"])) {

    $stmt = $db->ejecutar(
        "SELECT 
            id_preferencia,
            unidad_temperatura,
            tema
         FROM preferencias_usuario"
    );

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

//////////////////////////////////
// GUARDAR PREFERENCIAS
elseif (isset($_GET["guardar_preferencias"])) {
    
    if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
        echo "error_no_session";
        exit;
    }
    
    $unidad = ($_POST['unidad'] === 'Fahrenheit') ? 'Fahrenheit' : 'Celsius';
    $tema = ($_POST['tema'] === 'oscuro') ? 'oscuro' : 'claro';
    
    // Verificar si ya existen preferencias
    $stmt = $db->ejecutar(
        "SELECT id_usuario FROM preferencias_usuario WHERE id_usuario = ?",
        [$_SESSION['id_usuario']]
    );
    
    if($stmt->rowCount() > 0) {
        // Actualizar
        $db->ejecutar(
            "UPDATE preferencias_usuario 
             SET unidad_temperatura = ?, tema = ? 
             WHERE id_usuario = ?",
            [$unidad, $tema, $_SESSION['id_usuario']]
        );
        echo "correcto";
    } else {
        // Insertar
        $db->ejecutar(
            "INSERT INTO preferencias_usuario 
             (id_usuario, unidad_temperatura, tema) 
             VALUES (?, ?, ?)",
            [$_SESSION['id_usuario'], $unidad, $tema]
        );
        echo "correcto";
    }
    exit;
}

?>