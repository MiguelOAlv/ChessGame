import axios from 'axios';

const BASE_URL = 'https://localhost:443';

export async function autentificarUsuario(nombre, contrasena) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/login`, { nombre, contrasena });
    return response.data;
  } catch (error) {
    console.error('Error al obtener datos:', error);
    throw error;
  }
};

export async function registrarUsuario(nombre, contrasena, email, avatar) {
  // Validar el nombre
  const regex = /^[a-zA-Z0-9]+$/;
  if (nombre.length > 8) {
    throw new Error('El nombre no puede ser mayor de 8 caracteres');
  }

  if (!regex.test(nombre)) {
    throw new Error('El nombre solo puede contener letras y números');
  }

  // Validar la longitud de la contraseña
  if (contrasena.length < 8) {
    throw new Error('La contraseña debe tener al menos 8 caracteres');
  }

  // Validar que la contraseña contenga al menos una letra
  if (!/[a-zA-Z]/.test(contrasena)) {
    throw new Error('La contraseña debe contener al menos una letra');
  }

  // Validar el formato del email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    throw new Error('El email no tiene un formato válido');
  }

  // Validar el tamaño del avatar (entre 0 y 10 MB)
  const maxSize = 10 * 1024 * 1024; // 10 MB en bytes
  if (avatar.size > maxSize) {
    throw new Error('El tamaño del avatar no puede superar los 10 MB');
  }

  // Validar el tipo de archivo del avatar (jpg, jpeg o png)
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
  if (!allowedTypes.includes(avatar.type)) {
    throw new Error('El avatar debe ser un archivo de tipo JPG, JPEG o PNG');
  }

  try {
    const avatarData = await convertFileToBase64(avatar);
    const response = await axios.post(
      `${BASE_URL}/ChessGame/backend/api.php/register`,
      {
        nombre,
        contrasena,
        email,
        avatar: avatarData
      },
      {
        headers: {
          'Content-Type': 'application/json'
        }
      }
    );
    console.log('Response:', response);
    return response.data;
  } catch (error) {
    console.error('Error al registrar usuario:', error);
    throw error;
  }
}

export async function subirAvatar(avatar, idUsuario) {
  try {
    // Validar el tamaño del avatar (entre 0 y 10 MB)
    const maxSize = 10 * 1024 * 1024; // 10 MB en bytes
    if (avatar.size > maxSize) {
      throw new Error('El tamaño del avatar no puede superar los 10 MB');
    }

    // Validar el tipo de archivo del avatar (jpg, jpeg o png)
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!allowedTypes.includes(avatar.type)) {
      throw new Error('El avatar debe ser un archivo de tipo JPG, JPEG o PNG');
    }

    const avatarData = await convertFileToBase64(avatar);
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/subirAvatar`, { avatarData, idUsuario });
    return response.data;
  } catch (error) {
    console.error('Error al subir avatar:', error);
    throw error;
  }
}

function convertFileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}

export async function obtenerAvatar(idUsuario) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/avatar`, { idUsuario });
    return response.data;
  } catch (error) {
    console.error('Error al obtener datos:', error);
    throw error;
  }
};

export async function obtenerDatos(idUsuario, token) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/usuario`, { idUsuario, token });
    return response.data;
  } catch (error) {
    console.error('Error al obtener datos:', error);
    throw error;
  }
};
export async function actualizarPerfil(idUsuario, nombre, contrasena, correo) {
  // Validar el nombre si está presente
  if (nombre) {
    const regex = /^[a-zA-Z0-9]+$/;
    if (nombre.length > 8) {
      throw new Error('El nombre no puede ser mayor de 8 caracteres');
    }
    if (!regex.test(nombre)) {
      throw new Error('El nombre solo puede contener letras y números');
    }
  }

  // Validar la contraseña si está presente
  if (contrasena) {
    // Validar la longitud de la contraseña
    if (contrasena.length < 8) {
      throw new Error('La contraseña debe tener al menos 8 caracteres');
    }
    // Validar que la contraseña contenga al menos una letra
    if (!/[a-zA-Z]/.test(contrasena)) {
      throw new Error('La contraseña debe contener al menos una letra');
    }
  }

  // Validar el formato del correo si está presente
  if (correo) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(correo)) {
      throw new Error('El correo no tiene un formato válido');
    }
  }
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/actualizarPerfil`, { idUsuario, nombre, contrasena, correo });
    return response.data;
  } catch (error) {
    console.error('Error al obtener datos:', error);
    throw error;
  }
};

export async function obtenerEstadisticas(idUsuario) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/obtenerEstadisticas`, { idUsuario });
    return response.data;
  } catch (error) {
    console.error('Error al obtener datos:', error);
    throw error;
  }
};

export async function buscarAmigosPorNombre(nombreUsuario) {
  // Validar el nombre buscado
  const regex = /^[a-zA-Z0-9]+$/;
  if (nombreUsuario.length > 8) {
    throw new Error('El nombre no puede ser mayor de 8 caracteres');
  }

  if (!regex.test(nombreUsuario)) {
    throw new Error('El nombre solo puede contener letras y números');
  }

  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/buscarUsuario`, { nombreUsuario });
    return response.data;
  } catch (error) {
    console.error('Error al buscar amigos por nombre:', error);
    throw error;
  }
};

export async function agregarAmigo(amigoId, usuarioId) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/agregarAmigo`, { amigoId, usuarioId });
    return response.data;
  } catch (error) {
    console.error('Error al buscar amigos por nombre:', error);
    throw error;
  }
};

export async function borrarAmigo(amigoId, usuarioId) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/borrarAmigo`, { amigoId, usuarioId });
    return response.data;
  } catch (error) {
    console.error('Error al encontrar amigos por id:', error);
    throw error;
  }
};

export async function obtenerAmigosUsuario(usuarioId, token) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/obtenerAmigos`, { usuarioId , token });
    return response.data;
  } catch (error) {
    console.error('Error al encontrar amigos por id:', error);
    throw error;
  }
};

export async function obtenerSolicitudesUsuario(usuarioId) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/obtenerSolicitudes`, { usuarioId });
    return response.data;
  } catch (error) {
    console.error('Error al encontrar solicitudes por id:', error);
    throw error;
  }
}
export async function aceptarSolicitud(amigoId, usuarioId) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/aceptarSolicitud`, { amigoId, usuarioId });
    return response.data;
  } catch (error) {
    console.error('Error al encontrar solicitudes por id:', error);
    throw error;
  }
}

export async function rechazarSolicitud(amigoId, usuarioId) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/rechazarSolicitud`, { amigoId, usuarioId });
    return response.data;
  } catch (error) {
    console.error('Error al encontrar solicitudes por id:', error);
    throw error;
  }
}

export async function enviarSolicitudPartida(amigoId, usuarioId, tiempoPorJugadaSeleccionado, colorPiezasSeleccionado, partidaPuntuada) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/enviarSolicitudPartida`, { amigoId, usuarioId, tiempoPorJugadaSeleccionado, colorPiezasSeleccionado, partidaPuntuada });
    return response.data;
  } catch (error) {
    console.error('Error al enviar la solicitud de partida:', error);
    throw error;
  }
}

export async function obtenerPartidas(usuarioId) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/obtenerPartidas`, { usuarioId });
    return response.data;
  } catch (error) {
    console.error('Error al enviar la solicitud de partida:', error);
    throw error;
  }
}

export async function obtenerPartida(idPartida) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/obtenerPartida`, { idPartida });
    return response.data;
  } catch (error) {
    console.error('Error al enviar la solicitud de partida:', error);
    throw error;
  }
}

export async function actualizarPartida(idPartida, usuarioId, nuevoEstado) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/actualizarPartida`, { idPartida, usuarioId, nuevoEstado });
    return response.data;
  } catch (error) {
    console.error('Error al actualizar la partida:', error);
    throw error;
  }
}

export async function abandonarPartida(idPartida, usuarioId, estado) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/abandonarPartida`, { idPartida, usuarioId, estado });
    return response.data;
  } catch (error) {
    console.error('Error al abandonar la partida:', error);
    throw error;
  }
}

export async function finalizarPartida(idPartida, colorPieza, endGameReason) {
  try {
    const response = await axios.post(`${BASE_URL}/ChessGame/backend/api.php/finalizarPartida`, { idPartida, colorPieza, endGameReason });
    return response.data;
  } catch (error) {
    console.error('Error al abandonar la partida:', error);
    throw error;
  }
}