/**
 * Client-side validation helpers.
 */

export function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

export function isStrongPassword(password) {
  // At least 8 characters
  return password && password.length >= 8;
}

export function isRequired(value) {
  return value !== null && value !== undefined && String(value).trim() !== '';
}
