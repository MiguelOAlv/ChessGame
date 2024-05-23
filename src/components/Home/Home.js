import React, { useState, useEffect } from 'react';
import SideMenu from '../SideMenu/SideMenu';
import './home.css';
import { useNavigate } from 'react-router-dom';
import { obtenerDatos } from '../../services/apiService';
import { obtenerPartidas } from '../../services/apiService';
import { useAuth } from '../../contexts/Context';


const Home = () => {
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const idUsuario = localStorage.getItem('userId');
  const token = localStorage.getItem('token');
  const { logout } = useAuth();
  const [errorPartidas, setErrorPartidas] = useState('');
  const [partidasUsuario, setPartidasUsuario] = useState([]);

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

      const obtenerPartidasUsuarios = async () => {
        try {
          const response = await obtenerPartidas(idUsuario);
          console.log('RESPUESTA DE PARTIDAS');
          console.log(response.partidas);
          if (response.success) {
            setPartidasUsuario(response.partidas);
          } else {
            setErrorPartidas(response.message);
          }
        } catch (error) {
          setErrorPartidas(error.message);
        }

      };
      fetchDatos();
      obtenerPartidasUsuarios();
    }
  }, [idUsuario, navigate, token]);

  const handleBoardClick = (estado, idPartida) => {
    navigate('/board', { state: { estado, idPartida } });
  };

  return (
    <div className="root">
      <SideMenu loading={loading} />
      <main className="content">
        <div className="column">
          <div className="listTitle">Listado de Partidas Activas</div>
          {errorPartidas && <p className="errorMessage">{errorPartidas}</p>}
          <ul className="resultList gameList">
            {partidasUsuario && partidasUsuario.length > 0 && partidasUsuario.map((partida, index) => {
              if (partida.dias_restantes !== "0 horas" && partida.dias_restantes !== "0 días") {
                return (
                  <li key={index} className="resultadoItem">
                    {partida.estado && (
                      <div className="row align-items-center partidaRow" onClick={() => handleBoardClick(partida.estado, partida.idpartida)}>
                        <div className="col-3 col-md-3 d-flex align-items-center justify-content-center">
                          <div className="player player-white d-flex align-items-center">
                            <div className="div-imagen">
                              <img src={`data:image/png;base64,${partida.jugador_blancas.avatar}`} alt={partida.jugador_blancas.nombre} className="userAvatarHome mr-2" />
                            </div>
                            <span className="nombreAmigo">{partida.jugador_blancas.nombre}</span>
                          </div>
                        </div>
                        <div className="col-2 col-md-2 d-flex align-items-center justify-content-center">
                          <div className="partidaImagen" />
                        </div>
                        <div className="col-3 col-md-3 d-flex align-items-center justify-content-center">
                          <div className="player player-black d-flex align-items-center">
                            <div className="div-imagen">
                              <img src={`data:image/png;base64,${partida.jugador_negras.avatar}`} alt={partida.jugador_negras.nombre} className="userAvatarHome ml-2" />
                            </div>
                            <span className="nombreAmigo">{partida.jugador_negras.nombre}</span>
                          </div>
                        </div>
                        <div className="col-2 col-md-2 d-flex align-items-center justify-content-center">
                          <div className="infoPartida">
                            Turno: <br /> {partida.estado.turn === 'w' ? 'Blancas' : 'Negras'}
                            {((partida.estado.turn === 'w' && partida.jugador_blancas.id === parseInt(idUsuario)) ||
                              (partida.estado.turn === 'b' && partida.jugador_negras.id === parseInt(idUsuario))) && (
                                <div style={{ color: 'red' }}>
                                  ¡Tu turno!
                                </div>
                              )}
                          </div>
                        </div>
                        <div className="col-1 col-md-2 d-flex align-items-center justify-content-center">
                          <div className="infoPartida">
                            Tiempo: <br /> {partida.dias_restantes}
                          </div>
                        </div>
                      </div>
                    )}
                  </li>
                );
              } else {
                return null; // No renderizar la partida
              }
            })}
          </ul>
          <style jsx>{`
          
          @media (max-width: 767px) {
            .partidaRow {
              display: flex;
              flex-direction: row;
              justify-content: center;
              align-items: center;
              font-size: 10px; /* Reduce font size */
            }
            .player img {
              width: 30px; /* Smaller avatar size */
              height: 30px; /* Smaller avatar size */
              margin-left: 5px;
            }
            .div-imagen {
              width: 35px;
            }
            .partidaImagen {
              width: 20px;
              height: 20px;
            }
            .nombreAmigo {
              font-size: 12px; /* Smaller text size */
            }
            .infoPartida {
              font-size: 10px; /* Smaller text size */
            }
          }
        `}</style>
        </div>
      </main>
    </div>
  );




};

export default Home;
