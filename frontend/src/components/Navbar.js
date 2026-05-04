import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import NotificationBell from './NotificationBell';
import './Navbar.css';

const Navbar = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  if (!user) {
    return null;
  }

  return (
    <nav className="navbar">
      <div className="container navbar-content">
        <Link to="/dashboard" className="navbar-brand">
          📚 Digital Library
        </Link>

        <div className="navbar-menu">
          <Link to="/dashboard" className="nav-link">
            Dashboard
          </Link>
          <Link to="/books" className="nav-link">
            Books
          </Link>
          <Link to="/transactions" className="nav-link">
            My Transactions
          </Link>
          <Link to="/reservations" className="nav-link">
            Reservations
          </Link>
          <Link to="/fines" className="nav-link">
            Fines
          </Link>
          {(user.role === 'admin' || user.role === 'librarian') && (
            <Link to="/users" className="nav-link">
              Users
            </Link>
          )}
        </div>

        <div className="navbar-user">
          <NotificationBell />
          <span className="user-name">
            {user.first_name} {user.last_name}
          </span>
          <span className="user-role badge badge-info">{user.role}</span>
          <Link to="/profile" className="btn btn-secondary btn-sm">
            Profile
          </Link>
          <button onClick={handleLogout} className="btn btn-danger btn-sm">
            Logout
          </button>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
