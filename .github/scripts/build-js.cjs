#!/usr/bin/env node
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');

const pairs = ['Cart', 'Variables'].map(name => ({
    source: `public/js/${name}.js`,
    output: `public/js/${name}.min.js`
}));
const tooling = ['package.json', 'package-lock.json', '.github/scripts/build-js.cjs'];
const args = process.argv.slice(2);
const staged = args.includes('--staged');
const check = args.includes('--check');

function git(...args) {
    return execFileSync('git', args, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] });
}

async function main() {
    if (args.some(arg => !['--check', '--staged'].includes(arg)) || (staged && !check)) {
        throw new Error('Usage: build-js.cjs [--check [--staged]]');
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
    try {
        ({ minify } = require('terser'));
        const manifest = staged ? JSON.parse(git('show', ':package.json')) : require('../../package.json');
        if (require('terser/package.json').version !== manifest.devDependencies.terser) {
            throw new Error('Minifier version mismatch.');
        }
    } catch (error) {
        throw new Error('The pinned minifier is unavailable. Run npm ci before building JavaScript.');
    }
    const read = file => staged ? git('show', `:${file}`) : fs.readFileSync(file, 'utf8');
    const generated = [];
    for (const pair of selected) {
        const result = await minify(read(pair.source), {
            ecma: 5,
            compress: true,
            mangle: true,
            format: { comments: /^!|@preserve|@license/ }
        });
        generated.push({ ...pair, code: result.code + '\n' });
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
            'Run npm run build:js, then stage each changed source and its .min.js file.');
    }
    if (check) console.log(`JavaScript assets match ${staged ? 'the staged' : 'their'} sources.`);
}

main().catch(error => {
    console.error(error.message);
    process.exitCode = 1;
});
