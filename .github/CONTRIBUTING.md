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

# JavaScript Assets

Use Node.js 24.15 or newer. Install the pinned development dependencies and enable the local asset check once per clone:

```sh
npm ci
npm run hooks:install
```

After editing `public/js/Cart.js` or `public/js/Variables.js`, run `npm run build:js` and stage both the source and its `.min.js` file. Run `npm run check:js` to check all generated files, and `npm run test:js` for JavaScript regressions.

The pre-commit hook checks the staged versions and blocks stale minified assets. It never rewrites files or stages changes. Commits unrelated to these assets or their build configuration do not require Node.js. Hooks must be enabled separately in each clone; contributors with an existing hooks setup can call `node .github/scripts/build-js.cjs --check --staged` from their own hook.

# Running tests

Run these commands from the repository root. Install Docker with Compose v2, Bash, and Node.js 24.15 or newer. Docker must be running. WordPress, WooCommerce, Polylang, PHP, and PHPUnit are installed inside the test containers.

## Automated tests

Set up the local WordPress test site, then run the PHP suites:

```sh
tests/bin/setup.sh
tests/bin/run.sh
```

Setup downloads the pinned dependencies on the first run. The suites cover unit tests, WordPress AJAX requests, product translations, stock, carts, orders, and admin and storefront behavior. Use the disposable test database only; these tests create and delete product data. The site listens at `http://localhost:8098`.

Run the JavaScript tests and check that minified assets match their sources:

```sh
npm ci
npm run check:js
npm run test:js
```

GitHub Actions runs these checks on pushes and pull requests, with PHP 8.3, 8.4, and 8.5. It also validates the workflow files and checks release preparation with publishing disabled.

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

# Releases

Run **Actions → Publish release** on `main` and enter the new version. `release-it` updates versions and creates the GitHub release; `git-cliff` generates changelogs from commits; 10up deploys the tag to WordPress.org and builds the release ZIP. Tests run separately. If deployment fails, use GitHub's **Re-run failed jobs** to retry the deploy job.

Configure `SVN_USERNAME` and an SVN-specific `SVN_PASSWORD` in the GitHub environment named `wordpress-org`. Enter credentials directly in GitHub, never in source files or workflow inputs.

Edit the WordPress description in `.github/release/readme.toml`; the release regenerates `readme.txt` from that template. The latest eight commit summaries appear in the WordPress readme, while the complete notes are prepended to `CHANGELOG.md` without removing its history. Clear commit subjects become release notes.

# Submitting Changes

* Push your changes to the according topic branch in your fork of the repository.
* [Create a pull request](../../../compare) to the original repository.
* Wait for feedback. Pull requests get reviewed on a regular basis.

# License

By contributing code, you grant its use under the [MIT](../LICENSE).
