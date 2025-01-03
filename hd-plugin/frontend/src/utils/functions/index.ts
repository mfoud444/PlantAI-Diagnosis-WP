export function camelToSnake(obj: Record<string, any>): Record<string, any> {
  const snakeObj: Record<string, any> = {};

  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      const snakeKey = key.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);
      snakeObj[snakeKey] = obj[key];
    }
  }

  return snakeObj;
}
export  function snakeToCamel<T extends Record<string, any>>(obj: T): T {
  const camelObj: Record<string, any> = {};

  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      const camelKey = key.replace(/_([a-z])/g, (_, match) => match.toUpperCase());
      camelObj[camelKey] = obj[key];
    }
  }

  return camelObj as T;
}

export function getCurrentDate() {
  const date = new Date()
  const day = date.getDate()
  const month = date.getMonth() + 1
  const year = date.getFullYear()
  return `${year}-${month}-${day}`
}

export function extractUrlParams(url: string): Record<string, string> {
  const params = {};
  const urlParams = new URLSearchParams(url);
  urlParams.forEach((value, key) => {
    params[key] = value;
  });
  return params;
}

export function isEmailValid(email: string): boolean {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}