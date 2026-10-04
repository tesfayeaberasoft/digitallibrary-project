/**
 * Utility functions for formatting values across the app.
 */

/**
 * Format a date string to a readable local date.
 * @param {string} dateStr - ISO date string
 * @returns {string} e.g. "Jan 15, 2026"
 */
export function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

/**
 * Format a number as Ethiopian Birr currency.
 * @param {number} amount
 * @returns {string} e.g. "ETB 25.00"
 */
export function formatCurrency(amount) {
  if (amount === null || amount === undefined) return '—';
  return `ETB ${parseFloat(amount).toFixed(2)}`;
}

/**
 * Capitalize the first letter of a string.
 * @param {string} str
 * @returns {string}
 */
export function capitalize(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Return a relative time string (e.g. "2 hours ago").
 * @param {string} dateStr - ISO date string
 * @returns {string}
 */
export function timeAgo(dateStr) {
  const now = new Date();
  const past = new Date(dateStr);
  const diffMs = now - past;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMins / 60);
  const diffDays = Math.floor(diffHours / 24);

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  return `${diffDays}d ago`;
}
