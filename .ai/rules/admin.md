---
paths:
  - 'app/Actions/IssueCertificate.php,app/Actions/RevokeCertificate.php,app/Http/Controllers/Admin/CertificateController.php'
---

# Admin

## Revoked certificates are kept, never deleted
A revoked Certificate row stays (revoked_at set) so the daily certificates:issue command finds it and does not re-issue. IssueCertificate is idempotent and returns any existing row, revoked or not. Admins override eligibility by passing themself and a reason; issued_by is null for system-issued certificates. The public page /certificates/{code} shows only a "no longer valid" notice for revoked ones and must not leak the reason.
