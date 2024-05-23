import React, { useState, useEffect } from 'react';
import './App.css';
import Board from './components/Board/Board';
import Register from './components/Register/Register';
import LoginForm from './components/Login/LoginForm';
import Footer from './components/Footer/Footer';
import Home from './components/Home/Home';
import Profile from './components/Profile/Profile';
import Friends from './components/Friends/Friends';
import Practice from './components/Practice/Practice';
import Error404 from './components/Error/Error';
import { reducer } from './reducer/reducer';
import { useReducer } from 'react';
import { initGameState } from './constants';
import AppContext from './contexts/Context';
import { BrowserRouter as Router, Routes, Route, Navigate, useSearchParams  } from 'react-router-dom';
import { AuthProvider } from './contexts/Context';

function App() {
    const [appState, dispatch] = useReducer(reducer, initGameState);
    const [loggedIn, setLoggedIn] = useState(false);
    const [loggedOut, setLoggedOut] = useState(false);

    useEffect(() => {
        // Comprobamos si hay un estado de inicio de sesión almacenado en localStorage
        const storedLoggedIn = localStorage.getItem('loggedIn');
        if (storedLoggedIn) {
            setLoggedIn(true);
        }
        
    }, []);

    const handleLogin = () => {
        // Aquí podrías agregar la lógica para verificar el inicio de sesión exitoso
        setLoggedIn(true);
        setLoggedOut(false); // El usuario ha iniciado sesión nuevamente
        localStorage.setItem('loggedIn', true);
    };

    const handleLogout = () => {
        // Limpiamos el estado de inicio de sesión y el almacenamiento en localStorage al cerrar sesión
        setLoggedIn(false);
        setLoggedOut(true); // El usuario ha cerrado sesión
        localStorage.removeItem('loggedIn');
    };

    const providerState = {
        appState,
        dispatch
    };

    return (
        <Router>
            <AppContext.Provider value={providerState}>
                <AuthProvider>
                <div className="App">
                    <Routes>
                        {/* Ruta para el formulario de inicio de sesión */}
                        {loggedOut ? (
                            <Route path="/" element={<Navigate to="/" />} />
                        ) : (
                            <Route path="/" element={<LoginForm onLogin={handleLogin} />} />
                        )}
                        <Route path="/home" element={<Home onLogout={handleLogout} />} />
                        <Route path="/profile" element={<Profile onLogout={handleLogout} />} />
                        <Route path="/board" element={<Board onLogout={handleLogout} />} />
                        <Route path="/friends" element={<Friends onLogout={handleLogout} />} />
                        <Route path="/practice" element={<Practice onLogout={handleLogout} />} />
                        <Route path="/register" element={<Register />} />
                        {/* Ruta de error para rutas no definidas */}
                        <Route path="*" element={<Error404 />} />
                    </Routes>
                    <Footer />
                </div>
                </AuthProvider>
            </AppContext.Provider>
        </Router>
    );
}

export default App;
