import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import Navbar from './components/Navbar';
import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import Books from './pages/Books';
import BookDetails from './pages/BookDetails';
import Transactions from './pages/Transactions';
import Reservations from './pages/Reservations';
import Fines from './pages/Fines';
import Profile from './pages/Profile';
import Users from './pages/Users';
import './App.css';

// Protected Route Component
const ProtectedRoute = ({ children, roles }) => {
  const { user, loading } = useAuth();

  if (loading) {
    return <div className="loading">Loading...</div>;
  }

  if (!user) {
    return <Navigate to="/login" />;
  }

  if (roles && !roles.includes(user.role)) {
    return <Navigate to="/dashboard" />;
  }

  return children;
};

// Layout wrapper to conditionally show Navbar
const Layout = ({ children, showNavbar }) => {
  return (
    <div className="App">
      {showNavbar && <Navbar />}
      {children}
    </div>
  );
};

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          {/* Public Routes without Navbar */}
          <Route path="/" element={<Layout showNavbar={false}><Home /></Layout>} />
          <Route path="/login" element={<Layout showNavbar={false}><Login /></Layout>} />
          <Route path="/register" element={<Layout showNavbar={false}><Register /></Layout>} />
          
          {/* Protected Routes with Navbar */}
          <Route
            path="/dashboard"
            element={
              <Layout showNavbar={true}>
                <main className="main-content">
                  <ProtectedRoute>
                    <Dashboard />
                  </ProtectedRoute>
                </main>
              </Layout>
            }
          />
          
          <Route
            path="/books"
            element={
              <Layout showNavbar={true}>
                <main className="main-content">
                  <ProtectedRoute>
                    <Books />
                  </ProtectedRoute>
                </main>
              </Layout>
            }
          />
          
          <Route
            path="/books/:id"
            element={
              <Layout showNavbar={true}>
                <main className="main-content">
                  <ProtectedRoute>
                    <BookDetails />
                  </ProtectedRoute>
                </main>
              </Layout>
            }
          />
          
          <Route
            path="/transactions"
            element={
              <Layout showNavbar={true}>
                <main className="main-content">
                  <ProtectedRoute>
                    <Transactions />
                  </ProtectedRoute>
                </main>
              </Layout>
            }
          />
          
          <Route
            path="/reservations"
            element={
              <Layout showNavbar={true}>
                <main className="main-content">
                  <ProtectedRoute>
                    <Reservations />
                  </ProtectedRoute>
                </main>
              </Layout>
            }
          />
          
          <Route
            path="/fines"
            element={
              <Layout showNavbar={true}>
                <main className="main-content">
                  <ProtectedRoute>
                    <Fines />
                  </ProtectedRoute>
                </main>
              </Layout>
            }
          />
          
          <Route
            path="/profile"
            element={
              <Layout showNavbar={true}>
                <main className="main-content">
                  <ProtectedRoute>
                    <Profile />
                  </ProtectedRoute>
                </main>
              </Layout>
            }
          />
          
          <Route
            path="/users"
            element={
              <Layout showNavbar={true}>
                <main className="main-content">
                  <ProtectedRoute roles={['admin', 'librarian']}>
                    <Users />
                  </ProtectedRoute>
                </main>
              </Layout>
            }
          />
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default App;
