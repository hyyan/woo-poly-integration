#!/usr/bin/env node
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');

const pairs = ['Cart', 'Share', 'Variables'].map(name => ({
    source: `public/js/${name}.js`,
    output: `public/js/${name}.min.js`,
    type: 'js'
}));
pairs.push({ source: 'public/css/setup.css', output: 'public/css/setup.min.css', type: 'css' });
pairs.push({ source: 'public/css/share.css', output: 'public/css/share.min.css', type: 'css' });
pairs.push({ source: 'public/css/guide.css', output: 'public/css/guide.min.css', type: 'css' });
const tooling = ['package.json', 'package-lock.json', '.github/scripts/build-assets.cjs'];
const args = process.argv.slice(2);
const staged = args.includes('--staged');
const check = args.includes('--check');

function git(...args) {
    return execFileSync('git', args, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] });
}

async function main() {
    if (args.some(arg => !['--check', '--staged'].includes(arg)) || (staged && !check)) {
        throw new Error('Usage: build-assets.cjs [--check [--staged]]');
    }
    const root = path.resolve(__dirname, '../..');
    process.chdir(root);
    let selected = pairs;
    if (staged) {
        const changed = new Set(git('diff', '--cached', '--name-only', '-z').split('\0'));
        selected = tooling.some(file => changed.has(file)) ? pairs :
            pairs.filter(pair => changed.has(pair.source) || changed.has(pair.output));
        if (!selected.length) return;
    }
    let minify;
    let transform;
    try {
        ({ minify } = require('terser'));
        ({ transform } = require('esbuild'));
        const manifest = staged ? JSON.parse(git('show', ':package.json')) : require('../../package.json');
        if (require('terser/package.json').version !== manifest.devDependencies.terser ||
            require('esbuild/package.json').version !== manifest.devDependencies.esbuild) {
            throw new Error('Minifier version mismatch.');
        }
    } catch (error) {
        throw new Error('The pinned minifiers are unavailable. Run npm ci before building assets.');
    }
    const read = file => staged ? git('show', `:${file}`) : fs.readFileSync(file, 'utf8');
    const generated = [];
    for (const pair of selected) {
        let code;
        if (pair.type === 'css') {
            const result = await transform(read(pair.source), {
                loader: 'css',
                minify: true,
                legalComments: 'inline',
                target: ['chrome80', 'firefox75', 'safari13.1']
            });
            if (result.warnings.length) {
                throw new Error(`Invalid CSS in ${pair.source}: ${result.warnings.map(warning => warning.text).join('; ')}`);
            }
            code = result.code;
        } else {
            const result = await minify(read(pair.source), {
                ecma: 5,
                compress: true,
                mangle: true,
                format: { comments: /^!|@preserve|@license/ }
            });
            code = result.code + '\n';
        }
        generated.push({ ...pair, code });
    }
    const stale = [];
    for (const pair of generated) {
        if (check) {
            let current;
            try { current = read(pair.output); } catch (error) { current = null; }
            if (current !== pair.code) stale.push(pair.output);
        } else if (!fs.existsSync(pair.output) || read(pair.output) !== pair.code) {
            fs.writeFileSync(pair.output, pair.code);
            console.log(`Built ${pair.output}`);
        }
    }
    if (stale.length) {
        throw new Error(`Stale ${staged ? 'staged ' : ''}assets: ${stale.join(', ')}.\n` +
            'Run npm run build:assets, then stage each changed source and its minified file.');
    }
    if (check) console.log(`JavaScript and CSS assets match ${staged ? 'the staged' : 'their'} sources.`);
}

main().catch(error => {
    console.error(error.message);
    process.exitCode = 1;
});
