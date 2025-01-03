import type { AxiosProgressEvent, AxiosResponse, GenericAbortSignal } from 'axios'
import request from './axios'


export interface HttpOption {
  url: string
  data?: any
  method?: string
  headers?: any
  onDownloadProgress?: (progressEvent: AxiosProgressEvent) => void
  signal?: GenericAbortSignal
  beforeRequest?: () => void
  afterRequest?: () => void
}

function http<T = any>({
  url,
  data,
  method,
  headers,
  onDownloadProgress,
  signal,
  beforeRequest,
  afterRequest,
}: HttpOption) {
  const successHandler = (res: AxiosResponse<T>) => {


    if (typeof res.data === 'string') {
      return res.data;
    }
    return res.data;
  };

  const failHandler = (error: Error) => {
    afterRequest?.();
    throw new Error(error?.message || 'Error');
  };

  beforeRequest?.();

  method = method || 'GET';

  const params = Object.assign(typeof data === 'function' ? data() : data ?? {}, {});

  switch (method.toUpperCase()) {
    case 'GET':
      return request.get(url, { params, signal, onDownloadProgress }).then(successHandler, failHandler);
    case 'POST':
      return request.post(url, params, { headers, signal, onDownloadProgress }).then(successHandler, failHandler);
    case 'PUT':
      return request.put(url, params, { headers, signal, onDownloadProgress }).then(successHandler, failHandler);
    case 'DELETE':
      return request.delete(url, {  params, headers, signal, onDownloadProgress }).then(successHandler, failHandler);
    default:
      throw new Error(`Unsupported method: ${method}`);
  }
}




export function get<T = any>(
  { url, data, method = 'GET', onDownloadProgress, signal, beforeRequest, afterRequest }: HttpOption,
): Promise<T> {
  return http<T>({
    url,
    method,
    data,
    onDownloadProgress,
    signal,
    beforeRequest,
    afterRequest,
  })
}

export function post<T = any>(
  { url, data, method = 'POST', headers, onDownloadProgress, signal, beforeRequest, afterRequest }: HttpOption,
): Promise<T> {
  return http<T>({
    url,
    method,
    data,
    headers,
    onDownloadProgress,
    signal,
    beforeRequest,
    afterRequest,
  })
}

export function put<T = any>({
  url,
  data,
  method = 'PUT',
  headers,
  onDownloadProgress,
  signal,
  beforeRequest,
  afterRequest,
}: HttpOption): Promise<T> {
  return http<T>({
    url,
    method,
    data,
    headers,
    onDownloadProgress,
    signal,
    beforeRequest,
    afterRequest,
  });
}

export function del<T = any>({
  url,
  data,
  method = 'DELETE',
  headers,
  onDownloadProgress,
  signal,
  beforeRequest,
  afterRequest,
}: HttpOption): Promise<T> {
  return http<T>({
    url,
    method,
    data,
    headers,
    onDownloadProgress,
    signal,
    beforeRequest,
    afterRequest,
  });
}

// Download function
export function download<T = any>({
  url,
  data,
  method = 'GET',
  headers,
  onDownloadProgress,
  signal,
  beforeRequest,
  afterRequest,
}: HttpOption): Promise<Blob> {
  return http<T>({
    url,
    method,
    data,
    headers,
    onDownloadProgress,
    signal,
    beforeRequest,
    afterRequest,
  }).then((response: any) => {
    const blob = new Blob([response], { type: 'application/octet-stream' });
    return Promise.resolve(blob);
  });
}

export default post
