---
paths:
  - 'app/Actions/AssignParticipant.php,app/Http/Middleware/EnsurePartnerIsApproved.php,app/Http/Controllers/Admin/GroupController.php'
---

# Controllers Admin

## Group managers are admin-approved accountability partners
Only users with the partner role and partner_status=approved can be group managers (manager_ids validation). Partners sign up at /partners/register and are locked to partner.pending by EnsurePartnerIsApproved until an admin approves. A group has several partners; participants get group_user.partner_id via AssignParticipant (fewest participants wins, no capacity cap by decision). Declining moves the participant to the next partner or leaves them unassigned and alerts admins. Alerts are in-app only (ChallengeAlert, database channel).
