import React, { useState, useEffect } from 'react';
import { Button } from 'react-bootstrap';
import { obtenerAvatar, subirAvatar } from '../../services/apiService';
import { useAuth } from '../../contexts/Context';
import { Link, useNavigate  } from 'react-router-dom';
import './sidemenu.css';

const SideMenu = ({ loading }) => {
  const [open, setOpen] = useState(false);
  const [avatarUrl, setAvatarUrl] = useState('');
  const [openModal, setOpenModal] = useState(false); // Estado para controlar la apertura del modal
  const [selectedAvatar, setSelectedAvatar] = useState(null);
  const [error, setError] = useState(null);
  const idUsuario = localStorage.getItem('userId');
  const token = localStorage.getItem('token');
  const { logout } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (!idUsuario || !token) {
      logout();
      navigate('/');
    }else{
      const fetchAvatar = async () => {
        try {
          const response = await obtenerAvatar(idUsuario);
          const data = response.avatar;
          setAvatarUrl(`data:image/png;base64,${data}`);
        } catch (error) {
          console.error('Error fetching avatar:', error);
        }
      };

      fetchAvatar();
    }
  }, [idUsuario, navigate, token]);
  

  const handleFileChange = (event) => {
    setSelectedAvatar(event.target.files[0]);
    setError(null);
  };

  const handleUploadAvatar = async () => {
    if (selectedAvatar) {
      try {
      const response = await subirAvatar(selectedAvatar, idUsuario);
      const responses = response.split('}{');
      const response1 = JSON.parse(responses[0] + '}');
      const response2 = JSON.parse('{' + responses[1]);

      // Verificar la propiedad success del primer objeto JSON
      if (response1.success) {
          const data = response2.avatar;
          setAvatarUrl(`data:image/png;base64,${data}`);
          handleCloseModal();
        }
        
      } catch (error) {
        setError(error.message);
      }
    } else {
      setError('No se ha seleccionado ningún archivo.');
    }
  };
  const handleToggleMenu = () => {
    setOpen(!open);
  };

  const handleAvatarClick = () => {
    setOpenModal(true); // Abrir el modal al hacer clic en el avatar
  };

  const handleCloseModal = () => {
    setOpenModal(false); // Cerrar el modal
  };

  const handleLogoutClick = () => {
    logout(); // Cerrar sesión
    navigate('/'); // Redirigir al usuario al directorio raíz
  };
  
  return (
    <>
     <Button className="toggleButton btnSideMenu" onClick={handleToggleMenu}>
            ≡
          </Button>
    <div id="sidebar" className={open ? 'active' : ''}>
      <div className="sidebar-header">
        <h3>ChessGame</h3>
      </div>
        <ul className="list-unstyled components">
          <li>
          <div className="avatar-container">
              <img src={avatarUrl} alt="Avatar" className="avatar-img" onClick={handleAvatarClick} />
            </div>
          </li>
          <li>
            <Link to="/home">Inicio</Link>
          </li>
          <li>
            <Link to="/profile">Perfil</Link>
          </li>
          <li>
            <Link to="/friends">Amigos</Link>
          </li>
          <li>
          <Link to="/practice">Herramienta de practica</Link>
          </li>
          <li>
          <Link onClick={handleLogoutClick}>Cerrar Sesión</Link>
          </li>
        </ul>
      </div>
      {/* Modal para subir avatar */}
      {openModal && (
      <div className="modal fade show" tabIndex="-1" role="dialog" style={{ display: "block", backgroundColor: "rgba(0,0,0,0.5)" }}>
          <div className="modal-dialog modal-dialog-centered" role="document">
              <div className="modal-content">
                  <div className="modal-body">
                      <h5 className="text-whie text-center">Subir Avatar</h5>
                  </div>
                  <div className="modal-footer justify-content-center">
                  <input className="form-control" type="file" id="avatarInput" accept="image/png, image/jpeg, image/jpg" onChange={handleFileChange} />
                      <div className="row mt-3">
                        <div className="col">
                        <button type="button" className="btn btn-primary" onClick={handleUploadAvatar}>Aceptar</button>
                        </div>
                      <div className="col">
                      <button type="button" className="btn btn-secondary" onClick={handleCloseModal}>Cerrar</button></div>
                      {error && <p className="error">{error}</p>}
                      </div>
                  </div>
              </div>
          </div>
      </div>
  )}
    </>
  );
};

export default SideMenu;

