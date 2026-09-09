// export const API_BASE_URL = 'http://localhost';

export const API_BASE_URL = 'https://dmr-project-mwmp.onrender.com';

export const resolveServerUrl = (path: string) => {
  if (!path) return '';
  if (path.startsWith('http') || path.startsWith('data:') || path.startsWith('blob:')) {
    return path;
  }
  const cleanPath = path.startsWith('/') ? path.slice(1) : path;
  return `${API_BASE_URL}/${cleanPath}`;
};

async function handleResponse(response: Response) {
  const data = await response.json().catch(() => ({
    status: 'error',
    message: response.statusText || 'Network / Server Error',
  }));

  if (!response.ok) {
    throw new Error(data.message || `Request failed with status ${response.status}`);
  }
  return data;
}

function getHeaders(isFormData = false) {
  const email = localStorage.getItem('userEmail') || 'admin@gmail.com';
  const headers: Record<string, string> = {
    'X-User-Email': email,
    'Authorization': `Bearer ${email}`,
  };
  if (!isFormData) {
    headers['Content-Type'] = 'application/json';
  }
  return headers;
}

export const api = {
  async get(endpoint: string) {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'GET',
      credentials: 'include',
      headers: getHeaders(),
    });
    return handleResponse(response);
  },

  async post(endpoint: string, body: any) {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'POST',
      credentials: 'include', 
      headers: getHeaders(),
      body: JSON.stringify(body),
    });
    return handleResponse(response);
  },

  async postFormData(endpoint: string, formData: FormData) {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'POST',
      credentials: 'include',
      headers: getHeaders(true),
      body: formData,
    });
    return handleResponse(response);
  },

  async put(endpoint: string, body: any) {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'PUT',
      credentials: 'include', 
      headers: getHeaders(),
      body: JSON.stringify(body),
    });
    return handleResponse(response);
  },

  async delete(endpoint: string) {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'DELETE',
      credentials: 'include',
      headers: getHeaders(),
    });
    return handleResponse(response);
  }
};