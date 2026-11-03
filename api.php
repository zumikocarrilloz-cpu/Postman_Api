<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once("controllers.php");

$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);
if (!is_array($jsonData)) {
    $jsonData = [];
}

$data = array_merge($_GET, $_POST, $jsonData);
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? ($_GET['action'] ?? ($data['accion'] ?? ($data['action'] ?? '')));

switch ($accion) {
    
    case 'usuarios':
    case 'users':
        echo json_encode(obtenerUsuarios(), JSON_UNESCAPED_UNICODE);
        break;

    case 'usuario':
    case 'user':
        $id = $data['id'] ?? null;
        echo json_encode(obtenerUsuarioPorId($id), JSON_UNESCAPED_UNICODE);
        break;

    case 'registrar':
    case 'registrar_usuario':
    case 'register':
        echo json_encode(registrarUsuario($data), JSON_UNESCAPED_UNICODE);
        break;

    case 'login':
    case 'iniciar_sesion':
        echo json_encode(iniciarSesion($data), JSON_UNESCAPED_UNICODE);
        break;

    case 'actualizar_usuario':
    case 'update_user':
        echo json_encode(actualizarUsuario($data), JSON_UNESCAPED_UNICODE);
        break;

    case 'eliminar_usuario':
    case 'delete_user':
        $id = $data['id'] ?? null;
        echo json_encode(eliminarUsuario($id), JSON_UNESCAPED_UNICODE);
        break;

    
    case 'crear_producto':
    case 'registrar_producto':
    case 'create_product':
        echo json_encode(crearProducto($data), JSON_UNESCAPED_UNICODE);
        break;

    case 'mis_productos':
    case 'productos_usuario':
    case 'my_products':
    case 'user_products':
        $usuarioId = $data['usuario_id'] ?? ($data['user_id'] ?? null);
        echo json_encode(obtenerProductosPorUsuario($usuarioId), JSON_UNESCAPED_UNICODE);
        break;

    case 'producto':
    case 'obtener_producto':
    case 'product':
        $productoId = $data['id'] ?? null;
        $usuarioId = $data['usuario_id'] ?? ($data['user_id'] ?? null);
        echo json_encode(obtenerProductoPorId($productoId, $usuarioId), JSON_UNESCAPED_UNICODE);
        break;

    case 'actualizar_producto':
    case 'update_product':
        echo json_encode(actualizarProducto($data), JSON_UNESCAPED_UNICODE);
        break;

    case 'eliminar_producto':
    case 'delete_product':
        $productoId = $data['id'] ?? null;
        $usuarioId = $data['usuario_id'] ?? ($data['user_id'] ?? null);
        echo json_encode(eliminarProducto($productoId, $usuarioId), JSON_UNESCAPED_UNICODE);
        break;

    default:
        if ($metodo === 'GET') {
            $usuarioId = $data['usuario_id'] ?? ($data['user_id'] ?? null);
            if (!empty($usuarioId) && !isset($data['id'])) {
                echo json_encode(obtenerProductosPorUsuario($usuarioId), JSON_UNESCAPED_UNICODE);
            } elseif (isset($data['id']) && !empty($usuarioId)) {
                echo json_encode(obtenerProductoPorId($data['id'], $usuarioId), JSON_UNESCAPED_UNICODE);
            } elseif (isset($data['id'])) {
                echo json_encode(obtenerUsuarioPorId($data['id']), JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(obtenerUsuarios(), JSON_UNESCAPED_UNICODE);
            }
        } elseif ($metodo === 'POST') {
            if (isset($data['precio']) || isset($data['price'])) {
                echo json_encode(crearProducto($data), JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(registrarUsuario($data), JSON_UNESCAPED_UNICODE);
            }
        } elseif ($metodo === 'PUT') {
            if (isset($data['precio']) || isset($data['price'])) {
                echo json_encode(actualizarProducto($data), JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(actualizarUsuario($data), JSON_UNESCAPED_UNICODE);
            }
        } elseif ($metodo === 'DELETE') {
            $usuarioId = $data['usuario_id'] ?? ($data['user_id'] ?? null);
            if (!empty($usuarioId) && isset($data['id'])) {
                echo json_encode(eliminarProducto($data['id'], $usuarioId), JSON_UNESCAPED_UNICODE);
            } elseif (isset($data['id'])) {
                echo json_encode(eliminarUsuario($data['id']), JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(["estado" => "error", "mensaje" => "Parámetros no válidos"], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "Acción no especificada o no válida"], JSON_UNESCAPED_UNICODE);
        }
        break;
}
?>
