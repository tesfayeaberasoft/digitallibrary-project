import React, { useState, useEffect, useCallback } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { transactionsAPI } from '../services/api';

const Transactions = () => {
  const { user } = useAuth();
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchTransactions = useCallback(async () => {
    try {
      const response = await transactionsAPI.getUserTransactions(user.id);
      setTransactions(response.data.data.transactions);
    } catch (error) {
      console.error('Failed to fetch transactions:', error);
    } finally {
      setLoading(false);
    }
  }, [user.id]);

  useEffect(() => {
    fetchTransactions();
  }, [fetchTransactions]);

  if (loading) {
    return <div className="loading">Loading transactions...</div>;
  }

  return (
    <div className="container">
      <div className="page-header">
        <h1>My Transactions</h1>
        <p>View your borrowing history</p>
      </div>

      {transactions.length === 0 ? (
        <div className="empty-state">
          <h3>No transactions found</h3>
          <p>You haven't borrowed any books yet</p>
        </div>
      ) : (
        <div className="card">
          <table>
            <thead>
              <tr>
                <th>Book</th>
                <th>Author</th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              {transactions.map((transaction) => (
                <tr key={transaction.id}>
                  <td>{transaction.book_title}</td>
                  <td>{transaction.book_author}</td>
                  <td>{new Date(transaction.issue_date).toLocaleDateString()}</td>
                  <td>{new Date(transaction.due_date).toLocaleDateString()}</td>
                  <td>{transaction.return_date ? new Date(transaction.return_date).toLocaleDateString() : '-'}</td>
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

export default Transactions;
