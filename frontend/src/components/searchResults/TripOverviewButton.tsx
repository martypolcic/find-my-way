import { useAppSelector } from '../../store/hooks';
import { selectIsTripComplete } from '../../store/selectionSelectors';
import './TripOverviewButton.css';

interface TripOverviewButtonProps {
  onClick: () => void;
}

const TripOverviewButton = ({ onClick }: TripOverviewButtonProps) => {
  const isTripComplete = useAppSelector(selectIsTripComplete);

  return (
    <button className={`trip-overview-button ${isTripComplete ? 'complete' : 'incomplete'}`} 
      disabled={!isTripComplete}
      onClick={onClick}
    >
      Overview
    </button>
  );
};

export default TripOverviewButton;