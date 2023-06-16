import { test, expect } from '@playwright/test';

const rootFile = 'tests/e2e/.auth/root.json';

test.describe('Visiting the plugin administration @root @plugins', () => {
    test.use({ storageState: rootFile });

    test('should let us deactivate and re-activate plugins', async ({ page }) => {
        await page.goto('dispatch.php/admin/plugin');
        const tableRow = await page.getByRole('row', { name: /TerminWidget/i });
        const input = await tableRow.locator('input[name*=enabled]');
        const saveButton = await page.getByRole('button', { name: 'Speichern' });

        await input.uncheck();
        await saveButton.click();
        await expect(page.getByText('Plugin "TerminWidget" wurde deaktiviert')).toBeVisible();

        await input.check();
        await saveButton.click();
        await expect(page.getByText('Plugin "TerminWidget" wurde aktiviert')).toBeVisible();
    });
});
