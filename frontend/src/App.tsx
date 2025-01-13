import { useState } from 'react'
import './App.css'
import Header from './components/Header'
import SearchFlightsForm from './components/SearchFlightsForm'
import Jumbotron from './components/Jumbotron';
import AuthForm from './components/AuthForm';
import AdminManagement from './components/AdminManagement';

function App() {
  const [activeTab, setActiveTab] = useState('searchFlights');
  const [isLogged, setIsLogged] = useState(false);

  function handleTabChange(tabName: string) {
    if (tabName === 'management' && !isLogged) {
      setActiveTab('auth');
      return;
    }

    setActiveTab(tabName);
  }

  function handleLogin() {
    setIsLogged(true);
    setActiveTab('searchFlights');
  }

  function handleLogout() {
    setIsLogged(false);
  }

  function renderComponents() {
    if (activeTab === 'auth') {
      return <AuthForm onLogin={handleLogin} />;
    } else if (activeTab === 'management') {
      return <AdminManagement />;
    }



    return (
      <>
        <Jumbotron />
        <SearchFlightsForm />
      </>
    );
  }

  return (
    <>
      <Header onChangeTab={handleTabChange} onLogout={handleLogout} isLogged={isLogged}/>
      {
        renderComponents()
      }
    </>
  )
}

export default App
