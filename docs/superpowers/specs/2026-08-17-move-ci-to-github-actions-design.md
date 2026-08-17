# Move CI to GitHub Actions

Issue: [#23](https://github.com/whitemerry/phpkin/issues/23)

## Goal

Replace the Buddy pipeline (`buddy.yml`) with a GitHub Actions workflow that tests
phpkin across the PHP versions it supports, and runs on pull requests as well as
pushes to `master`.

## Motivation

Buddy runs a single job on PHP 7.1 and only on pushes to `master`. Pull requests
from contributors go untested until after merge, and the repo has taken several
outside PRs (#8, #12, #13). Moving to Actions gives per-version status checks on
every PR without maintaining CI outside the repository.

## Scope

In scope: the workflow file, the Composer and PHPUnit configuration changes needed
to make a multi-version matrix run, removal of the Buddy pipeline, and the README
badge that stops being accurate.

Out of scope: modernizing the test suite beyond what the matrix requires, changing
the library's `php >=5.3.0` constraint, and adding static analysis or linting.

## Decisions

### Test matrix: 5.6, 7.0, 7.4, 8.0, 8.5

The oldest and newest of each supported PHP major, plus the `.0` entry points where
breaking language changes land. A full 12-version matrix was rejected as redundant:
every version from 7.3 up resolves to the same PHPUnit 9.6, so the extra jobs
re-test the same harness.

This gives up coverage of PHPUnit 7.5 and 8.5 (which PHP 7.1 and 7.2 would have
pulled in). That is an accepted trade: those majors only exercise the test harness,
not the library.

### PHPUnit: one flexible constraint, capped at 9.6

`require-dev` widens to `^5.7 || ^6.0 || ^7.0 || ^8.0 || ^9.0`. Each job runs
`composer update`, and Composer resolves the newest PHPUnit that the job's PHP
version allows:

| PHP | PHPUnit |
| --- | --- |
| 5.6 | 5.7 |
| 7.0 | 6.5 |
| 7.4 | 9.6 |
| 8.0 | 9.6 |
| 8.5 | 9.6 |

Capping at 9.6 keeps a single `phpunit.xml` valid for every job. PHPUnit 10 dropped
the old XML schema outright, so allowing 10+ would mean maintaining two config files
and selecting between them per job.

`composer update` rather than `composer install`: `composer.lock` is gitignored, and
each PHP version must resolve its own PHPUnit anyway.

Known risk: PHPUnit 9.6 is not officially supported on PHP 8.5. That job may need
triage once the workflow runs.

### No coverage reporting

Buddy uploaded clover to CodeClimate via `cc-test-reporter`. The Actions workflow
drops this: every job runs with `coverage: none`, no coverage driver is installed,
and no secrets are needed. This also keeps the workflow working on forked PRs, where
secrets are unavailable.

Consequence: the CodeClimate **Test Coverage** badge in the README goes stale and is
removed. The **Maintainability** badge does not depend on uploads and stays.

### Triggers: push to `master` and all pull requests

Pull request runs are the main gain over Buddy. Pushes to other branches are not
triggered, to avoid duplicate runs when a branch has an open PR.

### Slack notifications dropped

`buddy.yml` had three Slack actions (success, failure, back-to-success). These are
not reimplemented. GitHub's own Slack app subscribes to workflow results without
requiring a webhook secret, which matches the decision to keep this workflow
secret-free.

## Required source changes

Two existing problems block a multi-version matrix. Both are in scope because the
matrix cannot pass without them.

### Test base class

All six test classes extend `\PHPUnit_Framework_TestCase`, removed in PHPUnit 6.
They must extend `\PHPUnit\Framework\TestCase` instead. That alias exists as far back
as PHPUnit 5.7, so the change is backward compatible and the PHP 5.6 job keeps
working.

No test defines `setUp()` or `tearDown()`, so the usual `: void` signature conflict
between PHPUnit majors does not arise.

### Test file discovery

`phpunit.xml` scans `./tests/` with `suffix=".php"`, which also loads
`tests/Mocker.php` — a plain helper class, not a test. PHPUnit 5.7 tolerated this;
PHPUnit 9 reports "No tests found in class" as a warning and exits non-zero, which
would fail three of the five jobs.

All six real tests are named `*TestCase.php`, so narrowing the suffix to
`TestCase.php` excludes the helper on every PHPUnit version.

## Changes

| File | Change |
| --- | --- |
| `.github/workflows/tests.yml` | New. Single `tests` job, 5-version matrix. |
| `composer.json` | Widen `require-dev` PHPUnit constraint. |
| `phpunit.xml` | Suffix to `TestCase.php`; drop clover `<logging>` and dead `<filter>`. |
| `tests/*TestCase.php` | Extend `\PHPUnit\Framework\TestCase` (6 files). |
| `buddy.yml` | Delete. |
| `README.md` | Remove Test Coverage badge. |

## Workflow structure

One workflow file, one job:

- `runs-on: ubuntu-latest`
- `fail-fast: false`, so one failing version does not mask the others
- Steps: `actions/checkout` → `shivammathur/setup-php` (`coverage: none`) →
  Composer cache keyed on PHP version → `composer update --prefer-dist
  --no-interaction --no-progress` → `vendor/bin/phpunit`

The extension setup in `buddy.yml` (gd, pdo_mysql, zip, xdebug) is not carried over.
Those were scaffolding for the Buddy Docker image; the library is dependency-free and
requires none of them.

## Verification

No PHP is available locally, so the workflow run on this branch is the proof. Both
known risks are confirmed or refuted from that run:

1. **PHP 5.6 and 7.0 on the `ubuntu-latest` (24.04) image.** If `setup-php` cannot
   provision them, pin those two versions to `ubuntu-22.04` with a matrix `include`.
2. **PHPUnit 9.6 on PHP 8.5.** Unsupported combination. If it fails on the harness
   rather than on library behavior, the options are to drop 8.5 from the matrix or
   revisit the PHPUnit 10+ cap.

Done means: all five jobs green on the branch, and the PR shows five status checks.
