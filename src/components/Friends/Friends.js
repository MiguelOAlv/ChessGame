import React, { useState, useEffect } from 'react';
import SideMenu from '../SideMenu/SideMenu';
import Avatar from '@material-ui/core/Avatar';
import Modal from '@material-ui/core/Modal';
import { obtenerDatos, buscarAmigosPorNombre, agregarAmigo, obtenerAmigosUsuario, borrarAmigo, obtenerSolicitudesUsuario, aceptarSolicitud, rechazarSolicitud, enviarSolicitudPartida } from '../../services/apiService';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/Context';
import './friends.css';

const Friends = () => {
  const navigate = useNavigate();
  const [nombreUsuario, setNombreUsuario] = useState('');
  const [amigoId, setAmigoId] = useState(null);
  const [amigos, setAmigos] = useState([]);
  const [amigosUsuario, setAmigosUsuario] = useState([]);
  const [solicitudesAmistad, setSolicitudes] = useState([]);
  const [infoBusqueda, setInfoBusqueda] = useState('');
  const [errorBusqueda, setErrorBusqueda] = useState('');
  const [infoAmigos, setInfoAmigos] = useState('');
  const [errorAmigos, setErrorAmigos] = useState('');
  const [infoSolicitud, setInfoSolicitud] = useState('');
  const [errorSolicitudes, setErrorSolicitudes] = useState('');
  const [loading, setLoading] = useState(true);
  const idUsuario = localStorage.getItem('userId');
  const token = localStorage.getItem('token');
  const { logout } = useAuth();

  // Estados para la ventana modal
  const [modalOpen, setModalOpen] = useState(false);
  const [tiempoPorJugadaSeleccionado, setTiempoPorJugadaSeleccionado] = useState(false);
  const [colorSeleccionado, setColorSeleccionado] = useState(false);
  const [partidaPuntuada, setPartidaPuntuada] = useState(false);
  const [errorModal, setErrorModal] = useState('');
  const [infoModal, setInfoModal] = useState('');

  useEffect(() => {
    if (!idUsuario || !token) {
      logout();
      navigate('/');
    } else {
      const fetchDatos = async () => {
        try {
          const response = await obtenerDatos(idUsuario, token);
          console.log(response);
          if (response && response.message === "Token no valido") {
            logout();
            navigate('/');
          } else {
            setLoading(false);
          }
        } catch (error) {
          console.error('Error fetching datos:', error);
        }
      };
      fetchDatos();
      setLoading(false);
      cargarAmigosUsuario(idUsuario);
      cargarSolicitudesUsuario(idUsuario);
    }
  }, [navigate, token, idUsuario]);


  const cargarAmigosUsuario = async () => {
    try {
      const response = await obtenerAmigosUsuario(idUsuario);
      console.log(response);
      if (response.success) {
        if (response.amigos.length === 0) {
          setErrorAmigos('El usuario aún no tiene amigos.');
        } else {
          setAmigosUsuario(response.amigos);
          setErrorAmigos('');
        }
      } else {
        setAmigosUsuario([]);
        setErrorAmigos(response.message);
        console.error('Error al cargar la lista de amigos del usuario:', response.message);
      }
    } catch (error) {
      console.error('Error al cargar la lista de amigos del usuario componente:', error);
    }
  };

  const cargarSolicitudesUsuario = async () => {
    try {
      const response = await obtenerSolicitudesUsuario(idUsuario);
      console.log(response);
      if (response.success) {
        if (response.amigos.length === 0) {
          setErrorSolicitudes('El usuario no tiene solicitudes entrantes.');
        } else {
          setSolicitudes(response.amigos);
          setErrorSolicitudes('');
        }
      } else {
        setSolicitudes([]);
        setErrorSolicitudes(response.message);
        console.error('Error al cargar la lista de solicitudes del usuario:', response.message);
      }
    } catch (error) {
      console.error('Error al cargar la lista de solicitudes del usuario componente:', error);
    }
  };

  const handleBuscarAmigo = async () => {
    try {
      if (nombreUsuario.trim() === '') {
        setErrorBusqueda('El campo de búsqueda está vacío');
        setAmigos([]);
        setInfoBusqueda([]);
        return; // Detener la ejecución si el campo de búsqueda está vacío
      }
      const response = await buscarAmigosPorNombre(nombreUsuario);
      if (response.success) {
        if (response.amigos.length === 0) {
          setErrorBusqueda('No se encontraron amigos con ese nombre.');
        } else {
          setAmigos(response.amigos);
          setInfoBusqueda('Se han encontrado ' + response.amigos.length + ' coincidencias');
          setErrorBusqueda('');
        }
      } else {
        setAmigos([]);
        setErrorBusqueda(response.message);
        console.error('No se encontraron amigos:', response.message);
      }
    } catch (error) {
      setErrorBusqueda(error.message);
    }
  };

  const handleAgregarAmigo = async (amigoId) => {
    try {
      const response = await agregarAmigo(amigoId, idUsuario);
      console.log(response);
      if (response.success) {
        cargarAmigosUsuario(idUsuario);
        cargarSolicitudesUsuario(idUsuario);
        setInfoBusqueda('Solicitud de amistad enviada correctamente');
      } else {
        setErrorBusqueda(response.message);
      }
    } catch (error) {
      setErrorBusqueda(error.message);
    }
  };

  const handleBorrarAmigo = async (amigoId) => {
    try {
      const response = await borrarAmigo(amigoId, idUsuario);
      if (response.success) {
        cargarAmigosUsuario(idUsuario);
        cargarSolicitudesUsuario(idUsuario);
        setInfoAmigos('Usuario borrado correctamente')
      } else {
        setErrorAmigos(response.message);
      }
    } catch (error) {
      setErrorAmigos(error.message)
    }
  };
  const handleAceptarSolicitud = async (amigoId) => {
    try {
      const response = await aceptarSolicitud(amigoId, idUsuario);
      console.log(response);
      if (response.success) {
        cargarAmigosUsuario(idUsuario);
        cargarSolicitudesUsuario(idUsuario);
        setInfoSolicitud('Usuario agregado correctamente');
      } else {
        setErrorSolicitudes(response.message);
      }
    } catch (error) {
      setErrorSolicitudes(error.message);
    }

  };
  const handleRechazarSolicitud = async (amigoId) => {
    try {
      const response = await rechazarSolicitud(amigoId, idUsuario);
      console.log(response);
      if (response.success) {
        cargarAmigosUsuario(idUsuario);
        cargarSolicitudesUsuario(idUsuario);
        setInfoSolicitud('Usuario rechazado correctamente');
      } else {
        setErrorSolicitudes(response.message);
      }
    } catch (error) {
      setErrorSolicitudes(error.message);
    }
  };

  const handleRetarPartida = async (amigoId) => {
    setAmigoId(amigoId);
    setModalOpen(true);
  };

  const sendChallenge = async (amigoId) => {
    const tiempoPorJugadaSeleccionadoElement = document.querySelector('.time-buttons .verde');
    const colorPiezasSeleccionadoElement = document.querySelector('.color-buttons .verde');
    const partidaPuntuadaCheckbox = document.querySelector('.custom-checkbox input[type="checkbox"]');

    const tiempoPorJugadaSeleccionado = tiempoPorJugadaSeleccionadoElement ? tiempoPorJugadaSeleccionadoElement.textContent : null;
    const colorPiezasSeleccionado = colorPiezasSeleccionadoElement ? colorPiezasSeleccionadoElement.textContent : null;
    const partidaPuntuada = partidaPuntuadaCheckbox ? partidaPuntuadaCheckbox.checked : false;

    if (!tiempoPorJugadaSeleccionado) {
      setErrorModal('No se ha seleccionado el tiempo por jugada');
      return;
    }

    if (!colorPiezasSeleccionado) {
      setErrorModal('No se ha seleccionado el color de piezas');
      return;
    }

    try {
      const response = await enviarSolicitudPartida(amigoId, idUsuario, tiempoPorJugadaSeleccionado, colorPiezasSeleccionado, partidaPuntuada);
      console.log(response);
      if (response.success) {
        const idPartida = response.partida[0].idpartida;
        console.log(idPartida);
        const estado = response.partida[0].estado;
        console.log(estado);
        navigate('/board', { state: { estado, idPartida } });
      } else {
        setErrorModal(response.message);
      }
    } catch (error) {
      setErrorModal(error.message);
    }
  };

  const [colorBotones, setColorBotones] = useState({
    '2 días': false,
    '3 días': false,
    '5 días': false,
    Blancas: false,
    Negras: false,
  });

  const cambiarColor = (e) => {
    const buttonClicked = e.target.textContent;
    const fila = e.target.parentNode;
    setErrorModal('');
    // Desactivar todos los botones de la misma fila
    Array.from(fila.children).forEach((child) => {
      if (child !== e.target) {
        child.classList.remove('verde'); // Remover clase 'verde' de todos los botones en la misma fila
        setColorBotones((prevState) => ({
          ...prevState,
          [child.textContent]: false,
        }));
      }
    });

    // Activar o desactivar el botón clicado
    setColorBotones((prevState) => ({
      ...prevState,
      [buttonClicked]: !prevState[buttonClicked]
    }));
    if (buttonClicked !== 'Blancas' && buttonClicked !== 'Negras') {
      setTiempoPorJugadaSeleccionado(buttonClicked);
    } else { // Establecer el color seleccionado si el botón clicado es 'Blancas' o 'Negras'
      setColorSeleccionado(buttonClicked);
    }
  };

  const handleCheckboxClick = () => {
    setPartidaPuntuada((prev) => !prev);
  };

  const closeModal = () => {
    setColorBotones({
      '2 días': false,
      '3 días': false,
      '5 días': false,
      'Blancas': false,
      'Negras': false,
    });
    setPartidaPuntuada(false);
    setErrorModal('');
    setModalOpen(false);
    setTiempoPorJugadaSeleccionado(false);
    setColorSeleccionado(false);
  };

  return (
    <div className="container-fluid vh-100">
      <SideMenu loading={loading} />
      <div className="row w-100 justify-content-center align-items-start">
        <div className="col-lg-3 col-sm-12 my-5">
            <div className="formContainer">
              <input
                type="text"
                value={nombreUsuario}
                onChange={(e) => setNombreUsuario(e.target.value)}
                className="input"
                placeholder="Nombre del usuario"
              />
              <button onClick={handleBuscarAmigo} className="button">Buscar</button>
            </div>
            {errorBusqueda && <p className="errorMessage">{errorBusqueda}</p>}
            {infoBusqueda && <p className="infoMessage">{infoBusqueda}</p>}
            <ul className="resultList">
              {amigos && amigos.length > 0 && amigos.map((amigo, index) => (
                <li key={index} className="resultItem">
                  <div className="userInfo">
                    <Avatar src={`data:image/png;base64,${amigo.avatar}`} alt="Avatar" className="userAvatar" />
                    <div>
                      <span className="nombreAmigo">{amigo.nombre}</span>
                    </div>
                  </div>
                  <button onClick={() => handleAgregarAmigo(amigo.id)} className="button">Agregar</button>
                </li>
              ))}
            </ul>
          </div>
          <div className="col-lg-3 col-sm-12 my-5">
            <div className="listaAmigosTitle">Listado de Amigos</div>
            {errorAmigos && <p className="errorMessage">{errorAmigos}</p>}
            {infoAmigos && <p className="infoMessage">{infoAmigos}</p>}
            <ul className="resultList friendList">
              {amigosUsuario && amigosUsuario.length > 0 && amigosUsuario.map((amigosUsuario, index) => (
                <li key={index} className="resultItem">
                  <div className="userInfo">
                    <Avatar src={`data:image/png;base64,${amigosUsuario.avatar}`} alt="Avatar" className="userAvatar" />
                    <div>
                      <span className="nombreAmigo">{amigosUsuario.nombre}</span>
                    </div>
                  </div>
                  <button onClick={() => handleRetarPartida(amigosUsuario.id)} className="button button-retar">Retar</button>
                  <button onClick={() => handleBorrarAmigo(amigosUsuario.id)} className="button button-borrar">Borrar</button>
                </li>
              ))}
            </ul>
          </div>
          <div className="col-lg-3 col-sm-12 my-5">
            <div className="listaAmigosTitle">Solicitudes de amistad</div>
            {errorSolicitudes && <p className="errorMessage">{errorSolicitudes}</p>}
            {infoSolicitud && <p className="infoMessage">{infoSolicitud}</p>}
            <ul className="resultList friendList">
              {solicitudesAmistad && solicitudesAmistad.length > 0 && solicitudesAmistad.map((solicitud, index) => (
                <li key={index} className="resultItem">
                  <div className="userInfo">
                    <Avatar src={`data:image/png;base64,${solicitud.avatar}`} alt="Avatar" className="userAvatar" />
                    <div>
                      <span className="nombreAmigo">{solicitud.nombre}</span>
                    </div>
                  </div>
                  <button onClick={() => handleAceptarSolicitud(solicitud.id)} className="button button-check"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="green">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                  </svg></button>
                  <button onClick={() => handleRechazarSolicitud(solicitud.id)} className="button button-cross"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="red">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                    <path d="M0 0h24v24H0z" fill="none" />
                  </svg></button>
                </li>
              ))}
            </ul>
        </div>
      </div>
      {/* Ventana modal para retar a un amigo */}
      <Modal
        open={modalOpen}
        onClose={closeModal}
      >
        <div className="modal">
          <div className="modal-content">
            <div className="button-text">Tiempo por jugada</div>
            <div className="button-row time-buttons">
              <button
                className={colorBotones['2 días'] ? 'button verde' : 'button'}
                onClick={cambiarColor}
              >
                2 días
              </button>
              <button
                className={colorBotones['3 días'] ? 'button verde' : 'button'}
                onClick={cambiarColor}
              >
                3 días
              </button>
              <button
                className={colorBotones['5 días'] ? 'button verde' : 'button'}
                onClick={cambiarColor}
              >
                5 días
              </button>
            </div>
            <div className="button-text">Selecciona tu color</div>
            <div className="button-row color-buttons">
              <button
                className={colorBotones['Blancas'] ? 'button verde' : 'button'}
                onClick={cambiarColor}
              >
                Blancas
              </button>
              <button
                className={colorBotones['Negras'] ? 'button verde' : 'button'}
                onClick={cambiarColor}
              >
                Negras
              </button>
            </div>
            <div className="button-text">Partida puntuada</div>
            <div className={`custom-checkbox ${partidaPuntuada ? 'custom-checked' : ''}`} onClick={handleCheckboxClick}>
              <div className="checkbox-circle"></div>
              <div className="tick">&#10003;</div>
              <div className="cross">X</div>

              <input
                type="checkbox"
                checked={partidaPuntuada}
                onChange={() => { }}
              />
            </div>
            {errorModal && <p className="errorMessageModal">{errorModal}</p>}
            {infoModal && <p className="infoModal">{infoModal}</p>}
            <div className="button-row operation-buttons">
              <button className='button' onClick={() => sendChallenge(amigoId)}>Enviar</button>
              <button className='button' onClick={closeModal}>Cerrar</button>
            </div>
          </div>
        </div>
      </Modal>
    </div>
  );
};

export default Friends;

