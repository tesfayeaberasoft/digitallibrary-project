import React, { useState, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { dashboardAPI } from '../services/api';
import './Dashboard.css';

const Dashboard = () => {
  const { user } = useAuth();
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    try {
      const response = await dashboardAPI.getStats();
      setStats(response.data.data);
    } catch (err) {
      setError('Failed to load dashboard statistics');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Loading dashboard...</div>;
  }

  if (error) {
    return <div className="container"><div className="alert alert-error">{error}</div></div>;
  }

  return (
    <div className="container">
      <div className="page-header">
        <h1>Welcome, {user.first_name}!</h1>
        <p>Here's an overview of the library system</p>
      </div>

      {stats && (
        <>
          <div className="grid grid-4">
            <div className="stat-card">
              <h3>Total Books</h3>
              <div className="stat-value">{stats.total_books}</div>
            </div>

            <div className="stat-card">
              <h3>Available Books</h3>
              <div className="stat-value">{stats.available_books}</div>
            </div>

            <div className="stat-card">
              <h3>Active Transactions</h3>
              <div className="stat-value">{stats.active_transactions}</div>
            </div>

            <div className="stat-card">
              <h3>Overdue Books</h3>
              <div className="stat-value" style={{ color: '#dc3545' }}>
                {stats.overdue_transactions}
              </div>
            </div>
          </div>

          {stats.popular_books && stats.popular_books.length > 0 && (
            <div className="card">
              <h2>Most Borrowed Books</h2>
              <table>
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Times Borrowed</th>
                  </tr>
                </thead>
                <tbody>
                  {stats.popular_books.map((book) => (
                    <tr key={book.id}>
                      <td>{book.title}</td>
                      <td>{book.author}</td>
                      <td>{book.borrow_count}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {stats.recent_transactions && stats.recent_transactions.length > 0 && (
            <div className="card">
              <h2>Recent Transactions</h2>
              <table>
                <thead>
                  <tr>
                    <th>Book</th>
                    <th>User</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  {stats.recent_transactions.map((transaction) => (
                    <tr key={transaction.id}>
                      <td>{transaction.book_title}</td>
                      <td>
                        {transaction.user_first_name} {transaction.user_last_name}
                      </td>
                      <td>{new Date(transaction.issue_date).toLocaleDateString()}</td>
                      <td>{new Date(transaction.due_date).toLocaleDateString()}</td>
                      <td>
                        <span className={`badge badge-${getStatusColor(transaction.status)}`}>
                          {transaction.status}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </>
      )}
    </div>
  );
};

const getStatusColor = (status) => {
  switch (status) {
    case 'issued':
      return 'info';
    case 'returned':
      return 'success';
    case 'overdue':
      return 'danger';
    default:
      return 'secondary';
  }
};

export default Dashboard;
