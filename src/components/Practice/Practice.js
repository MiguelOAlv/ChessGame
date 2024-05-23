import React, { useState, useEffect } from 'react';
import SideMenu from '../SideMenu/SideMenu';
import { useNavigate } from 'react-router-dom';
import { obtenerDatos } from '../../services/apiService';
import './practice.css';
import Ranks from './bits/Ranks';
import Files from './bits/Files';
import Pieces from '../Pieces/Pieces';
import PromotionBox from '../Popup/PromotionBox/PromotionBox';
import Popup from '../Popup/Popup';
import GameEnds from '../Popup/GameEnds/GameEnds';
import arbiter from '../../arbiter/arbiter';
import { getKingPosition } from '../../arbiter/getMoves';
import Control from '../Control/Control';
import MovesList from '../Control/bits/MovesList';
import { useAppContext } from '../../contexts/Context'
import { setupNewGame } from '../../reducer/actions/game';
import { useAuth } from '../../contexts/Context';

const Practice = () => {
    const ranks = Array(8).fill().map((x, i) => 8 - i);
    const files = Array(8).fill().map((x, i) => i + 1);
    const { appState, dispatch } = useAppContext();
    const position = appState.position[appState.position.length - 1]
    
    const [loading, setLoading] = useState(true);
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
                    console.log(datosResponse);
                    if (datosResponse && datosResponse.message === "Token no valido") {
                        logout();
                        navigate('/');
                    }else{
                        setLoading(false);
                        dispatch(setupNewGame())
                    }
                }
            } catch (error) {
                console.error('Error fetching datos:', error);
            }
        };

        fetchDatos();
    }, [idUsuario, navigate, token, dispatch]);

    const checkTile = (() => {
        //if (!appState || !Object.keys(appState).length || !position.length) return null;
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


    return (
        <div className="root">
            <SideMenu loading={loading} />
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
                                    <Pieces />
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
                            </Control>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );



};

export default Practice;
