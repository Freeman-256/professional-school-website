<?php
/**
 * Attendance Model
 */

class Attendance {
    private $conn;
    private $table = 'attendance';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function mark($data) {
        $query = "INSERT INTO {$this->table} 
                  (student_id, class_id, attendance_date, status, remarks, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iisss',
            $data['student_id'],
            $data['class_id'],
            $data['attendance_date'],
            $data['status'],
            $data['remarks']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Attendance marked successfully'];
        }
        return ['success' => false, 'message' => 'Error marking attendance'];
    }

    public function getByStudent($student_id, $from_date = null, $to_date = null) {
        $query = "SELECT a.*, s.name as student_name, c.name as class_name 
                  FROM {$this->table} a 
                  JOIN students st ON a.student_id = st.id 
                  JOIN users s ON st.user_id = s.id 
                  JOIN classes c ON a.class_id = c.id 
                  WHERE a.student_id = ?";
        
        $params = [$student_id];
        $types = 'i';

        if ($from_date && $to_date) {
            $query .= " AND a.attendance_date BETWEEN ? AND ?";
            $params[] = $from_date;
            $params[] = $to_date;
            $types .= 'ss';
        }
        
        $query .= " ORDER BY a.attendance_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAttendancePercentage($student_id) {
        $query = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as percentage
                  FROM {$this->table} 
                  WHERE student_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $student_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
