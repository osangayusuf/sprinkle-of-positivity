---
paths:
  - 'app/Actions/AssignParticipant.php,app/Http/Middleware/EnsurePartnerIsApproved.php,app/Http/Controllers/Admin/GroupController.php'
---

# Controllers Admin

## Group managers are admin-approved accountability partners
Only users with the partner role and partner_status=approved can be group managers (manager_ids validation). Partners sign up at /partners/register and are locked to partner.pending by EnsurePartnerIsApproved until an admin approves. A group has several partners; participants get group_user.partner_id via AssignParticipant (fewest participants wins, no capacity cap by decision). Declining moves the participant to the next partner or leaves them unassigned and alerts admins. Alerts are in-app only (ChallengeAlert, database channel). An admin can also hold the partner role (updateRole `also_partner`, which sets partner_status=approved), so one account does both jobs and appears in the manager list; admins who haven't opted in do not. Admins never apply to join a group (GroupPolicy::join is false for them) and can approve, reject, remove and decline-on-behalf without being managers.
