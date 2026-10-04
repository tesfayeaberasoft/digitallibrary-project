/**
 * Status constants — mirrors backend ENUM values.
 */

export const TRANSACTION_STATUS = {
  ISSUED:   'issued',
  RETURNED: 'returned',
  OVERDUE:  'overdue',
};

export const FINE_STATUS = {
  UNPAID: 'unpaid',
  PAID:   'paid',
  WAIVED: 'waived',
};

export const RESERVATION_STATUS = {
  PENDING:   'pending',
  FULFILLED: 'fulfilled',
  EXPIRED:   'expired',
  CANCELLED: 'cancelled',
};

export const USER_STATUS = {
  ACTIVE:    'active',
  INACTIVE:  'inactive',
  SUSPENDED: 'suspended',
};
