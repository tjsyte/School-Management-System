# School Management System

This project is a School Management System that manages information for administrators, students, teachers, courses, grades, and subjects. The system is developed using PHP, Oracle SQL Developer, XAMPP for Apache, OCI Client, and FPDF for generating reports.

## Features
- **Admin Management**: Manage admin accounts, including username, email, and password.
- **Student Management**: Handle student details, courses, sections, and academic years.
- **Teacher Management**: Manage teacher information and assign courses.
- **Course Management**: Add and update course information.
- **Subject Management**: Assign subjects to courses and academic years.
- **Grade Management**: Store and manage student grades.
- **Report Generation**: Use FPDF to generate detailed reports for grades and other data.

## Prerequisites
Before running this system, ensure you have the following installed and configured:
- [XAMPP](https://www.apachefriends.org/index.html) (for Apache server and PHP)
- [Oracle SQL Developer](https://www.oracle.com/database/sqldeveloper/) (for managing the database)
- Oracle OCI Client (for PHP-Oracle database interaction)
- [FPDF Library](http://www.fpdf.org/) (for report generation)

## Database Schema

### Tables
#### `admin` Table
| Column Name | Data Type          | Nullable | Description        |
|-------------|--------------------|----------|--------------------|
| `ADMIN_ID`  | NUMBER             | No       | Primary key        |
| `USERNAME`  | VARCHAR2(50 BYTE)  | No       | Admin username     |
| `EMAIL`     | VARCHAR2(100 BYTE) | No       | Admin email        |
| `PASSWORD`  | VARCHAR2(100 BYTE) | No       | Admin password     |

#### `course` Table
| Column Name  | Data Type          | Nullable | Description      |
|--------------|--------------------|----------|------------------|
| `COURSE_ID`  | NUMBER             | No       | Primary key      |
| `COURSE_NAME`| VARCHAR2(100 BYTE) | No       | Name of the course|

#### `grade` Table
| Column Name  | Data Type         | Nullable | Description              |
|--------------|-------------------|----------|--------------------------|
| `GRADE_ID`   | NUMBER            | No       | Primary key              |
| `STUDENT_ID` | NUMBER(38,0)      | No       | Foreign key (student)    |
| `SUBJECT_ID` | NUMBER(38,0)      | No       | Foreign key (subject)    |
| `TEACHER_ID` | NUMBER(38,0)      | No       | Foreign key (teacher)    |
| `GRADES`     | NUMBER(5,2)       | No       | Grade value (0-100)      |

#### `student` Table
| Column Name  | Data Type          | Nullable | Description        |
|--------------|--------------------|----------|--------------------|
| `STUDENT_ID` | NUMBER             | No       | Primary key        |
| `FULL_NAME`  | VARCHAR2(100 BYTE) | No       | Full name          |
| `LAST_NAME`  | VARCHAR2(100 BYTE) | No       | Last name          |
| `EMAIL`      | VARCHAR2(100 BYTE) | No       | Email address      |
| `PASSWORD`   | VARCHAR2(100 BYTE) | No       | Login password     |
| `COURSE_ID`  | NUMBER             | Yes      | Foreign key (course)|
| `SECTION`    | VARCHAR2(100 BYTE) | Yes      | Section information|
| `YEAR`       | VARCHAR2(100 BYTE) | Yes      | Academic year      |

#### `subject` Table
| Column Name     | Data Type          | Nullable | Description       |
|------------------|--------------------|----------|-------------------|
| `SUBJECT_ID`     | NUMBER             | No       | Primary key       |
| `SUBJECT_NAME`   | VARCHAR2(100 BYTE) | No       | Name of the subject|
| `SUBJECT_CODE`   | VARCHAR2(50 BYTE)  | Yes      | Subject code      |
| `COURSE_ID`      | NUMBER             | No       | Foreign key (course)|
| `YEAR`           | VARCHAR2(100 BYTE) | No       | Academic year     |

#### `teacher` Table
| Column Name  | Data Type          | Nullable | Description        |
|--------------|--------------------|----------|--------------------|
| `TEACHER_ID` | NUMBER             | No       | Primary key        |
| `FULL_NAME`  | VARCHAR2(100 BYTE) | No       | Full name          |
| `LAST_NAME`  | VARCHAR2(100 BYTE) | No       | Last name          |
| `EMAIL`      | VARCHAR2(100 BYTE) | No       | Email address      |
| `PASSWORD`   | VARCHAR2(100 BYTE) | No       | Login password     |
| `COURSE_ID`  | NUMBER             | Yes      | Foreign key (course)|

## How to Run

1. **Set up the Database**:
   - Use Oracle SQL Developer to create the required tables with the schema above.

2. **Configure OCI and PHP**:
   - Install and configure the Oracle OCI Client for PHP to enable PHP-Oracle connectivity.

3. **Set up XAMPP**:
   - Start the Apache server using XAMPP.
   - Place the project files in the `htdocs` directory.

4. **Install FPDF**:
   - Download the [FPDF library](http://www.fpdf.org/) and include it in the project for generating reports.

5. **Run the Application**:
   - Open a web browser and navigate to `http://localhost/[project_folder]/`.

## Tools and Technologies
- **Database**: Oracle SQL Developer
- **Backend**: PHP
- **Server**: Apache (via XAMPP)
- **Library**: FPDF for generating PDF reports

## Usage
- Admins can log in to manage courses, students, teachers, and grades.
- Teachers can log in to view assigned courses and update student grades.
- Students can log in to view grades and course details.

## Future Enhancements
- Add authentication and user roles.
- Improve UI design.
- Implement additional report generation features.

## License
This project is licensed under the MIT License.
