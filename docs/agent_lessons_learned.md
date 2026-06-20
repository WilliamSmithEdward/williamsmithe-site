# Agent Lessons Learned - Portfolio Site Implementation

This document records the technical challenges and solutions encountered during the implementation and production transition of the contact form.

## 1. Production Environment Configuration
- **Environment Variable Loading**: In Symfony `prod` mode, `.env` files are not always loaded dynamically. Running `composer dump-env prod` is essential to compile these into `.env.local.php` for performance and reliability.
- **Cache Management**: Any change to `.env` or configuration files requires `php bin/console cache:clear --env=prod` to take effect.

## 2. Symfony Mailer & Messenger Integration
- **Mailer Bridges**: Using a third-party mailer (e.g., Brevo) requires the specific bridge package (e.g., `symfony/brevo-mailer`).
- **API vs SMTP**: The `MAILER_DSN` format determines the transport. `brevo+api://` uses the REST API, while `brevo+smtp://` uses the SMTP protocol.
- **Messenger Overhead**: By default, Symfony Mailer routes emails through the Messenger bus. In production, this often defaults to `async` transport using a database table (`messenger_messages`).
- **Removing Database Dependency**: To run a purely API-driven mailer without a database:
    - Set `MESSENGER_TRANSPORT_DSN=sync://`.
    - Update `messenger.yaml` to route `SendEmailMessage` to the `sync` transport.
    - Change `failure_transport` to `sync` to avoid `doctrine://` requirements.

## 3. Handling Framework Dependencies
- **Dummy DSNs**: The `doctrine-bundle` may throw an `Environment variable not found: "DATABASE_URL"` error during container compilation even if the database isn't used. Providing a dummy DSN like `DATABASE_URL="sqlite:////dev/null"` satisfies the requirement without creating a file.

## 4. External API Constraints
- **IP Authorization**: Valid API keys are not always sufficient. Some providers (like Brevo) require IP whitelisting in their security settings, which can result in `401 Unauthorized` errors if the server IP is not registered.

## 5. Debugging Strategies
- **Foreground Server**: When encountering generic `500 Internal Server Error` pages in `prod` mode, running the PHP development server in the foreground (`php -S localhost:8000 -t public`) allows for the capture of real-time uncaught exceptions and critical logs.
