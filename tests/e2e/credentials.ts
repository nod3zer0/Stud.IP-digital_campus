export const credentials = {
    root: {
        username: process.env.PLAYWRIGHT_CREDENTIALS_ROOT_USERNAME ?? 'root@studip',
        password: process.env.PLAYWRIGHT_CREDENTIALS_ROOT_PASSWORD ?? 'testing',
    },
    admin: {
        username: process.env.PLAYWRIGHT_CREDENTIALS_ADMIN_USERNAME ?? 'test_admin',
        password: process.env.PLAYWRIGHT_CREDENTIALS_ADMIN_PASSWORD ?? 'testing',
    },
    dozent: {
        username: process.env.PLAYWRIGHT_CREDENTIALS_DOZENT_USERNAME ?? 'test_dozent',
        password: process.env.PLAYWRIGHT_CREDENTIALS_DOZENT_PASSWORD ?? 'testing',
    },
    tutor: {
        username: process.env.PLAYWRIGHT_CREDENTIALS_TUTOR_USERNAME ?? 'test_tutor',
        password: process.env.PLAYWRIGHT_CREDENTIALS_TUTOR_PASSWORD ?? 'testing',
    },
    autor: {
        username: process.env.PLAYWRIGHT_CREDENTIALS_AUTOR_USERNAME ?? 'test_autor',
        password: process.env.PLAYWRIGHT_CREDENTIALS_AUTOR_PASSWORD ?? 'testing',
    },
};
