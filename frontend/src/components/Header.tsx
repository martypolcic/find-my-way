import './Header.css';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
  faEarthEurope,
  faArrowRightToBracket,
  faArrowRightFromBracket,
  faGear
} from '@fortawesome/free-solid-svg-icons';
import { Link, useNavigate } from 'react-router-dom';
import { useSelector, useDispatch } from 'react-redux';
import { selectIsAuthenticated, logout } from '../store/authSlice';
import apiClient from '../api/client';
import { AppDispatch } from '../store/store';

function Header() {
  const isAuthenticated = useSelector(selectIsAuthenticated);
  const dispatch = useDispatch<AppDispatch>();
  const navigate = useNavigate();

  const handleLogout = async () => {
    try {
      await apiClient.post('/api/logout');
      dispatch(logout());
      navigate('/login');
    } catch (error) {
      console.error('Logout failed:', error);
    }
  };

  return (
    <header>
      <Link to="/" className="flex gap-2 items-center justify-center text-xl p-4 hover:text-green-800 transition-colors">
        <FontAwesomeIcon icon={faEarthEurope} />
        <h1>Find my way</h1>
      </Link>

      <div className="absolute right-4 top-1/2 -translate-y-1/2 flex gap-4 items-center text-xl">
        {isAuthenticated && (
          <Link to="/admin" title="Admin Dashboard">
            <FontAwesomeIcon icon={faGear} className="hover:text-gray-700 transition-colors" />
          </Link>
        )}

        {!isAuthenticated ? (
          <Link to="/login" title="Login">
            <FontAwesomeIcon icon={faArrowRightToBracket} className="hover:text-green-700 transition-colors" />
          </Link>
        ) : (
          <button title="Logout" onClick={handleLogout}>
            <FontAwesomeIcon icon={faArrowRightFromBracket} className="hover:text-red-700 transition-colors" />
          </button>
        )}
      </div>
    </header>
  );
}

export default Header;
