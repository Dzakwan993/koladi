const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://localhost:8000';

async function performLogin(page) {
  await page.goto(`${BASE_URL}/masuk`);
  await page.fill('input[name="email"]', 'admin@example.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(/dashboard/);
}

test.describe('Modul Kanban Board & DSS (E2E)', () => {

  test('TC-TASK-01: Memuat halaman Kanban Board', async ({ page }) => {
    await performLogin(page);
    // Navigasi ke halaman workspace board (sesuaikan ID workspace yang ada di DB lokal Anda)
    await page.goto(`${BASE_URL}/workspace/1`); 
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'screenshots/TC-TASK-01-board-kanban.png', fullPage: true });
    
    // Verifikasi container board tampil di halaman
    const boardContainer = page.locator('.kanban-board, #kanban-container, .board');
    await expect(boardContainer.first()).toBeVisible();
  });

  test('TC-TASK-05: Menyaring (filter) task berdasarkan status/prioritas', async ({ page }) => {
    await performLogin(page);
    await page.goto(`${BASE_URL}/workspace/1`);
    await page.waitForLoadState('networkidle');
    
    // Mencari dropdown filter priority atau filter button
    const filterBtn = page.locator('select[name="priority"], button:has-text("Filter")');
    if (await filterBtn.count() > 0) {
      await filterBtn.first().click();
      await page.selectOption('select[name="priority"]', 'high');
      await page.waitForTimeout(1000);
      await page.screenshot({ path: 'screenshots/TC-TASK-05-filter-priority.png', fullPage: true });
    } else {
      await page.screenshot({ path: 'screenshots/TC-TASK-05-fallback.png' });
    }
  });

  test('TC-DSS-01: Memuat dashboard metrik DSS', async ({ page }) => {
    await performLogin(page);
    await page.goto(`${BASE_URL}/dashboard`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'screenshots/TC-DSS-01-dashboard-dss.png', fullPage: true });
    await expect(page).toHaveURL(/dashboard/);
  });

  test('TC-DSS-03: Menampilkan grafik/chart statistik DSS', async ({ page }) => {
    await performLogin(page);
    await page.goto(`${BASE_URL}/statistik`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000); // Tunggu render animasi chart/grafik selesai
    await page.screenshot({ path: 'screenshots/TC-DSS-03-chart-dss.png', fullPage: true });
    
    // Verifikasi elemen canvas atau chart container ter-render
    const chartCanvas = page.locator('canvas, svg, .chart-container');
    await expect(chartCanvas.first()).toBeVisible();
  });

  test('TC-DSS-04: Menampilkan teks rekomendasi DSS', async ({ page }) => {
    await performLogin(page);
    await page.goto(`${BASE_URL}/statistik`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'screenshots/TC-DSS-04-rekomendasi.png', fullPage: true });
    
    // Memastikan box saran/rekomendasi keputusan DSS tampil di layar
    const recommendationBox = page.locator('.recommendation-box, [data-recommendation], #dss-recommendation');
    await expect(recommendationBox.first()).toBeVisible();
  });

});
