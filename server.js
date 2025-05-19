const express = require('express');
const mysql = require('mysql2');
const path = require('path');
const app = express();
const port = 8000;

// Middleware to parse incoming JSON and URL-encoded data
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static files from the 'public' folder
app.use(express.static(path.join(__dirname, 'public')));

// MySQL connection configuration – replace with your credentials
const connection = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'incident_report_db'
});

// Connect to the database
connection.connect(err => {
    if (err) {
        console.error('Database connection error: ' + err.stack);
        return;
    }
    console.log('Connected to MySQL');
});

// API endpoint for IT staff login
app.post('/api/login', (req, res) => {
    const { username, password } = req.body;
    // In production, use hashed passwords and proper validation
    connection.query(
        'SELECT * FROM users WHERE user_name = ? AND user_password = ?',
        [username, password],
        (err, results) => {
            if (err) return res.status(500).json({ success: false, message: 'Server error' });
            if (results.length > 0) {
                res.json({ success: true, staffId: results[0].id });
            } else {
                res.json({ success: false, message: 'Invalid credentials' });
            }
        }
    );
});

// API endpoint for submitting an incident report (guest)
app.post('/api/report', (req, res) => {
    console.log('Received report:', req.body); // Log request data
    const { problemType, severity, assignedStaff, description, reporterName, reporterDept } = req.body;
    const severityInt = parseInt(severity, 10);
    const assignedStaffInt = parseInt(assignedStaff, 10);

    connection.query(
        'INSERT INTO incidents (problem_type, severity, assigned_staff, description, reporter_name, reporter_department) VALUES (?, ?, ?, ?, ?, ?)',
        [problemType, severityInt, assignedStaffInt, description, reporterName, reporterDept],
        (err, results) => {
            if (err) {
                console.error('Error during report submission:', err);
                return res.status(500).json({ success: false, message: err.message });
            }
            res.json({ success: true, message: 'Report submitted successfully' });
        }
    );
});



// API endpoint for retrieving incidents assigned to a staff member
app.get('/api/dashboard/:staffId', (req, res) => {
    const { staffId } = req.params;
    connection.query(
        'SELECT * FROM incidents WHERE assigned_staff = ?',
        [staffId],
        (err, results) => {
            if (err) return res.status(500).json({ success: false, message: 'Error fetching data' });
            res.json(results);
        }
    );
});

// Start the server
app.listen(port, () => {
    console.log(`Server running on http://192.168.10.32:${port}`);
  });
