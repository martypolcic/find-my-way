import { useState } from "react";
import SearchResults from "./SearchResults";
import Sidebar from "../sidebarSection/Sidebar";
import SearchSection from "../searchSection/SearchSection"; // Import your search component
import './SearchResultsContainer.css';

const SearchResultsContainer = () => {
    const [currentStep, setCurrentStep] = useState('destination');
    const [showSearchOverlay, setShowSearchOverlay] = useState(false);

    const handleStepChange = (step: string) => {
        if (step === 'search') {
            setShowSearchOverlay(true);
        } else {
            setCurrentStep(step);
            setShowSearchOverlay(false);
        }
    };

    const closeSearchOverlay = () => {
        setShowSearchOverlay(false);
    };

    return (
        <div className="card-picker-container">
            <Sidebar 
                currentStep={currentStep} 
                onStepChange={handleStepChange}
            />
            <SearchResults currentStep={currentStep} onStepChange={handleStepChange} />
            
            {/* Search Overlay */}
            {showSearchOverlay && (
                <div className="search-overlay">
                    <div className="search-overlay-content">
                        <button 
                            className="close-overlay-button" 
                            onClick={closeSearchOverlay}
                        >
                            ×
                        </button>
                        <SearchSection />
                    </div>
                </div>
            )}
        </div>
    );
}

export default SearchResultsContainer;