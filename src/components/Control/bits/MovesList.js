import { useAppContext }from '../../../contexts/Context'
import TakeBack from './TakeBack'
import { useLocation } from 'react-router-dom';
import './MovesList.css'

const MovesList = () => {

    const { appState : {movesList} } = useAppContext();
    const location = useLocation();
    const isPracticeRoute = location.pathname === "/practice";

    return (
            <div>
                <div className='moves-list'>
                    {movesList.map((move, i) => (
                        <div key={i} data-number={Math.floor(i / 2) + 1}>{move}</div>
                    ))}
                </div>
                {isPracticeRoute && movesList.length > 0 && <TakeBack />}
            </div>
        );
}

export default MovesList