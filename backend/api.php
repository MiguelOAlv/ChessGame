<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    $paths = explode('/', trim($uri, '/'));
    $resource = basename(parse_url($uri, PHP_URL_PATH));

    switch ($resource) {
        case 'login':
            if (isset($data["nombre"]) && isset($data["contrasena"])) {
                autenticarUsuario($data["nombre"], $data["contrasena"]);
            }
            break;
        case 'register':
            $base64Avatar = $data['avatar'];
            registrarUsuario($data["nombre"], $data["contrasena"], $data["email"], $base64Avatar);
            break;
        case 'avatar':
            $idUsuario = $data["idUsuario"];
            obtenerAvatarPorID($idUsuario);
            break;
        case 'subirAvatar':
            $base64Avatar = $data['avatarData'];
            $idUsuario = $data["idUsuario"];
            subirAvatar($base64Avatar, $idUsuario);
        case 'usuario':
            $idUsuario = $data["idUsuario"];
            $token = $data["token"];
            obtenerDatos($idUsuario, $token);
            break;
        case 'actualizarPerfil':
            $idUsuario = $data["idUsuario"];
            $nombre = $correo = $contrasena = '';
            if (isset($data['nombre'])) {
                $nombre = $data['nombre'];
            }
            if (isset($data['correo'])) {
                $correo = $data['correo'];
            }
            if (isset($data['contrasena'])) {
                if ($data['contrasena'] !== '') {
                    $contrasena = $data['contrasena'];
                }
            }
            if ($nombre !== '' || $correo !== '' || $contrasena !== '') {
                actualizarPerfil($idUsuario, $nombre, $contrasena, $correo);
            } else {
                header('Content-Type: application/json');
                echo json_encode(["success" => false, "message" => "Error: Al menos uno de los campos (nombre, correo, contraseña) debe ser proporcionado para actualizar el perfil."]);
            }
            break;
        case 'obtenerEstadisticas':
            $idUsuario = $data["idUsuario"];
            obtenerEstadisticas($idUsuario);
            break;
        case 'buscarUsuario':
            $nombreAmigo = $data["nombreUsuario"];
            buscarAmigosPorNombre($nombreAmigo);
            break;
        case 'agregarAmigo':
            $amigoId = $data["amigoId"];
            $usuarioId = $data["usuarioId"];
            agregarAmigo($amigoId, $usuarioId);
            break;
        case 'borrarAmigo':
            $amigoId = $data["amigoId"];
            $usuarioId = $data["usuarioId"];
            borrarAmigo($amigoId, $usuarioId);
            break;
        case 'obtenerAmigos':
            $usuarioId = $data["usuarioId"];
            obtenerAmigos($usuarioId);
            break;
        case 'obtenerSolicitudes':
            $usuarioId = $data["usuarioId"];
            obtenerSolicitudes($usuarioId);
            break;
        case 'rechazarSolicitud':
            $amigoId = $data["amigoId"];
            $usuarioId = $data["usuarioId"];
            rechazarSolicitud($amigoId, $usuarioId);
            break;
        case 'aceptarSolicitud':
            $amigoId = $data["amigoId"];
            $usuarioId = $data["usuarioId"];
            aceptarSolicitud($amigoId, $usuarioId);
            break;
        case 'enviarSolicitudPartida':
            $amigoId = $data["amigoId"];
            $usuarioId = $data["usuarioId"];
            $tiempoPorJugada = $data["tiempoPorJugadaSeleccionado"];
            $colorPiezasUsuario = $data["colorPiezasSeleccionado"];
            $partidaPuntuada = $data["partidaPuntuada"];
            enviarSolicitudPartida($amigoId, $usuarioId, $tiempoPorJugada, $colorPiezasUsuario, $partidaPuntuada);
            break;
        case 'obtenerPartidas':
            $usuarioId = $data["usuarioId"];
            obtenerPartidas($usuarioId);
            break;
        case 'obtenerPartida':
            $idPartida = $data["idPartida"];
            obtenerPartida($idPartida);
            break;
        case 'actualizarPartida':
            $idPartida = $data["idPartida"];
            $usuarioId = $data["usuarioId"];
            $nuevoEstado = $data['nuevoEstado'];
            actualizarPartida($idPartida, $usuarioId, $nuevoEstado);
            break;
        case 'abandonarPartida':
            $idPartida = $data["idPartida"];
            $usuarioId = $data["usuarioId"];
            $estado = $data['estado'];
            abandonarPartida($idPartida, $usuarioId, $estado);
            break;
        case 'finalizarPartida':
            $idPartida = $data["idPartida"];
            $colorPieza = $data["colorPieza"];
            $endGameReason = $data["endGameReason"];
            finalizarPartida($idPartida, $colorPieza, $endGameReason);
            break;
        default:
            // Endpoint no encontrado
            http_response_code(404);
            echo json_encode(["message" => "Endpoint no encontrado"]);
            break;
    }
}
function get_connection()
{
    $dsn = 'mysql:host=localhost;dbname=chessdb';
    $user = 'root';
    $pass = '1234';
    $opciones = [];
    try {
        $con = new PDO($dsn, $user, $pass);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Fallo la conexion: " . $e->getMessage();
    };
    return $con;
}
// Función para autenticar usuario
function autenticarUsuario($user, $contraseña)
{

    $con = get_connection();
    // Consulta SQL para autenticar al usuario
    $sql = "SELECT idusuarios, nombre, contraseña, perfil FROM usuarios WHERE (nombre=:user OR correo=:user) AND estado=1";
    $statement = $con->prepare($sql);
    $statement->bindParam(':user', $user);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Verificar la contraseña
        if (password_verify("$contraseña", $result["contraseña"])) {
            $sessionToken = bin2hex(random_bytes(32));
            $updateSql = "UPDATE usuarios SET token=:token WHERE idusuarios=:userId";
            $updateStatement = $con->prepare($updateSql);
            $updateStatement->bindParam(':token', $sessionToken);
            $updateStatement->bindParam(':userId', $result["idusuarios"]);
            if($updateStatement->execute()){
                $response = array("success" => true, "message" => "Inicio de sesión exitoso", "userId" => $result["idusuarios"], "token" => $sessionToken);
            }else{
                $response = array("success" => false, "message" => "Inicio de sesión fallido");
            }
        } else {
            $response = array("success" => false, "message" => "Credenciales incorrectas");
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        $response = array("success" => false, "message" => "Credenciales incorrectas");
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
function registrarUsuario($nombre, $contraseña, $email, $avatar)
{
    $con = get_connection();

    // Verificar si el usuario ya existe en la base de datos
    $sql = "SELECT idusuarios FROM usuarios WHERE nombre=:nombre OR correo=:email";
    $statement = $con->prepare($sql);
    $statement->bindParam(':nombre', $nombre);
    $statement->bindParam(':email', $email);
    $statement->execute();
    $existingUser = $statement->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // El usuario ya existe, devolver un mensaje de error
        $response = array("success" => false, "message" => "El usuario ya está registrado");
        header('Content-Type: application/json');
        echo json_encode($response);
        return;
    }

    // Hash de la contraseña
    $hashedPassword = password_hash($contraseña, PASSWORD_DEFAULT);

    // Fecha actual
    $fechaCreacion = date('Y-m-d');

    // Estado y perfil predeterminados
    $estado = 1;
    $perfil = 1;
    //$avatarData = substr($avatar, strpos($avatar, ",") + 1);
    //$avatarData = base64_decode($avatar);
    $parts = explode(',', $avatar);
    // Tomar la segunda parte que contiene la parte base64
    $base64Content = $parts[1];
    // Decodificar el avatar base64 para obtener los datos binarios
    $avatarBinary = base64_decode($base64Content);

    // Insertar el nuevo usuario en la base de datos con el avatar en formato base64
    $sql = "INSERT INTO usuarios (nombre, contraseña, correo, fechaCreacion, estado, perfil, avatar) VALUES (:nombre, :contrasena, :correo, :fechaCreacion, :estado, :perfil, :avatar)";
    $statement = $con->prepare($sql);
    $statement->bindParam(':nombre', $nombre);
    $statement->bindParam(':contrasena', $hashedPassword);
    $statement->bindParam(':correo', $email);
    $statement->bindParam(':fechaCreacion', $fechaCreacion);
    $statement->bindParam(':estado', $estado);
    $statement->bindParam(':perfil', $perfil);
    $statement->bindParam(':avatar', $avatarBinary, PDO::PARAM_LOB);
    $success = $statement->execute();

    if ($success) {
        // Usuario registrado exitosamente
        $response = array("success" => true, "message" => "Usuario registrado exitosamente");
    } else {
        // Error al registrar el usuario
        $response = array("success" => false, "message" => "Error al registrar el usuario");
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}
//Funcion para subir un nuevo avatar
function subirAvatar($avatar, $idUsuario)
{
    $con = get_connection();

    $parts = explode(',', $avatar);
    // Tomar la segunda parte que contiene la parte base64
    $base64Content = $parts[1];
    // Decodificar el avatar base64 para obtener los datos binarios
    $avatarBinary = base64_decode($base64Content);

    $sql = "UPDATE usuarios SET avatar = :avatar WHERE idusuarios = :idusuarios";
    $statement = $con->prepare($sql);
    $statement->bindParam(':avatar', $avatarBinary, PDO::PARAM_LOB);
    $statement->bindParam(':idusuarios', $idUsuario);
    $success = $statement->execute();

    if ($success) {
        // Avatar actualizado correctamente
        $response = array("success" => true, "message" => "Avatar actualizado correctamente");
    } else {
        // Error al subir el avatar
        $response = array("success" => false, "message" => "Error al subir el avatar");
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}
//Funcion obtener avatar por ID
function obtenerAvatarPorID($idUsuario)
{
    $conn = get_connection();

    // Consulta para obtener el avatar por ID de usuario
    $sql = "SELECT avatar FROM usuarios WHERE idusuarios = $idUsuario";
    $stmt = $conn->prepare($sql);
    //$stmt->bindValue(':idUsuario', $idUsuario);
    $stmt->execute();
    $blob = $stmt->fetchColumn();
    // Codificar el Blob como una cadena base64
    $base64Avatar = base64_encode($blob);
    // Crear un array asociativo con el avatar codificado en base64
    $response = array('avatar' => $base64Avatar);
    // Devolver el JSON con el avatar codificado
    header('Content-Type: application/json');
    echo json_encode($response);
}
//Funcion obtener datos por id
function obtenerDatos($idUsuario, $token)
{
    if (!validarToken($idUsuario, $token)) {
        // Si el token no es válido, responder con un error de autorización
        $response = array("success" => false, "message" => "Token no valido");
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    $conn = get_connection();

    // Consulta para obtener los datos del usuario y el avatar por ID de usuario
    $sql = "SELECT nombre, contraseña, correo, fechaCreacion, avatar FROM usuarios WHERE idusuarios = :idUsuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idUsuario', $idUsuario);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        // Convertir el avatar blob a base64
        $avatarBlob = $userData['avatar'];
        $base64Avatar = base64_encode($avatarBlob);

        // Agregar el avatar en formato base64 al array de datos del usuario
        $userData['avatar'] = $base64Avatar;

        // Devolver los datos del usuario junto con el avatar en formato base64
        header('Content-Type: application/json');
        echo json_encode($userData);
    } else {
        // Usuario no encontrado
        http_response_code(404);
        echo json_encode(["message" => "Usuario no encontrado"]);
    }
}
//Funcion para actualizar el perfil del usuario
function actualizarPerfil($idUsuario, $nombre, $contraseña, $email)
{
    $con = get_connection();

    // Verificar si el usuario existe en la base de datos
    $sql = "SELECT idusuarios FROM usuarios WHERE idusuarios = :idUsuario";
    $statement = $con->prepare($sql);
    $statement->bindParam(':idUsuario', $idUsuario);
    $statement->execute();
    $existingUser = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$existingUser) {
        // El usuario no existe, devolver un mensaje de error
        $response = array("success" => false, "message" => "El usuario no existe");
        // Devuelve los resultados como JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    // Verificar si el usuario ya existe en la base de datos
    $sql = "SELECT idusuarios FROM usuarios WHERE (nombre = :nombre OR correo = :email) AND idusuarios != :idUsuario";
    $statement = $con->prepare($sql);
    $statement->bindParam(':nombre', $nombre);
    $statement->bindParam(':email', $email);
    $statement->bindParam(':idUsuario', $idUsuario);
    $statement->execute();
    $existingUser = $statement->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // El usuario ya existe, devolver un mensaje de error
        $response = array("success" => false, "message" => "El nombre o el correo ya estan siendo utilizados");
        header('Content-Type: application/json');
        echo json_encode($response);
        return;
    }
    // Inicializar las variables para los nuevos valores
    $newNombre = $nombre;
    $newEmail = $email;

    // Validar y hash de la nueva contraseña si se proporciona
    $hashedPassword = null;
    if (!empty($contraseña)) {
        $hashedPassword = password_hash($contraseña, PASSWORD_DEFAULT);
    }

    // Construir la consulta SQL dinámicamente según los valores proporcionados
    $sql = "UPDATE usuarios SET";
    $updates = [];
    if ($newNombre !== '') {
        $updates[] = " nombre = :nombre";
    }
    if ($hashedPassword !== null) {
        $updates[] = " contraseña = :contrasena";
    }
    if ($newEmail !== '') {
        $updates[] = " correo = :email";
    }
    $sql .= implode(',', $updates);
    $sql .= " WHERE idusuarios = :idUsuario";

    // Preparar y ejecutar la consulta
    $statement = $con->prepare($sql);
    $statement->bindParam(':idUsuario', $idUsuario);
    if ($newNombre !== '') {
        $statement->bindParam(':nombre', $newNombre);
    }
    if ($hashedPassword !== null) {
        $statement->bindParam(':contrasena', $hashedPassword);
    }
    if ($newEmail !== '') {
        $statement->bindParam(':email', $newEmail);
    }
    $success = $statement->execute();

    if ($success) {
        // Usuario actualizado exitosamente
        $response = array("success" => true, "message" => "Usuario actualizado exitosamente");
    } else {
        // Error al actualizar el usuario
        $response = array("success" => false, "message" => "Error al actualizar el usuario");
    }

    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

//Funcion para obtener estadisticas del usuario
function obtenerEstadisticas($idUsuario)
{
    $conn = get_connection();

    // Consulta para obtener los datos del usuario por ID de usuario
    $sql = "SELECT derrotas, empates, victorias, puntuacion FROM statsusuarios WHERE idUsuario = :idUsuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idUsuario', $idUsuario);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        // Si se encontraron datos, devolverlos en formato JSON
        header('Content-Type: application/json');
        echo json_encode(["success" => true, "message" => "Estadísticas encontradas", "stats" => $userData]);
    } else {
        // Si no se encontraron datos, devolver un mensaje de error en JSON y establecer el código de respuesta HTTP en 404
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Estadísticas no encontradas"]);
    }
}

//Buscador de usuarios
function buscarAmigosPorNombre($nombreAmigo)
{
    $conn = get_connection();
    $stmt = $conn->prepare("SELECT nombre, avatar, idusuarios FROM usuarios WHERE nombre LIKE :nombreAmigo");
    $stmt->bindValue(':nombreAmigo', "%$nombreAmigo%", PDO::PARAM_STR);
    $stmt->execute();

    // Inicializa un array para almacenar los amigos encontrados
    $amigos = array();

    // Itera sobre los resultados y agrega cada amigo al array de amigos
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nombre = $row["nombre"];
        $avatar = $row["avatar"];
        $id = $row["idusuarios"];
        $base64Avatar = base64_encode($avatar);

        // Agrega el amigo actual al array de amigos
        $amigos[] = array(
            "nombre" => $nombre,
            "avatar" => $base64Avatar,
            "id" => $id
        );
    }

    // Verifica si se encontraron resultados
    if (!empty($amigos)) {
        $response = array("success" => true, "message" => "Se encontraron resultados", "amigos" => $amigos);
    } else {
        $response = array("success" => false, "message" => "No se encontraron resultados para el nombre proporcionado");
    }

    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
function agregarAmigo($amigoId, $usuarioId)
{
    $conn = get_connection();

    // Verificar si el usuario está intentando agregarse a sí mismo
    if ($amigoId == $usuarioId) {
        $response = array("success" => false, "message" => "No puedes agregarte a ti mismo como amigo");
    } else {
        // Verificar si el usuario ya es amigo del amigo que se está intentando agregar
        $stmt = $conn->prepare("SELECT * FROM amigos 
        WHERE 
            ((usuario_id = :usuario_id AND amigo_id = :amigo_id) 
            OR 
            (usuario_id = :amigo_id AND amigo_id = :usuario_id))
        AND 
            estado = 'aceptado' AND borrado = 0");
        $stmt->bindParam(':usuario_id', $amigoId);
        $stmt->bindParam(':amigo_id', $usuarioId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Si el usuario ya es amigo del amigo que se intenta agregar, devolver un error
            $response = array("success" => false, "message" => "El usuario agregado ya es tu amigo");
        } else {
            // Verificar si ya existe una solicitud de amistad entre los usuarios (independientemente del estado)
            $stmt = $conn->prepare("SELECT * FROM amigos WHERE ((usuario_id = :usuario_id AND amigo_id = :amigo_id) OR (usuario_id = :amigo_id AND amigo_id = :usuario_id)) AND borrado = 1");
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->bindParam(':amigo_id', $amigoId);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Si ya existe una solicitud, verificar su estado
                if ($row['estado'] == 'rechazado' || $row['estado'] == 'aceptado' || $row['estado'] == 'pendiente' && $row['borrado'] == 1) {
                    if ($row['amigo_id'] == $usuarioId) {
                        // Si coincide, actualizar los campos usuario_id y amigo_id
                        $amigoIdOriginal = $row['amigo_id'];
                        $usuarioIdOriginal = $row['usuario_id'];
                        $stmt = $conn->prepare("UPDATE amigos SET usuario_id = :usuario_id, amigo_id = :amigo_id, estado='pendiente', borrado = 0 WHERE (usuario_id = :usuario_id_original AND amigo_id = :amigo_id_original)");
                        $stmt->bindParam(':usuario_id', $usuarioId);
                        $stmt->bindParam(':amigo_id', $amigoId);
                        $stmt->bindParam(':usuario_id_original', $usuarioIdOriginal);
                        $stmt->bindParam(':amigo_id_original', $amigoIdOriginal);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            $response = array("success" => true, "message" => "Solicitud de amistad enviada correctamente");
                        } else {
                            $response = array("success" => false, "message" => "Error al enviar la solicitud de amistad");
                        }
                    } else {
                        // Si la solicitud fue rechazada y luego eliminada, actualizar el estado y enviar la solicitud nuevamente
                        $stmt = $conn->prepare("UPDATE amigos SET estado = 'pendiente', borrado = 0 WHERE ((usuario_id = :usuario_id AND amigo_id = :amigo_id) OR (usuario_id = :amigo_id AND amigo_id = :usuario_id))");
                        $stmt->bindParam(':usuario_id', $usuarioId);
                        $stmt->bindParam(':amigo_id', $amigoId);
                        $stmt->execute();
                    }
                    if ($stmt->rowCount() > 0) {
                        $response = array("success" => true, "message" => "Solicitud de amistad enviada correctamente");
                    } else {
                        $response = array("success" => false, "message" => "Error al enviar la solicitud de amistad");
                    }
                } else {
                    // Si la solicitud está pendiente o aceptada, devolver un mensaje de error
                    $response = array("success" => false, "message" => "Ya has enviado una solicitud de amistad a este usuario o ya son amigos");
                }
            } else {

                // Verificar si existe una solicitud de amistad rechazada pero no borrada
                $stmt = $conn->prepare("SELECT * FROM amigos WHERE (usuario_id = :usuario_id AND amigo_id = :amigo_id) 
                            OR (usuario_id = :amigo_id AND amigo_id = :usuario_id) 
                            AND estado = 'rechazado' AND borrado = 0");
                $stmt->bindParam(':usuario_id', $usuarioId);
                $stmt->bindParam(':amigo_id', $amigoId);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    // Verificar si el usuario actual coincide con amigo_id de la fila seleccionada
                    if ($row['amigo_id'] == $usuarioId) {
                        // Si coincide, actualizar los campos usuario_id y amigo_id
                        $amigoIdOriginal = $row['amigo_id'];
                        $usuarioIdOriginal = $row['usuario_id'];
                        $stmt = $conn->prepare("UPDATE amigos SET usuario_id = :usuario_id, amigo_id = :amigo_id, estado='pendiente' WHERE (usuario_id = :usuario_id_original AND amigo_id = :amigo_id_original)");
                        $stmt->bindParam(':usuario_id', $usuarioId);
                        $stmt->bindParam(':amigo_id', $amigoId);
                        $stmt->bindParam(':usuario_id_original', $usuarioIdOriginal);
                        $stmt->bindParam(':amigo_id_original', $amigoIdOriginal);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            $response = array("success" => true, "message" => "Solicitud de amistad enviada correctamente");
                        } else {
                            $response = array("success" => false, "message" => "Error al enviar la solicitud de amistad");
                        }
                    } else {

                        // Si existe una solicitud rechazada pero no borrada, actualizar el estado a 'pendiente'
                        $stmt = $conn->prepare("UPDATE amigos SET estado = 'pendiente' WHERE (usuario_id = :usuario_id AND amigo_id = :amigo_id) 
                                OR (usuario_id = :amigo_id AND amigo_id = :usuario_id)");
                        $stmt->bindParam(':usuario_id', $usuarioId);
                        $stmt->bindParam(':amigo_id', $amigoId);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            $response = array("success" => true, "message" => "Solicitud de amistad enviada correctamente");
                        } else {
                            $response = array("success" => false, "message" => "Error al enviar la solicitud de amistad");
                        }
                    }
                } else {
                    // Insertar una nueva solicitud de amistad
                    $stmt = $conn->prepare("INSERT INTO amigos (usuario_id, amigo_id, estado) VALUES (:usuario_id, :amigo_id, 'pendiente')");
                    $stmt->bindParam(':usuario_id', $usuarioId);
                    $stmt->bindParam(':amigo_id', $amigoId);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $response = array("success" => true, "message" => "Solicitud de amistad enviada correctamente");
                    } else {
                        $response = array("success" => false, "message" => "Error al enviar la solicitud de amistad");
                    }
                }
            }
        }
    }

    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}


function borrarAmigo($amigoId, $usuarioId)
{
    try {
        $conn = get_connection();

        // Actualizar el estado de la amistad a "borrado" en la tabla de amigos
        $stmt = $conn->prepare("UPDATE amigos SET borrado = 1 WHERE (usuario_id = :usuario_id AND amigo_id = :amigo_id) OR (usuario_id = :amigo_id AND amigo_id = :usuario_id)");
        $stmt->bindParam(':amigo_id', $amigoId);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        // Verificar si se actualizó correctamente
        if ($stmt->rowCount() > 0) {
            $response = array("success" => true, "message" => "Amigo borrado correctamente");
        } else {
            $response = array("success" => false, "message" => "No se encontró la amistad para borrar");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al borrar amigo: " . $e->getMessage());
    }
    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
function obtenerAmigos($usuarioId)
{
    try {
        $conn = get_connection();

        // Consulta para obtener los amigos del usuario
        $stmt = $conn->prepare("SELECT usuarios.idusuarios, usuarios.nombre, usuarios.avatar 
        FROM amigos 
        INNER JOIN usuarios 
        ON ((amigos.amigo_id = usuarios.idusuarios AND amigos.usuario_id = :usuario_id)
            OR (amigos.usuario_id = usuarios.idusuarios AND amigos.amigo_id = :usuario_id))
        WHERE amigos.estado = 'aceptado' 
        AND amigos.borrado = 0");
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();
        // Inicializa un array para almacenar los amigos encontrados
        $amigos = array();

        // Itera sobre los resultados y agrega cada amigo al array de amigos
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nombre = $row["nombre"];
            $avatar = $row["avatar"];
            $id = $row["idusuarios"];
            $base64Avatar = base64_encode($avatar);

            // Verifica si el usuario actual es el amigo o el usuario
            if ($id == $usuarioId) {
                // El usuario actual es el amigo, por lo tanto, usa el otro ID como ID de amigo
                $idAmigo = ($row["amigo_id"] == $usuarioId) ? $row["usuario_id"] : $row["amigo_id"];
            } else {
                $idAmigo = $id;
            }
            // Agrega el amigo actual al array de amigos
            $amigos[] = array(
                "nombre" => $nombre,
                "avatar" => $base64Avatar,
                "id" => $idAmigo
            );
        }


        // Verificar si se encontraron amigos
        if (!empty($amigos)) {
            $response = array("success" => true, "message" => "Amigos encontrados", "amigos" => $amigos);
        } else {
            $response = array("success" => false, "message" => "No se encontraron amigos para este usuario");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al obtener amigos: " . $e->getMessage());
    }
    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
function obtenerSolicitudes($usuarioId)
{
    try {
        $conn = get_connection();

        // Consulta para obtener las solicitudes entrantes del usuario
        $stmt = $conn->prepare("SELECT solicitante.idusuarios AS idSolicitante, solicitante.nombre AS nombreSolicitante, solicitante.avatar AS avatarSolicitante
        FROM amigos
        INNER JOIN usuarios AS receptor ON amigos.amigo_id = receptor.idusuarios
        INNER JOIN usuarios AS solicitante ON amigos.usuario_id = solicitante.idusuarios
        WHERE receptor.idusuarios = :usuario_id
        AND amigos.estado = 'pendiente'
        AND amigos.borrado = 0;
        ");
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();
        // Inicializa un array para almacenar los amigos encontrados
        $amigos = array();

        // Itera sobre los resultados y agrega cada amigo al array de amigos
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nombreSolicitante = $row["nombreSolicitante"];
            $avatarSolicitante = $row["avatarSolicitante"];
            $idSolicitante = $row["idSolicitante"];
            $base64Avatar = base64_encode($avatarSolicitante);

            // Agrega el amigo actual al array de amigos
            $amigos[] = array(
                "nombre" => $nombreSolicitante,
                "avatar" => $base64Avatar,
                "id" => $idSolicitante
            );
        }

        // Verificar si se encontraron solicitudes
        if (!empty($amigos)) {
            $response = array("success" => true, "message" => "Solicitudes encontradas", "amigos" => $amigos);
        } else {
            $response = array("success" => false, "message" => "No se encontraron solicitudes para este usuario");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al obtener amigos: " . $e->getMessage());
    }
    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
function rechazarSolicitud($amigoId, $usuarioId)
{
    try {
        $conn = get_connection();

        // Actualizar el estado de la amistad a "rechazado" en la tabla de amigos
        $stmt = $conn->prepare("UPDATE amigos SET estado = 'rechazado' WHERE (usuario_id = :usuario_id AND amigo_id = :amigo_id) OR (usuario_id = :amigo_id AND amigo_id = :usuario_id)");
        $stmt->bindParam(':amigo_id', $amigoId);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        // Verificar si se actualizó correctamente
        if ($stmt->rowCount() > 0) {
            $response = array("success" => true, "message" => "Usuario rechazado correctamente");
        } else {
            $response = array("success" => false, "message" => "No se encontró la solicitud para rechazar");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al rechazar usuario: " . $e->getMessage());
    }
    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function aceptarSolicitud($amigoId, $usuarioId)
{
    try {
        $conn = get_connection();

        // Actualizar el estado de la amistad a "aceptado" en la tabla de amigos
        $stmt = $conn->prepare("UPDATE amigos SET estado = 'aceptado' WHERE (usuario_id = :usuario_id AND amigo_id = :amigo_id) OR (usuario_id = :amigo_id AND amigo_id = :usuario_id)");
        $stmt->bindParam(':amigo_id', $amigoId);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        // Verificar si se actualizó correctamente
        if ($stmt->rowCount() > 0) {
            $response = array("success" => true, "message" => "Usuario aceptado correctamente");
        } else {
            $response = array("success" => false, "message" => "No se encontró la solicitud para aceptar");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al aceptar usuario: " . $e->getMessage());
    }
    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
function enviarSolicitudPartida($amigoId, $usuarioId, $tiempoPorJugada, $colorPiezasUsuario, $partidaPuntuada)
{
    try {
        if ($colorPiezasUsuario === 'Blancas') {
            $jugadorBlancas = $usuarioId;
            $jugadorNegras = $amigoId;
        } else {
            $jugadorNegras = $usuarioId;
            $jugadorBlancas = $amigoId;
        }
        $tiempoPorMovimiento = obtenerTiempoPorJugada($tiempoPorJugada);
        $estado_inicial = array(
            "position" => [
                [
                    ["wr", "wn", "wb", "wq", "wk", "wb", "wn", "wr"],
                    ["wp", "wp", "wp", "wp", "wp", "wp", "wp", "wp"],
                    ["", "", "", "", "", "", "", ""],
                    ["", "", "", "", "", "", "", ""],
                    ["", "", "", "", "", "", "", ""],
                    ["", "", "", "", "", "", "", ""],
                    ["bp", "bp", "bp", "bp", "bp", "bp", "bp", "bp"],
                    ["br", "bn", "bb", "bq", "bk", "bb", "bn", "br"]
                ]
            ],
            "turn" => "w",
            "candidateMoves" => [],
            "movesList" => [],
            "promotionSquare" => null,
            "status" => "Ongoing",
            "castleDirection" => ["w" => "both", "b" => "both"]
        );
        $estado = json_encode($estado_inicial);

        if ($partidaPuntuada == '') {
            $partidaPuntuada = 0;
        }
        $fechaPartida = date('Y-m-d H:i:s');
        $cancelada = 0;
        $terminada = 0;

        $conn = get_connection();
        // Insertar una nueva solicitud de amistad
        $stmt = $conn->prepare("INSERT INTO partidas (jugador_blancas, jugador_negras, estado, tiempo_por_jugada, clasificada, fechaPartida, cancelada, terminada ) VALUES (:jugador_blancas, :jugador_negras, :estado, :tiempo_por_jugada, :clasificada, :fechaPartida, :cancelada, :terminada)");
        $stmt->bindParam(':jugador_blancas', $jugadorBlancas);
        $stmt->bindParam(':jugador_negras', $jugadorNegras);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':tiempo_por_jugada', $tiempoPorMovimiento);
        $stmt->bindParam(':clasificada', $partidaPuntuada);
        $stmt->bindParam(':fechaPartida', $fechaPartida);
        $stmt->bindParam(':cancelada', $cancelada);
        $stmt->bindParam(':terminada', $terminada);

        $stmt->execute();
        $idpartida = $conn->lastInsertId();

        if ($stmt->rowCount() > 0) {
            $partida[] = array(
                "idpartida" => $idpartida,
                "estado" => $estado_inicial,
            );
            $response = array("success" => true, "message" => "Solicitud de partida enviada correctamente", "partida" => $partida);
        } else {
            $response = array("success" => false, "message" => "Error al enviar la solicitud de partida");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al enviar la solicitud de partida: " . $e->getMessage());
    }
    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
function obtenerTiempoPorJugada($tiempoPorJugadaTexto)
{
    // Extraer el número de días del texto
    $dias = (int) filter_var($tiempoPorJugadaTexto, FILTER_SANITIZE_NUMBER_INT);
    return $dias;
}
function obtenerPartidas($usuarioId)
{
    $response = array();
    try {
        $conn = get_connection();

        // Recuperar partidas del usuario
        $stmt = $conn->prepare("SELECT p.idpartida, 
        u_blancas.idusuarios AS id_blancas,
        u_blancas.nombre AS nombre_blancas, 
        u_blancas.avatar AS avatar_blancas, 
        u_negras.idusuarios AS id_negras,
        u_negras.nombre AS nombre_negras, 
        u_negras.avatar AS avatar_negras,
        p.jugador_blancas,
        p.jugador_negras,
        p.tiempo_por_jugada, 
        p.clasificada, 
        p.estado, 
        p.fechaPartida, 
        p.ultimoMovimientoBlancas, 
        p.ultimoMovimientoNegras 
        FROM partidas p
        INNER JOIN usuarios u_blancas ON p.jugador_blancas = u_blancas.idusuarios
        INNER JOIN usuarios u_negras ON p.jugador_negras = u_negras.idusuarios
        WHERE (p.jugador_blancas = :usuario_id OR p.jugador_negras = :usuario_id )
        AND cancelada = 0 
        AND terminada = 0");
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        // Verificar si se actualizó correctamente
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                //Obtener id de partida
                $idpartida = $row['idpartida'];
                $tiempo_por_jugada = $row['tiempo_por_jugada'];
                $clasificada = $row['clasificada'];

                //ID y nombre de jugadores
                $nombreBlancas = $row['nombre_blancas'];
                $nombreNegras = $row['nombre_negras'];
                $idBlancas = $row['id_blancas'];
                $idNegras = $row['id_negras'];
                // Decodificar el estado de la partida
                $estado_partida = json_decode($row['estado'], true);

                //Obtener avatar
                $base64AvatarBlancas = base64_encode($row['avatar_blancas']);
                $base64AvatarNegras = base64_encode($row['avatar_negras']);

                // Obtener la fecha actual
                $fecha_actual = new DateTime();

                // Calcular la fecha límite de movimiento para el jugador
                $fecha_limite = new DateTime($row['fechaPartida']);
                $fecha_limite->add(new DateInterval('P' . $row['tiempo_por_jugada'] . 'D'));

                // Obtener el último movimiento entre blancas y negras
                $ultimo_movimiento_blancas = !empty($row['ultimoMovimientoBlancas']) ? new DateTime($row['ultimoMovimientoBlancas']) : null;
                $ultimo_movimiento_negras = !empty($row['ultimoMovimientoNegras']) ? new DateTime($row['ultimoMovimientoNegras']) : null;

                if ($ultimo_movimiento_blancas || $ultimo_movimiento_negras) {
                    // Determinar el último movimiento más reciente
                    $ultimo_movimiento = max($ultimo_movimiento_blancas, $ultimo_movimiento_negras);

                    // Calcular la fecha límite basada en el último movimiento
                    $fecha_limite = clone $ultimo_movimiento;
                    $fecha_limite->add(new DateInterval('P' . $row['tiempo_por_jugada'] . 'D'));
                }

                // Calcular los días/horas/minutos restantes para el jugador
                $intervalo = $fecha_actual->diff($fecha_limite);
                if ($intervalo->i === 0) {
                    $abandonar_respuesta = abandonarPartidaTiempo($idpartida, $usuarioId, $estado_partida);

                    // Verificar si la llamada fue exitosa
                    if ($abandonar_respuesta["success"]) {
                        continue;
                    }
                } elseif ($intervalo->days >= 1) {
                    $dias_restantes = $intervalo->days . " día";
                    if ($intervalo->days > 1) {
                        $dias_restantes .= "s";
                    }
                } elseif ($intervalo->h >= 1) {
                    $dias_restantes = $intervalo->h . " hora";
                    if ($intervalo->h > 1) {
                        $dias_restantes .= "s";
                    }
                } else {
                    $dias_restantes = $intervalo->i . " min";
                    if ($intervalo->i > 1) {
                        $dias_restantes .= "s";
                    }
                }


                $partidas[] = array(
                    "idpartida" => $idpartida,
                    "estado" => $estado_partida,
                    "dias_restantes" => $dias_restantes,
                    "tiempo_por_jugada" => $tiempo_por_jugada,
                    "clasificada" => $clasificada,
                    "jugador_blancas" => array(
                        "id" => $idBlancas,
                        "nombre" => $nombreBlancas,
                        "avatar" => $base64AvatarBlancas
                    ),
                    "jugador_negras" => array(
                        "id" => $idNegras,
                        "nombre" => $nombreNegras,
                        "avatar" => $base64AvatarNegras
                    ),
                );
            }
            if (!empty($partidas)) {
                $response = array("success" => true, "message" => "Visualizar partidas correcto", "partidas" => $partidas);
            } else {
                $response = array("success" => false, "message" => "El usuario no tiene partidas activas");
            }
        } else {
            $response = array("success" => false, "message" => "El usuario no tiene partidas activas");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al cargar las partidas del usuario: " . $e->getMessage());
    }
    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function obtenerPartida($idpartida)
{
    $response = array();
    try {
        $conn = get_connection();

        // Obtener la partida seleccionada
        $stmt = $conn->prepare("SELECT idpartida, jugador_blancas, jugador_negras, tiempo_por_jugada, clasificada, estado, fechaPartida, ultimoMovimientoBlancas, ultimoMovimientoNegras FROM partidas WHERE idpartida = :partida_id");
        $stmt->bindParam(':partida_id', $idpartida);
        $stmt->execute();

        // Verificar si se actualizó correctamente
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Obtener id de partida
                $idpartida = $row['idpartida'];
                // Decodificar el estado de la partida
                $estado_partida = json_decode($row['estado'], true);

                // Obtener jugadores
                $jugador_blancas = $row['jugador_blancas'];
                $jugador_negras = $row['jugador_negras'];

                // Obtener la fecha actual
                $fecha_actual = new DateTime();

                // Calcular la fecha límite de movimiento para el jugador
                $fecha_limite = new DateTime($row['fechaPartida']);
                $fecha_limite->add(new DateInterval('P' . $row['tiempo_por_jugada'] . 'D'));

                if (!empty($row['ultimoMovimientoBlancas']) && !empty($row['ultimoMovimientoNegras'])) {
                    $ultimo_movimiento = max($row['ultimoMovimientoBlancas'], $row['ultimoMovimientoNegras']);

                    // Calcular la fecha límite basada en el último movimiento
                    $fecha_limite = new DateTime($ultimo_movimiento);
                    $fecha_limite->add(new DateInterval('P' . $row['tiempo_por_jugada'] . 'D'));
                }

                // Calcular los días restantes para el jugador
                $dias_restantes = $fecha_actual->diff($fecha_limite)->days;
                $partida = array(
                    "idpartida" => $idpartida,
                    "estado" => $estado_partida,
                    "dias_restantes" => $dias_restantes,
                    "jugador_blancas" => $jugador_blancas,
                    "jugador_negras" => $jugador_negras
                );
            }
            if (!empty($partida)) {
                $response = array("success" => true, "message" => "Visualizar partida correcto", "partidas" => $partida);
            } else {
                $response = array("success" => false, "message" => "Error al cargar las partidas activas");
            }
        } else {
            $response = array("success" => false, "message" => "El usuario no tiene partidas activas");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al aceptar usuario: " . $e->getMessage());
    }
    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}


function actualizarPartida($idpartida, $usuarioId, $nuevoEstado)
{
    $response = array();
    try {
        $conn = get_connection();

        // Seleccionar la partida que se está jugando
        $stmt = $conn->prepare("SELECT idpartida, jugador_blancas, jugador_negras, tiempo_por_jugada, clasificada, estado, fechaPartida, ultimoMovimientoBlancas, ultimoMovimientoNegras FROM partidas WHERE idpartida = :partida_id");
        $stmt->bindParam(':partida_id', $idpartida);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Obtener jugadores y último movimiento
            $jugadorBlancas = $row['jugador_blancas'];
            $jugadorNegras = $row['jugador_negras'];
            $ultimoMovimientoBlancas = $row['ultimoMovimientoBlancas'];
            $ultimoMovimientoNegras = $row['ultimoMovimientoNegras'];

            // Verificar si el usuario tiene permiso para realizar el movimiento
            if (($usuarioId == $jugadorBlancas && $nuevoEstado['turn'] == 'b') || ($usuarioId == $jugadorNegras && $nuevoEstado['turn'] == 'w')) {
                // Actualizar el estado de la partida en la base de datos
                $estado = json_encode($nuevoEstado);
                $stmt = $conn->prepare("UPDATE partidas SET estado = :nuevoEstado WHERE idpartida = :partida_id");
                $stmt->bindParam(':partida_id', $idpartida);
                $stmt->bindParam(':nuevoEstado', $estado);
                $stmt->execute();

                // Verificar si se actualizó correctamente
                if ($stmt->rowCount() > 0) {
                    // Actualizar el último movimiento según el usuario
                    if ($usuarioId == $jugadorBlancas) {
                        $ultimoMovimientoBlancas = date('Y-m-d H:i:s');
                    } elseif ($usuarioId == $jugadorNegras) {
                        $ultimoMovimientoNegras = date('Y-m-d H:i:s');
                    }

                    // Actualizar último movimiento en la base de datos
                    $stmt = $conn->prepare("UPDATE partidas SET ultimoMovimientoBlancas = :ultimoMovimientoBlancas, ultimoMovimientoNegras = :ultimoMovimientoNegras WHERE idpartida = :partida_id");
                    $stmt->bindParam(':partida_id', $idpartida);
                    $stmt->bindParam(':ultimoMovimientoBlancas', $ultimoMovimientoBlancas);
                    $stmt->bindParam(':ultimoMovimientoNegras', $ultimoMovimientoNegras);
                    $stmt->execute();

                    $response = array("success" => true, "message" => "Partida actualizada", "estado" => json_decode($estado));
                } else {
                    $response = array("success" => false, "message" => "No se pudo actualizar la partida");
                }
            } else {
                $response = array("success" => false, "message" => "Movimiento no autorizado");
            }
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al actualizar la partida: " . $e->getMessage());
    }
    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function abandonarPartida($idpartida, $usuarioId, $estado)
{
    $response = array();
    try {
        $conn = get_connection();

        // Verificar si la partida tiene más de dos movimientos
        if (count($estado['position']) > 3) {
            // Actualizar tabla partidas: establecer columnas cancelada y terminada a 1
            $stmt = $conn->prepare("UPDATE partidas SET cancelada = 1, terminada = 1 WHERE idpartida = :partida_id");
            $stmt->bindParam(':partida_id', $idpartida);
            $stmt->execute();

            // Obtener información sobre si la partida es clasificada
            $esClasificada = esPartidaClasificada($idpartida);

            // Consultar los jugadores en la tabla partidas
            $stmt = $conn->prepare("SELECT jugador_blancas, jugador_negras FROM partidas WHERE idpartida = :partida_id");
            $stmt->bindParam(':partida_id', $idpartida);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $jugador_blancas = $row['jugador_blancas'];
                $jugador_negras = $row['jugador_negras'];
            }
            // Determinar qué jugador ha abandonado la partida
            $jugador_abandonado = ($jugador_blancas == $usuarioId) ? 'jugador_blancas' : 'jugador_negras';
            $jugador_permanece = ($jugador_abandonado == 'jugador_blancas') ? 'jugador_negras' : 'jugador_blancas';
            // Obtener el ID del jugador que permanece
            $jugador_permanece_id = ($jugador_permanece == 'jugador_blancas') ? $jugador_blancas : $jugador_negras;
            // Si la partida es clasificada, realizar operaciones adicionales
            if ($esClasificada) {
                // OBTENER PUNTUACIONES DE LOS JUGADORES
                // Consulta para obtener las stats del usuario por ID de usuario
                $sql = "SELECT derrotas, empates, victorias, puntuacion FROM statsusuarios WHERE idUsuario = :idUsuario";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':idUsuario', $usuarioId);
                $stmt->execute();
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userData) {
                    $puntuacionIdUsuario = $userData['puntuacion'];
                }

                // Consulta para obtener las stats del rival por ID de rival
                $sql = "SELECT derrotas, empates, victorias, puntuacion FROM statsusuarios WHERE idUsuario = :idUsuario";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':idUsuario', $jugador_permanece_id);
                $stmt->execute();
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userData) {
                    $puntuacionJugadorPermanece = $userData['puntuacion'];
                }

                // SISTEMA DE ELO
                if ($puntuacionIdUsuario > $puntuacionJugadorPermanece) {
                    $puntos = calcularPuntosSuma($puntuacionIdUsuario, $puntuacionJugadorPermanece);
                } else {
                    $puntos = calcularPuntosResta($puntuacionIdUsuario, $puntuacionJugadorPermanece);
                }

                // Restar los puntos en el campo puntuación de la tabla statsUsuarios
                $stmt = $conn->prepare("UPDATE statsUsuarios SET puntuacion = puntuacion - $puntos WHERE idUsuario = :usuario_id");
                $stmt->bindParam(':usuario_id', $usuarioId);
                $stmt->execute();

                // Sumar los puntos en el campo puntuación de la tabla statsUsuarios para el jugador que no ha abandonado
                $stmt = $conn->prepare("UPDATE statsUsuarios SET puntuacion = puntuacion + $puntos WHERE idUsuario = :jugador_id");
                $stmt->bindParam(':jugador_id', $$jugador_permanece);
                $stmt->execute();
            }
            // Actualizar la tabla statsUsuarios: sumar +1 al campo derrota en el usuarioId
            $stmt = $conn->prepare("UPDATE statsUsuarios SET derrotas = derrotas + 1 WHERE idUsuario = :usuario_id");
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->execute();

            // Actualizar la tabla statsUsuarios: sumar +1 al campo victorias en el jugador que no ha abandonado
            $stmt = $conn->prepare("UPDATE statsUsuarios SET victorias = victorias + 1 WHERE idUsuario = :jugador_id");
            $stmt->bindParam(':jugador_id', $$jugador_permanece);
            $stmt->execute();
        } else {
            // Si la partida tiene menos de dos movimientos, establecer columnas cancelada y terminada a 1
            $stmt = $conn->prepare("UPDATE partidas SET cancelada = 1, terminada = 1 WHERE idpartida = :partida_id");
            $stmt->bindParam(':partida_id', $idpartida);
            $stmt->execute();
        }

        // Verificar si se actualizó correctamente
        if ($stmt->rowCount() > 0) {
            $response = array("success" => true, "message" => "La partida se abandonó correctamente");
        } else {
            $response = array("success" => false, "message" => "No se pudo abandonar la partida");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al abandonar la partida: " . $e->getMessage());
    }

    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
function abandonarPartidaTiempo($idpartida, $usuarioId, $estado_partida)
{
    $response = array();
    try {
        $conn = get_connection();

        // Verificar si la partida tiene más de dos movimientos
        if (count($estado_partida['position']) > 3) {
            // Actualizar tabla partidas: establecer columnas cancelada y terminada a 1
            $stmt = $conn->prepare("UPDATE partidas SET cancelada = 1, terminada = 1 WHERE idpartida = :partida_id");
            $stmt->bindParam(':partida_id', $idpartida);
            $stmt->execute();

            // Obtener información sobre si la partida es clasificada
            $esClasificada = esPartidaClasificada($idpartida);

            // Consultar los jugadores en la tabla partidas
            $stmt = $conn->prepare("SELECT jugador_blancas, jugador_negras FROM partidas WHERE idpartida = :partida_id");
            $stmt->bindParam(':partida_id', $idpartida);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $jugador_blancas = $row['jugador_blancas'];
                $jugador_negras = $row['jugador_negras'];
            }
            // Determinar qué jugador ha abandonado la partida
            $jugador_abandonado = ($jugador_blancas == $usuarioId) ? 'jugador_blancas' : 'jugador_negras';
            $jugador_permanece = ($jugador_abandonado == 'jugador_blancas') ? 'jugador_negras' : 'jugador_blancas';
            // Obtener el ID del jugador que permanece
            $jugador_permanece_id = ($jugador_permanece == 'jugador_blancas') ? $jugador_blancas : $jugador_negras;
            // Si la partida es clasificada, realizar operaciones adicionales
            if ($esClasificada) {
                // OBTENER PUNTUACIONES DE LOS JUGADORES
                // Consulta para obtener las stats del usuario por ID de usuario
                $sql = "SELECT derrotas, empates, victorias, puntuacion FROM statsusuarios WHERE idUsuario = :idUsuario";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':idUsuario', $usuarioId);
                $stmt->execute();
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userData) {
                    $puntuacionIdUsuario = $userData['puntuacion'];
                }

                // Consulta para obtener las stats del rival por ID de rival
                $sql = "SELECT derrotas, empates, victorias, puntuacion FROM statsusuarios WHERE idUsuario = :idUsuario";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':idUsuario', $jugador_permanece_id);
                $stmt->execute();
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userData) {
                    $puntuacionJugadorPermanece = $userData['puntuacion'];
                }

                // SISTEMA DE ELO
                if ($puntuacionIdUsuario > $puntuacionJugadorPermanece) {
                    $puntos = calcularPuntosSuma($puntuacionIdUsuario, $puntuacionJugadorPermanece);
                } else {
                    $puntos = calcularPuntosResta($puntuacionIdUsuario, $puntuacionJugadorPermanece);
                }

                // Restar los puntos en el campo puntuación de la tabla statsUsuarios
                $stmt = $conn->prepare("UPDATE statsUsuarios SET puntuacion = puntuacion - $puntos WHERE idUsuario = :usuario_id");
                $stmt->bindParam(':usuario_id', $usuarioId);
                $stmt->execute();

                // Sumar los puntos en el campo puntuación de la tabla statsUsuarios para el jugador que no ha abandonado
                $stmt = $conn->prepare("UPDATE statsUsuarios SET puntuacion = puntuacion + $puntos WHERE idUsuario = :jugador_id");
                $stmt->bindParam(':jugador_id', $$jugador_permanece);
                $stmt->execute();
            }
            // Actualizar la tabla statsUsuarios: sumar +1 al campo derrota en el usuarioId
            $stmt = $conn->prepare("UPDATE statsUsuarios SET derrotas = derrotas + 1 WHERE idUsuario = :usuario_id");
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->execute();

            // Actualizar la tabla statsUsuarios: sumar +1 al campo victorias en el jugador que no ha abandonado
            $stmt = $conn->prepare("UPDATE statsUsuarios SET victorias = victorias + 1 WHERE idUsuario = :jugador_id");
            $stmt->bindParam(':jugador_id', $$jugador_permanece);
            $stmt->execute();
        } else {
            // Si la partida tiene menos de dos movimientos, establecer columnas cancelada y terminada a 1
            $stmt = $conn->prepare("UPDATE partidas SET cancelada = 1, terminada = 1 WHERE idpartida = :partida_id");
            $stmt->bindParam(':partida_id', $idpartida);
            $stmt->execute();
        }

        if ($stmt->rowCount() > 0) {
            $response = array("success" => true, "message" => "La partida se abandonó correctamente");
        } else {
            $response = array("success" => false, "message" => "No se pudo abandonar la partida");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al abandonar la partida: " . $e->getMessage());
    }

    return $response;
}
function esPartidaClasificada($idpartida)
{
    $conn = get_connection();

    // Consultar la columna clasificada en la tabla partidas
    $stmt = $conn->prepare("SELECT clasificada FROM partidas WHERE idpartida = :partida_id");
    $stmt->bindParam(':partida_id', $idpartida);
    $stmt->execute();

    // Obtener el resultado de la consulta
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si la partida es clasificada
    if ($result['clasificada'] == 1) {
        return true;
    } else {
        return false;
    }
}
// Calcula los puntos ganados o perdidos en base a la diferencia de puntuación
function calcularPuntosSuma($miPuntuacion, $puntuacionOponente)
{
    $diferencia = abs($miPuntuacion - $puntuacionOponente); // diferencia elos
    $puntos = floor($diferencia / 100);
    // $puntos = max(1, floor(10 - $diferencia / 100));

    // Si la diferencia es mayor o igual a 1000, se ajustan los puntos
    if ($diferencia >= 1000) {
        $puntos = 10;
    }
    if ($miPuntuacion > $puntuacionOponente) {
        return 10 + $puntos;
    } else {
        return 10 - $puntos;
    }
}

// Calcula los puntos ganados o perdidos en base a la diferencia de puntuación
function calcularPuntosResta($miPuntuacion, $puntuacionOponente)
{
    $diferencia = abs($miPuntuacion - $puntuacionOponente); // diferencia elos
    $puntos = floor($diferencia / 100);
    // $puntos = max(1, floor(10 - $diferencia / 100));

    // Si la diferencia es mayor o igual a 1000, se ajustan los puntos
    if ($diferencia >= 1000) {
        $puntos = 10;
    }
    if ($miPuntuacion > $puntuacionOponente) {
        return 10 + $puntos;
    } else {
        return 10 - $puntos;
    }
}
// Función para calcular los puntos ganados o perdidos en caso de empate
function calcularPuntosEmpate($miPuntuacion, $puntuacionOponente)
{
    $diferencia = $miPuntuacion - $puntuacionOponente;
    $puntos = floor(abs($diferencia) / 100);

    // Si la diferencia es mayor o igual a 1000, se ajustan los puntos
    if (abs($diferencia) >= 1000) {
        $puntos = 10;
    }

    return $diferencia > 0 ? $puntos : -$puntos;
}
function finalizarPartida($idPartida, $colorPieza, $endGameReason)
{
    $response = array();
    try {
        $conn = get_connection();

        // Actualizar tabla partidas: establecer columnas terminada a 1
        $stmt = $conn->prepare("UPDATE partidas SET terminada = 1 WHERE idpartida = :partida_id");
        $stmt->bindParam(':partida_id', $idPartida);
        $stmt->execute();

        // Obtener información sobre si la partida es clasificada
        $esClasificada = esPartidaClasificada($idPartida);

        // Consultar los jugadores en la tabla partidas
        $stmt = $conn->prepare("SELECT jugador_blancas, jugador_negras FROM partidas WHERE idpartida = :partida_id");
        $stmt->bindParam(':partida_id', $idPartida);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $jugador_blancas = $row['jugador_blancas'];
            $jugador_negras = $row['jugador_negras'];
        }
        // Consultar la puntuacion de los jugadores
        $stmt = $conn->prepare("SELECT puntuacion FROM statsusuarios WHERE idUsuario = :jugador_id_blancas");
        $stmt->bindParam(':jugador_id_blancas', $jugador_blancas);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $puntuacionBlancas = $row['puntuacion'];
        }
        $stmt = $conn->prepare("SELECT puntuacion FROM statsusuarios WHERE idUsuario = :jugador_id_negras");
        $stmt->bindParam(':jugador_id_negras', $jugador_negras);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $puntuacionNegras = $row['puntuacion'];
        }

        // Si la partida es clasificada, realizar operaciones adicionales
        if ($esClasificada) {
            if ($endGameReason == 'insufficientMaterial' || $endGameReason == 'stalemate') {
                // SISTEMA DE ELO
                $puntos = calcularPuntosEmpate($puntuacionBlancas, $puntuacionNegras);
                if ($puntos < 0) {
                    $puntosBlancas = abs($puntos);
                    // Sumar los puntos en el campo puntuación de la tabla statsUsuarios para el jugador de blancas
                    $stmt = $conn->prepare("UPDATE statsUsuarios SET puntuacion = puntuacion + $puntosBlancas WHERE idUsuario = :jugador_id_blancas");
                    $stmt->bindParam(':jugador_id_blancas', $jugador_blancas);
                    $stmt->execute();

                    // Sumar los puntos en el campo puntuación de la tabla statsUsuarios para el jugador de negras
                    $stmt = $conn->prepare("UPDATE statsUsuarios SET puntuacion = puntuacion + $puntos WHERE idUsuario = :jugador_id_negras");
                    $stmt->bindParam(':jugador_id_negras', $jugador_negras);
                    $stmt->execute();
                } else {
                    $puntosNegras = abs($puntos);
                    // Sumar los puntos en el campo puntuación de la tabla statsUsuarios para el jugador de blancas
                    $stmt = $conn->prepare("UPDATE statsUsuarios SET puntuacion = puntuacion - $puntos WHERE idUsuario = :jugador_id_blancas");
                    $stmt->bindParam(':jugador_id_blancas', $jugador_blancas);
                    $stmt->execute();

                    // Sumar los puntos en el campo puntuación de la tabla statsUsuarios para el jugador de negras
                    $stmt = $conn->prepare("UPDATE statsUsuarios SET puntuacion = puntuacion + $puntosNegras WHERE idUsuario = :jugador_id_negras");
                    $stmt->bindParam(':jugador_id_negras', $jugador_negras);
                    $stmt->execute();
                }
                //Agregar empate en la tabla de stats para los jugadores
                $stmt = $conn->prepare("UPDATE statsUsuarios SET empates = empates + 1 WHERE idUsuario = :jugador_id_negras OR idUsuario = :jugador_id_blancas");
                $stmt->bindParam(':jugador_id_negras', $jugador_negras);
                $stmt->bindParam(':jugador_id_blancas', $jugador_blancas);
                $stmt->execute();
            } else { //$endGameReason = "CheckMate"
                // Calcular puntos y otorgarlos al jugador correspondiente
                $ganador = ($colorPieza == 'w') ? $jugador_blancas : $jugador_negras;
                $perdedor = ($ganador == $jugador_blancas) ? $jugador_negras : $jugador_blancas;

                // SISTEMA DE ELO
                if ($puntuacionBlancas > $puntuacionNegras) {
                    $puntos = calcularPuntosSuma($puntuacionBlancas, $puntuacionNegras);
                } else {
                    $puntos = calcularPuntosResta($puntuacionBlancas, $puntuacionNegras);
                }

                // Restar los puntos en el campo puntuación de la tabla statsUsuarios
                $stmt = $conn->prepare("UPDATE statsUsuarios SET puntuacion = puntuacion - $puntos WHERE idUsuario = :perdedor");
                $stmt->bindParam(':perdedor', $perdedor);
                $stmt->execute();

                // Sumar los puntos en el campo puntuación de la tabla statsUsuarios para el jugador que gana
                $stmt = $conn->prepare("UPDATE statsUsuarios SET puntuacion = puntuacion + $puntos WHERE idUsuario = :ganador");
                $stmt->bindParam(':ganador', $ganador);
                $stmt->execute();

                // Actualizar la tabla statsUsuarios: sumar +1 al campo derrota en el usuarioId
                $stmt = $conn->prepare("UPDATE statsUsuarios SET derrotas = derrotas + 1 WHERE idUsuario = :perdedor");
                $stmt->bindParam(':perdedor', $perdedor);
                $stmt->execute();

                // Actualizar la tabla statsUsuarios: sumar +1 al campo victorias en el jugador que no ha abandonado
                $stmt = $conn->prepare("UPDATE statsUsuarios SET victorias = victorias + 1 WHERE idUsuario = :ganador");
                $stmt->bindParam(':ganador', $ganador);
                $stmt->execute();
            }
        } else { //No clasificada
            if ($endGameReason == 'insufficientMaterial' || $endGameReason == 'stalemate') {
                //Agregar empate en la tabla de stats para los jugadores
                $stmt = $conn->prepare("UPDATE statsUsuarios SET empates = empates + 1 WHERE idUsuario = :jugador_id_negras OR idUsuario = :jugador_id_blancas");
                $stmt->bindParam(':jugador_id_negras', $jugador_negras);
                $stmt->bindParam(':jugador_id_blancas', $jugador_blancas);
                $stmt->execute();
            } else {
                // Actualizar la tabla statsUsuarios: sumar +1 al campo derrota en el usuarioId
                $stmt = $conn->prepare("UPDATE statsUsuarios SET derrotas = derrotas + 1 WHERE idUsuario = :perdedor");
                $stmt->bindParam(':perdedor', $perdedor);
                $stmt->execute();

                // Actualizar la tabla statsUsuarios: sumar +1 al campo victorias en el jugador que no ha abandonado
                $stmt = $conn->prepare("UPDATE statsUsuarios SET victorias = victorias + 1 WHERE idUsuario = :ganador");
                $stmt->bindParam(':ganador', $ganador);
                $stmt->execute();
            }
        }

        // Verificar si se actualizó correctamente
        if ($stmt->rowCount() > 0) {
            $response = array("success" => true, "message" => "La partida se termino correctamente");
        } else {
            $response = array("success" => false, "message" => "No se pudo terminar la partida");
        }
    } catch (PDOException $e) {
        $response = array("success" => false, "message" => "Error al terminar la partida: " . $e->getMessage());
    }

    // Devuelve los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
function validarToken($idUsuario, $token) {
    $conn = get_connection();

    // Consulta para obtener el token almacenado en la base de datos para el usuario
    $sql = "SELECT token FROM usuarios WHERE idusuarios = :idUsuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idUsuario', $idUsuario);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        // Si el token enviado coincide con el token almacenado en la base de datos, el token es válido
        if ($token === $userData['token']) {
            return true;
        }
    }

    // Si el token no coincide o el usuario no tiene un token en la base de datos, el token no es válido
    return false;
}