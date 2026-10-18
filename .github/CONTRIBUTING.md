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

Use Node.js 18 or newer. Install the pinned development dependency and enable the local asset check once per clone:

```sh
npm ci
npm run hooks:install
```

After editing `public/js/Cart.js` or `public/js/Variables.js`, run `npm run build:js` and stage both the source and its `.min.js` file. Run `npm run check:js` to check all generated files, and `npm run test:js` for JavaScript regressions.

The pre-commit hook checks the staged versions and blocks stale minified assets. It never rewrites files or stages changes. Commits unrelated to these assets or their build configuration do not require Node.js. Hooks must be enabled separately in each clone; contributors with an existing hooks setup can call `node .github/scripts/build-js.cjs --check --staged` from their own hook.

# Submitting Changes

* Push your changes to the according topic branch in your fork of the repository.
* [Create a pull request](../../../compare) to the original repository.
* Wait for feedback. Pull requests get reviewed on a regular basis.

# License

By contributing code, you grant its use under the [MIT](../LICENSE).
