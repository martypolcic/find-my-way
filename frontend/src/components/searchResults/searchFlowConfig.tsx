export type SearchMode = 'trip' | 'flights' | 'accommodations';

export type StepConfig = {
  key: string;
  label: string;
};

export const flows: Record<SearchMode, StepConfig[]> = {
  trip: [
    { key: 'destination', label: 'Destination' },
    { key: 'departure', label: 'Departure Flight' },
    { key: 'accommodation', label: 'Accommodation' },
    { key: 'return', label: 'Return Flight' },
    { key: 'overview', label: 'Overview' },
    { key: 'search', label: 'Search Parameters' },
  ],
  flights: [
    { key: 'departure', label: 'Departure Flight' },
    { key: 'return', label: 'Return Flight' },
    { key: 'overview', label: 'Overview' },
    { key: 'search', label: 'Search Parameters' },
  ],
  accommodations: [
    { key: 'accommodation', label: 'Accommodation' },
    { key: 'overview', label: 'Overview' },
    { key: 'search', label: 'Search Parameters' },
  ],
};
