import React, { useState } from 'react';
import axios from 'axios';
import './AuthForm.css';

function AuthForm({onLogin}: {onLogin: () => void}) {
  const [isRegistering, setIsRegistering] = useState(false);
  const [authData, setAuthData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const [errorMessage, setErrorMessage] = useState('');

  async function handleSubmit(event: React.FormEvent) {
    event.preventDefault();

    const endpoint = isRegistering ? '/api/register' : '/api/login';

    try {
      await axios.get('/sanctum/csrf-cookie');
      const response = await axios.post(endpoint, authData);
      onLogin();
    } catch (error: any) {
      console.error(`Error ${isRegistering ? 'registering' : 'logging in'} user:`, error.response?.data || error.message);
      setErrorMessage(error.response?.data.message || error.message);
    }
  }

  return (
    <div className='auth-form'>
      <form onSubmit={handleSubmit} className='inputs'>
        {
          errorMessage && <p className='error-message'>{errorMessage}</p>
        }
        {
          isRegistering && (
            <input
              type="text"
              value={authData.name}
              onChange={(e) => setAuthData({ ...authData, name: e.target.value })}
              placeholder="Username"
              required
            />
          )
        }
        <input
          type="email"
          value={authData.email}
          onChange={(e) => setAuthData({ ...authData, email: e.target.value })}
          placeholder="Email"
          required
        />
        <input
          type="password"
          value={authData.password}
          onChange={(e) => setAuthData({ ...authData, password: e.target.value })}
          placeholder="Password"
          required
        />
        {
          isRegistering &&
            <input
            type="password"
            value={authData.password_confirmation}
            onChange={(e) => setAuthData({ ...authData, password_confirmation: e.target.value })}
            placeholder="Confirm password"
            required
          />
        }
        <button type="submit">
          {isRegistering ? 'Register' : 'Login'}
        </button>
      </form>
      <p className='register-text'>
        {isRegistering ? 'Already have an account?' : "Don't have an account?"}
        <button onClick={() => setIsRegistering(!isRegistering)}>
          {isRegistering ? 'Login here' : 'Register here'}
        </button>
      </p>
    </div>
  );
}

export default AuthForm;
