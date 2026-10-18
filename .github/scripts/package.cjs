#!/usr/bin/env node
// ZIP uses fixed timestamps, permissions and ordering for reproducible release assets.
const fs = require('node:fs');
const path = require('node:path');
const { createHash } = require('node:crypto');
const { execFileSync } = require('node:child_process');

function filesIn(root, prefix = '') {
    return fs
        .readdirSync(path.join(root, prefix), { withFileTypes: true })
        .flatMap((entry) => {
            const name = prefix + entry.name;
            if (entry.isSymbolicLink())
                throw new Error('Release files must not contain symbolic links.');
            if (entry.isDirectory()) return filesIn(root, name + '/');
            if (!entry.isFile() || /[\r\n]/.test(name))
                throw new Error('Invalid release file: ' + name);
            return [name];
        })
        .sort();
}
const sha256 = (file) => createHash('sha256').update(fs.readFileSync(file)).digest('hex');

function packageFiles(root, output) {
    root = path.resolve(root);
    output = path.resolve(output);
    const files = filesIn(root);
    if (!files.length) throw new Error('Cannot package an empty directory.');
    for (const file of files) {
        fs.chmodSync(path.join(root, file), 0o644);
        fs.utimesSync(path.join(root, file), 315532800, 315532800);
    }
    fs.mkdirSync(path.dirname(output), { recursive: true });
    fs.rmSync(output, { force: true });
    execFileSync('zip', ['-X', '-q', '-9', output, '-@'], {
        cwd: root,
        input: files.join('\n') + '\n',
        env: { ...process.env, TZ: 'UTC' }
    });
    const manifest = {
        archive_sha256: sha256(output),
        files: files.map((file) => ({
            path: file,
            size: fs.statSync(path.join(root, file)).size,
            sha256: sha256(path.join(root, file))
        }))
    };
    fs.writeFileSync(
        output.replace(/\.zip$/, '') + '.manifest.json',
        JSON.stringify(manifest, null, 2) + '\n'
    );
    console.log(
        `Created ${path.basename(output)} with ${files.length} files.\nSHA256 ${manifest.archive_sha256}`
    );
    return manifest;
}
module.exports = { packageFiles, filesIn, sha256 };
if (require.main === module) {
    try {
        if (process.argv.length !== 4) throw new Error('Usage: package.cjs STAGING OUTPUT.zip');
        packageFiles(process.argv[2], process.argv[3]);
    } catch (error) {
        console.error(error.message);
        process.exitCode = 1;
    }
}
