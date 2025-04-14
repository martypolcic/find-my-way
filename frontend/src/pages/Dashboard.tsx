import { useEffect, useState } from 'react';
import apiClient from '../api/client';
import Card from '../components/cards/Card';
import './Dashboard.css';

interface ProviderService {
  id: number;
  provider_id: number;
  service_type: 'flight' | 'accomodation';
  class_name: string;
  active: boolean;
}

export default function AdminDashboard() {
  const [services, setServices] = useState<ProviderService[]>([]);

  useEffect(() => {
    apiClient.get('/api/provider-services').then(res => setServices(res.data));
  }, []);

  const toggleActive = async (id: number) => {
    await apiClient.patch(`/api/provider-services/${id}/toggle`);
    setServices(prev =>
      prev.map(service =>
        service.id === id ? { ...service, active: !service.active } : service
      )
    );
  };

  const flights = services.filter(s => s.service_type === 'flight');
  const accommodations = services.filter(s => s.service_type === 'accomodation');

  return (
    <div className="admin-dashboard p-6">
      <section className="service-section">
        <h2 className="section-title">Flights</h2>
        <div className="card-grid">
          {flights.map(service => (
            <Card
              key={service.id}
              type="flight"
              title={service.class_name}
              subtitle="Flight Service"
              details={
                <p className="text-sm text-gray-600">
                  Status: {service.active ? '✅ Active' : '❌ Inactive'}
                </p>
              }
              onSelect={() => toggleActive(service.id)}
              selected={service.active}
            />
          ))}
        </div>
      </section>

      <section className="service-section">
        <h2 className="section-title">Accommodations</h2>
        <div className="card-grid">
          {accommodations.map(service => (
            <Card
              key={service.id}
              type="accommodation"
              title={service.class_name}
              subtitle="Accommodation Service"
              details={
                <p className="text-sm text-gray-600">
                  Status: {service.active ? '✅ Active' : '❌ Inactive'}
                </p>
              }
              onSelect={() => toggleActive(service.id)}
              selected={service.active}
            />
          ))}
        </div>
      </section>
    </div>
  );
}
