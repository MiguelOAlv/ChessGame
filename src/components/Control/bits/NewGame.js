import { useAppContext }from '../../../contexts/Context'
import { setupNewGame } from '../../../reducer/actions/game';
import './takeback.css';
const NewGame = () => {

    const { dispatch } = useAppContext();

    return <div>
        <button className="btn-retroceder" onClick={() => dispatch(setupNewGame())}>Nueva Partida</button>
    </div>
}

export default NewGame