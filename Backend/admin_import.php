<?php
// ... inside admin controller, after submitting a file form ...

if (isset($_FILES['student_file']) && $_FILES['student_file']['error'] == 0) {
    $fileName = $_FILES['student_file']['tmp_name'];
    
    // Basic CSV Validation
    $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
    if(!in_array($_FILES['student_file']['type'],$mimes)){
        die("Sorry, only CSV files allowed for this demo.");
    }

    $handle = fopen($fileName, "r");
    if ($handle !== FALSE) {
        // Skip the header row
        fgetcsv($handle, 1000, ","); 
        
        $pdo->beginTransaction();
        try {
             $stmt = $pdo->prepare("INSERT INTO users (username, first_name, last_name, email, role, password_hash) VALUES (?, ?, ?, ?, 'student', ?)");
             // Default password for imported students
             $defaultPassHash = password_hash('algiers2025', PASSWORD_DEFAULT);

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // $data[0] is username, $data[1] is firstname, etc. based on CSV structure above
                // Add validations here (e.g., check if username already exists)
                $stmt->execute([$data[0], $data[1], $data[2], $data[3], $defaultPassHash]);
            }
            $pdo->commit();
            echo "Students imported successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Error importing: " . $e->getMessage();
        }
        fclose($handle);
    }
}
?>