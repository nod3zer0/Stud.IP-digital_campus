import { test as setup, expect } from '@playwright/test';
import { credentials } from './credentials.ts';

const rootFile = 'tests/e2e/.auth/root.json';
const adminFile = 'tests/e2e/.auth/admin.json';
const dozentFile = 'tests/e2e/.auth/dozent.json';
const tutorFile = 'tests/e2e/.auth/tutor.json';
const autorFile = 'tests/e2e/.auth/autor.json';

setup('authenticate as root', async ({ page }) => {
    await login(page, credentials.root);
    await page.context().storageState({ path: rootFile });
});

setup('authenticate as admin', async ({ page }) => {
    await login(page, credentials.admin);
    await page.context().storageState({ path: adminFile });
});

setup('authenticate as dozent', async ({ page }) => {
    await login(page, credentials.dozent);
    await page.context().storageState({ path: dozentFile });
});

setup('authenticate as tutor', async ({ page }) => {
    await login(page, credentials.tutor);
    await page.context().storageState({ path: tutorFile });
});

setup('authenticate as autor', async ({ page }) => {
    await login(page, credentials.autor);
    await page.context().storageState({ path: autorFile });
});

async function login(page, { username, password }) {
    await page.goto('index.php?again=yes');

    await page.getByLabel(/Benutzername/i).fill(username);
    await page.getByLabel(/Passwort/i).fill(password);
    await page.getByRole('button', { name: 'Anmelden' }).click();
    await expect(page.locator('#avatar-menu-container')).toBeVisible();
}
