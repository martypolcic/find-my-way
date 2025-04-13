// components/Card.tsx
import React from 'react';
import './Card.css';

type CardProps = {
  type: 'destination' | 'flight' | 'accommodation';
  title: string;
  subtitle?: string;
  price?: string;
  image?: string;
  details?: React.ReactNode;
  selected?: boolean;
  onSelect?: () => void;
  className?: string;
};

const Card: React.FC<CardProps> = ({
  type,
  title,
  subtitle,
  price,
  image,
  details,
  selected = false,
  onSelect,
  className = ''
}) => {
  return (
    <div 
      className={`card card-${type} ${selected ? 'selected' : ''} ${className}`}
      onClick={onSelect}
    >
      {image && (
        <div className="card-image">
          <img src={image} alt={title} />
        </div>
      )}
      
      <div className="card-content">
        <div className="card-header">
          <h3 className="card-title">{title}</h3>
          {subtitle && <p className="card-subtitle">{subtitle}</p>}
        </div>
        
        {details && (
          <div className="card-details">
            {details}
          </div>
        )}
        
        {price && (
          <div className="card-footer">
            <span className="card-price">{price}</span>
            {onSelect && (
              <button 
                className={`card-select-btn ${selected ? 'selected' : ''}`}
                onClick={(e) => {
                  e.stopPropagation();
                  onSelect?.();
                }}
              >
                {selected ? 'Selected' : 'Select'}
              </button>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default Card;