import React from 'react';

export type StepPanelProps = {
  stepKey: string;
  isActive: boolean;
  children: React.ReactNode;
};

const StepPanel = ({ stepKey, isActive, children }: StepPanelProps) => {
  if (!isActive) return null;
  return (
    <section className="results-section" data-step={stepKey}>
      {children}
    </section>
  );
};

export default StepPanel;
