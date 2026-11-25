# WooPoly

WooPoly connects WooCommerce products, orders, and store pages with Polylang translations. Plugin code lives in `src/Hyyan/WPI/`, automated tests in `tests/`, and the user guide in `docs/`.

## Commands

Run from the repository root. Install Node dependencies with `npm ci`. See the [contribution guide](.github/CONTRIBUTING.md) for prerequisites and store setup.

| Change | Check |
| --- | --- |
| PHP | Run `.github/scripts/check-php-style` for staged PHP. It reports newly introduced WordPress Coding Standards violations. |
| Plugin behavior | In the disposable test environment, run `tests/bin/setup.sh`, then `tests/bin/run.sh`. Both commands write store data. Verify the database and HTTP endpoint are separate from the manual review store first. |
| JavaScript or CSS assets | Rebuild with `npm run build:assets`. Include the generated minified files. Run `npm run check:assets` and `npm run test:js`. |
| User documentation | Run `npm run docs:build` and `npm run docs:check`. Include the generated guide and verify it in the installed plugin. |

## Repository conventions

- Register Features settings in `src/Hyyan/WPI/Admin/Features.php`. Follow a neighboring field through the settings API and reuse its form, save action, and permissions.
- For credential settings, verify save, replacement, removal, and retention when another field changes. Check that the rendered form keeps the stored secret hidden.
- Preserve public callback names and inherited signatures. Document approved compatibility exceptions at the affected declaration. Document new actions and filters in `src/Hyyan/WPI/HooksInterface.php`.
- For storefront bugs, reproduce the reported URL with the same visitor role. Record the matching rule before refreshing permalinks. For duplicate navigation, inspect the menu source and linked pages. Clean up test-owned data.
- Update the user guide when behavior changes. Use the labels and permissions verified in the running store.

## Review

Use Conventional Commits for commit messages and PR titles. Follow the [PR template](.github/PULL_REQUEST_TEMPLATE.md) and report checks against the reviewed revision. During a review hold, keep changes unstaged pending explicit approval. Confirm authorization before committing, rebasing, pushing, publishing, or commenting on an issue. Check the diff and release archive for credentials and private material before sharing.
