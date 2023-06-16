import { test, expect } from '@playwright/test';

const autorFile = 'tests/e2e/.auth/autor.json';

test.describe('Visiting my Arbeitsplatz @autor @courseware', () => {
    test.use({ storageState: autorFile });

    test('should let us create a new Lernmaterial', async ({ page }) => {
        await page.goto('dispatch.php/start');
        await page.getByRole('link', { name: /Courseware Erstellen/ }).click();
        await page.getByRole('button', { name: 'Lernmaterial hinzufügen' }).click();
        await page.getByLabel('Titel des Lernmaterials*').click();
        await page.getByLabel('Titel des Lernmaterials*').fill('Ein Titel');
        await page.getByLabel('Titel des Lernmaterials*').press('Tab');
        await page.getByLabel('Beschreibung*').fill('Eine Beschreibung');
        await page.getByRole('tab', { name: 'Erscheinung' }).click();
        await page
            .getByRole('combobox', { name: 'Search for option' })
            .locator('div')
            .filter({ hasText: 'Blau' })
            .click();
        await page.getByRole('button', { name: 'Erstellen' }).click();
        const lernmaterial = await page.getByRole('link', { name: 'Ein Titel Eine Beschreibung' }).last();
        await expect(lernmaterial).toBeVisible();
    });
});
