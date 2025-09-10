# Meeting Controller Updates - TODO

## Tasks Completed:
- [x] Update MeetingController.php index() method to support status filtering
- [x] Add new join($id) method to update meeting status from "pending" to "ongoing"
- [x] Ensure attendees relationship is loaded in responses
- [x] Add join route in routes/api.php
- [ ] Test the implementation

## Details:
- Modify index() to accept ?status=pending query parameter
- Add join endpoint: PATCH /api/meetings/{id}/join
- Return meeting with status and attendees in join response
- Preserve existing relationships and functionality
