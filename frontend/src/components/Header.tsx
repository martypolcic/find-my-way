import './Header.css'

interface HeaderProps {
  onChangeTab: (tabName: string) => void;
  onLogout: () => void;
  isLogged: boolean;
}

function Header({ onChangeTab, onLogout, isLogged }: HeaderProps) {
  return (
    <header className='navbar'>
        <button onClick={() => onChangeTab('searchFlights')}>Home</button>
        {
          isLogged
            ? <>
            <button onClick={() => onChangeTab('management')}>Manage</button>
            <button onClick={onLogout}>Logout</button> 
            </>
            : <button onClick={() => onChangeTab('auth')}>Login</button>
        }
        
    </header>
  );
}

export default Header;