import { useAppContext }from '../../../contexts/Context'
import { takeBack } from '../../../reducer/actions/move';
import './takeback.css';
const TakeBack = () => {
    
    const { dispatch } = useAppContext();

    return <div>
        <button className="btn-retroceder" onClick={() => dispatch(takeBack())}>Retroceder</button>
    </div>
}

export default TakeBack