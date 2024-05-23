import React, { useState, useEffect } from 'react';
import SideMenu from '../SideMenu/SideMenu';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/Context';
import { obtenerDatos, obtenerEstadisticas, actualizarPerfil } from '../../services/apiService';
import './profile.css';

const Profile = () => {
  const [nombre, setNombre] = useState('');
  const [contraseña, setContraseña] = useState('');
  const [correo, setCorreo] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState({});
  const idUsuario = localStorage.getItem('userId');
  const token = localStorage.getItem('token');
  const { logout } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    const fetchDatos = async () => {
      try {
        if (!idUsuario || !token) {
          logout();
          navigate('/');
        } else {
          const datosResponse = await obtenerDatos(idUsuario, token);
          if (datosResponse && datosResponse.message === "Token no valido") {
            logout();
            navigate('/');
          }else{
            setLoading(false);
            setNombre(datosResponse.nombre);
            setCorreo(datosResponse.correo);
            const statsResponse = await obtenerEstadisticas(idUsuario);
            if (statsResponse) {
              setStats(statsResponse.stats);
            }
          }
        }
      } catch (error) {
        console.error('Error fetching datos:', error);
      }
    };

    fetchDatos();
  }, [idUsuario, navigate, logout, token]);

  const handleSubmit = async (event) => {
    event.preventDefault();
    try {
      const respuesta = await actualizarPerfil(idUsuario, nombre, contraseña, correo );
      if (respuesta.success) {
        setSuccess(respuesta.message);
        setError('');
      } else {
        setError(respuesta.message);
        setSuccess('');
      }
    } catch (error) {
      setError(error.message);
      setSuccess('');
    }
  };

  const total = stats.derrotas + stats.empates + stats.victorias;
  const victoriasAngle = (stats.victorias / total) * 360;
  const empatesAngle = (stats.empates / total) * 360;
  const derrotasAngle = (stats.derrotas / total) * 360;


  return (
    <div className="root">
      <SideMenu loading={loading} />
      <div className="container" style={{ marginBottom: "50px" }}>
        <div className="row justify-content-center">
          <div className="col-8">
            <form onSubmit={handleSubmit} style={{ maxWidth: "400px", width: "100%", margin: "auto" }}>
              <div className="form-group">
                <label htmlFor="nombre" className="label-avatar">Nombre:</label>
                <input type="text" className="form-control" id="nombre" placeholder={nombre} onChange={(e) => setNombre(e.target.value)} />
              </div>
              <div className="form-group">
                <label htmlFor="contraseña" className="label-avatar">Contraseña:</label>
                <input type="password" className="form-control" id="contraseña" placeholder={contraseña} onChange={(e) => setContraseña(e.target.value)} />
              </div>
              <div className="form-group">
                <label htmlFor="correo" className="label-avatar">Correo electrónico:</label>
                <input type="email" className="form-control" id="correo" placeholder={correo} onChange={(e) => setCorreo(e.target.value)} />
              </div>
              {error && <p className="error-message">{error}</p>}
              {success && <p className="success-message">{success}</p>}
              <button type="submit" className="btn btn-secondary w-100">
                Guardar cambios
              </button>
            </form>
          </div>
          <div className="col-8 mb-5 mt-4">
            
            <div className="row">
              <div className="resultadoStatsItem mt-5">
                <div className="row w-100">
                <h5 className="stats-title text-center mt-2">Estadísticas</h5>
                <hr style={{ margin: "0 auto", width: "80%", borderBottom: "1px solid #ccc" }} />
                {total && stats.derrotas>=1 && stats.empates>=1 && stats.victorias>=1 && (
                  <div className="col-12 d-flex justify-content-center mt-3">
                  <svg width="200" height="200">
                    <path
                      d={`M 100 100 l 100 0 A 100 100 0 ${victoriasAngle > 180 ? 1 : 0} 1 ${(100 * Math.cos(victoriasAngle * Math.PI / 180)) + 100} ${(100 * Math.sin(victoriasAngle * Math.PI / 180)) + 100} Z`}
                      fill="#669966"><title>Victorias: {stats.victorias}</title></path>
                    <path
                      d={`M 100 100 l ${(100 * Math.cos(victoriasAngle * Math.PI / 180))} ${(100 * Math.sin(victoriasAngle * Math.PI / 180))} A 100 100 0 ${empatesAngle > 180 ? 1 : 0} 1 ${(100 * Math.cos((victoriasAngle + empatesAngle) * Math.PI / 180)) + 100} ${(100 * Math.sin((victoriasAngle + empatesAngle) * Math.PI / 180)) + 100} Z`}
                      fill="#666666"><title>Empates: {stats.empates}</title></path>
                    <path
                      d={`M 100 100 l ${(100 * Math.cos((victoriasAngle + empatesAngle) * Math.PI / 180))} ${(100 * Math.sin((victoriasAngle + empatesAngle) * Math.PI / 180))} A 100 100 0 ${derrotasAngle > 180 ? 1 : 0} 1 200 100 Z`}
                      fill="#996666"><title>Derrotas: {stats.derrotas}</title></path>
                  </svg>
                </div>
                )}
                <hr style={{ margin: "20px auto 0", width: "50%", borderBottom: "1px solid #ccc"}} />
                <div className="row w-100 justify-content-center mt-2">
                  <div className="col-6 mt-2">
                    <div className="stats-circle">
                      <div className="stats-circle-victorias">Victorias: {stats.victorias}</div>
                      <div className="stats-circle-derrotas">Derrotas: {stats.derrotas}</div>
                    </div>
                  </div>
                  <div className="col-6 mt-2">
                    <div className="stats-circle">
                      <div className="stats-circle-empates">Empates: {stats.empates}</div>
                      <div className="stats-circle-puntuacion">Puntuación: {stats.puntuacion}</div>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
  
  

};

export default Profile;
