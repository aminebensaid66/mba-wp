# Production security and recovery runbook

This repository contains the WordPress application and local Docker stack. It does not provision production hosting, DNS, mail delivery, identity, or backup storage. Complete this runbook with the selected host before launch and keep the evidence in the private operations record; never commit credentials or personal data.

## Identity and access

- Require MFA/2FA for every account with Administrator or another privileged capability. WordPress core does not provide 2FA; enforce it through the host identity provider or a maintained MFA plugin. Test enrollment and account recovery, and keep recovery codes in the approved password manager.
- Give staff individual accounts, grant only the role/capabilities their work requires, remove access on departure, and review the privileged-user list quarterly. Do not share administrator logins. Use Editor/authoring accounts for content changes; private enquiry records are limited to Administrators by the plugin's dedicated capabilities.
- Disable dashboard theme/plugin file editing (`DISALLOW_FILE_EDIT`) in production. Prefer deployment-managed code updates. Use HTTPS for the whole site and protect the hosting control plane with MFA.
- Keep WordPress core, PHP, the theme, plugins, and host packages on supported versions. Subscribe to security/update alerts and assign an owner who checks them weekly.

## Secrets and environment

- Store database, SMTP, backup, analytics, and deployment credentials in the host's secret manager or protected environment configuration. Do not put live values in Git, issue comments, exported backups shared outside the operations team, or screenshots.
- Give production secrets unique values; rotate them after suspected exposure and when staff/vendors lose access. Restrict database access to the application network. Keep WordPress debug display disabled in production and protect/remove logs according to the retention policy.
- `.env.example` and the Compose defaults are for local development only. The local stack is not a production deployment template.

## Mail and domain authentication

- Select an authenticated SMTP/API mail provider and configure its supported WordPress integration using a host secret. Configure a verified sender on the business domain and a monitored reply/recipient mailbox. Do not rely on unauthenticated PHP mail for production.
- Publish the provider's exact SPF and DKIM DNS records. Configure DMARC initially in monitoring mode (`p=none`) with a monitored aggregate-report mailbox; review reports and legitimate senders, then move to a stricter policy only after the owner approves. Avoid creating multiple SPF records.
- Send a quote notification, contact notification, and customer acknowledgement to controlled mailboxes. Verify delivery, sender alignment, spam placement, and the provider's failure/bounce alert. Trigger a controlled failure and confirm it is visible in provider monitoring and that the enquiry remains available in wp-admin. Record date, provider, test recipient, and result without recording message content.

## Backups and recovery

- Schedule encrypted backups of both the production database and `wp-content/uploads` (including media and any application-managed private lead files) to storage outside the production host/account. A database-only backup does not recover uploads. Protect backup access separately and apply the approved backup retention period.
- Monitor backup job success and storage capacity. Alert a named operator on failure; do not treat a green dashboard as a restore test.
- Before launch and at least quarterly, restore the latest backup into an isolated staging environment. Verify database integrity, representative pages, media, quote/contact records, and private quote attachments. Confirm the staging copy is access-restricted, noindexes, and cannot send real customer emails or analytics. Record backup timestamp, restore timestamp, operator, checks, and result.
- Document recovery owner, host support path, recovery-point objective, recovery-time objective, and the order for restoring database/files. A production migration or restore must never overwrite the only known-good backup.

## Leads, uploads, and privacy requests

- The site stores contact and quote leads as private WordPress records. Quote attachments are stored outside the public uploads directory with restrictive file permissions; downloads require a nonce and the capability to edit that lead.
- The plugin registers leads in WordPress **Tools → Export Personal Data** and **Tools → Erase Personal Data**. Verify a test request in staging before launch. Verify erasure removes both matching lead types and their private quote attachments. A permanent delete from the Leads screen also removes the quote attachments; moving a lead to Trash retains it until permanent deletion.
- The business owner must choose and document a justified retention period for open, won, and closed enquiries before launch. Review the lead inbox monthly, delete records and attachments when the approved period expires, and include copies in the backup expiry policy. Check and document completion; deletion from the live database does not immediately remove retained backup copies.
- Publish an accurate privacy notice naming the data collected, purpose, recipients (including mail and hosting providers), retention period, privacy-request contact, and any analytics/cookie behavior. The owner/legal adviser approves the notice; this runbook is not a legal determination.

## Updates, monitoring, and incident response

- Test core, PHP, plugin, and theme updates in staging with a recent backup first. Smoke-test home, product/project pages, quote/contact submission, admin lead access, media, and mail. Deploy during a monitored window; verify the same paths afterward. Keep a documented rollback procedure that restores a compatible code/database/files snapshot.
- Monitor host uptime, TLS expiry, disk capacity, PHP/application errors, failed login patterns, mail-provider delivery/bounces, and backup completion. Alerts must reach a monitored owner. Avoid putting lead content, email addresses, tokens, or uploaded filenames in diagnostic logs.
- Maintain an incident contact list and a short procedure to contain access, rotate secrets, preserve relevant logs, restore service, and assess affected data. Escalate suspected personal-data exposure to the business owner promptly for legal/notification decisions.

## Launch evidence

The host/operator completes and dates this record outside source control:

| Control | Evidence to retain | Status |
| --- | --- | --- |
| MFA and least-privilege review | Privileged-account list and recovery test | Pending host setup |
| SMTP and failure alert | Delivery, bounce/failure, and alert test | Pending provider setup |
| Off-site backup and restore | Backup job and isolated restore record | Pending host setup |
| Lead retention and privacy request | Owner-approved period and staging erase test | Pending owner decision/test |
| Secret storage | Host secret-store inventory and access review | Pending host setup |
| Update and incident ownership | Named operators and staging/rollback exercise | Pending host setup |

These production controls cannot be marked verified from repository tests. Update this record when the real hosting, DNS, mail, identity, and backup services have been configured and tested.
