# Meetings Endpoints Update Instructions

## Changes Required

The Postman collection needs to be updated to use unified `/api/meetings` routes instead of separate `/api/admin/meetings` and `/api/client/meetings`.

## Search and Replace

In `Bareqq_Complete_API.postman_collection.json`, replace:

1. **Admin Meetings**: 
   - Replace: `"path": ["admin", "meetings"` 
   - With: `"path": ["meetings"`
   
2. **Client Meetings**:
   - Replace: `"path": ["client", "meetings"`
   - With: `"path": ["meetings"`

3. **Client Slots**:
   - Replace: `"path": ["client", "available-slots"]`
   - With: `"path": ["meetings", "available-slots"]`
   
   - Replace: `"path": ["client", "unbooked-slots"]`
   - With: `"path": ["meetings", "unbooked-slots"]`

## Manual Update Steps

1. Open `Bareqq_Complete_API.postman_collection.json` in a text editor
2. Find all instances of `/admin/meetings` and replace with `/meetings`
3. Find all instances of `/client/meetings` and replace with `/meetings`
4. Find `/client/available-slots` and replace with `/meetings/available-slots`
5. Find `/client/unbooked-slots` and replace with `/meetings/unbooked-slots`
6. Update the folder names:
   - Change "Admin - Meetings" to "Meetings (Unified)"
   - Remove "Client - Meetings" folder (merge into unified)

## Unified Structure

All endpoints will be under `/api/meetings`:
- GET `/api/meetings` - List meetings (admin sees all, client sees their own)
- GET `/api/meetings/{id}` - Get meeting details
- POST `/api/meetings` - Create meeting (client)
- DELETE `/api/meetings/{id}` - Delete meeting (client)
- GET `/api/meetings/{id}/join` - Join meeting (client)
- GET `/api/meetings/filter` - Filter meetings (client)
- GET `/api/meetings/available-slots` - Get available slots (both)
- GET `/api/meetings/unbooked-slots` - Get unbooked slots (both)
- PUT `/api/meetings/{id}/status` - Update status (admin)
- POST `/api/meetings/{id}/team` - Add team members (admin)
- DELETE `/api/meetings/{id}/team/{memberId}` - Remove team member (admin)
- POST `/api/meetings/{id}/team/sync-from-strategy` - Sync team (admin)

## Authorization

- Token determines access level
- Admin token: Full access
- Client token: Limited to own meetings
