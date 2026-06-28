import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8000';
const USER_EMAIL = 'e2e.koladi@example.com';
const USER_PASSWORD = 'password';

test.describe('Modul Authentication (E2E)', () => {

  test('TC-AUTH-01: Login dengan email dan password yang valid', async ({ page }) => {
    await page.goto(`${BASE_URL}/masuk`);
    await page.fill('input[name="email"]', USER_EMAIL);
    await page.fill('input[name="password"]', USER_PASSWORD);
    await Promise.all([
      page.waitForURL(/dashboard/, { waitUntil: 'domcontentloaded' }),
      page.click('button[type="submit"]'),
    ]);
    await expect(page).toHaveURL(/dashboard/);
    await page.screenshot({ path: 'screenshots/TC-AUTH-01-login-sukses.png', fullPage: true });
  });

  test('TC-AUTH-02: Login dengan password yang salah', async ({ page }) => {
    await page.goto(`${BASE_URL}/masuk`);
    await page.fill('input[name="email"]', USER_EMAIL);
    await page.fill('input[name="password"]', 'PasswordSalah123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/masuk/);
    await page.screenshot({ path: 'screenshots/TC-AUTH-02-login-gagal.png', fullPage: true });
  });

  test('TC-AUTH-03: Akses halaman /dashboard tanpa login', async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard`, { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/masuk/);
    await page.screenshot({ path: 'screenshots/TC-AUTH-03-redirect-auth.png', fullPage: true });
  });

  test('TC-AUTH-04: Register akun baru dengan data lengkap', async ({ page }) => {
    await page.goto(`${BASE_URL}/daftar`);
    await page.fill('input[name="full_name"]', 'Tester Koladi');
    const randomEmail = `tester.${Date.now()}@koladi.com`;
    await page.fill('input[name="email"]', randomEmail);
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(3000);
    await page.screenshot({ path: 'screenshots/TC-AUTH-04-daftar-sukses.png', fullPage: true });
  });

  test('TC-AUTH-05: Logout dari sistem', async ({ page }) => {
    await page.goto(`${BASE_URL}/masuk`);
    await page.fill('input[name="email"]', USER_EMAIL);
    await page.fill('input[name="password"]', USER_PASSWORD);
    await Promise.all([
      page.waitForURL(/dashboard/, { waitUntil: 'domcontentloaded' }),
      page.click('button[type="submit"]'),
    ]);

    await Promise.all([
      page.waitForURL(/masuk/, { waitUntil: 'domcontentloaded' }),
      page.locator('form[action*="keluar"]').evaluate((form) => form.requestSubmit()),
    ]);
    await expect(page).toHaveURL(/masuk/);
    await page.screenshot({ path: 'screenshots/TC-AUTH-05-logout-sukses.png', fullPage: true });
  });

});
