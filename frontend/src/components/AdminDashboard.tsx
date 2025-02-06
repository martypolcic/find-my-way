import { useState } from "react";
import AirportManagement from "./AirportManagement";
import FlightManagement from "./FlightManagement";
import "./AdminDashboard.css";

const AdminDashboard = () => {
  const [view, setView] = useState<"airports" | "flights" | null>(null);

  return (
    <div className="admin-dashboard">
      <h1>Admin Dashboard</h1>
      {!view ? (
        <div className="dashboard-buttons">
          <button onClick={() => setView("airports")}>Manage Airports</button>
          <button onClick={() => setView("flights")}>Manage Flights</button>
        </div>
      ) : (
        <div className="managment-wrapper">
          {view === "airports" ? <AirportManagement /> : <FlightManagement />}
          <button className="back-button" onClick={() => setView(null)}>
            Back to Dashboard
          </button>
        </div>
      )}
    </div>
  );
};

export default AdminDashboard;
