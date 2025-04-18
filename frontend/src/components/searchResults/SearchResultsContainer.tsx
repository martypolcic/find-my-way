import { useState } from "react";
import Sidebar from "../sidebarSection/Sidebar";
import SearchResults from "./SearchResults";
import { flows, SearchMode } from "./searchFlowConfig";
import './SearchResultsContainer.css';

const SearchResultsContainer = () => {
  const searchMode: SearchMode = 'trip';
  const steps = flows[searchMode];

  const [currentStep, setCurrentStep] = useState(steps[0].key);

  const handleStepChange = (step: string) => {
      setCurrentStep(step);
  };

  return (
    <div className="card-picker-container">
      <Sidebar 
        currentStep={currentStep} 
        onStepChange={handleStepChange}
        steps={steps}
      />
      <SearchResults 
        currentStep={currentStep} 
        onStepChange={handleStepChange}
        searchMode={searchMode}
      />
    </div>
  );
}

export default SearchResultsContainer;
