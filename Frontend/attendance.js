document.addEventListener('DOMContentLoaded', updateAttendanceTable);

function updateAttendanceTable() {
    const tableBody = document.getElementById('attendanceBody');
    if (!tableBody) return; // Exit if the table body isn't found
    
    const studentRows = tableBody.getElementsByTagName('tr');
    
    for (let row of studentRows) {
        let totalAbsences = 0;
        let totalParticipations = 0;
        
        const cells = row.getElementsByTagName('td');
        
        // Loop through S1/P1 to S6/P6 cells (indices 2 to 13)
        for (let i = 2; i <= 13; i++) {
            const cell = cells[i];
            const content = cell.textContent.trim();
            
            // i=2, 4, 6, 8, 10, 12 are Attendance (S) columns
            if (i % 2 === 0) { 
                // Absence if attendance cell is empty
                if (content === '') {
                    totalAbsences++;
                }
            } else { 
                // i=3, 5, 7, 9, 11, 13 are Participation (P) columns
                if (content === '✓') {
                    totalParticipations++;
                }
            }
        }
        
        // --- Step 1 & 2: Update Count Columns ---
        cells[14].textContent = totalAbsences; 
        cells[15].textContent = totalParticipations;
        
        
        // --- Step 3: Highlight the Student's Row ---
        row.classList.remove('highlight-green', 'highlight-yellow', 'highlight-red'); 
        
        if (totalAbsences < 3) {
            row.classList.add('highlight-green');
        } else if (totalAbsences <= 4) {
            row.classList.add('highlight-yellow');
        } else { // 5 or more absences
            row.classList.add('highlight-red');
        }
        
        // --- Step 4: Display Message ---
        
        let message = "";
        const isExcluded = totalAbsences >= 5;
        const isLowAttendance = totalAbsences >= 3;
        const isExcellentParticipation = totalParticipations >= 3;

        if (isExcluded) {
            message = "Excluded – too many absences – You need to participate more";
        } else if (isLowAttendance && isExcellentParticipation) {
            message = "Warning – attendance low – Excellent Participation";
        } else if (isLowAttendance) {
            message = "Warning – attendance low – You need to participate more";
        } else if (isExcellentParticipation) {
            message = "Good attendance – Excellent participation";
        } else {
            message = "Good attendance – You need to participate more";
        }
        
        cells[16].textContent = message;
    }
}