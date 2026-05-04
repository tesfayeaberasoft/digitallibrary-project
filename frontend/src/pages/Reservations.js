import React, { useState, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';
import './Reservations.css';

const Reservations = () => {
  const { user } = useAuth();
  const [reservations, setReservations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  useEffect(() => {
    fetchReservations();
  }, []);

  const fetchReservations = async () => {
    try {
      setLoading(true);
      const response = await api.get('/reservations');
      if (response.data.success) {
        setReservations(response.data.data);
      }
    } catch (err) {
      setError('Failed to load reservations');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const cancelReservation = async (reservationId) => {
    if (!window.confirm('Are you sure you want to cancel this reservation?')) {
      return;
    }

    try {
      const response = await api.delete(`/reservations/${reservationId}`);
      if (response.data.success) {
        setSuccessMessage('Reservation cancelled successfully');
        fetchReservations();
        setTimeout(() => setSuccessMessage(''), 3000);
      }
    } catch (err) {
      setError('Failed to cancel reservation');
      console.error(err);
    }
  };

  const getStatusBadge = (status) => {
    const statusClasses = {
      pending: 'status-pending',
      fulfilled: 'status-fulfilled',
      expired: 'status-expired',
      cancelled: 'status-cancelled'
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
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  if (loading) {
    return (
      <div className="reservations-page">
        <div className="loading-spinner">
          <div className="spinner"></div>
          <p>Loading reservations...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="reservations-page">
      <div className="page-header">
        <h1>My Reservations</h1>
        <p>Manage your book reservations and queue positions</p>
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

      {reservations.length === 0 ? (
        <div className="empty-state">
          <div className="empty-icon">📚</div>
          <h2>No Reservations</h2>
          <p>You haven't reserved any books yet.</p>
          <a href="/books" className="btn btn-primary">
            Browse Books
          </a>
        </div>
      ) : (
        <div className="reservations-grid">
          {reservations.map((reservation) => (
            <div key={reservation.id} className="reservation-card">
              <div className="reservation-header">
                <div className="book-info">
                  <h3>{reservation.book_title}</h3>
                  <p className="book-author">by {reservation.book_author}</p>
                </div>
                {getStatusBadge(reservation.status)}
              </div>

              <div className="reservation-details">
                <div className="detail-row">
                  <span className="detail-label">Reserved On:</span>
                  <span className="detail-value">{formatDate(reservation.reservation_date)}</span>
                </div>

                <div className="detail-row">
                  <span className="detail-label">Expires On:</span>
                  <span className="detail-value">{formatDate(reservation.expiry_date)}</span>
                </div>

                {reservation.queue_position && (
                  <div className="detail-row">
                    <span className="detail-label">Queue Position:</span>
                    <span className="detail-value queue-position">
                      #{reservation.queue_position}
                    </span>
                  </div>
                )}

                {reservation.fulfilled_date && (
                  <div className="detail-row">
                    <span className="detail-label">Fulfilled On:</span>
                    <span className="detail-value">{formatDate(reservation.fulfilled_date)}</span>
                  </div>
                )}
              </div>

              {reservation.status === 'pending' && (
                <div className="reservation-actions">
                  <button
                    className="btn btn-danger btn-sm"
                    onClick={() => cancelReservation(reservation.id)}
                  >
                    Cancel Reservation
                  </button>
                </div>
              )}

              {reservation.status === 'fulfilled' && !reservation.notified && (
                <div className="reservation-notice">
                  <span className="notice-icon">✓</span>
                  <span>Your book is ready for pickup!</span>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      <div className="reservations-info">
        <h3>Reservation Information</h3>
        <ul>
          <li>Reservations are valid for 7 days from the date of reservation</li>
          <li>You will be notified when your reserved book becomes available</li>
          <li>Please pick up your book within 48 hours of notification</li>
          <li>Expired reservations will be automatically cancelled</li>
        </ul>
      </div>
    </div>
  );
};

export default Reservations;
