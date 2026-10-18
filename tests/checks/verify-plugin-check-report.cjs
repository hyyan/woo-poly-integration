#!/usr/bin/env node
const fs = require('node:fs');

function verify(report) {
    const findings = [];
    let file,
        clean = false;
    for (const line of report.split(/\r?\n/)) {
        if (!line.trim()) continue;
        if (line.startsWith('FILE: ')) file = line.slice(6);
        else if (line.startsWith('[') && file) {
            const rows = JSON.parse(line);
            if (!Array.isArray(rows)) throw new Error('Invalid Plugin Check findings.');
            for (const row of rows) {
                if (!row || !['ERROR', 'WARNING'].includes(row.type) || !row.code)
                    throw new Error('Invalid Plugin Check finding.');
                findings.push({ ...row, file });
            }
        } else if (line === 'Success: Checks complete. No errors found.') clean = true;
        else throw new Error('Plugin Check did not produce a valid report.');
    }
    if (!findings.length && !clean) throw new Error('Plugin Check did not confirm a clean scan.');
    return {
        total: findings.length,
        errors: findings.filter((row) => row.type === 'ERROR').length,
        warnings: findings.filter((row) => row.type === 'WARNING').length
    };
}
module.exports = { verify };
if (require.main === module) {
    try {
        const result = verify(fs.readFileSync(process.argv[2], 'utf8'));
        console.log(JSON.stringify(result));
        process.exitCode = result.total ? 1 : 0;
    } catch (error) {
        console.error(error.message);
        process.exitCode = 1;
    }
}
