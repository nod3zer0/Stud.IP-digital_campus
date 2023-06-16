import { test, expect } from '@playwright/test';

const dozentFile = 'tests/e2e/.auth/dozent.json';

test.describe('Logging Out', () => {
    test.use({ storageState: dozentFile });

    test('should take us back to the homepage', async ({ page, baseURL }) => {
        await page.goto(baseURL);
        await expect(page.locator('#avatar-menu-container')).toBeVisible();
        await page.getByTitle('Testaccount Dozent').click();
        await page.getByRole('link', { name: 'Logout' }).click();
        await expect(page).toHaveURL(/index\.php.*logout=true/);
    });
});
