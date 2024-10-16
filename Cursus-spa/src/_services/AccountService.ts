import Axios from './CallerService';
import { useUserStore } from '@/stores/User';

export async function login(credentials: { email: string, password: string }): Promise<void> {
  const res = await Axios.post('/authenticate', credentials, {
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    baseURL: 'http://localhost:8000'
  });

  const userStore = useUserStore();
  userStore.setUser({
    id: res.data.user.id,
    email: res.data.user.email,
    role: res.data.user.role
  });
}

export function logout() {
  const userStore = useUserStore();
  userStore.clearUser();
  
  const cookies = document.cookie.split(";");
  for (let i = 0; i < cookies.length; i++) {
    const cookie = cookies[i];
    const eqPos = cookie.indexOf('=');
    const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
  }
}

export function isLogged(): boolean {
  const userStore = useUserStore();
  return userStore.isLogged;
}