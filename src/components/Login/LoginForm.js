import './login.css'
import React, { useState } from 'react';
import { autentificarUsuario } from '../../services/apiService';
import {useNavigate,Link} from 'react-router-dom';

const LoginForm = ({ onLogin }) => {
  const [nombre, setNombre] = useState('');
  const [contrasena, setContrasena] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await autentificarUsuario(nombre,contrasena);
      console.log(response);
      if(response.success === true){
        setSuccess('Inicio de sesión exitoso');
        localStorage.setItem('userId', response.userId);
        localStorage.setItem('token', response.token);
        onLogin();
        navigate('/home');
      }else{
        setError(response.message);
      }
    } catch (error) {
      setError(error.message);
    }
  };

  return (
<div>
<header className="header">ChessGame</header>

  <div className="form">
    <form onSubmit={handleSubmit}>
      <div className="form-group">
        <input
          type="text"
          className="form-control"
          id="nombre"
          name="nombre"
          placeholder="Nombre/Email"
          value={nombre}
          onChange={(e) => setNombre(e.target.value)}
          required
        />
      </div>
      <div className="form-group">
        <input
          type="password"
          className="form-control"
          id="contrasena"
          name="contrasena"
          placeholder="Contraseña"
          value={contrasena}
          onChange={(e) => setContrasena(e.target.value)}
          required
        />
      </div>
      {error && typeof error === 'string' ? <p className="error">{error}</p> : null}
      {success && typeof success === 'string' ? <p className="exito">{success}</p> : null}
      <button type="submit" className="btn btn-primary">
        ENTRAR
      </button>
      <Link to="/register" className="btn btn-secondary">
        REGISTRO
      </Link>
    </form>
  </div>
  </div>
  );
};

export default LoginForm;

