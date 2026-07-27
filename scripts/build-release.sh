#!/usr/bin/env bash

set -euo pipefail

project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
plugin_file="${project_root}/wp-hardening-toolkit.php"
plugin_slug="wp-hardening-toolkit"
version="$(sed -nE 's/^[[:space:]]*\*[[:space:]]Version:[[:space:]]*([^[:space:]]+).*/\1/p' "${plugin_file}" | head -n 1)"

if [[ -z "${version}" ]]; then
	echo "Unable to read the plugin version from ${plugin_file}." >&2
	exit 1
fi

if [[ $# -gt 0 && "${1#v}" != "${version}" ]]; then
	echo "Requested version ${1#v} does not match plugin version ${version}." >&2
	exit 1
fi

build_root="$(mktemp -d)"
package_root="${build_root}/${plugin_slug}"
dist_root="${project_root}/dist"
archive_name="${plugin_slug}-${version}.zip"

cleanup() {
	rm -rf "${build_root}"
}
trap cleanup EXIT

mkdir -p "${package_root}" "${dist_root}"

rsync -a --prune-empty-dirs \
	--exclude='.git/' \
	--exclude='.github/' \
	--exclude='dist/' \
	--exclude='docs/' \
	--exclude='scripts/' \
	--exclude='tests/' \
	--exclude='vendor/' \
	--exclude='.DS_Store' \
	--exclude='.gitignore' \
	--exclude='.phpunit.result.cache' \
	--exclude='phpcs.xml.dist' \
	--exclude='phpstan.neon' \
	--exclude='phpunit.xml.dist' \
	--exclude='README.md' \
	"${project_root}/" "${package_root}/"

composer install \
	--working-dir="${package_root}" \
	--no-dev \
	--no-interaction \
	--no-progress \
	--optimize-autoloader \
	--classmap-authoritative

rm "${package_root}/composer.json" "${package_root}/composer.lock"
rm -f "${dist_root}/${archive_name}" "${dist_root}/${archive_name}.sha256"

(
	cd "${build_root}"
	zip -qr "${dist_root}/${archive_name}" "${plugin_slug}"
)

(
	cd "${dist_root}"
	if command -v sha256sum >/dev/null 2>&1; then
		sha256sum "${archive_name}" > "${archive_name}.sha256"
	else
		shasum -a 256 "${archive_name}" > "${archive_name}.sha256"
	fi
)

echo "Built dist/${archive_name}"
echo "Built dist/${archive_name}.sha256"
