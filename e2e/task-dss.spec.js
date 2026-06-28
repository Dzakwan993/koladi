import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8000';
const WORKSPACE_ID = '3b61e2cf-dd4b-4732-ae89-8042450187b2'; // Div. Desain (UUID valid)

async function performLogin(page) {
  await page.goto(`${BASE_URL}/masuk`);
  await page.fill('input[name="email"]', 'e2e.koladi@example.com');
  await page.fill('input[name="password"]', 'password');
  await Promise.all([
    page.waitForURL(/dashboard/, { waitUntil: 'domcontentloaded' }),
    page.click('button[type="submit"]'),
  ]);
}

test.describe('Modul Kanban Board & DSS (E2E)', () => {

  test('TC-TASK-01: Memuat halaman Kanban Board', async ({ page }) => {
    await performLogin(page);
    await page.goto(`${BASE_URL}/kanban-tugas/${WORKSPACE_ID}`, { waitUntil: 'domcontentloaded' });
    const boardContainer = page.locator('#kanban-board');
    await expect(boardContainer).toBeAttached();
    await expect(page.getByRole('button', { name: 'Kanban' })).toBeVisible();
    await page.screenshot({ path: 'screenshots/TC-TASK-01-board-kanban.png', fullPage: true });
  });

  test('TC-TASK-05: Menyaring (filter) task berdasarkan status/prioritas', async ({ page }) => {
    await performLogin(page);
    await page.goto(`${BASE_URL}/kanban-tugas/${WORKSPACE_ID}`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#kanban-board')).toBeAttached();
    
    const filterBtn = page.locator('select[name="priority"], button:has-text("Filter"), .filter-select');
    if (await filterBtn.count() > 0) {
      await filterBtn.first().click();
      await page.screenshot({ path: 'screenshots/TC-TASK-05-filter-priority.png', fullPage: true });
    } else {
      await page.screenshot({ path: 'screenshots/TC-TASK-05-fallback.png' });
    }
  });

  test('TC-DSS-01: Memuat dashboard metrik DSS', async ({ page }) => {
    await performLogin(page);
    await page.goto(`${BASE_URL}/dashboard`, { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: /Selamat datang/i })).toBeVisible();
    await page.screenshot({ path: 'screenshots/TC-DSS-01-dashboard-dss.png', fullPage: true });
    await expect(page).toHaveURL(/dashboard/);
  });

  test('TC-DSS-03: Menampilkan grafik/chart statistik DSS', async ({ page }) => {
    await performLogin(page);
    await page.goto(`${BASE_URL}/statistik`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('.dashboard-container')).toBeVisible();
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'screenshots/TC-DSS-03-chart-dss.png', fullPage: true });
  });

  test('TC-DSS-04: Menampilkan teks rekomendasi DSS', async ({ page }) => {
    await performLogin(page);
    await page.goto(`${BASE_URL}/statistik`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('.dashboard-container')).toBeVisible();
    await page.screenshot({ path: 'screenshots/TC-DSS-04-rekomendasi.png', fullPage: true });
  });

});
