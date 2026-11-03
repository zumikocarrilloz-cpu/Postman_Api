<?php
include_once("connection.php");

// ==============================================================
//                    CRUD DE USUARIOS
// ==============================================================

// 1. OBTENER USUARIOS
function obtenerUsuarios() {
    $con = connection();
    $sql = "SELECT id, nombre, apellido, usuario, correo FROM usuarios";
    $result = mysqli_query($con, $sql);
    
    $usuarios = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $usuarios[] = $row;
    }
    $con->close();
    return ["estado" => "exito", "datos" => $usuarios];
}

// 2. REGISTRAR USUARIO
function registrarUsuario($data) {
    $con = connection();
    $nombre = trim($data['nombre'] ?? '');
    $apellido = trim($data['apellido'] ?? '');
    $usuario = trim($data['usuario'] ?? '');
    $contrasena = $data['contrasena'] ?? '';
    $correo = trim($data['correo'] ?? '');

    if (empty($nombre) || empty($apellido) || empty($usuario) || empty($contrasena) || empty($correo)) {
        return ["estado" => "error", "mensaje" => "Todos los campos son obligatorios"];
    }

    $passwordHash = password_hash($contrasena, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuarios (nombre, apellido, usuario, contrasena, correo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $apellido, $usuario, $passwordHash, $correo);

    if ($stmt->execute()) {
        $response = ["estado" => "exito", "mensaje" => "Usuario registrado exitosamente"];
    } else {
        $response = ["estado" => "error", "mensaje" => "Error al registrar: " . $stmt->error];
    }
    
    $stmt->close();
    $con->close();
    return $response;
}

// 3. INICIAR SESIÓN / AUTENTICACIÓN
function iniciarSesion($data) {
    $con = connection();
    $usuario = trim($data['usuario'] ?? '');
    $contrasena = $data['contrasena'] ?? '';

    if (empty($usuario) || empty($contrasena)) {
        return ["estado" => "error", "mensaje" => "Faltan credenciales"];
    }

    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($contrasena, $row['contrasena'])) {
            $stmt->close();
            $con->close();
            return [
                "estado" => "exito", 
                "mensaje" => "Login exitoso", 
                "usuario" => [
                    "id" => $row['id'],
                    "nombre" => $row['nombre'],
                    "usuario" => $row['usuario'],
                    "correo" => $row['correo']
                ]
            ];
        }
    }
    
    $stmt->close();
    $con->close();
    return ["estado" => "error", "mensaje" => "Credenciales incorrectas"];
}

// 4. ACTUALIZAR USUARIO
function actualizarUsuario($data) {
    $con = connection();
    $id = $data['id'] ?? null;
    $nombre = trim($data['nombre'] ?? '');
    $apellido = trim($data['apellido'] ?? '');
    $usuario = trim($data['usuario'] ?? '');
    $correo = trim($data['correo'] ?? '');

    if (empty($id)) {
        return ["estado" => "error", "mensaje" => "ID es obligatorio para actualizar"];
    }

    $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, usuario = ?, correo = ? WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $apellido, $usuario, $correo, $id);

    if ($stmt->execute()) {
        $response = ["estado" => "exito", "mensaje" => "Usuario actualizado correctamente"];
    } else {
        $response = ["estado" => "error", "mensaje" => "Error al actualizar"];
    }

    $stmt->close();
    $con->close();
    return $response;
}

// 5. CONSULTAR SOLO INFO DE UN USUARIO POR ID
function obtenerUsuarioPorId($id) {
    $con = connection();
    if (empty($id)) {
        return ["estado" => "error", "mensaje" => "ID no proporcionado"];
    }

    $sql = "SELECT id, nombre, apellido, usuario, correo FROM usuarios WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        $con->close();
        return ["estado" => "exito", "usuario" => $row];
    }

    $stmt->close();
    $con->close();
    return ["estado" => "error", "mensaje" => "Usuario no encontrado"];
}

// 6. ELIMINAR USUARIO (Primero elimina sus productos y luego el usuario)
function eliminarUsuario($id) {
    $con = connection();
    if (empty($id)) {
        return ["estado" => "error", "mensaje" => "ID no proporcionado"];
    }

    // 1. Elimina primero los productos de este usuario
    $sqlProd = "DELETE FROM productos WHERE usuario_id = ?";
    $stmtProd = $con->prepare($sqlProd);
    $stmtProd->bind_param("i", $id);
    $stmtProd->execute();
    $stmtProd->close();

    // 2. Elimina el usuario
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response = ["estado" => "exito", "mensaje" => "Usuario eliminado correctamente"];
    } else {
        $response = ["estado" => "error", "mensaje" => "Error al eliminar"];
    }

    $stmt->close();
    $con->close();
    return $response;
}


// ==============================================================
//                    CRUD DE PRODUCTOS
// ==============================================================

// 1. REGISTRAR PRODUCTO
function crearProducto($data) {
    $con = connection();
    $usuario_id = $data['usuario_id'] ?? null;
    $nombre = trim($data['nombre'] ?? '');
    $descripcion = trim($data['descripcion'] ?? '');
    $precio = $data['precio'] ?? null;
    $stock = $data['stock'] ?? 0;

    if (empty($usuario_id) || empty($nombre) || empty($precio)) {
        return ["estado" => "error", "mensaje" => "usuario_id, nombre y precio son obligatorios"];
    }

    $sql = "INSERT INTO productos (usuario_id, nombre, descripcion, precio, stock) VALUES (?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("issdi", $usuario_id, $nombre, $descripcion, $precio, $stock);

    if ($stmt->execute()) {
        $response = ["estado" => "exito", "mensaje" => "Producto registrado exitosamente"];
    } else {
        $response = ["estado" => "error", "mensaje" => "Error al registrar producto: " . $stmt->error];
    }

    $stmt->close();
    $con->close();
    return $response;
}

// 2. CONSULTAR PRODUCTOS DEL USUARIO E INFO DEL USUARIO
function obtenerProductosPorUsuario($usuario_id) {
    $con = connection();
    if (empty($usuario_id)) {
        return ["estado" => "error", "mensaje" => "usuario_id es obligatorio"];
    }

    // Info del usuario
    $sqlUser = "SELECT id, nombre, apellido, usuario, correo FROM usuarios WHERE id = ?";
    $stmtUser = $con->prepare($sqlUser);
    $stmtUser->bind_param("i", $usuario_id);
    $stmtUser->execute();
    $userResult = $stmtUser->get_result();

    if ($userRow = $userResult->fetch_assoc()) {
        // Productos de ese usuario
        $sqlProd = "SELECT id, nombre, descripcion, precio, stock FROM productos WHERE usuario_id = ?";
        $stmtProd = $con->prepare($sqlProd);
        $stmtProd->bind_param("i", $usuario_id);
        $stmtProd->execute();
        $prodResult = $stmtProd->get_result();

        $productos = [];
        while ($p = $prodResult->fetch_assoc()) {
            $productos[] = $p;
        }

        $stmtProd->close();
        $stmtUser->close();
        $con->close();

        return [
            "estado" => "exito",
            "usuario" => $userRow,
            "productos" => $productos
        ];
    }

    $stmtUser->close();
    $con->close();
    return ["estado" => "error", "mensaje" => "Usuario no encontrado"];
}

// 3. CONSULTAR UN PRODUCTO (Con restricción de acceso)
function obtenerProductoPorId($producto_id, $usuario_id) {
    $con = connection();
    if (empty($producto_id) || empty($usuario_id)) {
        return ["estado" => "error", "mensaje" => "id y usuario_id son obligatorios"];
    }

    $sql = "SELECT * FROM productos WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        $con->close();

        // Si el producto no le pertenece a este usuario
        if ($row['usuario_id'] != $usuario_id) {
            return ["estado" => "error", "mensaje" => "no tienes acceso a esta información"];
        }

        return ["estado" => "exito", "producto" => $row];
    }

    $stmt->close();
    $con->close();
    return ["estado" => "error", "mensaje" => "Producto no encontrado"];
}

// 4. ACTUALIZAR PRODUCTO
function actualizarProducto($data) {
    $con = connection();
    $id = $data['id'] ?? null;
    $usuario_id = $data['usuario_id'] ?? null;
    $nombre = trim($data['nombre'] ?? '');
    $descripcion = trim($data['descripcion'] ?? '');
    $precio = $data['precio'] ?? null;
    $stock = $data['stock'] ?? 0;

    if (empty($id) || empty($usuario_id)) {
        return ["estado" => "error", "mensaje" => "id y usuario_id son obligatorios"];
    }

    $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ? WHERE id = ? AND usuario_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssdiii", $nombre, $descripcion, $precio, $stock, $id, $usuario_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $response = ["estado" => "exito", "mensaje" => "Producto actualizado exitosamente"];
    } else {
        $response = ["estado" => "error", "mensaje" => "no tienes acceso a esta información o no hubo cambios"];
    }

    $stmt->close();
    $con->close();
    return $response;
}

// 5. ELIMINAR PRODUCTO
function eliminarProducto($id, $usuario_id) {
    $con = connection();
    if (empty($id) || empty($usuario_id)) {
        return ["estado" => "error", "mensaje" => "id y usuario_id son obligatorios"];
    }

    $sql = "DELETE FROM productos WHERE id = ? AND usuario_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $id, $usuario_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $response = ["estado" => "exito", "mensaje" => "Producto eliminado exitosamente"];
    } else {
        $response = ["estado" => "error", "mensaje" => "no tienes acceso a esta información"];
    }

    $stmt->close();
    $con->close();
    return $response;
}
?>
