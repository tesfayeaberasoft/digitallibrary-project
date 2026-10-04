import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import './Register.css';
import './Login.css'; // Reuse login styles

const Register = () => {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    first_name: '',
    last_name: '',
    phone: '',
    address: '',
    role: 'student',
  });
  const [error, setError] = useState('');
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const { register } = useAuth();
  const navigate = useNavigate();

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setErrors({});
    setLoading(true);

    const result = await register(formData);

    if (result.success) {
      navigate('/login', { state: { message: 'Registration successful! Please login.' } });
    } else {
      setError(result.message);
      setErrors(result.errors || {});
    }

    setLoading(false);
  };

  return (
    <div className="register-page">
      {/* Left Side - Illustration/Branding */}
      <div className="register-left">
        <div className="register-branding">
          <Link to="/" className="brand-logo">
            <span className="logo-icon">📚</span>
            <span className="logo-text">Digital Library</span>
          </Link>
          
          <div className="register-illustration">
            <div className="register-icon">🎓</div>
            <h2 className="register-title">Join Our Community</h2>
            <p className="register-text">
              Create your account and unlock access to thousands of books and resources
            </p>
          </div>
          
          <div className="register-benefits">
            <div className="benefit-item">
              <span className="benefit-icon">✓</span>
              <span>Free account with instant access</span>
            </div>
            <div className="benefit-item">
              <span className="benefit-icon">✓</span>
              <span>Borrow unlimited books</span>
            </div>
            <div className="benefit-item">
              <span className="benefit-icon">✓</span>
              <span>Track your reading progress</span>
            </div>
            <div className="benefit-item">
              <span className="benefit-icon">✓</span>
              <span>Personalized recommendations</span>
            </div>
          </div>
        </div>
      </div>

      {/* Right Side - Register Form */}
      <div className="register-right">
        <div className="register-form-container">
          <div className="register-header">
            <h1 className="register-form-title">Create Account</h1>
            <p className="register-form-subtitle">Fill in your details to get started</p>
          </div>

          {error && (
            <div className="alert alert-error">
              <span className="alert-icon">⚠️</span>
              <span>{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="login-form">
            <div className="form-row">
              <div className="form-group">
                <label className="form-label">First Name *</label>
                <div className="input-wrapper">
                  <span className="input-icon">👤</span>
                  <input
                    type="text"
                    name="first_name"
                    className="form-input"
                    placeholder="First name"
                    value={formData.first_name}
                    onChange={handleChange}
                    required
                  />
                </div>
                {errors.first_name && <div className="error">{errors.first_name[0]}</div>}
              </div>

              <div className="form-group">
                <label className="form-label">Last Name *</label>
                <div className="input-wrapper">
                  <span className="input-icon">👤</span>
                  <input
                    type="text"
                    name="last_name"
                    className="form-input"
                    placeholder="Last name"
                    value={formData.last_name}
                    onChange={handleChange}
                    required
                  />
                </div>
                {errors.last_name && <div className="error">{errors.last_name[0]}</div>}
              </div>
            </div>

            <div className="form-group">
              <label className="form-label">Email Address *</label>
              <div className="input-wrapper">
                <span className="input-icon">📧</span>
                <input
                  type="email"
                  name="email"
                  className="form-input"
                  placeholder="Enter your email"
                  value={formData.email}
                  onChange={handleChange}
                  required
                />
              </div>
              {errors.email && <div className="error">{errors.email[0]}</div>}
            </div>

            <div className="form-group">
              <label className="form-label">Password *</label>
              <div className="input-wrapper">
                <span className="input-icon">🔒</span>
                <input
                  type={showPassword ? 'text' : 'password'}
                  name="password"
                  className="form-input"
                  placeholder="Create a password"
                  value={formData.password}
                  onChange={handleChange}
                  required
                  minLength="6"
                />
                <button
                  type="button"
                  className="password-toggle"
                  onClick={() => setShowPassword(!showPassword)}
                >
                  {showPassword ? '👁️' : '👁️‍🗨️'}
                </button>
              </div>
              {errors.password && <div className="error">{errors.password[0]}</div>}
            </div>

            <div className="form-group">
              <label className="form-label">Phone Number</label>
              <div className="input-wrapper">
                <span className="input-icon">📱</span>
                <input
                  type="tel"
                  name="phone"
                  className="form-input"
                  placeholder="Enter your phone"
                  value={formData.phone}
                  onChange={handleChange}
                />
              </div>
              {errors.phone && <div className="error">{errors.phone[0]}</div>}
            </div>

            <div className="form-group">
              <label className="form-label">Address</label>
              <div className="input-wrapper">
                <span className="input-icon">📍</span>
                <input
                  type="text"
                  name="address"
                  className="form-input"
                  placeholder="Enter your address"
                  value={formData.address}
                  onChange={handleChange}
                />
              </div>
            </div>

            <div className="form-group">
              <label className="form-label">I am a *</label>
              <div className="input-wrapper">
                <span className="input-icon">🎯</span>
                <select 
                  name="role" 
                  className="form-input" 
                  value={formData.role} 
                  onChange={handleChange}
                  style={{ paddingLeft: '48px' }}
                >
                  <option value="student">Student</option>
                  <option value="staff">Staff</option>
                </select>
              </div>
            </div>

            <button type="submit" className="btn-submit" disabled={loading}>
              {loading ? (
                <>
                  <span className="spinner"></span>
                  <span>Creating Account...</span>
                </>
              ) : (
                <>
                  <span>Create Account</span>
                  <span className="btn-arrow">→</span>
                </>
              )}
            </button>
          </form>

          <div className="form-divider">
            <span className="divider-line"></span>
            <span className="divider-text">OR</span>
            <span className="divider-line"></span>
          </div>

          <button className="btn-social btn-google">
            <span className="social-icon">G</span>
            <span>Sign up with Google</span>
          </button>

          <div className="form-footer">
            <p className="footer-text">
              Already have an account?{' '}
              <Link to="/login" className="footer-link">
                Sign In
              </Link>
            </p>
            <Link to="/" className="back-home">
              ← Back to Home
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Register;
