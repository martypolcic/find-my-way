import React from 'react';
import axios from 'axios';

function Logout() {
  async function logoutUser() {
    try {
      await axios.post('/logout');
      console.log('User logged out successfully');
    } catch (error: any) {
      console.error('Error logging out user:', error.response?.data || error.message);
    }
  }

  return <button onClick={logoutUser}>Logout</button>;
}

export default Logout;
