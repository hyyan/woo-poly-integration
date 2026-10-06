#!/usr/bin/env bash
set -euo pipefail
repository=$(cd "$(dirname "$0")/../.." && pwd)
output=${1:-"$repository/work/release/woo-poly-integration.zip"}
staging=$(mktemp -d "${TMPDIR:-/tmp}/woopoly-package.XXXXXX")
trap 'rm -rf "$staging"' EXIT
rsync --archive --safe-links --exclude-from="$repository/.exclude-list" "$repository/" "$staging/woo-poly-integration/"
python3 - "$staging" "$output" <<'PY'
import hashlib
import json
import stat
import sys
import zipfile
from pathlib import Path
root = Path(sys.argv[1])
output = Path(sys.argv[2]).resolve()
output.parent.mkdir(parents=True, exist_ok=True)
files = sorted(p for p in root.rglob('*') if p.is_file())
if any(p.is_symlink() for p in root.rglob('*')):
    raise SystemExit('Release files must not contain symbolic links.')
with zipfile.ZipFile(output, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
    for path in files:
        entry = zipfile.ZipInfo(path.relative_to(root).as_posix(), (1980, 1, 1, 0, 0, 0))
        entry.compress_type = zipfile.ZIP_DEFLATED
        entry.external_attr = (stat.S_IFREG | 0o644) << 16
        archive.writestr(entry, path.read_bytes())
manifest = dict(archive_sha256=hashlib.sha256(output.read_bytes()).hexdigest(),
                files=[dict(path=p.relative_to(root).as_posix(), size=p.stat().st_size,
                            sha256=hashlib.sha256(p.read_bytes()).hexdigest()) for p in files])
output.with_suffix('.manifest.json').write_text(json.dumps(manifest, indent=2) + '\n')
print(f'Created {output.name} with {len(files)} files.')
print(f'SHA256 {manifest["archive_sha256"]}')
PY
