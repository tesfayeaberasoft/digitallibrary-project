import React, { useState, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';
import './Fines.css';

const Fines = () => {
  const { user } = useAuth();
  const [fines, setFines] = useState([]);
  const [totalUnpaid, setTotalUnpaid] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');
  const [paymentModal, setPaymentModal] = useState(null);
  const [paymentMethod, setPaymentMethod] = useState('cash');
  const [paymentReference, setPaymentReference] = useState('');

  useEffect(() => {
    fetchFines();
    fetchTotalUnpaid();
  }, []);

  const fetchFines = async () => {
    try {
      setLoading(true);
      const response = await api.get('/fines');
      if (response.data.success) {
        setFines(Array.isArray(response.data.data) ? response.data.data : []);
      }
    } catch (err) {
      setError('Failed to load fines');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const fetchTotalUnpaid = async () => {
    try {
      const response = await api.get('/fines/total');
      if (response.data.success) {
        setTotalUnpaid(response.data.data.total_unpaid);
      }
    } catch (err) {
      console.error('Failed to fetch total unpaid:', err);
    }
  };

  const openPaymentModal = (fine) => {
    setPaymentModal(fine);
    setPaymentMethod('cash');
    setPaymentReference('');
  };

  const closePaymentModal = () => {
    setPaymentModal(null);
    setPaymentMethod('cash');
    setPaymentReference('');
  };

  const handlePayment = async (e) => {
    e.preventDefault();

    try {
      const response = await api.post(`/fines/${paymentModal.id}/pay`, {
        payment_method: paymentMethod,
        payment_reference: paymentReference || null
      });

      if (response.data.success) {
        setSuccessMessage('Fine paid successfully!');
        fetchFines();
        fetchTotalUnpaid();
        closePaymentModal();
        setTimeout(() => setSuccessMessage(''), 3000);
      }
    } catch (err) {
      setError('Failed to process payment');
      console.error(err);
    }
  };

  const getStatusBadge = (status) => {
    const statusClasses = {
      unpaid: 'status-unpaid',
      paid: 'status-paid',
      waived: 'status-waived'
    };

    return (
      <span className={`status-badge ${statusClasses[status]}`}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const formatCurrency = (amount) => {
    return `$${parseFloat(amount).toFixed(2)}`;
  };

  if (loading) {
    return (
      <div className="fines-page">
        <div className="loading-spinner">
          <div className="spinner"></div>
          <p>Loading fines...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="fines-page">
      <div className="page-header">
        <h1>My Fines</h1>
        <p>View and pay your library fines</p>
      </div>

      {error && (
        <div className="alert alert-error">
          {error}
          <button onClick={() => setError('')}>×</button>
        </div>
      )}

      {successMessage && (
        <div className="alert alert-success">
          {successMessage}
          <button onClick={() => setSuccessMessage('')}>×</button>
        </div>
      )}

      {totalUnpaid > 0 && (
        <div className="total-unpaid-card">
          <div className="total-icon">💰</div>
          <div className="total-info">
            <h3>Total Unpaid Fines</h3>
            <p className="total-amount">{formatCurrency(totalUnpaid)}</p>
          </div>
        </div>
      )}

      {fines.length === 0 ? (
        <div className="empty-state">
          <div className="empty-icon">✅</div>
          <h2>No Fines</h2>
          <p>You don't have any fines. Keep up the good work!</p>
        </div>
      ) : (
        <div className="fines-table-container">
          <table className="fines-table">
            <thead>
              <tr>
                <th>Book Title</th>
                <th>Reason</th>
                <th>Days Overdue</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {fines.map((fine) => (
                <tr key={fine.id}>
                  <td>
                    <div className="book-cell">
                      <strong>{fine.book_title}</strong>
                    </div>
                  </td>
                  <td>{fine.reason || 'Overdue fine'}</td>
                  <td>
                    <span className="days-badge">{fine.days_overdue} days</span>
                  </td>
                  <td>
                    <strong className="amount">{formatCurrency(fine.amount)}</strong>
                  </td>
                  <td>{getStatusBadge(fine.status)}</td>
                  <td>{formatDate(fine.created_at)}</td>
                  <td>
                    {fine.status === 'unpaid' && (
                      <button
                        className="btn btn-primary btn-sm"
                        onClick={() => openPaymentModal(fine)}
                      >
                        Pay Now
                      </button>
                    )}
                    {fine.status === 'paid' && (
                      <span className="paid-date">
                        Paid: {formatDate(fine.paid_date)}
                      </span>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* Payment Modal */}
      {paymentModal && (
        <div className="modal-overlay" onClick={closePaymentModal}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>Pay Fine</h2>
              <button className="modal-close" onClick={closePaymentModal}>×</button>
            </div>

            <div className="modal-body">
              <div className="payment-summary">
                <h3>{paymentModal.book_title}</h3>
                <p className="fine-reason">{paymentModal.reason || 'Overdue fine'}</p>
                <p className="fine-amount">Amount: {formatCurrency(paymentModal.amount)}</p>
              </div>

              <form onSubmit={handlePayment}>
                <div className="form-group">
                  <label>Payment Method</label>
                  <select
                    value={paymentMethod}
                    onChange={(e) => setPaymentMethod(e.target.value)}
                    required
                  >
                    <option value="cash">Cash</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="mobile_money">Mobile Money</option>
                  </select>
                </div>

                {paymentMethod !== 'cash' && (
                  <div className="form-group">
                    <label>Payment Reference (Optional)</label>
                    <input
                      type="text"
                      value={paymentReference}
                      onChange={(e) => setPaymentReference(e.target.value)}
                      placeholder="Transaction ID or reference number"
                    />
                  </div>
                )}

                <div className="modal-actions">
                  <button type="button" className="btn btn-secondary" onClick={closePaymentModal}>
                    Cancel
                  </button>
                  <button type="submit" className="btn btn-primary">
                    Confirm Payment
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

      <div className="fines-info">
        <h3>Fine Information</h3>
        <ul>
          <li>Fines are calculated at $0.50 per day for overdue books</li>
          <li>Maximum fine per book is $25.00</li>
          <li>You cannot borrow new books if you have unpaid fines over $10.00</li>
          <li>Payment methods: Cash, Card, Bank Transfer, Mobile Money</li>
          <li>Contact the library if you believe a fine was charged in error</li>
        </ul>
      </div>
    </div>
  );
};

export default Fines;
