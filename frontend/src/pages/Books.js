import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { booksAPI } from '../services/api';
import './Books.css';

const Books = () => {
  const { user } = useAuth();
  const [books, setBooks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [showAddModal, setShowAddModal] = useState(false);

  useEffect(() => {
    fetchBooks();
  }, []);

  const fetchBooks = async () => {
    try {
      const response = await booksAPI.getAll();
      setBooks(response.data.data.books);
    } catch (error) {
      console.error('Failed to fetch books:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = async (e) => {
    e.preventDefault();
    if (!searchQuery.trim()) {
      fetchBooks();
      return;
    }

    try {
      const response = await booksAPI.search(searchQuery);
      setBooks(response.data.data.books);
    } catch (error) {
      console.error('Search failed:', error);
    }
  };

  if (loading) {
    return <div className="loading">Loading books...</div>;
  }

  return (
    <div className="container">
      <div className="page-header">
        <h1>Books</h1>
        <p>Browse and search library books</p>
      </div>

      <div className="actions">
        <form onSubmit={handleSearch} className="search-bar">
          <input
            type="text"
            className="form-control"
            placeholder="Search by title, author, or ISBN..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
          <button type="submit" className="btn btn-primary">
            Search
          </button>
        </form>

        {(user.role === 'admin' || user.role === 'librarian') && (
          <button className="btn btn-success" onClick={() => setShowAddModal(true)}>
            + Add Book
          </button>
        )}
      </div>

      {books.length === 0 ? (
        <div className="empty-state">
          <h3>No books found</h3>
          <p>Try adjusting your search or add new books to the library</p>
        </div>
      ) : (
        <div className="books-grid">
          {books.map((book) => (
            <Link to={`/books/${book.id}`} key={book.id} className="book-card">
              <div className="book-cover">
                {book.cover_image ? (
                  <img src={book.cover_image} alt={book.title} />
                ) : (
                  <div className="book-placeholder">📚</div>
                )}
              </div>
              <div className="book-info">
                <h3>{book.title}</h3>
                <p className="book-author">{book.author}</p>
                <p className="book-category">{book.category}</p>
                <div className="book-availability">
                  <span className={`badge ${book.available_copies > 0 ? 'badge-success' : 'badge-danger'}`}>
                    {book.available_copies > 0 ? `${book.available_copies} Available` : 'Not Available'}
                  </span>
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
};

export default Books;
