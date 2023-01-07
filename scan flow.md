scan flow:

- user scan and send qr code to server
- server decrypt and decode qr code
- server check is decoded qr code is available in database
- if exists insert user attendance to session_detail table and update attendance counter in attendance_session table
- re-call generate qr code based on data that user post
- send notification to admin with included new qr code data to update qr code image in admin and included attendance counter data
- done.