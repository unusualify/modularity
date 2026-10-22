#!/usr/bin/env node
// Validates a PR description against .github/PULL_REQUEST_TEMPLATE.md.
// Usage: PR_TITLE='feat: x' node check-pr-body.mjs < body.md   (or PR_BODY env)

import { readFileSync } from 'node:fs';

const TYPES = ['feat', 'fix', 'docs', 'style', 'refactor', 'perf', 'test', 'build', 'ci', 'chore', 'revert'];
const REQUIRED_SECTIONS = ['Checklist', 'Types of changes', 'Description', 'Commits', 'Behavior changes'];

export function parseSections(body) {
  const sections = {};
  let current = null;
  for (const line of body.replace(/<!--[\s\S]*?-->/g, '').split(/\r?\n/)) {
    const heading = line.match(/^##\s+(.+?)\s*$/);
    if (heading) {
      current = heading[1];
      sections[current] = [];
    } else if (current) {
      sections[current].push(line);
    }
  }
  return Object.fromEntries(Object.entries(sections).map(([k, v]) => [k, v.join('\n').trim()]));
}

export function checkPr(title, body) {
  const errors = [];
  const titleType = title.match(/^(\w+)(\([^)]+\))?!?: \S/)?.[1];
  const sections = parseSections(body ?? '');

  for (const name of REQUIRED_SECTIONS) {
    if (!(name in sections)) {
      errors.push(`Missing section "## ${name}" (keep every heading from the PR template).`);
    } else if (sections[name] === '') {
      errors.push(`Section "## ${name}" is empty.`);
    }
  }

  const types = sections['Types of changes'] ?? '';
  const checked = TYPES.filter((t) => new RegExp(`^- \\[[xX]\\] ${t}:`, 'm').test(types));
  if (checked.length === 0) {
    errors.push('Tick at least one box under "## Types of changes".');
  } else if (titleType && TYPES.includes(titleType) && !checked.includes(titleType)) {
    errors.push(`The title type "${titleType}" is not ticked under "## Types of changes".`);
  }

  const rows = (sections.Commits ?? '')
    .split('\n')
    .filter((l) => /^\|.*\|\s*$/.test(l.trim()))
    .slice(2)
    .filter((l) => l.replace(/[|\s-]/g, '') !== '');
  if ('Commits' in sections && rows.length === 0) {
    errors.push('"## Commits" needs a table with at least one row: | Commit | What |.');
  }

  return errors;
}

if (import.meta.url === `file://${process.argv[1]}`) {
  const title = process.env.PR_TITLE ?? '';
  const body = process.env.PR_BODY ?? readFileSync(0, 'utf8');
  const errors = checkPr(title, body);
  if (errors.length) {
    for (const e of errors) console.log(`::error title=PR description::${e}`);
    console.log('\nSee .cursor/rules/pull-requests.mdc and .github/PULL_REQUEST_TEMPLATE.md.');
    process.exit(1);
  }
  console.log('PR description OK.');
}
