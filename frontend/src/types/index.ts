export interface UserRecord {
  row: number;
  status: 'VALID' | 'ERROR';
  name: string;
  surname: string;
  email: string;
  error: string | null;
}

export interface ValidationSummary {
  success: boolean;
  filename?: string;
  total_rows: number;
  valid_count: number;
  invalid_count: number;
  imported_count?: number;
  is_dry_run: boolean;
  records: UserRecord[];
  error?: string;
}

export interface DatabaseUser {
  id: number;
  name: string;
  surname: string;
  email: string;
}

export interface ApiResponse<T = unknown> {
  success: boolean;
  message?: string;
  error?: string;
  data?: T;
}
