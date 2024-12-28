import { useState } from 'react'
import './App.css'
import Header from './components/Header'
import SearchFlightsForm from './components/SearchFlightsForm'
import Jumbotron from './components/Jumbotron';

function App() {
  const [activeTab, setActiveTab] = useState('searchFlights');

  function handleTabChange(tabName: string) {
    console.log('Tab changed:', tabName);
    setActiveTab(tabName);
  }

  return (
    <>
      <Header onChangeTab={handleTabChange}/>
      <Jumbotron/>
      <SearchFlightsForm />
    </>
  )
}

export default App
