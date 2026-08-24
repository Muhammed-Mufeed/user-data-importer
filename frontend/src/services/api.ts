import { ValidationSummary, DatabaseUser } from '../types';

const API_BASE_URL = 'http://localhost:8000';

export async function validateCsv(file: File): Promise<ValidationSummary> {
  const formData = new FormData();
  formData.append('file', file);

  const response = await fetch(`${API_BASE_URL}/api/validate`, {
    method: 'POST',
    body: formData,
  });

  const data = await response.json();
  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Failed to validate CSV file.');
  }

  return data;
}

export async function importUsers(file: File): Promise<ValidationSummary> {
  const formData = new FormData();
  formData.append('file', file);

  const response = await fetch(`${API_BASE_URL}/api/import`, {
    method: 'POST',
    body: formData,
  });

  const data = await response.json();
  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Failed to import CSV records.');
  }

  return data;
}

export async function createDatabaseTable(): Promise<{ success: boolean; message: string }> {
  const response = await fetch(`${API_BASE_URL}/api/create-table`, {
    method: 'POST',
  });

  const data = await response.json();
  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Failed to create PostgreSQL table.');
  }

  return data;
}

export async function fetchLiveUsers(): Promise<{ success: boolean; users: DatabaseUser[]; count: number }> {
  const response = await fetch(`${API_BASE_URL}/api/users`);
  const data = await response.json();

  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Failed to fetch database users.');
  }

  return data;
}
