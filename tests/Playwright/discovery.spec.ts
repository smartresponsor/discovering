import { expect, test } from '@playwright/test';

test('discovery surface renders through the canonical interface shell', async ({ page }) => {
  const response = await page.goto('/discovery');

  expect(response?.ok()).toBeTruthy();
  await expect(page.locator('[data-interfacing-shell="host-wide"]')).toBeVisible();
  await expect(page.locator('h1')).toContainText('Discovering');
});
