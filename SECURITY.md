# Security Policy

This document describes how to report vulnerabilities in CashFlow and what reporters can expect in return.

## Supported versions

Security fixes are applied to the main branch of the repository. Organizations that maintain their own copies or forks are responsible for incorporating those fixes into their installations.

## How to report a vulnerability

Vulnerabilities must be reported privately to [contacto@sandrapuerto.com](mailto:contacto@sandrapuerto.com), using the subject line "CashFlow Security". They must not be disclosed in Issues, Discussions or pull requests, as these are public spaces and the information could be exploited before a fix is available.

The report should include, to the extent possible:

* A description of the problem and the affected component (API, Filament panel, domain layer or database).
* The steps required to reproduce it.
* The estimated impact, for example unauthorized access, tampering with accounting transactions or exposure of financial information.
* The version or commit identifier in which it was observed.
* A proposed mitigation, if one exists.

## Handling process

1. The author will acknowledge receipt of the report within five (5) business days.
2. The vulnerability will be assessed, and the reporter will be informed whether it has been confirmed, together with its estimated severity.
3. A fix will be developed and verified in private.
4. Once the fix is available, it will be published to the main branch together with a description of the problem, and the reporter will be credited if they so wish.

Remediation times depend on the severity and complexity of the issue. The author will keep the reporter informed of progress.

## Coordinated disclosure

Reporters are asked not to disclose the details of the vulnerability publicly until a fix is available, or until a joint disclosure date has been agreed.

## Scope

The following are considered in scope: issues affecting the code in this repository, including:

* Authentication or authorization flaws in the API and the administrative panel.
* Improper alteration, omission or deletion of vouchers and accounting transactions.
* Circumvention of double-entry rules or of the domain layer validations.
* Exposure of financial information or of third-party data.
* Code or query injection, and vulnerabilities in the handling of uploaded supporting documents.

The following are considered out of scope:

* Vulnerabilities in third-party dependencies (Laravel, Filament, PostgreSQL or others) that must be fixed in their own projects. They may be reported to CashFlow if a mitigation is possible from this repository.
* Problems arising from the configuration or infrastructure of a particular installation.
* Attacks that require physical access to the server or administrative credentials that are already compromised.
* Social engineering, denial of service through resource exhaustion, and findings from automated scanners without a demonstration of impact.

## Recommendations for those deploying CashFlow

Since each installation is single-organization and its operation is the responsibility of each organization, the following is recommended:

* Set `APP_DEBUG=false` and `APP_ENV=production` in production environments.
* Keep the `.env` file out of version control and with restricted permissions.
* Serve the application exclusively over HTTPS.
* Use dedicated, least-privilege credentials for the database.
* Enable multi-factor authentication for administrative users whenever possible.
* Keep PHP, Laravel, Filament and the remaining dependencies up to date.
* Perform regular backups of the database and of the supporting documents, and verify that they can be restored.

## Acknowledgments

Those who report vulnerabilities responsibly will be credited in the release notes of the fix, unless they prefer to remain anonymous.
