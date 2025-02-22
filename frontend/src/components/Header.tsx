import './Header.css'

interface HeaderProps {
  onChangeTab: (tabName: string) => void;
}

function Header({ onChangeTab }: HeaderProps) {
  return (
    <header>
        <button onClick={() => onChangeTab('searchFlights')}>Home</button>
    </header>
  );
}

export default Header;