function validateForm() {
    let isValid = true;

    // Helper function to set error messages
    const setError = (id, message) => {
        const errorElement = document.getElementById(id + 'Error');
        errorElement.textContent = message;
        if (message !== '') {
            isValid = false;
        }
    };

    // 1. Student ID (Not empty, numbers only)
    const studentID = document.getElementById('student_id').value.trim();
    if (studentID === '') {
        setError('student_id', 'Student ID cannot be empty.');
    } else if (!/^\d+$/.test(studentID)) {
        setError('student_id', 'Student ID must contain only numbers.');
    } else {
        setError('student_id', '');
    }

    // 2. Name (Letters and spaces only)
    const nameInput = document.getElementById('name').value.trim();
    if (!/^[a-zA-Z\s]+$/.test(nameInput) || nameInput === '') {
        setError('name', 'Name must contain only letters and spaces.');
    } else {
        setError('name', '');
    }
    
    // 3. Email (Valid format)
    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; 
    if (!emailRegex.test(email)) {
        setError('email', 'Email must be in a valid format (e.g., user@domain.com).');
    } else {
        setError('email', '');
    }

    // The form submission is handled by PHP, but we prevent submission if client-side validation fails
    if (!isValid) {
        alert("Please correct the errors before submitting the form.");
    }
    
    return isValid;
}