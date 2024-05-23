import './register.css';
import React, { useState } from 'react';
import { registrarUsuario } from '../../services/apiService';
import { Link } from 'react-router-dom';

function Register() {
  const [nombre, setNombre] = useState('');
  const [contrasena, setContrasena] = useState('');
  const [email, setEmail] = useState('');
  const [avatar, setAvatar] = useState(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      const response = await registrarUsuario(nombre,contrasena,email,avatar);
      
      if (response.success) {
        setSuccess(response.message);
        setError('');
      } else {
        setError(response.message);
        setSuccess('');
      }
    } catch (error) {
      console.error('Error al enviar los datos:', error);
      setError('Error al registrar el usuario. Por favor, inténtalo de nuevo.');
      setSuccess('');
    }
  };

  return (
    <div>
      <header className="header">ChessGame</header>
      <div className="form">
        <form onSubmit={handleSubmit} encType="multipart/form-data">
          <div className="form-group">
            <input
              type="text"
              placeholder="Nombre"
              className="form-control"
              value={nombre}
              onChange={(e) => setNombre(e.target.value)}
              required
            />
            <input
              type="password"
              placeholder="Contraseña"
              className="form-control"
              value={contrasena}
              onChange={(e) => setContrasena(e.target.value)}
              required
            />
            <input
              type="email"
              placeholder="Correo electrónico"
              className="form-control"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
            <label for="file" className="label-avatar">Sube tu imagen de perfil</label>
            <input
              type="file"
              name="avatar"
              placeholder="Avatar"
              className="form-control"
              accept="image/png, image/jpeg, image/jpg"
              onChange={(e) => setAvatar(e.target.files[0])}
              required
            />
          </div>
          {error && <p className="error">{error}</p>}
          {success && <p className="exito">{success}</p>}
          <button type="submit" className="btn btn-primary">REGISTRARSE</button>
          <Link to="/" className="btn btn-secondary">ACCEDER</Link>
        </form>
      </div>
    </div>
  );
}

export default Register;
