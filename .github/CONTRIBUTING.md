Thanks for contributing&mdash;you rock!

# Getting Started

* Make sure you have a [GitHub account](https://github.com/signup/free).
* See if your issue has been discussed (or even fixed) earlier. You can [search for existing issues](../../../issues?q=is%3Aissue).
* Assuming it does not already exist, [create a new issue](../../../issues/new).
  * Clearly describe the issue. In case you want to report a bug, include steps to reproduce it.

* Fork the repository on GitHub.

# Making Changes

* Create a topic branch from where you want to base your work.
  * This is usually the `main` branch.
  * Only target release branches if you are certain your fix must be on that branch.
  * To quickly create a topic branch based on the `main` branch:
    * `git checkout -b issue/%YOUR-ISSUE-NUMBER%_%DESCRIPTIVE-TITLE% main`
    * a good example is `issue/123_typo_in_readme`
* Make commits of logical units.
* Make sure your commit messages are helpful.

# PHP formatting and commit messages

Use the WordPress Coding Standards for first-party PHP. Install the pinned development tools with `composer install --working-dir=.github/php-style`. The hook checks staged changes with `.github/scripts/check-php-style`. CI uses the same check against the base revision. New files receive the full check. Existing files are checked for newly introduced violations.

For deliberate formatting cleanup, run `.github/php-style/vendor/bin/phpcbf --standard=phpcs.xml.dist <file>` and review the diff. Preserve public callback names, inherited signatures, and existing behavior. The tools are isolated from the plugin's runtime dependencies.

Install the JavaScript development dependencies with `npm ci` and enable the repository hooks with `npm run hooks:install`. The pre-commit hook checks staged PHP and applicable assets. The commit-msg hook checks the message with commitlint. CI also checks the PR title and commit messages against Conventional Commits.

# JavaScript and CSS Assets

Use Node.js 24.15 or newer. Install the pinned development dependencies and enable the local asset check once per clone:

```sh
npm ci
npm run hooks:install
```

After editing `public/js/Cart.js`, `public/js/Variables.js`, or `public/css/setup.css`, run `npm run build:assets` and stage both the source and its `.min.js` or `.min.css` file. JavaScript uses Terser; CSS uses esbuild. WordPress loads the readable sources when `SCRIPT_DEBUG` is enabled and minified files otherwise.

Run `npm run check:assets` to check all generated files, and `npm run test:js` for asset build and JavaScript regressions. The older `build:js` and `check:js` commands remain available as aliases.

The pre-commit hook checks the staged versions and blocks stale minified assets. It never rewrites files or stages changes. Hooks must be enabled separately in each clone; contributors with an existing hooks setup can call `node .github/scripts/build-assets.cjs --check --staged` from their own hook. CI runs the same source-to-output check for JavaScript and CSS.

# Documentation

The user guide lives in `docs/` as Markdown. To preview changes locally:

```sh
npm ci
npm run docs:dev
```

Run `npm run docs:build` to check the production build and internal links. GitHub Actions builds documentation changes on pull requests and publishes updates from `main` to GitHub Pages.

# Running tests

Run these commands from the repository root. Install Docker with Compose v2, Bash, and Node.js 24.15 or newer. Docker must be running. WordPress, WooCommerce, Polylang, PHP, and PHPUnit are installed inside the test containers.

## Automated tests

Set up the local WordPress test site, then run the PHP suites:

```sh
tests/bin/setup.sh
tests/bin/run.sh
```

Setup downloads the pinned dependencies on the first run. The suites cover unit tests, WordPress AJAX requests, product translations, stock, carts, orders, and admin and storefront behavior. Use the disposable test database only; these tests create and delete product data. The site listens at `http://localhost:8098`.

Run the asset and JavaScript tests and check that minified assets match their sources:

```sh
npm ci
npm run check:assets
npm run test:js
```

GitHub Actions runs these checks on pushes and pull requests, with PHP 8.3, 8.4, and 8.5. It also validates the workflow files.

Stop the automated test site while keeping its data:

```sh
docker compose -f tests/compose.yaml stop
```

## Testing in a browser

The separate manual test store requires Make and rsync in addition to the tools above. Start it with:

```sh
make up
```

Open `http://localhost:8097` or `http://localhost:8097/wp-admin/`. Sign in as `manual-admin`, `manual-manager`, `manual-customer`, or `manual-subscriber`; each account uses the password `manual-password`. These accounts belong only to the local test store.

The store includes English and German products, linked variations, stock, coupons, and offline checkout. Check product editing, variation removal, stock synchronization, language switching, cart continuity, checkout, refunds, and order emails in both languages. Captured emails are available under **Tools → Manual store mail**.

Use `make classic` or `make block` to switch checkout formats. Run `make up` again to install your latest source changes while keeping the store data. `make stop` stops the store; `make reset` **deletes the manual store data** and recreates the fixtures. Clear browser cookies after a reset.

# Submitting Changes

* Push your changes to the according topic branch in your fork of the repository.
* [Create a pull request](../../../compare) to the original repository.
* Wait for feedback. Pull requests get reviewed on a regular basis.

# License

By contributing code, you grant its use under the [MIT](../LICENSE).
