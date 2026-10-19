const API = (() => {

  async function request(url, method = 'GET', data = null, loading = true, silent = false) {
    try {
      if (loading) Alpine.store('ui').startLoading();

      let options = {
        method,
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json'
        }
      };

      if (method !== 'GET' && method !== 'HEAD' && window.CSRF_TOKEN) {
        options.headers['X-CSRF-Token'] = window.CSRF_TOKEN;
      }

      if (data) {
        if (data instanceof FormData) {
          options.body = data;
        } else if (typeof data === 'object') {
          options.headers['Content-Type'] = 'application/json';
          options.body = JSON.stringify(data);
        }

        if ((method === 'PUT' || method === 'DELETE') && options.body instanceof FormData) {
          options.body.append('_method', method);
          options.method = 'POST';
        }
      }

      const res = await fetch(BASE_URL + '/api' + url, options);
      const json = await res.json().catch(() => ({ success: false, message: 'Respons server tidak valid' }));

      if (!res.ok || !json.success) {
        throw new Error(json.message || 'Terjadi kesalahan');
      }

      if (json.message && !silent) Alpine.store('ui').toast(json.message, 'success');
      return json;
    } catch (err) {
      if (!silent) Alpine.store('ui').toast(err.message, 'error');
      throw err;
    } finally {
      if (loading) Alpine.store('ui').stopLoading();
    }
  }

  return {
    get: (url, loading = true, silent = false) => request(url, 'GET', null, loading, silent),
    post: (url, data, loading = true, silent = false) => request(url, 'POST', data, loading, silent),
    put: (url, data, loading = true) => request(url, 'PUT', data, loading),
    delete: (url, data, loading = true) => request(url, 'DELETE', data, loading),
  };
})();
