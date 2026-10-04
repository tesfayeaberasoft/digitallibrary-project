/**
 * API-related constants.
 */
export const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

/** Fine rate in ETB per day — mirrors backend Fine::FINE_PER_DAY */
export const FINE_PER_DAY_ETB = 5.0;

/** Reservation expiry window in hours */
export const RESERVATION_EXPIRY_HOURS = 48;

/** Notification poll interval in milliseconds */
export const NOTIFICATION_POLL_INTERVAL_MS = 30000;
