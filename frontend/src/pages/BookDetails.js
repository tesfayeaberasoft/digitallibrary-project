import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { booksAPI } from '../services/api';

const BookDetails = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [book, setBook] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchBook = useCallback(async () => {
    try {
      const response = await booksAPI.getById(id);
      setBook(response.data.data);
    } catch (error) {
      console.error('Failed to fetch book:', error);
    } finally {
      setLoading(false);
    }
  }, [id]);

  useEffect(() => {
    fetchBook();
  }, [fetchBook]);

  const fetchBook = async () => {
    try {
      const response = await booksAPI.getById(id);
      setBook(response.data.data);
    } catch (error) {
      console.error('Failed to fetch book:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Loading book details...</div>;
  }

  if (!book) {
    return <div className="container"><div className="alert alert-error">Book not found</div></div>;
  }

  return (
    <div className="container">
      <button onClick={() => navigate('/books')} className="btn btn-secondary" style={{ marginBottom: '20px' }}>
        ← Back to Books
      </button>

      <div className="card">
        <div style={{ display: 'grid', gridTemplateColumns: '300px 1fr', gap: '30px' }}>
          <div>
            {book.cover_image ? (
              <img src={book.cover_image} alt={book.title} style={{ width: '100%', borderRadius: '8px' }} />
            ) : (
              <div style={{ width: '100%', height: '400px', background: '#f0f0f0', borderRadius: '8px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '80px' }}>
                📚
              </div>
            )}
          </div>

          <div>
            <h1>{book.title}</h1>
            <p style={{ fontSize: '18px', color: '#666', marginBottom: '20px' }}>{book.author}</p>

            <div style={{ marginBottom: '20px' }}>
              <span className={`badge ${book.available_copies > 0 ? 'badge-success' : 'badge-danger'}`}>
                {book.available_copies > 0 ? `${book.available_copies} of ${book.total_copies} Available` : 'Not Available'}
              </span>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px', marginBottom: '20px' }}>
              <div>
                <strong>ISBN:</strong> {book.isbn}
              </div>
              <div>
                <strong>Category:</strong> {book.category || 'N/A'}
              </div>
              <div>
                <strong>Publisher:</strong> {book.publisher || 'N/A'}
              </div>
              <div>
                <strong>Year:</strong> {book.publication_year || 'N/A'}
              </div>
              <div>
                <strong>Edition:</strong> {book.edition || 'N/A'}
              </div>
              <div>
                <strong>Pages:</strong> {book.pages || 'N/A'}
              </div>
              <div>
                <strong>Language:</strong> {book.language}
              </div>
              <div>
                <strong>Location:</strong> {book.location || 'N/A'}
              </div>
            </div>

            {book.description && (
              <div>
                <h3>Description</h3>
                <p style={{ color: '#666', lineHeight: '1.6' }}>{book.description}</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default BookDetails;
