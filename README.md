# DigiCareer Niger

A comprehensive digital diploma and career management platform connecting students, graduates, and employers in Niger.

## Features

### For Candidates
- **User Registration & Authentication**: Secure account creation with role-based access
- **Profile Management**: Create and edit professional profiles with bio, contact info, and profile picture
- **CV Builder**: Add education, skills, and work experience
- **Document Management**: Upload and manage diplomas, certificates, CVs, and portfolios
- **Job Browsing**: Search and filter available job opportunities
- **Job Applications**: Apply to jobs with optional cover letters
- **Invitations**: Receive and respond to employer invitations

### For Employers
- **Company Profile**: Manage company information, logo, and description
- **Job Posting**: Create and manage job listings
- **Candidate Search**: Find candidates by skills, education, and experience
- **Send Invitations**: Directly invite candidates to apply
- **Application Management**: Review, accept, or reject applications
- **Applicant Tracking**: View all applicants for each job posting

## Technology Stack

- **Frontend**: HTML5, CSS3 (Modern dark theme with glassmorphism), JavaScript
- **Backend**: PHP 
- **Database**: MySQL 
- **Authentication**: Session-based with password hashing

## Installation

### Prerequisites
- PHP  
- MySQL 
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

### Setup Instructions

1. **Clone/Download the project**
   ```
   Place files in your web server directory (e.g., htdocs, www, public_html)
   ```

2. **Create the database**
   ```
   - Open phpMyAdmin or MySQL command line
   - Import the digicareer_niger.sql file
   - This will create the database 'digicareer_niger' with all required tables
   ```

3. **Configure database connection**
   ```
   - Open db_connect.php
   - Update credentials if different from defaults:
     * DB_HOST: localhost
     * DB_NAME: digicareer_niger
     * DB_USER: root
     * DB_PASS: (empty by default)
   ```

4. **Set up file upload directories**
   ```
   The following directories will be created automatically:
   - uploads/profiles/ (for profile pictures)
   - uploads/logos/ (for company logos)
   - uploads/documents/ (for diplomas, CVs, etc.)
   
   Ensure your web server has write permissions to the uploads directory
   ```

5. **Access the application**
   ```
   Navigate to: http://localhost/FinalExamen/
   ```

## Default Test Accounts

The database includes two test accounts:

**Candidate Account**
- Email: candidate@test.com
- Password: password123

**Employer Account**
- Email: employer@test.com
- Password: password123

## File Structure

```
FinalExamen/
├── auth.php                    # Authentication helpers
├── db_connect.php              # Database connection
├── digicareer_niger.sql        # Database schema
├── style.css                   # Global styles
├── header.php                  # Header component
├── footer.php                  # Footer component
├── index.php                   # Landing page
├── register.php                # Registration
├── login.php                   # Login
├── logout.php                  # Logout
│
├── Candidate Pages
│   ├── candidate_dashboard.php # Dashboard
│   ├── profile.php             # Profile management
│   ├── my_documents.php        # Document management
│   ├── cv_builder.php          # CV builder
│   ├── browse_jobs.php         # Job browsing
│   ├── job_details.php         # Job details & apply
│   ├── my_applications.php     # Application tracking
│   └── invitations.php         # View invitations
│
├── Employer Pages
│   ├── employer_dashboard.php  # Dashboard
│   ├── company_profile.php     # Company profile
│   ├── post_job.php            # Post new job
│   ├── my_jobs.php             # Manage jobs
│   ├── job_applicants.php      # View applicants
│   ├── applications_received.php # All applications
│   ├── search_candidates.php   # Search & invite
│   └── view_candidate.php      # Candidate profile
│
└── uploads/                    # Upload directories
    ├── profiles/
    ├── logos/
    └── documents/
```

## Database Schema

### Main Tables
- **users**: User accounts (candidates & employers)
- **company_profiles**: Company information for employers
- **jobs**: Job postings
- **applications**: Job applications
- **documents**: Uploaded files
- **education**: Candidate education history
- **skills**: Candidate skills
- **experience**: Work experience
- **invitations**: Employer-to-candidate invitations

## Security Features

- Password hashing using PHP's password_hash()
- SQL injection protection via prepared statements
- XSS protection via htmlspecialchars()
- Session-based authentication
- Role-based access control
- File upload validation

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge

## Design Features

- Modern light/white theme with clean aesthetics
- Gradient accents with vibrant colors
- Glassmorphism effects with subtle shadows
- Smooth animations and transitions
- Fully responsive design
- Mobile-friendly interface

## Support

For issues or questions, please contact the development team.

## License

© 2025 DigiCareer Niger. All rights reserved.
