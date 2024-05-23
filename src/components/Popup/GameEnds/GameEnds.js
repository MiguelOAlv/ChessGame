import { Status } from '../../../constants';
import { useAppContext }from '../../../contexts/Context'
import { setupNewGame } from '../../../reducer/actions/game';
import { useLocation, useNavigate} from 'react-router-dom';
import './GameEnds.css'

const GameEnds = ({onClosePopup}) => {
    const location = useLocation();
    const navigate = useNavigate();
    const { appState : {status} , dispatch } = useAppContext();
    
    if (status === Status.ongoing || status === Status.promoting)
        return null

    const newGame = () => {
        dispatch(setupNewGame())
    }
    const goToHome = () => {
        navigate("/home");
    };
    const isPracticeRoute = location.pathname === "/practice";
    const isBoardRoute = location.pathname === "/board";
    const isWin = status.endsWith('ganan')
    
    return <div className="popup--inner popup--inner__center">
        <h1>{isWin ? status : 'Empate'}</h1>
        <p>{!isWin && status}</p>
        <div className={isWin ? `wins ${status}` : 'empate'}>
            </div>
        {isPracticeRoute && (<button className="btn btn-retroceder" onClick={newGame}>
                Nueva Partida
            </button>
        )}
        {isBoardRoute && (<button className="btn btn-retroceder" onClick={goToHome}>
                        Ir a Inicio
                    </button>
        )}
    </div>
   
}

export default GameEnds