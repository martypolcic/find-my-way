/**
 * Retrieves a cookie value by name
 * @param name Cookie name
 * @returns Cookie value or empty string if not found
 */
export function getCookie(name: string): string {
    if (typeof document === 'undefined') return '';
    
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop()?.split(';').shift() || '';
    return '';
  }
  
  /**
   * Sets a cookie
   * @param name Cookie name
   * @param value Cookie value
   * @param days Expiration in days
   */
  export function setCookie(name: string, value: string, days: number = 365): void {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
  }
  
  /**
   * Deletes a cookie
   * @param name Cookie name to delete
   */
  export function deleteCookie(name: string): void {
    document.cookie = `${name}=; Max-Age=-99999999; path=/`;
  }