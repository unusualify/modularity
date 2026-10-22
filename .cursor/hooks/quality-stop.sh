#!/usr/bin/env bash
# Cursor "stop" hook: runs `bin/agent-check check` when the agent finishes with
# uncommitted code changes in this package, and sends the first failure back to
# the agent as a follow-up message. Prints {} when there is nothing to report.
#
# Works from any working directory (package root or a parent workspace such as
# b2press-cms), because it resolves the package root from its own location.

set -uo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT" || { echo '{}'; exit 0; }

input="$(cat || true)"
status="$(printf '%s' "$input" | php -r '$d = json_decode(stream_get_contents(STDIN), true); echo is_array($d) ? ($d["status"] ?? "") : "";' 2>/dev/null)"

# Aborted or errored runs are not "finished work"; do not loop them back.
if [[ -n "$status" && "$status" != "completed" ]]; then
    echo '{}'
    exit 0
fi

if [[ "${MODULAROUS_SKIP_STOP_HOOK:-}" == "1" ]]; then
    echo '{}'
    exit 0
fi

changed="$(git status --porcelain --untracked-files=all -- \
    src modules config database routes tests vue/src vue/test vue/vitest-setup \
    vue/vitest.config.mjs quality.json phpstan.neon phpstan-baseline.neon composer.json 2>/dev/null)"

if [[ -z "$changed" ]]; then
    echo '{}'
    exit 0
fi

mkdir -p .agent
bin/agent-check check > .agent/stop-hook.log 2>&1
code=$?

if [[ "$code" -eq 0 ]]; then
    echo '{}'
    exit 0
fi

php -r '
$code = (int) $argv[1];
$result = is_file(".agent/last-check.json") ? json_decode(file_get_contents(".agent/last-check.json"), true) : null;
$step = null;
foreach (($result["steps"] ?? []) as $s) {
    if (($s["status"] ?? "") === "fail") { $step = $s; break; }
}
$name = $step["step"] ?? "unknown";
$tail = implode("\n", array_slice($step["failures"] ?? [], -20));
$message = "Quality gate failed: `bin/agent-check check` exited {$code} at step `{$name}`.\n\n"
    . "```\n{$tail}\n```\n\n"
    . "Fix the cause (see .agent/last-check.json and " . ($step["log"] ?? ".agent/logs") . "), then run `composer check` again. "
    . "Do not lower thresholds, grow the PHPStan baseline, skip or weaken tests. "
    . "If you cannot fix it, stop and report the failure to the user.";
echo json_encode(["followup_message" => $message], JSON_UNESCAPED_SLASHES), "\n";
' "$code"
exit 0
