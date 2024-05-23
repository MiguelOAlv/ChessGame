import React, { useState, useEffect } from 'react';
import { useAppContext, useAuth } from '../../contexts/Context'
import './Board.css';
import Ranks from './bits/Ranks';
import Files from './bits/Files';
import Pieces from '../Pieces/Pieces';
import PromotionBox from '../Popup/PromotionBox/PromotionBox';
import Popup from '../Popup/Popup';
import GameEnds from '../Popup/GameEnds/GameEnds';
import arbiter from '../../arbiter/arbiter';
import { getKingPosition } from '../../arbiter/getMoves';
import SideMenu from '../SideMenu/SideMenu';
import { obtenerDatos, obtenerPartida, actualizarPartida, abandonarPartida } from '../../services/apiService';
import { useLocation, useNavigate } from 'react-router-dom';
import actionTypes from '../../reducer/actionTypes';
import Control from '../Control/Control';
import MovesList from '../Control/bits/MovesList';

const Board = () => {
    const navigate = useNavigate();
    const ranks = Array(8).fill().map((x, i) => 8 - i);
    const files = Array(8).fill().map((x, i) => i + 1);
    const location = useLocation();
    const { estado: locationEstado, idPartida } = location.state || {};



    // Inicializamos appStateNuevoFormato y otros estados
    const [appStateNuevoFormato, setAppStateNuevoFormato] = useState(null);
    const appState = appStateNuevoFormato || locationEstado || originalAppState;
    let { appState: originalAppState, dispatch } = useAppContext(); // ESTADO ORIGINAL DE LA APLICACION
    const position = appState.position?.[appState.position.length - 1] || []; // Manejo del estado inicial
    console.log(position);

    const [loading, setLoading] = useState(true);
    const [mostrarMenu, setMostrarMenu] = useState(false);
    const [mostrarModalAbandonar, setMostrarModalAbandonar] = useState(false);
    const idUsuario = localStorage.getItem('userId');
    const token = localStorage.getItem('token');
    const { logout } = useAuth();
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

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

            const fetchPartida = async () => {
                try {
                    const response = await obtenerPartida(idPartida);
                    console.log('ESTADO PARTIDA');
                    console.log(response);
                    if (response.success) {
                        if (response.success) {
                            const { jugador_blancas, jugador_negras, estado } = response.partidas;
                            const userId = parseInt(idUsuario);
                            let playerColor;
                            if (userId === parseInt(jugador_blancas)) {
                                playerColor = 'w';
                            } else if (userId === parseInt(jugador_negras)) {
                                playerColor = 'b';
                            }
                            localStorage.setItem('playerColor', playerColor);
                        }

                        const nuevoEstado = response.partidas.estado;
                        const appStateNuevoFormato = {
                            position: nuevoEstado.position || originalAppState.position,
                            turn: nuevoEstado.turn || originalAppState.turn,
                            candidateMoves: nuevoEstado.candidateMoves || originalAppState.candidateMoves,
                            movesList: nuevoEstado.movesList || originalAppState.movesList,
                            promotionSquare: nuevoEstado.promotionSquare || originalAppState.promotionSquare,
                            status: nuevoEstado.stauts || originalAppState.status,
                            castleDirection: nuevoEstado.castleDirection || originalAppState.castleDirection
                        };
                        setAppStateNuevoFormato(appStateNuevoFormato);
                        console.log('APPSTATE NUEVO FORMATO')
                        console.log(appStateNuevoFormato)
                        dispatch({ type: actionTypes.REC_STATE, payload: { ...appStateNuevoFormato, ...nuevoEstado } });
                        originalAppState = appStateNuevoFormato
                        console.log(originalAppState);
                        setLoading(false);
                        setMostrarMenu(true);
                    }
                } catch (error) {
                    setMostrarMenu(true);
                    console.error('Error al cargar la partida:', error);
                }
            };
            fetchDatos();
            fetchPartida();
        }
    }, [idPartida, idUsuario, navigate, dispatch]);

    const checkTile = (() => {
        const isInCheck = (arbiter.isPlayerInCheck({
            positionAfterMove: position,
            player: appState.turn
        }))

        if (isInCheck)
            return getKingPosition(position, appState.turn)

        return null
    })()

    const getClassName = (i, j) => {
        let c = 'tile';
        c += (i + j) % 2 === 0 ? ' tile--dark ' : ' tile--light ';
        if (appState && appState.candidateMoves?.find(m => m[0] === i && m[1] === j)) {
            if (position[i][j])
                c += ' attacking';
            else
                c += ' highlight';
        }

        if (checkTile && checkTile[0] === i && checkTile[1] === j) {
            c += ' checked';
        }

        return c;
    }

    //Funcion para aceptar el movimiento
    const handleAcceptMove = async () => {
        try {
            console.log('ESTADO AL MANDAR MOVIMIENTO ORIGINAL!!!');
            console.log(originalAppState);
            const response = await actualizarPartida(idPartida, idUsuario, originalAppState);
            console.log('ACTUALIZAR PARTIDA');
            console.log(response);
            if (response.success) {
                console.log('ESTADO DESPUES DE ACTUALIZAR')
                console.log(response.estado)

                console.log('PARTIDA ACTUALIZADA');
                setSuccess(response.message);
                setError('');
            } else {
                setSuccess('');
                setError(response.message);
            }
        } catch (error) {
            setError('Error al aceptar el movimiento:', error);
            setSuccess('');
        }
    };

    // Función para abandonar la partida
    const abandonarPartidaModal = async () => {
        try {
            const response = await abandonarPartida(idPartida, idUsuario, originalAppState);
            if (response.success) {
                console.log('PARTIDA ABANDONADA');
                navigate('/home');
            }
        } catch (error) {
            console.error('Error al abandonar la partida:', error);
        }
    };

    // Función para mostrar el modal de abandonar partida
    const mostrarModalAbandonarPartida = () => {
        setMostrarModalAbandonar(true);
    };

    // Función para ocultar el modal de abandonar partida
    const ocultarModalAbandonarPartida = () => {
        setMostrarModalAbandonar(false);
    };

    return (
        <div className="root">
            {mostrarMenu && <SideMenu loading={loading} />}
            <main className="content content-chess" style={{ marginBottom: "60px" }}>
                <div className="panelChess">
                    <div className="row chess">
                        <div className="col">
                            <div className='board-container'>
                                <div className='board' >
                                    <Ranks ranks={ranks} />
                                    <div className='tiles'>
                                        {ranks.map((rank, i) =>
                                            files.map((file, j) =>
                                                <div
                                                    key={file + '' + rank}
                                                    i={i}
                                                    j={j}
                                                    className={`${getClassName(7 - i, j)}`}>
                                                </div>
                                            )
                                        )}
                                    </div>
                                    <Pieces idPartida={idPartida} />
                                    <Popup>
                                        <PromotionBox />
                                        <GameEnds />
                                    </Popup>
                                    <Files files={files} />

                                </div>
                            </div>
                        </div>
                        <div className="col">
                            <Control>
                                <MovesList />
                                {error && <p className="error">{error}</p>}
                                {success && <p className="success">{success}</p>}
                            </Control>
                        </div>
                    </div>
                </div>

                {mostrarMenu && appStateNuevoFormato && (
                    <div className="row">
                        <div className="col">
                            <button onClick={handleAcceptMove} className="btn btn-success mr-2">Confirmar Movimiento</button>
                        </div>
                        <div className="col">
                            <button onClick={mostrarModalAbandonarPartida} className="btn btn-danger">Abandonar Partida</button>
                        </div>
                    </div>
                )}
                {/* Modal de abandonar partida */}
                {mostrarModalAbandonar && (
                    <div className="modal fade show" tabIndex="-1" role="dialog" style={{ display: "block", backgroundColor: "rgba(0,0,0,0.5)" }}>
                        <div className="modal-dialog modal-dialog-centered" role="document">
                            <div className="modal-content">
                                <div className="modal-body">
                                    <p className="text-white text-center">¿Estás seguro de abandonar la partida? Contará como derrota si la partida tiene más de 2 movimientos.</p>
                                </div>
                                <div className="modal-footer">
                                    <div className="row">
                                        <div className="col">
                                            <button type="button" className="btn btn-success" onClick={abandonarPartidaModal}>Aceptar</button></div>
                                        <div className="col">
                                            <button type="button" className="btn btn-danger" onClick={ocultarModalAbandonarPartida}>Cancelar</button></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </main>
        </div>
    );
}

export default Board;
