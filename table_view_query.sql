user_attendance

SELECT 
user.id as user_id,
user.full_name,
attendance_session.id as session_id,
attendance_session.session_date,
SUM(IF(session_detail.present_on_time = 1, 1, 0)) as present_on_time,
SUM(IF(session_detail.present_late = 1, 1, 0)) as present_late,
SUM(
    IF(session_detail.present_on_time = 0 AND session_detail.present_late = 0, 1, 0)
) as not_present,
session_detail.present_out_at
FROM user
RIGHT JOIN session_detail ON user.id = session_detail.user_id
RIGHT JOIN attendance_session ON attendance_session.id = session_detail.attendance_session_id
WHERE 
user.role = 'user'
GROUP BY session_detail.attendance_session_id, user.id


user_attendance_by_user_id

SELECT 
user_id, full_name, 
SUM(present_on_time) as present_on_time, SUM(present_late) as present_late,
SUM(not_present) as not_present
FROM `user_attendance`
GROUP BY user_id