#!/usr/bin/env python3
import json
import sys
from collections import Counter
from pathlib import Path

report = Path(sys.argv[1]).read_text()
findings = []
file_name = None
clean_message = False
for line in report.splitlines():
    if not line.strip():
        continue
    if line.startswith('FILE: '):
        file_name = line[6:]
    elif line.startswith('[') and file_name:
        rows = json.loads(line)
        if not isinstance(rows, list):
            raise SystemExit('Invalid Plugin Check findings.')
        for row in rows:
            if row.get('type') not in ('ERROR', 'WARNING') or not row.get('code'):
                raise SystemExit('Invalid Plugin Check finding.')
            findings.append(dict(row, file=file_name))
    elif line == 'Success: Checks complete. No errors found.':
        clean_message = True
    else:
        raise SystemExit('Plugin Check did not produce a valid report.')
if not findings and not clean_message:
    raise SystemExit('Plugin Check did not confirm a clean scan.')
counts = Counter(row['type'] for row in findings)
print(json.dumps(dict(total=len(findings), errors=counts['ERROR'], warnings=counts['WARNING'])))
raise SystemExit(1 if findings else 0)
