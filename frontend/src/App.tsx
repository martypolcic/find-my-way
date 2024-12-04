import { useState } from 'react'
import './App.css'
import AirportCollection from './components/AirportCollection'
import Header from './components/Header'
import SearchFlightsForm from './components/SearchFlightsForm'

function App() {
  const [activeTab, setActiveTab] = useState('searchFlights');

  function handleTabChange(tabName: string) {
    console.log('Tab changed:', tabName);
    setActiveTab(tabName);
  }

  return (
    <>
      <Header onChangeTab={handleTabChange}/>
      {activeTab === 'searchFlights' && <SearchFlightsForm />}
      {activeTab === 'airports' && <AirportCollection />}
    </>
  )
}

export default App
