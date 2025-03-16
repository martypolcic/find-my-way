import './Header.css'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faEarthEurope } from '@fortawesome/free-solid-svg-icons'

function Header() {
  return (
    <header>
      <span className='flex gap-2 items-center justify-center text-xl p-4'>
        <FontAwesomeIcon icon={faEarthEurope} />
        <h1>Find my way</h1>
      </span>
    </header>
  );
}

export default Header;