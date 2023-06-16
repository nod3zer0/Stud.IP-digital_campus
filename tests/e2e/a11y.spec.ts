import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('Should not have any automatically detectable accessibility issues @a11y', () => {
    test.describe('while not logged in', () => {
        const pages = [
            ['on homepage', 'index.php?cancel_login=1'],
            ['on login page', 'index.php?again=yes'],
            ['on password forgotten page', 'dispatch.php/new_password?cancel_login=1'],
        ];

        pages.forEach(async ([title, url]) =>
            test(title, async ({ page, baseURL }) => {
                await page.goto(url);
                const accessibilityScanResults = await analyze(page);
                expect(accessibilityScanResults.violations).toEqual([]);
            })
        );
    });

    test.describe('while logged in as author', () => {
        const autorFile = 'tests/e2e/.auth/autor.json';
        test.use({ storageState: autorFile });

        const pages = [
            ['on start page', 'dispatch.php/start'],
            ['on profile page', 'dispatch.php/profile/index'],
            ['on my courses page', 'dispatch.php/courses'],
        ];

        pages.forEach(async ([title, url]) =>
            test(title, async ({ page, baseURL }) => {
                await page.goto(url);
                const accessibilityScanResults = await analyze(page);
                expect(accessibilityScanResults.violations).toEqual([]);
            })
        );
    });
});

function analyze(page) {
    return new AxeBuilder({ page }).analyze();
}
