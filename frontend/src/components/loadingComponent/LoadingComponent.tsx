import loadginGIF from '../../assets/loading.gif';
import './LoadingComponent.css';

const LoadingComponent = () => {
    
    return (
        <div className="loading-component">
            <img src={loadginGIF} alt="Loading Screen GIF" />
        </div>
    );
}

export default LoadingComponent;