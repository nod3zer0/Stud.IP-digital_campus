<?php

/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote
 */
$metadata['https://saml.example.com/entityid'] = [
    'entityid' => 'https://saml.example.com/entityid',
    'contacts' => [],
    'metadata-set' => 'saml20-idp-remote',
    'sign.authnrequest' => true,
    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://mocksaml.com/api/saml/sso',
        ],
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'https://mocksaml.com/api/saml/sso',
        ],
    ],
    'SingleLogoutService' => [],
    'ArtifactResolutionService' => [],
    'NameIDFormats' => [
        'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
    ],
    'keys' => [
        [
            'encryption' => false,
            'signing' => true,
            'type' => 'X509Certificate',
            'X509Certificate' => 'MIIC4jCCAcoCCQC33wnybT5QZDANBgkqhkiG9w0BAQsFADAyMQswCQYDVQQGEwJVSzEPMA0GA1UECgwGQm94eUhRMRIwEAYDVQQDDAlNb2NrIFNBTUwwIBcNMjIwMjI4MjE0NjM4WhgPMzAyMTA3MDEyMTQ2MzhaMDIxCzAJBgNVBAYTAlVLMQ8wDQYDVQQKDAZCb3h5SFExEjAQBgNVBAMMCU1vY2sgU0FNTDCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALGfYettMsct1T6tVUwTudNJH5Pnb9GGnkXi9Zw/e6x45DD0RuRONbFlJ2T4RjAE/uG+AjXxXQ8o2SZfb9+GgmCHuTJFNgHoZ1nFVXCmb/Hg8Hpd4vOAGXndixaReOiq3EH5XvpMjMkJ3+8+9VYMzMZOjkgQtAqO36eAFFfNKX7dTj3VpwLkvz6/KFCq8OAwY+AUi4eZm5J57D31GzjHwfjH9WTeX0MyndmnNB1qV75qQR3b2/W5sGHRv+9AarggJkF+ptUkXoLtVA51wcfYm6hILptpde5FQC8RWY1YrswBWAEZNfyrR4JeSweElNHg4NVOs4TwGjOPwWGqzTfgTlECAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAAYRlYflSXAWoZpFfwNiCQVE5d9zZ0DPzNdWhAybXcTyMf0z5mDf6FWBW5Gyoi9u3EMEDnzLcJNkwJAAc39Apa4I2/tml+Jy29dk8bTyX6m93ngmCgdLh5Za4khuU3AM3L63g7VexCuO7kwkjh/+LqdcIXsVGO6XDfu2QOs1Xpe9zIzLpwm/RNYeXUjbSj5ce/jekpAw7qyVVL4xOyh8AtUW1ek3wIw1MJvEgEPt0d16oshWJpoS1OT8Lr/22SvYEo3EmSGdTVGgk3x3s+A0qWAqTcyjr7Q4s/GKYRFfomGwz0TZ4Iw1ZN99Mm0eo2USlSRTVl7QHRTuiuSThHpLKQQ==',
        ],
    ],
];
$metadata['https://app.fit.vut.cz/simplesaml/saml2/idp/'] = [
    'entityid' => 'https://app.fit.vut.cz/simplesaml/saml2/idp/',
    'description' => [
        'en' => 'Faculty of information technology BUT',
        'cs' => 'Fakulta informačních technologií VUT',
    ],
    'OrganizationName' => [
        'en' => 'Faculty of information technology BUT',
        'cs' => 'Fakulta informačních technologií VUT',
    ],
    'name' => [
        'en' => 'Faculty of information technology BUT',
        'cs' => 'Fakulta informačních technologií VUT',
    ],
    'OrganizationDisplayName' => [
        'en' => 'Faculty of information technology BUT',
        'cs' => 'Fakulta informačních technologií VUT',
    ],
    'url' => [
        'en' => 'https://www.fit.vut.cz/.en',
        'cs' => 'https://www.fit.vut.cz/.cs',
    ],
    'OrganizationURL' => [
        'en' => 'https://www.fit.vut.cz/.en',
        'cs' => 'https://www.fit.vut.cz/.cs',
    ],
    'contacts' => [
        [
            'contactType' => 'support',
            'company' => 'CVT FIT VUT',
            'givenName' => 'Marek',
            'surName' => 'Kuchynka',
            'emailAddress' => [
                'kuchynkam@fit.vut.cz',
            ],
            'telephoneNumber' => [
                '+420 54114 1234',
            ],
        ],
        [
            'contactType' => 'technical',
            'givenName' => 'Ing. Marek Kuchynka',
            'emailAddress' => [
                'kuchynkam@fit.vut.cz',
            ],
        ],
    ],
    'metadata-set' => 'saml20-idp-remote',
    'sign.authnrequest' => true,
    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://app.fit.vut.cz/simplesaml/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://app.fit.vut.cz/simplesaml/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'ArtifactResolutionService' => [],
    'NameIDFormats' => [
        'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
    ],
    'keys' => [
        [
            'encryption' => false,
            'signing' => true,
            'type' => 'X509Certificate',
            'X509Certificate' => 'MIIE5TCCA02gAwIBAgIUJ/Qvnw7OoMC7qfT1FaDC19vrRs0wDQYJKoZIhvcNAQELBQAwgYExCzAJBgNVBAYTAkNaMRMwEQYDVQQIDApTb21lLVN0YXRlMQ0wCwYDVQQHDARCcm5vMRAwDgYDVQQKDAdGSVQgVlVUMRcwFQYDVQQDDA5hcHAuZml0LnZ1dC5jejEjMCEGCSqGSIb3DQEJARYUa3VjaHlua2FtQGZpdC52dXQuY3owHhcNMjIwMzE1MTQyMDE1WhcNMzIwMzE0MTQyMDE1WjCBgTELMAkGA1UEBhMCQ1oxEzARBgNVBAgMClNvbWUtU3RhdGUxDTALBgNVBAcMBEJybm8xEDAOBgNVBAoMB0ZJVCBWVVQxFzAVBgNVBAMMDmFwcC5maXQudnV0LmN6MSMwIQYJKoZIhvcNAQkBFhRrdWNoeW5rYW1AZml0LnZ1dC5jejCCAaIwDQYJKoZIhvcNAQEBBQADggGPADCCAYoCggGBAK8zSYHzm5Myx5iw8PjfHmj6wThFm4k0eq42VefchKzNvpjOLFksF8QdMvTxughRSkp3+22wImFpvCZuWizejpAXSf2BAtUL4+lpLDSnXGg5tOFR4hRWsuIIrQfd82cwG6CN2v+3H4shqr8lf4zoARMr2jUgNjRyGfsrqqpN+9LabDtWrtsarPvFOyOwQKyUuht+NVZId25ufHw4oRRVqYRWvke+e334o1uvVx461LOCGWZ7T035y/pXgsllbWcoXIcYwrS/h4irWKQLFeg/KHHkjB1kOUR+dWHSqnkhXYCpCvVNNt1lW6ZDil0tXeE5Aga96OKEiDGfqf60twdkm3fhS+ruA5bW+M/IHz/MLi7bcUkDtJ1u1penTkLf17asuHLPHJOnLxJ8q97nzaErbnvRGFzfnvR3Da/4uYJkW6Y+T5YyMDaAehYVedlXBx/sq26wRFCkz6RaFJ2CcZKLAMZN3bMo8nV5+zHNiCI42Esb2xffid0YYbvbJRMbtKRqgwIDAQABo1MwUTAdBgNVHQ4EFgQU6JZ6/y6Mp7x0WODW2K8gKg5PwZswHwYDVR0jBBgwFoAU6JZ6/y6Mp7x0WODW2K8gKg5PwZswDwYDVR0TAQH/BAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAYEAoB2AyKkgNbNehYecbbuaTvAKUoJ/6PZvKW0Kbl3igbLMc984IeuzAXhtuegv3jPbVz5uqRjPkBCOxMVmS3YzskssOnyLW8INxQmBeWwnUeqynTwZzQc7l7OwEfUAGTkBLN1Lew/BX0H7zibgFNOqaFiW+7CgRYLzWLTZ6MId5xQ9ee7MQJHLiN14HTyVMzl80Iu6Ej5MjaUFcpygSXrkpA4t7D8ytBU9LhjdIlAJahdKHNjc+BMolglh1WefRzkAIBRTiZPiO3WfEkGQbnL3lpjRreikyWCJOX5LsYR89nxj+u/LNalY5bDYV8s5Y/WGFUoehyfU+1tUXT5A6MW9kvmQ8YXFTZUBnG7K6oYRN6U4jvObPYmk/J1jrM3+dJHhT+nX1yuKzQDRjbl6GXhTNyHBENPzZVlwElQlOBd7tI/UhGJu3uXNqqoFKwiR2lx0Ox6wltPv4ZWW6OWF2Azho7rcUjXIfsfFhQlrK/P+1BB/wTLf0PDRg+Te/F6U9lo3',
        ],
        [
            'encryption' => true,
            'signing' => false,
            'type' => 'X509Certificate',
            'X509Certificate' => 'MIIE5TCCA02gAwIBAgIUJ/Qvnw7OoMC7qfT1FaDC19vrRs0wDQYJKoZIhvcNAQELBQAwgYExCzAJBgNVBAYTAkNaMRMwEQYDVQQIDApTb21lLVN0YXRlMQ0wCwYDVQQHDARCcm5vMRAwDgYDVQQKDAdGSVQgVlVUMRcwFQYDVQQDDA5hcHAuZml0LnZ1dC5jejEjMCEGCSqGSIb3DQEJARYUa3VjaHlua2FtQGZpdC52dXQuY3owHhcNMjIwMzE1MTQyMDE1WhcNMzIwMzE0MTQyMDE1WjCBgTELMAkGA1UEBhMCQ1oxEzARBgNVBAgMClNvbWUtU3RhdGUxDTALBgNVBAcMBEJybm8xEDAOBgNVBAoMB0ZJVCBWVVQxFzAVBgNVBAMMDmFwcC5maXQudnV0LmN6MSMwIQYJKoZIhvcNAQkBFhRrdWNoeW5rYW1AZml0LnZ1dC5jejCCAaIwDQYJKoZIhvcNAQEBBQADggGPADCCAYoCggGBAK8zSYHzm5Myx5iw8PjfHmj6wThFm4k0eq42VefchKzNvpjOLFksF8QdMvTxughRSkp3+22wImFpvCZuWizejpAXSf2BAtUL4+lpLDSnXGg5tOFR4hRWsuIIrQfd82cwG6CN2v+3H4shqr8lf4zoARMr2jUgNjRyGfsrqqpN+9LabDtWrtsarPvFOyOwQKyUuht+NVZId25ufHw4oRRVqYRWvke+e334o1uvVx461LOCGWZ7T035y/pXgsllbWcoXIcYwrS/h4irWKQLFeg/KHHkjB1kOUR+dWHSqnkhXYCpCvVNNt1lW6ZDil0tXeE5Aga96OKEiDGfqf60twdkm3fhS+ruA5bW+M/IHz/MLi7bcUkDtJ1u1penTkLf17asuHLPHJOnLxJ8q97nzaErbnvRGFzfnvR3Da/4uYJkW6Y+T5YyMDaAehYVedlXBx/sq26wRFCkz6RaFJ2CcZKLAMZN3bMo8nV5+zHNiCI42Esb2xffid0YYbvbJRMbtKRqgwIDAQABo1MwUTAdBgNVHQ4EFgQU6JZ6/y6Mp7x0WODW2K8gKg5PwZswHwYDVR0jBBgwFoAU6JZ6/y6Mp7x0WODW2K8gKg5PwZswDwYDVR0TAQH/BAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAYEAoB2AyKkgNbNehYecbbuaTvAKUoJ/6PZvKW0Kbl3igbLMc984IeuzAXhtuegv3jPbVz5uqRjPkBCOxMVmS3YzskssOnyLW8INxQmBeWwnUeqynTwZzQc7l7OwEfUAGTkBLN1Lew/BX0H7zibgFNOqaFiW+7CgRYLzWLTZ6MId5xQ9ee7MQJHLiN14HTyVMzl80Iu6Ej5MjaUFcpygSXrkpA4t7D8ytBU9LhjdIlAJahdKHNjc+BMolglh1WefRzkAIBRTiZPiO3WfEkGQbnL3lpjRreikyWCJOX5LsYR89nxj+u/LNalY5bDYV8s5Y/WGFUoehyfU+1tUXT5A6MW9kvmQ8YXFTZUBnG7K6oYRN6U4jvObPYmk/J1jrM3+dJHhT+nX1yuKzQDRjbl6GXhTNyHBENPzZVlwElQlOBd7tI/UhGJu3uXNqqoFKwiR2lx0Ox6wltPv4ZWW6OWF2Azho7rcUjXIfsfFhQlrK/P+1BB/wTLf0PDRg+Te/F6U9lo3',
        ],
    ],
];
