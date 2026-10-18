const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { spawnSync, execFileSync } = require('node:child_process');
const { test, beforeEach, afterEach } = require('node:test');

const repository = path.resolve(__dirname, '../..');
let fixture;

function run(command, args) {
    return spawnSync(command, args, { cwd: fixture, encoding: 'utf8' });
}
function git(...args) {
    return execFileSync('git', args, { cwd: fixture, encoding: 'utf8' });
}
function build(...args) {
    return run(process.execPath, ['.github/scripts/build-js.cjs', ...args]);
}
function hook() {
    return run('git', ['-c', 'core.hooksPath=.githooks', 'hook', 'run', 'pre-commit']);
}

beforeEach(() => {
    fixture = fs.mkdtempSync(path.join(os.tmpdir(), 'woopoly-assets-test-'));
    for (const file of ['.github/scripts/build-js.cjs', '.githooks/pre-commit', 'package.json', 'package-lock.json']) {
        fs.mkdirSync(path.dirname(path.join(fixture, file)), { recursive: true });
        fs.copyFileSync(path.join(repository, file), path.join(fixture, file));
    }
    fs.symlinkSync(path.join(repository, 'node_modules'), path.join(fixture, 'node_modules'), 'dir');
    fs.mkdirSync(path.join(fixture, 'public/js'), { recursive: true });
    for (const name of ['Cart', 'Variables']) {
        fs.writeFileSync(path.join(fixture, `public/js/${name}.js`), `window.${name} = 1;\n`);
    }
    git('init', '--quiet');
    assert.equal(build().status, 0);
    git('add', 'package.json', 'package-lock.json', '.github', '.githooks', 'public');
});

afterEach(() => fs.rmSync(fixture, { recursive: true, force: true }));

test('builds both assets deterministically and check mode never rewrites stale output', () => {
    const file = path.join(fixture, 'public/js/Cart.min.js');
    const first = fs.readFileSync(file, 'utf8');
    assert.equal(build().status, 0);
    assert.equal(fs.readFileSync(file, 'utf8'), first);
    fs.appendFileSync(path.join(fixture, 'public/js/Cart.js'), 'window.updated = true;\n');
    const check = build('--check');
    assert.notEqual(check.status, 0);
    assert.match(check.stderr, /Stale.*Cart\.min\.js/);
    assert.equal(fs.readFileSync(file, 'utf8'), first);
    assert.equal(build().status, 0);
    assert.equal(build('--check').status, 0);
});

test('hook accepts matching staged source and output', () => {
    assert.equal(hook().status, 0);
});

test('hook rejects stale staged output even when the working copy was rebuilt', () => {
    const stagedBefore = git('show', ':public/js/Cart.min.js');
    fs.appendFileSync(path.join(fixture, 'public/js/Cart.js'), 'window.updated = true;\n');
    git('add', 'public/js/Cart.js');
    assert.equal(build().status, 0);
    const check = hook();
    assert.notEqual(check.status, 0);
    assert.match(check.stderr, /Stale staged assets/);
    assert.equal(git('show', ':public/js/Cart.min.js'), stagedBefore);
    assert.notEqual(fs.readFileSync(path.join(fixture, 'public/js/Cart.min.js'), 'utf8'), stagedBefore);
});

test('hook checks the index without consuming unstaged source edits', () => {
    fs.appendFileSync(path.join(fixture, 'public/js/Variables.js'), 'window.unstaged = true;\n');
    const before = git('diff');
    assert.equal(hook().status, 0);
    assert.equal(git('diff'), before);
    assert.doesNotMatch(git('show', ':public/js/Variables.js'), /unstaged/);
});

test('hook rejects edited minified files that do not match their source', () => {
    fs.appendFileSync(path.join(fixture, 'public/js/Variables.min.js'), 'window.tampered = true;\n');
    git('add', 'public/js/Variables.min.js');
    assert.notEqual(hook().status, 0);
});

test('unrelated staged changes do not require the minifier', () => {
    git('rm', '-r', '--cached', '--quiet', '.');
    fs.unlinkSync(path.join(fixture, 'node_modules'));
    fs.writeFileSync(path.join(fixture, 'README.md'), 'Documentation only\n');
    git('add', 'README.md');
    assert.equal(hook().status, 0);
});

test('relevant changes fail with installation guidance when dependencies are missing', () => {
    fs.unlinkSync(path.join(fixture, 'node_modules'));
    const check = hook();
    assert.notEqual(check.status, 0);
    assert.match(check.stderr, /npm ci/);
});

test('syntax errors do not leave a partly rebuilt asset pair', () => {
    const before = fs.readFileSync(path.join(fixture, 'public/js/Cart.min.js'), 'utf8');
    fs.appendFileSync(path.join(fixture, 'public/js/Cart.js'), 'window.updated = true;\n');
    fs.writeFileSync(path.join(fixture, 'public/js/Variables.js'), 'function {');
    assert.notEqual(build().status, 0);
    assert.equal(fs.readFileSync(path.join(fixture, 'public/js/Cart.min.js'), 'utf8'), before);
});
