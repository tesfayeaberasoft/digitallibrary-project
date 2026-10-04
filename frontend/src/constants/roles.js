/**
 * User role constants — mirrors the backend ENUM.
 */
export const ROLES = {
  ADMIN:     'admin',
  LIBRARIAN: 'librarian',
  STUDENT:   'student',
  STAFF:     'staff',
};

/** Roles that can manage the library (issue books, manage users, view reports) */
export const STAFF_ROLES = [ROLES.ADMIN, ROLES.LIBRARIAN];
