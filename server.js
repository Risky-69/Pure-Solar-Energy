const express = require('express');
const mysql = require('mysql2');
require('dotenv').config();

const app = express();
app.use(express.json());

// MySQL Connection Pool
const db = mysql.createPool({
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || 'Password',
  database: process.env.DB_NAME || 'puresolarenergy',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

// Route to fetch users table data
app.get('/puresolarenergy', (req, res) => {
  const sqlQuery = 'SELECT * FROM users';

  db.query(sqlQuery, (err, results) => {
    if (err) {
      console.error('Database query error:', err.message);
      return res.status(500).json({ error: err.message });
    }
    res.json(results);
  });
});

// Root route
app.get('/', (req, res) => {
  res.send('Pure Solar Energy API is running. Go to /puresolarenergy to view database contents.');
});

app.listen(3000, () => {
  console.log('Server running on http://localhost:3000');
});