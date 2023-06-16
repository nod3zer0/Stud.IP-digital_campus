import { test, expect } from '@playwright/test';
import { credentials } from './credentials.ts';

test.describe('Loggin In - HTML Web Form @auth', () => {
    test.describe('Coming from homepage', () => {
        test('should take us to the login form @smoke', async ({ page, baseURL }) => {
            await page.goto('');

            await expect(page.locator('#loginbox')).toBeVisible();

            const loginLink = page.getByRole('link', { name: 'Login für registrierte NutzerInnen' });
            await expect(loginLink).toBeVisible();
            await loginLink.click();

            const benutzername = page.getByLabel(/Benutzername/i);
            await expect(benutzername).toBeVisible();
            await expect(benutzername).toBeEditable();

            const passwort = page.getByLabel(/Passwort/i);
            await expect(passwort).toBeVisible();
            await expect(passwort).toBeEditable();
        });
    });

    test.describe('Unauthorized', () => {
        test('redirects to the login form @smoke', async ({ page }) => {
            await page.goto('dispatch.php/start');

            await expect(page.getByLabel(/Passwort/)).toBeVisible();
            await expect(page.locator('#avatar-menu-container')).not.toBeVisible();
        });
    });

    test.describe('HTML Form submission', () => {
        test.beforeEach(async ({ page }) => {
            await page.goto('index.php?again=yes');
        });

        test('displays error on invalid login', async ({ page }) => {
            const benutzername = page.getByLabel(/Benutzername/i);
            const passwort = page.getByLabel(/Passwort/i);
            const submit = page.getByRole('button', { name: 'Anmelden' });

            await benutzername.fill('username');
            await passwort.fill('password');
            await submit.click();
            await expect(page.locator('css=.messagebox_error')).toBeVisible();
        });

        test('redirects to start page', async ({ page, baseURL }) => {
            const benutzername = page.getByLabel(/Benutzername/i);
            const passwort = page.getByLabel(/Passwort/i);
            const submit = page.getByRole('button', { name: 'Anmelden' });

            await benutzername.fill(credentials.autor.username);
            await passwort.fill(credentials.autor.password);
            await submit.click();
            await expect(page.locator('#avatar-menu-container')).toBeVisible();
            await expect(page).toHaveURL(`${baseURL}dispatch.php/start`);
        });
    });
});
